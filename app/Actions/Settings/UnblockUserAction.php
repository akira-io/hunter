<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Models\User;

final readonly class UnblockUserAction
{
    /**
     * Unblock a user.
     */
    public function handle(User $blocker, int $userId): void
    {
        $userToUnblock = User::query()->findOrFail($userId);

        $blocker->unblock($userToUnblock);
    }
}
