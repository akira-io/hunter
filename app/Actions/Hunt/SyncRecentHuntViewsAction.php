<?php

declare(strict_types=1);

namespace App\Actions\Hunt;

use Illuminate\Support\Facades\Log;

final readonly class SyncRecentHuntViewsAction
{
    /**
     * Create a new action instance.
     */
    public function __construct(
        private GetRecentHuntIdsAction $getRecentHuntIds,
        private UpdateHuntViewsFromPanAction $updateHuntViews,
    ) {}

    /**
     * Handle the action.
     */
    public function handle(): int
    {
        Log::info('Starting RECENT hunt views sync from Pan analytics');

        $recentHuntIds = $this->getRecentHuntIds->handle();

        if ($recentHuntIds->isEmpty()) {
            Log::info('No recent hunts to sync');

            return 0;
        }

        $syncedCount = $this->updateHuntViews->handle($recentHuntIds);

        Log::info("Recent hunt views sync completed. Synced {$syncedCount} recent hunts.");

        return $syncedCount;
    }
}
