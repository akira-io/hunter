<?php

declare(strict_types=1);

use App\Actions\Onboarding\CompleteOnboardingAction;
use App\Models\User;
use Carbon\CarbonInterface;

test('complete onboarding action marks user as completed', function () {
    $user = User::factory()->create([
        'onboarding_completed' => false,
        'onboarding_completed_at' => null,
    ]);

    $action = new CompleteOnboardingAction;
    $action->handle($user);

    $user->refresh();

    expect($user->onboarding_completed)->toBeTrue()
        ->and($user->onboarding_completed_at)->not->toBeNull()
        ->and($user->onboarding_completed_at)->toBeInstanceOf(CarbonInterface::class);
});

test('complete onboarding action updates already completed user', function () {
    $oldDate = now()->subDays(5);

    $user = User::factory()->create([
        'onboarding_completed' => true,
        'onboarding_completed_at' => $oldDate,
    ]);

    $action = new CompleteOnboardingAction;
    $action->handle($user);

    $user->refresh();

    expect($user->onboarding_completed)->toBeTrue()
        ->and($user->onboarding_completed_at)->not->toBeNull()
        ->and($user->onboarding_completed_at->isAfter($oldDate))->toBeTrue();
});

test('complete onboarding action works with multiple users', function () {
    $users = User::factory()->count(3)->create([
        'onboarding_completed' => false,
        'onboarding_completed_at' => null,
    ]);

    $action = new CompleteOnboardingAction;

    foreach ($users as $user) {
        $action->handle($user);
        $user->refresh();

        expect($user->onboarding_completed)->toBeTrue()
            ->and($user->onboarding_completed_at)->not->toBeNull();
    }
});
