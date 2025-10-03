<?php

declare(strict_types=1);

namespace App\Contracts\Search;

use App\DataTransferObjects\Search\SearchResultDto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface GlobalSearchable
{
    /**
     * Get the search type identifier
     */
    public function getType(): string;

    /**
     * Get the display label for this search type
     */
    public function getLabel(): string;

    /**
     * Get the icon for this search type
     */
    public function getIcon(): string;

    /**
     * Get the redirect URL for a specific result
     */
    public function getRedirectUrl(Model $model): string;

    /**
     * Search for items matching the query
     *
     * @return Collection<int, SearchResultDto>
     */
    public function search(string $query, int $limit = 5): Collection;

    /**
     * Get the order/priority for displaying results (lower = higher priority)
     */
    public function getPriority(): int;
}
