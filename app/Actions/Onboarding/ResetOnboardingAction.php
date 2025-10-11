<?php

declare(strict_types=1);

namespace App\Actions\Onboarding;

use App\Models\User;

final readonly class ResetOnboardingAction
{
    /**
     * Reset onboarding status for the user.
     */
    public function handle(User $user): void
    {
        $user->update([
            'onboarding_completed' => false,
            'onboarding_completed_at' => null,
        ]);
    }
}
