<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

final readonly class DeleteAccountAction
{
    /**
     * Delete user account and logout.
     */
    public function handle(User $user): void
    {
        Auth::logout();
        $user->delete();
    }
}
