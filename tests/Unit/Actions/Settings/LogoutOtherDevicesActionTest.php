<?php

declare(strict_types=1);

use App\Actions\Settings\LogoutOtherDevicesAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {

    $this->action = new LogoutOtherDevicesAction();
    $this->user = User::factory()->create();
});

it('logs out all other devices except current', function () {
    $currentIp = '192.168.1.100';

    // Create current session
    $currentSessionId = DB::table('authentication_logs')->insertGetId([
        'authenticatable_id' => $this->user->id,
        'authenticatable_type' => User::class,
        'ip_address' => $currentIp,
        'user_agent' => 'Mozilla/5.0 (Current)',
        'login_at' => now(),
        'login_successful' => true,
        'logout_at' => null,
        'cleared_by_user' => false,
    ]);

    // Create other sessions
    $otherSession1 = DB::table('authentication_logs')->insertGetId([
        'authenticatable_id' => $this->user->id,
        'authenticatable_type' => User::class,
        'ip_address' => '192.168.1.101',
        'user_agent' => 'Mozilla/5.0 (Other 1)',
        'login_at' => now()->subHour(),
        'login_successful' => true,
        'logout_at' => null,
        'cleared_by_user' => false,
    ]);

    $otherSession2 = DB::table('authentication_logs')->insertGetId([
        'authenticatable_id' => $this->user->id,
        'authenticatable_type' => User::class,
        'ip_address' => '192.168.1.102',
        'user_agent' => 'Mozilla/5.0 (Other 2)',
        'login_at' => now()->subHours(2),
        'login_successful' => true,
        'logout_at' => null,
        'cleared_by_user' => false,
    ]);

    // Act
    $count = $this->action->handle($this->user, $currentIp);

    // Assert
    expect($count)->toBe(2);

    // Current session should remain active
    $currentSession = DB::table('authentication_logs')->find($currentSessionId);
    expect($currentSession->logout_at)->toBeNull()
        ->and($currentSession->cleared_by_user)->toBeFalse();

    // Other sessions should be logged out
    $other1 = DB::table('authentication_logs')->find($otherSession1);
    expect($other1->logout_at)->not->toBeNull()
        ->and($other1->cleared_by_user)->toBeTrue();

    $other2 = DB::table('authentication_logs')->find($otherSession2);
    expect($other2->logout_at)->not->toBeNull()
        ->and($other2->cleared_by_user)->toBeTrue();
});

it('returns zero when no other sessions exist', function () {
    $currentIp = '192.168.1.100';

    // Create only current session
    DB::table('authentication_logs')->insert([
        'authenticatable_id' => $this->user->id,
        'authenticatable_type' => User::class,
        'ip_address' => $currentIp,
        'user_agent' => 'Mozilla/5.0',
        'login_at' => now(),
        'login_successful' => true,
        'logout_at' => null,
        'cleared_by_user' => false,
    ]);

    // Act
    $count = $this->action->handle($this->user, $currentIp);

    // Assert
    expect($count)->toBe(0);
});

it('does not affect already logged out sessions', function () {
    $currentIp = '192.168.1.100';

    // Create session that's already logged out
    $loggedOutSessionId = DB::table('authentication_logs')->insertGetId([
        'authenticatable_id' => $this->user->id,
        'authenticatable_type' => User::class,
        'ip_address' => '192.168.1.101',
        'user_agent' => 'Mozilla/5.0',
        'login_at' => now()->subHours(3),
        'login_successful' => true,
        'logout_at' => now()->subHours(2),
        'cleared_by_user' => false,
    ]);

    // Create active session on other device
    DB::table('authentication_logs')->insert([
        'authenticatable_id' => $this->user->id,
        'authenticatable_type' => User::class,
        'ip_address' => '192.168.1.102',
        'user_agent' => 'Mozilla/5.0',
        'login_at' => now()->subHour(),
        'login_successful' => true,
        'logout_at' => null,
        'cleared_by_user' => false,
    ]);

    // Act
    $count = $this->action->handle($this->user, $currentIp);

    // Assert - should only count the active session
    expect($count)->toBe(1);
});

it('only logs out sessions belonging to the user', function () {
    $currentIp = '192.168.1.100';
    $otherUser = User::factory()->create();

    // Create session for other user
    $otherUserSessionId = DB::table('authentication_logs')->insertGetId([
        'authenticatable_id' => $otherUser->id,
        'authenticatable_type' => User::class,
        'ip_address' => '192.168.1.101',
        'user_agent' => 'Mozilla/5.0',
        'login_at' => now(),
        'login_successful' => true,
        'logout_at' => null,
        'cleared_by_user' => false,
    ]);

    // Act
    $count = $this->action->handle($this->user, $currentIp);

    // Assert
    expect($count)->toBe(0);

    // Verify other user's session wasn't affected
    $otherUserSession = DB::table('authentication_logs')->find($otherUserSessionId);
    expect($otherUserSession->logout_at)->toBeNull()
        ->and($otherUserSession->cleared_by_user)->toBeFalse();
});

it('logs activity when sessions are logged out', function () {
    $currentIp = '192.168.1.100';

    // Create other session
    DB::table('authentication_logs')->insert([
        'authenticatable_id' => $this->user->id,
        'authenticatable_type' => User::class,
        'ip_address' => '192.168.1.101',
        'user_agent' => 'Mozilla/5.0',
        'login_at' => now(),
        'login_successful' => true,
        'logout_at' => null,
        'cleared_by_user' => false,
    ]);

    // Act
    $this->action->handle($this->user, $currentIp);

    // Assert activity was logged
    $this->assertDatabaseHas('activity_log', [
        'causer_id' => $this->user->id,
        'causer_type' => User::class,
        'description' => 'Logged out from all other devices',
    ]);
});

it('does not log activity when no sessions were logged out', function () {
    $currentIp = '192.168.1.100';

    // Act - no other sessions exist
    $this->action->handle($this->user, $currentIp);

    // Assert no activity was logged
    $this->assertDatabaseMissing('activity_log', [
        'causer_id' => $this->user->id,
        'description' => 'Logged out from all other devices',
    ]);
});
