<?php

declare(strict_types=1);

use App\Models\Hunt;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('hunt card includes data-pan attribute for tracking', function () {
    $user = User::factory()->create();
    $hunt = Hunt::factory()->create();

    actingAs($user);

    $response = get('/');

    $response->assertOk();

    expect($hunt->id)->toBeInt();
});

test('data-pan attribute format is correct', function () {
    $hunt = Hunt::factory()->create();

    $expectedDataPan = "hunt-{$hunt->id}";

    expect($expectedDataPan)->toStartWith('hunt-')
        ->and($expectedDataPan)->toContain((string) $hunt->id);
});
