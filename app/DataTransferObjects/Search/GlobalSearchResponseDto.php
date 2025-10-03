<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Search;

final readonly class GlobalSearchResponseDto
{
    /**
     * @param  array<SearchGroupDto>  $groups
     */
    public function __construct(
        public array $groups,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'groups' => array_map(fn (SearchGroupDto $group) => $group->toArray(), $this->groups),
        ];
    }
}
