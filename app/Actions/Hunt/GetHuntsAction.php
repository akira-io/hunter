<?php

declare(strict_types=1);

namespace App\Actions\Hunt;

use App\Models\Hunt;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as ConcretePaginator;

final readonly class GetHuntsAction
{
    /**
     * Get paginated hunts with like status for the given user.
     *
     * @return LengthAwarePaginator<int, Hunt>
     */
    public function handle(User $user): LengthAwarePaginator
    {
        $userLatestHuntId = $this->getUserLatestHuntId($user);

        $hunts = $this->getHuntsQuery($user, $userLatestHuntId);

        $hunts = $this->filterByVisibility($hunts, $user);

        return $this->attachLikeStatus($hunts, $user);
    }

    /**
     * Get the ID of the user's latest hunt.
     */
    private function getUserLatestHuntId(User $user): ?int
    {
        return Hunt::query()
            ->where('owner_id', $user->id)
            ->latest()
            ->value('id');
    }

    /**
     * Build the query to get hunts: only latest from auth user, all from others.
     *
     * @return LengthAwarePaginator<int, Hunt>
     */
    private function getHuntsQuery(User $user, ?int $userLatestHuntId): LengthAwarePaginator
    {
        return Hunt::query()
            ->with('owner')
            ->where(function (Builder $query) use ($user, $userLatestHuntId): void {
                if ($userLatestHuntId !== null && $userLatestHuntId !== 0) {
                    $query->where('id', $userLatestHuntId);
                }

                $query->orWhere('owner_id', '!=', $user->id);
            })
            ->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$userLatestHuntId ?? 0])
            ->latest()
            ->paginate();
    }

    /**
     * Filter hunts based on profile visibility.
     *
     * @param  LengthAwarePaginator<int, Hunt>  $hunts
     * @return LengthAwarePaginator<int, Hunt>
     */
    private function filterByVisibility(LengthAwarePaginator $hunts, User $user): LengthAwarePaginator
    {
        /** @var ConcretePaginator<int, Hunt> $concretePaginator */
        $concretePaginator = $hunts;

        $concretePaginator->setCollection(
            $concretePaginator->getCollection()->filter(
                fn (Hunt $hunt): bool => $hunt->owner->canBeViewedBy($user)
            )
        );

        return $hunts;
    }

    /**
     * Attach like status to hunts.
     *
     * @param  LengthAwarePaginator<int, Hunt>  $hunts
     * @return LengthAwarePaginator<int, Hunt>
     */
    private function attachLikeStatus(LengthAwarePaginator $hunts, User $user): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, Hunt> $result */
        $result = $user->attachLikeStatus($hunts);

        return $result;
    }
}
