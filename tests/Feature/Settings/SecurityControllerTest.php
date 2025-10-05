<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(function () {

    $this->user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);
});

describe('Security Page', function () {
    it('can be rendered for authenticated users', function () {
        $response = actingAs($this->user)
            ->get('/settings/security');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('settings/security')
            ->has('activeSessions')
            ->has('connectedAccounts')
            ->has('hasPassword'));
    });

    it('redirects unauthenticated users', function () {
        $response = get('/settings/security');

        $response->assertRedirect('/login');
    });

    it('shows active sessions', function () {
        // Create active sessions
        DB::table('authentication_logs')->insert([
            'authenticatable_id' => $this->user->id,
            'authenticatable_type' => User::class,
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0',
            'login_at' => now(),
            'login_successful' => true,
            'logout_at' => null,
            'cleared_by_user' => false,
        ]);

        $response = actingAs($this->user)
            ->get('/settings/security');

        $response->assertInertia(fn ($page) => $page
            ->component('settings/security')
            ->has('activeSessions', 1));
    });

    it('shows connected accounts status', function () {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'github_id' => 'github-123',
        ]);

        $response = actingAs($user)
            ->get('/settings/security');

        $response->assertInertia(fn ($page) => $page
            ->component('settings/security')
            ->where('connectedAccounts.github', true)
            ->where('connectedAccounts.google', false));
    });
});

describe('Revoke Session', function () {
    it('can revoke a specific session', function () {
        // Create session to revoke
        $sessionId = DB::table('authentication_logs')->insertGetId([
            'authenticatable_id' => $this->user->id,
            'authenticatable_type' => User::class,
            'ip_address' => '192.168.1.101',
            'user_agent' => 'Mozilla/5.0',
            'login_at' => now(),
            'login_successful' => true,
            'logout_at' => null,
            'cleared_by_user' => false,
        ]);

        $response = actingAs($this->user)
            ->delete("/settings/security/sessions/{$sessionId}");

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Sessão revogada com sucesso.');

        // Verify session was logged out
        $session = DB::table('authentication_logs')->find($sessionId);
        expect($session->logout_at)->not->toBeNull()
            ->and($session->cleared_by_user)->toBeTrue();
    });

    it('logs activity when session is revoked', function () {
        $sessionId = DB::table('authentication_logs')->insertGetId([
            'authenticatable_id' => $this->user->id,
            'authenticatable_type' => User::class,
            'ip_address' => '192.168.1.101',
            'user_agent' => 'Mozilla/5.0',
            'login_at' => now(),
            'login_successful' => true,
            'logout_at' => null,
            'cleared_by_user' => false,
        ]);

        actingAs($this->user)
            ->delete("/settings/security/sessions/{$sessionId}");

        assertDatabaseHas('activity_log', [
            'causer_id' => $this->user->id,
            'causer_type' => User::class,
            'description' => 'Session revoked',
        ]);
    });

    it('shows error for non-existent session', function () {
        $response = actingAs($this->user)
            ->delete('/settings/security/sessions/99999');

        $response->assertRedirect();
        $response->assertSessionHas('error');
    });

    it('requires authentication', function () {
        $response = delete('/settings/security/sessions/1');

        $response->assertRedirect('/login');
    });
});

describe('Logout Other Devices', function () {
    it('can logout from all other devices', function () {
        // Create current session
        DB::table('authentication_logs')->insert([
            'authenticatable_id' => $this->user->id,
            'authenticatable_type' => User::class,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
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
            'user_agent' => 'Mozilla/5.0',
            'login_at' => now(),
            'login_successful' => true,
            'logout_at' => null,
            'cleared_by_user' => false,
        ]);

        $otherSession2 = DB::table('authentication_logs')->insertGetId([
            'authenticatable_id' => $this->user->id,
            'authenticatable_type' => User::class,
            'ip_address' => '192.168.1.102',
            'user_agent' => 'Mozilla/5.0',
            'login_at' => now(),
            'login_successful' => true,
            'logout_at' => null,
            'cleared_by_user' => false,
        ]);

        $response = actingAs($this->user)
            ->post('/settings/security/logout-other-devices');

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify other sessions were logged out
        $session1 = DB::table('authentication_logs')->find($otherSession1);
        expect($session1->logout_at)->not->toBeNull();

        $session2 = DB::table('authentication_logs')->find($otherSession2);
        expect($session2->logout_at)->not->toBeNull();
    });

    it('shows info message when no other sessions exist', function () {
        $response = actingAs($this->user)
            ->post('/settings/security/logout-other-devices');

        $response->assertRedirect();
        $response->assertSessionHas('info', 'Não há outras sessões ativas.');
    });

    it('logs activity when logging out other devices', function () {
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

        actingAs($this->user)
            ->post('/settings/security/logout-other-devices');

        assertDatabaseHas('activity_log', [
            'causer_id' => $this->user->id,
            'description' => 'Logged out from all other devices',
        ]);
    });

    it('requires authentication', function () {
        $response = post('/settings/security/logout-other-devices');

        $response->assertRedirect('/login');
    });
});

describe('Disconnect OAuth Account', function () {
    it('can disconnect github account', function () {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'github_id' => 'github-123',
            'github_token' => 'github-token',
            'github_refresh_token' => 'github-refresh',
        ]);

        $response = actingAs($user)
            ->delete('/settings/security/accounts/github');

        $response->assertRedirect();
        $response->assertSessionHas('success');

        assertDatabaseHas('users', [
            'id' => $user->id,
            'github_id' => null,
            'github_token' => null,
            'github_refresh_token' => null,
        ]);
    });

    it('can disconnect google account', function () {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'google_id' => 'google-123',
            'google_token' => 'google-token',
            'google_refresh_token' => 'google-refresh',
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('security.disconnect-account', ['provider' => 'google']));

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'google_id' => null,
            'google_token' => null,
            'google_refresh_token' => null,
        ]);
    });

    it('shows error for invalid provider', function () {
        $response = actingAs($this->user)
            ->delete('/settings/security/accounts/invalid');

        $response->assertRedirect();
        $response->assertSessionHas('error');
    });

    it('logs activity when account is disconnected', function () {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'github_id' => 'github-123',
        ]);

        actingAs($user)
            ->delete('/settings/security/accounts/github');

        assertDatabaseHas('activity_log', [
            'causer_id' => $user->id,
            'description' => 'Github account disconnected',
        ]);
    });

    it('requires authentication', function () {
        $response = delete('/settings/security/accounts/github');

        $response->assertRedirect('/login');
    });
});
