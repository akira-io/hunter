<?php

declare(strict_types=1);

namespace App\Http\Controllers\Notification;

use App\Actions\Notifications\GetUnreadNotificationCountAction;
use App\Actions\Notifications\MarkAllNotificationsAsReadAction;
use App\Actions\Notifications\MarkNotificationAsReadAction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Response;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Middleware(['auth', 'verified'])]
#[Prefix('notifications')]
final readonly class NotificationController
{
    /**
     * Display all notifications for the authenticated user with pagination
     */
    #[Get('/', name: 'notifications.index')]
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        // Get filter parameter
        $filter = $request->get('filter', 'all'); // 'all', 'unread', 'read'

        // Build query based on filter
        $query = $user->notifications()->latest();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query->paginate(20);

        return inertia('notifications/index', [
            'notifications' => \Inertia\Inertia::scroll(
                fn () => $notifications->through(function (mixed $notification): array {
                    if (! $notification instanceof DatabaseNotification) {
                        throw new \RuntimeException('Expected DatabaseNotification instance');
                    }

                    /** @var array<string, mixed> $data */
                    $data = $notification->data;

                    return [
                        'id' => (string) $notification->id,
                        'type' => (string) ($data['type'] ?? 'default'),
                        'title' => (string) ($data['title'] ?? ''),
                        'message' => (string) ($data['message'] ?? ''),
                        'data' => $data,
                        'read_at' => $notification->read_at?->toISOString(),
                        'created_at' => $notification->created_at?->toISOString() ?? '',
                        'created_at_human' => $notification->created_at?->diffForHumans() ?? '',
                    ];
                })
            ),
            'unread_count' => $user->unreadNotifications()->count(),
            'filter' => $filter,
            'counts' => [
                'all' => $user->notifications()->count(),
                'unread' => $user->unreadNotifications()->count(),
                'read' => $user->notifications()->whereNotNull('read_at')->count(),
            ],
        ]);
    }

    /**
     * Mark notification as read
     */
    #[Post('/{id}/read', name: 'notifications.read')]
    public function read(Request $request, string $id, MarkNotificationAsReadAction $markNotificationAsReadAction): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $notification = $markNotificationAsReadAction->handle($user, $id);

        if (! $notification instanceof DatabaseNotification) {
            abort(404, 'Notification not found');
        }

        return back();
    }

    /**
     * Mark all notifications as read
     */
    #[Post('/read-all', name: 'notifications.readAll')]
    public function readAll(
        Request $request,
        MarkAllNotificationsAsReadAction $markAllNotificationsAsReadAction
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $markAllNotificationsAsReadAction->handle($user);

        return back();
    }

    /**
     * Get unread notification count
     */
    #[Get('/unread-count', name: 'notifications.unreadCount')]
    public function unreadCount(
        Request $request,
        GetUnreadNotificationCountAction $getUnreadNotificationCountAction
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        $unreadCount = $getUnreadNotificationCountAction->handle($user);

        return response()->json([
            'unread_count' => $unreadCount,
        ]);
    }
}
