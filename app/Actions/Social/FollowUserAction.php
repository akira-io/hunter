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
     * @throws CannotFollowYourSelfException|FollowableTraitNotFoundException|InvalidArgumentException
     */
    public function handle(User $follower, User $userToFollow): void
    {

        if ($follower->hasBlocked($userToFollow) || $follower->isBlockedBy($userToFollow)) {
            throw new InvalidArgumentException('Hunter não está a aceitar seguidores neste momento. Tente mais tarde.');
        }

        $follower->follow($userToFollow);

        $userToFollow->notify(new UserFollowedNotification($follower));
    }
}
