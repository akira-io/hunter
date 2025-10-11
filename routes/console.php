<?php

declare(strict_types=1);

use App\Jobs\SyncHuntViewsFromPan;
use App\Jobs\SyncRecentHuntViewsFromPan;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('horizon:snapshot')->everyFiveMinutes();

// Recent hunts (< 24h): Every 5 minutes - users are actively watching
Schedule::job(new SyncRecentHuntViewsFromPan)->everyFiveMinutes();

// All hunts: Every hour - for complete sync and old hunts
Schedule::job(new SyncHuntViewsFromPan)->hourly();
