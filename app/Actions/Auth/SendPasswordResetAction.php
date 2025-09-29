<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Password;

final readonly class SendPasswordResetAction
{
    /**
     * Send a password reset link to the given email.
     */
    public function handle(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }
}
