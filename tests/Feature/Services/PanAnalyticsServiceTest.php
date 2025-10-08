<?php

declare(strict_types=1);

use App\Services\Pan\PanAnalyticsService;
use Illuminate\Support\Facades\DB;
use Pan\Enums\EventType;
use Pan\PanConfiguration;

beforeEach(function () {
    // Clear analytics before each test
    DB::table('pan_analytics')->truncate();

    // Reset Pan configuration
    PanConfiguration::reset();
});

test('allows exact match analytics', function () {
    PanConfiguration::allowedAnalytics(['test-exact']);

    $service = app(PanAnalyticsService::class);
    $service->increment('test-exact', EventType::IMPRESSION);

    expect(DB::table('pan_analytics')->where('name', 'test-exact')->count())->toBe(1);
});

test('blocks non-allowed analytics', function () {
    PanConfiguration::allowedAnalytics(['test-allowed']);

    $service = app(PanAnalyticsService::class);
    $service->increment('test-blocked', EventType::IMPRESSION);

    expect(DB::table('pan_analytics')->where('name', 'test-blocked')->count())->toBe(0);
});

test('supports wildcard patterns', function () {
    PanConfiguration::allowedAnalytics(['hunt-*']);

    $service = app(PanAnalyticsService::class);

    // Should allow
    $service->increment('hunt-123', EventType::IMPRESSION);
    $service->increment('hunt-456', EventType::IMPRESSION);
    $service->increment('hunt-abc', EventType::IMPRESSION);

    // Should block
    $service->increment('post-123', EventType::IMPRESSION);
    $service->increment('other-456', EventType::IMPRESSION);

    expect(DB::table('pan_analytics')->where('name', 'like', 'hunt-%')->count())->toBe(3)
        ->and(DB::table('pan_analytics')->where('name', 'not like', 'hunt-%')->count())->toBe(0);
});

test('supports multiple patterns including wildcards', function () {
    PanConfiguration::allowedAnalytics([
        'onbording-profile',
        'hunt-*',
        'post-*',
    ]);

    $service = app(PanAnalyticsService::class);

    $service->increment('onbording-profile', EventType::IMPRESSION);
    $service->increment('hunt-123', EventType::IMPRESSION);
    $service->increment('post-456', EventType::IMPRESSION);
    $service->increment('comment-789', EventType::IMPRESSION); // Should be blocked

    expect(DB::table('pan_analytics')->count())->toBe(3)
        ->and(DB::table('pan_analytics')->where('name', 'onbording-profile')->count())->toBe(1)
        ->and(DB::table('pan_analytics')->where('name', 'hunt-123')->count())->toBe(1)
        ->and(DB::table('pan_analytics')->where('name', 'post-456')->count())->toBe(1)
        ->and(DB::table('pan_analytics')->where('name', 'comment-789')->count())->toBe(0);
});

test('respects max analytics limit', function () {
    PanConfiguration::allowedAnalytics(['test-*']);
    PanConfiguration::maxAnalytics(3);

    $service = app(PanAnalyticsService::class);

    $service->increment('test-1', EventType::IMPRESSION);
    $service->increment('test-2', EventType::IMPRESSION);
    $service->increment('test-3', EventType::IMPRESSION);
    $service->increment('test-4', EventType::IMPRESSION); // Should be blocked (limit reached)

    expect(DB::table('pan_analytics')->count())->toBe(3);
});

test('increments existing analytics correctly', function () {
    PanConfiguration::allowedAnalytics(['hunt-*']);

    $service = app(PanAnalyticsService::class);

    $service->increment('hunt-123', EventType::IMPRESSION);
    $service->increment('hunt-123', EventType::IMPRESSION);
    $service->increment('hunt-123', EventType::CLICK);

    $entry = DB::table('pan_analytics')->where('name', 'hunt-123')->first();

    expect($entry->impressions)->toBe(2)
        ->and($entry->clicks)->toBe(1);
});
