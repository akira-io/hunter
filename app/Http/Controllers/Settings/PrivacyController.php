<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\BlockUserAction;
use App\Actions\Settings\GetBlockedUsersAction;
use App\Actions\Settings\UnblockUserAction;
use App\Actions\Settings\UpdatePrivacySettingsAction;
use App\Http\Requests\Settings\UpdatePrivacySettingsRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Middleware(['auth', 'verified'])]
#[Prefix('settings/privacy')]
final readonly class PrivacyController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private UpdatePrivacySettingsAction $updatePrivacySettingsAction,
        private GetBlockedUsersAction $getBlockedUsersAction,
        private BlockUserAction $blockUserAction,
        private UnblockUserAction $unblockUserAction,
    ) {}

    /**
     * Show the user's privacy settings page.
     */
    #[Get('/', name: 'privacy.index')]
    public function index(Request $request): Response
    {
        $user = type($request->user())->as(User::class);

        return Inertia::render('settings/privacy', [
            'privacySettings' => $user->privacy_settings ?? [
                'profile_visibility' => 'public',
                'who_can_message' => 'everyone',
                'who_can_comment' => 'everyone',
                'searchable' => true,
                'show_activity_status' => true,
            ],
            'blockedUsers' => $this->getBlockedUsersAction->handle($user),
        ]);
    }

    /**
     * Update the user's privacy settings.
     */
    #[Post('/', name: 'privacy.update')]
    public function update(UpdatePrivacySettingsRequest $request): RedirectResponse
    {
        $user = type($request->user())->as(User::class);

        $this->updatePrivacySettingsAction->handle($user, $request->validated());

        return back();
    }

    /**
     * Block a user.
     */
    #[Post('/block/{userId}', name: 'privacy.block-user')]
    public function blockUser(Request $request, int $userId): RedirectResponse
    {
        $user = type($request->user())->as(User::class);

        $this->blockUserAction->handle($user, $userId);

        return back();
    }

    /**
     * Unblock a user.
     */
    #[Delete('/unblock/{userId}', name: 'privacy.unblock-user')]
    public function unblockUser(Request $request, int $userId): RedirectResponse
    {
        $user = type($request->user())->as(User::class);

        $this->unblockUserAction->handle($user, $userId);

        return back();
    }
}
