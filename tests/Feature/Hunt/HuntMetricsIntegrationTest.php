<?php

declare(strict_types=1);

use App\Models\Hunt;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('increments view count when viewing hunt detail', function () {
    $owner = User::factory()->create();
    $user = User::factory()->create();

    $hunt = Hunt::factory()->create([
        'owner_id' => $owner->id,
        'views_count' => 10,
        'shares_count' => 0,
    ]);

    actingAs($user)->get(route('hunts.show', $hunt));

    $hunt->refresh();

    expect($hunt->views_count)->toBe(11);
});
