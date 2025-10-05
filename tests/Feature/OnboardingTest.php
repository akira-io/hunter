<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;

test('user with onboarding completed should have the flag set to true in frontend', function () {
    $user = User::factory()->create([
        'onboarding_completed' => true,
        'onboarding_completed_at' => now(),
    ]);

    actingAs($user);

    $response = $this->get('/');

    $response->assertStatus(200);

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->where('auth.user.onboarding_completed', true)
        ->where('auth.user.id', $user->id)
    );
});

test('new user without onboarding completed should have the flag set to false in frontend', function () {
    $user = User::factory()->create([
        'onboarding_completed' => false,
        'onboarding_completed_at' => null,
    ]);

    actingAs($user);

    $response = $this->get('/');

    $response->assertStatus(200);

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->where('auth.user.onboarding_completed', false)
        ->where('auth.user.id', $user->id)
    );
});

test('completing onboarding sets the flag to true in database', function () {
    $user = User::factory()->create([
        'onboarding_completed' => false,
        'onboarding_completed_at' => null,
    ]);

    actingAs($user);

    $response = $this->post(route('onboarding.complete'));

    $response->assertRedirect();

    $user->refresh();

    expect($user->onboarding_completed)->toBeTrue()
        ->and($user->onboarding_completed_at)->not->toBeNull();
});

test('resetting onboarding sets the flag to false in database', function () {
    $user = User::factory()->create([
        'onboarding_completed' => true,
        'onboarding_completed_at' => now(),
    ]);

    actingAs($user);

    $response = $this->post(route('onboarding.reset'));

    $response->assertRedirect();

    $user->refresh();

    expect($user->onboarding_completed)->toBeFalse()
        ->and($user->onboarding_completed_at)->toBeNull();
});

test('onboarding completed timestamp is a valid datetime', function () {
    $user = User::factory()->create([
        'onboarding_completed' => false,
    ]);

    actingAs($user);

    $this->post(route('onboarding.complete'));

    $user->refresh();

    expect($user->onboarding_completed_at)
        ->toBeInstanceOf(Carbon\CarbonInterface::class)
        ->and($user->onboarding_completed_at->isToday())->toBeTrue();
});

test('onboarding state persists across multiple page loads', function () {
    $user = User::factory()->create([
        'onboarding_completed' => false,
    ]);

    actingAs($user);

    // First page load - should show onboarding needed
    $response1 = $this->get('/');
    $response1->assertInertia(fn (AssertableInertia $page) => $page
        ->where('auth.user.onboarding_completed', false)
    );

    // Complete onboarding
    $this->post(route('onboarding.complete'));

    // Second page load - should show onboarding completed
    $response2 = $this->get('/');
    $response2->assertInertia(fn (AssertableInertia $page) => $page
        ->where('auth.user.onboarding_completed', true)
    );

    // Third page load - should still show onboarding completed
    $response3 = $this->get('/');
    $response3->assertInertia(fn (AssertableInertia $page) => $page
        ->where('auth.user.onboarding_completed', true)
    );
});

test('user resource includes onboarding fields', function () {
    $user = User::factory()->create([
        'onboarding_completed' => true,
        'onboarding_completed_at' => now(),
    ]);

    $resource = new App\Http\Resources\UserResource($user);
    $array = $resource->toArray(request());

    expect($array)
        ->toHaveKey('onboarding_completed')
        ->toHaveKey('onboarding_completed_at')
        ->and($array['onboarding_completed'])->toBeTrue()
        ->and($array['onboarding_completed_at'])->not->toBeNull();
});
