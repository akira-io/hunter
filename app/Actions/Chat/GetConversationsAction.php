<?php

declare(strict_types=1);

namespace App\Actions\Chat;

use App\Actions\User\GetAvatarAction;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Builder;

final readonly class GetConversationsAction
{
    /**
     * Get new instance.
     */
    public function __construct(
        private GetAvatarAction $getAvatarAction
    ) {}

    /**
     * Get all conversations for a user.
     *
     * @return array<int, array<string, mixed>>
     */
    public function handle(User $user): array
    {
        /** @var Collection<int, Conversation> $conversationsCollection */
        $conversationsCollection = $user->conversations()
            ->with([
                'participants',
                'messages' => function (Builder|HasMany $query): void {
                    // @phpstan-ignore-next-line
                    $query->latest()->limit(1)->with('user');
                },
            ])
            ->orderBy('last_message_at', 'desc')
            ->get();

        /** @var array<int, array<string, mixed>> $result */
        $result = $conversationsCollection->map(function (Conversation $conversation) use ($user): array {
            /** @var Collection<int, Message> $messages */
            $messages = $conversation->getRelation('messages');
            $lastMessage = $messages->first();

            /** @var Collection<int, User> $participants */
            $participants = $conversation->getRelation('participants');
            $userId = $user->id;
            $otherParticipants = $participants->where('id', '!=', $userId);

            /** @var User $otherParticipant */
            $otherParticipant = $otherParticipants->first();

            return [
                'id' => $conversation->id,
                'title' => $conversation->title ?: $otherParticipants->pluck('name')->join(', '),
                'type' => $conversation->type,
                'avatar_url' => $this->getAvatarAction->handle($otherParticipant),
                'participants' => $participants->map(fn (User $participant): array => [
                    'id' => $participant->id,
                    'name' => $participant->name,
                    'avatar_url' => $this->getAvatarAction->handle($participant),
                ]),
                'last_message' => ($lastMessage instanceof Message) ? $this->formatMessage($lastMessage) : null,
                'last_message_at' => $conversation->last_message_at,
                'unread_count' => $conversation->messages()
                    ->where('user_id', '!=', $userId)
                    ->whereNull('read_at')
                    ->count(),
            ];
        })->values()->toArray();

        return $result;
    }

    /**
     * Format a message for API response.
     *
     * @return array{id: mixed, content: mixed, type: mixed, created_at: mixed, user: array{id: mixed, name: mixed, avatar_url: string|null}}
     */
    private function formatMessage(Message $message): array
    {
        $user = $message->user;

        return [
            'id' => $message->id,
            'content' => $message->content,
            'type' => $message->type,
            'metadata' => $message->metadata,
            'created_at' => $message->created_at,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar_url' => $this->getAvatarAction->handle($user),
            ],
        ];
    }
}
