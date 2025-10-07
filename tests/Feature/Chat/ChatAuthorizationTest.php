<?php

declare(strict_types=1);

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

use function Pest\Laravel\actingAs;

describe('Chat Authorization', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->nonParticipant = User::factory()->create();
    });

    describe('ChatController', function () {
        it('allows participant to access chat show page', function () {
            $conversation = Conversation::factory()->direct()->create();
            $conversation->participants()->attach([
                $this->user->id => ['joined_at' => now(), 'is_admin' => true],
                $this->otherUser->id => ['joined_at' => now(), 'is_admin' => false],
            ]);

            actingAs($this->user);
            $response = $this->get("/chat/{$conversation->id}");

            $response->assertSuccessful();
        });

        it('denies non-participant access to chat show page', function () {
            $conversation = Conversation::factory()->direct()->create();
            $conversation->participants()->attach([
                $this->user->id => ['joined_at' => now(), 'is_admin' => true],
                $this->otherUser->id => ['joined_at' => now(), 'is_admin' => false],
            ]);

            actingAs($this->nonParticipant);
            $response = $this->get("/chat/{$conversation->id}");

            $response->assertForbidden();
        });

        it('denies access to non-existent conversation', function () {
            actingAs($this->user);
            $response = $this->get('/chat/99999');

            $response->assertForbidden();
        });
    });

    describe('ConversationController API', function () {
        it('allows participant to view conversation messages', function () {
            $conversation = Conversation::factory()->direct()->create();
            $conversation->participants()->attach([
                $this->user->id => ['joined_at' => now(), 'is_admin' => true],
                $this->otherUser->id => ['joined_at' => now(), 'is_admin' => false],
            ]);

            Message::factory()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $this->otherUser->id,
                'content' => 'Test message',
            ]);

            actingAs($this->user);
            $response = $this->getJson("/conversations/{$conversation->id}");

            $response->assertSuccessful()
                ->assertJsonPath('id', $conversation->id)
                ->assertJsonPath('messages.0.content', 'Test message');
        });

        it('denies non-participant access to conversation messages', function () {
            $conversation = Conversation::factory()->direct()->create();
            $conversation->participants()->attach([
                $this->user->id => ['joined_at' => now(), 'is_admin' => true],
                $this->otherUser->id => ['joined_at' => now(), 'is_admin' => false],
            ]);

            actingAs($this->nonParticipant);
            $response = $this->getJson("/conversations/{$conversation->id}");

            $response->assertNotFound();
        });

        it('denies non-participant from deleting conversation', function () {
            $conversation = Conversation::factory()->direct()->create();
            $conversation->participants()->attach([
                $this->user->id => ['joined_at' => now(), 'is_admin' => true],
                $this->otherUser->id => ['joined_at' => now(), 'is_admin' => false],
            ]);

            actingAs($this->nonParticipant);
            $response = $this->deleteJson("/conversations/{$conversation->id}");

            $response->assertNotFound();
        });
    });

    describe('MessageController API', function () {
        it('allows participant to send message', function () {
            $conversation = Conversation::factory()->direct()->create();
            $conversation->participants()->attach([
                $this->user->id => ['joined_at' => now(), 'is_admin' => true],
                $this->otherUser->id => ['joined_at' => now(), 'is_admin' => false],
            ]);

            actingAs($this->user);
            $response = $this->postJson('/messages', [
                'conversation_id' => $conversation->id,
                'content' => 'Hello from participant',
                'type' => 'text',
            ]);

            $response->assertSuccessful()
                ->assertJsonPath('content', 'Hello from participant');
        });

        it('denies non-participant from sending message', function () {
            $conversation = Conversation::factory()->direct()->create();
            $conversation->participants()->attach([
                $this->user->id => ['joined_at' => now(), 'is_admin' => true],
                $this->otherUser->id => ['joined_at' => now(), 'is_admin' => false],
            ]);

            actingAs($this->nonParticipant);
            $response = $this->postJson('/messages', [
                'conversation_id' => $conversation->id,
                'content' => 'Unauthorized message',
                'type' => 'text',
            ]);

            $response->assertNotFound();
        });

        it('allows participant to mark messages as read', function () {
            $conversation = Conversation::factory()->direct()->create();
            $conversation->participants()->attach([
                $this->user->id => ['joined_at' => now(), 'is_admin' => true],
                $this->otherUser->id => ['joined_at' => now(), 'is_admin' => false],
            ]);

            $message = Message::factory()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $this->otherUser->id,
                'content' => 'Unread message',
            ]);

            actingAs($this->user);
            $response = $this->postJson("/conversations/{$conversation->id}/messages/read", [
                'message_ids' => [$message->id],
            ]);

            $response->assertSuccessful();
        });

        it('denies non-participant from marking messages as read', function () {
            $conversation = Conversation::factory()->direct()->create();
            $conversation->participants()->attach([
                $this->user->id => ['joined_at' => now(), 'is_admin' => true],
                $this->otherUser->id => ['joined_at' => now(), 'is_admin' => false],
            ]);

            $message = Message::factory()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $this->otherUser->id,
                'content' => 'Unread message',
            ]);

            actingAs($this->nonParticipant);
            $response = $this->postJson("/conversations/{$conversation->id}/messages/read", [
                'message_ids' => [$message->id],
            ]);

            $response->assertNotFound();
        });

        it('only allows message author to delete their own message', function () {
            $conversation = Conversation::factory()->direct()->create();
            $conversation->participants()->attach([
                $this->user->id => ['joined_at' => now(), 'is_admin' => true],
                $this->otherUser->id => ['joined_at' => now(), 'is_admin' => false],
            ]);

            $message = Message::factory()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $this->user->id,
                'content' => 'My message',
            ]);

            // Author can delete their own message
            actingAs($this->user);
            $response = $this->deleteJson("/messages/{$message->id}");
            $response->assertSuccessful();
        });

        it('denies other participants from deleting messages they did not author', function () {
            $conversation = Conversation::factory()->direct()->create();
            $conversation->participants()->attach([
                $this->user->id => ['joined_at' => now(), 'is_admin' => true],
                $this->otherUser->id => ['joined_at' => now(), 'is_admin' => false],
            ]);

            $message = Message::factory()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $this->user->id,
                'content' => 'My message',
            ]);

            // Other participant cannot delete
            actingAs($this->otherUser);
            $response = $this->deleteJson("/messages/{$message->id}");
            $response->assertNotFound();
        });
    });
});
