<?php

declare(strict_types=1);

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

describe('Message Model', function () {
    it('can be created with factory', function () {
        $message = Message::factory()->create();

        expect($message)
            ->toBeInstanceOf(Message::class)
            ->and($message->content)
            ->toBeString()
            ->and($message->type)
            ->toBe('text')
            ->and($message->conversation_id)
            ->toBeInt()
            ->and($message->user_id)
            ->toBeInt();
    });

    it('has a conversation relationship', function () {
        $conversation = Conversation::factory()->create();
        $message = Message::factory()->create(['conversation_id' => $conversation->id]);

        expect($message->conversation->id)->toBe($conversation->id);
    });

    it('has a user relationship', function () {
        $user = User::factory()->create();
        $message = Message::factory()->create(['user_id' => $user->id]);

        expect($message->user->id)->toBe($user->id);
    });

    it('can create text message', function () {
        $message = Message::factory()->text()->create();

        expect($message->type)
            ->toBe('text')
            ->and($message->metadata)
            ->toBeNull();
    });

    it('can create image message', function () {
        $message = Message::factory()->image()->create();

        expect($message->type)
            ->toBe('image')
            ->and($message->content)
            ->toBe('Image shared')
            ->and($message->metadata)
            ->toBeArray()
            ->and($message->metadata)
            ->toHaveKeys(['filename', 'size', 'url']);
    });

    it('can create file message', function () {
        $message = Message::factory()->file()->create();

        expect($message->type)
            ->toBe('file')
            ->and($message->content)
            ->toBe('File shared')
            ->and($message->metadata)
            ->toBeArray()
            ->and($message->metadata)
            ->toHaveKeys(['filename', 'size', 'mime_type']);
    });

    it('can scope unread messages for user', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $conversation = Conversation::factory()->create();

        // Create read message from user2
        $readMessage = Message::factory()->read()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $user2->id,
        ]);

        // Create unread message from user2
        $unreadMessage = Message::factory()->unread()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $user2->id,
        ]);

        // Create message from user1 (should not appear in unread for user1)
        $ownMessage = Message::factory()->unread()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $user1->id,
        ]);

        $unreadMessages = Message::query()->unread(user: $user1)->get();

        expect($unreadMessages)
            ->toHaveCount(1)
            ->and($unreadMessages->first()->id)
            ->toBe($unreadMessage->id);
    });

    it('can mark message as read', function () {
        $message = Message::factory()->unread()->create();

        expect($message->read_at)->toBeNull();

        $message->markAsRead();

        expect($message->fresh()->read_at)
            ->not->toBeNull()
            ->toBeInstanceOf(DateTimeInterface::class);
    });

    it('can create unread message', function () {
        $message = Message::factory()->unread()->create();

        expect($message->read_at)->toBeNull();
    });

    it('can create read message', function () {
        $message = Message::factory()->read()->create();

        expect($message->read_at)
            ->not->toBeNull()
            ->toBeInstanceOf(DateTimeInterface::class);
    });

    it('can create recent message', function () {
        $message = Message::factory()->recent()->create();

        expect($message->created_at)
            ->toBeInstanceOf(DateTimeInterface::class)
            ->and($message->created_at)
            ->toBeGreaterThan(now()->subMinutes(15));
    });

    it('can create old message', function () {
        $message = Message::factory()->old()->create();

        expect($message->created_at)
            ->toBeInstanceOf(DateTimeInterface::class)
            ->and($message->created_at)
            ->toBeLessThan(now()->subHours(20));
    });

    it('casts metadata to array', function () {
        $metadata = ['key' => 'value', 'number' => 42];
        $message = Message::factory()->create(['metadata' => $metadata]);

        expect($message->metadata)
            ->toBeArray()
            ->toBe($metadata);
    });

    it('casts read_at to datetime', function () {
        $message = Message::factory()->create(['read_at' => '2024-01-01 12:00:00']);

        expect($message->read_at)
            ->toBeInstanceOf(DateTimeInterface::class);
    });

    it('handles null metadata', function () {
        $message = Message::factory()->create(['metadata' => null]);

        expect($message->metadata)->toBeNull();
    });

    it('handles null read_at', function () {
        $message = Message::factory()->create(['read_at' => null]);

        expect($message->read_at)->toBeNull();
    });
});
