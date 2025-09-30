<?php

declare(strict_types=1);

use App\Http\Middleware\HandleAppearance;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;

it('shares default appearance when no cookie is present', function () {
    $middleware = new HandleAppearance();
    $request = new Request();

    $response = $middleware->handle($request, function ($request) {
        return new Response('Test');
    });

    expect(View::getShared()['appearance'])->toBe('system')
        ->and($response)->toBeInstanceOf(Response::class);
});

it('shares appearance from cookie when present', function () {
    $middleware = new HandleAppearance();
    $request = new Request();
    $request->cookies->set('appearance', 'dark');

    $response = $middleware->handle($request, function ($request) {
        return new Response('Test');
    });

    expect(View::getShared()['appearance'])->toBe('dark')
        ->and($response)->toBeInstanceOf(Response::class);
});

it('handles light appearance cookie', function () {
    $middleware = new HandleAppearance();
    $request = new Request();
    $request->cookies->set('appearance', 'light');

    $response = $middleware->handle($request, function ($request) {
        return new Response('Test');
    });

    expect(View::getShared()['appearance'])->toBe('light');
});

it('passes request through middleware chain', function () {
    $middleware = new HandleAppearance();
    $request = new Request();
    $request->cookies->set('appearance', 'dark');

    $nextCalled = false;
    $response = $middleware->handle($request, function ($req) use (&$nextCalled, $request) {
        $nextCalled = true;
        expect($req)->toBe($request);

        return new Response('Test Response');
    });

    expect($nextCalled)->toBeTrue()
        ->and($response->getContent())->toBe('Test Response');
});
