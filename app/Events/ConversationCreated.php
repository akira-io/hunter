<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ConversationCreated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    /** @var array{id:int,title:?string,type:string,participants:array<int,array{id:int,name:string,avatar_url:?string}>,last_message:null,last_message_at:?string,unread_count:int} */
    public array $conversation;

    /**
     * Create a new event instance.
     */
    public function __construct(public Conversation $model, public User $forUser)
    {

        /** @var Collection<int, User> $participantsCollection */
        $participantsCollection = $model->participants;

        /** @var array<int,array{id:int,name:string,avatar_url:string|null}> $participants */
        $participants = $participantsCollection->map(static function (User $participant): array {
            $id = $participant->id;
            $name = $participant->name;
            $avatarUrl = $participant->avatar_url;

            return [
                'id' => $id,
                'name' => $name,
                'avatar_url' => $avatarUrl,
            ];
        })->values()->all();

        $title = $model->title;

        if (! $title) {
            $title = $participantsCollection->where('id', '!=', $forUser->id)->pluck('name')->join(', ');
        }

        $this->conversation = [
            'id' => $model->id,
            'title' => $title ?: '',
            'type' => $model->type,
            'participants' => $participants,
            'last_message' => null,
            'last_message_at' => $model->last_message_at?->toISOString(),
            'unread_count' => 0,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return PrivateChannel[]
     */
    public function broadcastOn(): array
    {
        $userId = $this->forUser->id;

        return [new PrivateChannel('user.'.$userId)];
    }

    /**
     * The event's broadcast name.'
     */
    public function broadcastAs(): string
    {
        return 'conversation.created';
    }

    /**
     * The event's broadcast data.'
     *
     * @return array{conversation: array{id:int,title:?string,type:string,participants:array<int,array{id:int,name:string,avatar_url:?string}>,last_message:null,last_message_at:?string,unread_count:int}}
     */
    public function broadcastWith(): array
    {
        return [
            'conversation' => $this->conversation,
        ];
    }
}
