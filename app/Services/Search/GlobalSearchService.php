<?php

declare(strict_types=1);

namespace App\Services\Search;

use App\Contracts\Search\GlobalSearchable;
use App\DataTransferObjects\Search\GlobalSearchResponseDto;
use App\DataTransferObjects\Search\SearchGroupDto;
use App\Services\Search\Providers\HuntSearchProvider;
use App\Services\Search\Providers\UserSearchProvider;
use Illuminate\Container\Attributes\Singleton;

#[Singleton]
final readonly class GlobalSearchService
{
    public function __construct(
        private UserSearchProvider $userSearchProvider,
        private HuntSearchProvider $huntSearchProvider,
    ) {}

    /**
     * Search across all registered providers
     */
    public function search(string $query, int $limitPerProvider = 5): GlobalSearchResponseDto
    {
        if (empty($query) || mb_strlen($query) < 2) {
            return new GlobalSearchResponseDto(groups: []);
        }

        $providers = [
            $this->userSearchProvider,
            $this->huntSearchProvider,
            // Add more providers here - they will be auto-injected
        ];

        $groups = collect($providers)
            ->map(fn (GlobalSearchable $provider) => new SearchGroupDto(
                type: $provider->getType(),
                label: $provider->getLabel(),
                icon: $provider->getIcon(),
                results: $provider->search($query, $limitPerProvider),
                priority: $provider->getPriority(),
            ))
            ->filter(fn (SearchGroupDto $group) => $group->results->isNotEmpty())
            ->sortBy(fn (SearchGroupDto $group) => $group->priority)
            ->values()
            ->toArray();

        return new GlobalSearchResponseDto(groups: $groups);
    }
}
