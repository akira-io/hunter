<?php

declare(strict_types=1);

namespace App\Services\Metrics\Calculators;

use App\Contracts\Metrics\MetricsCalculable;
use App\DataTransferObjects\Metrics\HuntMetrics;
use App\DataTransferObjects\Metrics\MetricsData;
use App\Models\Hunt;
use App\Services\Metrics\MetricsPipeline;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final readonly class HuntMetricsCalculator implements MetricsCalculable
{
    /**
     * Create a new hunt metrics calculator instance.
     */
    public function __construct(
        private MetricsPipeline $pipeline,
    ) {}

    /**
     * Get the metrics type identifier.
     */
    public function getType(): string
    {
        return 'hunt';
    }

    /**
     * Get the model class that this calculator operates on.
     */
    public function getModelClass(): string
    {
        return Hunt::class;
    }

    /**
     * Check if this calculator supports the given model.
     */
    public function supports(Model $model): bool
    {
        return $model instanceof Hunt;
    }

    /**
     * Calculate comprehensive metrics for a hunt using pipeline.
     */
    public function calculate(Model $model): MetricsData
    {
        if (! $this->supports($model)) {
            throw new InvalidArgumentException('Model must be an instance of Hunt');
        }

        $huntMetrics = $this->pipeline->process($model);

        return new MetricsData(
            type: $this->getType(),
            metrics: $huntMetrics->toArray(),
        );
    }

    /**
     * Calculate metrics and return HuntMetrics DTO directly.
     * Recommended method for type-safe access.
     */
    public function calculateDetailed(Hunt $hunt): HuntMetrics
    {
        return $this->pipeline->process($hunt);
    }
}
