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
        $page = (int) $request->get('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        // Build query based on filter
        $query = $user->notifications()->latest();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        // Get total count for pagination
        $totalNotifications = $query->count();

        $notifications = $query
            ->offset($offset)
            ->limit($perPage)
            ->get()
            ->map(fn ($notification): array => [
                'id' => $notification->id,
                'type' => $notification->data['type'] ?? 'default',
                'title' => $notification->data['title'] ?? '',
                'message' => $notification->data['message'] ?? '',
                'data' => $notification->data,
                'read_at' => $notification->read_at?->toISOString(),
                'created_at' => $notification->created_at->toISOString(),
                'created_at_human' => $notification->created_at->diffForHumans(),
            ]);

        // Calculate pagination info
        $totalPages = (int) ceil($totalNotifications / $perPage);
        $hasNextPage = $page < $totalPages;
        $hasPrevPage = $page > 1;

        return inertia('notifications/index', [
            'notifications' => $notifications->toArray(),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalNotifications,
                'total_pages' => $totalPages,
                'has_next_page' => $hasNextPage,
                'has_prev_page' => $hasPrevPage,
            ],
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

        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        $notification = $markNotificationAsReadAction->handle($user, $id);

        if (! $notification instanceof \Illuminate\Notifications\DatabaseNotification) {
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

        if (! $user) {
            abort(401, 'Unauthenticated');
        }

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

        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $unreadCount = $getUnreadNotificationCountAction->handle($user);

        return response()->json([
            'unread_count' => $unreadCount,
        ]);
    }
}
