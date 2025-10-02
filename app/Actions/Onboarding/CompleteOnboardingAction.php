<?php

declare(strict_types=1);

namespace App\Actions\Onboarding;

use App\Models\User;
use Carbon\Carbon;

final readonly class CompleteOnboardingAction
{
    /**
     * Mark onboarding as completed for the user.
     */
    public function handle(User $user): void
    {
        $user->update([
            'onboarding_completed' => true,
            'onboarding_completed_at' => Carbon::now(),
        ]);
    }
}
