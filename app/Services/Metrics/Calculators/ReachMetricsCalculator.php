<?php

declare(strict_types=1);

namespace App\Services\Metrics\Calculators;

use App\Contracts\Metrics\ReachMetricsCalculatorInterface;

final readonly class ReachMetricsCalculator implements ReachMetricsCalculatorInterface
{
    /**
     * Calculate reach metrics.
     *
     * @return array{views: int, impressions: int, reach_score: float}
     */
    public function calculate(int $views): array
    {
        return [
            'views' => $views,
            'impressions' => $views, // Alias for compatibility
            'reach_score' => $this->calculateReachScore($views),
        ];
    }

    /**
     * Calculate reach score (0-100).
     * Based on logarithmic scale to reward high reach.
     */
    private function calculateReachScore(int $views): float
    {
        if ($views === 0) {
            return 0.0;
        }

        // Logarithmic scoring: log10(views) * 10
        // 10 views = 10, 100 views = 20, 1000 views = 30, etc.
        return min(100.0, round(log10($views) * 10, 2));
    }
}
