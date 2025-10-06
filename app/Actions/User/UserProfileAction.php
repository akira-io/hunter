<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

final readonly class UserProfileAction
{
    /**
     * Get the avatar URL for the user.
     */
    public function handle(User $user): mixed
    {

        /**
         * @var User $authUser
         */
        $authUser = type(Auth::user())->as(User::class);

        $user->avatar_url = new GetAvatarAction()->handle($user);

        $user->setAttribute('background_image_url', new GetBackgroundImageAction()->handle($user));

        $userWithFollowStatus = type($authUser->attachFollowStatus(followables: $user))->as(Collection::class)->sole();
        $userWithFollowStatus['is_blocked'] = $authUser->hasBlocked($user);

        return $userWithFollowStatus;

    }
}
