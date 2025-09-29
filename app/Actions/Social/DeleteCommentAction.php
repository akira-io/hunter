<?php

declare(strict_types=1);

namespace App\Actions\Social;

use Akira\Commentable\Exceptions\DeleteCommentNotAllowedException;
use App\Models\Comment;
use App\Models\User;

final readonly class DeleteCommentAction
{
    /**
     * Delete a comment.
     *
     * @throws DeleteCommentNotAllowedException
     */
    public function handle(User $user, Comment $comment): void
    {
        $user->deleteComment($comment);
    }
}
