<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Models\User;

final readonly class DisconnectOAuthAccountAction
{
    /**
     * Disconnect an OAuth provider account.
     */
    public function handle(User $user, string $provider): bool
    {
        // Prevent disconnecting if no password is set
        if (empty($user->password)) {
            return false;
        }

        $updated = match ($provider) {
            'github' => $user->update([
                'github_id' => null,
                'github_token' => null,
                'github_refresh_token' => null,
            ]),
            'google' => $user->update([
                'google_id' => null,
                'google_token' => null,
                'google_refresh_token' => null,
            ]),
            default => false,
        };

        if ($updated) {
            // Log the security event
            activity()
                ->causedBy($user)
                ->log(ucfirst($provider).' account disconnected');
        }

        return $updated;
    }
}
