<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Search\GlobalSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Middleware(['auth', 'verified'])]
final readonly class SearchController
{
    public function __construct(
        private GlobalSearchService $globalSearchService
    ) {}

    #[Get('/api/search', name: 'search.index')]
    public function __invoke(Request $request): JsonResponse
    {
        $query = (string) $request->input('q', '');

        $response = $this->globalSearchService->search($query);

        return response()->json($response->toArray());
    }
}
