<?php

declare(strict_types=1);

use App\Actions\Settings\RevokeSessionAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {

    $this->action = new RevokeSessionAction();
    $this->user = User::factory()->create();
});

it('revokes a specific session', function () {
    // Create an active session
    $sessionId = DB::table('authentication_logs')->insertGetId([
        'authenticatable_id' => $this->user->id,
        'authenticatable_type' => User::class,
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0',
        'login_at' => now(),
        'login_successful' => true,
        'logout_at' => null,
        'cleared_by_user' => false,
    ]);

    // Act
    $result = $this->action->handle($this->user, $sessionId);

    // Assert
    expect($result)->toBeTrue();

    $session = DB::table('authentication_logs')->find($sessionId);
    expect($session->logout_at)->not->toBeNull()
        ->and($session->cleared_by_user)->toBeTrue();
});

it('returns false when session does not exist', function () {
    // Act
    $result = $this->action->handle($this->user, 99999);

    // Assert
    expect($result)->toBeFalse();
});

it('does not revoke session that is already logged out', function () {
    // Create a session that's already logged out
    $sessionId = DB::table('authentication_logs')->insertGetId([
        'authenticatable_id' => $this->user->id,
        'authenticatable_type' => User::class,
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0',
        'login_at' => now()->subHours(2),
        'login_successful' => true,
        'logout_at' => now()->subHour(),
        'cleared_by_user' => false,
    ]);

    $logoutTime = DB::table('authentication_logs')->find($sessionId)->logout_at;

    // Act
    $result = $this->action->handle($this->user, $sessionId);

    // Assert
    expect($result)->toBeFalse();

    // Verify logout time wasn't changed
    $session = DB::table('authentication_logs')->find($sessionId);
    expect($session->logout_at)->toBe($logoutTime);
});

it('only revokes sessions belonging to the user', function () {
    $otherUser = User::factory()->create();

    // Create session for other user
    $sessionId = DB::table('authentication_logs')->insertGetId([
        'authenticatable_id' => $otherUser->id,
        'authenticatable_type' => User::class,
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0',
        'login_at' => now(),
        'login_successful' => true,
        'logout_at' => null,
        'cleared_by_user' => false,
    ]);

    // Act - try to revoke other user's session
    $result = $this->action->handle($this->user, $sessionId);

    // Assert
    expect($result)->toBeFalse();

    // Verify session wasn't modified
    $session = DB::table('authentication_logs')->find($sessionId);
    expect($session->logout_at)->toBeNull()
        ->and($session->cleared_by_user)->toBeFalse();
});

it('logs activity when session is revoked', function () {
    // Create an active session
    $sessionId = DB::table('authentication_logs')->insertGetId([
        'authenticatable_id' => $this->user->id,
        'authenticatable_type' => User::class,
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0',
        'login_at' => now(),
        'login_successful' => true,
        'logout_at' => null,
        'cleared_by_user' => false,
    ]);

    // Act
    $this->action->handle($this->user, $sessionId);

    // Assert activity was logged
    $this->assertDatabaseHas('activity_log', [
        'causer_id' => $this->user->id,
        'causer_type' => User::class,
        'description' => 'Session revoked',
    ]);
});
