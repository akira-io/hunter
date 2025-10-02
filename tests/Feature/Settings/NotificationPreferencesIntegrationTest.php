<?php

use App\Actions\Social\FollowUserAction;
use App\Models\User;
use App\Notifications\UserFollowedNotification;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Notification::fake();
});

describe('Follow Notifications Settings', function () {
    test('user receives notification when follow_notifications is true', function () {
        $follower = User::factory()->create();
        $userToFollow = User::factory()->create([
            'notification_settings' => [
                'follow_notifications' => true,
                'email_notifications' => true,
                'browser_notifications' => true,
            ],
        ]);

        $action = new FollowUserAction;
        $action->handle($follower, $userToFollow);

        Notification::assertSentTo($userToFollow, UserFollowedNotification::class);
    });

    test('user does NOT receive notification when follow_notifications is false', function () {
        $follower = User::factory()->create();
        $userToFollow = User::factory()->create([
            'notification_settings' => [
                'follow_notifications' => false,
                'email_notifications' => true,
                'browser_notifications' => true,
            ],
        ]);

        $action = new FollowUserAction;
        $action->handle($follower, $userToFollow);

        Notification::assertNotSentTo($userToFollow, UserFollowedNotification::class);
    });

    test('user receives notification when notification_settings is null (default true)', function () {
        $follower = User::factory()->create();
        $userToFollow = User::factory()->create([
            'notification_settings' => null,
        ]);

        $action = new FollowUserAction;
        $action->handle($follower, $userToFollow);

        Notification::assertSentTo($userToFollow, UserFollowedNotification::class);
    });

    test('user receives notification when follow_notifications is missing (default true)', function () {
        $follower = User::factory()->create();
        $userToFollow = User::factory()->create([
            'notification_settings' => [
                'email_notifications' => true,
                'browser_notifications' => true,
                // follow_notifications not set - should default to true
            ],
        ]);

        $action = new FollowUserAction;
        $action->handle($follower, $userToFollow);

        Notification::assertSentTo($userToFollow, UserFollowedNotification::class);
    });
});

describe('All Notifications Settings Combinations', function () {
    test('all notifications enabled - user receives follow notification', function () {
        $follower = User::factory()->create();
        $userToFollow = User::factory()->create([
            'notification_settings' => [
                'follow_notifications' => true,
                'email_notifications' => true,
                'browser_notifications' => true,
            ],
        ]);

        $action = new FollowUserAction;
        $action->handle($follower, $userToFollow);

        Notification::assertSentTo($userToFollow, UserFollowedNotification::class);
    });

    test('all notifications disabled - user does NOT receive follow notification', function () {
        $follower = User::factory()->create();
        $userToFollow = User::factory()->create([
            'notification_settings' => [
                'follow_notifications' => false,
                'email_notifications' => false,
                'browser_notifications' => false,
            ],
        ]);

        $action = new FollowUserAction;
        $action->handle($follower, $userToFollow);

        Notification::assertNotSentTo($userToFollow, UserFollowedNotification::class);
    });

    test('only email enabled - user does NOT receive follow notification', function () {
        $follower = User::factory()->create();
        $userToFollow = User::factory()->create([
            'notification_settings' => [
                'follow_notifications' => false,
                'email_notifications' => true,
                'browser_notifications' => false,
            ],
        ]);

        $action = new FollowUserAction;
        $action->handle($follower, $userToFollow);

        Notification::assertNotSentTo($userToFollow, UserFollowedNotification::class);
    });

    test('only browser enabled - user does NOT receive follow notification', function () {
        $follower = User::factory()->create();
        $userToFollow = User::factory()->create([
            'notification_settings' => [
                'follow_notifications' => false,
                'email_notifications' => false,
                'browser_notifications' => true,
            ],
        ]);

        $action = new FollowUserAction;
        $action->handle($follower, $userToFollow);

        Notification::assertNotSentTo($userToFollow, UserFollowedNotification::class);
    });

    test('only follow notifications enabled - user receives notification', function () {
        $follower = User::factory()->create();
        $userToFollow = User::factory()->create([
            'notification_settings' => [
                'follow_notifications' => true,
                'email_notifications' => false,
                'browser_notifications' => false,
            ],
        ]);

        $action = new FollowUserAction;
        $action->handle($follower, $userToFollow);

        Notification::assertSentTo($userToFollow, UserFollowedNotification::class);
    });
});

describe('Settings Persistence', function () {
    test('settings are persisted correctly in database', function () {
        $user = User::factory()->create();

        actingAs($user)
            ->patch('/settings/notifications', [
                'follow_notifications' => false,
                'email_notifications' => false,
                'browser_notifications' => false,
            ]);

        $user->refresh();

        expect($user->notification_settings)->toBe([
            'follow_notifications' => false,
            'email_notifications' => false,
            'browser_notifications' => false,
        ]);
    });

    test('disabled settings prevent notifications from being sent', function () {
        $follower = User::factory()->create();
        $userToFollow = User::factory()->create([
            'notification_settings' => [
                'follow_notifications' => true,
                'email_notifications' => true,
                'browser_notifications' => true,
            ],
        ]);

        // Update settings to disable follow notifications
        actingAs($userToFollow)
            ->patch('/settings/notifications', [
                'follow_notifications' => false,
                'email_notifications' => true,
                'browser_notifications' => true,
            ]);

        $userToFollow->refresh();

        // Try to follow
        $action = new FollowUserAction;
        $action->handle($follower, $userToFollow);

        // Should NOT receive notification
        Notification::assertNotSentTo($userToFollow, UserFollowedNotification::class);
    });

    test('enabled settings allow notifications to be sent', function () {
        $follower = User::factory()->create();
        $userToFollow = User::factory()->create([
            'notification_settings' => [
                'follow_notifications' => false,
                'email_notifications' => true,
                'browser_notifications' => true,
            ],
        ]);

        // Update settings to enable follow notifications
        actingAs($userToFollow)
            ->patch('/settings/notifications', [
                'follow_notifications' => true,
                'email_notifications' => true,
                'browser_notifications' => true,
            ]);

        $userToFollow->refresh();

        // Try to follow
        $action = new FollowUserAction;
        $action->handle($follower, $userToFollow);

        // Should receive notification
        Notification::assertSentTo($userToFollow, UserFollowedNotification::class);
    });
});

describe('Edge Cases', function () {
    test('user with empty array notification_settings receives notification (default true)', function () {
        $follower = User::factory()->create();
        $userToFollow = User::factory()->create([
            'notification_settings' => [],
        ]);

        $action = new FollowUserAction;
        $action->handle($follower, $userToFollow);

        Notification::assertSentTo($userToFollow, UserFollowedNotification::class);
    });

    test('multiple users with different settings receive notifications correctly', function () {
        $follower = User::factory()->create();
        
        $userWithNotificationsEnabled = User::factory()->create([
            'notification_settings' => ['follow_notifications' => true],
        ]);
        
        $userWithNotificationsDisabled = User::factory()->create([
            'notification_settings' => ['follow_notifications' => false],
        ]);

        $action = new FollowUserAction;
        
        $action->handle($follower, $userWithNotificationsEnabled);
        $action->handle($follower, $userWithNotificationsDisabled);

        Notification::assertSentTo($userWithNotificationsEnabled, UserFollowedNotification::class);
        Notification::assertNotSentTo($userWithNotificationsDisabled, UserFollowedNotification::class);
    });

    test('toggling settings on and off works correctly', function () {
        $follower = User::factory()->create();
        $userToFollow = User::factory()->create([
            'notification_settings' => ['follow_notifications' => true],
        ]);

        // First follow - should receive
        $action = new FollowUserAction;
        $follower->unfollow($userToFollow); // Ensure not following
        $action->handle($follower, $userToFollow);
        Notification::assertSentTo($userToFollow, UserFollowedNotification::class);

        // Disable notifications
        $userToFollow->update([
            'notification_settings' => ['follow_notifications' => false],
        ]);
        $userToFollow->refresh();

        // Clear notification history
        Notification::fake();

        // Unfollow and follow again - should NOT receive
        $follower->unfollow($userToFollow);
        $action->handle($follower, $userToFollow);
        Notification::assertNotSentTo($userToFollow, UserFollowedNotification::class);

        // Enable notifications again
        $userToFollow->update([
            'notification_settings' => ['follow_notifications' => true],
        ]);
        $userToFollow->refresh();

        // Clear notification history
        Notification::fake();

        // Unfollow and follow again - should receive
        $follower->unfollow($userToFollow);
        $action->handle($follower, $userToFollow);
        Notification::assertSentTo($userToFollow, UserFollowedNotification::class);
    });
});
