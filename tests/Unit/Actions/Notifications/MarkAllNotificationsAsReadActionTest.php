<?php

declare(strict_types=1);

use App\Actions\Notifications\MarkAllNotificationsAsReadAction;
use App\Models\User;
use App\Notifications\UserFollowedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    Event::fake();
    $this->action = new MarkAllNotificationsAsReadAction();
    $this->user = User::factory()->create();
});

test('it marks all unread notifications as read', function () {
    $follower = User::factory()->create();

    // Create multiple notifications
    for ($i = 0; $i < 5; $i++) {
        $this->user->notify(new UserFollowedNotification($follower));
    }

    expect($this->user->unreadNotifications()->count())->toBe(5);

    $this->action->handle($this->user);

    expect($this->user->unreadNotifications()->count())->toBe(0)
        ->and($this->user->notifications()->whereNotNull('read_at')->count())->toBe(5);
});

test('it does not affect already read notifications', function () {
    $follower = User::factory()->create();

    // Create notifications
    $this->user->notify(new UserFollowedNotification($follower));
    $this->user->notify(new UserFollowedNotification($follower));

    // Mark one as read
    $firstNotification = $this->user->notifications()->first();
    $firstNotification->markAsRead();
    $firstReadAt = $firstNotification->read_at;

    sleep(1);

    $this->action->handle($this->user);

    // Verify first notification's read_at didn't change
    $firstNotification->refresh();
    expect($firstNotification->read_at->timestamp)->toBe($firstReadAt->timestamp);
});

test('it handles user with no notifications', function () {
    expect($this->user->notifications()->count())->toBe(0);

    // Should not throw an error
    $this->action->handle($this->user);

    expect($this->user->notifications()->count())->toBe(0);
});

test('it only affects the specified user notifications', function () {
    $otherUser = User::factory()->create();
    $follower = User::factory()->create();

    // Create notifications for both users
    $this->user->notify(new UserFollowedNotification($follower));
    $otherUser->notify(new UserFollowedNotification($follower));

    $this->action->handle($this->user);

    // User's notifications should be read
    expect($this->user->unreadNotifications()->count())->toBe(0);

    // Other user's notifications should still be unread
    expect($otherUser->unreadNotifications()->count())->toBe(1);
});

test('it updates notifications in database', function () {
    $follower = User::factory()->create();

    for ($i = 0; $i < 3; $i++) {
        $this->user->notify(new UserFollowedNotification($follower));
    }

    $notificationIds = $this->user->notifications()->pluck('id')->toArray();

    $this->action->handle($this->user);

    // Verify all notifications have read_at in database
    foreach ($notificationIds as $id) {
        $this->assertDatabaseHas('notifications', [
            'id' => $id,
        ]);

        $notification = $this->user->notifications()->find($id);
        expect($notification->read_at)->not->toBeNull();
    }
});
