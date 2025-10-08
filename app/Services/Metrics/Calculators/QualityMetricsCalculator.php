<?php

declare(strict_types=1);

namespace App\Services\Metrics\Calculators;

use App\Contracts\Metrics\QualityMetricsCalculatorInterface;

final readonly class QualityMetricsCalculator implements QualityMetricsCalculatorInterface
{
    private const int WEIGHT_VIEW = 1;

    private const int WEIGHT_LIKE = 3;

    private const int WEIGHT_COMMENT = 5;

    private const int WEIGHT_SHARE = 10;

    /**
     * Calculate quality metrics.
     *
     * @return array{
     *     quality_score: float,
     *     virality_coefficient: float,
     *     performance_level: string,
     *     rank: int
     * }
     */
    public function calculate(int $views, int $likes, int $comments, int $shares): array
    {
        $qualityScore = $this->calculateQualityScore($views, $likes, $comments, $shares);
        $viralityCoefficient = $this->calculateViralityCoefficient($views, $shares);

        return [
            'quality_score' => $qualityScore,
            'virality_coefficient' => $viralityCoefficient,
            'performance_level' => $this->getPerformanceLevel($qualityScore),
            'rank' => $this->calculateRank($qualityScore),
        ];
    }

    /**
     * Calculate quality score (0-100).
     * Uses weighted scoring where comments > likes > views.
     */
    private function calculateQualityScore(int $views, int $likes, int $comments, int $shares): float
    {
        if ($views === 0) {
            return 0.0;
        }

        $weightedScore = ($views * self::WEIGHT_VIEW)
            + ($likes * self::WEIGHT_LIKE)
            + ($comments * self::WEIGHT_COMMENT)
            + ($shares * self::WEIGHT_SHARE);

        // Normalize to 0-100 scale
        // Max realistic score: 1000 views + 300 likes + 100 comments + 20 shares = 4900
        $maxPossibleScore = ($views * self::WEIGHT_SHARE); // If all views became shares
        $normalizedScore = ($weightedScore / max($maxPossibleScore, 1)) * 100;

        return min(100.0, round($normalizedScore, 2));
    }

    /**
     * Calculate virality coefficient.
     * How likely content is to be shared (0-1 scale).
     */
    private function calculateViralityCoefficient(int $views, int $shares): float
    {
        if ($views === 0) {
            return 0.0;
        }

        return round($shares / $views, 3);
    }

    /**
     * Get performance level based on quality score.
     */
    private function getPerformanceLevel(float $qualityScore): string
    {
        return match (true) {
            $qualityScore >= 80 => 'excellent',
            $qualityScore >= 60 => 'good',
            $qualityScore >= 40 => 'average',
            $qualityScore >= 20 => 'below_average',
            default => 'poor',
        };
    }

    /**
     * Calculate rank (1-5 stars).
     */
    private function calculateRank(float $qualityScore): int
    {
        return match (true) {
            $qualityScore >= 80 => 5,
            $qualityScore >= 60 => 4,
            $qualityScore >= 40 => 3,
            $qualityScore >= 20 => 2,
            default => 1,
        };
    }
}
