<?php

declare(strict_types=1);

use App\Actions\Social\FollowUserAction;
use App\Models\User;
use App\Notifications\UserFollowedNotification;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Notification::fake();
});

describe('Independent Notification Channels', function () {
    test('in-app false, email true → send email only', function () {
        $follower = User::factory()->create();
        $userToFollow = User::factory()->create([
            'notification_settings' => [
                'follow_notifications' => false, // In-app disabled
                'email_notifications' => true,   // Email enabled
                'browser_notifications' => false,
            ],
        ]);

        $action = new FollowUserAction;
        $action->handle($follower, $userToFollow);

        Notification::assertSentTo(
            $userToFollow,
            UserFollowedNotification::class,
            function ($notification, $channels) {
                return in_array('mail', $channels)
                    && ! in_array('database', $channels)
                    && ! in_array('broadcast', $channels);
            }
        );
    });

    test('in-app true, email false → in-app only', function () {
        $follower = User::factory()->create();
        $userToFollow = User::factory()->create([
            'notification_settings' => [
                'follow_notifications' => true,  // In-app enabled
                'email_notifications' => false,  // Email disabled
                'browser_notifications' => false,
            ],
        ]);

        $action = new FollowUserAction;
        $action->handle($follower, $userToFollow);

        Notification::assertSentTo(
            $userToFollow,
            UserFollowedNotification::class,
            function ($notification, $channels) {
                return in_array('database', $channels)
                    && ! in_array('mail', $channels)
                    && ! in_array('broadcast', $channels);
            }
        );
    });

    test('both true → both channels', function () {
        $follower = User::factory()->create();
        $userToFollow = User::factory()->create([
            'notification_settings' => [
                'follow_notifications' => true,
                'email_notifications' => true,
                'browser_notifications' => false,
            ],
        ]);

        $action = new FollowUserAction;
        $action->handle($follower, $userToFollow);

        Notification::assertSentTo(
            $userToFollow,
            UserFollowedNotification::class,
            function ($notification, $channels) {
                return in_array('database', $channels)
                    && in_array('mail', $channels);
            }
        );
    });

    test('both false → no notification sent', function () {
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

        // When all channels are disabled, Laravel doesn't send the notification at all
        Notification::assertNothingSentTo($userToFollow);
    });

    test('browser true, in-app false, email false → browser only', function () {
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

        Notification::assertSentTo(
            $userToFollow,
            UserFollowedNotification::class,
            function ($notification, $channels) {
                return in_array('broadcast', $channels)
                    && ! in_array('database', $channels)
                    && ! in_array('mail', $channels);
            }
        );
    });

    test('all three enabled → all three channels', function () {
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

        Notification::assertSentTo(
            $userToFollow,
            UserFollowedNotification::class,
            function ($notification, $channels) {
                return in_array('database', $channels)
                    && in_array('mail', $channels)
                    && in_array('broadcast', $channels);
            }
        );
    });
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

    test('user still receives email when follow_notifications is false but email is true', function () {
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

        Notification::assertSentTo(
            $userToFollow,
            UserFollowedNotification::class,
            function ($notification, $channels) {
                return in_array('mail', $channels);
            }
        );
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

    test('all notifications disabled - no notification sent', function () {
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

        // When all channels are disabled, Laravel doesn't send the notification at all
        Notification::assertNothingSentTo($userToFollow);
    });

    test('only email enabled - user receives email notification', function () {
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

        Notification::assertSentTo(
            $userToFollow,
            UserFollowedNotification::class,
            function ($notification, $channels) {
                return in_array('mail', $channels) && count($channels) === 1;
            }
        );
    });

    test('only browser enabled - user receives browser notification', function () {
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

        Notification::assertSentTo(
            $userToFollow,
            UserFollowedNotification::class,
            function ($notification, $channels) {
                return in_array('broadcast', $channels) && count($channels) === 1;
            }
        );
    });

    test('only follow notifications enabled - user receives in-app notification', function () {
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

        Notification::assertSentTo(
            $userToFollow,
            UserFollowedNotification::class,
            function ($notification, $channels) {
                return in_array('database', $channels) && count($channels) === 1;
            }
        );
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

    test('disabled in-app settings do not prevent email notifications', function () {
        $follower = User::factory()->create();
        $userToFollow = User::factory()->create([
            'notification_settings' => [
                'follow_notifications' => true,
                'email_notifications' => true,
                'browser_notifications' => true,
            ],
        ]);

        // Update settings to disable in-app follow notifications but keep email
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

        // Should still receive email notification
        Notification::assertSentTo(
            $userToFollow,
            UserFollowedNotification::class,
            function ($notification, $channels) {
                return in_array('mail', $channels);
            }
        );
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
            'notification_settings' => [
                'follow_notifications' => false,
                'email_notifications' => false,
                'browser_notifications' => false,
            ],
        ]);

        $action = new FollowUserAction;

        $action->handle($follower, $userWithNotificationsEnabled);
        $action->handle($follower, $userWithNotificationsDisabled);

        Notification::assertSentTo(
            $userWithNotificationsEnabled,
            UserFollowedNotification::class,
            function ($notification, $channels) {
                return in_array('database', $channels);
            }
        );

        // When all channels are disabled, no notification is sent
        Notification::assertNothingSentTo($userWithNotificationsDisabled);
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
            'notification_settings' => [
                'follow_notifications' => false,
                'email_notifications' => false,
                'browser_notifications' => false,
            ],
        ]);
        $userToFollow->refresh();

        // Clear notification history
        Notification::fake();

        // Unfollow and follow again - should NOT send any notification
        $follower->unfollow($userToFollow);
        $action->handle($follower, $userToFollow);
        Notification::assertNothingSentTo($userToFollow);

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
