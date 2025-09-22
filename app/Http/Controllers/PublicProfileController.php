<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Akira\Followable\Exceptions\FollowableTraitNotFoundException;
use App\Actions\Followable\GetHuntingsAction;
use App\Actions\User\UserProfileAction;
use App\Http\Resources\Hunt\HuntResource;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Response;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Throwable;

#[Middleware(['auth', 'verified'])]
final readonly class PublicProfileController
{
    /**
     * Display the public profile of a user.
     *
     * @throws FollowableTraitNotFoundException
     * @throws Throwable
     */
    #[Get('public-profile/{user}', name: 'public.profile.show')]
    public function show(Request $request, User $user, GetHuntingsAction $huntingsAction, UserProfileAction $userProfileAction): Response
    {
        /** @var User $authUser */
        $authUser = $request->user();

        $hunts = $user->hunts()->latest()->paginate();
        $hunters = $user->followers()->latest()->paginate();
        $huntings = $huntingsAction->handle($user);

        return inertia('public-profile', [
            'user' => $userProfileAction->handle($user),
            'hunts' => HuntResource::collection($authUser->attachLikeStatus($hunts)),
            'hunters' => $authUser->attachFollowStatus($hunters),
            'huntings' => $authUser->attachFollowStatus($huntings),
        ]);
    }
}
