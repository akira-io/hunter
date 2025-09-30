<?php

declare(strict_types=1);

use App\Actions\Auth\SendPasswordResetAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

it('sends password reset link for valid email', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $action = new SendPasswordResetAction();
    $status = $action->handle($user->email);

    expect($status)->toBe(Password::RESET_LINK_SENT);
});

it('returns appropriate status for invalid email', function () {
    $action = new SendPasswordResetAction();
    $status = $action->handle('nonexistent@example.com');

    expect($status)->toBe(Password::INVALID_USER);
});

it('handles email throttling', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $action = new SendPasswordResetAction();

    // Send first reset link
    $status1 = $action->handle($user->email);
    expect($status1)->toBe(Password::RESET_LINK_SENT);

    // Try to send another one immediately (should be throttled)
    $status2 = $action->handle($user->email);
    expect($status2)->toBe(Password::RESET_THROTTLED);
});
