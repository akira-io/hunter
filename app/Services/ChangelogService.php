<?php

declare(strict_types=1);

namespace App\Services;

use App\ValueObjects\ChangelogEntry;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

/**
 * Changelog Service
 * Parses and provides access to the CHANGELOG.md file.
 */
final readonly class ChangelogService
{
    private const string CHANGELOG_PATH = 'CHANGELOG.md';

    private const int CACHE_TTL = 3600; // 1 hour

    private const string CACHE_KEY = 'changelog:entries';

    /**
     * Get all changelog entries.
     *
     * @return Collection<int, ChangelogEntry>
     */
    public function getAll(): Collection
    {
        return Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL,
            fn (): Collection => $this->parseChangelog()
        );
    }

    /**
     * Get the latest changelog entry.
     */
    public function getLatest(): ?ChangelogEntry
    {
        return $this->getAll()->first();
    }

    /**
     * Get changelog entry by version.
     */
    public function getByVersion(string $version): ?ChangelogEntry
    {
        return $this->getAll()->first(
            fn (ChangelogEntry $entry): bool => $entry->version === $version
        );
    }

    /**
     * Get recent changelog entries.
     *
     * @return Collection<int, ChangelogEntry>
     */
    public function getRecent(int $limit = 5): Collection
    {
        return $this->getAll()->take($limit);
    }

    /**
     * Get only released versions (excluding "Unreleased").
     *
     * @return Collection<int, ChangelogEntry>
     */
    public function getReleased(): Collection
    {
        return $this->getAll()->reject(
            fn (ChangelogEntry $entry): bool => $entry->isLatest()
        );
    }

    /**
     * Clear the changelog cache.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Parse the CHANGELOG.md file.
     *
     * @return Collection<int, ChangelogEntry>
     */
    private function parseChangelog(): Collection
    {
        $path = base_path(self::CHANGELOG_PATH);

        if (! File::exists($path)) {
            return collect();
        }

        $content = File::get($path);
        $entries = collect();

        // Split by version headers (## followed by version number or "Unreleased")
        $pattern = '/^## (.+?)$/m';
        preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);
        $counter = count($matches[0]);

        for ($i = 0; $i < $counter; $i++) {
            $versionLine = $matches[1][$i][0];
            $startPos = $matches[0][$i][1];
            $endPos = $matches[0][$i + 1][1] ?? mb_strlen($content);

            $sectionContent = mb_substr($content, $startPos, $endPos - $startPos);

            $entry = $this->parseEntry($versionLine, $sectionContent);
            $entries->push($entry);
        }

        return $entries;
    }

    /**
     * Parse a single changelog entry.
     */
    private function parseEntry(string $versionLine, string $content): ChangelogEntry
    {
        // Extract version and date
        // Format: "0.6.0 (2025-10-11)" or "Unreleased"
        $version = mb_trim($versionLine);
        $date = null;

        if (preg_match('/^(.+?)\s*\((\d{4}-\d{2}-\d{2})\)$/', $version, $matches)) {
            $version = mb_trim($matches[1]);
            $date = Carbon::createFromFormat('Y-m-d', $matches[2]);
        }

        // Parse sections (### Section Name)
        $sections = $this->parseSections($content);

        return new ChangelogEntry(
            version: $version,
            date: $date,
            sections: $sections,
            rawContent: mb_trim($content),
        );
    }

    /**
     * Parse sections within an entry.
     *
     * @return array<string, array<int, string>>
     */
    private function parseSections(string $content): array
    {
        $sections = [];

        // Find all section headers (### Section Name or **Section Name**)
        $pattern = '/^###\s+(.+?)$/m';
        preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);
        $counter = count($matches[0]);

        for ($i = 0; $i < $counter; $i++) {
            $sectionName = mb_trim($matches[1][$i][0]);
            $sectionName = $this->normalizeSectionName($sectionName);

            $startPos = $matches[0][$i][1] + mb_strlen($matches[0][$i][0]);
            $endPos = $matches[0][$i + 1][1] ?? mb_strlen($content);

            $sectionContent = mb_substr($content, $startPos, $endPos - $startPos);
            $items = $this->parseItems($sectionContent);

            if ($items !== []) {
                $sections[$sectionName] = $items;
            }
        }

        // Also check for **Section:** format in older versions
        if ($sections === []) {
            return $this->parseLegacySections($content);
        }

        return $sections;
    }

    /**
     * Parse items from section content (bullet points).
     *
     * @return array<int, string>
     */
    private function parseItems(string $content): array
    {
        $items = [];

        // Match bullet points (- Item)
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = mb_trim($line);

            // Match lines starting with - or *
            if (preg_match('/^[-*]\s+(.+)$/', $line, $matches)) {
                $items[] = mb_trim($matches[1]);
            }
        }

        return $items;
    }

    /**
     * Parse legacy section format (#### Section or **Section:**).
     *
     * @return array<string, array<int, string>>
     */
    private function parseLegacySections(string $content): array
    {
        $sections = [];

        // Try #### headers
        $pattern = '/^####\s+(.+?)$/m';
        if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            $counter = count($matches[0]);
            for ($i = 0; $i < $counter; $i++) {
                $sectionName = mb_trim($matches[1][$i][0]);
                $sectionName = $this->normalizeSectionName($sectionName);

                $startPos = $matches[0][$i][1] + mb_strlen($matches[0][$i][0]);
                $endPos = $matches[0][$i + 1][1] ?? mb_strlen($content);

                $sectionContent = mb_substr($content, $startPos, $endPos - $startPos);
                $items = $this->parseItems($sectionContent);

                if ($items !== []) {
                    $sections[$sectionName] = $items;
                }
            }
        }

        return $sections;
    }

    /**
     * Normalize section name for consistency.
     */
    private function normalizeSectionName(string $name): string
    {
        // Remove emoji and special characters
        $name = preg_replace('/[^\w\s-]/u', '', $name);

        // Remove common prefixes
        $name = preg_replace('/^(Features|Bug Fixes|Improvements)/i', '$1', (string) $name);

        return mb_trim($name);
    }
}
