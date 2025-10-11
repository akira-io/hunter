<?php

declare(strict_types=1);

namespace App\Http\Controllers\Likeable;

use App\Actions\Social\ToggleLikeAction;
use App\Models\Hunt;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;

#[Middleware(['auth', 'verified'])]
final readonly class ToggleHuntLikeController
{
    /**
     * Store a new like for the hunt.
     */
    #[Post('/likeable/{hunt}', name: 'hunts.toggle-like')]
    public function store(Request $request, Hunt $hunt, ToggleLikeAction $toggleLikeAction): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $toggleLikeAction->handle(user: $user, likeable: $hunt);

        return back();
    }
}
