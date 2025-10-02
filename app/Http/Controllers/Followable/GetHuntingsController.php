<?php

declare(strict_types=1);

namespace App\Http\Controllers\Followable;

use Akira\Followable\Exceptions\FollowableTraitNotFoundException;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Response;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Middleware(['auth', 'verified'])]
final readonly class GetHuntingsController
{
    /**
     * Display the followings of the authenticated user.
     *
     * @throws FollowableTraitNotFoundException
     */
    #[Get('followable/followings', name: 'followable.followings')]
    public function __invoke(Request $request): Response
    {
        /*** @var User $user */
        $user = type($request->user())->as(User::class);
        $paginator = $user->followings()->with(['followable'])->paginate(20);
        $followingsWithStatus = $user->attachFollowStatus($paginator);
        /** @var \Illuminate\Support\Collection<(int|string), mixed> $followingsWithStatus */
        $paginator->setCollection($followingsWithStatus);

        return inertia('followable/huntings', [
            'followings' => \Inertia\Inertia::scroll($paginator),
        ]);
    }
}
