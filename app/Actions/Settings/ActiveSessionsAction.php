<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use Akira\LaravelAuthLogs\AuthenticationLog;
use App\Models\User;
use Illuminate\Support\Collection;

final readonly class ActiveSessionsAction
{
    /**
     * Get all active authentication sessions for the user.
     *
     * @return Collection<int, array{id: int, ip_address: string, user_agent: string, login_at: string, location: ?string, is_current: bool}>
     */
    public function handle(User $user): Collection
    {
        $sessions = $user->authenticationLogs()
            ->whereNotNull('login_at')
            ->whereNull('logout_at')
            ->orderBy('login_at', 'desc')
            ->get();

        $uniqueSessions = $sessions->groupBy('ip_address')->map(
            fn (AuthenticationLog $group) => $group->query()
                ->first())
            ->values();

        return $uniqueSessions->map(fn (AuthenticationLog $session): array => [
            'id' => $session->id,
            'ip_address' => $session->ip_address,
            'user_agent' => $session->user_agent,
            'login_at' => $session->login_at,
            'location' => $session->location,
            'is_current' => $session->ip_address === request()->ip(),
        ]);
    }
}
