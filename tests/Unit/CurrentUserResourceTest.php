<?php

declare(strict_types=1);

use App\Actions\User\GetAvatarAction;
use App\Http\Resources\CurrentUserResource;
use App\Models\User;

beforeEach(function () {
    $this->getAvatarAction = new GetAvatarAction();
});

describe('CurrentUserResource', function () {
    it('returns correctly formatted user data with all required fields', function () {
        $user = User::factory()->create([
            'name' => 'John Doe',
        ]);

        $resource = new CurrentUserResource($user);
        $result = $resource->toArray(request());

        expect($result)->toBeArray()
            ->toHaveKeys(['id', 'name', 'avatar_url'])
            ->and($result['id'])->toBe($user->id)
            ->and($result['name'])->toBe('John Doe');
    });

    it('returns array with exact structure for Inertia props', function () {
        $user = User::factory()->create();

        $resource = new CurrentUserResource($user);
        $result = $resource->toArray(request());

        expect($result)->toBeArray()
            ->and(array_keys($result))->toBe(['id', 'name', 'avatar_url'])
            ->and(count($result))->toBe(3);
    });

    it('formats multiple users consistently', function () {
        $user1 = User::factory()->create(['name' => 'User One']);
        $user2 = User::factory()->create(['name' => 'User Two']);

        $result1 = (new CurrentUserResource($user1))->toArray(request());
        $result2 = (new CurrentUserResource($user2))->toArray(request());

        expect($result1)->toBeArray()
            ->and($result1['name'])->toBe('User One')
            ->and($result1)->toHaveKey('avatar_url')
            ->and($result2)->toBeArray()
            ->and($result2['name'])->toBe('User Two')
            ->and($result2)->toHaveKey('avatar_url');
    });

    it('preserves user ID as integer type', function () {
        $user = User::factory()->create();

        $resource = new CurrentUserResource($user);
        $result = $resource->toArray(request());

        expect($result['id'])->toBeInt()
            ->and($result['id'])->toBe($user->id);
    });

    it('preserves user name as string type', function () {
        $user = User::factory()->create(['name' => 'Test User']);

        $resource = new CurrentUserResource($user);
        $result = $resource->toArray(request());

        expect($result['name'])->toBeString()
            ->and($result['name'])->toBe('Test User');
    });

    it('includes avatar_url key even if null', function () {
        $user = User::factory()->create();

        $resource = new CurrentUserResource($user);
        $result = $resource->toArray(request());

        expect($result)->toHaveKey('avatar_url');
    });

    it('returns consistent structure for users with different attributes', function () {
        $userWithAvatarUrl = User::factory()->create([
            'name' => 'User With Avatar',
            'avatar_url' => 'https://example.com/avatar.jpg',
        ]);

        $userWithoutAvatarUrl = User::factory()->create([
            'name' => 'User Without Avatar',
            'avatar_url' => null,
        ]);

        $result1 = (new CurrentUserResource($userWithAvatarUrl))->toArray(request());
        $result2 = (new CurrentUserResource($userWithoutAvatarUrl))->toArray(request());

        expect(array_keys($result1))->toBe(array_keys($result2))
            ->and($result1)->toHaveKeys(['id', 'name', 'avatar_url'])
            ->and($result2)->toHaveKeys(['id', 'name', 'avatar_url']);
    });
});
