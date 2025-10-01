<?php

declare(strict_types=1);

namespace App\Events;

use App\Actions\User\GetAvatarAction;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class UserOnline implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public User $user)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel[]
     */
    public function broadcastOn(): array
    {
        return [new Channel('online')];
    }

    /**
     * The event's broadcast name.'
     */
    public function broadcastAs(): string
    {
        return 'user.online';
    }

    /**
     * The event's broadcast data.'
     *
     * @return array{user: array{id:int, name:string, avatar_url: string|null}}
     */
    public function broadcastWith(): array
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar_url' => new GetAvatarAction()->handle($this->user),
            ],
        ];
    }
}
