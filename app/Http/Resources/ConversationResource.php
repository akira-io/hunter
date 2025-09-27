<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $otherParticipants = $this->participants->where('id', '!=', $user?->id);
        $lastMessage = $this->messages->first();

        return [
            'id' => $this->id,
            'title' => $this->title ?: $otherParticipants->pluck('name')->join(', '),
            'type' => $this->type,
            'created_by' => $this->created_by,
            'last_message_at' => $this->last_message_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Participants info
            'participants' => UserResource::collection($this->participants),
            'participants_count' => $this->participants->count(),

            // Last message
            'last_message' => $lastMessage ? new MessageResource($lastMessage) : null,

            // Unread count for current user
            'unread_count' => $this->messages()
                ->where('user_id', '!=', $user?->id)
                ->whereNull('read_at')
                ->count(),

            // Current user's participation info
            'current_user_joined_at' => $user ? $this->participants()
                ->where('user_id', $user->id)
                ->first()?->pivot?->joined_at : null,

            // Mobile-specific fields
            'is_muted' => false, // Para futuro uso no mobile
            'is_pinned' => false, // Para futuro uso no mobile
        ];
    }
}
