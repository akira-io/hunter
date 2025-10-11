<?php

declare(strict_types=1);

namespace Tests\Unit\DataTransferObjects\Metrics;

use App\DataTransferObjects\Metrics\HuntMetrics;
use Tests\TestCase;

final class HuntMetricsTest extends TestCase
{
    public function test_creates_hunt_metrics_with_all_properties(): void
    {
        $metrics = new HuntMetrics(
            views: 1000,
            likes: 100,
            comments: 50,
            shares: 25,
            engagementRate: 17.5,
            interactionRate: 15.0,
            commentRate: 5.0,
            shareRate: 2.5,
            qualityScore: 75.5,
            viralityCoefficient: 0.15,
            avgEngagementPerView: 0.175,
            performanceLevel: 'excellent',
            rank: 5
        );

        $this->assertEquals(1000, $metrics->views);
        $this->assertEquals(100, $metrics->likes);
        $this->assertEquals(50, $metrics->comments);
        $this->assertEquals(25, $metrics->shares);
        $this->assertEquals(17.5, $metrics->engagementRate);
        $this->assertEquals(75.5, $metrics->qualityScore);
        $this->assertEquals('excellent', $metrics->performanceLevel);
    }

    public function test_get_total_engagements_sums_all_interactions(): void
    {
        $metrics = new HuntMetrics(
            views: 1000,
            likes: 100,
            comments: 50,
            shares: 25,
            engagementRate: 17.5,
            interactionRate: 15.0,
            commentRate: 5.0,
            shareRate: 2.5,
            qualityScore: 75.5,
            viralityCoefficient: 0.15,
            avgEngagementPerView: 0.175,
            performanceLevel: 'excellent',
            rank: 5
        );

        $this->assertEquals(175, $metrics->getTotalEngagements()); // 100 + 50 + 25
    }

    public function test_get_total_interactions_excludes_shares(): void
    {
        $metrics = new HuntMetrics(
            views: 1000,
            likes: 100,
            comments: 50,
            shares: 25,
            engagementRate: 17.5,
            interactionRate: 15.0,
            commentRate: 5.0,
            shareRate: 2.5,
            qualityScore: 75.5,
            viralityCoefficient: 0.15,
            avgEngagementPerView: 0.175,
            performanceLevel: 'excellent',
            rank: 5
        );

        $this->assertEquals(150, $metrics->getTotalInteractions()); // 100 + 50 (no shares)
    }

    public function test_is_performing_well_returns_true_when_quality_score_above_50(): void
    {
        $metrics = new HuntMetrics(
            views: 1000,
            likes: 100,
            comments: 50,
            shares: 25,
            engagementRate: 17.5,
            interactionRate: 15.0,
            commentRate: 5.0,
            shareRate: 2.5,
            qualityScore: 75.0,
            viralityCoefficient: 0.15,
            avgEngagementPerView: 0.175,
            performanceLevel: 'excellent',
            rank: 5
        );

        $this->assertTrue($metrics->isPerformingWell());
    }

    public function test_is_performing_well_returns_true_when_quality_score_equals_50(): void
    {
        $metrics = new HuntMetrics(
            views: 1000,
            likes: 100,
            comments: 50,
            shares: 25,
            engagementRate: 17.5,
            interactionRate: 15.0,
            commentRate: 5.0,
            shareRate: 2.5,
            qualityScore: 50.0,
            viralityCoefficient: 0.15,
            avgEngagementPerView: 0.175,
            performanceLevel: 'good',
            rank: 10
        );

        $this->assertTrue($metrics->isPerformingWell());
    }

    public function test_is_performing_well_returns_false_when_quality_score_below_50(): void
    {
        $metrics = new HuntMetrics(
            views: 100,
            likes: 10,
            comments: 5,
            shares: 2,
            engagementRate: 17.0,
            interactionRate: 15.0,
            commentRate: 5.0,
            shareRate: 2.0,
            qualityScore: 35.0,
            viralityCoefficient: 0.05,
            avgEngagementPerView: 0.17,
            performanceLevel: 'average',
            rank: 25
        );

        $this->assertFalse($metrics->isPerformingWell());
    }

    public function test_is_viral_returns_true_when_virality_coefficient_above_threshold(): void
    {
        $metrics = new HuntMetrics(
            views: 1000,
            likes: 100,
            comments: 50,
            shares: 150, // High shares
            engagementRate: 30.0,
            interactionRate: 15.0,
            commentRate: 5.0,
            shareRate: 15.0,
            qualityScore: 75.0,
            viralityCoefficient: 0.15, // >= 0.1 (10%)
            avgEngagementPerView: 0.30,
            performanceLevel: 'viral',
            rank: 1
        );

        $this->assertTrue($metrics->isViral());
    }

    public function test_is_viral_returns_false_when_virality_coefficient_below_threshold(): void
    {
        $metrics = new HuntMetrics(
            views: 1000,
            likes: 100,
            comments: 50,
            shares: 25,
            engagementRate: 17.5,
            interactionRate: 15.0,
            commentRate: 5.0,
            shareRate: 2.5,
            qualityScore: 75.0,
            viralityCoefficient: 0.05, // < 0.1 (10%)
            avgEngagementPerView: 0.175,
            performanceLevel: 'excellent',
            rank: 5
        );

        $this->assertFalse($metrics->isViral());
    }

    public function test_to_array_includes_all_metrics(): void
    {
        $metrics = new HuntMetrics(
            views: 1000,
            likes: 100,
            comments: 50,
            shares: 25,
            engagementRate: 17.5,
            interactionRate: 15.0,
            commentRate: 5.0,
            shareRate: 2.5,
            qualityScore: 75.0,
            viralityCoefficient: 0.15,
            avgEngagementPerView: 0.175,
            performanceLevel: 'excellent',
            rank: 5
        );

        $array = $metrics->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('views', $array);
        $this->assertArrayHasKey('likes', $array);
        $this->assertArrayHasKey('comments', $array);
        $this->assertArrayHasKey('shares', $array);
        $this->assertArrayHasKey('total_engagements', $array);
        $this->assertArrayHasKey('engagement_rate', $array);
        $this->assertArrayHasKey('interaction_rate', $array);
        $this->assertArrayHasKey('comment_rate', $array);
        $this->assertArrayHasKey('share_rate', $array);
        $this->assertArrayHasKey('quality_score', $array);
        $this->assertArrayHasKey('virality_coefficient', $array);
        $this->assertArrayHasKey('avg_engagement_per_view', $array);
        $this->assertArrayHasKey('performance_level', $array);
        $this->assertArrayHasKey('rank', $array);
        $this->assertArrayHasKey('is_performing_well', $array);
        $this->assertArrayHasKey('is_viral', $array);

        $this->assertEquals(1000, $array['views']);
        $this->assertEquals(175, $array['total_engagements']);
        $this->assertTrue($array['is_performing_well']);
        $this->assertTrue($array['is_viral']);
    }

    public function test_to_array_with_non_viral_low_performing_metrics(): void
    {
        $metrics = new HuntMetrics(
            views: 50,
            likes: 5,
            comments: 2,
            shares: 1,
            engagementRate: 16.0,
            interactionRate: 14.0,
            commentRate: 4.0,
            shareRate: 2.0,
            qualityScore: 20.0, // Below 50
            viralityCoefficient: 0.02, // Below 0.1
            avgEngagementPerView: 0.16,
            performanceLevel: 'poor',
            rank: 100
        );

        $array = $metrics->toArray();

        $this->assertFalse($array['is_performing_well']);
        $this->assertFalse($array['is_viral']);
        $this->assertEquals('poor', $array['performance_level']);
        $this->assertEquals(100, $array['rank']);
    }

    public function test_handles_zero_values(): void
    {
        $metrics = new HuntMetrics(
            views: 0,
            likes: 0,
            comments: 0,
            shares: 0,
            engagementRate: 0.0,
            interactionRate: 0.0,
            commentRate: 0.0,
            shareRate: 0.0,
            qualityScore: 0.0,
            viralityCoefficient: 0.0,
            avgEngagementPerView: 0.0,
            performanceLevel: 'none',
            rank: 0
        );

        $this->assertEquals(0, $metrics->getTotalEngagements());
        $this->assertEquals(0, $metrics->getTotalInteractions());
        $this->assertFalse($metrics->isPerformingWell());
        $this->assertFalse($metrics->isViral());
    }
}
