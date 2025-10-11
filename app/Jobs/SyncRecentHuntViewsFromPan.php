<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Hunt\SyncRecentHuntViewsAction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class SyncRecentHuntViewsFromPan implements ShouldQueue
{
    use Queueable;

    /**
     * Handle the job.
     * Syncs only hunts created in the last 24 hours for faster updates.
     */
    public function handle(SyncRecentHuntViewsAction $action): void
    {
        $action->handle();
    }
}
