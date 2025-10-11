<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Metrics\EngagementMetricsCalculatorInterface;
use App\Contracts\Metrics\QualityMetricsCalculatorInterface;
use App\Contracts\Metrics\ReachMetricsCalculatorInterface;
use App\Services\Metrics\Calculators\EngagementMetricsCalculator;
use App\Services\Metrics\Calculators\QualityMetricsCalculator;
use App\Services\Metrics\Calculators\ReachMetricsCalculator;
use Illuminate\Support\ServiceProvider;

final class MetricsServiceProvider extends ServiceProvider
{
    /**
     * All the container bindings that should be registered.
     *
     * @var array<string, string>
     */
    public array $bindings = [
        EngagementMetricsCalculatorInterface::class => EngagementMetricsCalculator::class,
        QualityMetricsCalculatorInterface::class => QualityMetricsCalculator::class,
        ReachMetricsCalculatorInterface::class => ReachMetricsCalculator::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
