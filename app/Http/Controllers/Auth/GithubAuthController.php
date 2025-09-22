<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\ValueObjects\GithubUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Spatie\RouteAttributes\Attributes\Get;

final class GithubAuthController
{
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
        /** @var \Laravel\Socialite\Two\User $githubUser */
        $githubUser = Socialite::driver('github')->user();

        $githubUserData = GithubUser::from($githubUser)->toArray();

        $user = User::query()
            ->firstWhere('github_id', $githubUser->getId());

        if (! $user) {
            $user = User::query()->create($githubUserData);
        } else {
            $githubUserData['bio'] = $user->bio ?? $githubUserData['bio'];
            $githubUserData['location'] = $user->location ?? $githubUserData['location'];
            $githubUserData['avatar_url'] = $user->avatar_url ?? $githubUserData['avatar_url'];
            $githubUserData['email'] = $user->email ?? $githubUserData['email'];

            $user->update($githubUserData);
        }

        Auth::login($user, remember: true);

        return to_route('hunts.index');
    }
}
