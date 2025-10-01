<?php

declare(strict_types=1);

use App\Actions\Notifications\GetUserNotificationsAction;
use App\Models\User;
use App\Notifications\UserFollowedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    Event::fake();
    $this->action = new GetUserNotificationsAction();
    $this->user = User::factory()->create();
});

test('it returns empty array when user has no notifications', function () {
    $result = $this->action->handle($this->user);

    expect($result)->toHaveKeys(['notifications', 'unread_count'])
        ->and($result['notifications'])->toBeArray()->toBeEmpty()
        ->and($result['unread_count'])->toBe(0);
});

test('it returns unread notifications only by default', function () {
    $follower = User::factory()->create();

    // Create notifications
    $this->user->notify(new UserFollowedNotification($follower));
    $this->user->notify(new UserFollowedNotification($follower));
    $this->user->notify(new UserFollowedNotification($follower));

    // Mark one as read
    $this->user->notifications()->first()->markAsRead();

    $result = $this->action->handle($this->user, unreadOnly: true);

    expect($result['notifications'])->toHaveCount(2)
        ->and($result['unread_count'])->toBe(2);
});

test('it returns all notifications when unread only is false', function () {
    $follower = User::factory()->create();

    // Create notifications
    $this->user->notify(new UserFollowedNotification($follower));
    $this->user->notify(new UserFollowedNotification($follower));

    // Mark one as read
    $this->user->notifications()->first()->markAsRead();

    $result = $this->action->handle($this->user, unreadOnly: false);

    expect($result['notifications'])->toHaveCount(2)
        ->and($result['unread_count'])->toBe(1);
});

test('it respects the limit parameter', function () {
    $follower = User::factory()->create();

    // Create 10 notifications
    for ($i = 0; $i < 10; $i++) {
        $this->user->notify(new UserFollowedNotification($follower));
    }

    $result = $this->action->handle($this->user, limit: 5);

    expect($result['notifications'])->toHaveCount(5)
        ->and($result['unread_count'])->toBe(10);
});

test('it formats notification data correctly', function () {
    $follower = User::factory()->create([
        'name' => 'John Doe',
        'user_name' => 'johndoe',
    ]);

    $this->user->notify(new UserFollowedNotification($follower));

    $result = $this->action->handle($this->user);

    $notification = $result['notifications'][0];

    expect($notification)->toHaveKeys(['id', 'type', 'title', 'message', 'data', 'read_at', 'created_at', 'created_at_human'])
        ->and($notification['type'])->toBe('follow')
        ->and($notification['title'])->toBe('Novo seguidor')
        ->and($notification['read_at'])->toBeNull();
});

test('it returns notifications in latest order', function () {
    $follower = User::factory()->create();

    // Create notifications with slight delay
    $this->user->notify(new UserFollowedNotification($follower));
    sleep(1);
    $this->user->notify(new UserFollowedNotification($follower));

    $result = $this->action->handle($this->user, unreadOnly: false);

    $notifications = $result['notifications'];
    expect($notifications)->toHaveCount(2);

    // Latest notification should be first
    $firstCreatedAt = strtotime($notifications[0]['created_at']);
    $secondCreatedAt = strtotime($notifications[1]['created_at']);

    expect($firstCreatedAt)->toBeGreaterThanOrEqual($secondCreatedAt);
});

test('it includes read_at timestamp when notification is read', function () {
    $follower = User::factory()->create();

    $this->user->notify(new UserFollowedNotification($follower));

    // Mark as read
    $this->user->notifications()->first()->markAsRead();

    $result = $this->action->handle($this->user, unreadOnly: false);

    expect($result['notifications'][0]['read_at'])->not->toBeNull();
});
