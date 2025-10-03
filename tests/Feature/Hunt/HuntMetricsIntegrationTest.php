<?php

declare(strict_types=1);

use App\Models\Hunt;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

// it('includes metrics in hunt resource when viewing hunt detail')->skip(' Update test for Inertia response', function () {
//     $owner = User::factory()->create();
//     $user = User::factory()->create();
//
//     $hunt = Hunt::factory()->create([
//         'owner_id' => $owner->id,
//         'views_count' => 100,
//         'shares_count' => 10,
//     ]);
//
//     // Add likes
//     $likers = User::factory()->count(5)->create();
//     foreach ($likers as $liker) {
//         $liker->like($hunt);
//     }
//
//     // Add comments
//     $hunt->comments()->create([
//         'commenter_id' => $user->id,
//         'content' => 'Great hunt!',
//     ]);
//
//     $response = actingAs($user)->get(route('hunts.show', $hunt));
//
//     $response->assertSuccessful();
//
//     expect($response->viewData('hunt'))
//         ->toBeObject()
//         ->toHaveProperty('metrics')
//         ->and($response->viewData('hunt')->metrics['type'])->toBe('hunt')
//         ->and($response->viewData('hunt')->metrics['metrics']['views'])->toBe(101) // +1 from viewing
//         ->and($response->viewData('hunt')->metrics['metrics']['likes'])->toBe(5)
//         ->and($response->viewData('hunt')->metrics['metrics']['comments'])->toBe(1)
//         ->and($response->viewData('hunt')->metrics['metrics']['shares'])->toBe(10);
// });

// it('includes metrics in hunt resource when listing hunts')->skip(' Update test for Inertia response', function () {
//     $owner = User::factory()->create();
//     $user = User::factory()->create();
//
//     $hunt = Hunt::factory()->create([
//         'owner_id' => $owner->id,
//         'views_count' => 50,
//         'shares_count' => 5,
//     ]);
//
//     $user->like($hunt);
//
//     $response = actingAs($user)->get(route('hunts.index'));
//
//     $response->assertSuccessful();
//
//     $hunts = $response->viewData('hunts')->items();
//
//     expect($hunts)->toHaveCount(1)
//         ->and($hunts[0])->toHaveProperty('metrics')
//         ->and($hunts[0]->metrics['type'])->toBe('hunt')
//         ->and($hunts[0]->metrics['metrics']['views'])->toBe(50)
//         ->and($hunts[0]->metrics['metrics']['likes'])->toBe(1);
// });

// it('calculates engagement metrics correctly in response')->skip(' Update test for Inertia response', function () {
//     $owner = User::factory()->create();
//     $user = User::factory()->create();
//
//     $hunt = Hunt::factory()->create([
//         'owner_id' => $owner->id,
//         'views_count' => 100,
//         'shares_count' => 10,
//     ]);
//
//     // 20 likes + 5 comments + 10 shares = 35 engagements
//     $likers = User::factory()->count(20)->create();
//     foreach ($likers as $liker) {
//         $liker->like($hunt);
//     }
//
//     for ($i = 0; $i < 5; $i++) {
//         $hunt->comments()->create([
//             'commenter_id' => $user->id,
//             'content' => "Comment $i",
//         ]);
//     }
//
//     $response = actingAs($user)->get(route('hunts.show', $hunt));
//
//     $metrics = $response->viewData('hunt')->metrics['metrics'];
//
//     // Total engagements: 20 + 5 + 10 = 35
//     // Engagement rate: (35 / 101) * 100 = 34.65%
//     // Interaction rate: (25 / 101) * 100 = 24.75%
//     expect($metrics['total_engagements'])->toBe(35)
//         ->and($metrics['engagement_rate'])->toBe(34.65)
//         ->and($metrics['interaction_rate'])->toBe(24.75);
// });

it('increments view count when viewing hunt detail', function () {
    $owner = User::factory()->create();
    $user = User::factory()->create();

    $hunt = Hunt::factory()->create([
        'owner_id' => $owner->id,
        'views_count' => 10,
        'shares_count' => 0,
    ]);

    actingAs($user)->get(route('hunts.show', $hunt));

    $hunt->refresh();

    expect($hunt->views_count)->toBe(11);
});

// it('returns correct metrics for hunt with no engagement')->skip(' Update test for Inertia response', function () {
//     $owner = User::factory()->create();
//     $user = User::factory()->create();
//
//     $hunt = Hunt::factory()->create([
//         'owner_id' => $owner->id,
//         'views_count' => 100,
//         'shares_count' => 0,
//     ]);
//
//     $response = actingAs($user)->get(route('hunts.show', $hunt));
//
//     $metrics = $response->viewData('hunt')->metrics['metrics'];
//
//     expect($metrics['total_engagements'])->toBe(0)
//         ->and($metrics['engagement_rate'])->toBe(0.0)
//         ->and($metrics['interaction_rate'])->toBe(0.0)
//         ->and($metrics['avg_engagement_per_view'])->toBe(0.0);
// });

// it('calculates metrics for multiple hunts correctly')->skip(' Update test for Inertia response', function () {
//     $owner = User::factory()->create();
//     $user = User::factory()->create();
//
//     $hunt1 = Hunt::factory()->create([
//         'owner_id' => $owner->id,
//         'views_count' => 100,
//         'shares_count' => 5,
//     ]);
//
//     $hunt2 = Hunt::factory()->create([
//         'owner_id' => $owner->id,
//         'views_count' => 200,
//         'shares_count' => 10,
//     ]);
//
//     // Hunt 1: 3 likes
//     $likers1 = User::factory()->count(3)->create();
//     foreach ($likers1 as $liker) {
//         $liker->like($hunt1);
//     }
//
//     // Hunt 2: 7 likes
//     $likers2 = User::factory()->count(7)->create();
//     foreach ($likers2 as $liker) {
//         $liker->like($hunt2);
//     }
//
//     $response = actingAs($user)->get(route('hunts.index'));
//
//     $hunts = $response->viewData('hunts')->items();
//
//     expect($hunts)->toHaveCount(2)
//         ->and($hunts[0]->metrics['metrics']['likes'])->toBe(7)
//         ->and($hunts[0]->metrics['metrics']['views'])->toBe(200)
//         ->and($hunts[1]->metrics['metrics']['likes'])->toBe(3)
//         ->and($hunts[1]->metrics['metrics']['views'])->toBe(100);
// });
