<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\User;

final readonly class UpdateProfileAction
{
    /**
     * Update user profile information.
     *
     * @param  array<string, mixed>  $profileData
     */
    public function handle(User $user, array $profileData): bool
    {
        $user->fill($profileData);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        return $user->save();
    }
}
