<?php

declare(strict_types=1);

namespace App\Actions\Hunt;

use App\Models\Hunt;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class UpdateHuntViewsFromPanAction
{
    /**
     * Handle the action - update hunt views from Pan analytics.
     *
     * @param  Collection<int, int>|null  $huntIds  Specific hunt IDs to update. If null, updates all.
     * @return int Number of hunts updated
     */
    public function handle(?Collection $huntIds = null): int
    {

        $query = $this->buildQuery();

        if ($this->containsHuntIds($huntIds)) {
            $huntNames = $huntIds->map(fn ($id): string => "hunt-{$id}");
            $query->whereIn('name', $huntNames->toArray());
        }

        $huntAnalytics = $query->get();

        $updatedCount = 0;

        foreach ($huntAnalytics as $analytic) {
            $huntId = $this->extractHuntId($analytic->name);

            if ($huntId === null) {
                continue;
            }

            $updated = $this->updateHuntViews($huntId, $analytic);

            if ($updated !== 0) {
                $updatedCount++;
            }
        }

        return $updatedCount;
    }

    /**
     * Extract hunt ID from Pan analytics name.
     */
    private function extractHuntId(string $name): ?int
    {
        $huntId = str_replace('hunt-', '', $name);

        if (! is_numeric($huntId)) {
            return null;
        }

        return (int) $huntId;
    }

    /**
     * Build query to fetch hunt analytics from Pan.
     */
    private function buildQuery(): Builder
    {

        return DB::table('pan_analytics')
            ->where('name', 'like', 'hunt-%');
    }

    /**
     * Update hunt views.
     */
    private function updateHuntViews(int $huntId, mixed $analytic): int
    {

        return Hunt::query()
            ->where('id', $huntId)
            ->update(['views_count' => $analytic->impressions]);
    }

    /**
     * Check if the action should update specific hunts.
     *
     * @param  Collection<int, int>|null  $huntIds
     */
    private function containsHuntIds(?Collection $huntIds): bool
    {

        return $huntIds instanceof Collection && $huntIds->isNotEmpty();
    }
}
