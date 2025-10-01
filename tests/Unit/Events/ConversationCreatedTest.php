<?php

declare(strict_types=1);

use App\Events\ConversationCreated;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('builds payload with title fallback, participants and correct channel/name', function () {
    $userA = User::factory()->create(['name' => 'Alice']);
    $userB = User::factory()->create(['name' => 'Bob']);

    $conversation = Conversation::create([
        'type' => 'direct',
        'created_by' => $userA->id,
        'title' => null,
    ]);

    $conversation->participants()->attach([
        $userA->id => ['joined_at' => now(), 'is_admin' => true],
        $userB->id => ['joined_at' => now(), 'is_admin' => false],
    ]);

    $event = new ConversationCreated($conversation->fresh('participants'), $userA);

    // Broadcast name
    expect($event->broadcastAs())->toBe('conversation.created');

    // Broadcast channel is the forUser private channel
    $channels = $event->broadcastOn();
    expect($channels)->toHaveCount(1)
        ->and($channels[0]->name)->toBe('private-user.'.$userA->id);

    // Payload
    $with = $event->broadcastWith();
    expect($with)->toHaveKey('conversation');

    $payload = $with['conversation'];
    expect($payload)->toHaveKeys([
        'id', 'title', 'type', 'participants', 'last_message', 'last_message_at', 'unread_count',
    ])
        ->and($payload['title'])->toBe('Bob')
        ->and($payload['participants'])->toBeArray()->toHaveCount(2);

});
