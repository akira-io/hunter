<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Message $message) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return PrivateChannel[]
     */
    public function broadcastOn(): array
    {
        $conversationId = $this->message->getAttribute('conversation_id');
        if (!is_numeric($conversationId)) {
            throw new \InvalidArgumentException('Conversation ID must be numeric');
        }
        return [
            new PrivateChannel('conversation.'.$conversationId),
        ];
    }

    /**
     * The event's broadcast name.'
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * The event's broadcast data.'
     *
     * @return array{message: array{id: int, content: string, type: string, metadata: mixed, created_at: mixed, user: array{id: int, name: string, avatar_url: mixed}}}
     */
    public function broadcastWith(): array
    {
        $messageId = $this->message->getAttribute('id');
        $messageContent = $this->message->getAttribute('content');
        $messageType = $this->message->getAttribute('type');
        $user = $this->message->getRelation('user');

        if (!is_numeric($messageId) || !is_string($messageContent) || !is_string($messageType)) {
            throw new \InvalidArgumentException('Invalid message attributes');
        }

        if (!($user instanceof \App\Models\User)) {
            throw new \InvalidArgumentException('User relation must be a User instance');
        }

        $userId = $user->getAttribute('id');
        $userName = $user->getAttribute('name');

        if (!is_numeric($userId) || !is_string($userName)) {
            throw new \InvalidArgumentException('Invalid user attributes');
        }

        return [
            'message' => [
                'id' => (int) $messageId,
                'content' => $messageContent,
                'type' => $messageType,
                'metadata' => $this->message->getAttribute('metadata'),
                'created_at' => $this->message->getAttribute('created_at'),
                'user' => [
                    'id' => (int) $userId,
                    'name' => $userName,
                    'avatar_url' => $user->getAttribute('avatar_url'),
                ],
            ],
        ];
    }
}
