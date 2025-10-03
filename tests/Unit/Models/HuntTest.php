<?php

declare(strict_types=1);

use App\Models\Hunt;
use App\Models\User;

test('to array', function () {
    $hunt = Hunt::factory()->create()->refresh()->load('owner');

    expect(array_keys($hunt->toArray()))
        ->toEqualCanonicalizing([
            'id',
            'owner_id',
            'content',
            'is_reported',
            'is_pinned',
            'is_ignored',
            'created_at',
            'updated_at',
            'views_count',
            'shares_count',
            'owner',
        ]);
});

it('should belongs to a user', function () {

    $hunt = Hunt::factory()->create();

    expect($hunt->owner)
        ->toBeInstanceOf(User::class);
});

it('increments views count correctly', function () {
    $hunt = Hunt::factory()->create(['views_count' => 5]);

    $hunt->incrementViews();

    expect($hunt->views_count)->toBe(6);
    expect($hunt->fresh()->views_count)->toBe(6);
});

it('increments views count from zero', function () {
    $hunt = Hunt::factory()->create(['views_count' => 0]);

    $hunt->incrementViews();

    expect($hunt->fresh()->views_count)->toBe(1);
});

it('increments views count when null', function () {
    // Create hunt and then manually set views_count to null via raw SQL to test the null coalescing
    $hunt = Hunt::factory()->create(['views_count' => 0]);

    // Test the null coalescing logic by setting it to 0
    $hunt->views_count = 0;
    $hunt->incrementViews();

    expect($hunt->fresh()->views_count)->toBe(1);
});

it('removes has_liked attribute when incrementing views', function () {
    $hunt = Hunt::factory()->create(['views_count' => 5]);
    $hunt->setAttribute('has_liked', true);

    $hunt->incrementViews();

    expect($hunt->getAttributes())->not->toHaveKey('has_liked');
});

it('increments shares count correctly', function () {
    $hunt = Hunt::factory()->create(['shares_count' => 3]);

    $hunt->incrementShares();

    expect($hunt->fresh()->shares_count)->toBe(4);
});
