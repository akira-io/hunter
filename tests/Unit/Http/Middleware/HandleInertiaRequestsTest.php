<?php

declare(strict_types=1);

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Http\Request;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('shares basic app data', function () {
    $middleware = new HandleInertiaRequests();
    $request = new Request();

    $sharedData = $middleware->share($request);

    expect($sharedData)->toHaveKey('name')
        ->and($sharedData['name'])->toBe(config('app.name'))
        ->and($sharedData)->toHaveKey('quote')
        ->and($sharedData['quote'])->toHaveKeys(['message', 'author']);
});

it('shares null user when not authenticated', function () {
    $middleware = new HandleInertiaRequests();
    $request = new Request();

    $sharedData = $middleware->share($request);

    expect($sharedData)->toHaveKey('auth')
        ->and($sharedData['auth'])->toHaveKey('user')
        ->and($sharedData['auth']['user'])->toBeNull();
});

it('shares user data when authenticated', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $middleware = new HandleInertiaRequests();
    $request = new Request();
    $request->setUserResolver(fn () => $user);

    $sharedData = $middleware->share($request);

    expect($sharedData['auth']['user'])->not->toBeNull()
        ->and($sharedData['auth']['user'])->toHaveKey('id')
        ->and($sharedData['auth']['user']['id'])->toBe($user->id)
        ->and($sharedData['auth']['user'])->toHaveKey('name')
        ->and($sharedData['auth']['user']['name'])->toBe('John Doe');
});

it('shares sidebar state as false when no cookie', function () {
    $middleware = new HandleInertiaRequests();
    $request = new Request();

    $sharedData = $middleware->share($request);

    expect($sharedData)->toHaveKey('sidebarOpen')
        ->and($sharedData['sidebarOpen'])->toBeFalse();
});

it('shares sidebar state as false when cookie is false', function () {
    $middleware = new HandleInertiaRequests();
    $request = new Request();
    $request->cookies->set('sidebar_state', 'false');

    $sharedData = $middleware->share($request);

    expect($sharedData['sidebarOpen'])->toBeFalse();
});

it('shares sidebar state as true when cookie is true', function () {
    $middleware = new HandleInertiaRequests();
    $request = new Request();
    $request->cookies->set('sidebar_state', 'true');

    $sharedData = $middleware->share($request);

    expect($sharedData['sidebarOpen'])->toBeTrue();
});

it('includes parent shared data', function () {
    $middleware = new HandleInertiaRequests();
    $request = new Request();

    $sharedData = $middleware->share($request);

    // Should include standard Inertia shared data like errors
    expect($sharedData)->toHaveKey('errors');
});

it('parses quotes correctly', function () {
    $middleware = new HandleInertiaRequests();
    $request = new Request();

    $sharedData = $middleware->share($request);

    expect($sharedData['quote']['message'])->toBeString()
        ->and($sharedData['quote']['author'])->toBeString()
        ->and(mb_strlen($sharedData['quote']['message']))->toBeGreaterThan(0)
        ->and(mb_strlen($sharedData['quote']['author']))->toBeGreaterThan(0);
});
