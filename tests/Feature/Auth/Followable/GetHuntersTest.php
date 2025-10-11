<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake();
    $this->user = actingAsAuthUser();
    $this->hunter = App\Models\User::factory()->create();
});

it('should get all user hunters', function () {

    $this->hunter->follow($this->user);

    $this->get(route('followable.followers'))
        ->assertOk();

    expect($this->user->followers)->toHaveCount(1);

});

/**
 * Additional coverage for "get hunters" (followers) endpoint.
 *
 * Test framework: Pest PHP (pestphp/pest) with Laravel's Laravel\Testing utilities.
 * Framework: Laravel.
 */
it('returns empty hunters list when authenticated user has no followers', function () {
    $this->get(route('followable.followers'))
        ->assertOk();

    expect($this->user->followers)->toHaveCount(0);
});

it('returns multiple hunters when several users follow the authenticated user', function () {
    $hunters = App\Models\User::factory()->count(3)->create();

    foreach ($hunters as $h) {
        $h->follow($this->user);
    }

    $this->get(route('followable.followers'))
        ->assertOk();

    expect($this->user->followers)->toHaveCount(3);
});

it('only includes users who follow the authenticated user (directionality check)', function () {
    $nonFollower = App\Models\User::factory()->create();     // the auth user follows this account
    $this->user->follow($nonFollower);

    $follower = App\Models\User::factory()->create();        // this account follows the auth user
    $follower->follow($this->user);

    $this->get(route('followable.followers'))
        ->assertOk();

    $followers = $this->user->followers;
    expect($followers)->toHaveCount(1);
    expect($followers->pluck('id')->toArray())->toContain($follower->id);
    expect($followers->pluck('id')->toArray())->not->toContain($nonFollower->id);
});

it('does not duplicate hunters when follow is called multiple times for the same pair', function () {
    // Attempt to create a duplicate follow
    $this->hunter->follow($this->user);
    $this->hunter->follow($this->user);

    $this->get(route('followable.followers'))
        ->assertOk();

    expect($this->user->followers)->toHaveCount(1);
});

it('redirects unauthenticated guests to login when accessing hunters list', function () {
    // Override the file-level beforeEach auth by logging out
    auth()->logout();

    $response = $this->get(route('followable.followers'));

    expect($response->status())->toBe(302);
    // Prefer Laravel assertion for clarity if available in this stack
    if (function_exists('route')) {
        $response->assertRedirect(route('login'));
    }
});
