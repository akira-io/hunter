<?php

declare(strict_types=1);

namespace App\Actions\Social;

use Akira\Followable\Exceptions\FollowableTraitNotFoundException;
use App\Models\User;

final readonly class UnfollowUserAction
{
    /**
     * Unfollow a user.
     *
     * @throws FollowableTraitNotFoundException
     */
    public function handle(User $follower, User $userToUnfollow): void
    {
        $follower->unfollow($userToUnfollow);
    }
}
