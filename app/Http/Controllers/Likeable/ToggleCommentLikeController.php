<?php

declare(strict_types=1);

namespace App\Http\Controllers\Likeable;

use App\Actions\Social\ToggleLikeAction;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;

#[Middleware(['auth', 'verified'])]
final readonly class ToggleCommentLikeController
{
    /**
     * Store a new like for the comment.
     */
    #[Post('/likeable/comments/{comment}', name: 'comments.toggle-like')]
    public function store(Request $request, Comment $comment, ToggleLikeAction $toggleLikeAction): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $toggleLikeAction->handle($user, $comment);

        return to_route('hunts.index');
    }
}
