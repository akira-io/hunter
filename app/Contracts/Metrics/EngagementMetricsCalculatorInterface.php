<?php

declare(strict_types=1);

namespace App\Contracts\Metrics;

/**
 * Contract for calculating engagement metrics.
 * Defines how engagement rates should be calculated.
 */
interface EngagementMetricsCalculatorInterface
{
    /**
     * Calculate engagement metrics based on views and interactions.
     *
     * @return array{
     *     engagement_rate: float,
     *     interaction_rate: float,
     *     comment_rate: float,
     *     share_rate: float,
     *     avg_engagement_per_view: float
     * }
     */
    public function calculate(int $views, int $likes, int $comments, int $shares): array;
}
