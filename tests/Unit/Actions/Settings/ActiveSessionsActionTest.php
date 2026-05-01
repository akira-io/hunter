<?php

declare(strict_types=1);

use Akira\LaravelAuthLogs\AuthenticationLog;
use App\Actions\Settings\ActiveSessionsAction;
use App\Models\User;

beforeEach(function () {
    $this->action = new ActiveSessionsAction();
});

it('returns only most recent session per ip', function () {
    $user = User::factory()->create();
    $sameIp = '192.168.1.1';

    $oldSession = AuthenticationLog::forceCreate([
        'authenticatable_type' => User::class,
        'authenticatable_id' => $user->id,
        'ip_address' => $sameIp,
        'user_agent' => 'Mozilla/5.0 (Old Browser)',
        'login_at' => now()->subHours(2),
        'logout_at' => null,
    ]);

    $middleSession = AuthenticationLog::forceCreate([
        'authenticatable_type' => User::class,
        'authenticatable_id' => $user->id,
        'ip_address' => $sameIp,
        'user_agent' => 'Mozilla/5.0 (Middle Browser)',
        'login_at' => now()->subHour(),
        'logout_at' => null,
    ]);

    $recentSession = AuthenticationLog::forceCreate([
        'authenticatable_type' => User::class,
        'authenticatable_id' => $user->id,
        'ip_address' => $sameIp,
        'user_agent' => 'Mozilla/5.0 (Recent Browser)',
        'login_at' => now(),
        'logout_at' => null,
    ]);

    $sessions = $this->action->handle($user);

    expect($sessions->count())->toBe(1);
    expect($sessions->first()['id'])->toBe($recentSession->id);
    expect($sessions->first()['ip_address'])->toBe($sameIp);
});

it('returns multiple sessions with different ips', function () {
    $user = User::factory()->create();

    $session1 = AuthenticationLog::forceCreate([
        'authenticatable_type' => User::class,
        'authenticatable_id' => $user->id,
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0 (Browser 1)',
        'login_at' => now()->subHour(),
        'logout_at' => null,
    ]);

    $session2 = AuthenticationLog::forceCreate([
        'authenticatable_type' => User::class,
        'authenticatable_id' => $user->id,
        'ip_address' => '192.168.1.2',
        'user_agent' => 'Mozilla/5.0 (Browser 2)',
        'login_at' => now(),
        'logout_at' => null,
    ]);

    $sessions = $this->action->handle($user);

    expect($sessions->count())->toBe(2);
});

it('excludes logged out sessions', function () {
    $user = User::factory()->create();

    $activeSession = AuthenticationLog::forceCreate([
        'authenticatable_type' => User::class,
        'authenticatable_id' => $user->id,
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0 (Active)',
        'login_at' => now(),
        'logout_at' => null,
    ]);

    $loggedOutSession = AuthenticationLog::forceCreate([
        'authenticatable_type' => User::class,
        'authenticatable_id' => $user->id,
        'ip_address' => '192.168.1.2',
        'user_agent' => 'Mozilla/5.0 (Logged Out)',
        'login_at' => now()->subHour(),
        'logout_at' => now(),
    ]);

    $sessions = $this->action->handle($user);

    expect($sessions->count())->toBe(1);
    expect($sessions->first()['id'])->toBe($activeSession->id);
});

it('marks current session correctly', function () {
    $user = User::factory()->create();
    $currentIp = request()->ip();

    AuthenticationLog::forceCreate([
        'authenticatable_type' => User::class,
        'authenticatable_id' => $user->id,
        'ip_address' => $currentIp,
        'user_agent' => 'Mozilla/5.0 (Current)',
        'login_at' => now(),
        'logout_at' => null,
    ]);

    AuthenticationLog::forceCreate([
        'authenticatable_type' => User::class,
        'authenticatable_id' => $user->id,
        'ip_address' => '192.168.1.99',
        'user_agent' => 'Mozilla/5.0 (Other)',
        'login_at' => now(),
        'logout_at' => null,
    ]);

    $sessions = $this->action->handle($user);

    $currentSession = $sessions->firstWhere('ip_address', $currentIp);
    $otherSession = $sessions->firstWhere('ip_address', '192.168.1.99');

    expect($currentSession['is_current'])->toBeTrue();
    expect($otherSession['is_current'])->toBeFalse();
});

it('handles duplicate ips and returns most recent', function () {
    $user = User::factory()->create();
    $ip1 = '192.168.1.1';
    $ip2 = '192.168.1.2';

    AuthenticationLog::forceCreate([
        'authenticatable_type' => User::class,
        'authenticatable_id' => $user->id,
        'ip_address' => $ip1,
        'user_agent' => 'Mozilla/5.0 (Old)',
        'login_at' => now()->subHours(3),
        'logout_at' => null,
    ]);

    AuthenticationLog::forceCreate([
        'authenticatable_type' => User::class,
        'authenticatable_id' => $user->id,
        'ip_address' => $ip1,
        'user_agent' => 'Mozilla/5.0 (Middle)',
        'login_at' => now()->subHours(2),
        'logout_at' => null,
    ]);

    $mostRecentIp1 = AuthenticationLog::forceCreate([
        'authenticatable_type' => User::class,
        'authenticatable_id' => $user->id,
        'ip_address' => $ip1,
        'user_agent' => 'Mozilla/5.0 (Recent)',
        'login_at' => now()->subHour(),
        'logout_at' => null,
    ]);

    AuthenticationLog::forceCreate([
        'authenticatable_type' => User::class,
        'authenticatable_id' => $user->id,
        'ip_address' => $ip2,
        'user_agent' => 'Mozilla/5.0 (Old IP2)',
        'login_at' => now()->subMinutes(30),
        'logout_at' => null,
    ]);

    $mostRecentIp2 = AuthenticationLog::forceCreate([
        'authenticatable_type' => User::class,
        'authenticatable_id' => $user->id,
        'ip_address' => $ip2,
        'user_agent' => 'Mozilla/5.0 (Recent IP2)',
        'login_at' => now(),
        'logout_at' => null,
    ]);

    $sessions = $this->action->handle($user);

    expect($sessions->count())->toBe(2);

    $sessionIds = $sessions->pluck('id')->toArray();
    expect($sessionIds)->toContain($mostRecentIp1->id);
    expect($sessionIds)->toContain($mostRecentIp2->id);
});
