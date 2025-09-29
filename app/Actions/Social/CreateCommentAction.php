<?php

declare(strict_types=1);

namespace App\Actions\Social;

use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Model;

final readonly class CreateCommentAction
{
    /**
     * Create a comment for a commentable model.
     *
     * @throws Exception
     */
    public function handle(User $user, Model $commentable, string $content): void
    {
        $user->comment($commentable, $content);
    }
}
