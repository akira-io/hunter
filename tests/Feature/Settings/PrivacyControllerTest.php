<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('privacy settings page can be rendered', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('privacy.index'));

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('settings/privacy')
            ->has('privacySettings')
            ->has('blockedUsers'));
});

test('privacy settings can be updated', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('privacy.update'), [
        'profile_visibility' => 'followers',
        'who_can_message' => 'followers',
        'who_can_comment' => 'followers',
        'searchable' => false,
        'show_activity_status' => false,
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect();

    $user->refresh();

    expect($user->privacy_settings['profile_visibility'])->toBe('followers')
        ->and($user->privacy_settings['who_can_message'])->toBe('followers')
        ->and($user->privacy_settings['who_can_comment'])->toBe('followers')
        ->and($user->privacy_settings['searchable'])->toBeFalse()
        ->and($user->privacy_settings['show_activity_status'])->toBeFalse();
});

test('privacy settings require valid values for profile_visibility', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('privacy.update'), [
        'profile_visibility' => 'invalid',
        'who_can_message' => 'everyone',
        'who_can_comment' => 'everyone',
        'searchable' => true,
        'show_activity_status' => true,
    ]);

    $response->assertSessionHasErrors(['profile_visibility']);
});

test('privacy settings require valid values for who_can_message', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('privacy.update'), [
        'profile_visibility' => 'public',
        'who_can_message' => 'invalid',
        'who_can_comment' => 'everyone',
        'searchable' => true,
        'show_activity_status' => true,
    ]);

    $response->assertSessionHasErrors(['who_can_message']);
});

test('privacy settings require valid values for who_can_comment', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('privacy.update'), [
        'profile_visibility' => 'public',
        'who_can_message' => 'everyone',
        'who_can_comment' => 'invalid',
        'searchable' => true,
        'show_activity_status' => true,
    ]);

    $response->assertSessionHasErrors(['who_can_comment']);
});

test('privacy settings require boolean for searchable', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('privacy.update'), [
        'profile_visibility' => 'public',
        'who_can_message' => 'everyone',
        'who_can_comment' => 'everyone',
        'searchable' => 'invalid',
        'show_activity_status' => true,
    ]);

    $response->assertSessionHasErrors(['searchable']);
});

test('privacy settings require boolean for show_activity_status', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('privacy.update'), [
        'profile_visibility' => 'public',
        'who_can_message' => 'everyone',
        'who_can_comment' => 'everyone',
        'searchable' => true,
        'show_activity_status' => 'invalid',
    ]);

    $response->assertSessionHasErrors(['show_activity_status']);
});

test('user can block another user', function () {
    $user = User::factory()->create();
    $userToBlock = User::factory()->create();

    $response = $this->actingAs($user)->post(route('privacy.block-user', ['userId' => $userToBlock->id]));

    $response->assertSessionHasNoErrors()
        ->assertRedirect();

    expect($user->hasBlocked($userToBlock))->toBeTrue();
});

test('user can unblock another user', function () {
    $user = User::factory()->create();
    $blocked = User::factory()->create();

    $user->block($blocked);
    expect($user->hasBlocked($blocked))->toBeTrue();

    $response = $this->actingAs($user)->delete(route('privacy.unblock-user', ['userId' => $blocked->id]));

    $response->assertSessionHasNoErrors()
        ->assertRedirect();

    $user->refresh();
    expect($user->hasBlocked($blocked))->toBeFalse();
});

test('blocking non-existent user returns 404', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('privacy.block-user', ['userId' => 99999]));

    $response->assertNotFound();
});

test('unblocking non-existent user returns 404', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->delete(route('privacy.unblock-user', ['userId' => 99999]));

    $response->assertNotFound();
});

test('blocked users list shows correct users', function () {
    $user = User::factory()->create();
    $blocked1 = User::factory()->create();
    $blocked2 = User::factory()->create();
    $notBlocked = User::factory()->create();

    $user->block($blocked1);
    $user->block($blocked2);

    $response = $this->actingAs($user)->get(route('privacy.index'));

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('settings/privacy')
            ->has('blockedUsers', 2)
            ->where('blockedUsers.0.id', $blocked1->id)
            ->where('blockedUsers.1.id', $blocked2->id));
});

test('guest cannot access privacy settings', function () {
    $response = $this->get(route('privacy.index'));

    $response->assertRedirect(route('login'));
});

test('guest cannot update privacy settings', function () {
    $response = $this->post(route('privacy.update'), [
        'profile_visibility' => 'public',
        'who_can_message' => 'everyone',
        'who_can_comment' => 'everyone',
        'searchable' => true,
        'show_activity_status' => true,
    ]);

    $response->assertRedirect(route('login'));
});
