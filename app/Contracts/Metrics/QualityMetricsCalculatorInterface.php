<?php

declare(strict_types=1);

namespace App\Contracts\Metrics;

/**
 * Contract for calculating quality metrics.
 * Defines how content quality should be measured.
 */
interface QualityMetricsCalculatorInterface
{
    /**
     * Calculate quality metrics based on engagement data.
     *
     * @return array{
     *     quality_score: float,
     *     virality_coefficient: float,
     *     performance_level: string,
     *     rank: int
     * }
     */
    public function calculate(int $views, int $likes, int $comments, int $shares): array;
}
