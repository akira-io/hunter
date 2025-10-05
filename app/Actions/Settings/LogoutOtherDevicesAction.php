<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Models\User;

final readonly class LogoutOtherDevicesAction
{
    /**
     * Logout user from all other devices except current.
     */
    public function handle(User $user, string $currentIp): int
    {
        $updated = $user->authenticationLogs()
            ->whereNotNull('login_at')
            ->whereNull('logout_at')
            ->where('ip_address', '!=', $currentIp)
            ->update([
                'logout_at' => now(),
                'cleared_by_user' => true,
            ]);

        if ($updated > 0) {
            // Log the security event
            activity()
                ->causedBy($user)
                ->log('Logged out from all other devices');
        }

        return $updated;
    }
}
