<?php

declare(strict_types=1);

use App\DataTransferObjects\Metrics\HuntMetrics;

it('creates hunt metrics with all properties', function () {
    $metrics = new HuntMetrics(
        views: 1000,
        likes: 100,
        comments: 50,
        shares: 25,
        engagementRate: 17.5,
        interactionRate: 15.0,
        commentRate: 5.0,
        shareRate: 2.5,
        qualityScore: 75.5,
        viralityCoefficient: 0.15,
        avgEngagementPerView: 0.175,
        performanceLevel: 'excellent',
        rank: 5
    );

    expect($metrics->views)->toBe(1000)
        ->and($metrics->likes)->toBe(100)
        ->and($metrics->comments)->toBe(50)
        ->and($metrics->shares)->toBe(25)
        ->and($metrics->engagementRate)->toBe(17.5)
        ->and($metrics->qualityScore)->toBe(75.5)
        ->and($metrics->performanceLevel)->toBe('excellent');
});

it('gets total engagements summing all interactions', function () {
    $metrics = new HuntMetrics(
        views: 1000,
        likes: 100,
        comments: 50,
        shares: 25,
        engagementRate: 17.5,
        interactionRate: 15.0,
        commentRate: 5.0,
        shareRate: 2.5,
        qualityScore: 75.5,
        viralityCoefficient: 0.15,
        avgEngagementPerView: 0.175,
        performanceLevel: 'excellent',
        rank: 5
    );

    expect($metrics->getTotalEngagements())->toBe(175); // 100 + 50 + 25
});

it('gets total interactions excluding shares', function () {
    $metrics = new HuntMetrics(
        views: 1000,
        likes: 100,
        comments: 50,
        shares: 25,
        engagementRate: 17.5,
        interactionRate: 15.0,
        commentRate: 5.0,
        shareRate: 2.5,
        qualityScore: 75.5,
        viralityCoefficient: 0.15,
        avgEngagementPerView: 0.175,
        performanceLevel: 'excellent',
        rank: 5
    );

    expect($metrics->getTotalInteractions())->toBe(150); // 100 + 50
});

it('determines performance as good when quality score > 50', function () {
    $metrics = new HuntMetrics(
        views: 1000,
        likes: 100,
        comments: 50,
        shares: 25,
        engagementRate: 17.5,
        interactionRate: 15.0,
        commentRate: 5.0,
        shareRate: 2.5,
        qualityScore: 75.0,
        viralityCoefficient: 0.15,
        avgEngagementPerView: 0.175,
        performanceLevel: 'excellent',
        rank: 5
    );

    expect($metrics->isPerformingWell())->toBeTrue();
});

it('determines performance as good when quality score equals 50', function () {
    $metrics = new HuntMetrics(
        views: 1000,
        likes: 100,
        comments: 50,
        shares: 25,
        engagementRate: 17.5,
        interactionRate: 15.0,
        commentRate: 5.0,
        shareRate: 2.5,
        qualityScore: 50.0,
        viralityCoefficient: 0.15,
        avgEngagementPerView: 0.175,
        performanceLevel: 'good',
        rank: 10
    );

    expect($metrics->isPerformingWell())->toBeTrue();
});

it('determines performance as poor when quality score < 50', function () {
    $metrics = new HuntMetrics(
        views: 100,
        likes: 10,
        comments: 5,
        shares: 2,
        engagementRate: 17.0,
        interactionRate: 15.0,
        commentRate: 5.0,
        shareRate: 2.0,
        qualityScore: 35.0,
        viralityCoefficient: 0.05,
        avgEngagementPerView: 0.17,
        performanceLevel: 'average',
        rank: 25
    );

    expect($metrics->isPerformingWell())->toBeFalse();
});

it('detects viral when virality coefficient ≥ 0.1', function () {
    $metrics = new HuntMetrics(
        views: 1000,
        likes: 100,
        comments: 50,
        shares: 150,
        engagementRate: 30.0,
        interactionRate: 15.0,
        commentRate: 5.0,
        shareRate: 15.0,
        qualityScore: 75.0,
        viralityCoefficient: 0.15,
        avgEngagementPerView: 0.30,
        performanceLevel: 'viral',
        rank: 1
    );

    expect($metrics->isViral())->toBeTrue();
});

it('detects non-viral when virality coefficient < 0.1', function () {
    $metrics = new HuntMetrics(
        views: 1000,
        likes: 100,
        comments: 50,
        shares: 25,
        engagementRate: 17.5,
        interactionRate: 15.0,
        commentRate: 5.0,
        shareRate: 2.5,
        qualityScore: 75.0,
        viralityCoefficient: 0.05,
        avgEngagementPerView: 0.175,
        performanceLevel: 'excellent',
        rank: 5
    );

    expect($metrics->isViral())->toBeFalse();
});

it('converts to array including all metrics', function () {
    $metrics = new HuntMetrics(
        views: 1000,
        likes: 100,
        comments: 50,
        shares: 25,
        engagementRate: 17.5,
        interactionRate: 15.0,
        commentRate: 5.0,
        shareRate: 2.5,
        qualityScore: 75.0,
        viralityCoefficient: 0.15,
        avgEngagementPerView: 0.175,
        performanceLevel: 'excellent',
        rank: 5
    );

    $array = $metrics->toArray();

    expect($array)
        ->toBeArray()
        ->toHaveKeys([
            'views',
            'likes',
            'comments',
            'shares',
            'total_engagements',
            'engagement_rate',
            'interaction_rate',
            'comment_rate',
            'share_rate',
            'quality_score',
            'virality_coefficient',
            'avg_engagement_per_view',
            'performance_level',
            'rank',
            'is_performing_well',
            'is_viral',
        ])
        ->and($array['views'])->toBe(1000)
        ->and($array['total_engagements'])->toBe(175)
        ->and($array['is_performing_well'])->toBeTrue()
        ->and($array['is_viral'])->toBeTrue();
});

it('handles non-viral low-performing metrics correctly', function () {
    $metrics = new HuntMetrics(
        views: 50,
        likes: 5,
        comments: 2,
        shares: 1,
        engagementRate: 16.0,
        interactionRate: 14.0,
        commentRate: 4.0,
        shareRate: 2.0,
        qualityScore: 20.0,
        viralityCoefficient: 0.02,
        avgEngagementPerView: 0.16,
        performanceLevel: 'poor',
        rank: 100
    );

    $array = $metrics->toArray();

    expect($array['is_performing_well'])->toBeFalse()
        ->and($array['is_viral'])->toBeFalse()
        ->and($array['performance_level'])->toBe('poor')
        ->and($array['rank'])->toBe(100);
});

it('handles zero values gracefully', function () {
    $metrics = new HuntMetrics(
        views: 0,
        likes: 0,
        comments: 0,
        shares: 0,
        engagementRate: 0.0,
        interactionRate: 0.0,
        commentRate: 0.0,
        shareRate: 0.0,
        qualityScore: 0.0,
        viralityCoefficient: 0.0,
        avgEngagementPerView: 0.0,
        performanceLevel: 'none',
        rank: 0
    );

    expect($metrics->getTotalEngagements())->toBe(0)
        ->and($metrics->getTotalInteractions())->toBe(0)
        ->and($metrics->isPerformingWell())->toBeFalse()
        ->and($metrics->isViral())->toBeFalse();
});
