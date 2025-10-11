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
            name: (string) $data['name'],
            email: (string) $data['email'],
            bio: isset($data['bio']) ? (string) $data['bio'] : null,
            location: isset($data['location']) ? (string) $data['location'] : null,
        );
    }

    /**
     * Create from profile update request.
     */
    public static function fromRequest(ProfileUpdateRequest $request): self
    {
        return new self(
            name: (string) $request->input('name'),
            email: (string) $request->input('email'),
            bio: $request->input('bio') ? (string) $request->input('bio') : null,
            location: $request->input('location') ? (string) $request->input('location') : null,
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
        ], fn (?string $value): bool => $value !== null);
    }
}
