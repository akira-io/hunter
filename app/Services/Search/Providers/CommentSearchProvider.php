<?php

declare(strict_types=1);

namespace App\Services\Search\Providers;

use App\Contracts\Search\GlobalSearchable;
use App\DataTransferObjects\Search\SearchResultDto;
use BadMethodCallException;
use Illuminate\Support\Collection;

/**
 * Example: Comment Search Provider
 *
 * To add this to global search:
 * 1. Enable Searchable trait on Comment model
 * 2. Configure scout index in config/scout.php
 * 3. Add to GlobalSearchServiceProvider->register():
 *    $app->make(CommentSearchProvider::class)
 *
 * That's it! No changes needed to controller or frontend!
 */
final readonly class CommentSearchProvider implements GlobalSearchable
{
    public function getType(): string
    {
        return 'comments';
    }

    public function getLabel(): string
    {
        return 'Comentários';
    }

    public function getIcon(): string
    {
        return 'message-circle';
    }

    public function getPriority(): int
    {
        return 3; // Lower priority than users and hunts
    }

    public function getRedirectUrl(mixed $model): string
    {
        // TODO: Implement when Comment model exists
        throw new BadMethodCallException('Not implemented yet');
        // Example implementation:
        /*
        if (! $model instanceof Comment) {
            throw new \InvalidArgumentException('Model must be an instance of Comment');
        }

        return $model->commentable->url . "#comment-{$model->id}";
        */
    }

    /**
     * @return Collection<int, SearchResultDto>
     */
    public function search(string $query, int $limit = 5): Collection
    {
        // TODO: Implement when Comment model has Searchable trait
        return collect([]);

        // Example implementation:
        /*
        return Comment::search($query)
            ->take($limit)
            ->get()
            ->load('user', 'commentable')
            ->map(fn (Comment $comment) => new SearchResultDto(
                id: (string) $comment->id,
                title: Str::limit($comment->content, 80),
                subtitle: "por {$comment->user->name}",
                description: "em {$comment->commentable->title}",
                image: $comment->user->avatar_url,
                url: $comment->commentable->url . "#comment-{$comment->id}",
                metadata: [
                    'user_id' => $comment->user->id,
                    'user_name' => $comment->user->name,
                    'created_at' => $comment->created_at->toIso8601String(),
                ],
            ));
        */
    }
}
