<?php

declare(strict_types=1);

namespace App\Actions\Social;

use Akira\Followable\Exceptions\CannotFollowYourSelfException;
use Akira\Followable\Exceptions\FollowableTraitNotFoundException;
use App\Models\User;

final readonly class FollowUserAction
{
    /**
     * Follow a user.
     *
     * @throws CannotFollowYourSelfException|FollowableTraitNotFoundException
     */
    public function handle(User $follower, User $userToFollow): void
    {
        $follower->follow($userToFollow);
    }
}
