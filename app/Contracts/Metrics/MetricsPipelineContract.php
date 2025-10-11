<?php

declare(strict_types=1);

namespace App\Contracts\Metrics;

use App\DataTransferObjects\Metrics\HuntMetrics;
use Illuminate\Database\Eloquent\Model;

/**
 * Contract for metrics pipeline orchestrator.
 * Processes metrics through a series of calculators.
 */
interface MetricsPipelineContract
{
    /**
     * Process metrics through the pipeline.
     * Returns complete metrics after all stages.
     */
    public function process(Model $model): HuntMetrics;

    /**
     * Get the pipeline stages (calculators).
     *
     * @return array<class-string<MetricsPipeContract>>
     */
    public function getStages(): array;
}
