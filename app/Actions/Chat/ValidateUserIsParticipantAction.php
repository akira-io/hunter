<?php

declare(strict_types=1);

namespace App\Actions\Chat;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final readonly class ValidateUserIsParticipantAction
{
    /**
     * Validate that a user is a participant of a conversation.
     *
     * @throws HttpException
     */
    public function handle(User $user, int $conversationId): Conversation
    {
        /** @var Conversation|null $conversation */
        $conversation = Conversation::query()
            ->whereHas('participants', function (Builder $query) use ($user): void {
                $query->where('user_id', $user->id);
            })
            ->find($conversationId);

        if ($conversation === null) {
            throw new HttpException(
                Response::HTTP_FORBIDDEN,
                'You are not authorized to access this conversation'
            );
        }

        return $conversation;
    }
}
