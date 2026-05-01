<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ChangelogService;
use App\ValueObjects\ChangelogEntry;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\RouteAttributes\Attributes\Get;

final readonly class ChangelogController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private ChangelogService $changelogService
    ) {}

    /**
     * Display the changelog page.
     */
    #[Get('/changelog', name: 'changelog.index')]
    public function index(): Response
    {
        $entries = $this->changelogService->getAll();

        return Inertia::render('changelog/index', [
            'entries' => $entries->map(fn (ChangelogEntry $entry): array => $entry->toArray()),
        ]);
    }

    /**
     * Get a specific version.
     */
    #[Get('/changelog/{version}', name: 'changelog.show')]
    public function show(string $version): Response
    {
        $entry = $this->changelogService->getByVersion($version);

        if (! $entry instanceof ChangelogEntry) {
            abort(404, 'Changelog version not found');
        }

        return Inertia::render('changelog/show', [
            'entry' => $entry->toArray(),
        ]);
    }
}
