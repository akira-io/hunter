<?php

declare(strict_types=1);

namespace App\Services\Metrics;

use App\Contracts\Metrics\MetricsCalculable;
use App\DataTransferObjects\Metrics\MetricsData;
use App\Services\Metrics\Calculators\HuntMetricsCalculator;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

#[Singleton]
final readonly class MetricsCalculatorService
{
    /**
     * Create a new metrics calculator service instance.
     */
    public function __construct(
        private HuntMetricsCalculator $huntMetricsCalculator,
    ) {
        //
    }

    /**
     * Calculate metrics for a given model instance.
     *
     * @throws InvalidArgumentException When no calculator supports the model.
     */
    public function calculate(Model $model): MetricsData
    {
        $calculator = $this->getCalculatorForModel($model);

        if (! $calculator instanceof MetricsCalculable) {
            throw new InvalidArgumentException(
                sprintf('No metrics calculator found for model: %s', $model::class)
            );
        }

        return $calculator->calculate($model);
    }

    /**
     * Get all registered calculators.
     *
     * @return array<MetricsCalculable>
     */
    private function getCalculators(): array
    {
        return [
            $this->huntMetricsCalculator,
            // Add more calculators here - they will be auto-injected
        ];
    }

    /**
     * Find the calculator that supports the given model.
     */
    private function getCalculatorForModel(Model $model): ?MetricsCalculable
    {

        return array_find($this->getCalculators(), fn ($calculator): bool => $calculator->supports($model));
    }
}
