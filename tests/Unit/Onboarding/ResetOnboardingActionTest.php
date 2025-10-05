<?php

declare(strict_types=1);

use App\Actions\Onboarding\ResetOnboardingAction;
use App\Models\User;

test('reset onboarding action marks user as not completed', function () {
    $user = User::factory()->create([
        'onboarding_completed' => true,
        'onboarding_completed_at' => now(),
    ]);

    $action = new ResetOnboardingAction;
    $action->handle($user);

    $user->refresh();

    expect($user->onboarding_completed)->toBeFalse()
        ->and($user->onboarding_completed_at)->toBeNull();
});

test('reset onboarding action works on already reset user', function () {
    $user = User::factory()->create([
        'onboarding_completed' => false,
        'onboarding_completed_at' => null,
    ]);

    $action = new ResetOnboardingAction;
    $action->handle($user);

    $user->refresh();

    expect($user->onboarding_completed)->toBeFalse()
        ->and($user->onboarding_completed_at)->toBeNull();
});

test('reset onboarding action works with multiple users', function () {
    $users = User::factory()->count(3)->create([
        'onboarding_completed' => true,
        'onboarding_completed_at' => now(),
    ]);

    $action = new ResetOnboardingAction;

    foreach ($users as $user) {
        $action->handle($user);
        $user->refresh();

        expect($user->onboarding_completed)->toBeFalse()
            ->and($user->onboarding_completed_at)->toBeNull();
    }
});

test('reset onboarding action clears timestamp completely', function () {
    $user = User::factory()->create([
        'onboarding_completed' => true,
        'onboarding_completed_at' => now()->subWeeks(2),
    ]);

    $action = new ResetOnboardingAction;
    $action->handle($user);

    $user->refresh();

    expect($user->onboarding_completed)->toBeFalse()
        ->and($user->onboarding_completed_at)->toBeNull()
        ->and($user->getAttribute('onboarding_completed_at'))->toBeNull();
});
