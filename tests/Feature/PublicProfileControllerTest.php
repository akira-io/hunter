<?php

declare(strict_types=1);

use App\Models\Hunt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();

    // Create some hunts for the other user
    $this->hunts = Hunt::factory()->count(3)->create([
        'owner_id' => $this->otherUser->id,
    ]);

    // Make the user follow the other user
    $this->user->follow($this->otherUser);
});

it('displays a user public profile with hunts, hunters and huntings', function () {
    // Act as the authenticated user
    $response = $this->actingAs($this->user)
        ->get(route('public.profile.show', $this->otherUser));

    $response->assertStatus(200);
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('public-profile')
        ->has('user')
        ->has('hunts.data', 3)
        ->has('hunters')
        ->has('huntings')
    );
});

it('requires authentication to view public profile', function () {
    $response = $this->get(route('public.profile.show', $this->otherUser));

    $response->assertRedirect(route('login'));
});

it('returns 404 for non-existent user', function () {
    $response = $this->actingAs($this->user)
        ->get(route('public.profile.show', 999));

    $response->assertStatus(404);
});

// Additional scenarios for PublicProfileController public profile view.
// Framework: Pest PHP + Laravel test utilities + Inertia AssertableInertia.

it('orders hunts by most recent first on public profile', function () {
    $viewer = User::factory()->create();
    $profileOwner = User::factory()->create();

    // Create 3 hunts with predictable timestamps
    $oldest = Hunt::factory()->create([
        'owner_id' => $profileOwner->id,
        'created_at' => now()->subDays(3),
    ]);
    $middle = Hunt::factory()->create([
        'owner_id' => $profileOwner->id,
        'created_at' => now()->subDays(2),
    ]);
    $newest = Hunt::factory()->create([
        'owner_id' => $profileOwner->id,
        'created_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($viewer)
        ->get(route('public.profile.show', $profileOwner));

    $response->assertStatus(200);
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('public-profile')
        // Ensure we have three hunts in the payload
        ->has('hunts.data', 3)
        // Assert the first item corresponds to the most recent hunt (by id or created_at order)
        ->where('hunts.data.0.id', $newest->id)
        ->where('hunts.data.1.id', $middle->id)
        ->where('hunts.data.2.id', $oldest->id)
    );
});

it('shows empty hunts list when the profiled user has no hunts', function () {
    $viewer = User::factory()->create();
    $profileOwner = User::factory()->create();

    $response = $this->actingAs($viewer)
        ->get(route('public.profile.show', $profileOwner));

    $response->assertStatus(200);
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('public-profile')
        ->has('hunts.data', 0)
    );
});

it('includes followers (hunters) and followings (huntings) counts accurately', function () {
    $viewer = User::factory()->create(); // authenticated viewer
    $profileOwner = User::factory()->create();

    // Two users follow the profile owner (hunters)
    $hunterA = User::factory()->create();
    $hunterB = User::factory()->create();
    $hunterA->follow($profileOwner);
    $hunterB->follow($profileOwner);

    // Profile owner follows three others (huntings)
    $follow1 = User::factory()->create();
    $follow2 = User::factory()->create();
    $follow3 = User::factory()->create();
    $profileOwner->follow($follow1);
    $profileOwner->follow($follow2);
    $profileOwner->follow($follow3);

    $response = $this->actingAs($viewer)
        ->get(route('public.profile.show', $profileOwner));

    $response->assertStatus(200);
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('public-profile')
        // Prefer counts if collections are paginated; otherwise count array items.
        ->where('hunters', fn ($hunters) => is_countable($hunters) ? count($hunters) === 2 : (data_get($hunters, 'data') ? count($hunters['data']) === 2 : true))
        ->where('huntings', fn ($huntings) => is_countable($huntings) ? count($huntings) === 3 : (data_get($huntings, 'data') ? count($huntings['data']) === 3 : true))
    );
});

it('does not mix in hunts from other users', function () {
    $viewer = User::factory()->create();
    $profileOwner = User::factory()->create();
    $otherUser = User::factory()->create();

    // Hunts for profile owner
    $ownerHunts = Hunt::factory()->count(2)->create([
        'owner_id' => $profileOwner->id,
    ]);

    // Hunts for a different user
    $strayHunts = Hunt::factory()->count(2)->create([
        'owner_id' => $otherUser->id,
    ]);

    $response = $this->actingAs($viewer)
        ->get(route('public.profile.show', $profileOwner));

    $response->assertStatus(200);
    $response->assertInertia(fn (AssertableInertia $page) => $page->component('public-profile')
        ->has('hunts.data', 2)
        ->where('hunts.data', fn ($data) => collect($data)->every(fn ($h) => $h['owner']['id'] === $profileOwner->id)
        )
    );
});

it('returns 404 when the profiled user is deleted (soft or hard)', function () {
    $viewer = User::factory()->create();
    $deleted = User::factory()->create();
    $deleted->delete(); // soft delete or hard delete; either should 404 via route model binding

    $response = $this->actingAs($viewer)
        ->get(route('public.profile.show', $deleted->id));

    $response->assertStatus(404);
});

it('returns 404 for invalid username or id format', function () {
    $viewer = User::factory()->create();

    $nonExistentId = PHP_INT_MAX;

    $response = $this->actingAs($viewer)
        ->get(route('public.profile.show', $nonExistentId));

    $response->assertStatus(404);
});

it('exposes minimal expected user payload on profile', function () {
    $viewer = User::factory()->create();
    $profileOwner = User::factory()->create();

    $response = $this->actingAs($viewer)
        ->get(route('public.profile.show', $profileOwner));

    $response->assertStatus(200);
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('public-profile')
        ->has('user')
        ->where('user.id', $profileOwner->id));
});
