<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        DB::table('users')->get()->each(function ($user): void {
            $settingsJson = $user->notification_settings ?? '{}';
            $settings = is_string($settingsJson) ? json_decode($settingsJson, true) : [];
            $settings = is_array($settings) ? $settings : [];

            if (! isset($settings['hunt_notifications_in_app'])) {
                $settings['hunt_notifications_in_app'] = true;
            }
            if (! isset($settings['hunt_notifications_browser'])) {
                $settings['hunt_notifications_browser'] = true;
            }
            if (! isset($settings['hunt_notifications_email'])) {
                $settings['hunt_notifications_email'] = true;
            }

            DB::table('users')
                ->where('id', $user->id)
                ->update(['notification_settings' => json_encode($settings)]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove hunt notification settings from existing users
        DB::table('users')->get()->each(function ($user): void {
            $settingsJson = $user->notification_settings ?? '{}';
            $settings = is_string($settingsJson) ? json_decode($settingsJson, true) : [];
            $settings = is_array($settings) ? $settings : [];

            unset($settings['hunt_notifications_in_app']);
            unset($settings['hunt_notifications_browser']);
            unset($settings['hunt_notifications_email']);

            DB::table('users')
                ->where('id', $user->id)
                ->update(['notification_settings' => json_encode($settings)]);
        });
    }
};
