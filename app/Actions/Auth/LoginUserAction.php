<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DataTransferObjects\Auth\LoginCredentials;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class LoginUserAction
{
    /**
     * Attempt to authenticate the user with the given credentials.
     *
     * @throws ValidationException
     */
    public function handle(LoginCredentials $credentials): bool
    {
        $ip = $credentials->ip ?? (string) request()->ip();
        $throttleKey = $this->getThrottleKey($credentials->email, $ip);

        $this->ensureIsNotRateLimited($throttleKey);

        if (! Auth::attempt($credentials->toArray(), $credentials->remember)) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($throttleKey);

        return true;
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    private function ensureIsNotRateLimited(string $throttleKey): void
    {
        if (! RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($throttleKey);

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key.
     */
    private function getThrottleKey(string $email, string $ip): string
    {
        return Str::transliterate(Str::lower($email).'|'.$ip);
    }
}
