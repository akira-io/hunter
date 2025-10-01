<?php

declare(strict_types=1);

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\actingAs;

it('dispatches MessageSent event when a message is posted', function () {
    Event::fake();

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

    actingAs($sender);

    $response = $this->postJson('/messages', [
        'conversation_id' => $conversation->id,
        'content' => 'Hello via WS',
        'type' => 'text',
    ]);

    $response->assertCreated();

    Event::assertDispatched(MessageSent::class, function (MessageSent $event) use ($conversation) {
        return $event->message->conversation_id === $conversation->id
            && $event->broadcastOn()[0]->name === 'private-conversation.'.$conversation->id
            && $event->broadcastAs() === 'message.sent';
    });
});
