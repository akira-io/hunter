<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\actingAs;

it('clears presence cache key on logout', function () {
    $user = User::factory()->create();

    // Simulate user being online
    Cache::put("user_online_{$user->id}", now(), now()->addMinutes(10));
    expect(Cache::has("user_online_{$user->id}"))->toBeTrue();

    actingAs($user);

    $response = $this->post('/logout');

    $response->assertRedirect('/');

    expect(Cache::has("user_online_{$user->id}"))->toBeFalse();
});
