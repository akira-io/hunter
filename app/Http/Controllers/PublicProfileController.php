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

        // Check if the authenticated user can view this profile
        if (! $user->canBeViewedBy($authUser)) {
            abort(403, 'You do not have permission to view this profile.');
        }

        $hunts = $user->hunts()->latest()->paginate();
        $hunters = $user->followers()->latest()->paginate();
        $huntings = $huntingsAction->handle($user);

        $huntersWithStatus = $authUser->attachFollowStatus($hunters);

        $huntersWithStatus->transform(function ($hunter) use ($authUser) {
            // Handle both User models and arrays
            $hunterId = $hunter instanceof User ? $hunter->id : $hunter['id'];

            /** @var User $user */
            $user = User::query()->find($hunterId);

            if ($hunter instanceof User) {
                $hunter->setAttribute('is_blocked', $authUser->hasBlocked($user));
            } else {
                $hunter['is_blocked'] = $authUser->hasBlocked($user);
            }

            return $hunter;
        });

        // Attach follow status and blocked status to huntings
        $huntingsWithStatus = $authUser->attachFollowStatus($huntings);
        $huntingsWithStatus->transform(function ($hunting) use ($authUser) {
            // Handle both User models and arrays
            $huntingId = $hunting instanceof User ? $hunting->id : $hunting['id'];

            /** @var User $user */
            $user = User::query()->find($huntingId);

            if ($hunting instanceof User) {
                $hunting->setAttribute('is_blocked', $authUser->hasBlocked($user));
            } else {
                $hunting['is_blocked'] = $authUser->hasBlocked($user);
            }

            return $hunting;
        });

        return inertia('public-profile', [
            'user' => $userProfileAction->handle($user),
            'hunts' => HuntResource::collection($authUser->attachLikeStatus($hunts)),
            'hunters' => $huntersWithStatus,
            'huntings' => $huntingsWithStatus,
        ]);
    }
}
