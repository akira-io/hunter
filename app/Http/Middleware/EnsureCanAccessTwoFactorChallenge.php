<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class EnsureCanAccessTwoFactorChallenge
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            return redirect()->route('hunts.index');
        }

        if (! $request->session()->has('login.id')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
