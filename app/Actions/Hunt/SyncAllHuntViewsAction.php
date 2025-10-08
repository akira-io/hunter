<?php

declare(strict_types=1);

namespace App\Actions\Hunt;

use Illuminate\Support\Facades\Log;

final readonly class SyncAllHuntViewsAction
{
    /**
     * Create a new action instance.
     */
    public function __construct(private UpdateHuntViewsFromPanAction $updateHuntViews) {}

    /**
     * Handle the action.
     */
    public function handle(): int
    {
        Log::info('Starting hunt views sync from Pan analytics');

        $syncedCount = $this->updateHuntViews->handle();

        Log::info("Hunt views sync completed. Synced {$syncedCount} hunts.");

        return $syncedCount;
    }
}
