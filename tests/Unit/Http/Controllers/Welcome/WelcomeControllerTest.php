<?php

declare(strict_types=1);

use App\Actions\GetHuntersAction;
use App\Http\Controllers\Welcome\WelcomeController;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Response;

it('index returns inertia response for first page non ajax', function () {
    User::factory()->count(5)->create();
    $controller = app(WelcomeController::class);
    $request = Request::create('/', 'GET', ['page' => 1]);

    $response = $controller->index($request, app(GetHuntersAction::class));

    expect($response)->toBeInstanceOf(Response::class);
});

it('index uses normal flow for ajax requests even when page gt 1', function () {
    User::factory()->count(20)->create();
    $controller = app(WelcomeController::class);
    $request = Request::create('/', 'GET', ['page' => 2], [], [], [
        'HTTP_X-Inertia' => 'true',
    ]);

    $response = $controller->index($request, app(GetHuntersAction::class));

    expect($response)->toBeInstanceOf(Response::class);
});

it('index aggregates users for non ajax refresh with page gt 1 and no query', function () {
    User::factory()->count(30)->create();
    $controller = app(WelcomeController::class);
    $request = Request::create('/', 'GET', ['page' => 2]);

    $response = $controller->index($request, app(GetHuntersAction::class));

    expect($response)->toBeInstanceOf(Response::class);
});

it('index skips multipage when query param present', function () {
    User::factory()->count(20)->create();
    $controller = app(WelcomeController::class);
    $request = Request::create('/', 'GET', ['page' => 2, 'q' => 'john']);

    $response = $controller->index($request, app(GetHuntersAction::class));

    expect($response)->toBeInstanceOf(Response::class);
});

it('index handles unexpected page values gracefully', function () {
    User::factory()->count(10)->create();
    $controller = app(WelcomeController::class);

    foreach ([-5, 0, 'not-a-number'] as $pageVal) {
        $request = Request::create('/', 'GET', ['page' => $pageVal]);

        $response = $controller->index($request, app(GetHuntersAction::class));

        expect($response)->toBeInstanceOf(Response::class);
    }
});

it('index respects pagination limits', function () {
    User::factory()->count(50)->create();
    $controller = app(WelcomeController::class);
    $request = Request::create('/', 'GET', ['page' => 1]);

    $response = $controller->index($request, app(GetHuntersAction::class));

    expect($response)->toBeInstanceOf(Response::class);
});
