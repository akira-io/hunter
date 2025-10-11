<?php

declare(strict_types=1);

namespace App\Actions\Chat;

use App\Actions\User\GetAvatarAction;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class GetConversationMessagesAction
{
    /**
     * Get new instance.
     */
    public function __construct(
        private GetAvatarAction $getAvatarAction
    ) {}

    /**
     * Get conversation with its messages for a user.
     *
     * @return array<string, mixed>
     *
     * @throws ModelNotFoundException
     */
    public function handle(User $user, int $conversationId): array
    {
        /** @var Conversation $conversation */
        $conversation = Conversation::query()
            ->whereHas('participants', function (Builder $q) use ($user): void {
                $q->where('user_id', $user->getAttribute('id'));
            })
            ->findOrFail($conversationId);

        $messagesCollection = $conversation->messages()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        /** @var \Illuminate\Support\Collection<int, array{id: mixed, content: mixed, type: mixed, metadata: mixed, created_at: mixed, user: array{id: mixed, name: mixed, avatar_url: string|null}}> $messages */
        $messages = $messagesCollection->map(fn (Message $message): array => $this->formatMessage($message));

        /** @var Collection<int, User> $participants */
        $participants = $conversation->participants;

        return [
            'id' => $conversation->id,
            'title' => $conversation->title,
            'type' => $conversation->type,
            'participants' => $participants->map(fn (User $participant): array => [
                'id' => $participant->id,
                'name' => $participant->name,
                'avatar_url' => $this->getAvatarAction->handle($participant),
            ]),
            'messages' => $messages,
            'unread_count' => $conversation->messages()
                ->where('user_id', '!=', $user->getAttribute('id'))
                ->whereNull('read_at')
                ->count(),
        ];
    }

    /**
     * Format a message for API response.
     *
     * @return array{id: mixed, content: mixed, type: mixed, metadata: mixed, created_at: mixed, user: array{id: mixed, name: mixed, avatar_url: string|null}}
     */
    private function formatMessage(Message $message): array
    {
        /** @var User $user */
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
