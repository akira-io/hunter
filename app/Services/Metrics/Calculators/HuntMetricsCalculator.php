<?php

declare(strict_types=1);

namespace App\Services\Metrics\Calculators;

use App\Contracts\Metrics\MetricsCalculable;
use App\DataTransferObjects\Metrics\MetricsData;
use App\Models\Hunt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use InvalidArgumentException;

final readonly class HuntMetricsCalculator implements MetricsCalculable
{
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
     * Calculate metrics for a given model instance.
     *
     * @throws InvalidArgumentException When the model is not supported.
     */
    public function calculate(Model $model): MetricsData
    {
        if (! $this->supports($model)) {
            throw new InvalidArgumentException('Model must be an instance of Hunt');
        }

        /** @var Hunt $hunt */
        $hunt = $model;

        $views = $hunt->views_count ?? 0;
        $likes = $hunt->likesCount();
        $comments = $hunt->comments()->count();
        $shares = $hunt->shares_count ?? 0;

        $totalEngagements = $this->engagements($likes, $comments, $shares);

        $interactions = $likes + $comments;

        return new MetricsData(
            type: $this->getType(),
            metrics: [
                'views' => $views,
                'likes' => $likes,
                'comments' => $comments,
                'shares' => $shares,
                'total_engagements' => $totalEngagements,
                'engagement_rate' => $this->engagementRate($views, $totalEngagements),
                'interaction_rate' => $this->engagementRate($views, $interactions),
                'avg_engagement_per_view' => $this->averageEngagementPerView($views, $totalEngagements),
            ],
        );
    }

    /**
     * Calculate total engagements.
     */
    private function engagements(MorphToMany|int $likes, int $comments, int $shares): int|float
    {

        return $likes + $comments + $shares;
    }

    /**
     * Calculate engagement rate as a percentage.
     */
    private function engagementRate(mixed $views, mixed $totalEngagements): float
    {

        return $views > 0
            ? round(($totalEngagements / $views) * 100, 2)
            : 0.0;
    }

    /**
     * Calculate average engagements per view.
     */
    private function averageEngagementPerView(int $views, float|int $totalEngagements): float
    {

        return $views > 0
            ? round($totalEngagements / $views, 2)
            : 0.0;
    }
}
