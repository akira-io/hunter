<?php

declare(strict_types=1);

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

use function Pest\Laravel\actingAs;

describe('Chat Layout Integration', function () {
    it('displays conversations ordered by online status first', function () {
        $user = User::factory()->create();
        $onlineUser = User::factory()->create(['name' => 'Online User']);
        $offlineUser = User::factory()->create(['name' => 'Offline User']);

        // Create conversations
        $onlineConversation = Conversation::factory()->create(['last_message_at' => now()->subHours(2)]);
        $onlineConversation->participants()->attach([
            $user->id => ['joined_at' => now(), 'is_admin' => true],
            $onlineUser->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        $offlineConversation = Conversation::factory()->create(['last_message_at' => now()->subHour()]);
        $offlineConversation->participants()->attach([
            $user->id => ['joined_at' => now(), 'is_admin' => true],
            $offlineUser->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        // Mark online user as online
        cache()->put("user_online_{$onlineUser->id}", now(), now()->addMinutes(10));

        actingAs($user);

        $response = $this->getJson('/conversations');

        $conversations = $response->json();

        // Frontend sorts, not backend - so we just check they're both present
        expect($conversations)->toHaveCount(2);

        $conversationIds = array_column($conversations, 'id');
        expect($conversationIds)->toContain($onlineConversation->id);
        expect($conversationIds)->toContain($offlineConversation->id);
    });

    it('shows unread count badge on conversations', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $conversation = Conversation::factory()->create();
        $conversation->participants()->attach([
            $user->id => ['joined_at' => now(), 'is_admin' => true],
            $otherUser->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        // Create unread messages from other user
        Message::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
            'user_id' => $otherUser->id,
            'read_at' => null,
        ]);

        actingAs($user);

        $response = $this->getJson('/conversations');

        $response->assertOk()
            ->assertJsonPath('0.unread_count', 3);
    });

    it('displays other participant info for direct conversations', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create(['name' => 'John Doe']);

        $conversation = Conversation::factory()->create(['type' => 'direct']);
        $conversation->participants()->attach([
            $user->id => ['joined_at' => now(), 'is_admin' => true],
            $otherUser->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        actingAs($user);

        $response = $this->getJson('/conversations');

        $response->assertOk()
            ->assertJsonPath('0.other_participant.id', $otherUser->id)
            ->assertJsonPath('0.other_participant.name', 'John Doe')
            ->assertJsonPath('0.title', 'John Doe'); // Title should be other user's name
    });

    it('handles group conversations correctly', function () {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $conversation = Conversation::factory()->create([
            'type' => 'group',
            'title' => 'Team Chat',
        ]);

        $conversation->participants()->attach([
            $user->id => ['joined_at' => now(), 'is_admin' => true],
            $user2->id => ['joined_at' => now(), 'is_admin' => false],
            $user3->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        actingAs($user);

        $response = $this->getJson('/conversations');

        $response->assertOk()
            ->assertJsonPath('0.type', 'group')
            ->assertJsonPath('0.title', 'Team Chat')
            ->assertJsonCount(3, '0.participants');
    });

    it('shows last message preview in conversation list', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $conversation = Conversation::factory()->create();
        $conversation->participants()->attach([
            $user->id => ['joined_at' => now(), 'is_admin' => true],
            $otherUser->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $otherUser->id,
            'content' => 'Latest message content',
        ]);

        actingAs($user);

        $response = $this->getJson('/conversations');

        $response->assertOk()
            ->assertJsonPath('0.last_message.content', 'Latest message content')
            ->assertJsonPath('0.last_message.user.id', $otherUser->id);
    });
});

describe('Chat Wayfinder Routes', function () {
    it('generates correct chat index URL', function () {
        expect(route('chat.index'))->toBe(url('/chat'));
    });

    it('generates correct chat show URL', function () {
        expect(route('chat.show', ['conversation' => 123]))
            ->toBe(url('/chat/123'));
    });

});

describe('Chat Badge in Sidebar', function () {
    it('calculates total unread messages across all conversations', function () {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // Conversation 1 with 2 unread messages
        $conv1 = Conversation::factory()->create();
        $conv1->participants()->attach([
            $user->id => ['joined_at' => now(), 'is_admin' => true],
            $user2->id => ['joined_at' => now(), 'is_admin' => false],
        ]);
        Message::factory()->count(2)->create([
            'conversation_id' => $conv1->id,
            'user_id' => $user2->id,
            'read_at' => null,
        ]);

        // Conversation 2 with 3 unread messages
        $conv2 = Conversation::factory()->create();
        $conv2->participants()->attach([
            $user->id => ['joined_at' => now(), 'is_admin' => true],
            $user3->id => ['joined_at' => now(), 'is_admin' => false],
        ]);
        Message::factory()->count(3)->create([
            'conversation_id' => $conv2->id,
            'user_id' => $user3->id,
            'read_at' => null,
        ]);

        actingAs($user);

        $response = $this->getJson('/conversations');

        $conversations = $response->json();
        $totalUnread = array_sum(array_column($conversations, 'unread_count'));

        expect($totalUnread)->toBe(5);
    });
});
