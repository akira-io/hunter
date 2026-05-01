<?php

declare(strict_types=1);

use App\Services\ChangelogService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

afterEach(function () {
    Cache::forget('changelog:entries');
});

it('displays changelog index page', function () {
    $response = $this->get('/changelog');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('changelog/index')
            ->has('entries')
        );
});

it('displays changelog entries with correct structure', function () {
    $response = $this->get('/changelog');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('changelog/index')
            ->has('entries.0', fn ($entry) => $entry
                ->has('version')
                ->has('formatted_version')
                ->has('date')
                ->has('formatted_date')
                ->has('human_date')
                ->has('is_latest')
                ->has('sections')
                ->has('total_changes')
                ->has('raw_content')
            )
        );
});

it('gets latest changelog entry json', function () {
    $changelogService = new ChangelogService;
    $latest = $changelogService->getLatest();

    expect($latest)
        ->toBeInstanceOf(App\ValueObjects\ChangelogEntry::class)
        ->and($latest->version)->toBe('0.6.0');
});

it('displays specific version page', function () {
    $response = $this->get('/changelog/0.6.0');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('changelog/show')
            ->where('entry.version', '0.6.0')
            ->has('entry.sections')
            ->whereType('entry.total_changes', 'integer')
        );
});

it('returns 404 for non-existent version', function () {
    $response = $this->get('/changelog/999.999.999');

    $response->assertNotFound();
});

it('changelog pages are publicly accessible', function () {
    $response = $this->get('/changelog');

    $response->assertSuccessful();

    $response = $this->get('/changelog/0.6.0');

    $response->assertSuccessful();
});

it('caches changelog data across requests', function () {
    // First request
    $response1 = $this->get('/changelog');
    $response1->assertSuccessful();

    expect(Cache::has('changelog:entries'))->toBeTrue();

    // Second request should use cached data
    $response2 = $this->get('/changelog');
    $response2->assertSuccessful();

    // Both responses should be successful
    expect($response1->status())->toBe(200)
        ->and($response2->status())->toBe(200);
});

it('shows version 0.6.0 with sections', function () {
    $response = $this->get('/changelog/0.6.0');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('changelog/show')
            ->where('entry.version', '0.6.0')
            ->has('entry.sections')
        );
});

it('formats dates correctly', function () {
    $response = $this->get('/changelog/0.6.0');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('entry.formatted_date', '11 de outubro de 2025')
        );
});

it('shows correct total changes count', function () {
    $changelogService = new ChangelogService;
    $entry = $changelogService->getByVersion('0.6.0');

    $expectedTotal = collect($entry->sections)->sum(fn ($items) => count($items));

    $response = $this->get('/changelog/0.6.0');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('entry.total_changes', $expectedTotal)
        );
});

it('shows first entry as version 0.6.0', function () {
    $response = $this->get(route('changelog.index'));

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('entries.0.version', '0.6.0')
        );
});
