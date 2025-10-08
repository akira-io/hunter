<?php

declare(strict_types=1);

namespace App\Actions\Hunt;

use App\Models\Hunt;
use Illuminate\Support\Collection;

final readonly class GetRecentHuntIdsAction
{
    /**
     * Handle the action
     *
     * @return Collection<int, int>
     */
    public function handle(): Collection
    {
        return Hunt::query()
            ->where('created_at', '>=', now()->subDay())
            ->pluck('id');
    }
}
