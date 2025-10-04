<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;

#[Middleware(['auth:web'])]
final readonly class PresenceController
{
    /**
     * Mark user as online.
     *
     * @return array{status: string, user_id: int}
     */
    #[Post('/presence/online', withoutMiddleware: VerifyCsrfToken::class)]
    public function online(Request $request): array
    {
        /** @var User $user */
        $user = $request->user();
        cache()->put("user_online_{$user->id}", now(), now()->addMinutes(10));

        return ['status' => 'online', 'user_id' => $user->id];
    }

    /**
     * Mark user as offline.
     *
     * @return array{status: string, user_id: int}
     */
    #[Post('/presence/offline', withoutMiddleware: VerifyCsrfToken::class)]
    public function offline(Request $request): array
    {
        /** @var User $user */
        $user = $request->user();
        cache()->forget("user_online_{$user->id}");

        return ['status' => 'offline', 'user_id' => $user->id];
    }

    /**
     * Get list of online users.
     */
    #[Get('/users/online')]
    public function onlineUsers(Request $request): AnonymousResourceCollection
    {
        $onlineUserIds = [];

        /** @var User $requestUser */
        $requestUser = $request->user();

        $users = User::all();
        foreach ($users as $user) {
            if (cache()->has("user_online_{$user->id}")) {
                $onlineUserIds[] = $user->id;
            }
        }

        $onlineUsers = User::query()->whereIn('id', $onlineUserIds)
            ->where('id', '!=', $requestUser->id)
            ->get();

        return UserResource::collection($onlineUsers);
    }
}
