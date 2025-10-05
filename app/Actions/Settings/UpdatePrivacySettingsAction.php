<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Models\User;

final readonly class UpdatePrivacySettingsAction
{
    /**
     * Update user's privacy settings.
     *
     * @param  array<string, mixed>  $settings
     */
    public function handle(User $user, array $settings): void
    {
        $user->update([
            'privacy_settings' => $settings,
        ]);
    }
}
