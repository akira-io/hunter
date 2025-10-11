<?php

declare(strict_types=1);

namespace App\Actions\Notifications;

use App\Models\User;

final readonly class GetUnreadNotificationCountAction
{
    /**
     * Get the count of unread notifications for a user
     */
    public function handle(User $user): int
    {
        return $user->unreadNotifications()->count();
    }
}
