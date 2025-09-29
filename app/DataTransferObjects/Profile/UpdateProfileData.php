<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Profile;

use App\Http\Requests\Settings\ProfileUpdateRequest;

final readonly class UpdateProfileData
{
    /**
     * Create profile update data transfer object.
     */
    public function __construct(
        public string $name,
        public string $email,
        public ?string $bio = null,
        public ?string $location = null,
    ) {}

    /**
     * Create from array data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            bio: $data['bio'] ?? null,
            location: $data['location'] ?? null,
        );
    }

    /**
     * Create from profile update request.
     */
    public static function fromRequest(ProfileUpdateRequest $request): self
    {
        return new self(
            name: $request->input('name'),
            email: $request->input('email'),
            bio: $request->input('bio'),
            location: $request->input('location'),
        );
    }

    /**
     * Get data as array for model update.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'bio' => $this->bio,
            'location' => $this->location,
        ], fn ($value) => $value !== null);
    }
}
