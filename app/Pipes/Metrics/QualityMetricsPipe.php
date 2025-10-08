<?php

declare(strict_types=1);

namespace App\Pipes\Metrics;

use App\Contracts\Metrics\MetricsPipeContract;
use App\DataTransferObjects\Metrics\MetricsContext;
use App\Services\Metrics\Calculators\QualityMetricsCalculator;
use Closure;

/**
 * Calculate Quality Metrics Action.
 * Pipeline stage: Calculates quality score and virality.
 */
final readonly class QualityMetricsPipe implements MetricsPipeContract
{
    /**
     * Generate a new instance of the pipe.
     */
    public function __construct(
        private QualityMetricsCalculator $calculator,
    ) {}

    /**
     * Handle the pipeline stage.
     */
    public function handle(MetricsContext $context, Closure $next): MetricsContext
    {

        $qualityMetrics = $this->calculator->calculate(
            $context->views,
            $context->likes,
            $context->comments,
            $context->shares,
        );

        $enrichedContext = $context->withCalculated($qualityMetrics);

        return $next($enrichedContext);
    }
}
