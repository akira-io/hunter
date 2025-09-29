<?php

declare(strict_types=1);

use Akira\Followable\Exceptions\CannotFollowYourSelfException;
use App\Actions\Social\FollowUserAction;
use App\Models\User;

test('it can follow a user', function () {
    $follower = User::factory()->create();
    $userToFollow = User::factory()->create();
    $action = new FollowUserAction();

    $action->handle($follower, $userToFollow);

    expect($follower->isFollowing($userToFollow))->toBeTrue()
        ->and($userToFollow->isFollowedBy($follower))->toBeTrue();
});

test('it prevents following yourself', function () {
    $user = User::factory()->create();
    $action = new FollowUserAction();

    expect(function () use ($action, $user) {
        $action->handle($user, $user);
    })->toThrow(CannotFollowYourSelfException::class);
});
