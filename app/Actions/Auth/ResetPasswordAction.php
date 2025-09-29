<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

final readonly class ResetPasswordAction
{
    /**
     * Reset the password with the given token and credentials.
     *
     * @param  array<string, mixed>  $credentials
     */
    public function handle(array $credentials): string
    {
        return Password::reset($credentials, function ($user, $password): void {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        });
    }
}
