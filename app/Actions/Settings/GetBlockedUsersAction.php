<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Models\BlockedUser;
use App\Models\User;
use Illuminate\Support\Collection;

final readonly class GetBlockedUsersAction
{
    /**
     * Get all users blocked by the given user.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function handle(User $user): Collection
    {
        return $user->blockedUsers()
            ->with('blocked:id,name,user_name,avatar_url')
            ->get()
            ->map(fn (BlockedUser $blockedUser): array => [
                'id' => $blockedUser->blocked->id,
                'name' => $blockedUser->blocked->name,
                'user_name' => $blockedUser->blocked->user_name,
                'avatar_url' => $blockedUser->blocked->avatar_url,
                'blocked_at' => $blockedUser->created_at->format('d-m-Y'),
            ]);
    }
}
