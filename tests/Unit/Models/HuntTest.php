<?php

declare(strict_types=1);

use App\Models\Hunt;
use App\Models\User;

test('to array', function () {
    $hunt = Hunt::factory()->create()->refresh();

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
            'owner',
        ]);
});

it('should belongs to a user', function () {

    $hunt = Hunt::factory()->create();

    expect($hunt->owner)
        ->toBeInstanceOf(User::class);
});
