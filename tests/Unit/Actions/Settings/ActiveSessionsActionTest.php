<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Settings;

use App\Actions\Settings\ActiveSessionsAction;
use App\Models\User;
use Akira\LaravelAuthLogs\AuthenticationLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActiveSessionsActionTest extends TestCase
{
    use RefreshDatabase;

    private ActiveSessionsAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new ActiveSessionsAction();
    }

    public function test_returns_only_most_recent_session_per_ip(): void
    {
        $user = User::factory()->create();
        $sameIp = '192.168.1.1';

        // Create multiple sessions with the same IP at different times
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

        // Should return only 1 session (the most recent one)
        $this->assertCount(1, $sessions);
        $this->assertEquals($recentSession->id, $sessions->first()['id']);
        $this->assertEquals($sameIp, $sessions->first()['ip_address']);
    }

    public function test_returns_multiple_sessions_with_different_ips(): void
    {
        $user = User::factory()->create();

        // Create sessions with different IPs
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

        // Should return both sessions since they have different IPs
        $this->assertCount(2, $sessions);
    }

    public function test_excludes_logged_out_sessions(): void
    {
        $user = User::factory()->create();

        // Create active session
        $activeSession = AuthenticationLog::forceCreate([
            'authenticatable_type' => User::class,
            'authenticatable_id' => $user->id,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0 (Active)',
            'login_at' => now(),
            'logout_at' => null,
        ]);

        // Create logged out session
        $loggedOutSession = AuthenticationLog::forceCreate([
            'authenticatable_type' => User::class,
            'authenticatable_id' => $user->id,
            'ip_address' => '192.168.1.2',
            'user_agent' => 'Mozilla/5.0 (Logged Out)',
            'login_at' => now()->subHour(),
            'logout_at' => now(),
        ]);

        $sessions = $this->action->handle($user);

        // Should return only the active session
        $this->assertCount(1, $sessions);
        $this->assertEquals($activeSession->id, $sessions->first()['id']);
    }

    public function test_marks_current_session_correctly(): void
    {
        $user = User::factory()->create();
        $currentIp = request()->ip();

        // Create session with current IP
        AuthenticationLog::forceCreate([
            'authenticatable_type' => User::class,
            'authenticatable_id' => $user->id,
            'ip_address' => $currentIp,
            'user_agent' => 'Mozilla/5.0 (Current)',
            'login_at' => now(),
            'logout_at' => null,
        ]);

        // Create session with different IP
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

        $this->assertTrue($currentSession['is_current']);
        $this->assertFalse($otherSession['is_current']);
    }

    public function test_handles_duplicate_ips_and_returns_most_recent(): void
    {
        $user = User::factory()->create();
        $ip1 = '192.168.1.1';
        $ip2 = '192.168.1.2';

        // Create 3 sessions for IP1 (should keep most recent)
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

        // Create 2 sessions for IP2 (should keep most recent)
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

        // Should return only 2 sessions (one per IP)
        $this->assertCount(2, $sessions);
        
        $sessionIds = $sessions->pluck('id')->toArray();
        $this->assertContains($mostRecentIp1->id, $sessionIds);
        $this->assertContains($mostRecentIp2->id, $sessionIds);
    }
}
