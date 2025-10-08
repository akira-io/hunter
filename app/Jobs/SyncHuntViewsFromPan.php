<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Hunt\SyncAllHuntViewsAction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class SyncHuntViewsFromPan implements ShouldQueue
{
    use Queueable;

    /**
     * Handle the job.
     */
    public function handle(SyncAllHuntViewsAction $action): void
    {
        $action->handle();
    }
}
