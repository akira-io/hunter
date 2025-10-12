<?php

declare(strict_types=1);

use App\Http\Middleware\RedirectIfTwoFactorRequired;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function Pest\Laravel\actingAs;

it('allows access to two-factor routes', function () {
    $request = Request::create('/two-factor-challenge', 'GET');
    $request->setRouteResolver(function () {
        $route = new Illuminate\Routing\Route('GET', '/two-factor-challenge', []);
        $route->name('two-factor.login');

        return $route;
    });
    $request->setLaravelSession(app('session.store'));
    $request->session()->put('login.id', 123);

    $middleware = new RedirectIfTwoFactorRequired();
    $response = $middleware->handle($request, fn ($req) => response('next'));

    expect((string) $response->getContent())->toBe('next');
});

it('allows access to two-factor store routes', function () {
    $request = Request::create('/two-factor-challenge', 'POST');
    $request->setRouteResolver(function () {
        $route = new Illuminate\Routing\Route('POST', '/two-factor-challenge', []);
        $route->name('two-factor.login.store');

        return $route;
    });
    $request->setLaravelSession(app('session.store'));
    $request->session()->put('login.id', 123);

    $middleware = new RedirectIfTwoFactorRequired();
    $response = $middleware->handle($request, fn ($req) => response('next'));

    expect((string) $response->getContent())->toBe('next');
});

it('allows access to two-factor cancel routes', function () {
    $request = Request::create('/two-factor-challenge/cancel', 'POST');
    $request->setRouteResolver(function () {
        $route = new Illuminate\Routing\Route('POST', '/two-factor-challenge/cancel', []);
        $route->name('two-factor.cancel');

        return $route;
    });
    $request->setLaravelSession(app('session.store'));

    $middleware = new RedirectIfTwoFactorRequired();
    $response = $middleware->handle($request, fn ($req) => response('next'));

    expect((string) $response->getContent())->toBe('next');
});

it('redirects to two-factor challenge when not authenticated but has login session', function () {
    Auth::logout();

    $request = Request::create('/dashboard', 'GET');
    $request->setRouteResolver(function () {
        $route = new Illuminate\Routing\Route('GET', '/dashboard', []);
        $route->name('dashboard');

        return $route;
    });
    $request->setLaravelSession(app('session.store'));
    $request->session()->put('login.id', 123);

    $middleware = new RedirectIfTwoFactorRequired();
    $response = $middleware->handle($request, fn ($req) => response('next'));

    expect($response->isRedirect())->toBeTrue()
        ->and($response->headers->get('Location'))->toContain('two-factor');
});

it('allows normal requests when authenticated', function () {
    $user = User::factory()->create();
    actingAs($user);

    $request = Request::create('/dashboard', 'GET');
    $request->setRouteResolver(function () {
        $route = new Illuminate\Routing\Route('GET', '/dashboard', []);
        $route->name('dashboard');

        return $route;
    });
    $request->setLaravelSession(app('session.store'));

    $middleware = new RedirectIfTwoFactorRequired();
    $response = $middleware->handle($request, fn ($req) => response('next'));

    expect((string) $response->getContent())->toBe('next');
});

it('allows normal requests when no login session exists', function () {
    Auth::logout();

    $request = Request::create('/dashboard', 'GET');
    $request->setRouteResolver(function () {
        $route = new Illuminate\Routing\Route('GET', '/dashboard', []);
        $route->name('dashboard');

        return $route;
    });
    $request->setLaravelSession(app('session.store'));

    $middleware = new RedirectIfTwoFactorRequired();
    $response = $middleware->handle($request, fn ($req) => response('next'));

    expect((string) $response->getContent())->toBe('next');
});
