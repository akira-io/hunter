<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\HandleGoogleAuthAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User;
use Spatie\RouteAttributes\Attributes\Get;

final readonly class GoogleAuthController
{
    public function __construct(
        private HandleGoogleAuthAction $handleGoogleAuthAction
    ) {}

    /**
     * Redirect the user to the Google authentication page.
     */
    #[Get('/auth/google', name: 'google.login')]
    public function redirect(): RedirectResponse|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        return Socialite::driver('google')
            ->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    #[Get('/auth/google/callback', name: 'google.callback')]
    public function callback(): RedirectResponse
    {
        /** @var User $googleUser */
        $googleUser = Socialite::driver('google')->user();

        $user = $this->handleGoogleAuthAction->execute($googleUser);

        Auth::login($user, remember: true);

        return to_route('hunts.index');
    }
}
