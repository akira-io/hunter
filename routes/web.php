<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Welcome\WelcomeController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('home');

Route::middleware('auth:web')->group(static function () {

    // Chat API endpoints - exempt from CSRF to avoid SPA fetch mismatches
    Route::apiResource('conversations', ConversationController::class)
        ->withoutMiddleware([VerifyCsrfToken::class]);
    Route::apiResource('messages', MessageController::class)->only(['store', 'destroy'])
        ->withoutMiddleware([VerifyCsrfToken::class]);
    Route::post('/conversations/{conversation}/messages/read', [MessageController::class, 'markAsRead'])
        ->withoutMiddleware([VerifyCsrfToken::class]);

    // Followed hunters
    Route::get('/followed-hunters', [App\Http\Controllers\Api\FollowedHuntersController::class, 'index']);

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
