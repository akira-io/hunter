<?php

declare(strict_types=1);

use App\DataTransferObjects\Metrics\MetricsContext;
use App\Models\Hunt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates metrics context with all properties', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

    $context = new MetricsContext(
        model: $hunt,
        views: 1000,
        likes: 100,
        comments: 50,
        shares: 25,
        calculated: ['engagement_rate' => 17.5]
    );

    expect($context->model)->toBe($hunt)
        ->and($context->views)->toBe(1000)
        ->and($context->likes)->toBe(100)
        ->and($context->comments)->toBe(50)
        ->and($context->shares)->toBe(25)
        ->and($context->calculated)->toBe(['engagement_rate' => 17.5]);
});

it('creates context with empty calculated array by default', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

    $context = new MetricsContext(
        model: $hunt,
        views: 100,
        likes: 10,
        comments: 5,
        shares: 2
    );

    expect($context->calculated)->toBe([]);
});

it('withCalculated creates new immutable instance', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

    $original = new MetricsContext(
        model: $hunt,
        views: 1000,
        likes: 100,
        comments: 50,
        shares: 25,
        calculated: ['engagement_rate' => 17.5]
    );

    $new = $original->withCalculated([
        'interaction_rate' => 15.0,
        'quality_score' => 75.0,
    ]);

    expect($original->calculated)->toBe(['engagement_rate' => 17.5])
        ->and($new->calculated)->toBe([
            'engagement_rate' => 17.5,
            'interaction_rate' => 15.0,
            'quality_score' => 75.0,
        ])
        ->and($new->model)->toBe($hunt)
        ->and($new->views)->toBe(1000)
        ->and($new->likes)->toBe(100);
});

it('withCalculated merges with existing metrics', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

    $context = new MetricsContext(
        model: $hunt,
        views: 1000,
        likes: 100,
        comments: 50,
        shares: 25,
        calculated: [
            'metric1' => 10,
            'metric2' => 20,
        ]
    );

    $updated = $context->withCalculated([
        'metric3' => 30,
        'metric4' => 40,
    ]);

    expect($updated->calculated)->toBe([
        'metric1' => 10,
        'metric2' => 20,
        'metric3' => 30,
        'metric4' => 40,
    ]);
});

it('withCalculated overwrites existing keys', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

    $context = new MetricsContext(
        model: $hunt,
        views: 1000,
        likes: 100,
        comments: 50,
        shares: 25,
        calculated: [
            'engagement_rate' => 17.5,
            'quality_score' => 75.0,
        ]
    );

    $updated = $context->withCalculated([
        'engagement_rate' => 20.0,
        'new_metric' => 99.9,
    ]);

    expect($updated->calculated)->toBe([
        'engagement_rate' => 20.0,
        'quality_score' => 75.0,
        'new_metric' => 99.9,
    ]);
});

it('get returns calculated metric value', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

    $context = new MetricsContext(
        model: $hunt,
        views: 1000,
        likes: 100,
        comments: 50,
        shares: 25,
        calculated: [
            'engagement_rate' => 17.5,
            'quality_score' => 75.0,
        ]
    );

    expect($context->get('engagement_rate'))->toBe(17.5)
        ->and($context->get('quality_score'))->toBe(75.0);
});

it('get returns default when key not found', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

    $context = new MetricsContext(
        model: $hunt,
        views: 1000,
        likes: 100,
        comments: 50,
        shares: 25,
        calculated: []
    );

    expect($context->get('non_existent'))->toBeNull()
        ->and($context->get('non_existent', 0))->toBe(0)
        ->and($context->get('non_existent', 'default'))->toBe('default');
});

it('has returns true when key exists', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

    $context = new MetricsContext(
        model: $hunt,
        views: 1000,
        likes: 100,
        comments: 50,
        shares: 25,
        calculated: [
            'engagement_rate' => 17.5,
            'null_value' => null,
        ]
    );

    expect($context->has('engagement_rate'))->toBeTrue()
        ->and($context->has('null_value'))->toBeTrue();
});

it('has returns false when key does not exist', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

    $context = new MetricsContext(
        model: $hunt,
        views: 1000,
        likes: 100,
        comments: 50,
        shares: 25,
        calculated: ['engagement_rate' => 17.5]
    );

    expect($context->has('non_existent_key'))->toBeFalse()
        ->and($context->has('quality_score'))->toBeFalse();
});

it('maintains immutability across chained withCalculated calls', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

    $context1 = new MetricsContext(
        model: $hunt,
        views: 1000,
        likes: 100,
        comments: 50,
        shares: 25
    );

    $context2 = $context1->withCalculated(['metric1' => 1]);
    $context3 = $context2->withCalculated(['metric2' => 2]);
    $context4 = $context3->withCalculated(['metric3' => 3]);

    expect($context1)->not->toBe($context2)
        ->and($context2)->not->toBe($context3)
        ->and($context3)->not->toBe($context4)
        ->and($context1->calculated)->toBe([])
        ->and($context2->calculated)->toBe(['metric1' => 1])
        ->and($context3->calculated)->toBe(['metric1' => 1, 'metric2' => 2])
        ->and($context4->calculated)->toBe(['metric1' => 1, 'metric2' => 2, 'metric3' => 3]);
});
