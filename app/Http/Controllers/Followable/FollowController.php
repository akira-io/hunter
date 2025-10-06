<?php

declare(strict_types=1);

namespace App\Http\Controllers\Followable;

use Akira\Followable\Exceptions\CannotFollowYourSelfException;
use Akira\Followable\Exceptions\FollowableTraitNotFoundException;
use App\Actions\Social\FollowUserAction;
use App\Http\Requests\Feed\FollowRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;

#[Middleware(['auth', 'verified'])]
final readonly class FollowController
{
    /**
     * Follow a user.
     *
     * @throws CannotFollowYourSelfException|FollowableTraitNotFoundException|InvalidArgumentException
     */
    #[Post('followable/follow', name: 'followable.follow')]
    public function __invoke(FollowRequest $request, FollowUserAction $followUserAction): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @var User $userToFollow */
        $userToFollow = User::query()->findOrFail($request->validated('user_id'));

        $followUserAction->handle(follower: $user, userToFollow: $userToFollow);

        return back();
    }
}
