<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ConversationCreated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    /** @var array{id:int,title:?string,type:string,participants:array<int,array{id:int,name:string,avatar_url:?string}>,last_message:null,last_message_at:mixed,unread_count:int} */
    public array $conversation;

    public function __construct(public Conversation $model, public User $forUser)
    {
        $this->conversation = [
            'id' => $model->id,
            'title' => $model->title ?: $model->participants->where('id', '!=', $forUser->id)->pluck('name')->join(', '),
            'type' => $model->type,
            'participants' => $model->participants->map(static fn (User $participant) => [
                'id' => $participant->id,
                'name' => $participant->name,
                'avatar_url' => $participant->avatar_url,
            ])->values()->all(),
            'last_message' => null,
            'last_message_at' => $model->last_message_at,
            'unread_count' => 0,
        ];
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.'.$this->forUser->id)];
    }

    public function broadcastAs(): string
    {
        return 'conversation.created';
    }

    /**
     * @return array{conversation: array{id:int,title:?string,type:string,participants:array<int,array{id:int,name:string,avatar_url:?string}>,last_message:null,last_message_at:mixed,unread_count:int}}
     */
    public function broadcastWith(): array
    {
        return [
            'conversation' => $this->conversation,
        ];
    }
}
