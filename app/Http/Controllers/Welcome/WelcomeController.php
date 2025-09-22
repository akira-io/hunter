<?php

declare(strict_types=1);

namespace App\Http\Controllers\Welcome;

use App\Actions\GetHuntersAction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final readonly class WelcomeController
{
    /**
     * Display  or search for users
     *
     * @throws Throwable
     */
    public function index(Request $request, GetHuntersAction $action): Response
    {
        $currentPage = (int) $request->get('page', 1);
        $isInertiaRequest = (bool) $request->header('X-Inertia');
        // If it's a page refresh (not AJAX) and page > 1, load all pages from 1 to current
        // This applies to ANY page > 1, including the last page
        if (! $isAjaxRequest && $currentPage > 1 && ! $request->has('q')) {
            $allUsers = collect();
            $finalPaginator = null;

            // Load all pages from 1 to current page
            for ($page = 1; $page <= $currentPage; $page++) {
                $pageRequest = clone $request;
                $pageRequest->merge(['page' => $page]);
                [$pageUsers, $paginator] = $action->handle($pageRequest);
                $allUsers = $allUsers->concat($pageUsers);
                $finalPaginator = $paginator;
            }

            return Inertia::render('welcome', [
                'users' => $allUsers->values()->all(),
                'paginator' => $finalPaginator,
            ]);
        }

        // Normal behavior for AJAX requests or first page
        [$user, $paginator] = $action->handle($request);

        return Inertia::render('welcome', [
            'users' => Inertia::merge($user),
            'paginator' => $paginator,
        ]);
    }
}
