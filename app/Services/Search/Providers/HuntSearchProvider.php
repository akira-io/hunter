<?php

declare(strict_types=1);

namespace App\Services\Search\Providers;

use App\Contracts\Search\GlobalSearchable;
use App\DataTransferObjects\Search\SearchResult;
use App\Models\Hunt;
use App\Services\Search\Concerns\HasScoutSearch;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Database\Eloquent\Model;

#[Singleton]
final class HuntSearchProvider implements GlobalSearchable
{
    use HasScoutSearch;

    /**
     * Get the search type identifier.
     */
    public function getType(): string
    {
        return 'hunts';
    }

    /**
     * Get the display label for this search type.
     */
    public function getLabel(): string
    {
        return 'Hunts';
    }

    /**
     * Get the icon for this search type.
     */
    public function getIcon(): string
    {
        return 'file-text';
    }

    /**
     * Get the display priority for results.
     */
    public function getPriority(): int
    {
        return 2;
    }

    /**
     * Get the Hunt model class name.
     */
    public function getModelClass(): string
    {
        return Hunt::class;
    }

    /**
     * Transform a Hunt model into a search result DTO.
     */
    public function mapToSearchResults(Model $model): SearchResult
    {
        /** @var Hunt $model */
        return new SearchResult(
            id: (string) $model->id,
            title: $model->content,
            subtitle: "por {$model->owner->name}",
            description: $model->created_at->diffForHumans(),
            image: $model->owner->avatar_url,
            url: $this->getRedirectUrl($model),
            metadata: [
                'owner_id' => $model->owner->id,
                'owner_name' => $model->owner->name,
                'owner_username' => $model->owner->user_name,
                'created_at' => $model->created_at->toIso8601String(),
            ],
        );
    }

    /**
     * Generate the hunt detail URL.
     */
    public function buildRedirectUrl(Model $model): string
    {
        /** @var Hunt $model */
        return route('hunts.show', ['hunt' => $model->id]);
    }

    /**
     * Define relationships to an eager load for hunt results.
     *
     * @return array<int, string>
     */
    protected function getRelations(): array
    {
        return ['owner'];
    }

    /**
     * Determine if a hunt should be included in search results.
     * Only includes hunts from users who have enabled searchable in their privacy settings.
     */
    protected function shouldIncludeInResults(Model $model): bool
    {
        /** @var Hunt $model */
        return ($model->owner->privacy_settings['searchable'] ?? true) === true;
    }
}
