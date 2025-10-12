<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final readonly class RedirectIfTwoFactorRequired
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->routeIs('two-factor.login') ||
            $request->routeIs('two-factor.login.store') ||
            $request->routeIs('two-factor.cancel')
        ) {
            return $next($request);
        }

        if (! Auth::check() && $request->session()->has('login.id')) {
            return redirect()->route('two-factor.login');
        }

        return $next($request);
    }
}
