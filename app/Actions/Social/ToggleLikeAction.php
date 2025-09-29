<?php

declare(strict_types=1);

namespace App\Actions\Social;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

final readonly class ToggleLikeAction
{
    /**
     * Toggle like status for a likeable model.
     */
    public function handle(User $user, Model $likeable): void
    {
        $user->toggleLike($likeable);
    }
}
