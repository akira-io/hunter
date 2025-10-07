<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Actions\User\GetAvatarAction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
final class CurrentUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{id: int, name: string, avatar_url: string|null}
     */
    public function toArray(Request $request): array
    {
        $getAvatarAction = app(GetAvatarAction::class);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar_url' => $getAvatarAction->handle($this->resource),
        ];
    }
}
