<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Metrics;

use Illuminate\Database\Eloquent\Model;

/**
 * Metrics Context - Carries data through the pipeline.
 * Immutable object that gets enriched at each stage.
 */
final readonly class MetricsContext
{
    /**
     * Create a new metrics context.
     *
     * @param  array<string, mixed>  $calculated
     */
    public function __construct(
        public Model $model,
        public int $views,
        public int $likes,
        public int $comments,
        public int $shares,
        public array $calculated = [],
    ) {}

    /**
     * Add calculated metrics to context (immutable).
     *
     * @param  array<string, mixed>  $metrics
     */
    public function withCalculated(array $metrics): self
    {
        return new self(
            model: $this->model,
            views: $this->views,
            likes: $this->likes,
            comments: $this->comments,
            shares: $this->shares,
            calculated: array_merge($this->calculated, $metrics),
        );
    }

    /**
     * Get a calculated metric value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->calculated[$key] ?? $default;
    }

    /**
     * Check if a metric has been calculated.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->calculated);
    }
}
