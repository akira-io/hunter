<?php

declare(strict_types=1);

namespace App\Actions\Chat;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class SendMessageAction
{
    /**
     * Send a message in a conversation.
     *
     * @param  array<string, mixed>|null  $metadata
     *
     * @throws Throwable
     */
    public function handle(
        User $user,
        int $conversationId,
        string $content,
        string $type = 'text',
        ?array $metadata = null
    ): Message {

        $conversation = $this->findConversationForUser($user, $conversationId);

        return DB::transaction(function () use ($user, $conversation, $content, $type, $metadata): Message {

            /** @var Message $message */
            $message = Message::query()->create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'content' => $content,
                'type' => $type,
                'metadata' => $metadata,
            ]);

            $conversation->update(['last_message_at' => now()]);

            MessageSent::dispatch($message);

            $message->load('user');

            return $message;
        });
    }

    /**
     * Find conversation for user (validate user is participant).
     *
     * @throws ModelNotFoundException
     */
    private function findConversationForUser(User $user, int $conversationId): Conversation
    {
        /** @var Conversation|null $conversation */
        $conversation = Conversation::query()
            ->whereHas('participants', function ($q) use ($user): void {
                $q->where('user_id', $user->id);
            })
            ->find($conversationId);

        if (! $conversation) {
            throw new ModelNotFoundException('Conversation not found or user is not a participant');
        }

        return $conversation;
    }
}
