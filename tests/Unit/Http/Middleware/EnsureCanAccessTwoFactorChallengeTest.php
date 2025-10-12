<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureCanAccessTwoFactorChallenge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function Pest\Laravel\actingAs;

it('redirects to hunts index when user is already authenticated', function () {
    $user = User::factory()->create();
    $request = Request::create('/two-factor-challenge', 'GET');

    actingAs($user);

    $middleware = new EnsureCanAccessTwoFactorChallenge();
    $response = $middleware->handle($request, fn ($req) => response('next'));

    expect($response->isRedirect())->toBeTrue()
        ->and($response->headers->get('Location'))->toContain('hunts');
});

it('redirects to login when session does not have login.id', function () {
    Auth::logout();

    $request = Request::create('/two-factor-challenge', 'GET');
    $request->setLaravelSession(app('session.store'));

    $middleware = new EnsureCanAccessTwoFactorChallenge();
    $response = $middleware->handle($request, fn ($req) => response('next'));

    expect($response->isRedirect())->toBeTrue()
        ->and($response->headers->get('Location'))->toContain('login');
});

it('allows access when user is not authenticated but has login.id in session', function () {
    Auth::logout();

    $request = Request::create('/two-factor-challenge', 'GET');
    $request->setLaravelSession(app('session.store'));
    $request->session()->put('login.id', 123);

    $middleware = new EnsureCanAccessTwoFactorChallenge();
    $response = $middleware->handle($request, fn ($req) => response('next'));

    expect((string) $response->getContent())->toBe('next');
});
