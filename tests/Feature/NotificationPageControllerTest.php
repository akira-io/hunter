<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\UserFollowedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    Event::fake();
    DB::table('notifications')->delete();
    $this->user = actingAsAuthUser();
});

it('can access notifications page', function () {
    $response = $this->get(route('notifications.index'));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('notifications/index')
            ->has('notifications')
            ->has('unread_count')
            ->has('filter')
            ->has('counts')
        );
});

it('displays notifications with infinite scroll pagination', function () {
    // Create some test notifications
    $follower = User::factory()->create();

    // Create multiple notifications to test pagination
    for ($i = 0; $i < 25; $i++) {
        $this->user->notify(new UserFollowedNotification($follower));
    }

    $response = $this->get(route('notifications.index'));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('notifications/index')
            ->has('notifications')
            ->where('counts.all', 25)
            ->where('unread_count', 25)
        );
});

it('supports pagination with page parameter', function () {
    // Create enough notifications to have a second page
    $follower = User::factory()->create();

    for ($i = 0; $i < 25; $i++) {
        $this->user->notify(new UserFollowedNotification($follower));
    }

    $response = $this->get(route('notifications.index', ['page' => 2]));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('notifications/index')
            ->where('counts.all', 25)
        );
});

it('shows unread count correctly', function () {
    $follower = User::factory()->create();

    // Create 3 notifications
    for ($i = 0; $i < 3; $i++) {
        $this->user->notify(new UserFollowedNotification($follower));
    }

    // Mark one as read
    $this->user->notifications()->first()->markAsRead();

    $response = $this->get(route('notifications.index'));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->where('unread_count', 2)
        );
});

it('includes both read and unread notifications', function () {
    $follower = User::factory()->create();

    // Create notifications
    $this->user->notify(new UserFollowedNotification($follower));
    $this->user->notify(new UserFollowedNotification($follower));

    // Mark one as read
    $this->user->notifications()->first()->markAsRead();

    $response = $this->get(route('notifications.index'));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->where('counts.all', 2)
            ->where('counts.read', 1)
            ->where('counts.unread', 1)
        );
});

it('requires authentication', function () {
    auth()->logout();

    $response = $this->get(route('notifications.index'));

    $response->assertRedirect();
});

it('can filter notifications by unread', function () {
    $follower = User::factory()->create();

    // Create 5 notifications
    for ($i = 0; $i < 5; $i++) {
        $this->user->notify(new UserFollowedNotification($follower));
    }

    // Mark 2 as read
    $this->user->notifications()->take(2)->each(fn ($n) => $n->markAsRead());

    $response = $this->get(route('notifications.index', ['filter' => 'unread']));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->where('filter', 'unread')
            ->where('counts.unread', 3)
            ->where('counts.read', 2)
            ->where('counts.all', 5)
        );
});

it('can filter notifications by read', function () {
    $follower = User::factory()->create();

    // Create 5 notifications
    for ($i = 0; $i < 5; $i++) {
        $this->user->notify(new UserFollowedNotification($follower));
    }

    // Mark 2 as read
    $this->user->notifications()->take(2)->each(fn ($n) => $n->markAsRead());

    $response = $this->get(route('notifications.index', ['filter' => 'read']));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->where('filter', 'read')
            ->where('counts.read', 2)
            ->where('counts.unread', 3)
            ->where('counts.all', 5)
        );
});

it('shows all notifications by default', function () {
    $follower = User::factory()->create();

    // Create 5 notifications
    for ($i = 0; $i < 5; $i++) {
        $this->user->notify(new UserFollowedNotification($follower));
    }

    // Mark 2 as read
    $this->user->notifications()->take(2)->each(fn ($n) => $n->markAsRead());

    $response = $this->get(route('notifications.index'));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->where('filter', 'all')
            ->where('counts.all', 5)
            ->where('counts.read', 2)
            ->where('counts.unread', 3)
        );
});

it('can mark a notification as read', function () {
    $follower = User::factory()->create();
    $this->user->notify(new UserFollowedNotification($follower));

    $notification = $this->user->notifications()->first();

    expect($notification->read_at)->toBeNull();

    $response = $this->post(route('notifications.read', ['id' => $notification->id]));

    $response->assertRedirect();

    $notification->refresh();
    expect($notification->read_at)->not->toBeNull();
});

it('returns 404 when marking non-existent notification as read', function () {
    // Use a valid UUID format but one that doesn't exist
    $nonExistentUuid = '00000000-0000-0000-0000-000000000000';

    $response = $this->post(route('notifications.read', ['id' => $nonExistentUuid]));

    $response->assertNotFound();
});

it('can mark all notifications as read', function () {
    $follower = User::factory()->create();

    // Create 5 notifications
    for ($i = 0; $i < 5; $i++) {
        $this->user->notify(new UserFollowedNotification($follower));
    }

    expect($this->user->unreadNotifications()->count())->toBe(5);

    $response = $this->post(route('notifications.readAll'));

    $response->assertRedirect();

    expect($this->user->unreadNotifications()->count())->toBe(0);
});

it('can get unread notification count', function () {
    $follower = User::factory()->create();

    // Create 3 notifications
    for ($i = 0; $i < 3; $i++) {
        $this->user->notify(new UserFollowedNotification($follower));
    }

    $response = $this->get(route('notifications.unreadCount'));

    $response->assertStatus(200)
        ->assertJson(['unread_count' => 3]);
});

it('redirects when marking notification as read without authentication', function () {
    auth()->logout();

    $response = $this->post(route('notifications.read', ['id' => 'some-id']));

    $response->assertRedirect();
});

it('redirects when marking all as read without authentication', function () {
    auth()->logout();

    $response = $this->post(route('notifications.readAll'));

    $response->assertRedirect();
});

it('redirects when getting unread count without authentication', function () {
    auth()->logout();

    $response = $this->get(route('notifications.unreadCount'));

    $response->assertRedirect();
});
