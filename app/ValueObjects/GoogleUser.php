<?php

declare(strict_types=1);

namespace App\ValueObjects;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Two\User;

final readonly class GoogleUser
{
    /**
     * The GoogleUser value object.
     */
    public function __construct(public User $user) {}

    /**
     * Create a new instance of the GoogleUser value object.
     */
    public static function from(User $user): self
    {
        return resolve(self::class, [
            'user' => $user,
        ]);
    }

    /**
     * Convert the GoogleUser value object to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->user->getName(),
            'email' => $this->user->getEmail(),
            'avatar_url' => $this->user->getAvatar(),
            'email_verified_at' => now(),
            'password' => Hash::make(Str::random(32)),
        ];
    }
}
