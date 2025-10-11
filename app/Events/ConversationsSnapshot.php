<?php

declare(strict_types=1);

namespace App\Events;

use App\Actions\User\GetAvatarAction;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ConversationsSnapshot implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

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
     * @return PrivateChannel[]
     */
    public function broadcastOn(): array
    {

        $userId = $this->user->id;

        return [new PrivateChannel('user.'.$userId)];
    }

    /**
     * The event's broadcast name.'
     */
    public function broadcastAs(): string
    {

        return 'conversations.snapshot';
    }

    /**
     * The event's broadcast data.'
     *
     * @return array{conversations: array<int,
     *     array{id:int,title:string|null,type:string,avatar_url:string|null,participants:array<int,array{id:int,name:string,avatar_url:string|null}>,last_message:array{id:int,content:string,type:string,created_at:string,user:array{id:int,name:string}}|null,last_message_at:string|null,unread_count:int}>}
     */
    public function broadcastWith(): array
    {

        $user = $this->user;

        $query = $user->conversations();

        $query->with('participants');
        // @phpstan-ignore-next-line
        $query->with(['messages' => fn (HasMany $query) => $query->latest()->limit(1)->with('user')]);

        /** @var Collection<int, Conversation> $conversationsCollection */
        $conversationsCollection = $query->orderBy('last_message_at', 'desc')
            ->get();

        $conversations = $conversationsCollection->map(function (Conversation $conversation) use ($user): array {

            /** @var Collection<int, Message> $messages */
            $messages = $conversation->messages;
            $lastMessage = $messages->first();

            /** @var Collection<int, User> $participants */
            $participants = $conversation->participants;
            $otherParticipants = $participants->where('id', '!=', $user->id);

            $firstOtherParticipant = $otherParticipants->first();

            $conversationId = $conversation->id;
            /** @var string|null $conversationTitle */
            $conversationTitle = $conversation->title;
            $conversationType = $conversation->type;
            $conversationLastMessageAt = $conversation->last_message_at;
            $userId = $user->id;

            /** @var string|null $finalTitle */
            $finalTitle = $conversationTitle
                ?: ($otherParticipants->pluck('name')->join(', ')
                    ?: 'Conversation #'.$conversationId);

            return [
                'id' => $conversation->id,
                'title' => $finalTitle,
                'type' => $conversationType,
                'avatar_url' => ($firstOtherParticipant instanceof User)
                    ? new GetAvatarAction()->handle($firstOtherParticipant) : null,
                'participants' => $participants->map(static function (User $participant): array {

                    $participantId = $participant->id;
                    $participantName = $participant->name;
                    /** @var string|null $participantAvatarUrl */
                    $participantAvatarUrl = $participant->avatar_url;

                    return [
                        'id' => $participantId,
                        'name' => $participantName,
                        'avatar_url' => $participantAvatarUrl,
                    ];
                })->values()->all(),
                'last_message' => ($lastMessage instanceof Message) ? [
                    'id' => $lastMessage->id,
                    'content' => $lastMessage->content,
                    'type' => $lastMessage->type,
                    'created_at' => $lastMessage->created_at->toISOString(),
                    'user' => (function () use ($lastMessage): array {
                        $messageUser = $lastMessage->user;

                        return [
                            'id' => $messageUser->id,
                            'name' => $messageUser->name,
                        ];
                    })(),
                ] : null,
                'last_message_at' => $conversationLastMessageAt?->toISOString(),
                'unread_count' => $conversation->messages()
                    ->where('user_id', '!=', $userId)
                    ->whereNull('read_at')
                    ->count(),
            ];
        })->values()->all();

        return [
            'conversations' => $conversations,
        ];
    }
}
