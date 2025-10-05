<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can block another user', function () {
    $blocker = User::factory()->create();
    $blocked = User::factory()->create();

    $blocker->block($blocked);

    expect($blocker->hasBlocked($blocked))->toBeTrue();
});

test('user cannot block themselves', function () {
    $user = User::factory()->create();

    expect($user->blockedUsers()->where('blocked_id', $user->id)->exists())->toBeFalse();
});

test('user can unblock another user', function () {
    $blocker = User::factory()->create();
    $blocked = User::factory()->create();

    $blocker->block($blocked);
    expect($blocker->hasBlocked($blocked))->toBeTrue();

    $blocker->unblock($blocked);
    expect($blocker->hasBlocked($blocked))->toBeFalse();
});

test('blocking same user twice does not create duplicate entries', function () {
    $blocker = User::factory()->create();
    $blocked = User::factory()->create();

    $blocker->block($blocked);
    $blocker->block($blocked);

    expect($blocker->blockedUsers()->count())->toBe(1);
});

test('user can check if blocked by another user', function () {
    $blocker = User::factory()->create();
    $blocked = User::factory()->create();

    $blocker->block($blocked);

    expect($blocked->isBlockedBy($blocker))->toBeTrue();
});

test('public profile can be viewed by everyone', function () {
    $user = User::factory()->create([
        'privacy_settings' => [
            'profile_visibility' => 'public',
        ],
    ]);

    $viewer = User::factory()->create();
    $guest = null;

    expect($user->canBeViewedBy($viewer))->toBeTrue()
        ->and($user->canBeViewedBy($guest))->toBeTrue();
});

test('followers only profile can be viewed by followers', function () {
    $user = User::factory()->create([
        'privacy_settings' => [
            'profile_visibility' => 'followers',
        ],
    ]);

    $follower = User::factory()->create();
    $nonFollower = User::factory()->create();

    $follower->follow($user);

    expect($user->canBeViewedBy($follower))->toBeTrue()
        ->and($user->canBeViewedBy($nonFollower))->toBeFalse()
        ->and($user->canBeViewedBy(null))->toBeFalse();
});

test('private profile cannot be viewed by others', function () {
    $user = User::factory()->create([
        'privacy_settings' => [
            'profile_visibility' => 'private',
        ],
    ]);

    $viewer = User::factory()->create();

    expect($user->canBeViewedBy($viewer))->toBeFalse()
        ->and($user->canBeViewedBy(null))->toBeFalse()
        ->and($user->canBeViewedBy($user))->toBeTrue();
});

test('blocked user cannot view profile even if public', function () {
    $user = User::factory()->create([
        'privacy_settings' => [
            'profile_visibility' => 'public',
        ],
    ]);

    $blocked = User::factory()->create();
    $user->block($blocked);

    expect($user->canBeViewedBy($blocked))->toBeFalse();
});

test('user who blocked cannot view blocked user profile', function () {
    $blocker = User::factory()->create([
        'privacy_settings' => [
            'profile_visibility' => 'public',
        ],
    ]);

    $blocked = User::factory()->create([
        'privacy_settings' => [
            'profile_visibility' => 'public',
        ],
    ]);

    $blocker->block($blocked);

    expect($blocked->canBeViewedBy($blocker))->toBeFalse();
});

test('user can receive messages from everyone when setting is everyone', function () {
    $user = User::factory()->create([
        'privacy_settings' => [
            'who_can_message' => 'everyone',
        ],
    ]);

    $sender = User::factory()->create();

    expect($user->canReceiveMessagesFrom($sender))->toBeTrue();
});

test('user can receive messages from followers only when setting is followers', function () {
    $user = User::factory()->create([
        'privacy_settings' => [
            'who_can_message' => 'followers',
        ],
    ]);

    $follower = User::factory()->create();
    $nonFollower = User::factory()->create();

    $follower->follow($user);

    expect($user->canReceiveMessagesFrom($follower))->toBeTrue()
        ->and($user->canReceiveMessagesFrom($nonFollower))->toBeFalse();
});

test('user cannot receive messages when setting is none', function () {
    $user = User::factory()->create([
        'privacy_settings' => [
            'who_can_message' => 'none',
        ],
    ]);

    $sender = User::factory()->create();

    expect($user->canReceiveMessagesFrom($sender))->toBeFalse();
});

test('blocked user cannot send messages', function () {
    $user = User::factory()->create([
        'privacy_settings' => [
            'who_can_message' => 'everyone',
        ],
    ]);

    $blocked = User::factory()->create();
    $user->block($blocked);

    expect($user->canReceiveMessagesFrom($blocked))->toBeFalse();
});

test('user can receive comments from everyone when setting is everyone', function () {
    $user = User::factory()->create([
        'privacy_settings' => [
            'who_can_comment' => 'everyone',
        ],
    ]);

    $commenter = User::factory()->create();

    expect($user->canReceiveCommentsFrom($commenter))->toBeTrue()
        ->and($user->canReceiveCommentsFrom(null))->toBeTrue();
});

test('user can receive comments from followers only when setting is followers', function () {
    $user = User::factory()->create([
        'privacy_settings' => [
            'who_can_comment' => 'followers',
        ],
    ]);

    $follower = User::factory()->create();
    $nonFollower = User::factory()->create();

    $follower->follow($user);

    expect($user->canReceiveCommentsFrom($follower))->toBeTrue()
        ->and($user->canReceiveCommentsFrom($nonFollower))->toBeFalse()
        ->and($user->canReceiveCommentsFrom(null))->toBeFalse();
});

test('user cannot receive comments when setting is disabled', function () {
    $user = User::factory()->create([
        'privacy_settings' => [
            'who_can_comment' => 'disabled',
        ],
    ]);

    $commenter = User::factory()->create();

    expect($user->canReceiveCommentsFrom($commenter))->toBeFalse()
        ->and($user->canReceiveCommentsFrom(null))->toBeFalse();
});

test('blocked user cannot comment', function () {
    $user = User::factory()->create([
        'privacy_settings' => [
            'who_can_comment' => 'everyone',
        ],
    ]);

    $blocked = User::factory()->create();
    $user->block($blocked);

    expect($user->canReceiveCommentsFrom($blocked))->toBeFalse();
});

test('user is searchable by default', function () {
    $user = User::factory()->create([
        'privacy_settings' => [
            'searchable' => true,
        ],
    ]);

    expect($user->isSearchable())->toBeTrue();
});

test('user can disable search visibility', function () {
    $user = User::factory()->create([
        'privacy_settings' => [
            'searchable' => false,
        ],
    ]);

    expect($user->isSearchable())->toBeFalse();
});

test('user shows activity status by default', function () {
    $user = User::factory()->create([
        'privacy_settings' => [
            'show_activity_status' => true,
        ],
    ]);

    expect($user->showsActivityStatus())->toBeTrue();
});

test('user can hide activity status', function () {
    $user = User::factory()->create([
        'privacy_settings' => [
            'show_activity_status' => false,
        ],
    ]);

    expect($user->showsActivityStatus())->toBeFalse();
});
