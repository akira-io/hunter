<?php

declare(strict_types=1);

use App\Events\UserOffline;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    Event::fake();
});

it('identifies and cleans offline users correctly', function () {
    // Create test users
    $recentUser = User::factory()->create();
    $oldUser = User::factory()->create();
    $veryOldUser = User::factory()->create();
    $noLastSeenUser = User::factory()->create();

    // Set up cache with different timestamps (matching app logic exactly)
    Cache::put("user_online_{$recentUser->id}", now()->subMinutes(3), now()->addMinutes(10));
    Cache::put("user_online_{$oldUser->id}", now()->subMinutes(10), now()->addMinutes(10));
    Cache::put("user_online_{$veryOldUser->id}", now()->subMinutes(30), now()->addMinutes(10));
    // noLastSeenUser has no cache entry

    artisan('chat:clean-offline-users')
        ->expectsOutput('Cleaned offline users: 2')
        ->expectsOutput('Online users: 1')
        ->assertExitCode(0);

    // Verify offline users cache was cleared
    expect(Cache::get("user_online_{$oldUser->id}"))->toBeNull();
    expect(Cache::get("user_online_{$veryOldUser->id}"))->toBeNull();

    // Verify online user cache remains
    expect(Cache::has("user_online_{$recentUser->id}"))->toBeTrue();

    // Verify UserOffline events were dispatched for offline users
    Event::assertDispatched(UserOffline::class, function ($event) use ($oldUser) {
        return $event->user->id === $oldUser->id;
    });
    Event::assertDispatched(UserOffline::class, function ($event) use ($veryOldUser) {
        return $event->user->id === $veryOldUser->id;
    });

    // Verify no event for recent user or user with no cache
    Event::assertNotDispatched(UserOffline::class, function ($event) use ($recentUser) {
        return $event->user->id === $recentUser->id;
    });
    Event::assertNotDispatched(UserOffline::class, function ($event) use ($noLastSeenUser) {
        return $event->user->id === $noLastSeenUser->id;
    });

    // Total events should be 2
    Event::assertDispatchedTimes(UserOffline::class, 2);
});

it('handles users with no cache entries', function () {
    // Create users with no cache entries
    User::factory()->count(5)->create();

    artisan('chat:clean-offline-users')
        ->expectsOutput('Cleaned offline users: 0')
        ->expectsOutput('Online users: 0')
        ->assertExitCode(0);

    // No events should be dispatched
    Event::assertNotDispatched(UserOffline::class);
});

it('handles empty user database', function () {
    // No users in database
    expect(User::count())->toBe(0);

    artisan('chat:clean-offline-users')
        ->expectsOutput('Cleaned offline users: 0')
        ->expectsOutput('Online users: 0')
        ->assertExitCode(0);

    Event::assertNotDispatched(UserOffline::class);
});

it('handles users with exactly 5 minute threshold', function () {
    $exactlyFiveMinutesUser = User::factory()->create();

    // Just under 5 minutes ago should be considered online (threshold is <= 5)
    Cache::put("user_online_{$exactlyFiveMinutesUser->id}", now()->subMinutes(4)->subSeconds(59), now()->addMinutes(10));

    artisan('chat:clean-offline-users')
        ->expectsOutput('Cleaned offline users: 0')
        ->expectsOutput('Online users: 1')
        ->assertExitCode(0);

    // User should remain online (threshold is <= 5 minutes)
    expect(Cache::has("user_online_{$exactlyFiveMinutesUser->id}"))->toBeTrue();
    Event::assertNotDispatched(UserOffline::class);
});

it('handles users just over 5 minute threshold', function () {
    $justOverThresholdUser = User::factory()->create();

    // Just over 5 minutes (6 minutes to account for test execution time) should be considered offline
    Cache::put("user_online_{$justOverThresholdUser->id}", now()->subMinutes(6), now()->addMinutes(10));

    artisan('chat:clean-offline-users')
        ->expectsOutput('Cleaned offline users: 1')
        ->expectsOutput('Online users: 0')
        ->assertExitCode(0);

    // User should go offline
    expect(Cache::get("user_online_{$justOverThresholdUser->id}"))->toBeNull();
    Event::assertDispatched(UserOffline::class, function ($event) use ($justOverThresholdUser) {
        return $event->user->id === $justOverThresholdUser->id;
    });
});

it('processes large number of users in chunks', function () {
    // Create more than 100 users to test chunking
    $users = User::factory()->count(250)->create();

    // Set some users as online and some as offline
    foreach ($users->take(50) as $user) {
        Cache::put("user_online_{$user->id}", now()->subMinutes(3), now()->addMinutes(10)); // Online
    }

    foreach ($users->skip(50)->take(100) as $user) {
        Cache::put("user_online_{$user->id}", now()->subMinutes(10), now()->addMinutes(10)); // Offline
    }

    // Remaining 100 users have no cache entries

    artisan('chat:clean-offline-users')
        ->expectsOutput('Cleaned offline users: 100')
        ->expectsOutput('Online users: 50')
        ->assertExitCode(0);

    Event::assertDispatchedTimes(UserOffline::class, 100);
});

it('handles non-Carbon cache values gracefully', function () {
    $user = User::factory()->create();

    // Put a non-Carbon value in cache (shouldn't happen in real app, but testing robustness)
    Cache::put("user_online_{$user->id}", 'invalid-value');

    artisan('chat:clean-offline-users')
        ->expectsOutput('Cleaned offline users: 0')
        ->expectsOutput('Online users: 0')
        ->assertExitCode(0);

    // Non-Carbon values should be ignored
    Event::assertNotDispatched(UserOffline::class);
});

it('handles mixed valid and invalid cache values', function () {
    $validUser = User::factory()->create();
    $invalidUser = User::factory()->create();

    Cache::put("user_online_{$validUser->id}", now()->subMinutes(10), now()->addMinutes(10)); // Valid Carbon, offline
    Cache::put("user_online_{$invalidUser->id}", 'invalid'); // Invalid value

    artisan('chat:clean-offline-users')
        ->expectsOutput('Cleaned offline users: 1')
        ->expectsOutput('Online users: 0')
        ->assertExitCode(0);

    // Only valid user should trigger event
    Event::assertDispatchedTimes(UserOffline::class, 1);
    Event::assertDispatched(UserOffline::class, function ($event) use ($validUser) {
        return $event->user->id === $validUser->id;
    });
});
