<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Search;

final readonly class SearchResultDto
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $id,
        public string $title,
        public ?string $subtitle,
        public ?string $description,
        public ?string $image,
        public string $url,
        public array $metadata = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'image' => $this->image,
            'url' => $this->url,
            'metadata' => $this->metadata,
        ];
    }
}
