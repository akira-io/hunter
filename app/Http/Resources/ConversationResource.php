<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Conversation */
final class ConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User|null $user */
        $user = $request->user();

        /** @var Collection<int, User> $participantsRelation */
        $participantsRelation = $this->participants;

        /** @var Collection<int, Message> $messagesRelation */
        $messagesRelation = $this->messages;

        $userId = $user?->id;
        $otherParticipants = $participantsRelation->where('id', '!=', $userId);
        $lastMessage = $messagesRelation->first();

        $title = $this->title;
        if (! $title) {
            $title = $otherParticipants->pluck('name')->join(', ');
        }

        return [
            'id' => $this->id,
            'title' => $title,
            'type' => $this->type,
            'created_by' => $this->created_by,
            'last_message_at' => $this->last_message_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Participants info
            'participants' => UserResource::collection($participantsRelation),
            'participants_count' => $participantsRelation->count(),

            // Last message
            'last_message' => ($lastMessage instanceof Message) ? new MessageResource($lastMessage) : null,

            // Unread count for current user
            'unread_count' => $user ? $this->messages()
                ->where('user_id', '!=', $userId)
                ->whereNull('read_at')
                ->count() : 0,

            // Current user's participation info
            'current_user_joined_at' => $user ? (function () use ($user) {
                $participation = $this->participants()
                    ->where('user_id', $user->getAttribute('id'))
                    ->first();
                if (
                    $participation && isset($participation->pivot)
                    && is_object($participation->pivot)
                    && isset($participation->pivot->joined_at)) {
                    return $participation->pivot->joined_at;
                }

                return null;
            })() : null,

            // Mobile-specific fields
            'is_muted' => false, // Para futuro uso no mobile
            'is_pinned' => false, // Para futuro uso no mobile
        ];
    }
}
