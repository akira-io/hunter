<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Metrics;

final readonly class MetricsData
{
    /**
     * Create a new metrics data instance.
     *
     * @param  array<string, int|float|string>  $metrics
     */
    public function __construct(
        public string $type,
        public array $metrics,
    ) {}

    /**
     * Get a specific metric value.
     */
    public function get(string $key, int|float|string $default = 0): int|float|string
    {
        return $this->metrics[$key] ?? $default;
    }

    /**
     * Convert to array.
     *
     * @return array{type: string, metrics: array<string, int|float|string>}
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'metrics' => $this->metrics,
        ];
    }
}
