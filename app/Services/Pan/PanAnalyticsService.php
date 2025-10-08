<?php

declare(strict_types=1);

namespace App\Services\Pan;

use Illuminate\Support\Facades\DB;
use Pan\Contracts\AnalyticsRepository;
use Pan\Enums\EventType;
use Pan\PanConfiguration;
use Pan\ValueObjects\Analytic;

final readonly class PanAnalyticsService implements AnalyticsRepository
{
    /**
     * Create a new analytics service instance.
     */
    public function __construct(private PanConfiguration $config) {}

    /**
     * Returns all analytics.
     *
     * @return array<int, Analytic>
     */
    public function all(): array
    {
        /** @var array<int, Analytic> $all */
        $all = DB::table('pan_analytics')->get()->map(fn (mixed $analytic): Analytic => new Analytic(
            id: (int) $analytic->id,
            name: $analytic->name,
            impressions: (int) $analytic->impressions,
            hovers: (int) $analytic->hovers,
            clicks: (int) $analytic->clicks,
        ))->toArray();

        return $all;
    }

    /**
     * Increments the given event for the given analytic.
     * Supports wildcard patterns (e.g., 'hunt-*').
     */
    public function increment(string $name, EventType $event): void
    {
        [
            'allowed_analytics' => $allowedAnalytics,
            'max_analytics' => $maxAnalytics,
        ] = $this->config->toArray();

        if (count($allowedAnalytics) > 0 && ! $this->isAllowed($name, $allowedAnalytics)) {
            return;
        }

        if (DB::table('pan_analytics')->where('name', $name)->count() === 0) {
            if (DB::table('pan_analytics')->count() < $maxAnalytics) {
                DB::table('pan_analytics')->insert(['name' => $name, $event->column() => 1]);
            }

            return;
        }

        DB::table('pan_analytics')->where('name', $name)->increment($event->column());
    }

    /**
     * Flush all analytics.
     */
    public function flush(): void
    {
        DB::table('pan_analytics')->truncate();
    }

    /**
     * Check if a name is allowed based on exact match or wildcard patterns.
     *
     * @param  array<int, string>  $allowedAnalytics
     */
    private function isAllowed(string $name, array $allowedAnalytics): bool
    {
        foreach ($allowedAnalytics as $pattern) {
            // Exact match
            if ($pattern === $name) {
                return true;
            }

            // Wildcard pattern (e.g., 'hunt-*' matches 'hunt-123', 'hunt-456', etc.)
            if (str_ends_with($pattern, '*')) {
                $prefix = mb_substr($pattern, 0, -1); // Remove the '*'
                if (str_starts_with($name, $prefix)) {
                    return true;
                }
            }
        }

        return false;
    }
}
