<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can create conversation when recipient allows everyone', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create([
        'privacy_settings' => [
            'who_can_message' => 'everyone',
        ],
    ]);

    $response = $this->actingAs($sender)->postJson('/conversations', [
        'type' => 'direct',
        'participants' => [$recipient->id],
    ]);

    $response->assertSuccessful();
});

test('follower can create conversation when recipient allows followers only', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create([
        'privacy_settings' => [
            'who_can_message' => 'followers',
        ],
    ]);

    $sender->follow($recipient);

    $response = $this->actingAs($sender)->postJson('/conversations', [
        'type' => 'direct',
        'participants' => [$recipient->id],
    ]);

    $response->assertSuccessful();
});

test('non-follower cannot create conversation when recipient allows followers only', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create([
        'privacy_settings' => [
            'who_can_message' => 'followers',
        ],
    ]);

    $response = $this->actingAs($sender)->postJson('/conversations', [
        'type' => 'direct',
        'participants' => [$recipient->id],
    ]);

    $response->assertStatus(422);
    $response->assertJson(['error' => 'Este utilizador não aceita mensagens.']);
});

test('user cannot create conversation when recipient disables messages', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create([
        'privacy_settings' => [
            'who_can_message' => 'none',
        ],
    ]);

    $response = $this->actingAs($sender)->postJson('/conversations', [
        'type' => 'direct',
        'participants' => [$recipient->id],
    ]);

    $response->assertStatus(422);
    $response->assertJson(['error' => 'Este utilizador não aceita mensagens.']);
});

test('blocked user cannot create conversation', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create([
        'privacy_settings' => [
            'who_can_message' => 'everyone',
        ],
    ]);

    $recipient->block($sender);

    $response = $this->actingAs($sender)->postJson('/conversations', [
        'type' => 'direct',
        'participants' => [$recipient->id],
    ]);

    $response->assertStatus(422);
    $response->assertJson(['error' => 'Este utilizador não aceita mensagens.']);
});

test('user who blocked cannot create conversation with blocked user', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create([
        'privacy_settings' => [
            'who_can_message' => 'everyone',
        ],
    ]);

    $sender->block($recipient);

    $response = $this->actingAs($sender)->postJson('/conversations', [
        'type' => 'direct',
        'participants' => [$recipient->id],
    ]);

    $response->assertStatus(422);
    $response->assertJson(['error' => 'Este utilizador não aceita mensagens.']);
});
