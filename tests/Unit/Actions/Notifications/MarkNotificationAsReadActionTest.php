<?php

declare(strict_types=1);

use App\Actions\Notifications\MarkNotificationAsReadAction;
use App\Models\User;
use App\Notifications\UserFollowedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    Event::fake();
    $this->action = new MarkNotificationAsReadAction();
    $this->user = User::factory()->create();
});

test('it marks a notification as read', function () {
    $follower = User::factory()->create();
    $this->user->notify(new UserFollowedNotification($follower));

    $notification = $this->user->notifications()->first();
    expect($notification->read_at)->toBeNull();

    $result = $this->action->handle($this->user, $notification->id);

    expect($result)->not->toBeNull()
        ->and($result->read_at)->not->toBeNull();
});

test('it returns null when notification not found', function () {
    // Use a valid UUID format but one that doesn't exist
    $nonExistentUuid = '00000000-0000-0000-0000-000000000000';

    $result = $this->action->handle($this->user, $nonExistentUuid);

    expect($result)->toBeNull();
});

test('it only marks notifications belonging to the user', function () {
    $otherUser = User::factory()->create();
    $follower = User::factory()->create();

    $otherUser->notify(new UserFollowedNotification($follower));
    $otherNotification = $otherUser->notifications()->first();

    $result = $this->action->handle($this->user, $otherNotification->id);

    expect($result)->toBeNull();

    // Verify the other user's notification was not marked as read
    $otherNotification->refresh();
    expect($otherNotification->read_at)->toBeNull();
});

test('it does not fail when marking already read notification', function () {
    $follower = User::factory()->create();
    $this->user->notify(new UserFollowedNotification($follower));

    $notification = $this->user->notifications()->first();
    $notification->markAsRead();

    $firstReadAt = $notification->read_at;

    // Try to mark as read again
    $result = $this->action->handle($this->user, $notification->id);

    expect($result)->not->toBeNull()
        ->and($result->read_at)->toEqual($firstReadAt);
});

test('it updates the notification in database', function () {
    $follower = User::factory()->create();
    $this->user->notify(new UserFollowedNotification($follower));

    $notification = $this->user->notifications()->first();

    $this->action->handle($this->user, $notification->id);

    $this->assertDatabaseHas('notifications', [
        'id' => $notification->id,
    ]);

    // Verify read_at is not null in database
    $updatedNotification = $this->user->notifications()->find($notification->id);
    expect($updatedNotification->read_at)->not->toBeNull();
});
