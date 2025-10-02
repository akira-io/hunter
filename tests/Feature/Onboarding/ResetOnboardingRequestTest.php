<?php

declare(strict_types=1);

use App\Http\Requests\Onboarding\ResetOnboardingRequest;
use App\Models\User;

use function Pest\Laravel\actingAs;

test('reset onboarding request authorizes authenticated user', function () {
    $user = User::factory()->create();

    actingAs($user);

    $request = ResetOnboardingRequest::create(
        route('onboarding.reset'),
        'POST'
    );

    $request->setUserResolver(fn () => $user);

    expect($request->authorize())->toBeTrue();
});

test('reset onboarding request denies guest user', function () {
    $request = ResetOnboardingRequest::create(
        route('onboarding.reset'),
        'POST'
    );

    $request->setUserResolver(fn () => null);

    expect($request->authorize())->toBeFalse();
});

test('reset onboarding request has no validation rules', function () {
    $user = User::factory()->create();

    actingAs($user);

    $request = ResetOnboardingRequest::create(
        route('onboarding.reset'),
        'POST'
    );

    $request->setUserResolver(fn () => $user);

    expect($request->rules())->toBeArray()
        ->and($request->rules())->toBeEmpty();
});

test('reset onboarding request validates successfully with empty data', function () {
    $user = User::factory()->create([
        'onboarding_completed' => true,
    ]);

    actingAs($user);

    $response = $this->post(route('onboarding.reset'));

    $response->assertRedirect();
});
