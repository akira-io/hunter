<?php

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
        ],
    ]);

    $response = actingAs($user)
        ->patch('/settings/notifications', [
            'follow_notifications' => false,
            'email_notifications' => true,
            'browser_notifications' => false,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    assertDatabaseHas('users', [
        'id' => $user->id,
        'notification_settings' => json_encode([
            'follow_notifications' => false,
            'email_notifications' => true,
            'browser_notifications' => false,
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

    $response->assertSessionHasErrors(['email_notifications', 'browser_notifications']);
});

test('notification settings must be boolean values', function () {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->patch('/settings/notifications', [
            'follow_notifications' => 'invalid',
            'email_notifications' => 'invalid',
            'browser_notifications' => 'invalid',
        ]);

    $response->assertSessionHasErrors([
        'follow_notifications',
        'email_notifications',
        'browser_notifications',
    ]);
});

test('guest cannot access notification settings', function () {
    $response = $this->get('/settings/notifications');

    $response->assertRedirect('/login');
});
