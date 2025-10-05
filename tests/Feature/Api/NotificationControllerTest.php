<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\UserFollowedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('GET /api/notifications - index', function () {
    it('returns user notifications', function () {
        $follower = User::factory()->create();
        $this->user->notify(new UserFollowedNotification($follower));

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/notifications');

        $response->assertOk()
            ->assertJsonStructure([
                'notifications' => [
                    '*' => ['id', 'type', 'title', 'message', 'data', 'read_at', 'created_at'],
                ],
                'unread_count',
            ]);
    });

    it('returns empty notifications array when user has no notifications', function () {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/notifications');

        $response->assertOk()
            ->assertJson([
                'notifications' => [],
                'unread_count' => 0,
            ]);
    });

    it('returns 401 when unauthenticated', function () {
        $response = $this->getJson('/api/notifications');

        $response->assertStatus(401);
    });

    it('returns only unread notifications by default', function () {
        $follower = User::factory()->create();

        // Create 5 notifications
        for ($i = 0; $i < 5; $i++) {
            $this->user->notify(new UserFollowedNotification($follower));
        }

        // Mark 2 as read
        $notifications = $this->user->notifications()->get();
        $notifications[0]->markAsRead();
        $notifications[1]->markAsRead();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/notifications');

        $response->assertOk();

        $data = $response->json();
        expect($data)->toHaveKey('notifications')
            ->and(count($data['notifications']))->toBe(3)
            ->and($data['unread_count'])->toBe(3);
    });

    it('formats notification data correctly', function () {
        $follower = User::factory()->create(['name' => 'John Doe']);
        $this->user->notify(new UserFollowedNotification($follower));

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/notifications');

        $response->assertOk();

        $data = $response->json();
        expect($data)->toHaveKeys(['notifications', 'unread_count'])
            ->and($data['notifications'])->toBeArray()
            ->and(count($data['notifications']))->toBeGreaterThan(0);

        $notification = $data['notifications'][0];
        expect($notification)->toHaveKeys(['id', 'type', 'title', 'message', 'data', 'read_at', 'created_at'])
            ->and($notification['type'])->toBe('follow')
            ->and($notification['read_at'])->toBeNull();
    });
});

describe('PUT /api/notifications/{id} - update', function () {
    it('marks notification as read', function () {
        $follower = User::factory()->create();
        $this->user->notify(new UserFollowedNotification($follower));

        $notification = $this->user->notifications()->first();
        expect($notification->read_at)->toBeNull();

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/notifications/{$notification->id}");

        $response->assertOk()
            ->assertJson(['success' => true]);

        $notification->refresh();
        expect($notification->read_at)->not->toBeNull();
    });

    it('returns 404 when notification does not exist', function () {
        // Use a valid UUID format but one that doesn't exist
        $nonExistentUuid = '00000000-0000-0000-0000-000000000000';

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/notifications/{$nonExistentUuid}");

        $response->assertStatus(404)
            ->assertJson(['error' => 'Notification not found']);
    });

    it('returns 404 when trying to mark another users notification', function () {
        $otherUser = User::factory()->create();
        $follower = User::factory()->create();
        $otherUser->notify(new UserFollowedNotification($follower));

        $notification = $otherUser->notifications()->first();

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/notifications/{$notification->id}");

        $response->assertStatus(404)
            ->assertJson(['error' => 'Notification not found']);
    });

    it('returns 401 when unauthenticated', function () {
        $response = $this->putJson('/api/notifications/some-id');

        $response->assertStatus(401);
    });

    it('does not fail when marking already read notification', function () {
        $follower = User::factory()->create();
        $this->user->notify(new UserFollowedNotification($follower));

        $notification = $this->user->notifications()->first();
        $notification->markAsRead();

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/notifications/{$notification->id}");

        $response->assertOk()
            ->assertJson(['success' => true]);
    });
});

describe('POST /api/notifications/mark-all-read - store', function () {
    it('marks all notifications as read', function () {
        $follower = User::factory()->create();

        // Create 5 notifications
        for ($i = 0; $i < 5; $i++) {
            $this->user->notify(new UserFollowedNotification($follower));
        }

        expect($this->user->unreadNotifications()->count())->toBe(5);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/notifications/mark-all-read');

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->user->refresh();
        expect($this->user->unreadNotifications()->count())->toBe(0);
    });

    it('returns success even when no notifications exist', function () {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/notifications/mark-all-read');

        $response->assertOk()
            ->assertJson(['success' => true]);
    });

    it('returns 401 when unauthenticated', function () {
        $response = $this->postJson('/api/notifications/mark-all-read');

        $response->assertStatus(401);
    });

    it('does not affect already read notifications', function () {
        $follower = User::factory()->create();

        // Create 5 notifications
        for ($i = 0; $i < 5; $i++) {
            $this->user->notify(new UserFollowedNotification($follower));
        }

        // Mark 2 as read first and store their IDs
        $readNotifications = $this->user->notifications()->take(2)->get();
        $readNotifications->each(fn ($n) => $n->markAsRead());

        $firstNotificationId = $readNotifications->first()->id;
        $firstReadAt = $readNotifications->first()->read_at;

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/notifications/mark-all-read');

        $response->assertOk();

        // Verify the first read notification timestamp didn't change
        $this->user->refresh();
        $firstNotification = $this->user->notifications()->find($firstNotificationId);
        expect($firstNotification->read_at->timestamp)
            ->toBe($firstReadAt->timestamp);
    });

    it('only marks current users notifications', function () {
        $otherUser = User::factory()->create();
        $follower = User::factory()->create();

        // Create notifications for both users
        $this->user->notify(new UserFollowedNotification($follower));
        $otherUser->notify(new UserFollowedNotification($follower));

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/notifications/mark-all-read');

        $response->assertOk();

        // Current user notifications should be read
        expect($this->user->unreadNotifications()->count())->toBe(0);

        // Other user notifications should still be unread
        expect($otherUser->unreadNotifications()->count())->toBe(1);
    });
});

describe('GET /api/notifications/unread-count - show', function () {
    it('returns correct unread count', function () {
        $follower = User::factory()->create();

        // Create 7 notifications
        for ($i = 0; $i < 7; $i++) {
            $this->user->notify(new UserFollowedNotification($follower));
        }

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/notifications/unread-count');

        $response->assertOk()
            ->assertJson(['unread_count' => 7]);
    });

    it('returns zero when no unread notifications', function () {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/notifications/unread-count');

        $response->assertOk()
            ->assertJson(['unread_count' => 0]);
    });

    it('excludes read notifications from count', function () {
        $follower = User::factory()->create();

        // Create 5 notifications
        for ($i = 0; $i < 5; $i++) {
            $this->user->notify(new UserFollowedNotification($follower));
        }

        // Mark 2 as read
        $this->user->notifications()->take(2)->each(fn ($n) => $n->markAsRead());

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/notifications/unread-count');

        $response->assertOk()
            ->assertJson(['unread_count' => 3]);
    });

    it('returns 401 when unauthenticated', function () {
        $response = $this->getJson('/api/notifications/unread-count');

        $response->assertStatus(401);
    });

    it('updates count after marking as read', function () {
        $follower = User::factory()->create();

        // Create 3 notifications
        for ($i = 0; $i < 3; $i++) {
            $this->user->notify(new UserFollowedNotification($follower));
        }

        // Initial count
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/notifications/unread-count');
        $response->assertJson(['unread_count' => 3]);

        // Mark one as read
        $this->user->notifications()->first()->markAsRead();

        // Updated count
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/notifications/unread-count');
        $response->assertJson(['unread_count' => 2]);
    });

    it('only counts current users notifications', function () {
        $otherUser = User::factory()->create();
        $follower = User::factory()->create();

        // Create notifications for both users
        for ($i = 0; $i < 3; $i++) {
            $this->user->notify(new UserFollowedNotification($follower));
        }

        for ($i = 0; $i < 5; $i++) {
            $otherUser->notify(new UserFollowedNotification($follower));
        }

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/notifications/unread-count');

        $response->assertOk()
            ->assertJson(['unread_count' => 3]);
    });
});
