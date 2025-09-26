<?php

declare(strict_types=1);

namespace App\Events;

use App\Actions\User\GetAvatarAction;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ConversationsSnapshot implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public User $user)
    {
        //
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.'.$this->user->id)];
    }

    public function broadcastAs(): string
    {
        return 'conversations.snapshot';
    }

    /**
     * @return array{conversations: array<int, array{id:int,title:?string,type:string,participants:array<int,array{id:int,name:string,avatar_url:?string}>,last_message:array{id:int,content:string,type:string,created_at:mixed,user:array{id:int,name:string}}|null,last_message_at:mixed,unread_count:int}>}
     */
    public function broadcastWith(): array
    {
        $user = $this->user;

        $conversations = $user->conversations()
            ->with(['participants', 'messages' => function ($query) {
                $query->latest()->limit(1)->with('user');
            }])
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function (Conversation $conversation) use ($user) {
                $lastMessage = $conversation->messages->first();
                $otherParticipants = $conversation->participants->where('id', '!=', $user->id);

                return [
                    'id' => $conversation->id,
                    'title' => $conversation->title ?: $otherParticipants->pluck('name')->join(', '),
                    'type' => $conversation->type,
                    'avatar_url' => new GetAvatarAction()->handle($otherParticipants->first()),
                    'participants' => $conversation->participants->map(static fn (User $participant) => [
                        'id' => $participant->id,
                        'name' => $participant->name,
                        'avatar_url' => $participant->avatar_url,
                    ])->values()->all(),
                    'last_message' => $lastMessage ? [
                        'id' => $lastMessage->id,
                        'content' => $lastMessage->content,
                        'type' => $lastMessage->type,
                        'created_at' => $lastMessage->created_at,
                        'user' => [
                            'id' => $lastMessage->user->id,
                            'name' => $lastMessage->user->name,
                        ],
                    ] : null,
                    'last_message_at' => $conversation->last_message_at,
                    'unread_count' => $conversation->messages()
                        ->where('user_id', '!=', $user->id)
                        ->whereNull('read_at')
                        ->count(),
                ];
            })->values()->all();

        return [
            'conversations' => $conversations,
        ];
    }
}
