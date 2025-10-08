<?php

declare(strict_types=1);

namespace App\Services\Metrics;

use App\Contracts\Metrics\MetricsPipelineContract;
use App\DataTransferObjects\Metrics\HuntMetrics;
use App\DataTransferObjects\Metrics\MetricsContext;
use App\Models\Hunt;
use App\Pipes\Metrics\EngagementMetricsPipe;
use App\Pipes\Metrics\QualityMetricsPipe;
use App\Pipes\Metrics\ReachMetricsPipe;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pipeline\Pipeline;
use InvalidArgumentException;

/**
 * Metrics Pipeline - Processes metrics through stages.
 * Uses Laravel Pipeline for clean, extensible metric calculation.
 */
final readonly class MetricsPipeline implements MetricsPipelineContract
{
    /**
     * Create a new metrics pipeline instance.
     */
    public function __construct(
        private Pipeline $pipeline,
    ) {}

    /**
     * Process model through metrics pipeline.
     */
    public function process(Model $model): HuntMetrics
    {
        if (! $model instanceof Hunt) {
            throw new InvalidArgumentException('Model must be an instance of Hunt');
        }

        $context = $this->createContext($model);

        /** @var MetricsContext $processedContext */
        $processedContext = $this->pipeline
            ->send($context)
            ->through($this->getStages())
            ->thenReturn();

        return $this->buildHuntMetrics($processedContext);
    }

    /**
     * Get pipeline stages (calculators).
     *
     * @return array<class-string>
     */
    public function getStages(): array
    {
        return [
            ReachMetricsPipe::class,
            EngagementMetricsPipe::class,
            QualityMetricsPipe::class,
        ];
    }

    /**
     * Create initial metrics context from model.
     */
    private function createContext(Hunt $hunt): MetricsContext
    {
        return new MetricsContext(
            model: $hunt,
            views: $hunt->views_count ?? 0,
            likes: $hunt->likesCount(),
            comments: $hunt->comments()->count(),
            shares: $hunt->shares_count ?? 0,
        );
    }

    /**
     * Build HuntMetrics DTO from processed context.
     */
    private function buildHuntMetrics(MetricsContext $context): HuntMetrics
    {
        return new HuntMetrics(
            views: $context->views,
            likes: $context->likes,
            comments: $context->comments,
            shares: $context->shares,
            engagementRate: (float) $context->get('engagement_rate', 0.0),
            interactionRate: (float) $context->get('interaction_rate', 0.0),
            commentRate: (float) $context->get('comment_rate', 0.0),
            shareRate: (float) $context->get('share_rate', 0.0),
            qualityScore: (float) $context->get('quality_score', 0.0),
            viralityCoefficient: (float) $context->get('virality_coefficient', 0.0),
            avgEngagementPerView: (float) $context->get('avg_engagement_per_view', 0.0),
            performanceLevel: (string) $context->get('performance_level', 'poor'),
            rank: (int) $context->get('rank', 1),
        );
    }
}
