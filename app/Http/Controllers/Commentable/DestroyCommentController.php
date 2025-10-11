<?php

declare(strict_types=1);

namespace App\Http\Controllers\Commentable;

use Akira\Commentable\Exceptions\DeleteCommentNotAllowedException;
use App\Actions\Social\DeleteCommentAction;
use App\Http\Requests\Commentable\DeleteCommentRequest;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Middleware(['auth', 'verified'])]
#[Prefix('commentable/comments')]
final readonly class DestroyCommentController
{
    /**
     * Delete a comment.
     *
     * @throws DeleteCommentNotAllowedException
     */
    #[Delete('{comment}', name: 'comments.destroy')]
    public function destroy(DeleteCommentRequest $request, Comment $comment, DeleteCommentAction $deleteCommentAction): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $deleteCommentAction->handle($user, $comment);

        return back();
    }
}
