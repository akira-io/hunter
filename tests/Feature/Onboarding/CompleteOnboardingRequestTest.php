<?php

declare(strict_types=1);

use App\Http\Requests\Onboarding\CompleteOnboardingRequest;
use App\Models\User;

use function Pest\Laravel\actingAs;

test('complete onboarding request authorizes authenticated user', function () {
    $user = User::factory()->create();

    actingAs($user);

    $request = CompleteOnboardingRequest::create(
        route('onboarding.complete'),
        'POST'
    );

    $request->setUserResolver(fn () => $user);

    expect($request->authorize())->toBeTrue();
});

test('complete onboarding request denies guest user', function () {
    $request = CompleteOnboardingRequest::create(
        route('onboarding.complete'),
        'POST'
    );

    $request->setUserResolver(fn () => null);

    expect($request->authorize())->toBeFalse();
});

test('complete onboarding request has no validation rules', function () {
    $user = User::factory()->create();

    actingAs($user);

    $request = CompleteOnboardingRequest::create(
        route('onboarding.complete'),
        'POST'
    );

    $request->setUserResolver(fn () => $user);

    expect($request->rules())->toBeArray()
        ->and($request->rules())->toBeEmpty();
});

test('complete onboarding request validates successfully with empty data', function () {
    $user = User::factory()->create([
        'onboarding_completed' => false,
    ]);

    actingAs($user);

    $response = $this->post(route('onboarding.complete'));

    $response->assertRedirect();
});
