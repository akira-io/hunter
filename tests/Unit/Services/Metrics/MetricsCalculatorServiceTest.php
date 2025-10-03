<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Models\Hunt;
use App\Models\User;
use App\Services\Metrics\MetricsCalculatorService;

beforeEach(function () {
    $this->service = app(MetricsCalculatorService::class);
});

it('calculates metrics for hunt model', function () {
    $owner = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $owner->id,
        'views_count' => 100,
        'shares_count' => 5,
    ]);

    $user = User::factory()->create();
    $user->like($hunt);

    $hunt->comments()->create([
        'commenter_id' => $user->id,
        'content' => 'Great hunt!',
    ]);

    $metrics = $this->service->calculate($hunt);

    expect($metrics->type)->toBe('hunt')
        ->and($metrics->get('views'))->toBe(100)
        ->and($metrics->get('likes'))->toBe(1)
        ->and($metrics->get('comments'))->toBe(1)
        ->and($metrics->get('shares'))->toBe(5);
});

it('throws exception for unsupported model', function () {
    $user = User::factory()->make();

    $this->service->calculate($user);
})->throws(InvalidArgumentException::class, 'No metrics calculator found for model');

// it('throws exception for comment model')->skip(' Create Comment factory or update test', function () {
//     $owner = User::factory()->create();
//     $hunt = Hunt::factory()->create(['owner_id' => $owner->id]);
//
//     $comment = Comment::factory()->create([
//         'owner_id' => $owner->id,
//         'commentable_id' => $hunt->id,
//         'commentable_type' => Hunt::class,
//     ]);
//
//     $this->service->calculate($comment);
// })->throws(InvalidArgumentException::class, 'No metrics calculator found for model');

it('finds correct calculator for hunt model', function () {
    $hunt = Hunt::factory()->create([
        'views_count' => 50,
        'shares_count' => 2,
    ]);

    $metrics = $this->service->calculate($hunt);

    expect($metrics->type)->toBe('hunt');
});

it('handles hunt with no engagements', function () {
    $hunt = Hunt::factory()->create([
        'views_count' => 1000,
        'shares_count' => 0,
    ]);

    $metrics = $this->service->calculate($hunt);

    expect($metrics->get('total_engagements'))->toBe(0)
        ->and($metrics->get('engagement_rate'))->toBe(0.0);
});

it('handles hunt with high engagement', function () {
    $owner = User::factory()->create();
    $hunt = Hunt::factory()->create([
        'owner_id' => $owner->id,
        'views_count' => 100,
        'shares_count' => 50,
    ]);

    // Add 30 likes
    $users = User::factory()->count(30)->create();
    foreach ($users as $user) {
        $user->like($hunt);
    }

    // Add 20 comments
    for ($i = 0; $i < 20; $i++) {
        $hunt->comments()->create([
            'commenter_id' => $users->random()->id,
            'content' => "Comment $i",
        ]);
    }

    $metrics = $this->service->calculate($hunt);

    // Total: 30 + 20 + 50 = 100
    // Rate: (100 / 100) * 100 = 100%
    expect($metrics->get('total_engagements'))->toBe(100)
        ->and($metrics->get('engagement_rate'))->toBe(100.0);
});

it('is a singleton service', function () {
    $service1 = app(MetricsCalculatorService::class);
    $service2 = app(MetricsCalculatorService::class);

    expect($service1)->toBe($service2);
});
