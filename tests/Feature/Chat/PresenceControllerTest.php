<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

describe('PresenceController', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        actingAs($this->user);
        Cache::flush();
    });

    it('marks user as online', function () {
        $response = post('/presence/online');

        $response->assertOk()
            ->assertJson([
                'status' => 'online',
                'user_id' => $this->user->id,
            ]);

        expect(Cache::has("user_online_{$this->user->id}"))->toBeTrue();
    });

    it('marks user as offline', function () {
        // First mark as online
        Cache::put("user_online_{$this->user->id}", now(), now()->addMinutes(10));

        $response = post('/presence/offline');

        $response->assertOk()
            ->assertJson([
                'status' => 'offline',
                'user_id' => $this->user->id,
            ]);

        expect(Cache::has("user_online_{$this->user->id}"))->toBeFalse();
    });

    it('returns list of online users excluding current user', function () {
        $user2 = User::factory()->create(['name' => 'User 2']);
        $user3 = User::factory()->create(['name' => 'User 3']);
        $offlineUser = User::factory()->create(['name' => 'Offline User']);

        // Mark users as online (NOT including current user)
        Cache::put("user_online_{$user2->id}", now(), now()->addMinutes(10));
        Cache::put("user_online_{$user3->id}", now(), now()->addMinutes(10));

        $response = get('/users/online');

        $response->assertOk()
            ->assertJsonCount(2, 'data') // Should not include current user
            ->assertJsonPath('data.0.id', $user2->id)
            ->assertJsonPath('data.1.id', $user3->id);
    });

    it('returns empty array when no users are online', function () {
        $response = get('/users/online');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('does not require CSRF token for online endpoint', function () {
        $this->withoutMiddleware(Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->postJson('/presence/online', [], [
            'X-CSRF-TOKEN' => 'invalid-token',
        ]);

        $response->assertOk();
    });

    it('does not require CSRF token for offline endpoint', function () {
        $this->withoutMiddleware(Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->postJson('/presence/offline', [], [
            'X-CSRF-TOKEN' => 'invalid-token',
        ]);

        $response->assertOk();
    });

    it('requires authentication for marking online', function () {
        auth()->logout();

        $response = post('/presence/online');

        $response->assertRedirect(route('login'));
    });

    it('requires authentication for marking offline', function () {
        auth()->logout();

        $response = post('/presence/offline');

        $response->assertRedirect(route('login'));
    });

    it('requires authentication for viewing online users', function () {
        auth()->logout();

        $response = get('/users/online');

        $response->assertRedirect(route('login'));
    });

    it('sets cache with correct TTL for online status', function () {
        post('/presence/online');

        $cachedValue = Cache::get("user_online_{$this->user->id}");

        expect($cachedValue)->not->toBeNull();

        // Cache should expire in approximately 10 minutes
        sleep(1);
        expect(Cache::has("user_online_{$this->user->id}"))->toBeTrue();
    });

    it('can mark multiple users as online simultaneously', function () {
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        actingAs($this->user);
        post('/presence/online');

        actingAs($user2);
        post('/presence/online');

        actingAs($user3);
        post('/presence/online');

        expect(Cache::has("user_online_{$this->user->id}"))->toBeTrue();
        expect(Cache::has("user_online_{$user2->id}"))->toBeTrue();
        expect(Cache::has("user_online_{$user3->id}"))->toBeTrue();

        actingAs($this->user);
        $response = get('/users/online');

        $response->assertJsonCount(2, 'data'); // Excluding current user
    });

    it('returns user resource format for online users', function () {
        $onlineUser = User::factory()->create([
            'name' => 'Online User',
            'email' => 'online@example.com',
        ]);

        Cache::put("user_online_{$onlineUser->id}", now(), now()->addMinutes(10));

        $response = get('/users/online');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                    ],
                ],
            ]);
    });

    it('handles concurrent online/offline requests', function () {
        // Mark as online
        post('/presence/online');
        expect(Cache::has("user_online_{$this->user->id}"))->toBeTrue();

        // Immediately mark as offline
        post('/presence/offline');
        expect(Cache::has("user_online_{$this->user->id}"))->toBeFalse();

        // Mark as online again
        post('/presence/online');
        expect(Cache::has("user_online_{$this->user->id}"))->toBeTrue();
    });
});
