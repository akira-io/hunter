<?php

declare(strict_types=1);

namespace App\Contracts\Metrics;

use App\DataTransferObjects\Metrics\MetricsData;
use Illuminate\Database\Eloquent\Model;

interface MetricsCalculable
{
    /**
     * Get the metrics type identifier.
     *
     * This identifier is used to identify the type of metrics being calculated.
     * Should be unique across all metrics calculators.
     */
    public function getType(): string;

    /**
     * Calculate metrics for a given model instance.
     *
     * Executes all necessary calculations and returns a standardized
     * metrics data object.
     */
    public function calculate(Model $model): MetricsData;

    /**
     * Get the model class that this calculator operates on.
     *
     * Returns the fully qualified class name of the Eloquent model
     * that this metrics calculator operates on.
     */
    public function getModelClass(): string;

    /**
     * Check if this calculator supports the given model.
     */
    public function supports(Model $model): bool;
}
