<?php

declare(strict_types=1);

use App\Http\Requests\Settings\NotificationUpdateRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
    ]);
});

it('authorizes all requests', function () {
    $request = new NotificationUpdateRequest;

    expect($request->authorize())->toBeTrue();
});

it('has correct validation rules', function () {
    $request = new NotificationUpdateRequest;
    $rules = $request->rules();

    expect($rules)->toHaveKey('follow_notifications')
        ->and($rules)->toHaveKey('email_notifications')
        ->and($rules)->toHaveKey('browser_notifications')
        ->and($rules['follow_notifications'])->toContain('required')
        ->and($rules['follow_notifications'])->toContain('boolean')
        ->and($rules['email_notifications'])->toContain('required')
        ->and($rules['email_notifications'])->toContain('boolean')
        ->and($rules['browser_notifications'])->toContain('required')
        ->and($rules['browser_notifications'])->toContain('boolean');
});

it('returns validated data with all notifications enabled', function () {
    $request = NotificationUpdateRequest::createFrom(
        request()->create('/settings/notifications', 'POST', [
            'follow_notifications' => true,
            'email_notifications' => true,
            'browser_notifications' => true,
        ]),
        new NotificationUpdateRequest
    );

    $request->setContainer(app());
    $request->setUserResolver(fn () => $this->user);
    $request->validateResolved();

    $validated = $request->validated();

    expect($validated)->toBe([
        'follow_notifications' => true,
        'email_notifications' => true,
        'browser_notifications' => true,
    ]);
});

it('returns validated data with all notifications disabled', function () {
    $request = NotificationUpdateRequest::createFrom(
        request()->create('/settings/notifications', 'POST', [
            'follow_notifications' => false,
            'email_notifications' => false,
            'browser_notifications' => false,
        ]),
        new NotificationUpdateRequest
    );

    $request->setContainer(app());
    $request->setUserResolver(fn () => $this->user);
    $request->validateResolved();

    $validated = $request->validated();

    expect($validated)->toBe([
        'follow_notifications' => false,
        'email_notifications' => false,
        'browser_notifications' => false,
    ]);
});

it('returns validated data with mixed notification settings', function () {
    $request = NotificationUpdateRequest::createFrom(
        request()->create('/settings/notifications', 'POST', [
            'follow_notifications' => true,
            'email_notifications' => false,
            'browser_notifications' => true,
        ]),
        new NotificationUpdateRequest
    );

    $request->setContainer(app());
    $request->setUserResolver(fn () => $this->user);
    $request->validateResolved();

    $validated = $request->validated();

    expect($validated)->toBe([
        'follow_notifications' => true,
        'email_notifications' => false,
        'browser_notifications' => true,
    ]);
});

it('converts string true to boolean', function () {
    actingAs($this->user);

    $response = $this->patchJson('/settings/notifications', [
        'follow_notifications' => '1',
        'email_notifications' => '1',
        'browser_notifications' => 1,
    ]);

    $response->assertRedirect();

    // Verify the data was saved correctly as booleans
    $this->user->refresh();
    expect($this->user->notification_settings['follow_notifications'])->toBeTrue()
        ->and($this->user->notification_settings['email_notifications'])->toBeTrue()
        ->and($this->user->notification_settings['browser_notifications'])->toBeTrue();
});

it('converts string false to boolean', function () {
    actingAs($this->user);

    $response = $this->patchJson('/settings/notifications', [
        'follow_notifications' => '0',
        'email_notifications' => '0',
        'browser_notifications' => 0,
    ]);

    $response->assertRedirect();

    // Verify the data was saved correctly as booleans
    $this->user->refresh();
    expect($this->user->notification_settings['follow_notifications'])->toBeFalse()
        ->and($this->user->notification_settings['email_notifications'])->toBeFalse()
        ->and($this->user->notification_settings['browser_notifications'])->toBeFalse();
});

it('has custom error messages', function () {
    $request = new NotificationUpdateRequest;
    $messages = $request->messages();

    expect($messages)->toHaveKey('follow_notifications.required')
        ->and($messages)->toHaveKey('follow_notifications.boolean')
        ->and($messages)->toHaveKey('email_notifications.required')
        ->and($messages)->toHaveKey('email_notifications.boolean')
        ->and($messages)->toHaveKey('browser_notifications.required')
        ->and($messages)->toHaveKey('browser_notifications.boolean');
});

it('fails validation when follow_notifications is missing', function () {
    actingAs($this->user);

    $this->patchJson('/settings/notifications', [
        'email_notifications' => true,
        'browser_notifications' => true,
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['follow_notifications']);
});

it('fails validation when email_notifications is missing', function () {
    actingAs($this->user);

    $this->patchJson('/settings/notifications', [
        'follow_notifications' => true,
        'browser_notifications' => true,
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email_notifications']);
});

it('fails validation when browser_notifications is missing', function () {
    actingAs($this->user);

    $this->patchJson('/settings/notifications', [
        'follow_notifications' => true,
        'email_notifications' => true,
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['browser_notifications']);
});
