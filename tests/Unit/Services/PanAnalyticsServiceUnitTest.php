<?php

declare(strict_types=1);

use App\Services\Pan\PanAnalyticsService;
use Illuminate\Support\Facades\DB;
use Pan\Enums\EventType;
use Pan\PanConfiguration;

beforeEach(function () {
    DB::table('pan_analytics')->truncate();
    PanConfiguration::reset();
});

it('inserts analytics when no allowedAnalytics configured', function () {
    // Do not set allowed analytics - default should allow all
    $service = app(PanAnalyticsService::class);

    $service->increment('random-1', EventType::IMPRESSION);

    expect(DB::table('pan_analytics')->where('name', 'random-1')->count())->toBe(1);
});

it('returns Analytic objects from all()', function () {
    $service = app(PanAnalyticsService::class);

    DB::table('pan_analytics')->insert(['name' => 'a', 'impressions' => 3, 'hovers' => 0, 'clicks' => 1]);

    $all = $service->all();

    expect($all)->toBeArray();
    expect($all[0])->toBeInstanceOf(Pan\ValueObjects\Analytic::class);
    expect($all[0]->impressions)->toBe(3);
});

it('flush truncates the table', function () {
    $service = app(PanAnalyticsService::class);

    DB::table('pan_analytics')->insert(['name' => 'to-flush', 'impressions' => 1]);

    expect(DB::table('pan_analytics')->count())->toBe(1);

    $service->flush();

    expect(DB::table('pan_analytics')->count())->toBe(0);
});
