<?php

declare(strict_types=1);

namespace App\Pipes\Metrics;

use App\Contracts\Metrics\MetricsPipeContract;
use App\DataTransferObjects\Metrics\MetricsContext;
use App\Services\Metrics\Calculators\EngagementMetricsCalculator;
use Closure;

/**
 * Calculate Engagement Metrics Action.
 * Pipeline stage: Calculates engagement rates.
 */
final readonly class EngagementMetricsPipe implements MetricsPipeContract
{
    /**
     * Generate a new instance of the pipe.
     */
    public function __construct(
        private EngagementMetricsCalculator $calculator,
    ) {}

    /**
     * Handle the pipeline stage.
     */
    public function handle(MetricsContext $context, Closure $next): MetricsContext
    {
        $engagementMetrics = $this->calculator->calculate(
            $context->views,
            $context->likes,
            $context->comments,
            $context->shares,
        );

        $enrichedContext = $context->withCalculated($engagementMetrics);

        return $next($enrichedContext);
    }
}
