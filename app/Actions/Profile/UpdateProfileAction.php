<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\DataTransferObjects\Profile\UpdateProfileData;
use App\Models\User;

final readonly class UpdateProfileAction
{
    /**
     * Update user profile information.
     */
    public function handle(User $user, UpdateProfileData $profileData): bool
    {
        $user->fill($profileData->toArray());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        return $user->save();
    }
}
