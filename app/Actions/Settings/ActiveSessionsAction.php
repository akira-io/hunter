<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Models\User;
use Illuminate\Support\Collection;

final readonly class ActiveSessionsAction
{
    /**
     * Get all active authentication sessions for the user.
     */
    public function handle(User $user): Collection
    {
        return $user->authenticationLogs()
            ->whereNotNull('login_at')
            ->whereNull('logout_at')
            ->get()
            ->map(fn ($session) => [
                'id' => $session->id,
                'ip_address' => $session->ip_address,
                'user_agent' => $session->user_agent,
                'login_at' => $session->login_at,
                'location' => $session->location,
                'is_current' => $session->ip_address === request()->ip(),
            ]);
    }
}
