<?php

declare(strict_types=1);

namespace App\Actions\Chat;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class MarkMessagesAsReadAction
{
    /**
     * Mark messages as read for a user in a conversation.
     *
     * @param  array<int>  $messageIds
     *
     * @throws ModelNotFoundException
     */
    public function handle(User $user, int $conversationId, array $messageIds = []): int
    {

        /** @var Conversation $conversation */
        $conversation = Conversation::query()
            ->whereHas('participants', function ($q) use ($user): void {
                $q->where('user_id', $user->getAttribute('id'));
            })
            ->findOrFail($conversationId);

        $query = Message::query()
            ->where('conversation_id', $conversation->getAttribute('id'))
            ->where('user_id', '!=', $user->getAttribute('id'))
            ->whereNull('read_at');

        if (! empty($messageIds)) {
            $query->whereIn('id', $messageIds);
        }

        $updatedCount = $query->update(['read_at' => now()]);

        $conversation->participants()->updateExistingPivot($user->getAttribute('id'), [
            'last_read_at' => now(),
        ]);

        return $updatedCount;
    }
}
