<?php

declare(strict_types=1);

use App\Actions\Settings\DisconnectOAuthAccountAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = new DisconnectOAuthAccountAction();
});

it('disconnects github account successfully', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
        'github_id' => 'github-123',
        'github_token' => 'github-token',
        'github_refresh_token' => 'github-refresh',
    ]);

    // Act
    $result = $this->action->handle($user, 'github');

    // Assert
    expect($result)->toBeTrue();
    expect($user->refresh()->github_id)->toBeNull();
    expect($user->github_token)->toBeNull();
    expect($user->github_refresh_token)->toBeNull();
});

it('disconnects google account successfully', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
        'google_id' => 'google-123',
        'google_token' => 'google-token',
        'google_refresh_token' => 'google-refresh',
    ]);

    // Act
    $result = $this->action->handle($user, 'google');

    // Assert
    expect($result)->toBeTrue();
    expect($user->refresh()->google_id)->toBeNull();
    expect($user->google_token)->toBeNull();
    expect($user->google_refresh_token)->toBeNull();
});

it('returns false for invalid provider', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    // Act
    $result = $this->action->handle($user, 'invalid-provider');

    // Assert
    expect($result)->toBeFalse();
});

it('logs activity when github account is disconnected', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
        'github_id' => 'github-123',
        'github_token' => 'github-token',
        'github_refresh_token' => 'github-refresh',
    ]);

    // Act
    $this->action->handle($user, 'github');

    // Assert activity was logged
    $this->assertDatabaseHas('activity_log', [
        'causer_id' => $user->id,
        'causer_type' => User::class,
        'description' => 'Github account disconnected',
    ]);
});

it('logs activity when google account is disconnected', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
        'google_id' => 'google-123',
        'google_token' => 'google-token',
        'google_refresh_token' => 'google-refresh',
    ]);

    // Act
    $this->action->handle($user, 'google');

    // Assert activity was logged
    $this->assertDatabaseHas('activity_log', [
        'causer_id' => $user->id,
        'causer_type' => User::class,
        'description' => 'Google account disconnected',
    ]);
});

it('does not log activity when disconnection fails', function () {
    // Test with invalid provider instead
    $user = User::factory()->create([
        'password' => bcrypt('password'),
        'github_id' => 'github-123',
    ]);

    // Act - try to disconnect with invalid provider
    $this->action->handle($user, 'invalid-provider');

    // Assert no activity was logged for invalid provider
    $this->assertDatabaseMissing('activity_log', [
        'causer_id' => $user->id,
        'description' => 'Invalid-provider account disconnected',
    ]);
});
