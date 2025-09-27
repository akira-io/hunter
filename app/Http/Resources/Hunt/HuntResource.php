<?php

declare(strict_types=1);

namespace App\Http\Resources\Hunt;

use App\Http\Resources\Commentable\CommentResource;
use App\Models\Comment;
use App\Models\Hunt;
use App\Models\User;
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
        return [
            'id' => $this->id,
            'content' => $this->content,
            'is_reported' => $this->is_reported,
            'is_pinned' => $this->is_pinned,
            'is_ignored' => $this->is_ignored,
            'created_at' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at->diffForHumans(),
            'owner' => HuntOwnerResource::make($this->owner),
            'comments' => CommentResource::collection($this->commentsWithHasLiked()),
            'likes_count' => $this->likesCount(),
            'views' => 0,
            'shares' => 0,
            'has_liked' => $this->has_liked,
            'image_url' => $this->getFirstMediaUrl('hunts'),
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

        /** @var \Illuminate\Database\Eloquent\Builder<Comment> $builder */
        $builder = $this->comments()->orderByDesc('created_at');

        if ($user === null) {
            /** @var \Illuminate\Database\Eloquent\Collection<int, Comment> $commentsCollection */
            $commentsCollection = $builder->get();
            $commentsCollection->each(function (Comment $comment): void {
                $comment->has_liked = false;
            });
            /** @var \Illuminate\Support\Collection<int, Comment> $comments */
            $comments = $commentsCollection;
            return $comments;
        }

        $builderWithCount = $builder
            ->withCount([
                'likes as has_liked' => function ($q) use ($user): void {
                    if (is_object($q) && method_exists($q, 'where')) {
                        $q->where('user_id', $user->getAttribute('id'));
                    }
                },
            ]);

        /** @var \Illuminate\Database\Eloquent\Collection<int, Comment> $commentsWithCount */
        $commentsWithCount = $builderWithCount->get();
        $commentsWithCount->each(function (Comment $comment): void {
            $comment->has_liked = (bool) $comment->has_liked;
        });

        /** @var \Illuminate\Support\Collection<int, Comment> $comments */
        $comments = $commentsWithCount;

        return $comments;
    }
}
