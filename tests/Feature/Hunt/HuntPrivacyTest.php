<?php

declare(strict_types=1);

use App\Models\Hunt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can view hunt from public profile', function () {
    $viewer = User::factory()->create();
    $owner = User::factory()->create([
        'privacy_settings' => [
            'profile_visibility' => 'public',
        ],
    ]);
    $hunt = Hunt::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($viewer)
        ->get(route('hunts.show', $hunt));

    $response->assertSuccessful();
});

test('follower can view hunt from followers-only profile', function () {
    $viewer = User::factory()->create();
    $owner = User::factory()->create([
        'privacy_settings' => [
            'profile_visibility' => 'followers',
        ],
    ]);
    $hunt = Hunt::factory()->create(['owner_id' => $owner->id]);

    $viewer->follow($owner);

    $response = $this->actingAs($viewer)
        ->get(route('hunts.show', $hunt));

    $response->assertSuccessful();
});

test('non-follower cannot view hunt from followers-only profile', function () {
    $viewer = User::factory()->create();
    $owner = User::factory()->create([
        'privacy_settings' => [
            'profile_visibility' => 'followers',
        ],
    ]);
    $hunt = Hunt::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($viewer)
        ->get(route('hunts.show', $hunt));

    $response->assertForbidden();
});

test('user cannot view hunt from private profile', function () {
    $viewer = User::factory()->create();
    $owner = User::factory()->create([
        'privacy_settings' => [
            'profile_visibility' => 'private',
        ],
    ]);
    $hunt = Hunt::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($viewer)
        ->get(route('hunts.show', $hunt));

    $response->assertForbidden();
});

test('blocked user cannot view hunt', function () {
    $viewer = User::factory()->create();
    $owner = User::factory()->create([
        'privacy_settings' => [
            'profile_visibility' => 'public',
        ],
    ]);
    $hunt = Hunt::factory()->create(['owner_id' => $owner->id]);

    $owner->block($viewer);

    $response = $this->actingAs($viewer)
        ->get(route('hunts.show', $hunt));

    $response->assertForbidden();
});

test('hunts feed does not show hunts from private profiles', function () {
    $viewer = User::factory()->create();
    $publicOwner = User::factory()->create([
        'privacy_settings' => [
            'profile_visibility' => 'public',
        ],
    ]);
    $privateOwner = User::factory()->create([
        'privacy_settings' => [
            'profile_visibility' => 'private',
        ],
    ]);

    $publicHunt = Hunt::factory()->create(['owner_id' => $publicOwner->id]);
    $privateHunt = Hunt::factory()->create(['owner_id' => $privateOwner->id]);

    $response = $this->actingAs($viewer)
        ->get(route('hunts.index'));

    $response->assertSuccessful();

    // The feed should be filtered - check by fetching the data directly
    $hunts = App\Actions\Hunt\GetHuntsAction::class;
    $hunts = app($hunts)->handle($viewer);
    $huntIds = $hunts->pluck('id')->toArray();

    expect($huntIds)->toContain($publicHunt->id);
    expect($huntIds)->not->toContain($privateHunt->id);
});

test('hunts feed does not show hunts from followers-only profiles when not following', function () {
    $viewer = User::factory()->create();
    $followersOnlyOwner = User::factory()->create([
        'privacy_settings' => [
            'profile_visibility' => 'followers',
        ],
    ]);

    $hunt = Hunt::factory()->create(['owner_id' => $followersOnlyOwner->id]);

    $response = $this->actingAs($viewer)
        ->get(route('hunts.index'));

    $response->assertSuccessful();

    // The feed should be filtered - check by fetching the data directly
    $hunts = App\Actions\Hunt\GetHuntsAction::class;
    $hunts = app($hunts)->handle($viewer);
    $huntIds = $hunts->pluck('id')->toArray();

    expect($huntIds)->not->toContain($hunt->id);
});

test('hunts feed shows hunts from followers-only profiles when following', function () {
    $viewer = User::factory()->create();
    $followersOnlyOwner = User::factory()->create([
        'privacy_settings' => [
            'profile_visibility' => 'followers',
        ],
    ]);

    $hunt = Hunt::factory()->create(['owner_id' => $followersOnlyOwner->id]);

    $viewer->follow($followersOnlyOwner);

    $response = $this->actingAs($viewer)
        ->get(route('hunts.index'));

    $response->assertSuccessful();

    // The feed should be filtered - check by fetching the data directly
    $hunts = App\Actions\Hunt\GetHuntsAction::class;
    $hunts = app($hunts)->handle($viewer);
    $huntIds = $hunts->pluck('id')->toArray();

    expect($huntIds)->toContain($hunt->id);
});
