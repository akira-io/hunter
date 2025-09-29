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
     *
     * @return LengthAwarePaginator<int, Hunt>
     */
    public function handle(User $user): LengthAwarePaginator
    {
        $hunts = Hunt::query()
            ->latest()
            ->paginate();

        /** @var LengthAwarePaginator<int, Hunt> $result */
        $result = $user->attachLikeStatus($hunts);

        return $result;
    }
}
