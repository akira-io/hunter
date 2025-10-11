<?php

declare(strict_types=1);

namespace App\Actions\Hunt;

use App\Models\Hunt;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class GetUserHuntsAction
{
    /**
     * Get all paginated hunts created by the given user, ordered by most recent.
     *
     * @return LengthAwarePaginator<int, Hunt>
     */
    public function handle(User $user): LengthAwarePaginator
    {
        $hunts = Hunt::query()
            ->with('owner')
            ->where('owner_id', $user->id)
            ->latest()
            ->paginate();

        /** @var LengthAwarePaginator<int, Hunt> $result */
        $result = $user->attachLikeStatus($hunts);

        return $result;
    }
}
