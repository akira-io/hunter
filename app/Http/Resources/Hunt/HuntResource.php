<?php

declare(strict_types=1);

namespace App\Http\Resources\Hunt;

use App\Http\Resources\Commentable\CommentResource;
use App\Models\Comment;
use App\Models\Hunt;
use App\Models\User;
use App\Services\Metrics\Calculators\HuntMetricsCalculator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;

/** @mixin Hunt */
final class HuntResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var HuntMetricsCalculator $calculator */
        $calculator = app(HuntMetricsCalculator::class);

        /** @var Hunt $hunt */
        $hunt = $this->resource;

        $huntMetrics = $calculator->calculateDetailed($hunt);

        /** @var User|null $user */
        $user = $request->user();

        $isOwner = $user instanceof User && $user->id === $this->owner_id;

        $publicMetrics = [
            'views' => Number::abbreviate($huntMetrics->views),
            'likes' => Number::abbreviate($huntMetrics->likes),
            'comments' => Number::abbreviate($huntMetrics->comments),
            'shares' => Number::abbreviate($huntMetrics->shares),
        ];

        $advancedMetrics = $isOwner ? [
            'views' => Number::abbreviate($huntMetrics->views),
            'likes' => Number::abbreviate($huntMetrics->likes),
            'comments' => Number::abbreviate($huntMetrics->comments),
            'shares' => Number::abbreviate($huntMetrics->shares),
            'total_engagements' => $huntMetrics->getTotalEngagements(),

            'engagement_rate' => $huntMetrics->engagementRate,
            'interaction_rate' => $huntMetrics->interactionRate,
            'comment_rate' => $huntMetrics->commentRate,
            'share_rate' => $huntMetrics->shareRate,
            'quality_score' => $huntMetrics->qualityScore,
            'virality_coefficient' => $huntMetrics->viralityCoefficient,
            'avg_engagement_per_view' => $huntMetrics->avgEngagementPerView,
            'performance_level' => $huntMetrics->performanceLevel,
            'rank' => $huntMetrics->rank,
            'is_viral' => $huntMetrics->isViral(),
            'is_performing_well' => $huntMetrics->isPerformingWell(),
        ] : null;

        return [
            'id' => $this->id,
            'content' => $this->content,
            'is_reported' => $this->is_reported,
            'is_pinned' => $this->is_pinned,
            'is_ignored' => $this->is_ignored,
            'created_at' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at->diffForHumans(),
            'owner' => HuntOwnerResource::make($this->owner)->resolve(),
            'comments' => CommentResource::collection($this->commentsWithHasLiked())->resolve(),
            'likes_count' => (int) $publicMetrics['likes'],
            'views' => (int) $publicMetrics['views'],
            'shares' => (int) $publicMetrics['shares'],
            'has_liked' => $this->has_liked ?? false,
            'image_url' => $this->getFirstMediaUrl('hunts'),
            'metrics' => $advancedMetrics,
            'can_comment' => $user instanceof User && $this->owner->canReceiveCommentsFrom($user),
            'is_owner' => $isOwner,
        ];

    }

    /**
     * Get the comments with the has_liked status.
     *
     * @return Collection<int, Comment>
     */
    public function commentsWithHasLiked(): Collection
    {
        /** @var User|null $user */
        $user = request()->user();

        /** @var Builder<Comment> $builder */
        $builder = $this->comments()->orderByDesc('created_at');

        if ($user === null) {
            /** @var EloquentCollection<int, Comment> $commentsCollection */
            $commentsCollection = $builder->get();
            $commentsCollection->each(function (Comment $comment): void {
                $comment->has_liked = false;
            });
            /** @var Collection<int, Comment> $comments */
            $comments = $commentsCollection;

            return $comments;
        }

        /** @var Builder<Comment> $builderWithCount */
        $builderWithCount = $builder
            ->withCount([
                'likes as has_liked' => function (Builder $q) use ($user): void {
                    $q->where('user_id', $user->getAttribute('id'));
                },
            ]);

        /** @var EloquentCollection<int, Comment> $commentsWithCount */
        $commentsWithCount = $builderWithCount->get();
        $commentsWithCount->each(function (Comment $comment): void {
            $comment->has_liked = (bool) $comment->has_liked;
        });

        /** @var Collection<int, Comment> $comments */
        $comments = $commentsWithCount;

        return $comments;
    }
}
