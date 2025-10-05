<?php

declare(strict_types=1);

namespace App\Actions\Chat;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final readonly class GetOnlineUsersAction
{
    /**
     * Get list of online users (only those followed or with existing conversations).
     * Filters users based on privacy settings - only shows users who accept messages from the current user
     * and who have enabled activity status visibility.
     *
     * @return Collection<int, User>
     */
    public function handle(User $user): Collection
    {
        $followedUserIds = User::query()
            ->whereHas('followers', fn (Builder $query) => $query
                ->where('user_id', $user->id)
                ->whereNotNull('accepted_at'))
            ->pluck('id')
            ->toArray();

        $conversationUserIds = $user->conversations()
            ->with('participants')
            ->get()
            ->flatMap(fn (Conversation $conversation) => $conversation->participants->pluck('id'))
            ->unique()
            ->filter(fn (mixed $id): bool => $id !== $user->id)
            ->toArray();

        $relevantUserIds = array_unique([...$followedUserIds, ...$conversationUserIds]);

        /** @var array<int, string> $relevantUserIds */
        /** @var array<int, string> $onlineUserIds */
        $onlineUserIds = [];

        foreach ($relevantUserIds as $userId) {
            if (Cache::has("user_online_{$userId}")) {
                $onlineUserIds[] = $userId;
            }
        }

        /** @var Collection<int, User> */
        $onlineUsers = User::query()
            ->whereIn('id', $onlineUserIds)
            ->get();

        // Filter based on privacy settings:
        // 1. Only show users who accept messages from current user
        // 2. Only show users who have enabled activity status visibility
        return $onlineUsers->filter(fn (User $onlineUser): bool => $onlineUser->canReceiveMessagesFrom($user) && $onlineUser->showsActivityStatus());
    }
}
