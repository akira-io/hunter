<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Pan\PanAnalyticsService;
use Illuminate\Support\ServiceProvider;
use Pan\Contracts\AnalyticsRepository;
use Pan\PanConfiguration;

final class PanServiceProvider extends ServiceProvider
{
    /**
     * Register the application's Pan configuration.
     */
    public function register(): void
    {
        // Bind PanAnalyticsService as the AnalyticsRepository
        $this->app->singleton(AnalyticsRepository::class, PanAnalyticsService::class);

        // Configure allowed analytics with wildcard patterns (SECURE!)
        PanConfiguration::allowedAnalytics([
            'onbording-profile',
            'hunt-*', // Wildcard support via PanAnalyticsService
        ]);

        // Set reasonable max limit (not unlimited!)
        PanConfiguration::maxAnalytics(10000);
    }
}
