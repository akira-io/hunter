<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Events\ConversationsSnapshot;
use App\Events\UserOnline;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

final class TrackUserPresence
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $cacheKey = "user_online_{$user->id}";
            $lastSeen = Cache::get($cacheKey);

            if ($lastSeen === null) {
                // First time we see the user in this window; broadcast online and initial conversations snapshot
                UserOnline::dispatch($user);
                ConversationsSnapshot::dispatch($user);
            }

            // Refresh TTL
            Cache::put($cacheKey, now(), now()->addMinutes(5));
        }

        return $next($request);
    }
}
