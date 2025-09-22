<?php

declare(strict_types=1);

namespace App\Http\Controllers\Followable;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Middleware(['auth', 'verified'])]
final readonly class GetHuntersController
{
    /**
     * Display the followers of the authenticated user.
     */
    #[Get('followable/followers', name: 'followable.followers')]
    public function __invoke(Request $request): Response
    {

        /*** @var User $user */
        $user = type($request->user())->as(User::class);

        $followers = $user->followers()->paginate(20);

        return inertia('followable/hunters', [
            'followers' => $user->attachFollowStatus($followers),
        ]);
    }
}
