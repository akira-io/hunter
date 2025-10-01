<?php

declare(strict_types=1);

namespace App\Actions\Notifications;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

final readonly class GetUserNotificationsAction
{
    /**
     * Get user notifications with formatted data
     */
    public function handle(User $user, int $limit = 50, bool $unreadOnly = true): array
    {
        $query = $user->notifications()->latest();

        // Filter to unread only if requested
        if ($unreadOnly) {
            $query->whereNull('read_at');
        }

        $notifications = $query
            ->limit($limit)
            ->get()
            ->map(fn (DatabaseNotification $notification): array => [
                'id' => $notification->id,
                'type' => $notification->data['type'] ?? 'default',
                'title' => $notification->data['title'] ?? '',
                'message' => $notification->data['message'] ?? '',
                'data' => $notification->data,
                'read_at' => $notification->read_at?->toISOString(),
                'created_at' => $notification->created_at->toISOString(),
                'created_at_human' => $notification->created_at->diffForHumans(),
            ])
            ->toArray();

        $unreadCount = $user->unreadNotifications()->count();

        return [
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ];
    }
}
