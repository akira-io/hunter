<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Events\UserOffline;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

final readonly class UpdatePrivacySettingsAction
{
    /**
     * Update user's privacy settings.
     *
     * @param  array<string, mixed>  $settings
     */
    public function handle(User $user, array $settings): void
    {
        $previousShowActivityStatus = $user->showsActivityStatus();

        $user->update([
            'privacy_settings' => $settings,
        ]);

        $user->refresh();

        if ($previousShowActivityStatus && ! $user->showsActivityStatus()) {
            Cache::forget("user_online_{$user->id}");
            UserOffline::dispatch($user);
        }
    }
}
