<?php

declare(strict_types=1);

use App\Actions\Hunt\GetHuntsAction;
use App\Models\Hunt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user latest hunt appears first in hunts feed', function () {
    $authenticatedUser = User::factory()->create();
    $otherUser = User::factory()->create();

    // Create older hunts from authenticated user (should NOT appear in feed)
    Hunt::factory()->create([
        'owner_id' => $authenticatedUser->id,
        'created_at' => now()->subDays(5),
    ]);

    Hunt::factory()->create([
        'owner_id' => $authenticatedUser->id,
        'created_at' => now()->subDays(4),
    ]);

    // Create some hunts from other users with more recent timestamps
    $recentHuntFromOther = Hunt::factory()->create([
        'owner_id' => $otherUser->id,
        'created_at' => now()->subMinutes(1),
    ]);

    // Create the latest hunt from authenticated user (should be FIRST in feed)
    $latestAuthUserHunt = Hunt::factory()->create([
        'owner_id' => $authenticatedUser->id,
        'created_at' => now()->subDays(3),
    ]);

    // Get hunts feed
    $getHuntsAction = app(GetHuntsAction::class);
    $hunts = $getHuntsAction->handle($authenticatedUser);

    // Get hunt IDs in order
    $huntIds = $hunts->pluck('id')->toArray();

    // Assert that authenticated user's latest hunt is first
    expect($huntIds[0])->toBe($latestAuthUserHunt->id);

    // Assert that the recent hunt from other user is in the feed (but not first)
    expect($huntIds)->toContain($recentHuntFromOther->id);

    // Assert that only the latest hunt from authenticated user appears
    expect($huntIds)->toHaveCount(2); // Only latest from auth user + one from other user
});

test('when authenticated user has no hunts, feed shows other users hunts', function () {
    $authenticatedUser = User::factory()->create();
    $otherUser = User::factory()->create();

    // Create hunts from other user
    $hunt1 = Hunt::factory()->create([
        'owner_id' => $otherUser->id,
        'created_at' => now()->subHours(1),
    ]);

    $hunt2 = Hunt::factory()->create([
        'owner_id' => $otherUser->id,
        'created_at' => now()->subHours(2),
    ]);

    // Get hunts feed
    $getHuntsAction = app(GetHuntsAction::class);
    $hunts = $getHuntsAction->handle($authenticatedUser);

    // Get hunt IDs
    $huntIds = $hunts->pluck('id')->toArray();

    // Assert both hunts from other user appear
    expect($huntIds)->toContain($hunt1->id);
    expect($huntIds)->toContain($hunt2->id);
});

test('latest hunt from authenticated user appears first even if much older than others', function () {
    $authenticatedUser = User::factory()->create();
    $otherUser = User::factory()->create();

    // Create very recent hunt from other user
    $veryRecentHunt = Hunt::factory()->create([
        'owner_id' => $otherUser->id,
        'created_at' => now(),
    ]);

    // Create older hunt from authenticated user
    $oldAuthUserHunt = Hunt::factory()->create([
        'owner_id' => $authenticatedUser->id,
        'created_at' => now()->subWeeks(2),
    ]);

    // Get hunts feed
    $getHuntsAction = app(GetHuntsAction::class);
    $hunts = $getHuntsAction->handle($authenticatedUser);

    // Get hunt IDs in order
    $huntIds = $hunts->pluck('id')->toArray();

    // Assert that authenticated user's hunt is first, even though it's 2 weeks old
    expect($huntIds[0])->toBe($oldAuthUserHunt->id);

    // Assert that the very recent hunt from other user is second
    expect($huntIds[1])->toBe($veryRecentHunt->id);
});
