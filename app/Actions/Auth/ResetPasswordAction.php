<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DataTransferObjects\Auth\PasswordResetData;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

final readonly class ResetPasswordAction
{
    /**
     * Reset the password with the given token and credentials.
     */
    public function handle(PasswordResetData $resetData): string
    {
        $status = Password::reset($resetData->toArray(), function (User $user, string $password): void {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        });

        return (string) $status;
    }
}
