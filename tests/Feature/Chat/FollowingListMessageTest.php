<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\actingAs;

it('can create conversation from following list and navigate to chat', function () {
    $user = User::factory()->create();
    $followedUser = User::factory()->create();

    actingAs($user);

    // User follows the other user
    $user->follow($followedUser);

    // Create a direct conversation with the followed user
    $response = $this->postJson('/conversations', [
        'type' => 'direct',
        'participants' => [$followedUser->id],
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['id', 'message']);

    $conversationId = $response->json('id');

    expect($conversationId)->toBeInt();

    // Verify we can access the conversation
    $chatResponse = $this->get("/chat/{$conversationId}");
    $chatResponse->assertOk();
});

it('returns existing conversation when creating duplicate from following list', function () {
    $user = User::factory()->create();
    $followedUser = User::factory()->create();

    actingAs($user);

    // User follows the other user
    $user->follow($followedUser);

    // Create first conversation
    $firstResponse = $this->postJson('/conversations', [
        'type' => 'direct',
        'participants' => [$followedUser->id],
    ]);

    $firstResponse->assertStatus(201);
    $firstConversationId = $firstResponse->json('id');

    // Try to create duplicate conversation
    $secondResponse = $this->postJson('/conversations', [
        'type' => 'direct',
        'participants' => [$followedUser->id],
    ]);

    $secondResponse->assertStatus(200); // 200 because it returns existing
    $secondConversationId = $secondResponse->json('id');

    // Should return the same conversation ID
    expect($secondConversationId)->toBe($firstConversationId);
});

it('can message user without following them first', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    actingAs($user);

    // Create conversation without following
    $response = $this->postJson('/conversations', [
        'type' => 'direct',
        'participants' => [$otherUser->id],
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['id', 'message']);

    $conversationId = $response->json('id');

    // Verify we can access the conversation
    $chatResponse = $this->get("/chat/{$conversationId}");
    $chatResponse->assertOk();
});
