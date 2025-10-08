<?php

declare(strict_types=1);

namespace App\Pipes\Metrics;

use App\Contracts\Metrics\MetricsPipeContract;
use App\DataTransferObjects\Metrics\MetricsContext;
use App\Services\Metrics\Calculators\ReachMetricsCalculator;
use Closure;

/**
 * Calculate Reach Metrics Action.
 * Pipeline stage: Calculates reach-related metrics.
 */
final readonly class ReachMetricsPipe implements MetricsPipeContract
{
    /**
     * Generate a new instance of the pipe.
     */
    public function __construct(
        private ReachMetricsCalculator $calculator,
    ) {}

    /**
     * Handle the pipeline stage.
     */
    public function handle(MetricsContext $context, Closure $next): MetricsContext
    {
        // Calculate reach metrics
        $reachMetrics = $this->calculator->calculate($context->views);

        // Enrich context with calculated metrics
        $enrichedContext = $context->withCalculated($reachMetrics);

        // Pass to next stage
        return $next($enrichedContext);
    }
}
