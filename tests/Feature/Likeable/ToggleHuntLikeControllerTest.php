<?php

declare(strict_types=1);

use App\Models\Hunt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->hunt = Hunt::factory()->create(['owner_id' => $this->user->id]);
});

it('toggles like on a hunt and redirects to hunts index', function () {
    actingAsAuthUser();

    $response = $this->post(route('hunts.toggle-like', $this->hunt));

    $response->assertRedirectBack();
});

it('requires authentication to toggle like on a hunt', function () {
    $response = $this->post(route('hunts.toggle-like', $this->hunt));

    $response->assertRedirect(route('login'));
});

it('returns 404 for non-existent hunt', function () {
    actingAsAuthUser();

    $response = $this->post(route('hunts.toggle-like', 999));

    $response->assertStatus(404);
});
