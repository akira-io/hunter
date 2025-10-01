<?php

declare(strict_types=1);

use App\Actions\Notifications\SendNotificationAction;
use App\Models\User;
use App\Notifications\UserFollowedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    Event::fake();
    $this->action = new SendNotificationAction();
    $this->user = User::factory()->create();
});

test('it sends a notification to a user', function () {
    Notification::fake();

    $follower = User::factory()->create();
    $notification = new UserFollowedNotification($follower);

    $this->action->handle($this->user, $notification);

    Notification::assertSentTo($this->user, UserFollowedNotification::class);
});

test('it creates notification in database', function () {
    $follower = User::factory()->create();
    $notification = new UserFollowedNotification($follower);

    $this->action->handle($this->user, $notification);

    expect($this->user->notifications()->count())->toBe(1);
});

test('it sends notifications to multiple users', function () {
    Notification::fake();

    $users = User::factory()->count(5)->create();
    $follower = User::factory()->create();
    $notification = new UserFollowedNotification($follower);

    $this->action->handleMultiple($users, $notification);

    foreach ($users as $user) {
        Notification::assertSentTo($user, UserFollowedNotification::class);
    }
});

test('it creates notifications for all users when sending to multiple', function () {
    $users = User::factory()->count(3)->create();
    $follower = User::factory()->create();
    $notification = new UserFollowedNotification($follower);

    $this->action->handleMultiple($users, $notification);

    foreach ($users as $user) {
        expect($user->notifications()->count())->toBe(1);
    }
});

test('it handles empty collection when sending to multiple users', function () {
    Notification::fake();

    $users = collect([]);
    $follower = User::factory()->create();
    $notification = new UserFollowedNotification($follower);

    // Should not throw error
    $this->action->handleMultiple($users, $notification);

    Notification::assertNothingSent();
});

test('it sends multiple different notifications to same user', function () {
    $follower1 = User::factory()->create();
    $follower2 = User::factory()->create();

    $this->action->handle($this->user, new UserFollowedNotification($follower1));
    $this->action->handle($this->user, new UserFollowedNotification($follower2));

    expect($this->user->notifications()->count())->toBe(2);
});
