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

        $user = User::query()->firstWhere('email', $githubUserData['email']);

        if ($user) {
            $updateData = [
                'github_id' => $githubUserData['github_id'],
                'github_token' => $githubUserData['github_token'],
                'github_refresh_token' => $githubUserData['github_refresh_token'],
                'user_name' => $githubUserData['user_name'],
                'github_url' => $githubUserData['github_url'],
            ];

            if (empty($user->bio)) {
                $updateData['bio'] = $githubUserData['bio'];
            }
            if (empty($user->location)) {
                $updateData['location'] = $githubUserData['location'];
            }
            if (empty($user->avatar_url)) {
                $updateData['avatar_url'] = $githubUserData['avatar_url'];
            }

            $user->update($updateData);
        } else {

            $user = User::query()->firstWhere('github_id', $githubUser->getId());

            if ($user) {

                $githubUserData['bio'] = $user->bio ?? $githubUserData['bio'];
                $githubUserData['location'] = $user->location ?? $githubUserData['location'];
                $githubUserData['avatar_url'] = $user->avatar_url ?? $githubUserData['avatar_url'];
                $githubUserData['email'] = $user->email ?? $githubUserData['email'];

                $user->update($githubUserData);
            } else {
                $user = User::query()->create($githubUserData);
            }
        }

        Auth::login($user, remember: true);

        return to_route('hunts.index');
    }
}
