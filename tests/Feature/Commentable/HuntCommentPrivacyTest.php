<?php

declare(strict_types=1);

use App\Models\Hunt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can comment on hunt when owner allows everyone', function () {
    $owner = User::factory()->create([
        'privacy_settings' => [
            'who_can_comment' => 'everyone',
        ],
    ]);

    $commenter = User::factory()->create();
    $hunt = Hunt::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($commenter)->post(route('hunts.comment', $hunt), [
        'content' => 'Great hunt!',
    ]);

    $response->assertSessionHasNoErrors();
    expect($hunt->comments()->count())->toBe(1);
});

test('follower can comment on hunt when owner allows followers only', function () {
    $owner = User::factory()->create([
        'privacy_settings' => [
            'who_can_comment' => 'followers',
        ],
    ]);

    $follower = User::factory()->create();
    $follower->follow($owner);

    $hunt = Hunt::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($follower)->post(route('hunts.comment', $hunt), [
        'content' => 'Great hunt!',
    ]);

    $response->assertSessionHasNoErrors();
    expect($hunt->comments()->count())->toBe(1);
});

test('non-follower cannot comment on hunt when owner allows followers only', function () {
    $owner = User::factory()->create([
        'privacy_settings' => [
            'who_can_comment' => 'followers',
        ],
    ]);

    $nonFollower = User::factory()->create();
    $hunt = Hunt::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($nonFollower)->post(route('hunts.comment', $hunt), [
        'content' => 'Great hunt!',
    ]);

    $response->assertForbidden();
    expect($hunt->comments()->count())->toBe(0);
});

test('user cannot comment on hunt when owner has comments disabled', function () {
    $owner = User::factory()->create([
        'privacy_settings' => [
            'who_can_comment' => 'disabled',
        ],
    ]);

    $commenter = User::factory()->create();
    $hunt = Hunt::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($commenter)->post(route('hunts.comment', $hunt), [
        'content' => 'Great hunt!',
    ]);

    $response->assertForbidden();
    expect($hunt->comments()->count())->toBe(0);
});

test('blocked user cannot comment on hunt', function () {
    $owner = User::factory()->create([
        'privacy_settings' => [
            'who_can_comment' => 'everyone',
        ],
    ]);

    $blockedUser = User::factory()->create();
    $owner->block($blockedUser);

    $hunt = Hunt::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($blockedUser)->post(route('hunts.comment', $hunt), [
        'content' => 'Great hunt!',
    ]);

    $response->assertForbidden();
    expect($hunt->comments()->count())->toBe(0);
});

test('owner can always comment on their own hunt', function () {
    $owner = User::factory()->create([
        'privacy_settings' => [
            'who_can_comment' => 'disabled',
        ],
    ]);

    $hunt = Hunt::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($owner)->post(route('hunts.comment', $hunt), [
        'content' => 'Update on my hunt!',
    ]);

    $response->assertSessionHasNoErrors();
    expect($hunt->comments()->count())->toBe(1);
});

test('guest cannot comment on hunt when owner allows everyone', function () {
    $owner = User::factory()->create([
        'privacy_settings' => [
            'who_can_comment' => 'everyone',
        ],
    ]);

    $hunt = Hunt::factory()->create(['owner_id' => $owner->id]);

    $response = $this->post(route('hunts.comment', $hunt), [
        'content' => 'Great hunt!',
    ]);

    $response->assertRedirect(route('login'));
    expect($hunt->comments()->count())->toBe(0);
});
