<?php

declare(strict_types=1);

use App\Events\UserOffline;
use App\Events\UserOnline;
use App\Models\User;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\actingAs;

it('broadcasts UserOnline on first authenticated request and UserOffline on logout', function () {
    Event::fake();

    $user = User::factory()->create();

    actingAs($user);

    // Trigger middleware TrackUserPresence by hitting a web route
    $this->get('/')->assertOk();

    Event::assertDispatched(UserOnline::class, function (UserOnline $event) use ($user) {
        return $event->user->id === $user->id && $event->broadcastAs() === 'user.online';
    });

    // Now log out
    $this->post('/logout')->assertRedirect('/');

    Event::assertDispatched(UserOffline::class, function (UserOffline $event) use ($user) {
        return $event->user->id === $user->id && $event->broadcastAs() === 'user.offline';
    });
});
