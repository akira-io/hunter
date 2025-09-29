<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Profile\DeleteAccountAction;
use App\Actions\Profile\UpdateProfileAction;
use App\Actions\User\Profile\UpdateProfileAvatarAction;
use App\Actions\User\Profile\UpdateProfileBackgroundAction;
use App\DataTransferObjects\Profile\UpdateProfileData;
use App\Enums\SkillsEnum;
use App\Http\Requests\Settings\DeleteAccountRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

final readonly class ProfileController
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = type($request->user())->as(User::class);

        return Inertia::render('settings/profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'skills' => SkillsEnum::get(),
            'highlightedSkills' => $user->skills,
            'academicBackgrounds' => $user->academicBackgrounds,
            'followers' => $user->followers()->count(),
            'followings' => $user->followings()->count(),
        ]);
    }

    /**
     * Update the user's profile settings.
     *
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function update(ProfileUpdateRequest $request, UpdateProfileAvatarAction $profileAvatarAction, UpdateProfileBackgroundAction $profileBackgroundAction, UpdateProfileAction $updateProfileAction): RedirectResponse
    {
        $user = type($request->user())->as(User::class);

        $request->updateImages($profileBackgroundAction, $profileAvatarAction);

        $updateProfileAction->handle(
            user: $user,
            profileData: UpdateProfileData::fromRequest(request: $request)
        );

        return back();
    }

    /**
     * Delete the user's account.
     */
    public function destroy(DeleteAccountRequest $request, DeleteAccountAction $deleteAccountAction): RedirectResponse
    {
        $user = type($request->user())->as(User::class);

        $deleteAccountAction->handle(user: $user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
