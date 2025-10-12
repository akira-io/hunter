<?php

declare(strict_types=1);

use App\Models\Hunt;
use App\Models\User;
use App\Services\Metrics\MetricsPipeline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pipeline\Pipeline;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->pipeline = new MetricsPipeline(app(Pipeline::class));
});

it('processes hunt metrics successfully', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

    $metrics = $this->pipeline->process($hunt);

    expect($metrics)->toBeInstanceOf(App\DataTransferObjects\Metrics\HuntMetrics::class)
        ->and($metrics->views)->toBeInt()
        ->and($metrics->likes)->toBeInt()
        ->and($metrics->comments)->toBeInt()
        ->and($metrics->shares)->toBeInt()
        ->and($metrics->engagementRate)->toBeFloat()
        ->and($metrics->qualityScore)->toBeFloat();
});

it('throws exception when model is not a Hunt instance', function () {
    $user = User::factory()->create();

    expect(fn () => $this->pipeline->process($user))
        ->toThrow(InvalidArgumentException::class, 'Model must be an instance of Hunt');
});

it('returns correct pipeline stages', function () {
    $stages = $this->pipeline->getStages();

    expect($stages)->toBeArray()
        ->and($stages)->toHaveCount(3)
        ->and($stages)->toContain(App\Pipes\Metrics\ReachMetricsPipe::class)
        ->and($stages)->toContain(App\Pipes\Metrics\EngagementMetricsPipe::class)
        ->and($stages)->toContain(App\Pipes\Metrics\QualityMetricsPipe::class);
});

it('processes hunt with zero metrics', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'views_count' => 0,
        'shares_count' => 0,
    ]);

    $metrics = $this->pipeline->process($hunt);

    expect($metrics->views)->toBe(0)
        ->and($metrics->shares)->toBe(0);
});

it('processes hunt with high engagement', function () {
    $user = User::factory()->create();
    $liker = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'views_count' => 1000,
        'shares_count' => 50,
    ]);

    // Add some likes (user likes the hunt)
    $liker->like($hunt);

    $metrics = $this->pipeline->process($hunt);

    expect($metrics->views)->toBe(1000)
        ->and($metrics->likes)->toBeGreaterThanOrEqual(1)
        ->and($metrics->shares)->toBe(50);
});

it('calculates metrics for hunt with comments', function () {
    $user = User::factory()->create();
    $commenter = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'views_count' => 100,
    ]);

    // Add a comment (commenter comments on hunt)
    $commenter->comment($hunt, 'Great hunt!');

    $metrics = $this->pipeline->process($hunt);

    expect($metrics->comments)->toBe(1)
        ->and($metrics->commentRate)->toBeFloat();
});

it('creates context with correct initial values', function () {
    $user = User::factory()->create();
    $liker = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $user->id,
        'views_count' => 500,
        'shares_count' => 10,
    ]);

    $liker->like($hunt);

    $metrics = $this->pipeline->process($hunt);

    expect($metrics->views)->toBe(500)
        ->and($metrics->shares)->toBe(10)
        ->and($metrics->likes)->toBe(1);
});

it('returns all required metrics fields', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create(['owner_id' => $user->id]);

    $metrics = $this->pipeline->process($hunt);

    // Check all required fields exist
    expect($metrics)->toHaveProperty('views')
        ->and($metrics)->toHaveProperty('likes')
        ->and($metrics)->toHaveProperty('comments')
        ->and($metrics)->toHaveProperty('shares')
        ->and($metrics)->toHaveProperty('engagementRate')
        ->and($metrics)->toHaveProperty('interactionRate')
        ->and($metrics)->toHaveProperty('commentRate')
        ->and($metrics)->toHaveProperty('shareRate')
        ->and($metrics)->toHaveProperty('qualityScore')
        ->and($metrics)->toHaveProperty('viralityCoefficient')
        ->and($metrics)->toHaveProperty('avgEngagementPerView')
        ->and($metrics)->toHaveProperty('performanceLevel')
        ->and($metrics)->toHaveProperty('rank');
});

it('processes multiple hunts independently', function () {
    $user = User::factory()->create();

    $hunt1 = Hunt::factory()->create([
        'owner_id' => $user->id,
        'views_count' => 100,
    ]);

    $hunt2 = Hunt::factory()->create([
        'owner_id' => $user->id,
        'views_count' => 200,
    ]);

    $metrics1 = $this->pipeline->process($hunt1);
    $metrics2 = $this->pipeline->process($hunt2);

    expect($metrics1->views)->toBe(100)
        ->and($metrics2->views)->toBe(200);
});
