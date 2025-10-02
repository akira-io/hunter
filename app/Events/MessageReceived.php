<?php

declare(strict_types=1);

namespace App\Events;

use App\Actions\User\GetAvatarAction;
use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class MessageReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Message $message, public User $forUser) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return PrivateChannel[]
     */
    public function broadcastOn(): array
    {
        $userId = $this->forUser->id;
        $channelName = 'user.'.$userId;

        return [
            new PrivateChannel($channelName),
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
     * The event's broadcast data.
     *
     * @return array{message: array{id: int, content: string, type: string, metadata: mixed, created_at: mixed, user: array{id: int, name: string, avatar_url: mixed}}, conversation_id: int}
     */
    public function broadcastWith(): array
    {
        $message = $this->message;
        $user = $message->user;

        return [
            'message' => [
                'id' => $message->id,
                'content' => $message->content,
                'type' => $message->type,
                'metadata' => $message->metadata,
                'created_at' => $message->created_at,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar_url' => new GetAvatarAction()->handle($user),
                ],
            ],
            'conversation_id' => $message->conversation_id,
        ];
    }
}
