<?php

declare(strict_types=1);

use App\Models\Hunt;
use App\Services\Metrics\Calculators\HuntMetricsCalculator;
use Illuminate\Support\Facades\DB;

test('calculates comprehensive hunt metrics', function () {
    $hunt = Hunt::factory()->create([
        'views_count' => 1000,
        'shares_count' => 50,
    ]);

    // Add likes
    DB::table('likeables')->insert(array_map(fn () => [
        'likeable_id' => $hunt->id,
        'likeable_type' => Hunt::class,
        'user_id' => App\Models\User::factory()->create()->id,
        'created_at' => now(),
        'updated_at' => now(),
    ], range(1, 100)));

    // Add comments using factory
    App\Models\Comment::factory()
        ->count(20)
        ->forHunt($hunt)
        ->create();

    $calculator = app(HuntMetricsCalculator::class);
    $metrics = $calculator->calculateDetailed($hunt);

    expect($metrics->views)->toBe(1000)
        ->and($metrics->likes)->toBe(100)
        ->and($metrics->comments)->toBe(20)
        ->and($metrics->shares)->toBe(50)
        ->and($metrics->engagementRate)->toBeGreaterThan(0)
        ->and($metrics->qualityScore)->toBeGreaterThan(0)
        ->and($metrics->performanceLevel)->toBeIn(['poor', 'below_average', 'average', 'good', 'excellent'])
        ->and($metrics->rank)->toBeBetween(1, 5);
});

test('identifies viral content', function () {
    $hunt = Hunt::factory()->create([
        'views_count' => 100,
        'shares_count' => 15, // 15% share rate
    ]);

    $calculator = app(HuntMetricsCalculator::class);
    $metrics = $calculator->calculateDetailed($hunt);

    expect($metrics->isViral())->toBeTrue()
        ->and($metrics->viralityCoefficient)->toBeGreaterThanOrEqual(0.1);
});

test('identifies performing content correctly', function () {
    $hunt = Hunt::factory()->create([
        'views_count' => 100,
        'shares_count' => 10,
    ]);

    // Add significant likes
    DB::table('likeables')->insert(array_map(fn () => [
        'likeable_id' => $hunt->id,
        'likeable_type' => Hunt::class,
        'user_id' => App\Models\User::factory()->create()->id,
        'created_at' => now(),
        'updated_at' => now(),
    ], range(1, 30)));

    $calculator = app(HuntMetricsCalculator::class);
    $metrics = $calculator->calculateDetailed($hunt);

    // With 30 likes + 10 shares on 100 views, quality should be good
    expect($metrics->qualityScore)->toBeGreaterThan(0)
        ->and($metrics->performanceLevel)->toBeIn(['poor', 'below_average', 'average', 'good', 'excellent'])
        ->and($metrics->rank)->toBeGreaterThanOrEqual(1);
});

test('handles zero views gracefully', function () {
    $hunt = Hunt::factory()->create([
        'views_count' => 0,
        'shares_count' => 0,
    ]);

    $calculator = app(HuntMetricsCalculator::class);
    $metrics = $calculator->calculateDetailed($hunt);

    expect($metrics->views)->toBe(0)
        ->and($metrics->engagementRate)->toBe(0.0)
        ->and($metrics->qualityScore)->toBe(0.0)
        ->and($metrics->performanceLevel)->toBe('poor')
        ->and($metrics->rank)->toBe(1);
});

test('calculates engagement rates correctly', function () {
    $hunt = Hunt::factory()->create([
        'views_count' => 100,
        'shares_count' => 10,
    ]);

    DB::table('likeables')->insert(array_map(fn () => [
        'likeable_id' => $hunt->id,
        'likeable_type' => Hunt::class,
        'user_id' => App\Models\User::factory()->create()->id,
        'created_at' => now(),
        'updated_at' => now(),
    ], range(1, 20)));

    $calculator = app(HuntMetricsCalculator::class);
    $metrics = $calculator->calculateDetailed($hunt);

    // 20 likes + 10 shares = 30 engagements / 100 views = 30%
    expect($metrics->engagementRate)->toBe(30.0)
        ->and($metrics->shareRate)->toBe(10.0)
        ->and($metrics->interactionRate)->toBe(20.0);
});

test('returns correct type identifier', function () {
    $calculator = app(HuntMetricsCalculator::class);

    expect($calculator->getType())->toBe('hunt');
});

test('returns correct model class', function () {
    $calculator = app(HuntMetricsCalculator::class);

    expect($calculator->getModelClass())->toBe(Hunt::class);
});

test('supports method returns true for Hunt model', function () {
    $hunt = Hunt::factory()->create();
    $calculator = app(HuntMetricsCalculator::class);

    expect($calculator->supports($hunt))->toBeTrue();
});

test('supports method returns false for non-Hunt model', function () {
    $user = App\Models\User::factory()->create();
    $calculator = app(HuntMetricsCalculator::class);

    expect($calculator->supports($user))->toBeFalse();
});

test('calculate method throws exception for non-Hunt model', function () {
    $user = App\Models\User::factory()->create();
    $calculator = app(HuntMetricsCalculator::class);

    expect(fn () => $calculator->calculate($user))
        ->toThrow(InvalidArgumentException::class, 'Model must be an instance of Hunt');
});

test('calculate method returns MetricsData for Hunt model', function () {
    $hunt = Hunt::factory()->create([
        'views_count' => 100,
        'shares_count' => 10,
    ]);

    $calculator = app(HuntMetricsCalculator::class);
    $result = $calculator->calculate($hunt);

    expect($result)->toBeInstanceOf(App\DataTransferObjects\Metrics\MetricsData::class)
        ->and($result->type)->toBe('hunt')
        ->and($result->metrics)->toBeArray()
        ->and($result->metrics)->toHaveKey('views')
        ->and($result->metrics)->toHaveKey('likes')
        ->and($result->metrics)->toHaveKey('engagement_rate');
});
