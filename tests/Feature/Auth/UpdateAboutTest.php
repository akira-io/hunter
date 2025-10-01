<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;

it('should update about section', function () {
    Event::fake();

    $user = App\Models\User::factory()->create();

    $this->actingAs($user)
        ->patch(route('profile.about'), [
            'bio' => 'This is my bio',
        ])
        ->assertRedirect(route('profile.edit'));

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'bio' => 'This is my bio',
    ]);
});
