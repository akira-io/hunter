<?php

declare(strict_types=1);

namespace App\Services\Search\Providers;

use App\Contracts\Search\GlobalSearchable;
use App\DataTransferObjects\Search\SearchResultDto;
use App\Models\Hunt;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Support\Collection;
use InvalidArgumentException;

#[Singleton]
final readonly class HuntSearchProvider implements GlobalSearchable
{
    public function getType(): string
    {
        return 'hunts';
    }

    public function getLabel(): string
    {
        return 'Projetos';
    }

    public function getIcon(): string
    {
        return 'file-text';
    }

    public function getPriority(): int
    {
        return 2;
    }

    public function getRedirectUrl(mixed $model): string
    {
        if (! $model instanceof Hunt) {
            throw new InvalidArgumentException('Model must be an instance of Hunt');
        }

        return route('hunts.index')."#hunt-{$model->id}";
    }

    /**
     * @return Collection<int, SearchResultDto>
     */
    public function search(string $query, int $limit = 5): Collection
    {
        return Hunt::search($query)
            ->take($limit)
            ->get()
            ->load('owner')
            ->map(fn (Hunt $hunt) => new SearchResultDto(
                id: (string) $hunt->id,
                title: $hunt->content,
                subtitle: "por {$hunt->owner->name}",
                description: $hunt->created_at->diffForHumans(),
                image: $hunt->owner->avatar_url,
                url: $this->getRedirectUrl($hunt),
                metadata: [
                    'owner_id' => $hunt->owner->id,
                    'owner_name' => $hunt->owner->name,
                    'owner_username' => $hunt->owner->user_name,
                    'created_at' => $hunt->created_at->toIso8601String(),
                ],
            ));
    }
}
