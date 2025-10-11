<?php

declare(strict_types=1);

use App\Models\Hunt;
use App\Models\User;

beforeEach(function () {
    $this->user = actingAsAuthUser();
});

it('shows all hunts created by the authenticated user', function () {
    // Create multiple hunts for the authenticated user
    $hunt1 = Hunt::factory()->create([
        'owner_id' => $this->user->id,
        'content' => 'First hunt',
        'created_at' => now()->subHours(3),
    ]);

    $hunt2 = Hunt::factory()->create([
        'owner_id' => $this->user->id,
        'content' => 'Second hunt',
        'created_at' => now()->subHours(2),
    ]);

    $hunt3 = Hunt::factory()->create([
        'owner_id' => $this->user->id,
        'content' => 'Third hunt',
        'created_at' => now()->subHour(),
    ]);

    $response = $this->get(route('hunts.my'));

    $response->assertSuccessful();

    $hunts = $response->viewData('page')['props']['hunts']['data'];

    // Assert all three hunts are shown
    expect($hunts)->toHaveCount(3);

    // Verify all hunts belong to the authenticated user
    foreach ($hunts as $hunt) {
        expect($hunt['owner']['id'])->toBe($this->user->id);
    }
});

it('orders hunts from newest to oldest', function () {
    // Create hunts in random order
    $oldHunt = Hunt::factory()->create([
        'owner_id' => $this->user->id,
        'content' => 'Old hunt',
        'created_at' => now()->subDays(3),
    ]);

    $newestHunt = Hunt::factory()->create([
        'owner_id' => $this->user->id,
        'content' => 'Newest hunt',
        'created_at' => now(),
    ]);

    $middleHunt = Hunt::factory()->create([
        'owner_id' => $this->user->id,
        'content' => 'Middle hunt',
        'created_at' => now()->subDay(),
    ]);

    $response = $this->get(route('hunts.my'));

    $response->assertSuccessful();

    $hunts = $response->viewData('page')['props']['hunts']['data'];

    // Assert correct order (newest first)
    expect($hunts[0]['content'])->toBe('Newest hunt');
    expect($hunts[1]['content'])->toBe('Middle hunt');
    expect($hunts[2]['content'])->toBe('Old hunt');
});

it('does not show hunts from other users', function () {
    $otherUser = User::factory()->create();

    // Create hunts for the authenticated user
    Hunt::factory()->create([
        'owner_id' => $this->user->id,
        'content' => 'My hunt',
    ]);

    // Create hunts for another user
    Hunt::factory()->count(3)->create([
        'owner_id' => $otherUser->id,
    ]);

    $response = $this->get(route('hunts.my'));

    $response->assertSuccessful();

    $hunts = $response->viewData('page')['props']['hunts']['data'];

    // Assert only the authenticated user's hunt is shown
    expect($hunts)->toHaveCount(1);
    expect($hunts[0]['owner']['id'])->toBe($this->user->id);
});

it('shows empty state when user has no hunts', function () {
    $response = $this->get(route('hunts.my'));

    $response->assertSuccessful();

    $hunts = $response->viewData('page')['props']['hunts']['data'];

    expect($hunts)->toHaveCount(0);
});

it('includes hunt metrics for own hunts', function () {
    $hunt = Hunt::factory()->create([
        'owner_id' => $this->user->id,
        'content' => 'Hunt with metrics',
    ]);

    $response = $this->get(route('hunts.my'));

    $response->assertSuccessful();

    $hunts = $response->viewData('page')['props']['hunts']['data'];

    expect($hunts)->toHaveCount(1);
    expect($hunts[0])->toHaveKey('metrics');
    expect($hunts[0]['is_owner'])->toBeTrue();
});

it('requires authentication', function () {
    auth()->logout();

    $response = $this->get(route('hunts.my'));

    $response->assertRedirect(route('login'));
});

it('paginates hunts correctly', function () {
    // Create more hunts than the default pagination limit
    Hunt::factory()->count(20)->create([
        'owner_id' => $this->user->id,
    ]);

    $response = $this->get(route('hunts.my'));

    $response->assertSuccessful();

    $huntsData = $response->viewData('page')['props']['hunts'];

    // Check pagination structure exists
    expect($huntsData)->toHaveKey('data');
});
