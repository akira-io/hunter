<?php

declare(strict_types=1);

use App\Http\Responses\TwoFactorLoginResponse;
use Illuminate\Http\Request;

it('returns json response when request wants json', function () {
    $request = Request::create('/two-factor-challenge', 'POST');
    $request->headers->set('Accept', 'application/json');

    $response = new TwoFactorLoginResponse();
    $result = $response->toResponse($request);

    expect($result)->toBeInstanceOf(Illuminate\Http\JsonResponse::class)
        ->and($result->getStatusCode())->toBe(204);
});

it('returns redirect response when request does not want json', function () {
    $request = Request::create('/two-factor-challenge', 'POST');

    $response = new TwoFactorLoginResponse();
    $result = $response->toResponse($request);

    expect($result->isRedirect())->toBeTrue()
        ->and($result->headers->get('Location'))->toContain('hunts');
});

it('redirects to intended url when available', function () {
    $request = Request::create('/two-factor-challenge', 'POST');
    $request->setLaravelSession(app('session.store'));
    $request->session()->put('url.intended', '/profile');

    $response = new TwoFactorLoginResponse();
    $result = $response->toResponse($request);

    expect($result->isRedirect())->toBeTrue()
        ->and($result->headers->get('Location'))->toContain('profile');
});
