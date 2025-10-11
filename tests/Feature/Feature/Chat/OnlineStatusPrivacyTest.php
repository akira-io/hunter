<?php

declare(strict_types=1);

use App\Actions\Chat\GetOnlineUsersAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
});

describe('Online Status Privacy - GetOnlineUsersAction', function () {
    it('returns online users when viewer has activity status enabled', function () {
        $viewer = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => true],
        ]);

        $onlineUser = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => true],
        ]);

        // Follow the online user
        $viewer->follow($onlineUser);
        $onlineUser->acceptFollowRequestFrom($viewer);

        // Mark user as online
        Cache::put("user_online_{$onlineUser->id}", now(), now()->addMinutes(10));

        $action = new GetOnlineUsersAction();
        $result = $action->handle($viewer);

        expect($result)->toHaveCount(1)
            ->and($result->first()->id)->toBe($onlineUser->id);
    });

    it('returns empty collection when viewer has activity status disabled', function () {
        $viewer = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => false],
        ]);

        $onlineUser = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => true],
        ]);

        // Follow the online user
        $viewer->follow($onlineUser);
        $onlineUser->acceptFollowRequestFrom($viewer);

        // Mark user as online
        Cache::put("user_online_{$onlineUser->id}", now(), now()->addMinutes(10));

        $action = new GetOnlineUsersAction();
        $result = $action->handle($viewer);

        // Viewer can't see anyone's status because they disabled their own
        expect($result)->toBeEmpty();
    });

    it('does not show users who have activity status disabled', function () {
        $viewer = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => true],
        ]);

        $hiddenUser = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => false],
        ]);

        // Follow the hidden user
        $viewer->follow($hiddenUser);
        $hiddenUser->acceptFollowRequestFrom($viewer);

        // Mark user as online
        Cache::put("user_online_{$hiddenUser->id}", now(), now()->addMinutes(10));

        $action = new GetOnlineUsersAction();
        $result = $action->handle($viewer);

        // Hidden user should not appear in results
        expect($result)->toBeEmpty();
    });

    it('implements mutual privacy correctly', function () {
        $viewer = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => false],
        ]);

        $user1 = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => true],
        ]);

        $user2 = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => false],
        ]);

        // Follow both users
        $viewer->follow($user1);
        $user1->acceptFollowRequestFrom($viewer);
        $viewer->follow($user2);
        $user2->acceptFollowRequestFrom($viewer);

        // Mark both as online
        Cache::put("user_online_{$user1->id}", now(), now()->addMinutes(10));
        Cache::put("user_online_{$user2->id}", now(), now()->addMinutes(10));

        $action = new GetOnlineUsersAction();
        $result = $action->handle($viewer);

        // Viewer should see no one because they disabled their own status
        expect($result)->toBeEmpty();
    });

    it('filters blocked users from online list', function () {
        $viewer = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => true],
        ]);

        $blockedUser = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => true],
        ]);

        // Follow and block the user
        $viewer->follow($blockedUser);
        $blockedUser->acceptFollowRequestFrom($viewer);
        $viewer->block($blockedUser);

        // Mark user as online
        Cache::put("user_online_{$blockedUser->id}", now(), now()->addMinutes(10));

        $action = new GetOnlineUsersAction();
        $result = $action->handle($viewer);

        // Blocked user should not appear
        expect($result)->toBeEmpty();
    });
});

describe('Online Status Privacy - User Model', function () {
    it('canShowOnlineStatusTo returns false when user has status disabled', function () {
        $user = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => false],
        ]);

        $viewer = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => true],
        ]);

        expect($user->canShowOnlineStatusTo($viewer))->toBeFalse();
    });

    it('canShowOnlineStatusTo returns false when viewer has status disabled', function () {
        $user = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => true],
        ]);

        $viewer = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => false],
        ]);

        expect($user->canShowOnlineStatusTo($viewer))->toBeFalse();
    });

    it('canShowOnlineStatusTo returns true when both have status enabled', function () {
        $user = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => true],
        ]);

        $viewer = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => true],
        ]);

        expect($user->canShowOnlineStatusTo($viewer))->toBeTrue();
    });

    it('canShowOnlineStatusTo returns false when users are blocked', function () {
        $user = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => true],
        ]);

        $viewer = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => true],
        ]);

        $viewer->block($user);

        expect($user->canShowOnlineStatusTo($viewer))->toBeFalse();
    });

    it('isOnline returns true when cache exists', function () {
        $user = User::factory()->create();

        Cache::put("user_online_{$user->id}", now(), now()->addMinutes(10));

        expect($user->isOnline())->toBeTrue();
    });

    it('isOnline returns false when cache does not exist', function () {
        $user = User::factory()->create();

        expect($user->isOnline())->toBeFalse();
    });
});

describe('Online Status Privacy - UpdatePrivacySettingsAction', function () {
    it('clears cache and dispatches offline event when status is disabled', function () {
        Event::fake();

        $user = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => true],
        ]);

        Cache::put("user_online_{$user->id}", now(), now()->addMinutes(10));

        $action = new App\Actions\Settings\UpdatePrivacySettingsAction();
        $action->handle($user, [
            'show_activity_status' => false,
            'profile_visibility' => 'public',
            'who_can_message' => 'everyone',
            'who_can_comment' => 'everyone',
            'searchable' => true,
        ]);

        expect(Cache::has("user_online_{$user->id}"))->toBeFalse();

        Event::assertDispatched(App\Events\UserOffline::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    });

    it('does not dispatch offline event when status remains enabled', function () {
        Event::fake();

        $user = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => true],
        ]);

        Cache::put("user_online_{$user->id}", now(), now()->addMinutes(10));

        $action = new App\Actions\Settings\UpdatePrivacySettingsAction();
        $action->handle($user, [
            'show_activity_status' => true,
            'profile_visibility' => 'public',
            'who_can_message' => 'everyone',
            'who_can_comment' => 'everyone',
            'searchable' => true,
        ]);

        expect(Cache::has("user_online_{$user->id}"))->toBeTrue();

        Event::assertNotDispatched(App\Events\UserOffline::class);
    });

    it('does not dispatch offline event when status remains disabled', function () {
        Event::fake();

        $user = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => false],
        ]);

        $action = new App\Actions\Settings\UpdatePrivacySettingsAction();
        $action->handle($user, [
            'show_activity_status' => false,
            'profile_visibility' => 'public',
            'who_can_message' => 'everyone',
            'who_can_comment' => 'everyone',
            'searchable' => true,
        ]);

        Event::assertNotDispatched(App\Events\UserOffline::class);
    });
});

describe('Online Status Privacy - TrackUserPresence Middleware', function () {
    it('does not track presence when user has status disabled', function () {
        $user = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => false],
        ]);

        $this->actingAs($user)
            ->get('/');

        expect(Cache::has("user_online_{$user->id}"))->toBeFalse();
    });

    it('tracks presence when user has status enabled', function () {
        $user = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => true],
        ]);

        $this->actingAs($user)
            ->get('/');

        expect(Cache::has("user_online_{$user->id}"))->toBeTrue();
    });
});

describe('Online Status Privacy - UserResource', function () {
    it('includes is_online when both users have status enabled', function () {
        $viewer = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => true],
        ]);

        $user = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => true],
        ]);

        Cache::put("user_online_{$user->id}", now(), now()->addMinutes(10));

        $resource = new App\Http\Resources\UserResource($user);
        $array = $resource->toArray(
            Illuminate\Http\Request::create('/', 'GET')->setUserResolver(fn () => $viewer)
        );

        expect($array['is_online'])->toBeTrue();
    });

    it('shows offline when viewer has status disabled', function () {
        $viewer = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => false],
        ]);

        $user = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => true],
        ]);

        Cache::put("user_online_{$user->id}", now(), now()->addMinutes(10));

        $resource = new App\Http\Resources\UserResource($user);
        $array = $resource->toArray(
            Illuminate\Http\Request::create('/', 'GET')->setUserResolver(fn () => $viewer)
        );

        expect($array['is_online'])->toBeFalse();
    });

    it('shows offline when user has status disabled', function () {
        $viewer = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => true],
        ]);

        $user = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => false],
        ]);

        Cache::put("user_online_{$user->id}", now(), now()->addMinutes(10));

        $resource = new App\Http\Resources\UserResource($user);
        $array = $resource->toArray(
            Illuminate\Http\Request::create('/', 'GET')->setUserResolver(fn () => $viewer)
        );

        expect($array['is_online'])->toBeFalse();
    });
});

describe('Online Status Privacy - FollowedHuntersController', function () {
    it('shows online status when both users have status enabled', function () {
        $viewer = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => true],
        ]);

        $followed = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => true],
        ]);

        $viewer->follow($followed);
        $followed->acceptFollowRequestFrom($viewer);

        Cache::put("user_online_{$followed->id}", now(), now()->addMinutes(10));

        $response = $this->actingAs($viewer)
            ->getJson('/followed-hunters');

        $response->assertOk();

        $hunters = $response->json();
        expect($hunters[0]['is_online'])->toBeTrue();
    });

    it('hides online status when viewer has status disabled', function () {
        $viewer = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => false],
        ]);

        $followed = User::factory()->create([
            'privacy_settings' => ['show_activity_status' => true],
        ]);

        $viewer->follow($followed);
        $followed->acceptFollowRequestFrom($viewer);

        Cache::put("user_online_{$followed->id}", now(), now()->addMinutes(10));

        $response = $this->actingAs($viewer)
            ->getJson('/followed-hunters');

        $response->assertOk();

        $hunters = $response->json();
        expect($hunters[0]['is_online'])->toBeFalse();
    });
});
