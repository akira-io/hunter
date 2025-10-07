<?php

declare(strict_types=1);

namespace App\Http\Resources\Hunt;

use App\Http\Resources\Commentable\CommentResource;
use App\Models\Comment;
use App\Models\Hunt;
use App\Models\User;
use App\Services\Metrics\MetricsCalculatorService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

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
        /** @var MetricsCalculatorService $metricsService */
        $metricsService = app(MetricsCalculatorService::class);

        /** @var Hunt $hunt */
        $hunt = $this->resource;

        $metrics = $metricsService->calculate($hunt);

        /** @var User|null $user */
        $user = $request->user();

        $isOwner = $user instanceof User && $user->id === $this->owner_id;

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
            'likes_count' => (int) $metrics->get('likes'),
            'views' => $isOwner ? (int) $metrics->get('views') : null,
            'shares' => $isOwner ? (int) $metrics->get('shares') : null,
            'has_liked' => $this->has_liked ?? false,
            'image_url' => $this->getFirstMediaUrl('hunts'),
            'metrics' => $isOwner ? $metrics->toArray() : null,
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
