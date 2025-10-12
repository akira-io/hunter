<?php

declare(strict_types=1);

use App\Models\BlockedUser;
use App\Models\User;

it('blocker relationship returns user who blocked', function () {
    $blocker = User::factory()->create(['name' => 'Blocker User']);
    $blocked = User::factory()->create(['name' => 'Blocked User']);

    $blockedUser = BlockedUser::create([
        'blocker_id' => $blocker->id,
        'blocked_id' => $blocked->id,
    ]);

    expect($blockedUser->blocker)->toBeInstanceOf(User::class);
    expect($blockedUser->blocker->id)->toBe($blocker->id);
    expect($blockedUser->blocker->name)->toBe('Blocker User');
});

it('blocked relationship returns user who was blocked', function () {
    $blocker = User::factory()->create(['name' => 'Blocker User']);
    $blocked = User::factory()->create(['name' => 'Blocked User']);

    $blockedUser = BlockedUser::create([
        'blocker_id' => $blocker->id,
        'blocked_id' => $blocked->id,
    ]);

    expect($blockedUser->blocked)->toBeInstanceOf(User::class);
    expect($blockedUser->blocked->id)->toBe($blocked->id);
    expect($blockedUser->blocked->name)->toBe('Blocked User');
});

it('can create blocked user with fillable attributes', function () {
    $blocker = User::factory()->create();
    $blocked = User::factory()->create();

    $blockedUser = BlockedUser::create([
        'blocker_id' => $blocker->id,
        'blocked_id' => $blocked->id,
    ]);

    $this->assertDatabaseHas('blocked_users', [
        'blocker_id' => $blocker->id,
        'blocked_id' => $blocked->id,
    ]);

    expect($blockedUser->created_at)->not->toBeNull();
    expect($blockedUser->updated_at)->not->toBeNull();
});

it('has timestamps', function () {
    $blocker = User::factory()->create();
    $blocked = User::factory()->create();

    $blockedUser = BlockedUser::create([
        'blocker_id' => $blocker->id,
        'blocked_id' => $blocked->id,
    ]);

    expect($blockedUser->created_at)->not->toBeNull();
    expect($blockedUser->updated_at)->not->toBeNull();
    expect($blockedUser->created_at)->toBeInstanceOf(Carbon\CarbonInterface::class);
    expect($blockedUser->updated_at)->toBeInstanceOf(Carbon\CarbonInterface::class);
});

it('multiple blocks can exist', function () {
    $blocker = User::factory()->create();
    $blocked1 = User::factory()->create();
    $blocked2 = User::factory()->create();

    BlockedUser::create([
        'blocker_id' => $blocker->id,
        'blocked_id' => $blocked1->id,
    ]);

    BlockedUser::create([
        'blocker_id' => $blocker->id,
        'blocked_id' => $blocked2->id,
    ]);

    expect(BlockedUser::where('blocker_id', $blocker->id)->count())->toBe(2);
});
