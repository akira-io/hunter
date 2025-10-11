<?php

declare(strict_types=1);

use App\Models\Hunt;
use App\Models\User;

beforeEach(function () {
    $this->user = actingAsAuthUser();
});

it('shows only the latest hunt from the authenticated user in the feed', function () {
    // Create multiple hunts for the authenticated user
    $oldestHunt = Hunt::factory()->create([
        'owner_id' => $this->user->id,
        'content' => 'Oldest hunt from user',
        'created_at' => now()->subHours(3),
    ]);

    $middleHunt = Hunt::factory()->create([
        'owner_id' => $this->user->id,
        'content' => 'Middle hunt from user',
        'created_at' => now()->subHours(2),
    ]);

    $latestHunt = Hunt::factory()->create([
        'owner_id' => $this->user->id,
        'content' => 'Latest hunt from user',
        'created_at' => now()->subHour(),
    ]);

    $response = $this->get(route('hunts.index'));

    $response->assertSuccessful();

    // Get the hunts data from the response
    $hunts = $response->viewData('page')['props']['hunts']['data'];

    // Filter hunts from the authenticated user
    $userHunts = collect($hunts)->filter(fn ($hunt) => $hunt['owner']['id'] === $this->user->id);

    // Assert only one hunt from the user is shown
    expect($userHunts)->toHaveCount(1);

    // Assert it's the latest hunt
    expect($userHunts->first()['content'])->toBe('Latest hunt from user');
});

it('shows all hunts from other users in the feed', function () {
    // Create hunts from other users
    $otherUser1 = User::factory()->create();
    $otherUser2 = User::factory()->create();

    $hunt1 = Hunt::factory()->create([
        'owner_id' => $otherUser1->id,
        'content' => 'Hunt from user 1',
        'created_at' => now()->subHours(2),
    ]);

    $hunt2 = Hunt::factory()->create([
        'owner_id' => $otherUser1->id,
        'content' => 'Another hunt from user 1',
        'created_at' => now()->subHour(),
    ]);

    $hunt3 = Hunt::factory()->create([
        'owner_id' => $otherUser2->id,
        'content' => 'Hunt from user 2',
        'created_at' => now()->subMinutes(30),
    ]);

    $response = $this->get(route('hunts.index'));

    $response->assertSuccessful();

    $hunts = $response->viewData('page')['props']['hunts']['data'];

    // Filter hunts from other users
    $otherUsersHunts = collect($hunts)->filter(
        fn ($hunt) => $hunt['owner']['id'] !== $this->user->id
    );

    // Assert all hunts from other users are shown
    expect($otherUsersHunts)->toHaveCount(3);
});

it('shows the latest hunt from the authenticated user mixed with all hunts from others', function () {
    $otherUser = User::factory()->create();

    // Create multiple hunts for the authenticated user
    Hunt::factory()->create([
        'owner_id' => $this->user->id,
        'content' => 'Old hunt from auth user',
        'created_at' => now()->subHours(3),
    ]);

    $latestUserHunt = Hunt::factory()->create([
        'owner_id' => $this->user->id,
        'content' => 'Latest hunt from auth user',
        'created_at' => now()->subHour(),
    ]);

    // Create hunts from another user
    $otherHunt1 = Hunt::factory()->create([
        'owner_id' => $otherUser->id,
        'content' => 'First hunt from other user',
        'created_at' => now()->subHours(2),
    ]);

    $otherHunt2 = Hunt::factory()->create([
        'owner_id' => $otherUser->id,
        'content' => 'Second hunt from other user',
        'created_at' => now()->subMinutes(30),
    ]);

    $response = $this->get(route('hunts.index'));

    $response->assertSuccessful();

    $hunts = $response->viewData('page')['props']['hunts']['data'];
    $huntCollection = collect($hunts);

    // Assert total count (1 from auth user + 2 from other user)
    expect($huntCollection)->toHaveCount(3);

    // Assert authenticated user has only one hunt
    $userHunts = $huntCollection->filter(fn ($hunt) => $hunt['owner']['id'] === $this->user->id);
    expect($userHunts)->toHaveCount(1);
    expect($userHunts->first()['content'])->toBe('Latest hunt from auth user');

    // Assert other user has all their hunts
    $otherUserHunts = $huntCollection->filter(fn ($hunt) => $hunt['owner']['id'] === $otherUser->id);
    expect($otherUserHunts)->toHaveCount(2);
});

it('respects privacy settings when showing hunts in feed', function () {
    $privateUser = User::factory()->create([
        'privacy_settings' => [
            'profile_visibility' => 'private',
        ],
    ]);

    // Create a hunt from the private user
    Hunt::factory()->create([
        'owner_id' => $privateUser->id,
        'content' => 'Hunt from private user',
    ]);

    $response = $this->get(route('hunts.index'));

    $response->assertSuccessful();

    $hunts = $response->viewData('page')['props']['hunts']['data'];

    // Assert the hunt from the private user is not shown
    $privateUserHunts = collect($hunts)->filter(
        fn ($hunt) => $hunt['owner']['id'] === $privateUser->id
    );

    expect($privateUserHunts)->toHaveCount(0);
});
