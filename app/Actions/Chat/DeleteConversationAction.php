<?php

declare(strict_types=1);

namespace App\Actions\Chat;

use App\Models\Conversation;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class DeleteConversationAction
{
    /**
     * Delete a conversation (only by creator).
     *
     * @throws ModelNotFoundException|Exception
     */
    public function handle(User $user, int $conversationId): void
    {
        /** @var Conversation $conversation */
        $conversation = Conversation::query()
            ->whereHas('participants', function ($q) use ($user): void {
                $q->where('user_id', $user->getAttribute('id'));
            })
            ->with('creator')
            ->findOrFail($conversationId);

        $creator = $conversation->getRelation('creator');
        if ($creator instanceof User && $creator->getAttribute('id') !== $user->getAttribute('id')) {
            throw new Exception('Unauthorized');
        }

        $conversation->delete();
    }
}
