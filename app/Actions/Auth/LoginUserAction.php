<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final readonly class LoginUserAction
{
    /**
     * Attempt to authenticate the user with the given credentials.
     *
     * @param  array<string, mixed>  $credentials
     *
     * @throws ValidationException
     */
    public function handle(array $credentials, bool $remember = false): bool
    {
        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        return true;
    }
}
