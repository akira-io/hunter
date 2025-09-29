<?php

declare(strict_types=1);

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

use function Pest\Laravel\actingAs;

describe('ConversationController', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        actingAs($this->user);
    });

    describe('index', function () {
        it('returns user conversations with correct structure', function () {
            $otherUser = User::factory()->create();
            $conversation = Conversation::factory()->direct()->create();

            $conversation->participants()->attach([
                $this->user->id => ['joined_at' => now(), 'is_admin' => true],
                $otherUser->id => ['joined_at' => now(), 'is_admin' => false],
            ]);

            $message = Message::factory()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $otherUser->id,
                'content' => 'Hello!',
            ]);

            $conversation->update(['last_message_at' => $message->created_at]);

            $response = $this->getJson('/conversations');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'title',
                        'type',
                        'participants' => [
                            '*' => [
                                'id',
                                'name',
                                'avatar_url',
                            ],
                        ],
                        'avatar_url',
                        'last_message' => [
                            'id',
                            'content',
                            'type',
                            'created_at',
                            'user' => [
                                'id',
                                'name',
                                'avatar_url',
                            ],
                        ],
                        'last_message_at',
                        'unread_count',
                    ],
                ])
                ->assertJsonPath('0.id', $conversation->id)
                ->assertJsonPath('0.type', 'direct')
                ->assertJsonPath('0.last_message.content', 'Hello!');
        });

        it('returns conversations ordered by last_message_at', function () {
            $otherUser = User::factory()->create();

            $oldConversation = Conversation::factory()->create([
                'last_message_at' => now()->subHours(2),
            ]);
            $oldConversation->participants()->attach([
                $this->user->id => ['joined_at' => now(), 'is_admin' => true],
                $otherUser->id => ['joined_at' => now(), 'is_admin' => false],
            ]);

            $newConversation = Conversation::factory()->create([
                'last_message_at' => now(),
            ]);
            $newConversation->participants()->attach([
                $this->user->id => ['joined_at' => now(), 'is_admin' => true],
                $otherUser->id => ['joined_at' => now(), 'is_admin' => false],
            ]);

            $response = $this->getJson('/conversations');

            $response->assertSuccessful()
                ->assertJsonPath('0.id', $newConversation->id)
                ->assertJsonPath('1.id', $oldConversation->id);
        });

        it('only returns conversations where user is participant', function () {
            $otherUser1 = User::factory()->create();
            $otherUser2 = User::factory()->create();

            // Conversation user is part of
            $userConversation = Conversation::factory()->create();
            $userConversation->participants()->attach([
                $this->user->id => ['joined_at' => now(), 'is_admin' => true],
                $otherUser1->id => ['joined_at' => now(), 'is_admin' => false],
            ]);

            // Conversation user is NOT part of
            $otherConversation = Conversation::factory()->create();
            $otherConversation->participants()->attach([
                $otherUser1->id => ['joined_at' => now(), 'is_admin' => true],
                $otherUser2->id => ['joined_at' => now(), 'is_admin' => false],
            ]);

            $response = $this->getJson('/conversations');

            $response->assertSuccessful()
                ->assertJsonCount(1)
                ->assertJsonPath('0.id', $userConversation->id);
        });

        it('calculates unread count correctly', function () {
            $otherUser = User::factory()->create();
            $conversation = Conversation::factory()->create();

            $conversation->participants()->attach([
                $this->user->id => ['joined_at' => now(), 'is_admin' => true],
                $otherUser->id => ['joined_at' => now(), 'is_admin' => false],
            ]);

            // Create unread messages from other user
            Message::factory()->count(3)->unread()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $otherUser->id,
            ]);

            // Create read message from other user
            Message::factory()->read()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $otherUser->id,
            ]);

            // Create message from current user (should not count)
            Message::factory()->unread()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $this->user->id,
            ]);

            $response = $this->getJson('/conversations');

            $response->assertSuccessful()
                ->assertJsonPath('0.unread_count', 3);
        });

    });

    describe('store', function () {
        it('creates direct conversation successfully', function () {
            $otherUser = User::factory()->create();

            $response = $this->postJson('/conversations', [
                'type' => 'direct',
                'participants' => [$otherUser->id],
            ]);

            $response->assertCreated()
                ->assertJsonStructure([
                    'id',
                    'message',
                ]);

            $this->assertDatabaseHas('conversations', [
                'type' => 'direct',
                'created_by' => $this->user->id,
            ]);

            $conversation = Conversation::find($response->json('id'));
            expect($conversation->participants)
                ->toHaveCount(2)
                ->and($conversation->participants->pluck('id')->toArray())
                ->toContain($this->user->id, $otherUser->id);
        });

        it('creates group conversation successfully', function () {
            $otherUsers = User::factory()->count(2)->create();

            $response = $this->postJson('/conversations', [
                'type' => 'group',
                'participants' => $otherUsers->pluck('id')->toArray(),
                'title' => 'Test Group',
            ]);

            $response->assertCreated()
                ->assertJsonStructure([
                    'id',
                    'message',
                ]);

            $this->assertDatabaseHas('conversations', [
                'type' => 'group',
                'title' => 'Test Group',
                'created_by' => $this->user->id,
            ]);

            $conversation = Conversation::find($response->json('id'));
            expect($conversation->participants)->toHaveCount(3); // 2 other users + current user
        });

        it('returns existing direct conversation if it already exists', function () {
            $otherUser = User::factory()->create();

            // Create existing conversation
            $existingConversation = Conversation::factory()->direct()->create();
            $existingConversation->participants()->attach([
                $this->user->id => ['joined_at' => now(), 'is_admin' => true],
                $otherUser->id => ['joined_at' => now(), 'is_admin' => false],
            ]);

            $response = $this->postJson('/conversations', [
                'type' => 'direct',
                'participants' => [$otherUser->id],
            ]);

            $response->assertSuccessful()
                ->assertJsonPath('id', $existingConversation->id)
                ->assertJsonPath('message', 'Conversation already exists');
        });

        it('validates direct conversation has exactly one other participant', function () {
            $otherUsers = User::factory()->count(2)->create();

            $response = $this->postJson('/conversations', [
                'type' => 'direct',
                'participants' => $otherUsers->pluck('id')->toArray(),
            ]);

            $response->assertUnprocessable()
                ->assertJsonPath('error', 'Direct conversations must have exactly one other participant');
        });

        it('validates required fields', function () {
            $response = $this->postJson('/conversations', []);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['type', 'participants']);
        });

        it('validates participants exist', function () {
            $response = $this->postJson('/conversations', [
                'type' => 'direct',
                'participants' => [99999], // Non-existent user
            ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['participants.0']);
        });

        it('filters out current user from participants', function () {
            $otherUser = User::factory()->create();

            $response = $this->postJson('/conversations', [
                'type' => 'direct',
                'participants' => [$this->user->id, $otherUser->id], // Include current user
            ]);

            $response->assertCreated();

            $conversation = Conversation::find($response->json('id'));
            expect($conversation->participants)->toHaveCount(2); // Should still be 2, not 3
        });

        it('returns 422 when participant user does not exist after validation', function () {
            // The Laravel validation will catch this first with the exists rule
            $response = $this->postJson('/conversations', [
                'type' => 'direct',
                'participants' => [999999], // Non-existent user ID
            ]);

            $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['participants.0']]);
        });

        // Note: Database transaction failure tests are complex to implement without affecting other tests
        // The CreateConversationAction handles database transactions properly and any real database
        // errors would result in a 500 response as intended by the controller error handling
    });

    describe('show', function () {
        it('returns conversation with messages', function () {
            $otherUser = User::factory()->create();
            $conversation = Conversation::factory()->create();

            $conversation->participants()->attach([
                $this->user->id => ['joined_at' => now(), 'is_admin' => true],
                $otherUser->id => ['joined_at' => now(), 'is_admin' => false],
            ]);

            $messages = Message::factory()->count(3)->create([
                'conversation_id' => $conversation->id,
                'user_id' => $otherUser->id,
            ]);

            $response = $this->getJson("/conversations/{$conversation->id}");

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'id',
                    'title',
                    'type',
                    'participants' => [
                        '*' => [
                            'id',
                            'name',
                            'avatar_url',
                        ],
                    ],
                    'messages' => [
                        '*' => [
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
                        ],
                    ],
                ])
                ->assertJsonPath('id', $conversation->id)
                ->assertJsonCount(3, 'messages');
        });

        it('orders messages by created_at ascending', function () {
            $otherUser = User::factory()->create();
            $conversation = Conversation::factory()->create();

            $conversation->participants()->attach([
                $this->user->id => ['joined_at' => now(), 'is_admin' => true],
                $otherUser->id => ['joined_at' => now(), 'is_admin' => false],
            ]);

            $oldMessage = Message::factory()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $otherUser->id,
                'content' => 'Old message',
                'created_at' => now()->subHours(2),
            ]);

            $newMessage = Message::factory()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $otherUser->id,
                'content' => 'New message',
                'created_at' => now(),
            ]);

            $response = $this->getJson("/conversations/{$conversation->id}");

            $response->assertSuccessful()
                ->assertJsonPath('messages.0.content', 'Old message')
                ->assertJsonPath('messages.1.content', 'New message');
        });

        it('returns 404 for non-existent conversation', function () {
            $response = $this->getJson('/conversations/99999');

            $response->assertNotFound()
                ->assertJsonPath('error', 'Conversation not found');
        });

        it('returns 404 when user is not participant', function () {
            $otherUser1 = User::factory()->create();
            $otherUser2 = User::factory()->create();

            $conversation = Conversation::factory()->create();
            $conversation->participants()->attach([
                $otherUser1->id => ['joined_at' => now(), 'is_admin' => true],
                $otherUser2->id => ['joined_at' => now(), 'is_admin' => false],
            ]);

            $response = $this->getJson("/conversations/{$conversation->id}");

            $response->assertNotFound()
                ->assertJsonPath('error', 'Conversation not found');
        });

    });

    describe('destroy', function () {
        it('deletes conversation when user is creator', function () {
            $otherUser = User::factory()->create();
            $conversation = Conversation::factory()->create(['created_by' => $this->user->id]);

            $conversation->participants()->attach([
                $this->user->id => ['joined_at' => now(), 'is_admin' => true],
                $otherUser->id => ['joined_at' => now(), 'is_admin' => false],
            ]);

            $response = $this->deleteJson("/conversations/{$conversation->id}");

            $response->assertSuccessful()
                ->assertJsonPath('message', 'Conversation deleted successfully');

            $this->assertModelMissing($conversation);
        });

        it('returns 403 when user is not creator', function () {
            $creator = User::factory()->create();
            $otherUser = User::factory()->create();
            $conversation = Conversation::factory()->create(['created_by' => $creator->id]);

            $conversation->participants()->attach([
                $this->user->id => ['joined_at' => now(), 'is_admin' => false],
                $otherUser->id => ['joined_at' => now(), 'is_admin' => false],
            ]);

            $response = $this->deleteJson("/conversations/{$conversation->id}");

            $response->assertForbidden()
                ->assertJsonPath('error', 'Unauthorized');

            $this->assertModelExists($conversation);
        });

        it('returns 404 for non-existent conversation', function () {
            $response = $this->deleteJson('/conversations/99999');

            $response->assertNotFound()
                ->assertJsonPath('error', 'Conversation not found');
        });

        it('returns 404 when user is not participant', function () {
            $creator = User::factory()->create();
            $otherUser = User::factory()->create();
            $conversation = Conversation::factory()->create(['created_by' => $creator->id]);

            $conversation->participants()->attach([
                $creator->id => ['joined_at' => now(), 'is_admin' => true],
                $otherUser->id => ['joined_at' => now(), 'is_admin' => false],
            ]);

            $response = $this->deleteJson("/conversations/{$conversation->id}");

            $response->assertNotFound()
                ->assertJsonPath('error', 'Conversation not found');
        });

    });
});

describe('ConversationController - Unauthorized Access', function () {
    it('returns 401 for index when user is not authenticated', function () {
        $response = $this->getJson('/conversations');
        $response->assertUnauthorized();
    });

    it('returns 401 for store when user is not authenticated', function () {
        $response = $this->postJson('/conversations', [
            'type' => 'direct',
            'participants' => [1],
        ]);
        $response->assertUnauthorized();
    });

    it('returns 401 for show when user is not authenticated', function () {
        $response = $this->getJson('/conversations/1');
        $response->assertUnauthorized();
    });

    it('returns 401 for destroy when user is not authenticated', function () {
        $response = $this->deleteJson('/conversations/1');
        $response->assertUnauthorized();
    });

    // Note: Tests for defensive Auth::user() checks (when it returns non-User instances)
    // are complex to implement in integration tests due to middleware interference.
    // These checks are defensive programming practices that rarely occur in real scenarios.
});
