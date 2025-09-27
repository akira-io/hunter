<?php

declare(strict_types=1);

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

describe('Conversation Model', function () {
    it('can be created with factory', function () {
        $conversation = Conversation::factory()->create();

        expect($conversation)
            ->toBeInstanceOf(Conversation::class)
            ->and($conversation->type)
            ->toBeIn(['direct', 'group'])
            ->and($conversation->created_by)
            ->toBeInt();
    });

    it('has a creator relationship', function () {
        $user = User::factory()->create();
        $conversation = Conversation::factory()->create(['created_by' => $user->id]);

        expect($conversation->creator->id)->toBe($user->id)
            ->and($conversation->creator->name)->toBe($user->name);
    });

    it('has participants relationship', function () {
        $conversation = Conversation::factory()->withParticipants(3)->create();

        expect($conversation->participants)
            ->toHaveCount(3)
            ->and($conversation->participants->first())
            ->toBeInstanceOf(User::class);
    });

    it('has messages relationship', function () {
        $conversation = Conversation::factory()->withMessages(5)->create();

        expect($conversation->messages)
            ->toHaveCount(5)
            ->and($conversation->messages->first())
            ->toBeInstanceOf(Message::class);
    });

    it('can scope conversations for a specific user', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // Create conversation with user1 and user2
        $conversation1 = Conversation::factory()->create();
        $conversation1->participants()->attach([
            $user1->id => ['joined_at' => now(), 'is_admin' => true],
            $user2->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        // Create conversation with only user3
        $conversation2 = Conversation::factory()->create();
        $conversation2->participants()->attach([
            $user3->id => ['joined_at' => now(), 'is_admin' => true],
        ]);

        $user1Conversations = Conversation::query()->forUser(user: $user1)->get();
        $user3Conversations = Conversation::query()->forUser(user: $user3)->get();

        expect($user1Conversations)
            ->toHaveCount(1)
            ->and($user1Conversations->first()->id)
            ->toBe($conversation1->id)
            ->and($user3Conversations)
            ->toHaveCount(1)
            ->and($user3Conversations->first()->id)
            ->toBe($conversation2->id);
    });

    it('can find direct conversation between two users', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // Create direct conversation between user1 and user2
        $directConversation = Conversation::factory()->direct()->create();
        $directConversation->participants()->attach([
            $user1->id => ['joined_at' => now(), 'is_admin' => true],
            $user2->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        // Create group conversation with user1, user2, and user3
        $groupConversation = Conversation::factory()->group()->create();
        $groupConversation->participants()->attach([
            $user1->id => ['joined_at' => now(), 'is_admin' => true],
            $user2->id => ['joined_at' => now(), 'is_admin' => false],
            $user3->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        $foundConversation = Conversation::query()->directConversation(user1: $user1, user2: $user2)->first();

        expect($foundConversation)
            ->not->toBeNull()
            ->and($foundConversation->id)
            ->toBe($directConversation->id)
            ->and($foundConversation->type)
            ->toBe('direct');
    });

    it('does not find direct conversation when users are not in same conversation', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // Create conversation between user1 and user3 (not user2)
        $conversation = Conversation::factory()->direct()->create();
        $conversation->participants()->attach([
            $user1->id => ['joined_at' => now(), 'is_admin' => true],
            $user3->id => ['joined_at' => now(), 'is_admin' => false],
        ]);

        $foundConversation = Conversation::query()->directConversation(user1: $user1, user2: $user2)->first();

        expect($foundConversation)->toBeNull();
    });

    it('can create direct conversation', function () {
        $conversation = Conversation::factory()->direct()->create();

        expect($conversation->type)
            ->toBe('direct')
            ->and($conversation->title)
            ->toBeNull();
    });

    it('can create group conversation', function () {
        $conversation = Conversation::factory()->group()->create();

        expect($conversation->type)
            ->toBe('group')
            ->and($conversation->title)
            ->not->toBeNull();
    });

    it('casts last_message_at to datetime', function () {
        $conversation = Conversation::factory()->create([
            'last_message_at' => '2024-01-01 12:00:00',
        ]);

        expect($conversation->last_message_at)
            ->toBeInstanceOf(DateTimeInterface::class);
    });

    it('updates last_message_at when messages are added', function () {
        $conversation = Conversation::factory()->create(['last_message_at' => null]);

        expect($conversation->last_message_at)->toBeNull();

        // Add a message
        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'created_at' => now(),
        ]);

        $conversation->update(['last_message_at' => $message->created_at]);
        $conversation->refresh();

        expect($conversation->last_message_at)
            ->not->toBeNull()
            ->toBeInstanceOf(DateTimeInterface::class)
            ->and($conversation->last_message_at->format('Y-m-d H:i:s'))
            ->toBe($message->created_at->format('Y-m-d H:i:s'));
    });

    it('has latest message relationship', function () {
        $conversation = Conversation::factory()->create();

        // Create messages with different timestamps
        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'created_at' => now()->subMinutes(10),
            'content' => 'Old message',
        ]);

        $latestMessage = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'created_at' => now(),
            'content' => 'Latest message',
        ]);

        $retrievedLatestMessage = $conversation->latestMessage()->first();

        expect($retrievedLatestMessage)
            ->not->toBeNull()
            ->and($retrievedLatestMessage->id)
            ->toBe($latestMessage->id)
            ->and($retrievedLatestMessage->content)
            ->toBe('Latest message');
    });
});
