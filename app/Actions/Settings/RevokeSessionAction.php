<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Models\User;

final readonly class RevokeSessionAction
{
    /**
     * Revoke a specific authentication session.
     */
    public function handle(User $user, int $sessionId): bool
    {
        $updated = $user->authenticationLogs()
            ->where('id', $sessionId)
            ->whereNull('logout_at')
            ->update([
                'logout_at' => now(),
                'cleared_by_user' => true,
            ]);

        if ($updated) {
            // Log the security event
            activity()
                ->causedBy($user)
                ->log('Session revoked');
        }

        return $updated > 0;
    }
}
