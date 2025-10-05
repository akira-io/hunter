<?php

declare(strict_types=1);

namespace App\Actions\Social;

use App\Models\Hunt;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final readonly class CreateCommentAction
{
    /**
     * Create a comment for a commentable model.
     *
     * @throws Exception
     */
    public function handle(User $user, Model $commentable, string $content): void
    {
        // Check privacy settings for Hunt comments
        if ($commentable instanceof Hunt) {
            $owner = $commentable->owner;

            if (! $owner->canReceiveCommentsFrom($user)) {
                throw new AccessDeniedHttpException('Não tem permissão para comentar nesta publicação.');
            }
        }

        $user->comment($commentable, $content);
    }
}
