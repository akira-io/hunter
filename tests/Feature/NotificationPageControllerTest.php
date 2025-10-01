<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\UserFollowedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = actingAsAuthUser();
});

it('can access notifications page', function () {
    $response = $this->get(route('notifications.index'));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('notifications/index')
            ->has('notifications')
            ->has('pagination')
            ->has('unread_count')
        );
});

it('displays notifications with pagination info', function () {
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
            ->where('pagination.total', 25)
            ->where('pagination.per_page', 20)
            ->where('pagination.total_pages', 2)
            ->where('pagination.current_page', 1)
            ->where('pagination.has_next_page', true)
            ->where('pagination.has_prev_page', false)
            ->has('notifications', 20) // Should show 20 notifications on first page
        );
});

it('can navigate to second page', function () {
    // Create enough notifications to have a second page
    $follower = User::factory()->create();

    for ($i = 0; $i < 25; $i++) {
        $this->user->notify(new UserFollowedNotification($follower));
    }

    $response = $this->get(route('notifications.index', ['page' => 2]));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('notifications/index')
            ->where('pagination.current_page', 2)
            ->where('pagination.has_next_page', false)
            ->where('pagination.has_prev_page', true)
            ->has('notifications', 5) // Should show 5 notifications on second page
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
            ->has('notifications', 2) // Should show both read and unread
        );
});

it('requires authentication', function () {
    auth()->logout();

    $response = $this->get(route('notifications.index'));

    $response->assertRedirect();
});
