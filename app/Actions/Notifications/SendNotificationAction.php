<?php

declare(strict_types=1);

namespace App\Actions\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;

final readonly class SendNotificationAction
{
    /**
     * Send a notification to a user
     */
    public function handle(User $user, Notification $notification): void
    {
        $user->notify($notification);
    }

    /**
     * Send a notification to multiple users
     *
     * @param  iterable<User>  $users
     */
    public function handleMultiple(iterable $users, Notification $notification): void
    {
        foreach ($users as $user) {
            $user->notify($notification);
        }
    }
}
