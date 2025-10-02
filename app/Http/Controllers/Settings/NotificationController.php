<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\UpdateNotificationSettingsAction;
use App\Http\Requests\Settings\NotificationUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Middleware(['auth', 'verified'])]
#[Prefix('settings/notifications')]
final readonly class NotificationController
{
    public function __construct(
        private UpdateNotificationSettingsAction $updateNotificationSettingsAction
    ) {}

    #[Get('/', name: 'settings.notifications')]
    public function edit(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return Inertia::render('settings/notifications', [
            'notificationSettings' => $user->notification_settings ?? [
                'follow_notifications' => true,
                'email_notifications' => true,
                'browser_notifications' => true,
            ],
        ]);
    }

    #[Patch('/', name: 'settings.notifications.update')]
    public function update(NotificationUpdateRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $this->updateNotificationSettingsAction->handle($user, $request->validated());

        return redirect()->back()->with('success', 'Preferências de notificação atualizadas com sucesso.');
    }
}
