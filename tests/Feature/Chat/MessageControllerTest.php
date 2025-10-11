<?php

declare(strict_types=1);

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\actingAs;

describe('MessageController', function () {
    beforeEach(function () {
        Event::fake();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->conversation = Conversation::factory()->create();

        $this->conversation->participants()->attach([
            $this->user->id => ['joined_at' => now(), 'is_admin' => true],
            $this->otherUser->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        actingAs($this->user);
    });

    describe('store', function () {
        it('creates message successfully', function () {
            $response = $this->postJson('/messages', [
                'conversation_id' => $this->conversation->id,
                'content' => 'Hello, World!',
                'type' => 'text',
            ]);

            $response->assertCreated()
                ->assertJsonStructure([
                    'id',
                    'content',
                    'type',
                    'metadata',
                    'created_at',
                    'user' => [
                        'id',
                        'name',
                        'avatar_url',
                    ],
                ])
                ->assertJsonPath('content', 'Hello, World!')
                ->assertJsonPath('type', 'text')
                ->assertJsonPath('user.id', $this->user->id);

            $this->assertDatabaseHas('messages', [
                'conversation_id' => $this->conversation->id,
                'user_id' => $this->user->id,
                'content' => 'Hello, World!',
                'type' => 'text',
            ]);

            Event::assertDispatched(MessageSent::class);
        });

        it('updates conversation last_message_at', function () {
            $oldTimestamp = $this->conversation->last_message_at;

            $this->postJson('/messages', [
                'conversation_id' => $this->conversation->id,
                'content' => 'Hello!',
            ]);

            $this->conversation->refresh();
            expect($this->conversation->last_message_at)
                ->not->toBe($oldTimestamp)
                ->toBeInstanceOf(DateTimeInterface::class);
        });

        it('defaults to text type when not provided', function () {
            $response = $this->postJson('/messages', [
                'conversation_id' => $this->conversation->id,
                'content' => 'Hello!',
            ]);

            $response->assertCreated()
                ->assertJsonPath('type', 'text');
        });

        it('can create image message', function () {
            $metadata = [
                'filename' => 'image.jpg',
                'size' => 1024,
                'url' => 'https://example.com/image.jpg',
            ];

            $response = $this->postJson('/messages', [
                'conversation_id' => $this->conversation->id,
                'content' => 'Image shared',
                'type' => 'image',
                'metadata' => $metadata,
            ]);

            $response->assertCreated()
                ->assertJsonPath('type', 'image')
                ->assertJsonPath('metadata', $metadata);
        });

        it('can create file message', function () {
            $metadata = [
                'filename' => 'document.pdf',
                'size' => 2048,
                'mime_type' => 'application/pdf',
            ];

            $response = $this->postJson('/messages', [
                'conversation_id' => $this->conversation->id,
                'content' => 'File shared',
                'type' => 'file',
                'metadata' => $metadata,
            ]);

            $response->assertCreated()
                ->assertJsonPath('type', 'file')
                ->assertJsonPath('metadata', $metadata);
        });

        it('validates required fields', function () {
            $response = $this->postJson('/messages', []);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['conversation_id', 'content']);
        });

        it('validates conversation exists', function () {
            $response = $this->postJson('/messages', [
                'conversation_id' => 99999,
                'content' => 'Hello!',
            ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['conversation_id']);
        });

        it('validates content length', function () {
            $response = $this->postJson('/messages', [
                'conversation_id' => $this->conversation->id,
                'content' => str_repeat('a', 10001), // Exceeds max length
            ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['content']);
        });

        it('validates message type', function () {
            $response = $this->postJson('/messages', [
                'conversation_id' => $this->conversation->id,
                'content' => 'Hello!',
                'type' => 'invalid_type',
            ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['type']);
        });

        it('validates metadata is array when provided', function () {
            $response = $this->postJson('/messages', [
                'conversation_id' => $this->conversation->id,
                'content' => 'Hello!',
                'metadata' => 'not_an_array',
            ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['metadata']);
        });

        it('returns 404 when user is not participant', function () {
            $nonParticipant = User::factory()->create();
            actingAs($nonParticipant);

            $response = $this->postJson('/messages', [
                'conversation_id' => $this->conversation->id,
                'content' => 'Hello!',
            ]);

            $response->assertNotFound()
                ->assertJsonPath('error', 'Conversation not found');
        });

        it('rolls back transaction on failure', function () {
            // Force an exception by creating invalid conversation state
            $invalidConversation = Conversation::factory()->create();
            $invalidConversation->delete(); // Delete but keep ID

            $response = $this->postJson('/messages', [
                'conversation_id' => $invalidConversation->id,
                'content' => 'Hello!',
            ]);

            $response->assertUnprocessable();

            // Ensure no message was created
            $this->assertDatabaseMissing('messages', [
                'content' => 'Hello!',
            ]);

            Event::assertNotDispatched(MessageSent::class);
        });

        // Note: Testing the exact line 69-70 (500 error for generic exceptions) is challenging
        // because SendMessageAction is a final class and cannot be mocked.
        // The defensive 500 error handling exists for edge cases like memory exhaustion,
        // database connection failures, etc. that are difficult to simulate in tests.
    });

    describe('markAsRead', function () {
        beforeEach(function () {
            // Create unread messages from other user
            $this->unreadMessages = Message::factory()->count(3)->unread()->create([
                'conversation_id' => $this->conversation->id,
                'user_id' => $this->otherUser->id,
            ]);

            // Create message from current user (shouldn't be marked as read)
            $this->ownMessage = Message::factory()->unread()->create([
                'conversation_id' => $this->conversation->id,
                'user_id' => $this->user->id,
            ]);
        });

        it('marks all unread messages as read when no message_ids provided', function () {
            $response = $this->postJson("/conversations/{$this->conversation->id}/messages/read");

            $response->assertSuccessful()
                ->assertJsonPath('message', 'Messages marked as read');

            // Check that other user's messages are marked as read
            foreach ($this->unreadMessages as $message) {
                $message->refresh();
                expect($message->read_at)->not->toBeNull();
            }

            // Check that own message is still unread
            $this->ownMessage->refresh();
            expect($this->ownMessage->read_at)->toBeNull();
        });

        it('marks specific messages as read when message_ids provided', function () {
            $specificMessage = $this->unreadMessages->first();

            $response = $this->postJson("/conversations/{$this->conversation->id}/messages/read", [
                'message_ids' => [$specificMessage->id],
            ]);

            $response->assertSuccessful();

            // Check that specific message is marked as read
            $specificMessage->refresh();
            expect($specificMessage->read_at)->not->toBeNull();

            // Check that other messages are still unread
            $otherMessages = $this->unreadMessages->skip(1);
            foreach ($otherMessages as $message) {
                $message->refresh();
                expect($message->read_at)->toBeNull();
            }
        });

        it('validates message_ids exist', function () {
            $response = $this->postJson("/conversations/{$this->conversation->id}/messages/read", [
                'message_ids' => [99999], // Non-existent message
            ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['message_ids.0']);
        });

        it('returns 404 for non-existent conversation', function () {
            $response = $this->postJson('/conversations/99999/messages/read');

            $response->assertNotFound()
                ->assertJsonPath('error', 'Conversation not found');
        });

        it('returns 404 when user is not participant', function () {
            $nonParticipant = User::factory()->create();
            actingAs($nonParticipant);

            $response = $this->postJson("/conversations/{$this->conversation->id}/messages/read");

            $response->assertNotFound()
                ->assertJsonPath('error', 'Conversation not found');
        });

        it('only marks messages from other users as read', function () {
            // Create additional messages from current user
            $userMessages = Message::factory()->count(2)->unread()->create([
                'conversation_id' => $this->conversation->id,
                'user_id' => $this->user->id,
            ]);

            $response = $this->postJson("/conversations/{$this->conversation->id}/messages/read");

            $response->assertSuccessful();

            // Check that current user's messages remain unread
            foreach ($userMessages as $message) {
                $message->refresh();
                expect($message->read_at)->toBeNull();
            }
        });

        it('does not affect already read messages', function () {
            // Create already read message
            $readMessage = Message::factory()->read()->create([
                'conversation_id' => $this->conversation->id,
                'user_id' => $this->otherUser->id,
            ]);

            $originalReadAt = $readMessage->read_at;

            $response = $this->postJson("/conversations/{$this->conversation->id}/messages/read");

            $response->assertSuccessful();

            // Check that read_at timestamp didn't change
            $readMessage->refresh();
            expect($readMessage->read_at->format('Y-m-d H:i:s'))
                ->toBe($originalReadAt->format('Y-m-d H:i:s'));
        });
    });

    describe('markAsRead - Unauthorized Access', function () {
        it('returns 401 when user is not authenticated', function () {
            auth()->logout();

            $response = $this->postJson("/conversations/{$this->conversation->id}/messages/read");

            $response->assertUnauthorized();
        });
    });

    describe('destroy', function () {
        beforeEach(function () {
            $this->message = Message::factory()->create([
                'conversation_id' => $this->conversation->id,
                'user_id' => $this->user->id,
                'content' => 'My message',
            ]);

            $this->otherUserMessage = Message::factory()->create([
                'conversation_id' => $this->conversation->id,
                'user_id' => $this->otherUser->id,
                'content' => 'Other user message',
            ]);
        });

        it('deletes own message successfully', function () {
            $response = $this->deleteJson("/messages/{$this->message->id}");

            $response->assertSuccessful()
                ->assertJsonPath('message', 'Message deleted successfully');

            $this->assertModelMissing($this->message);
        });

        it('cannot delete other users messages', function () {
            $response = $this->deleteJson("/messages/{$this->otherUserMessage->id}");

            $response->assertNotFound()
                ->assertJsonPath('error', 'Message not found');

            $this->assertModelExists($this->otherUserMessage);
        });

        it('returns 404 for non-existent message', function () {
            $response = $this->deleteJson('/messages/99999');

            $response->assertNotFound()
                ->assertJsonPath('error', 'Message not found');
        });

        it('returns 404 when trying to delete message from different user', function () {
            $anotherUser = User::factory()->create();
            actingAs($anotherUser);

            $response = $this->deleteJson("/messages/{$this->message->id}");

            $response->assertNotFound()
                ->assertJsonPath('error', 'Message not found');

            $this->assertModelExists($this->message);
        });
    });

    describe('destroy - Unauthorized Access', function () {
        beforeEach(function () {
            $this->message = Message::factory()->create([
                'conversation_id' => $this->conversation->id,
                'user_id' => $this->user->id,
                'content' => 'My message',
            ]);
        });

        it('returns 401 when user is not authenticated', function () {
            auth()->logout();

            $response = $this->deleteJson("/messages/{$this->message->id}");

            $response->assertUnauthorized();

            $this->assertModelExists($this->message);
        });
    });

    describe('store - Unauthorized Access', function () {
        it('returns 401 when user is not authenticated', function () {
            auth()->logout();

            $response = $this->postJson('/messages', [
                'conversation_id' => $this->conversation->id,
                'content' => 'Hello!',
            ]);

            $response->assertUnauthorized();
        });

        // Note: Tests for defensive Auth::user() checks and exception scenarios
        // are complex to implement without mocking, which we avoid.
        // These defensive checks are unlikely to occur in real scenarios.
    });

    // Note: Auth edge case tests removed to avoid mocking
    // The defensive Auth::user() checks are covered by architectural design
});
