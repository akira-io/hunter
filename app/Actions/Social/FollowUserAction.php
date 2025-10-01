<?php

declare(strict_types=1);

namespace App\Actions\Social;

use Akira\Followable\Exceptions\CannotFollowYourSelfException;
use Akira\Followable\Exceptions\FollowableTraitNotFoundException;
use App\Models\User;
use App\Notifications\UserFollowedNotification;

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

        // Send notification to the user being followed if they have follow notifications enabled
        $settings = $userToFollow->notification_settings ?? [];
        if ($settings['follow_notifications'] ?? true) {
            $userToFollow->notify(new UserFollowedNotification($follower));
        }
    }
}
