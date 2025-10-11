<?php

declare(strict_types=1);

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\actingAs;

describe('Chat Integration', function () {
    beforeEach(function () {
        Event::fake();
    });

    it('can create complete chat flow between two users', function () {
        $user1 = User::factory()->create(['name' => 'Alice']);
        $user2 = User::factory()->create(['name' => 'Bob']);

        actingAs($user1);

        // 1. User1 creates a conversation with User2
        $createResponse = $this->postJson('/conversations', [
            'type' => 'direct',
            'participants' => [$user2->id],
        ]);

        $createResponse->assertCreated();
        $conversationId = $createResponse->json('id');

        // 2. User1 sends first message
        $messageResponse1 = $this->postJson('/messages', [
            'conversation_id' => $conversationId,
            'content' => 'Hello Bob!',
            'type' => 'text',
        ]);

        $messageResponse1->assertCreated()
            ->assertJsonPath('content', 'Hello Bob!')
            ->assertJsonPath('user.name', 'Alice');

        // 3. Check conversation appears in User1's list
        $conversationsResponse1 = $this->getJson('/conversations');
        $conversationsResponse1->assertSuccessful()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $conversationId)
            ->assertJsonPath('0.title', 'Bob')
            ->assertJsonPath('0.unread_count', 0); // No unread for sender

        // 4. Switch to User2 and check their conversation list
        actingAs($user2);

        $conversationsResponse2 = $this->getJson('/conversations');
        $conversationsResponse2->assertSuccessful()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $conversationId)
            ->assertJsonPath('0.title', 'Alice')
            ->assertJsonPath('0.unread_count', 1); // 1 unread message

        // 5. User2 views the conversation
        $conversationResponse = $this->getJson("/conversations/{$conversationId}");
        $conversationResponse->assertSuccessful()
            ->assertJsonPath('id', $conversationId)
            ->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.content', 'Hello Bob!')
            ->assertJsonPath('messages.0.user.name', 'Alice');

        // 6. User2 marks messages as read
        $markReadResponse = $this->postJson("/conversations/{$conversationId}/messages/read");
        $markReadResponse->assertSuccessful();

        // 7. User2 sends a reply
        $messageResponse2 = $this->postJson('/messages', [
            'conversation_id' => $conversationId,
            'content' => 'Hi Alice! How are you?',
            'type' => 'text',
        ]);

        $messageResponse2->assertCreated()
            ->assertJsonPath('content', 'Hi Alice! How are you?')
            ->assertJsonPath('user.name', 'Bob');

        // 8. User2 sends an image message
        $imageResponse = $this->postJson('/messages', [
            'conversation_id' => $conversationId,
            'content' => 'Check this out!',
            'type' => 'image',
            'metadata' => [
                'filename' => 'photo.jpg',
                'size' => 1024000,
                'url' => 'https://example.com/photo.jpg',
            ],
        ]);

        $imageResponse->assertCreated()
            ->assertJsonPath('type', 'image');

        // 9. Switch back to User1 and check updated conversation
        actingAs($user1);

        $finalConversationResponse = $this->getJson("/conversations/{$conversationId}");
        $finalConversationResponse->assertSuccessful()
            ->assertJsonCount(3, 'messages') // 3 total messages
            ->assertJsonPath('messages.0.content', 'Hello Bob!')
            ->assertJsonPath('messages.1.content', 'Hi Alice! How are you?')
            ->assertJsonPath('messages.2.content', 'Check this out!')
            ->assertJsonPath('messages.2.type', 'image');

        // 10. Check User1's conversation list shows unread messages
        $finalConversationsResponse = $this->getJson('/conversations');
        $finalConversationsResponse->assertSuccessful()
            ->assertJsonPath('0.unread_count', 2) // 2 unread messages from User2
            ->assertJsonPath('0.last_message.content', 'Check this out!');

        // 11. Verify events were dispatched
        Event::assertDispatched(MessageSent::class, 3); // 3 messages sent

        // 12. Verify database state
        $conversation = Conversation::find($conversationId);
        expect($conversation)
            ->not->toBeNull()
            ->and($conversation->participants)->toHaveCount(2)
            ->and($conversation->messages)->toHaveCount(3)
            ->and($conversation->type)->toBe('direct');

        $unreadForUser1 = Message::query()->unread(user: $user1)->where('conversation_id', $conversationId)->count();
        expect($unreadForUser1)->toBe(2);

        $unreadForUser2 = Message::query()->unread(user: $user2)->where('conversation_id', $conversationId)->count();
        expect($unreadForUser2)->toBe(0); // User2 read the first message
    });

    it('can create group conversation with multiple participants', function () {
        $creator = User::factory()->create(['name' => 'Creator']);
        $user1 = User::factory()->create(['name' => 'User1']);
        $user2 = User::factory()->create(['name' => 'User2']);
        $user3 = User::factory()->create(['name' => 'User3']);

        actingAs($creator);

        // Create group conversation
        $createResponse = $this->postJson('/conversations', [
            'type' => 'group',
            'participants' => [$user1->id, $user2->id, $user3->id],
            'title' => 'Project Team',
        ]);

        $createResponse->assertCreated();
        $conversationId = $createResponse->json('id');

        // Creator sends message to group
        $messageResponse = $this->postJson('/messages', [
            'conversation_id' => $conversationId,
            'content' => 'Welcome to the project team!',
        ]);

        $messageResponse->assertCreated();

        // Each participant should see the conversation
        foreach ([$user1, $user2, $user3] as $user) {
            actingAs($user);

            $conversationsResponse = $this->getJson('/conversations');
            $conversationsResponse->assertSuccessful()
                ->assertJsonCount(1)
                ->assertJsonPath('0.id', $conversationId)
                ->assertJsonPath('0.title', 'Project Team')
                ->assertJsonPath('0.type', 'group')
                ->assertJsonPath('0.unread_count', 1);
        }

        // Verify group has all participants
        $conversation = Conversation::find($conversationId);
        expect($conversation->participants)->toHaveCount(4); // Creator + 3 participants
    });

    it('handles conversation deletion properly', function () {
        $creator = User::factory()->create();
        $participant = User::factory()->create();

        actingAs($creator);

        // Create conversation
        $createResponse = $this->postJson('/conversations', [
            'type' => 'direct',
            'participants' => [$participant->id],
        ]);

        $conversationId = $createResponse->json('id');

        // Add some messages
        $this->postJson('/messages', [
            'conversation_id' => $conversationId,
            'content' => 'Hello!',
        ]);

        // Creator deletes conversation
        $deleteResponse = $this->deleteJson("/conversations/{$conversationId}");
        $deleteResponse->assertSuccessful();

        // Conversation should be deleted
        $this->assertDatabaseMissing('conversations', ['id' => $conversationId]);

        // Messages should be deleted (cascade)
        $this->assertDatabaseMissing('messages', ['conversation_id' => $conversationId]);

        // Participant should not see conversation anymore
        actingAs($participant);
        $conversationsResponse = $this->getJson('/conversations');
        $conversationsResponse->assertSuccessful()->assertJsonCount(0);
    });

    it('handles message deletion properly', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $conversation = Conversation::factory()->create();
        $conversation->participants()->attach([
            $user1->id => ['joined_at' => now(), 'is_admin' => true],
            $user2->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        actingAs($user1);

        // User1 sends message
        $messageResponse = $this->postJson('/messages', [
            'conversation_id' => $conversation->id,
            'content' => 'This will be deleted',
        ]);

        $messageId = $messageResponse->json('id');

        // User1 deletes their own message
        $deleteResponse = $this->deleteJson("/messages/{$messageId}");
        $deleteResponse->assertSuccessful();

        // Message should be deleted
        $this->assertDatabaseMissing('messages', ['id' => $messageId]);

        // Conversation should still exist
        $this->assertDatabaseHas('conversations', ['id' => $conversation->id]);
    });

    it('prevents unauthorized access to conversations', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $outsider = User::factory()->create();

        // Create conversation between user1 and user2
        $conversation = Conversation::factory()->create();
        $conversation->participants()->attach([
            $user1->id => ['joined_at' => now(), 'is_admin' => true],
            $user2->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $user1->id,
        ]);

        actingAs($outsider);

        // Outsider cannot view conversation
        $viewResponse = $this->getJson("/conversations/{$conversation->id}");
        $viewResponse->assertNotFound();

        // Outsider cannot send message
        $messageResponse = $this->postJson('/messages', [
            'conversation_id' => $conversation->id,
            'content' => 'Unauthorized message',
        ]);
        $messageResponse->assertNotFound();

        // Outsider cannot mark messages as read
        $readResponse = $this->postJson("/conversations/{$conversation->id}/messages/read");
        $readResponse->assertNotFound();

        // Outsider cannot delete conversation
        $deleteResponse = $this->deleteJson("/conversations/{$conversation->id}");
        $deleteResponse->assertNotFound();

        // Outsider cannot delete message
        $deleteMessageResponse = $this->deleteJson("/messages/{$message->id}");
        $deleteMessageResponse->assertNotFound();
    });

    it('handles existing direct conversation scenario', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        actingAs($user1);

        // Create first conversation
        $firstResponse = $this->postJson('/conversations', [
            'type' => 'direct',
            'participants' => [$user2->id],
        ]);

        $firstResponse->assertCreated();
        $conversationId = $firstResponse->json('id');

        // Try to create same conversation again
        $secondResponse = $this->postJson('/conversations', [
            'type' => 'direct',
            'participants' => [$user2->id],
        ]);

        $secondResponse->assertSuccessful()
            ->assertJsonPath('id', $conversationId)
            ->assertJsonPath('message', 'Conversation already exists');

        // Should still only have one conversation in database
        $conversationCount = Conversation::where('type', 'direct')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user1->id))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user2->id))
            ->count();

        expect($conversationCount)->toBe(1);
    });

    it('updates last_message_at correctly throughout conversation', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $conversation = Conversation::factory()->create(['last_message_at' => null]);
        $conversation->participants()->attach([
            $user1->id => ['joined_at' => now(), 'is_admin' => true],
            $user2->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        expect($conversation->fresh()->last_message_at)->toBeNull();

        actingAs($user1);

        // Send first message
        $this->postJson('/messages', [
            'conversation_id' => $conversation->id,
            'content' => 'First message',
        ]);

        $conversation->refresh();
        $firstMessageTime = $conversation->last_message_at;
        expect($firstMessageTime)->not->toBeNull();

        // Wait a moment and send second message
        sleep(1);

        actingAs($user2);
        $this->postJson('/messages', [
            'conversation_id' => $conversation->id,
            'content' => 'Second message',
        ]);

        $conversation->refresh();
        $secondMessageTime = $conversation->last_message_at;

        expect($secondMessageTime)
            ->not->toBeNull()
            ->toBeGreaterThan($firstMessageTime);
    });
});
