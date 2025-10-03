<?php

declare(strict_types=1);

namespace App\Services\Search\Concerns;

use App\DataTransferObjects\Search\SearchResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Laravel\Scout\Builder;
use RuntimeException;

trait HasScoutSearch
{
    /**
     * Search for items matching the given query using Laravel Scout.
     *
     * @return Collection<int, SearchResult> Collection of search results
     */
    public function search(string $query, int $limit = 5): Collection
    {
        $results = $this->buildQuery($query, $limit)->get();

        $relations = $this->getRelations();
        if (! empty($relations)) {
            $results->load($relations);
        }

        return $results->map(fn ($model) => $this->mapToSearchResults($model));
    }

    /**
     * Get the redirect URL for a model, validating it's the correct type.
     *
     * @throws InvalidArgumentException If model is not the expected type
     */
    public function getRedirectUrl(Model $model): string
    {
        $expectedClass = $this->getModelClass();

        if (! $model instanceof $expectedClass) {
            throw new InvalidArgumentException(
                sprintf('Model must be an instance of %s, %s given', $expectedClass, $model::class)
            );
        }

        return $this->buildRedirectUrl($model);
    }

    /**
     * Build the Scout search query builder.
     *
     * @return Builder<Model> The Scout query builder
     */
    protected function buildQuery(string $query, int $limit): Builder
    {
        $modelClass = $this->getModelClass();
        $instance = resolve($modelClass);

        if (! is_object($instance) || ! method_exists($instance, 'search')) {
            throw new RuntimeException('Model class must have search method');
        }

        $builder = $instance::search($query);

        if (! $builder instanceof Builder) {
            throw new RuntimeException('search() must return Scout Builder instance');
        }

        return $builder->take($limit);
    }

    /**
     * Define relationships to eager load for search results.
     *
     * @return array<int, string> Array of relationship names to load
     */
    protected function getRelations(): array
    {
        return [];
    }
}
