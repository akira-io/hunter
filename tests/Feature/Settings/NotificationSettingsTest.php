<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

test('user can view notification settings page', function () {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->get('/settings/notifications');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('settings/notifications')
        ->has('notificationSettings')
    );
});

test('user can update notification settings', function () {
    $user = User::factory()->create([
        'notification_settings' => [
            'follow_notifications' => true,
            'email_notifications' => true,
            'browser_notifications' => true,
            'hunt_notifications_in_app' => true,
            'hunt_notifications_browser' => true,
            'hunt_notifications_email' => false,
        ],
    ]);

    $response = actingAs($user)
        ->patch('/settings/notifications', [
            'follow_notifications' => false,
            'email_notifications' => true,
            'browser_notifications' => false,
            'hunt_notifications_in_app' => true,
            'hunt_notifications_browser' => false,
            'hunt_notifications_email' => true,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    assertDatabaseHas('users', [
        'id' => $user->id,
        'notification_settings' => json_encode([
            'follow_notifications' => false,
            'email_notifications' => true,
            'browser_notifications' => false,
            'hunt_notifications_in_app' => true,
            'hunt_notifications_browser' => false,
            'hunt_notifications_email' => true,
        ]),
    ]);
});

test('notification settings validation requires all fields', function () {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->patch('/settings/notifications', [
            'follow_notifications' => false,
            // Missing other fields
        ]);

    $response->assertSessionHasErrors([
        'email_notifications',
        'browser_notifications',
        'hunt_notifications_in_app',
        'hunt_notifications_browser',
        'hunt_notifications_email',
    ]);
});

test('notification settings must be boolean values', function () {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->patch('/settings/notifications', [
            'follow_notifications' => 'invalid',
            'email_notifications' => 'invalid',
            'browser_notifications' => 'invalid',
            'hunt_notifications_in_app' => 'invalid',
            'hunt_notifications_browser' => 'invalid',
            'hunt_notifications_email' => 'invalid',
        ]);

    $response->assertSessionHasErrors([
        'follow_notifications',
        'email_notifications',
        'browser_notifications',
        'hunt_notifications_in_app',
        'hunt_notifications_browser',
        'hunt_notifications_email',
    ]);
});

test('guest cannot access notification settings', function () {
    $response = $this->get('/settings/notifications');

    $response->assertRedirect('/login');
});
