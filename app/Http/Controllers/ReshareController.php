<?php

declare(strict_types=1);

namespace App\Http.Controllers;

use App\Models\Hunt;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Middleware(['auth', 'verified'])]
#[Prefix('hunts/{hunt}/reshare')]
final readonly class ReshareController
{
    #[Post(uri: '/', name: 'hunts.reshare.store')]
    public function store(Request $request, Hunt $hunt): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $hunt->reshares()->create([
            'user_id' => $user->id,
            'comment' => $request->input('comment'),
        ]);

        return back();
    }

    #[Delete(uri: '/', name: 'hunts.reshare.destroy')]
    public function destroy(Request $request, Hunt $hunt): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $hunt->reshares()->where('user_id', $user->id)->delete();

        return back();
    }
}
