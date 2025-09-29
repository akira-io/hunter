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
     */
    public function handle(User $user): LengthAwarePaginator
    {
        $hunts = Hunt::query()
            ->latest()
            ->paginate();

        return $user->attachLikeStatus($hunts);
    }
}
