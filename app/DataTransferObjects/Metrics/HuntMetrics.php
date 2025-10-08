<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Metrics;

/**
 * Hunt Metrics DTO - Comprehensive social media metrics.
 */
final readonly class HuntMetrics
{
    /**
     * Create a new HuntMetrics instance.
     */
    public function __construct(
        public int $views,
        public int $likes,
        public int $comments,
        public int $shares,

        public float $engagementRate,
        public float $interactionRate,
        public float $commentRate,
        public float $shareRate,

        public float $qualityScore,
        public float $viralityCoefficient,
        public float $avgEngagementPerView,

        public string $performanceLevel,
        public int $rank,
    ) {}

    /**
     * Get total engagements
     */
    public function getTotalEngagements(): int
    {
        return $this->likes + $this->comments + $this->shares;
    }

    /**
     * Get total interactions
     */
    public function getTotalInteractions(): int
    {
        return $this->likes + $this->comments;
    }

    /**
     * Check if this hunt is performing well.
     */
    public function isPerformingWell(): bool
    {
        return $this->qualityScore >= 50.0;
    }

    /**
     * Check if this hunt is going viral.
     */
    public function isViral(): bool
    {
        return $this->viralityCoefficient >= 0.1; // 10% share rate
    }

    /**
     * Convert to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'views' => $this->views,
            'likes' => $this->likes,
            'comments' => $this->comments,
            'shares' => $this->shares,
            'total_engagements' => $this->getTotalEngagements(),

            'engagement_rate' => $this->engagementRate,
            'interaction_rate' => $this->interactionRate,
            'comment_rate' => $this->commentRate,
            'share_rate' => $this->shareRate,

            'quality_score' => $this->qualityScore,
            'virality_coefficient' => $this->viralityCoefficient,
            'avg_engagement_per_view' => $this->avgEngagementPerView,

            'performance_level' => $this->performanceLevel,
            'rank' => $this->rank,
            'is_performing_well' => $this->isPerformingWell(),
            'is_viral' => $this->isViral(),
        ];
    }
}
