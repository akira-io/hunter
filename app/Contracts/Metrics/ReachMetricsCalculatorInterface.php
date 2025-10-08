<?php

declare(strict_types=1);

namespace App\Contracts\Metrics;

/**
 * Contract for calculating reach metrics.
 * Defines how impressions and reach should be measured.
 */
interface ReachMetricsCalculatorInterface
{
    /**
     * Calculate reach metrics based on view count.
     *
     * @return array{views: int, impressions: int, reach_score: float}
     */
    public function calculate(int $views): array;
}
