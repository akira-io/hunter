<?php

declare(strict_types=1);

use App\Actions\Notifications\GetUnreadNotificationCountAction;
use App\Models\User;
use App\Notifications\UserFollowedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    Event::fake();
    $this->action = new GetUnreadNotificationCountAction();
    $this->user = User::factory()->create();
});

test('it returns zero when user has no notifications', function () {
    $count = $this->action->handle($this->user);

    expect($count)->toBe(0);
});

test('it returns correct count of unread notifications', function () {
    $follower = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->user->notify(new UserFollowedNotification($follower));
    }

    $count = $this->action->handle($this->user);

    expect($count)->toBe(5);
});

test('it does not count read notifications', function () {
    $follower = User::factory()->create();

    // Create 5 notifications
    for ($i = 0; $i < 5; $i++) {
        $this->user->notify(new UserFollowedNotification($follower));
    }

    // Mark 2 as read
    $this->user->notifications()->limit(2)->get()->markAsRead();

    $count = $this->action->handle($this->user);

    expect($count)->toBe(3);
});

test('it returns zero when all notifications are read', function () {
    $follower = User::factory()->create();

    for ($i = 0; $i < 3; $i++) {
        $this->user->notify(new UserFollowedNotification($follower));
    }

    // Mark all as read
    $this->user->unreadNotifications->markAsRead();

    $count = $this->action->handle($this->user);

    expect($count)->toBe(0);
});

test('it only counts notifications for the specified user', function () {
    $otherUser = User::factory()->create();
    $follower = User::factory()->create();

    // Create notifications for both users
    for ($i = 0; $i < 3; $i++) {
        $this->user->notify(new UserFollowedNotification($follower));
    }

    for ($i = 0; $i < 2; $i++) {
        $otherUser->notify(new UserFollowedNotification($follower));
    }

    $count = $this->action->handle($this->user);

    expect($count)->toBe(3);
});

test('it updates count after marking notifications as read', function () {
    $follower = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->user->notify(new UserFollowedNotification($follower));
    }

    expect($this->action->handle($this->user))->toBe(5);

    // Mark one as read
    $this->user->notifications()->first()->markAsRead();

    expect($this->action->handle($this->user))->toBe(4);
});
