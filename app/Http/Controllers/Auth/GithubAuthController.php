<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\HandleGithubAuthAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User;
use Spatie\RouteAttributes\Attributes\Get;

final readonly class GithubAuthController
{
    /**
     * Constructor to inject the HandleGithubAuthAction dependency.
     */
    public function __construct(
        private HandleGithubAuthAction $handleGithubAuthAction
    ) {}

    /**
     * Redirect the user to the GitHub authentication page.
     */
    #[Get('/auth/github', name: 'github.login')]
    public function redirect(): RedirectResponse|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        return Socialite::driver('github')
            ->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     */
    #[Get('/auth/github/callback', name: 'github.callback')]
    public function callback(): RedirectResponse
    {
        /** @var User $githubUser */
        $githubUser = Socialite::driver('github')->user();

        $user = $this->handleGithubAuthAction->handle($githubUser);

        Auth::login($user, remember: true);

        return to_route('hunts.index');
    }
}
