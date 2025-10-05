<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Models\User;
use InvalidArgumentException;

final readonly class BlockUserAction
{
    /**
     * Block a user.
     */
    public function handle(User $blocker, int $userId): void
    {
        $userToBlock = User::query()->findOrFail($userId);

        if ($blocker->id === $userToBlock->id) {
            throw new InvalidArgumentException('Não pode bloquear-se a si mesmo.');
        }

        $blocker->block($userToBlock);
    }
}
