<?php

declare(strict_types=1);

namespace App\Actions\Chat;

use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class DeleteMessageAction
{
    /**
     * Delete a message (only by its author).
     *
     * @throws ModelNotFoundException
     */
    public function handle(User $user, int $messageId): void
    {
        /** @var Message $message */
        $message = Message::query()
            ->where('user_id', $user->getAttribute('id'))
            ->findOrFail($messageId);

        $message->delete();
    }
}
