<?php

declare(strict_types=1);

use App\DataTransferObjects\Metrics\MetricsData;

it('creates metrics data with type and metrics', function () {
    $metricsData = new MetricsData(
        type: 'hunt',
        metrics: [
            'views' => 100,
            'likes' => 50,
            'comments' => 10,
        ]
    );

    expect($metricsData->type)->toBe('hunt')
        ->and($metricsData->metrics)->toBe([
            'views' => 100,
            'likes' => 50,
            'comments' => 10,
        ]);
});

it('gets specific metric value', function () {
    $metricsData = new MetricsData(
        type: 'hunt',
        metrics: [
            'views' => 100,
            'likes' => 50,
        ]
    );

    expect($metricsData->get('views'))->toBe(100)
        ->and($metricsData->get('likes'))->toBe(50);
});

it('returns default value when metric does not exist', function () {
    $metricsData = new MetricsData(
        type: 'hunt',
        metrics: []
    );

    expect($metricsData->get('views'))->toBe(0)
        ->and($metricsData->get('likes', 10))->toBe(10)
        ->and($metricsData->get('shares', 'N/A'))->toBe('N/A');
});

it('converts to array', function () {
    $metricsData = new MetricsData(
        type: 'hunt',
        metrics: [
            'views' => 100,
            'likes' => 50,
        ]
    );

    expect($metricsData->toArray())->toBe([
        'type' => 'hunt',
        'metrics' => [
            'views' => 100,
            'likes' => 50,
        ],
    ]);
});

it('handles float values', function () {
    $metricsData = new MetricsData(
        type: 'hunt',
        metrics: [
            'engagement_rate' => 25.5,
            'avg_engagement' => 1.23,
        ]
    );

    expect($metricsData->get('engagement_rate'))->toBe(25.5)
        ->and($metricsData->get('avg_engagement'))->toBe(1.23);
});

it('handles string values', function () {
    $metricsData = new MetricsData(
        type: 'hunt',
        metrics: [
            'status' => 'active',
            'category' => 'technology',
        ]
    );

    expect($metricsData->get('status'))->toBe('active')
        ->and($metricsData->get('category'))->toBe('technology');
});
