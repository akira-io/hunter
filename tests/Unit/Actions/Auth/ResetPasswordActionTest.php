<?php

declare(strict_types=1);

use App\Actions\Auth\ResetPasswordAction;
use App\DataTransferObjects\Auth\PasswordResetData;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

it('can reset password with valid token', function () {
    Event::fake();

    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('oldpassword'),
    ]);

    $token = Password::createToken($user);

    $resetData = PasswordResetData::fromArray([
        'email' => 'test@example.com',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
        'token' => $token,
    ]);

    $action = new ResetPasswordAction();
    $status = $action->handle($resetData);

    expect($status)->toBe(Password::PASSWORD_RESET);

    // Refresh user from database
    $user->refresh();

    // Verify password was changed
    expect(Hash::check('newpassword123', $user->password))->toBeTrue()
        ->and(Hash::check('oldpassword', $user->password))->toBeFalse()
        ->and($user->getRememberToken())->not->toBeNull();

    Event::assertDispatched(PasswordReset::class, function ($event) use ($user) {
        return $event->user->id === $user->id;
    });
});

it('returns appropriate status for invalid token', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('oldpassword'),
    ]);

    $resetData = PasswordResetData::fromArray([
        'email' => 'test@example.com',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
        'token' => 'invalid-token',
    ]);

    $action = new ResetPasswordAction();
    $status = $action->handle($resetData);

    expect($status)->toBe(Password::INVALID_TOKEN);

    // Verify password was not changed
    $user->refresh();
    expect(Hash::check('oldpassword', $user->password))->toBeTrue();
});

it('returns appropriate status for invalid email', function () {
    $resetData = PasswordResetData::fromArray([
        'email' => 'nonexistent@example.com',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
        'token' => 'some-token',
    ]);

    $action = new ResetPasswordAction();
    $status = $action->handle($resetData);

    expect($status)->toBe(Password::INVALID_USER);
});

it('updates remember token on successful reset', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('oldpassword'),
        'remember_token' => 'old-remember-token',
    ]);

    $oldRememberToken = $user->getRememberToken();
    $token = Password::createToken($user);

    $resetData = PasswordResetData::fromArray([
        'email' => 'test@example.com',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
        'token' => $token,
    ]);

    $action = new ResetPasswordAction();
    $action->handle($resetData);

    $user->refresh();
    expect($user->getRememberToken())->not->toBe($oldRememberToken)
        ->and($user->getRememberToken())->not->toBeNull()
        ->and(mb_strlen($user->getRememberToken()))->toBe(60);
});
