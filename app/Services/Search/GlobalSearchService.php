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
    /**
     * Create a new global search service instance.
     */
    public function __construct(
        private UserSearchProvider $userSearchProvider,
        private HuntSearchProvider $huntSearchProvider,
    ) {}

    /**
     * Search across all registered providers and group results by type.
     */
    public function search(string $query, int $limitPerProvider = 5): GlobalSearchResponseDto
    {
        if ($query === '' || $query === '0' || mb_strlen($query) < 2) {
            return new GlobalSearchResponseDto(groups: []);
        }

        $providers = [
            $this->userSearchProvider,
            $this->huntSearchProvider,
            // Add more providers here - they will be auto-injected
        ];

        $groups = collect($providers)
            ->map(fn (GlobalSearchable $provider): SearchGroupDto => new SearchGroupDto(
                type: $provider->getType(),
                label: $provider->getLabel(),
                icon: $provider->getIcon(),
                results: $provider->search($query, $limitPerProvider),
                priority: $provider->getPriority(),
            ))
            ->filter(fn (SearchGroupDto $group) => $group->results->isNotEmpty())
            ->sortBy(fn (SearchGroupDto $group): int => $group->priority)
            ->values();

        /** @var array<SearchGroupDto> $groupsArray */
        $groupsArray = $groups->toArray();

        return new GlobalSearchResponseDto(groups: $groupsArray);
    }
}
