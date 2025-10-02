<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
final class UserResource extends JsonResource
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
            'email' => $this->email,
            'password' => $this->password,
            'remember_token' => $this->remember_token,
            'avatar_url' => $this->getMedia('profile_avatar')->last()?->getUrl() ?? $this->avatar_url,
            'background_image_url' => $this->getMedia('profile_background')->last()?->getUrl() ?? 'https://images.unsplash.com/photo-1746768934151-8c5cb84bcf11?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxmZWF0dXJlZC1waG90b3MtZmVlZHwzMHx8fGVufDB8fHx8fA%3D%3D',
            'location' => $this->location,
            'bio' => $this->bio,
            'user_name' => $this->user_name,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'skills' => $this->skills,
            'github_url' => $this->github_url,
            'twitter_url' => $this->twitter_url,
            'linkedin_url' => $this->linkedin_url,
            'bluesky_url' => $this->bluesky_url,
            'website_url' => $this->website_url,
            'youtube_url' => $this->youtube_url,
            'onboarding_completed' => $this->onboarding_completed,
            'onboarding_completed_at' => $this->onboarding_completed_at,
        ];
    }
}
