<?php

declare(strict_types=1);

use App\Actions\Auth\LoginUserAction;
use App\DataTransferObjects\Auth\LoginCredentials;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('can authenticate user with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $credentials = LoginCredentials::from([
        'email' => 'test@example.com',
        'password' => 'password123',
        'remember' => false,
        'ip' => '127.0.0.1',
    ]);

    $action = new LoginUserAction();
    $result = $action->handle($credentials);

    expect($result)->toBeTrue()
        ->and(Auth::check())->toBeTrue()
        ->and(Auth::id())->toBe($user->id);
});

it('throws validation exception with invalid credentials', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $credentials = LoginCredentials::from([
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
        'remember' => false,
        'ip' => '127.0.0.1',
    ]);

    $action = new LoginUserAction();

    expect(fn () => $action->handle($credentials))
        ->toThrow(ValidationException::class)
        ->and(Auth::check())->toBeFalse();
});

it('handles remember me functionality', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $credentials = LoginCredentials::from([
        'email' => 'test@example.com',
        'password' => 'password123',
        'remember' => true,
        'ip' => '127.0.0.1',
    ]);

    $action = new LoginUserAction();
    $result = $action->handle($credentials);

    expect($result)->toBeTrue()
        ->and(Auth::check())->toBeTrue();
});

it('applies rate limiting after failed attempts', function () {
    RateLimiter::clear('test@example.com|127.0.0.1');

    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $credentials = LoginCredentials::from([
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
        'remember' => false,
        'ip' => '127.0.0.1',
    ]);

    $action = new LoginUserAction();

    // Make 5 failed attempts
    for ($i = 0; $i < 5; $i++) {
        try {
            $action->handle($credentials);
        } catch (ValidationException) {
            // Expected
        }
    }

    // 6th attempt should trigger rate limiting
    expect(fn () => $action->handle($credentials))
        ->toThrow(ValidationException::class);
});

it('dispatches lockout event when rate limited', function () {
    Event::fake();
    RateLimiter::clear('test@example.com|127.0.0.1');

    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $credentials = LoginCredentials::from([
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
        'remember' => false,
        'ip' => '127.0.0.1',
    ]);

    $action = new LoginUserAction();

    // Make enough failed attempts to trigger rate limiting
    for ($i = 0; $i < 6; $i++) {
        try {
            $action->handle($credentials);
        } catch (ValidationException) {
            // Expected
        }
    }

    Event::assertDispatched(Lockout::class);
});

it('clears rate limiter on successful authentication', function () {
    RateLimiter::clear('test@example.com|127.0.0.1');

    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $wrongCredentials = LoginCredentials::from([
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
        'remember' => false,
        'ip' => '127.0.0.1',
    ]);

    $rightCredentials = LoginCredentials::from([
        'email' => 'test@example.com',
        'password' => 'password123',
        'remember' => false,
        'ip' => '127.0.0.1',
    ]);

    $action = new LoginUserAction();

    // Make a few failed attempts
    for ($i = 0; $i < 3; $i++) {
        try {
            $action->handle($wrongCredentials);
        } catch (ValidationException) {
            // Expected
        }
    }

    // Successful login should clear the rate limiter
    $result = $action->handle($rightCredentials);
    expect($result)->toBeTrue();

    // Should be able to make more attempts without being rate limited
    Auth::logout();
    $result = $action->handle($rightCredentials);
    expect($result)->toBeTrue();
});

it('uses request ip when no ip provided in credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $credentials = LoginCredentials::from([
        'email' => 'test@example.com',
        'password' => 'password123',
        'remember' => false,
        'ip' => null,
    ]);

    $action = new LoginUserAction();
    $result = $action->handle($credentials);

    expect($result)->toBeTrue();
});
