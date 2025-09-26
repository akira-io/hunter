<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\actingAs;

it('lists online users regardless of user id position', function () {
    // Create many users to ensure some are beyond any arbitrary limit
    $users = User::factory()->count(30)->create();

    // Pick two users near the end to simulate the issue (ids > 20)
    $first = $users[28];
    $second = $users[29];

    // Authenticate as any user to hit the auth-protected route
    actingAs($users[0]);

    // Mark the two users as online in cache (matching the app logic)
    Cache::put("user_online_{$first->id}", now(), now()->addMinutes(10));
    Cache::put("user_online_{$second->id}", now(), now()->addMinutes(10));

    $response = $this->get('/users/online');

    $response->assertOk();

    $usersArray = $response->json('data');
    expect($usersArray)->toBeArray();

    // Ensure the returned users include the two marked online
    $ids = collect($usersArray)->pluck('id');
    expect($ids->contains($first->id))->toBeTrue();
    expect($ids->contains($second->id))->toBeTrue();
});
