<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Models\User;

final readonly class UpdateNotificationSettingsAction
{
    /**
     * Update user notification settings.
     *
     * @param  array<string, bool>  $settings
     */
    public function handle(User $user, array $settings): bool
    {
        return $user->update([
            'notification_settings' => $settings,
        ]);
    }
}
