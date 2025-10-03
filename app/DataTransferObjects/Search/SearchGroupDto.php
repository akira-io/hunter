<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Search;

use Illuminate\Support\Collection;

final readonly class SearchGroupDto
{
    /**
     * @param  Collection<int, SearchResultDto>  $results
     */
    public function __construct(
        public string $type,
        public string $label,
        public string $icon,
        public Collection $results,
        public int $priority,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'label' => $this->label,
            'icon' => $this->icon,
            'results' => $this->results->map(fn (SearchResultDto $result) => $result->toArray())->toArray(),
            'priority' => $this->priority,
        ];
    }
}
