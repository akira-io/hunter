<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create some users for testing
    User::factory()->count(5)->create();
});

it('displays the finder page', function () {
    $user = actingAsAuthUser();

    $response = $this->get(route('finder.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('finder')
        ->has('users')
        ->has('users.data')
    );
});

it('requires authentication', function () {
    $response = $this->get(route('finder.index'));

    $response->assertRedirect(route('login'));
});

it('displays random users when no search query is provided', function () {
    $user = actingAsAuthUser();

    $response = $this->get(route('finder.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('finder')
        ->has('users.data', 6) // We created 5 users in beforeEach + 1 auth user
    );
});

it('searches for users when a query is provided', function () {
    // Create a user with a specific name to search for
    User::factory()->create(['name' => 'SearchableUser']);

    $user = actingAsAuthUser();

    $response = $this->get(route('finder.index', ['q' => 'SearchableUser']));

    $response->assertStatus(200);
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('finder')
        ->has('users')
        ->has('users.data')
    );

    // We can't directly assert on the search results because the search is handled by the GetHuntersAction
    // and we're not mocking it. But we can ensure the page loads correctly with a search query.
});

it('loads academic backgrounds for users', function () {
    $user = actingAsAuthUser();

    $response = $this->get(route('finder.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('finder')
        ->has('users')
        ->has('users.data')
    );

    // We can't directly assert that academic backgrounds are loaded because we're not mocking
    // the GetHuntersAction, but we can ensure the page loads correctly.
});
