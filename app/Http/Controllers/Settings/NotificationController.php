<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\UpdateNotificationSettingsAction;
use App\Http\Requests\Settings\NotificationUpdateRequest;
use App\Models\User;
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
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private UpdateNotificationSettingsAction $updateNotificationSettingsAction
    ) {}

    /**
     * Display the notification settings form.
     */
    #[Get('/', name: 'settings.notifications')]
    public function edit(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('settings/notifications', [
            'notificationSettings' => $user->notification_settings ?? [
                'follow_notifications' => true,
                'email_notifications' => true,
                'browser_notifications' => true,
                'hunt_notifications_in_app' => true,
                'hunt_notifications_browser' => true,
                'hunt_notifications_email' => false,
            ],
        ]);
    }

    /**
     * Update the notification settings.
     */
    #[Patch('/', name: 'settings.notifications.update')]
    public function update(NotificationUpdateRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->updateNotificationSettingsAction->handle($user, $request->validated());

        return redirect()->back()->with('success', 'Preferências de notificação atualizadas com sucesso.');
    }
}
