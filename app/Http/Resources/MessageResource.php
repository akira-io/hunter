<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'content' => $this->content,
            'type' => $this->type,
            'metadata' => $this->metadata,
            'read_at' => $this->read_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // User info
            'user' => new UserResource($this->user),

            // Message status
            'is_read' => ! is_null($this->read_at),
            'is_own' => $this->user_id === $request->user()?->id,

            // Mobile-specific fields
            'local_id' => null, // Para sincronização offline no mobile
            'sync_status' => 'synced', // Para controle de sincronização
        ];
    }
}
