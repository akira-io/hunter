<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can view public profile', function () {
    $viewer = User::factory()->create();
    $profileOwner = User::factory()->create([
        'privacy_settings' => [
            'profile_visibility' => 'public',
        ],
    ]);

    $response = $this->actingAs($viewer)
        ->get(route('public.profile.show', $profileOwner));

    $response->assertSuccessful();
});

test('follower can view followers-only profile', function () {
    $viewer = User::factory()->create();
    $profileOwner = User::factory()->create([
        'privacy_settings' => [
            'profile_visibility' => 'followers',
        ],
    ]);

    $viewer->follow($profileOwner);

    $response = $this->actingAs($viewer)
        ->get(route('public.profile.show', $profileOwner));

    $response->assertSuccessful();
});

test('non-follower cannot view followers-only profile', function () {
    $viewer = User::factory()->create();
    $profileOwner = User::factory()->create([
        'privacy_settings' => [
            'profile_visibility' => 'followers',
        ],
    ]);

    $response = $this->actingAs($viewer)
        ->get(route('public.profile.show', $profileOwner));

    $response->assertForbidden();
});

test('user cannot view private profile', function () {
    $viewer = User::factory()->create();
    $profileOwner = User::factory()->create([
        'privacy_settings' => [
            'profile_visibility' => 'private',
        ],
    ]);

    $response = $this->actingAs($viewer)
        ->get(route('public.profile.show', $profileOwner));

    $response->assertForbidden();
});

test('blocked user cannot view profile', function () {
    $viewer = User::factory()->create();
    $profileOwner = User::factory()->create([
        'privacy_settings' => [
            'profile_visibility' => 'public',
        ],
    ]);

    $profileOwner->block($viewer);

    $response = $this->actingAs($viewer)
        ->get(route('public.profile.show', $profileOwner));

    $response->assertForbidden();
});

test('user who blocked cannot view blocked user profile', function () {
    $viewer = User::factory()->create();
    $profileOwner = User::factory()->create([
        'privacy_settings' => [
            'profile_visibility' => 'public',
        ],
    ]);

    $viewer->block($profileOwner);

    $response = $this->actingAs($viewer)
        ->get(route('public.profile.show', $profileOwner));

    $response->assertForbidden();
});

test('owner can always view their own profile', function () {
    $user = User::factory()->create([
        'privacy_settings' => [
            'profile_visibility' => 'private',
        ],
    ]);

    $response = $this->actingAs($user)
        ->get(route('public.profile.show', $user));

    $response->assertSuccessful();
});
