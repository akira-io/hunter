<?php

declare(strict_types=1);

namespace App\Actions\Notifications;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

final readonly class GetUserNotificationsAction
{
    /**
     * Get user notifications with formatted data
     *
     * @return array{notifications: array<int, array{id: string, type: string, title: string, message: string, data: array<string, mixed>, read_at: string|null, created_at: string, created_at_human: string}>, unread_count: int}
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
            ->map(function (DatabaseNotification $notification): array {
                /** @var array<string, mixed> $data */
                $data = $notification->data;

                /** @var string|null $readAt */
                $readAt = $notification->read_at?->toISOString() ?? null;

                return [
                    'id' => (string) $notification->id,
                    'type' => (string) ($data['type'] ?? 'default'),
                    'title' => (string) ($data['title'] ?? ''),
                    'message' => (string) ($data['message'] ?? ''),
                    'data' => $data,
                    'read_at' => $readAt,
                    'created_at' => (string) ($notification->created_at?->toISOString() ?? ''),
                    'created_at_human' => (string) ($notification->created_at?->diffForHumans() ?? ''),
                ];
            })
            ->values()
            ->all();

        $unreadCount = $user->unreadNotifications()->count();

        return [
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ];
    }
}
