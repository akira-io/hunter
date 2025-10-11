<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\actingAs;

test('onboarding controller complete endpoint requires authentication', function () {
    $response = $this->post(route('onboarding.complete'));

    $response->assertRedirect(route('login'));
});

test('onboarding controller reset endpoint requires authentication', function () {
    $response = $this->post(route('onboarding.reset'));

    $response->assertRedirect(route('login'));
});

test('onboarding controller complete marks user as completed', function () {
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

test('onboarding controller reset marks user as not completed', function () {
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

test('onboarding controller complete can be called multiple times', function () {
    $user = User::factory()->create([
        'onboarding_completed' => false,
    ]);

    actingAs($user);

    $this->post(route('onboarding.complete'));
    $user->refresh();
    $firstCompletionDate = $user->onboarding_completed_at;

    // Wait a moment to ensure different timestamp
    sleep(1);

    $this->post(route('onboarding.complete'));
    $user->refresh();

    expect($user->onboarding_completed)->toBeTrue()
        ->and($user->onboarding_completed_at)->not->toEqual($firstCompletionDate)
        ->and($user->onboarding_completed_at->isAfter($firstCompletionDate))->toBeTrue();
});

test('onboarding controller reset can be called multiple times', function () {
    $user = User::factory()->create([
        'onboarding_completed' => true,
        'onboarding_completed_at' => now(),
    ]);

    actingAs($user);

    $this->post(route('onboarding.reset'));
    $user->refresh();

    expect($user->onboarding_completed)->toBeFalse()
        ->and($user->onboarding_completed_at)->toBeNull();

    // Complete and reset again
    $this->post(route('onboarding.complete'));
    $this->post(route('onboarding.reset'));
    $user->refresh();

    expect($user->onboarding_completed)->toBeFalse()
        ->and($user->onboarding_completed_at)->toBeNull();
});

test('onboarding controller complete and reset work in sequence', function () {
    $user = User::factory()->create([
        'onboarding_completed' => false,
    ]);

    actingAs($user);

    // Complete
    $this->post(route('onboarding.complete'));
    $user->refresh();
    expect($user->onboarding_completed)->toBeTrue();

    // Reset
    $this->post(route('onboarding.reset'));
    $user->refresh();
    expect($user->onboarding_completed)->toBeFalse();

    // Complete again
    $this->post(route('onboarding.complete'));
    $user->refresh();
    expect($user->onboarding_completed)->toBeTrue();
});

test('onboarding controller complete redirects back', function () {
    $user = User::factory()->create();

    actingAs($user);

    $response = $this->from('/some-page')->post(route('onboarding.complete'));

    $response->assertRedirect('/some-page');
});

test('onboarding controller reset redirects back', function () {
    $user = User::factory()->create();

    actingAs($user);

    $response = $this->from('/settings/appearance')->post(route('onboarding.reset'));

    $response->assertRedirect('/settings/appearance');
});

test('different users can have different onboarding states', function () {
    $user1 = User::factory()->create(['onboarding_completed' => false]);
    $user2 = User::factory()->create(['onboarding_completed' => false]);

    actingAs($user1);
    $this->post(route('onboarding.complete'));

    actingAs($user2);

    $user1->refresh();
    $user2->refresh();

    expect($user1->onboarding_completed)->toBeTrue()
        ->and($user2->onboarding_completed)->toBeFalse();
});
