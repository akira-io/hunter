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
        // Update the default value for notification_settings column
        DB::statement("
            ALTER TABLE users
            ALTER COLUMN notification_settings
            SET DEFAULT '{\"follow_notifications\":true,\"email_notifications\":true,\"browser_notifications\":true,\"hunt_notifications_in_app\":true,\"hunt_notifications_browser\":true,\"hunt_notifications_email\":true}'::json
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the old default value
        DB::statement("
            ALTER TABLE users
            ALTER COLUMN notification_settings
            SET DEFAULT '{\"follow_notifications\":true,\"email_notifications\":true,\"browser_notifications\":true}'::json
        ");
    }
};
