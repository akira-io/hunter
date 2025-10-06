<?php

declare(strict_types=1);

namespace App\Actions\Social;

use Akira\Followable\Exceptions\CannotFollowYourSelfException;
use Akira\Followable\Exceptions\FollowableTraitNotFoundException;
use App\Models\User;
use App\Notifications\UserFollowedNotification;
use InvalidArgumentException;

final readonly class FollowUserAction
{
    /**
     * Follow a user.
     *
     * @throws CannotFollowYourSelfException|FollowableTraitNotFoundException
     */
    public function handle(User $follower, User $userToFollow): void
    {
        // Prevent following if either user has blocked the other
        if ($follower->hasBlocked($userToFollow) || $follower->isBlockedBy($userToFollow)) {
            throw new InvalidArgumentException('Não pode seguir este utilizador.');
        }

        $follower->follow($userToFollow);

        $userToFollow->notify(new UserFollowedNotification($follower));
    }
}
