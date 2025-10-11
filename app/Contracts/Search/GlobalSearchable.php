<?php

declare(strict_types=1);

namespace App\Contracts\Search;

use App\DataTransferObjects\Search\SearchResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface GlobalSearchable
{
    /**
     * Get the search type identifier.
     *
     * This identifier is used to group results by type in the search response.
     * Should be unique across all search providers.
     */
    public function getType(): string;

    /**
     * Get the display label for this search type.
     *
     * This label is shown in the search UI to categorize results.
     */
    public function getLabel(): string;

    /**
     * Get the icon for this search type.
     *
     * The icon is displayed next to the search group in the UI.
     */
    public function getIcon(): string;

    /**
     * Get the redirect URL for a specific result.
     *
     * Generates the URL where users should be redirected when clicking
     * on a search result. The implementation should validate the model type.
     */
    public function getRedirectUrl(Model $model): string;

    /**
     * Search for items matching the query.
     *
     * Executes the search using the provider's search engine (typically Scout)
     * and returns results as SearchResultDto instances.
     *
     * @return Collection<int, SearchResult>
     */
    public function search(string $query, int $limit = 5): Collection;

    /**
     * Get the order/priority for displaying results.
     *
     * Lower values have higher priority and appear first in search results.
     * Use this to control the order of result groups in the UI.
     */
    public function getPriority(): int;

    /**
     * Get the model class that this provider searches.
     *
     * Returns the fully qualified class name of the Eloquent model
     * that this search provider operates on.
     */
    public function getModelClass(): string;

    /**
     * Transform a model instance into a SearchResultDto.
     *
     * Maps the model's attributes to a standardized search result format.
     * This method is called for each search result to prepare it for the UI.
     */
    public function mapToSearchResults(Model $model): SearchResult;

    /**
     * Generate the redirect URL for a specific model instance.
     */
    public function buildRedirectUrl(Model $model): string;
}
