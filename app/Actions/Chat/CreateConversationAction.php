<?php

declare(strict_types=1);

namespace App\Actions\Chat;

use App\Events\ConversationCreated;
use App\Models\Conversation;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateConversationAction
{
    /**
     * Create a new conversation.
     *
     * @param  array<int>  $participantIds
     *
     * @throws Throwable
     */
    public function handle(User $creator, string $type, array $participantIds, ?string $title = null): array
    {

        $filteredParticipantIds = collect($participantIds)
            ->filter(fn (int $id): bool => $id !== $creator->getAttribute('id'))
            ->values();

        if ($type === 'direct' && $filteredParticipantIds->count() !== 1) {
            throw new Exception('Direct conversations must have exactly one other participant');
        }

        if ($type === 'direct') {
            $otherUser = User::query()->find($filteredParticipantIds->first());
            if (! $otherUser instanceof User) {
                throw new Exception('Invalid participant');
            }

            $existingConversation = $this->findExistingDirectConversation($creator, $otherUser);
            if ($existingConversation) {
                return [
                    'id' => $existingConversation->getAttribute('id'),
                    'message' => 'Conversation already exists',
                    'existing' => true,
                ];
            }
        }

        return DB::transaction(function () use ($creator, $type, $title, $filteredParticipantIds): array {
            $conversation = Conversation::query()->create([
                'title' => $title,
                'type' => $type,
                'created_by' => $creator->getAttribute('id'),
            ]);

            $this->attachParticipants($conversation, $creator, $filteredParticipantIds);

            $this->broadcastConversationCreated($conversation);

            return [
                'id' => $conversation->getAttribute('id'),
                'message' => 'Conversation created successfully',
                'existing' => false,
            ];
        });
    }

    /**
     * Find existing direct conversation between two users.
     */
    private function findExistingDirectConversation(User $user1, User $user2): ?Conversation
    {
        return Conversation::query()
            ->where('type', 'direct')
            ->whereHas('participants', function (Builder $q) use ($user1): void {
                $q->where('user_id', $user1->getAttribute('id'));
            })
            ->whereHas('participants', function (Builder $q) use ($user2): void {
                $q->where('user_id', $user2->getAttribute('id'));
            })
            ->has('participants', '=', 2)
            ->first();
    }

    /**
     * Attach participants to conversation.
     *
     * @param  \Illuminate\Support\Collection<int, int>  $participantIds
     */
    private function attachParticipants(Conversation $conversation, User $creator, $participantIds): void
    {
        $allParticipants = $participantIds->concat([$creator->getAttribute('id')]);

        /** @var array<int, array{joined_at: Carbon, is_admin: bool}> $attachData */
        $attachData = [];
        $creatorId = (int) $creator->getAttribute('id');

        foreach ($allParticipants as $id) {
            if (is_numeric($id)) {
                $intId = (int) $id;
                $attachData[$intId] = [
                    'joined_at' => now(),
                    'is_admin' => $intId === $creatorId,
                ];
            }
        }

        $conversation->participants()->attach($attachData);
    }

    /**
     * Broadcast conversation created event to all participants.
     */
    private function broadcastConversationCreated(Conversation $conversation): void
    {
        $conversation->load('participants');

        /** @var Collection<int, User> $participants */
        $participants = $conversation->getRelation('participants');

        foreach ($participants as $participant) {
            ConversationCreated::dispatch($conversation, $participant);
        }
    }
}
