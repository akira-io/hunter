<?php

declare(strict_types=1);

beforeEach(function () {
    $this->user = actingAsAuthUser();
    $this->hunter = App\Models\User::factory()->create();
});

it('should get all user hunters', function () {

    $this->hunter->follow($this->user);

    $response = $this->get(route('followable.followers'));

    $followers = $response->getOriginalContent()->getData()['page']['props']['followers'];

    expect($response->status())
        ->toBe(200)
        ->and($followers)
        ->toHaveCount(1);

});

/**
 * Additional coverage for "get hunters" (followers) endpoint.
 *
 * Test framework: Pest PHP (pestphp/pest) with Laravel's Laravel\Testing utilities.
 * Framework: Laravel.
 */

it('returns empty hunters list when authenticated user has no followers', function () {
    $response = $this->get(route('followable.followers'));

    $followers = $response->getOriginalContent()->getData()['page']['props']['followers'];

    expect($response->status())
        ->toBe(200)
        ->and($followers)
        ->toHaveCount(0);
});

it('returns multiple hunters when several users follow the authenticated user', function () {
    $hunters = App\Models\User::factory()->count(3)->create();

    foreach ($hunters as $h) {
        $h->follow($this->user);
    }

    $response = $this->get(route('followable.followers'));

    $followers = $response->getOriginalContent()->getData()['page']['props']['followers'];
    $followersArr = json_decode(json_encode($followers), true);
    $ids = array_column($followersArr, 'id');

    expect($response->status())
        ->toBe(200)
        ->and($followers)
        ->toHaveCount(3)
        ->and($ids)
        ->toContain($hunters[0]->id, $hunters[1]->id, $hunters[2]->id);
});

it('only includes users who follow the authenticated user (directionality check)', function () {
    $nonFollower = App\Models\User::factory()->create();     // the auth user follows this account
    $this->user->follow($nonFollower);

    $follower = App\Models\User::factory()->create();        // this account follows the auth user
    $follower->follow($this->user);

    $response = $this->get(route('followable.followers'));

    $followers = $response->getOriginalContent()->getData()['page']['props']['followers'];
    $followersArr = json_decode(json_encode($followers), true);
    $ids = array_column($followersArr, 'id');

    expect($response->status())->toBe(200);
    expect($ids)->toContain($follower->id);
    expect($ids)->not->toContain($nonFollower->id);
});

it('does not duplicate hunters when follow is called multiple times for the same pair', function () {
    // Attempt to create a duplicate follow
    $this->hunter->follow($this->user);
    $this->hunter->follow($this->user);

    $response = $this->get(route('followable.followers'));

    $followers = $response->getOriginalContent()->getData()['page']['props']['followers'];
    $followersArr = json_decode(json_encode($followers), true);
    $ids = array_column($followersArr, 'id');
    $counts = array_count_values($ids);

    expect($response->status())
        ->toBe(200)
        ->and($followers)
        ->toHaveCount(1)
        ->and($counts[$this->hunter->id] ?? 0)
        ->toBe(1);
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
