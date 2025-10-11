<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('lists online users regardless of user id position', function () {
    // Create many users to ensure some are beyond any arbitrary limit
    $users = User::factory()->count(30)->create();

    // Pick two users near the end to simulate the issue (ids > 20)
    $first = $users[28];
    $second = $users[29];
    $authenticatedUser = $users[0];

    actingAs($authenticatedUser);

    $authenticatedUser->follow($first);
    $authenticatedUser->follow($second);

    // Mark the two users as online in cache (matching the app logic)
    Cache::put("user_online_{$first->id}", now(), now()->addMinutes(10));
    Cache::put("user_online_{$second->id}", now(), now()->addMinutes(10));

    $response = $this->get('/users/online');

    $response->assertOk();

    $usersArray = $response->json('data');
    expect($usersArray)->toBeArray();

    // Ensure the returned users include the two marked online
    $ids = collect($usersArray)->pluck('id');
    expect($ids->contains($first->id))->toBeTrue()
        ->and($ids->contains($second->id))->toBeTrue();
});

it('does not show online users who disabled messages', function () {
    $authenticatedUser = User::factory()->create();
    $onlineUser = User::factory()->create([
        'privacy_settings' => [
            'who_can_message' => 'none',
        ],
    ]);

    actingAs($authenticatedUser);

    $authenticatedUser->follow($onlineUser);

    // Mark user as online
    Cache::put("user_online_{$onlineUser->id}", now(), now()->addMinutes(10));

    $response = $this->get('/users/online');

    $response->assertOk();

    $ids = collect($response->json('data'))->pluck('id');
    expect($ids->contains($onlineUser->id))->toBeFalse();
});

it('does not show online users who only accept messages from followers when not following them', function () {
    $authenticatedUser = User::factory()->create();
    $onlineUser = User::factory()->create([
        'privacy_settings' => [
            'who_can_message' => 'followers',
        ],
    ]);

    actingAs($authenticatedUser);

    // Don't follow the user
    // Mark user as online
    Cache::put("user_online_{$onlineUser->id}", now(), now()->addMinutes(10));

    $response = $this->get('/users/online');

    $response->assertOk();

    $ids = collect($response->json('data'))->pluck('id');
    expect($ids->contains($onlineUser->id))->toBeFalse();
});

it('shows online users who only accept messages from followers when following them', function () {
    $authenticatedUser = User::factory()->create();
    $onlineUser = User::factory()->create([
        'privacy_settings' => [
            'who_can_message' => 'followers',
        ],
    ]);

    actingAs($authenticatedUser);

    $authenticatedUser->follow($onlineUser);

    // Mark user as online
    Cache::put("user_online_{$onlineUser->id}", now(), now()->addMinutes(10));

    $response = $this->get('/users/online');

    $response->assertOk();

    $ids = collect($response->json('data'))->pluck('id');
    expect($ids->contains($onlineUser->id))->toBeTrue();
});

it('does not show blocked users as online', function () {
    $authenticatedUser = User::factory()->create();
    $onlineUser = User::factory()->create();

    actingAs($authenticatedUser);

    $authenticatedUser->follow($onlineUser);
    $onlineUser->block($authenticatedUser);

    // Mark user as online
    Cache::put("user_online_{$onlineUser->id}", now(), now()->addMinutes(10));

    $response = $this->get('/users/online');

    $response->assertOk();

    $ids = collect($response->json('data'))->pluck('id');
    expect($ids->contains($onlineUser->id))->toBeFalse();
});

it('does not show users who disabled activity status as online', function () {
    $authenticatedUser = User::factory()->create();
    $onlineUser = User::factory()->create([
        'privacy_settings' => [
            'show_activity_status' => false,
        ],
    ]);

    actingAs($authenticatedUser);

    $authenticatedUser->follow($onlineUser);

    // Mark user as online
    Cache::put("user_online_{$onlineUser->id}", now(), now()->addMinutes(10));

    $response = $this->get('/users/online');

    $response->assertOk();

    $ids = collect($response->json('data'))->pluck('id');
    expect($ids->contains($onlineUser->id))->toBeFalse();
});

it('shows users who enabled activity status as online', function () {
    $authenticatedUser = User::factory()->create();
    $onlineUser = User::factory()->create([
        'privacy_settings' => [
            'show_activity_status' => true,
        ],
    ]);

    actingAs($authenticatedUser);

    $authenticatedUser->follow($onlineUser);

    // Mark user as online
    Cache::put("user_online_{$onlineUser->id}", now(), now()->addMinutes(10));

    $response = $this->get('/users/online');

    $response->assertOk();

    $ids = collect($response->json('data'))->pluck('id');
    expect($ids->contains($onlineUser->id))->toBeTrue();
});
