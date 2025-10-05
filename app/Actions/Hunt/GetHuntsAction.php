<?php

declare(strict_types=1);

namespace App\Actions\Hunt;

use App\Models\Hunt;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class GetHuntsAction
{
    /**
     * Get paginated hunts with like status for the given user.
     * Filters hunts based on profile visibility - only shows hunts from users the current user can view.
     *
     * @return LengthAwarePaginator<int, Hunt>
     */
    public function handle(User $user): LengthAwarePaginator
    {
        $hunts = Hunt::query()
            ->with('owner')
            ->latest()
            ->paginate();

        // Filter hunts based on profile visibility
        $hunts->setCollection(
            $hunts->getCollection()->filter(fn (Hunt $hunt): bool => $hunt->owner->canBeViewedBy($user))
        );

        /** @var LengthAwarePaginator<int, Hunt> $result */
        $result = $user->attachLikeStatus($hunts);

        return $result;
    }
}
