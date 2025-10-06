<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Events\ConversationsSnapshot;
use App\Events\UserOnline;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

final readonly class TrackUserPresence
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            /** @var mixed $user */
            $user = Auth::user();
            if (! $user instanceof User) {
                /** @var Response $response */
                $response = $next($request);

                return $response;
            }

            /** @var mixed $userId */
            $userId = $user->getAttribute('id');
            if (! is_numeric($userId)) {
                /** @var Response $response */
                $response = $next($request);

                return $response;
            }

            // Only track presence if user has activity status enabled
            if (! $user->showsActivityStatus()) {
                /** @var Response $response */
                $response = $next($request);

                return $response;
            }

            $cacheKey = "user_online_{$userId}";
            $lastSeen = Cache::get($cacheKey);

            if ($lastSeen === null) {
                // First time we see the user in this window; broadcast online and initial conversations snapshot
                UserOnline::dispatch($user);
                ConversationsSnapshot::dispatch($user);
            }

            // Refresh TTL
            Cache::put($cacheKey, now(), now()->addMinutes(5));
        }

        /** @var Response $response */
        $response = $next($request);

        return $response;
    }
}
