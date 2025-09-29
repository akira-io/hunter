<?php

declare(strict_types=1);

use App\Actions\Auth\HandleGoogleAuthAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Two\User as SocialiteUser;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = new HandleGoogleAuthAction();
});

it('creates a new user when no existing user is found', function () {
    // Arrange
    $googleUser = new SocialiteUser();
    $googleUser->id = 'google-123';
    $googleUser->name = 'John Doe';
    $googleUser->email = 'john@example.com';
    $googleUser->avatar = 'https://lh3.googleusercontent.com/a/avatar.jpg';
    $googleUser->token = 'google-token';
    $googleUser->refreshToken = 'google-refresh-token';

    // Act
    $user = $this->action->handle($googleUser);

    // Assert
    expect($user)->toBeInstanceOf(User::class)
        ->and($user->email)->toBe('john@example.com')
        ->and($user->name)->toBe('John Doe')
        ->and($user->avatar_url)->toBe('https://lh3.googleusercontent.com/a/avatar.jpg')
        ->and($user->email_verified_at)->not->toBeNull();

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
        'name' => 'John Doe',
    ]);
});

it('links Google account to existing user found by email', function () {
    // Arrange - Create existing user (e.g., from GitHub auth)
    $existingUser = User::factory()->create([
        'email' => 'john@example.com',
        'name' => 'John Doe',
        'avatar_url' => null,
        'email_verified_at' => null,
    ]);

    $googleUser = new SocialiteUser();
    $googleUser->id = 'google-123';
    $googleUser->name = 'John Doe (Google)';
    $googleUser->email = 'john@example.com';
    $googleUser->avatar = 'https://lh3.googleusercontent.com/a/google-avatar.jpg';
    $googleUser->token = 'google-token';
    $googleUser->refreshToken = 'google-refresh-token';

    // Act
    $user = $this->action->handle($googleUser);

    // Assert
    expect($user->id)->toBe($existingUser->id)
        ->and($user->email)->toBe('john@example.com')
        ->and($user->name)->toBe('John Doe')
        ->and($user->avatar_url)->toBe('https://lh3.googleusercontent.com/a/google-avatar.jpg')
        ->and($user->email_verified_at)->not->toBeNull()
        ->and(User::where('email', 'john@example.com')->count())->toBe(1); // Same user

});

it('preserves existing user data when linking Google account', function () {
    // Arrange - Create existing user with existing data
    $existingUser = User::factory()->create([
        'email' => 'john@example.com',
        'name' => 'John Doe',
        'avatar_url' => 'https://existing-avatar.jpg',
        'email_verified_at' => now()->subDays(5),
    ]);

    $googleUser = new SocialiteUser();
    $googleUser->id = 'google-123';
    $googleUser->name = 'John Doe (Google)';
    $googleUser->email = 'john@example.com';
    $googleUser->avatar = 'https://lh3.googleusercontent.com/a/new-avatar.jpg';
    $googleUser->token = 'google-token';
    $googleUser->refreshToken = 'google-refresh-token';

    // Act
    $user = $this->action->handle($googleUser);

    // Assert
    expect($user->id)->toBe($existingUser->id)
        ->and($user->avatar_url)->toBe('https://existing-avatar.jpg')
        ->and($user->email_verified_at)->toEqual($existingUser->email_verified_at);
});

it('updates empty avatar from Google data', function () {
    // Arrange - Create user without avatar
    $existingUser = User::factory()->create([
        'email' => 'john@example.com',
        'name' => 'John Doe',
        'avatar_url' => '',
        'email_verified_at' => now(),
    ]);

    $googleUser = new SocialiteUser();
    $googleUser->id = 'google-123';
    $googleUser->name = 'John Doe';
    $googleUser->email = 'john@example.com';
    $googleUser->avatar = 'https://lh3.googleusercontent.com/a/google-avatar.jpg';
    $googleUser->token = 'google-token';

    // Act
    $user = $this->action->handle($googleUser);

    // Assert
    expect($user->avatar_url)->toBe('https://lh3.googleusercontent.com/a/google-avatar.jpg');
});

it('verifies email when user email is not verified', function () {
    // Arrange - Create user with unverified email
    $existingUser = User::factory()->create([
        'email' => 'john@example.com',
        'name' => 'John Doe',
        'email_verified_at' => null,
    ]);

    $googleUser = new SocialiteUser();
    $googleUser->id = 'google-123';
    $googleUser->name = 'John Doe';
    $googleUser->email = 'john@example.com';
    $googleUser->avatar = 'https://lh3.googleusercontent.com/a/avatar.jpg';

    // Act
    $user = $this->action->handle($googleUser);

    // Assert
    expect($user->email_verified_at)->not->toBeNull();
});
