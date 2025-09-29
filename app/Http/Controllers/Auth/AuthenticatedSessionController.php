<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LoginUserAction;
use App\DataTransferObjects\Auth\LoginCredentials;
use App\Events\UserOffline;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

final readonly class AuthenticatedSessionController
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, LoginUserAction $loginUserAction): RedirectResponse
    {
        $credentials = LoginCredentials::from(
            credentials: $request->only('email', 'password'),
            remember: $request->boolean('remember'),
            ip: $request->ip()
        );

        $loginUserAction->handle($credentials);

        $request->session()->regenerate();

        return redirect()->intended(route('hunts.index', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        UserOffline::dispatch($user);
        Cache::forget("user_online_{$user->id}");

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
