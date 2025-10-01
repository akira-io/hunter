<?php

declare(strict_types=1);

namespace App\Actions\Notifications;

use App\Models\User;

final readonly class MarkAllNotificationsAsReadAction
{
    /**
     * Mark all notifications as read for a user
     */
    public function handle(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }
}
