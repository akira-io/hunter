<?php

declare(strict_types=1);

use App\Http\Resources\Hunt\HuntResource;
use App\Models\Hunt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('hunt resource includes can_comment true when user can comment', function () {
    $viewer = User::factory()->create();
    $owner = User::factory()->create([
        'privacy_settings' => [
            'who_can_comment' => 'everyone',
        ],
    ]);
    $hunt = Hunt::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($viewer)->get(route('hunts.show', $hunt));

    $response->assertSuccessful();

    // Get the hunt data from Inertia props
    $props = $response->viewData('page')['props'];

    expect($props['hunt'])->toHaveKey('can_comment')
        ->and($props['hunt']['can_comment'])->toBeTrue();
});

test('hunt resource includes can_comment false when comments are disabled', function () {
    $viewer = User::factory()->create();
    $owner = User::factory()->create([
        'privacy_settings' => [
            'who_can_comment' => 'disabled',
        ],
    ]);
    $hunt = Hunt::factory()->create(['owner_id' => $owner->id]);

    $this->actingAs($viewer);

    $resource = HuntResource::make($hunt)->resolve();

    expect($resource)->toHaveKey('can_comment')
        ->and($resource['can_comment'])->toBeFalse();
});

test('hunt resource includes can_comment false when only followers can comment and user is not following', function () {
    $viewer = User::factory()->create();
    $owner = User::factory()->create([
        'privacy_settings' => [
            'who_can_comment' => 'followers',
        ],
    ]);
    $hunt = Hunt::factory()->create(['owner_id' => $owner->id]);

    $this->actingAs($viewer);

    $resource = HuntResource::make($hunt)->resolve();

    expect($resource)->toHaveKey('can_comment')
        ->and($resource['can_comment'])->toBeFalse();
});

test('hunt resource includes can_comment true when only followers can comment and user is following', function () {
    $viewer = User::factory()->create();
    $owner = User::factory()->create([
        'privacy_settings' => [
            'who_can_comment' => 'followers',
        ],
    ]);
    $hunt = Hunt::factory()->create(['owner_id' => $owner->id]);

    $viewer->follow($owner);

    $response = $this->actingAs($viewer)->get(route('hunts.show', $hunt));

    $response->assertSuccessful();

    $props = $response->viewData('page')['props'];

    expect($props['hunt'])->toHaveKey('can_comment')
        ->and($props['hunt']['can_comment'])->toBeTrue();
});

test('hunt resource includes can_comment false when user is blocked', function () {
    $viewer = User::factory()->create();
    $owner = User::factory()->create([
        'privacy_settings' => [
            'who_can_comment' => 'everyone',
        ],
    ]);
    $hunt = Hunt::factory()->create(['owner_id' => $owner->id]);

    $owner->block($viewer);

    $this->actingAs($viewer);

    $resource = HuntResource::make($hunt)->resolve();

    expect($resource)->toHaveKey('can_comment')
        ->and($resource['can_comment'])->toBeFalse();
});

test('hunt resource includes can_comment true for owner own hunt', function () {
    $owner = User::factory()->create([
        'privacy_settings' => [
            'who_can_comment' => 'disabled',
        ],
    ]);
    $hunt = Hunt::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($owner)->get(route('hunts.show', $hunt));

    $response->assertSuccessful();

    $props = $response->viewData('page')['props'];

    expect($props['hunt'])->toHaveKey('can_comment')
        ->and($props['hunt']['can_comment'])->toBeTrue();
});

test('hunt resource includes can_comment false for guests', function () {
    $owner = User::factory()->create([
        'privacy_settings' => [
            'who_can_comment' => 'everyone',
        ],
    ]);
    $hunt = Hunt::factory()->create(['owner_id' => $owner->id]);

    // Not authenticated
    $resource = HuntResource::make($hunt)->resolve();

    expect($resource)->toHaveKey('can_comment')
        ->and($resource['can_comment'])->toBeFalse();
});
