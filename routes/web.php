<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Welcome\WelcomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('home');

Route::middleware('auth:web')->group(static function () {
    // Chat mobile page
    Route::get('/chat/mobile/{conversation}', function ($conversationId) {
        $user = auth()->user();
        return Inertia\Inertia::render('Chat/Mobile', [
            'conversationId' => (int) $conversationId,
            'currentUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar_url' => (new App\Actions\User\GetAvatarAction())->handle($user),
            ],
        ]);
    })->name('chat.mobile');

    // Chat endpoints - exempt from CSRF to avoid SPA fetch mismatches
    Route::apiResource('conversations', ConversationController::class)
        ->withoutMiddleware([Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
    Route::apiResource('messages', MessageController::class)->only(['store', 'destroy'])
        ->withoutMiddleware([Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
    Route::post('/conversations/{conversation}/messages/read', [MessageController::class, 'markAsRead'])
        ->withoutMiddleware([Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

    // Presence endpoints (production) - exempt from CSRF to avoid SPA fetch mismatches
    Route::post('/presence/online', function (Request $request) {
        $user = $request->user();
        cache()->put("user_online_{$user->id}", now(), now()->addMinutes(10));

        return response()->json(['status' => 'online', 'user_id' => $user->id]);
    })->withoutMiddleware([Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

    Route::post('/presence/offline', function (Request $request) {
        $user = $request->user();
        cache()->forget("user_online_{$user->id}");

        return response()->json(['status' => 'offline', 'user_id' => $user->id]);
    })->withoutMiddleware([Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

    // Online users (production)
    Route::get('/users/online', function (Request $request) {
        $onlineUserIds = [];

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

    // Followed hunters
    Route::get('/followed-hunters', [App\Http\Controllers\Api\FollowedHuntersController::class, 'index']);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
