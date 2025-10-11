<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GetHuntersAction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Middleware(['auth', 'verified'])]
#[Prefix('finder')]
final readonly class FinderController
{
    /**
     * Display the finder page.
     */
    #[Get('/', name: 'finder.index')]
    public function __invoke(Request $request, GetHuntersAction $action): Response
    {
        [$users, $paginator] = $action->handle($request);

        return Inertia::render('finder', [
            'users' => Inertia::scroll($paginator),
        ]);
    }
}
