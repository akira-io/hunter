<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Onboarding\CompleteOnboardingAction;
use App\Actions\Onboarding\ResetOnboardingAction;
use App\Http\Requests\Onboarding\CompleteOnboardingRequest;
use App\Http\Requests\Onboarding\ResetOnboardingRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Middleware(['auth', 'verified'])]
#[Prefix('onboarding')]

final readonly class OnboardingController
{
    /**
     * Get a new instance.
     */
    public function __construct(
        private CompleteOnboardingAction $completeOnboardingAction,
        private ResetOnboardingAction $resetOnboardingAction
    ) {}

    /**
     * Mark onboarding as completed for the authenticated user
     */
    #[Post('/complete', name: 'onboarding.complete')]
    public function store(CompleteOnboardingRequest $request): RedirectResponse
    {
        $user = type($request->user())->as(User::class);

        $this->completeOnboardingAction->handle($user);

        return redirect()->back();
    }

    /**
     * Reset onboarding status (for replay tutorial)
     */
    #[Post('/reset', name: 'onboarding.reset')]
    public function destroy(ResetOnboardingRequest $request): RedirectResponse
    {
        $user = type($request->user())->as(User::class);

        $this->resetOnboardingAction->handle($user);

        return redirect()->back();
    }
}
