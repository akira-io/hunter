<?php

declare(strict_types=1);

namespace App\Actions\Followable;

use Akira\Followable\Exceptions\FollowableTraitNotFoundException;
use Akira\Followable\Followable;
use App\Models\User;
use Illuminate\Support\Collection;

final readonly class GetHuntingsAction
{
    /**
     * Get the huntings of the authenticated user.
     *
     * @return Collection<int, mixed>
     *
     * @throws FollowableTraitNotFoundException
     */
    public function handle(User $user): Collection
    {
        return $user->followings()
            ->paginate()->map(fn (Followable $followable) => $followable->followable) // @phpstan-ignore-line
            ->filter()
            ->values();
    }
}
