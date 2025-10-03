<?php

declare(strict_types=1);

use App\Models\Hunt;
use App\Models\User;
use App\Services\Metrics\Calculators\HuntMetricsCalculator;

beforeEach(function () {
    $this->calculator = new HuntMetricsCalculator();
});

it('returns correct type', function () {
    expect($this->calculator->getType())->toBe('hunt');
});

it('returns correct model class', function () {
    expect($this->calculator->getModelClass())->toBe(Hunt::class);
});

it('supports hunt model', function () {
    $hunt = Hunt::factory()->make();

    expect($this->calculator->supports($hunt))->toBeTrue();
});

it('does not support non-hunt model', function () {
    $user = User::factory()->make();

    expect($this->calculator->supports($user))->toBeFalse();
});

it('throws exception when calculating metrics for non-hunt model', function () {
    $user = User::factory()->make();

    $this->calculator->calculate($user);
})->throws(InvalidArgumentException::class, 'Model must be an instance of Hunt');

it('calculates metrics with zero values', function () {
    $hunt = Hunt::factory()->create([
        'views_count' => 0,
        'shares_count' => 0,
    ]);

    $metrics = $this->calculator->calculate($hunt);

    expect($metrics->get('views'))->toBe(0)
        ->and($metrics->get('likes'))->toBe(0)
        ->and($metrics->get('comments'))->toBe(0)
        ->and($metrics->get('shares'))->toBe(0)
        ->and($metrics->get('total_engagements'))->toBe(0)
        ->and($metrics->get('engagement_rate'))->toBe(0.0)
        ->and($metrics->get('interaction_rate'))->toBe(0.0)
        ->and($metrics->get('avg_engagement_per_view'))->toBe(0.0);
});

it('calculates basic metrics correctly', function () {
    $owner = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $owner->id,
        'views_count' => 100,
        'shares_count' => 10,
    ]);

    // Add likes
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user1->like($hunt);
    $user2->like($hunt);

    // Add comments
    $hunt->comments()->create([
        'commenter_id' => $user1->id,
        'content' => 'Great hunt!',
    ]);

    $metrics = $this->calculator->calculate($hunt);

    expect($metrics->get('views'))->toBe(100)
        ->and($metrics->get('likes'))->toBe(2)
        ->and($metrics->get('comments'))->toBe(1)
        ->and($metrics->get('shares'))->toBe(10);
});

it('calculates engagement rate correctly', function () {
    $owner = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $owner->id,
        'views_count' => 100,
        'shares_count' => 10,
    ]);

    // 5 likes + 3 comments + 10 shares = 18 engagements
    $users = User::factory()->count(5)->create();
    foreach ($users as $user) {
        $user->like($hunt);
    }

    for ($i = 0; $i < 3; $i++) {
        $hunt->comments()->create([
            'commenter_id' => $users[0]->id,
            'content' => "Comment $i",
        ]);
    }

    $metrics = $this->calculator->calculate($hunt);

    // Total engagements: 5 + 3 + 10 = 18
    // Engagement rate: (18 / 100) * 100 = 18%
    expect($metrics->get('total_engagements'))->toBe(18)
        ->and($metrics->get('engagement_rate'))->toBe(18.0);
});

it('calculates interaction rate correctly', function () {
    $owner = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $owner->id,
        'views_count' => 200,
        'shares_count' => 0,
    ]);

    // 10 likes + 5 comments = 15 interactions (shares not counted)
    $users = User::factory()->count(10)->create();
    foreach ($users as $user) {
        $user->like($hunt);
    }

    for ($i = 0; $i < 5; $i++) {
        $hunt->comments()->create([
            'commenter_id' => $users[0]->id,
            'content' => "Comment $i",
        ]);
    }

    $metrics = $this->calculator->calculate($hunt);

    // Interaction rate: (15 / 200) * 100 = 7.5%
    expect($metrics->get('interaction_rate'))->toBe(7.5);
});

it('calculates average engagement per view correctly', function () {
    $owner = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $owner->id,
        'views_count' => 50,
        'shares_count' => 5,
    ]);

    // 3 likes + 2 comments + 5 shares = 10 engagements
    $users = User::factory()->count(3)->create();
    foreach ($users as $user) {
        $user->like($hunt);
    }

    for ($i = 0; $i < 2; $i++) {
        $hunt->comments()->create([
            'commenter_id' => $users[0]->id,
            'content' => "Comment $i",
        ]);
    }

    $metrics = $this->calculator->calculate($hunt);

    // Avg: 10 / 50 = 0.2
    expect($metrics->get('avg_engagement_per_view'))->toBe(0.2);
});

it('handles high engagement scenarios', function () {
    $owner = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $owner->id,
        'views_count' => 1000,
        'shares_count' => 250,
    ]);

    // 400 likes + 100 comments + 250 shares = 750 engagements
    $users = User::factory()->count(400)->create();

    // Create likes
    foreach ($users as $user) {
        $user->like($hunt);
    }

    // Create 100 comments
    for ($i = 0; $i < 100; $i++) {
        $hunt->comments()->create([
            'commenter_id' => $users->random()->id,
            'content' => "Comment $i",
        ]);
    }

    $metrics = $this->calculator->calculate($hunt);

    // Engagement rate: (750 / 1000) * 100 = 75%
    expect($metrics->get('total_engagements'))->toBe(750)
        ->and($metrics->get('engagement_rate'))->toBe(75.0);
});

it('returns metrics data with correct type', function () {
    $hunt = Hunt::factory()->create([
        'views_count' => 10,
        'shares_count' => 1,
    ]);

    $metrics = $this->calculator->calculate($hunt);

    expect($metrics->type)->toBe('hunt');
});
