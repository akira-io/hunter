<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Hunt\CreateHuntAction;
use App\Actions\Hunt\DeleteHuntAction;
use App\Actions\Hunt\GetHuntsAction;
use App\DataTransferObjects\Hunt\CreateHuntData;
use App\Http\Requests\Hunt\CreateHuntRequest;
use App\Http\Requests\Hunt\DeleteHuntRequest;
use App\Http\Resources\Hunt\HuntResource;
use App\Models\Hunt;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Middleware(['auth', 'verified'])]

#[Prefix('hunts')]
final readonly class HuntController
{
    /**
     * Display the hunt line.
     */
    #[Get(uri: '/', name: 'hunts.index')]
    public function index(Request $request, GetHuntsAction $getHuntsAction): Response
    {
        /** @var User $user */
        $user = $request->user();

        $hunts = $getHuntsAction->handle(user: $user);

        return Inertia::render('hunts/hunts', [
            'hunts' => Inertia::scroll(fn () => HuntResource::collection($hunts)),
        ]);
    }

    /**
     * Store a new hunt.
     */
    #[Post(uri: '/', name: 'hunts.store')]
    public function store(CreateHuntRequest $request, CreateHuntAction $createHuntAction): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $hunt = $createHuntAction->handle(
            user: $user,
            huntData: CreateHuntData::fromRequest(request: $request)
        );

        // Attach like status for the current user
        $user->attachLikeStatus($hunt);

        return to_route('hunts.index')->with([
            'newHunt' => HuntResource::make($hunt)->resolve(),
        ]);
    }

    /**
     * Show a single hunt.
     */
    #[Get(uri: '/{hunt}', name: 'hunts.show')]
    public function show(Request $request, Hunt $hunt): Response
    {
        /** @var User $user */
        $user = $request->user();

        if (! $hunt->owner->canBeViewedBy($user)) {
            abort(403, 'You do not have permission to view this hunt.');
        }

        $user->attachLikeStatus($hunt);

        return Inertia::render('hunts/show', [
            'hunt' => HuntResource::make($hunt)->resolve(),
        ]);
    }

    /**
     * Delete a hunt.
     */
    #[Delete(uri: '/{hunt}', name: 'hunts.destroy')]
    public function destroy(DeleteHuntRequest $request, Hunt $hunt, DeleteHuntAction $deleteHuntAction): RedirectResponse
    {
        $deleteHuntAction->handle(hunt: $hunt);

        return back();
    }
}
