<?php

declare(strict_types=1);

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('broadcasts on conversation and user channels with correct payload and name', function () {
    $sender = User::factory()->create();
    $receiver = User::factory()->create();

    $conversation = Conversation::create([
        'type' => 'direct',
        'created_by' => $sender->id,
    ]);

    $conversation->participants()->attach([
        $sender->id => ['joined_at' => now(), 'is_admin' => true],
        $receiver->id => ['joined_at' => now(), 'is_admin' => false],
    ]);

    $message = Message::create([
        'conversation_id' => $conversation->id,
        'user_id' => $sender->id,
        'content' => 'Hello',
        'type' => 'text',
        'metadata' => null,
    ]);

    $event = new MessageSent($message);

    // broadcast name
    expect($event->broadcastAs())->toBe('message.sent');

    // channels include conversation and both users
    $channels = $event->broadcastOn();
    $channelNames = array_map(fn ($c) => $c->name, $channels);

    expect($channelNames)->toContain('private-conversation.'.$conversation->id)
        ->toContain('private-user.'.$sender->id)
        ->toContain('private-user.'.$receiver->id);

    // payload
    $with = $event->broadcastWith();
    expect($with)->toHaveKeys(['message', 'conversation_id'])
        ->and($with['conversation_id'])->toBe($conversation->id)
        ->and($with['message'])->toHaveKeys(['id', 'content', 'type', 'metadata', 'created_at', 'user'])
        ->and($with['message']['user'])->toHaveKeys(['id', 'name', 'avatar_url']);
});
