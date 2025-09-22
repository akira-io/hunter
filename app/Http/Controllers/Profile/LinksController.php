<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Http\Requests\Profile\ProfileLinkRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Patch;

#[Middleware(['auth', 'verified'])]
final readonly class LinksController
{
    /**
     * Display the user's profile links.
     */
    #[Patch('/profile/links', name: 'profile.links')]
    public function __invoke(ProfileLinkRequest $request): RedirectResponse
    {
        $user = type($request->user())->as(User::class);
        $validatedData = $request->validated();
        /** @var array<string, mixed> $attributes */
        $attributes = is_array($validatedData) ? $validatedData : [];
        $user->update($attributes);

        return to_route('profile.edit');
    }
}
