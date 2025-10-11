<?php

declare(strict_types=1);

use Akira\Followable\Exceptions\CannotFollowYourSelfException;
use App\Actions\Social\FollowUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it can follow a user', function () {
    Notification::fake();

    $follower = User::factory()->create();
    $userToFollow = User::factory()->create();
    $action = new FollowUserAction();

    $action->handle($follower, $userToFollow);

    expect($follower->isFollowing($userToFollow))->toBeTrue()
        ->and($userToFollow->isFollowedBy($follower))->toBeTrue();
});

test('it prevents following yourself', function () {
    $user = User::factory()->create();
    $action = new FollowUserAction();

    expect(function () use ($action, $user) {
        $action->handle($user, $user);
    })->toThrow(CannotFollowYourSelfException::class);
});

test('it sends notification when follow notifications are enabled', function () {
    Notification::fake();

    $follower = User::factory()->create();
    $userToFollow = User::factory()->create([
        'notification_settings' => ['follow_notifications' => true],
    ]);
    $action = new FollowUserAction();

    $action->handle($follower, $userToFollow);

    Notification::assertSentTo(
        $userToFollow,
        App\Notifications\UserFollowedNotification::class,
        function ($notification, $channels) {
            return in_array('database', $channels);
        }
    );
});

test('it does not send in-app notification when follow notifications are disabled', function () {
    Notification::fake();

    $follower = User::factory()->create();
    $userToFollow = User::factory()->create([
        'notification_settings' => ['follow_notifications' => false],
    ]);
    $action = new FollowUserAction();

    $action->handle($follower, $userToFollow);

    // Notification is sent, but not to database channel
    Notification::assertSentTo(
        $userToFollow,
        App\Notifications\UserFollowedNotification::class,
        function ($notification, $channels) {
            return ! in_array('database', $channels);
        }
    );
});

test('it sends email when email notifications are enabled even if in-app is disabled', function () {
    Notification::fake();

    $follower = User::factory()->create();
    $userToFollow = User::factory()->create([
        'notification_settings' => [
            'follow_notifications' => false, // In-app disabled
            'email_notifications' => true,   // Email enabled
        ],
    ]);
    $action = new FollowUserAction();

    $action->handle($follower, $userToFollow);

    // Notification is sent with email channel but not database
    Notification::assertSentTo(
        $userToFollow,
        App\Notifications\UserFollowedNotification::class,
        function ($notification, $channels) {
            return in_array('mail', $channels) && ! in_array('database', $channels);
        }
    );
});

test('it sends notification by default when notification_settings is null', function () {
    Notification::fake();

    $follower = User::factory()->create();
    $userToFollow = User::factory()->create([
        'notification_settings' => null,
    ]);
    $action = new FollowUserAction();

    $action->handle($follower, $userToFollow);

    Notification::assertSentTo(
        $userToFollow,
        App\Notifications\UserFollowedNotification::class
    );
});
