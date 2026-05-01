<?php

declare(strict_types=1);

namespace App\ValueObjects;

use Carbon\Carbon;

/**
 * Changelog Entry DTO
 * Represents a single version entry from the changelog.
 */
final readonly class ChangelogEntry
{
    /**
     * Create a new changelog entry.
     *
     * @param  array<string, array<int, string>>  $sections
     */
    public function __construct(
        public string $version,
        public ?Carbon $date,
        public array $sections,
        public string $rawContent,
    ) {}

    /**
     * Check if this is the latest version.
     */
    public function isLatest(): bool
    {
        return str_starts_with(mb_strtolower($this->version), 'unreleased');
    }

    /**
     * Get formatted version string.
     */
    public function getFormattedVersion(): string
    {
        return $this->isLatest() ? 'Unreleased' : "v{$this->version}";
    }

    /**
     * Get formatted date string in Portuguese.
     */
    public function getFormattedDate(): ?string
    {
        if (! $this->date instanceof Carbon) {
            return null;
        }

        return $this->date->locale('pt_BR')->isoFormat('D [de] MMMM [de] YYYY');
    }

    /**
     * Get human-readable date in Portuguese.
     */
    public function getHumanDate(): ?string
    {
        if (! $this->date instanceof Carbon) {
            return null;
        }

        return $this->date->locale('pt_BR')->diffForHumans();
    }

    /**
     * Check if entry has a specific section.
     */
    public function hasSection(string $section): bool
    {
        return isset($this->sections[$section]) && ! empty($this->sections[$section]);
    }

    /**
     * Get items from a specific section.
     *
     * @return array<int, string>
     */
    public function getSection(string $section): array
    {
        return $this->sections[$section] ?? [];
    }

    /**
     * Get all section names.
     *
     * @return array<int, string>
     */
    public function getSectionNames(): array
    {
        return array_keys($this->sections);
    }

    /**
     * Count total changes across all sections.
     */
    public function getTotalChanges(): int
    {
        return array_sum(array_map('count', $this->sections));
    }

    /**
     * Convert to array for JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'formatted_version' => $this->getFormattedVersion(),
            'date' => $this->date?->toISOString(),
            'formatted_date' => $this->getFormattedDate(),
            'human_date' => $this->getHumanDate(),
            'is_latest' => $this->isLatest(),
            'sections' => $this->sections,
            'total_changes' => $this->getTotalChanges(),
            'raw_content' => $this->rawContent,
        ];
    }
}
