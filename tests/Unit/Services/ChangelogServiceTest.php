<?php

declare(strict_types=1);

use App\Services\ChangelogService;
use App\ValueObjects\ChangelogEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->changelogService = new ChangelogService;
});

afterEach(function () {
    Cache::forget('changelog:entries');
});

it('returns empty collection when changelog file does not exist', function () {
    File::shouldReceive('exists')
        ->once()
        ->with(base_path('CHANGELOG.md'))
        ->andReturn(false);

    $entries = $this->changelogService->getAll();

    expect($entries)->toBeEmpty();
});

it('parses changelog entries correctly', function () {
    $entries = $this->changelogService->getAll();

    expect($entries)->not->toBeEmpty()
        ->and($entries->first())->toBeInstanceOf(ChangelogEntry::class);
});

it('returns latest changelog entry', function () {
    $latest = $this->changelogService->getLatest();

    expect($latest)
        ->toBeInstanceOf(ChangelogEntry::class)
        ->and($latest->version)->toBe('0.6.0');
});

it('gets changelog entry by version', function () {
    $entry = $this->changelogService->getByVersion('0.5.0');

    expect($entry)
        ->toBeInstanceOf(ChangelogEntry::class)
        ->and($entry->version)->toBe('0.5.0')
        ->and($entry->date)->toBeInstanceOf(Carbon::class)
        ->and($entry->sections)->toBeArray()
        ->and($entry->sections)->not->toBeEmpty();
});

it('returns null when version not found', function () {
    $entry = $this->changelogService->getByVersion('999.999.999');

    expect($entry)->toBeNull();
});

it('gets recent changelog entries with limit', function () {
    $recent = $this->changelogService->getRecent(3);

    expect($recent)->toHaveCount(3)
        ->and($recent->first()->version)->toBe('0.6.0');
});

it('parses version with date correctly', function () {
    $entry = $this->changelogService->getByVersion('0.6.0');

    expect($entry->date)
        ->toBeInstanceOf(Carbon::class)
        ->and($entry->date->format('Y-m-d'))->toBe('2025-10-11')
        ->and($entry->getFormattedDate())->toBe('11 de outubro de 2025');
});

it('parses sections with items', function () {
    $entry = $this->changelogService->getByVersion('0.6.0');

    expect($entry->sections)->toBeArray()
        ->and($entry->sections)->not->toBeEmpty()
        ->and($entry->getTotalChanges())->toBeGreaterThan(0);
});

it('caches changelog entries', function () {
    Cache::flush();

    // First call - should hit the file system
    $entries1 = $this->changelogService->getAll();

    // Second call - should hit the cache
    $entries2 = $this->changelogService->getAll();

    expect($entries1)->toEqual($entries2)
        ->and(Cache::has('changelog:entries'))->toBeTrue();
});

it('clears cache correctly', function () {
    $this->changelogService->getAll();

    expect(Cache::has('changelog:entries'))->toBeTrue();

    $this->changelogService->clearCache();

    expect(Cache::has('changelog:entries'))->toBeFalse();
});

it('gets only released versions excluding unreleased', function () {
    $released = $this->changelogService->getReleased();

    expect($released)->not->toBeEmpty()
        ->and($released->every(fn (ChangelogEntry $entry) => ! $entry->isLatest()))->toBeTrue();
});

it('normalizes section names correctly', function () {
    $entry = $this->changelogService->getByVersion('0.6.0');

    $sectionNames = array_keys($entry->sections);

    foreach ($sectionNames as $name) {
        expect($name)
            ->toBeString()
            ->and(str_contains($name, '**'))->toBeFalse()
            ->and(str_contains($name, '###'))->toBeFalse();
    }
});

it('counts total changes correctly', function () {
    $entry = $this->changelogService->getByVersion('0.6.0');

    $expectedTotal = collect($entry->sections)->sum(fn ($items) => count($items));

    expect($entry->getTotalChanges())->toBe($expectedTotal);
});
