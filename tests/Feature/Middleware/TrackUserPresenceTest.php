<?php

declare(strict_types=1);

use App\Events\ConversationsSnapshot;
use App\Events\UserOnline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('tracks authenticated user presence and broadcasts events on first request', function () {
    Event::fake();

    $user = actingAsAuthUser();

    $cacheKey = "user_online_{$user->id}";

    // Ensure cache is clear
    Cache::forget($cacheKey);

    $response = $this->get(route('home'));

    $response->assertStatus(200);

    // Verify cache was set
    expect(Cache::has($cacheKey))->toBeTrue();

    // Verify events were dispatched
    Event::assertDispatched(UserOnline::class, function ($event) use ($user) {
        return $event->user->id === $user->id;
    });

    Event::assertDispatched(ConversationsSnapshot::class, function ($event) use ($user) {
        return $event->user->id === $user->id;
    });
});

it('refreshes cache but does not broadcast events on subsequent requests', function () {
    Event::fake();

    $user = actingAsAuthUser();

    $cacheKey = "user_online_{$user->id}";

    // Simulate user already online by setting cache
    Cache::put($cacheKey, now(), now()->addMinutes(5));

    $response = $this->get(route('home'));

    $response->assertStatus(200);

    // Verify cache is still set
    expect(Cache::has($cacheKey))->toBeTrue();

    // Verify events were NOT dispatched since user was already online
    Event::assertNotDispatched(UserOnline::class);
    Event::assertNotDispatched(ConversationsSnapshot::class);
});

it('does not track presence for unauthenticated users', function () {
    Event::fake();

    $response = $this->get('/');

    $response->assertStatus(200);

    // Verify no events were dispatched
    Event::assertNotDispatched(UserOnline::class);
    Event::assertNotDispatched(ConversationsSnapshot::class);
});
