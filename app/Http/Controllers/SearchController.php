<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Search\GlobalSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;

/**
 * Handle global search requests across the application.
 *
 * This controller provides a unified search interface that searches
 * across multiple models (Users, Hunts, etc.) and returns grouped results.
 */
#[Middleware(['auth', 'verified'])]
final readonly class SearchController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private GlobalSearchService $globalSearchService
    ) {}

    /**
     * Search for users, hunts, and posts.
     *
     * @return JsonResponse The search results grouped by type
     */
    #[Get('/api/search', name: 'search.index')]
    public function __invoke(Request $request): JsonResponse
    {
        $query = (string) $request->input('q', '');

        $response = $this->globalSearchService->search($query);

        return response()->json($response->toArray());
    }
}
