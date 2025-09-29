<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use App\ValueObjects\GoogleUser;
use Laravel\Socialite\Two\User as SocialiteUser;

final readonly class HandleGoogleAuthAction
{
    /**
     * Handle Google authentication for a user.
     *
     * This action implements email-first authentication strategy for Google OAuth,
     * creating new users or linking to existing accounts by email.
     */
    public function handle(SocialiteUser $googleUser): User
    {
        $googleUserData = GoogleUser::from($googleUser)->toArray();

        $user = $this->findUserByEmail($googleUserData['email']);

        if ($user instanceof User) {
            return $this->updateExistingUser($user, $googleUserData);
        }

        return $this->createNewUser($googleUserData);
    }

    /**
     * Find user by email address.
     */
    private function findUserByEmail(string $email): ?User
    {
        return User::query()->firstWhere('email', $email);
    }

    /**
     * Update existing user with Google data if necessary.
     */
    private function updateExistingUser(User $user, array $googleUserData): User
    {
        $updateData = [];

        if (empty($user->avatar_url) && ! empty($googleUserData['avatar_url'])) {
            $updateData['avatar_url'] = $googleUserData['avatar_url'];
        }

        if (is_null($user->email_verified_at)) {
            $updateData['email_verified_at'] = $googleUserData['email_verified_at'];
        }

        if ($updateData !== []) {
            $user->update($updateData);
        }

        return $user;
    }

    /**
     * Create a new user from Google data.
     */
    private function createNewUser(array $googleUserData): User
    {
        return User::query()->create($googleUserData);
    }
}
