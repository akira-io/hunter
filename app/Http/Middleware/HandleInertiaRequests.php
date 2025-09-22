<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Foundation\Inspiring;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Throwable;

final class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     *
     * @throws Throwable
     */
    public function share(Request $request): array
    {
        $quotes = str(Inspiring::quotes()->random());

        [$message, $author] = explode('-', $quotes->value());

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => mb_trim($message), 'author' => mb_trim($author)],
            'auth' => [
                'user' => $request->user() ? UserResource::make($request->user())->resolve() : null,
            ],
            'sidebarOpen' => $request->hasCookie('sidebar_state') && $request->cookie('sidebar_state') !== 'false',
        ];
    }
}
