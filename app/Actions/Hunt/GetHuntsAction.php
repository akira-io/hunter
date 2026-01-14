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
        $followedUserIds = $user->following()->pluck('id')->toArray();
        $userIds = array_merge([$user->id], $followedUserIds);

        $hunts = Hunt::query()
            ->with(['owner', 'reshares'])
            ->whereIn('owner_id', $userIds)
            ->orWhereHas('reshares', function (Builder $query) use ($userIds) {
                $query->whereIn('user_id', $userIds);
            })
            ->latest()
            ->paginate();

        $hunts = $this->filterByVisibility($hunts, $user);

        return $this->attachLikeStatus($hunts, $user);
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
