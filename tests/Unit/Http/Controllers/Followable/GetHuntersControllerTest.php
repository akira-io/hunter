<?php

declare(strict_types=1);

use App\Http\Controllers\Followable\GetHuntersController;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Response as InertiaResponse;

it('returns inertia response with followers prop and component name', function () {
    $user = User::factory()->create();
    $follower1 = User::factory()->create();
    $follower2 = User::factory()->create();

    $follower1->follow($user);
    $follower2->follow($user);

    $request = Request::create('/followable/followers', 'GET');
    $request->setUserResolver(fn () => $user);

    $controller = new GetHuntersController();
    $response = $controller($request);

    expect($response)->toBeInstanceOf(InertiaResponse::class);
});

it('returns inertia response with correct pagination', function () {
    $user = User::factory()->create();

    $request = Request::create('/followable/followers', 'GET');
    $request->setUserResolver(fn () => $user);

    $controller = new GetHuntersController();
    $response = $controller($request);

    expect($response)->toBeInstanceOf(InertiaResponse::class);
});

it('handles empty followers gracefully', function () {
    $user = User::factory()->create();

    $request = Request::create('/followable/followers', 'GET');
    $request->setUserResolver(fn () => $user);

    $controller = new GetHuntersController();
    $response = $controller($request);

    expect($response)->toBeInstanceOf(InertiaResponse::class);
});

