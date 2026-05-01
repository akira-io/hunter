<?php

declare(strict_types=1);

use App\Actions\Settings\BlockUserAction;
use App\Models\User;

beforeEach(function () {
    $this->action = new BlockUserAction();
});

it('successfully blocks another user', function () {
    $blocker = User::factory()->create();
    $userToBlock = User::factory()->create();

    $this->action->handle($blocker, $userToBlock->id);

    expect($blocker->hasBlocked($userToBlock))->toBeTrue();
});

it('cannot block yourself', function () {
    $user = User::factory()->create();

    expect(fn () => $this->action->handle($user, $user->id))
        ->toThrow(InvalidArgumentException::class, 'Não pode bloquear-se a si mesmo.');
});

it('throws exception when user not found', function () {
    $blocker = User::factory()->create();
    $nonExistentUserId = 99999;

    expect(fn () => $this->action->handle($blocker, $nonExistentUserId))
        ->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);
});

it('can block multiple users', function () {
    $blocker = User::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    $this->action->handle($blocker, $user1->id);
    $this->action->handle($blocker, $user2->id);
    $this->action->handle($blocker, $user3->id);

    expect($blocker->hasBlocked($user1))->toBeTrue();
    expect($blocker->hasBlocked($user2))->toBeTrue();
    expect($blocker->hasBlocked($user3))->toBeTrue();
});

it('blocking creates database record', function () {
    $blocker = User::factory()->create();
    $userToBlock = User::factory()->create();

    $this->action->handle($blocker, $userToBlock->id);

    $this->assertDatabaseHas('blocked_users', [
        'blocker_id' => $blocker->id,
        'blocked_id' => $userToBlock->id,
    ]);
});

it('idempotent blocking same user twice', function () {
    $blocker = User::factory()->create();
    $userToBlock = User::factory()->create();

    $this->action->handle($blocker, $userToBlock->id);
    $this->action->handle($blocker, $userToBlock->id);

    expect($blocker->hasBlocked($userToBlock))->toBeTrue();
    expect($blocker->blockedUsers->count())->toBe(1);
});
