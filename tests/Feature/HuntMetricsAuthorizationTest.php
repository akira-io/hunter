<?php

declare(strict_types=1);

use App\Models\Hunt;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('Hunt Metrics Authorization', function () {
    beforeEach(function () {
        $this->owner = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->hunt = Hunt::factory()->create([
            'owner_id' => $this->owner->id,
            'views_count' => 100,
            'shares_count' => 10,
        ]);
    });

    it('shows metrics to hunt owner on show page', function () {
        actingAs($this->owner);

        $response = get(route('hunts.show', ['hunt' => $this->hunt->id]));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('hunts/show')
                ->where('hunt.is_owner', true)
                ->where('hunt.views', 100)
                ->where('hunt.shares', 10)
                ->has('hunt.metrics')
            );
    });

    it('hides metrics from non-owner on show page', function () {
        actingAs($this->otherUser);

        $response = get(route('hunts.show', ['hunt' => $this->hunt->id]));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('hunts/show')
                ->where('hunt.is_owner', false)
                ->where('hunt.views', null)
                ->where('hunt.shares', null)
                ->where('hunt.metrics', null)
            );
    });

    it('shows likes_count to all users', function () {
        actingAs($this->otherUser);

        // Add some likes
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $this->hunt->likes()->create(['user_id' => $user1->id]);
        $this->hunt->likes()->create(['user_id' => $user2->id]);

        $response = get(route('hunts.show', ['hunt' => $this->hunt->id]));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('hunt.likes_count', 2)
            );
    });

    it('shows comments to all users', function () {
        actingAs($this->otherUser);

        $response = get(route('hunts.show', ['hunt' => $this->hunt->id]));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('hunt.comments')
            );
    });

    it('includes is_owner flag correctly for owner', function () {
        actingAs($this->owner);

        $response = get(route('hunts.show', ['hunt' => $this->hunt->id]));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('hunt.is_owner', true)
            );
    });

    it('includes is_owner flag correctly for non-owner', function () {
        actingAs($this->otherUser);

        $response = get(route('hunts.show', ['hunt' => $this->hunt->id]));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('hunt.is_owner', false)
            );
    });

    it('shows metrics to owner on hunts index', function () {
        actingAs($this->owner);

        $response = get(route('hunts.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('hunts/hunts')
                ->has('hunts')
            );
    });

    it('hides detailed metrics from non-owner on hunts index', function () {
        actingAs($this->otherUser);

        $response = get(route('hunts.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('hunts/hunts')
                ->has('hunts')
            );
    });

    it('allows owner to see their own hunt metrics with null shares', function () {
        actingAs($this->owner);

        $huntWithoutShares = Hunt::factory()->create([
            'owner_id' => $this->owner->id,
            'views_count' => 50,
            'shares_count' => 0,
        ]);

        $response = get(route('hunts.show', ['hunt' => $huntWithoutShares->id]));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('hunt.is_owner', true)
                ->where('hunt.views', 50)
                ->where('hunt.shares', 0)
            );
    });

    it('prevents non-owner from accessing metrics via API resource', function () {
        actingAs($this->otherUser);

        $response = get(route('hunts.show', ['hunt' => $this->hunt->id]));

        $response->assertOk();

        $huntData = $response->viewData('page')['props']['hunt'];

        expect($huntData['is_owner'])->toBeFalse()
            ->and($huntData['views'])->toBeNull()
            ->and($huntData['shares'])->toBeNull()
            ->and($huntData['metrics'])->toBeNull();
    });

    it('maintains backward compatibility with likes_count visibility', function () {
        actingAs($this->otherUser);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        $this->hunt->likes()->create(['user_id' => $user1->id]);
        $this->hunt->likes()->create(['user_id' => $user2->id]);
        $this->hunt->likes()->create(['user_id' => $user3->id]);

        $response = get(route('hunts.show', ['hunt' => $this->hunt->id]));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('hunt.likes_count', 3)
            );
    });
});
