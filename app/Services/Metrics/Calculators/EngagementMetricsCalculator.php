<?php

declare(strict_types=1);

namespace App\Services\Metrics\Calculators;

use App\Contracts\Metrics\EngagementMetricsCalculatorInterface;

final readonly class EngagementMetricsCalculator implements EngagementMetricsCalculatorInterface
{
    /**
     * Calculate engagement metrics.
     *
     * @return array{
     *     engagement_rate: float,
     *     interaction_rate: float,
     *     comment_rate: float,
     *     share_rate: float,
     *     avg_engagement_per_view: float
     * }
     */
    public function calculate(int $views, int $likes, int $comments, int $shares): array
    {
        $totalEngagements = $likes + $comments + $shares;
        $totalInteractions = $likes + $comments;

        return [
            'engagement_rate' => $this->calculateRate($views, $totalEngagements),
            'interaction_rate' => $this->calculateRate($views, $totalInteractions),
            'comment_rate' => $this->calculateRate($views, $comments),
            'share_rate' => $this->calculateRate($views, $shares),
            'avg_engagement_per_view' => $this->averagePerView($views, $totalEngagements),
        ];
    }

    /**
     * Calculate rate as percentage.
     */
    private function calculateRate(int $views, int $count): float
    {
        if ($views === 0) {
            return 0.0;
        }

        return round(($count / $views) * 100, 2);
    }

    /**
     * Calculate average per view.
     */
    private function averagePerView(int $views, int $count): float
    {
        if ($views === 0) {
            return 0.0;
        }

        return round($count / $views, 3);
    }
}
