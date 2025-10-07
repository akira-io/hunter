<?php

declare(strict_types=1);

use App\Http\Controllers\Api\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // User presence for mobile
    Route::post('/presence/online', function (Request $request) {
        $user = $request->user();
        cache()->put("user_online_{$user->id}", now(), now()->addMinutes(10));

        return response()->json(['status' => 'online']);
    });

    Route::post('/presence/offline', function (Request $request) {
        $user = $request->user();
        cache()->forget("user_online_{$user->id}");

        return response()->json(['status' => 'offline']);
    });

    // Get online users
    Route::get('/users/online', function (Request $request) {
        $onlineUserIds = [];

        // Procurar Hunters online no cache
        $users = App\Models\User::all();
        foreach ($users as $user) {
            if (cache()->has("user_online_{$user->id}")) {
                $onlineUserIds[] = $user->id;
            }
        }

        $onlineUsers = App\Models\User::whereIn('id', $onlineUserIds)
            ->where('id', '!=', $request->user()->id)
            ->get();

        return App\Http\Resources\UserResource::collection($onlineUsers);
    });

    // Bulk message status
    Route::post('/messages/bulk-read', function (Request $request) {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'integer|exists:messages,id',
        ]);

        App\Models\Message::whereIn('id', $request->message_ids)
            ->where('user_id', '!=', $request->user()->id)
            ->update(['read_at' => now()]);

        return response()->json(['status' => 'updated']);
    });

    // Notifications API
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::put('/{id}', [NotificationController::class, 'update']);
        Route::post('/mark-all-read', [NotificationController::class, 'store']);
        Route::get('/unread-count', [NotificationController::class, 'show']);
    });

});
