<?php

declare(strict_types=1);

namespace App\Http\Resources\Hunt;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
final class HuntOwnerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'name' => $this->name,
            'user_name' => $this->user_name,
            'avatar_url' => $this->getMedia('profile_avatar')->last()?->getUrl() ?? $this->avatar_url,
        ];
    }
}
