<?php

declare(strict_types=1);

use App\Events\ConversationsSnapshot;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('builds snapshot with last message and unread counts and correct channel/name', function () {
    $user = User::factory()->create();
    $other = User::factory()->create(['name' => 'Other']);

    $conversation = Conversation::create([
        'type' => 'direct',
        'created_by' => $user->id,
        'title' => null,
        'last_message_at' => now(),
    ]);

    $conversation->participants()->attach([
        $user->id => ['joined_at' => now(), 'is_admin' => true],
        $other->id => ['joined_at' => now(), 'is_admin' => false],
    ]);

    // Create one unread message from other to user
    Message::factory()->unread()->create([
        'conversation_id' => $conversation->id,
        'user_id' => $other->id,
        'content' => 'Hi',
        'type' => 'text',
        'metadata' => null,
    ]);

    $event = new ConversationsSnapshot($user);

    // broadcast name and channel
    expect($event->broadcastAs())->toBe('conversations.snapshot');
    $channels = $event->broadcastOn();
    expect($channels)->toHaveCount(1)
        ->and($channels[0]->name)->toBe('private-user.'.$user->id);

    // payload
    $with = $event->broadcastWith();
    expect($with)->toHaveKey('conversations');

    $list = $with['conversations'];
    expect($list)->toBeArray()->toHaveCount(1);
    $item = $list[0];

    expect($item)->toHaveKeys([
        'id', 'title', 'type', 'participants', 'last_message', 'last_message_at', 'unread_count',
    ])
        ->and($item['last_message'])->not->toBeNull()
        ->and($item['unread_count'])->toBe(1);
});

it('handles conversation without messages', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $conversation = Conversation::create([
        'type' => 'direct',
        'created_by' => $user->id,
        'title' => 'Titled convo',
    ]);

    $conversation->participants()->attach([
        $user->id => ['joined_at' => now(), 'is_admin' => true],
        $other->id => ['joined_at' => now(), 'is_admin' => false],
    ]);

    $event = new ConversationsSnapshot($user);
    $with = $event->broadcastWith();

    $list = $with['conversations'];
    expect($list)->toBeArray()->toHaveCount(1);
    $item = $list[0];

    expect($item['title'])->toBe('Titled convo')
        ->and($item['last_message'])->toBeNull()
        ->and($item['unread_count'])->toBe(0);
});
