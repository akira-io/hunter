<?php

declare(strict_types=1);

namespace App\Contracts\Metrics;

use App\DataTransferObjects\Metrics\MetricsContext;
use Closure;

/**
 * Contract for metrics calculator pipeline stages.
 * Each calculator processes the context and passes it to the next stage.
 */
interface MetricsPipeContract
{
    /**
     * Calculate metrics and enrich the context.
     * This method follows the Pipeline pattern.
     *
     * @param  Closure(MetricsContext): MetricsContext  $next
     */
    public function handle(MetricsContext $context, Closure $next): MetricsContext;
}
