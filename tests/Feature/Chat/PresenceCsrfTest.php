<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\actingAs;

it('allows posting presence endpoints without CSRF token', function () {
    $user = User::factory()->create();

    actingAs($user);

    // No explicit CSRF token sent
    $online = $this->post('/presence/online');
    $online->assertOk()->assertJson(['status' => 'online', 'user_id' => $user->id]);

    $offline = $this->post('/presence/offline');
    $offline->assertOk()->assertJson(['status' => 'offline', 'user_id' => $user->id]);
});
