<?php

declare(strict_types=1);

use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('hunts.index', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    // Verify user is authenticated before logout
    $this->assertAuthenticated();

    $response = $this->post('/logout');

    $response->assertRedirect('/');

    // Make another request to verify user is logged out
    $this->get('/')->assertOk();
    $this->assertGuest();
});

test('users are rate limited after too many login attempts', function () {
    $user = User::factory()->create();

    // Trigger rate limiting by making 5 failed attempts
    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);
    }

    // The next attempt should be rate limited
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    // Assert that session has errors for email field
    $response->assertSessionHasErrors('email');

    // Get the error message
    $error = $response->getSession()->get('errors')->getBag('default')->first('email');

    // Verify the error message matches the throttle pattern
    // Message format: "Too many login attempts. Please try again in X seconds."
    expect($error)->toBeString()
        ->and($error)->toContain('Too many')
        ->and($error)->toContain('seconds');

    // Alternative: Verify using regex pattern
    $template = __('auth.throttle', ['seconds' => '___']);
    $pattern = '/^'.preg_quote($template, '/').'$/';
    $pattern = str_replace('___', '\d+', $pattern);

    expect($error)->toMatch($pattern);
});
