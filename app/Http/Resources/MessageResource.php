<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class MessageResource extends JsonResource
{
    /**
     * @var \App\Models\Message
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getAttribute('id'),
            'conversation_id' => $this->resource->getAttribute('conversation_id'),
            'content' => $this->resource->getAttribute('content'),
            'type' => $this->resource->getAttribute('type'),
            'metadata' => $this->resource->getAttribute('metadata'),
            'read_at' => $this->resource->getAttribute('read_at'),
            'created_at' => $this->resource->getAttribute('created_at'),
            'updated_at' => $this->resource->getAttribute('updated_at'),

            // User info
            'user' => new UserResource($this->resource->getRelation('user')),

            // Message status
            'is_read' => ! is_null($this->resource->getAttribute('read_at')),
            'is_own' => $this->resource->getAttribute('user_id') === (($user = $request->user()) && is_object($user) && method_exists($user, 'getAttribute') ? $user->getAttribute('id') : null),

            // Mobile-specific fields
            'local_id' => null, // Para sincronização offline no mobile
            'sync_status' => 'synced', // Para controle de sincronização
        ];
    }
}
