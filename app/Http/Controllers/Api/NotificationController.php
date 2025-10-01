<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Notifications\GetUnreadNotificationCountAction;
use App\Actions\Notifications\GetUserNotificationsAction;
use App\Actions\Notifications\MarkAllNotificationsAsReadAction;
use App\Actions\Notifications\MarkNotificationAsReadAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class NotificationController
{
    /**
     * Get user notifications
     */
    public function index(Request $request, GetUserNotificationsAction $getUserNotificationsAction): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $result = $getUserNotificationsAction->handle($user);

        return response()->json($result);
    }

    /**
     * Mark notification as read
     */
    public function update(
        Request $request,
        string $id,
        MarkNotificationAsReadAction $markNotificationAsReadAction
    ): JsonResponse {
        $user = $request->user();

        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $notification = $markNotificationAsReadAction->handle($user, $id);

        if (! $notification instanceof \Illuminate\Notifications\DatabaseNotification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function store(
        Request $request,
        MarkAllNotificationsAsReadAction $markAllNotificationsAsReadAction
    ): JsonResponse {
        $user = $request->user();

        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $markAllNotificationsAsReadAction->handle($user);

        return response()->json(['success' => true]);
    }

    /**
     * Get unread notification count
     */
    public function show(
        Request $request,
        GetUnreadNotificationCountAction $getUnreadNotificationCountAction
    ): JsonResponse {
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
