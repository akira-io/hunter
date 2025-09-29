<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Actions\Profile\UpdateAcademicBackgroundAction;
use App\Http\Requests\Profile\AcademicBackgroundRequest;
use App\Models\AcademicBackground;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Middleware('auth')]
#[Prefix('profile/academic-background')]
final readonly class AcademicBackgroundController
{
    /**
     * Store the user's professional education.
     */
    #[Post('/', name: 'profile.education')]
    public function store(AcademicBackgroundRequest $request, UpdateAcademicBackgroundAction $updateAcademicBackgroundAction): RedirectResponse
    {
        $user = type($request->user())->as(User::class);

        $validatedData = (array) $request->validated();

        $updateAcademicBackgroundAction->handle($user, $validatedData);

        return to_route('profile.edit');
    }

    /**
     * Delete the user's professional education.
     */
    #[Delete('/{academicBackground}', name: 'profile.education.delete')]
    public function destroy(AcademicBackground $academicBackground): RedirectResponse
    {
        $academicBackground->delete();

        return to_route('profile.edit');
    }
}
