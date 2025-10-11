<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->json('privacy_settings')->nullable()->default(json_encode([
                'profile_visibility' => 'public',
                'who_can_message' => 'everyone',
                'who_can_comment' => 'everyone',
                'searchable' => true,
                'show_activity_status' => true,
            ]))->after('notification_settings');
        });

        // Update PostgreSQL column default
        DB::statement("
            ALTER TABLE users
            ALTER COLUMN privacy_settings
            SET DEFAULT '{\"profile_visibility\":\"public\",\"who_can_message\":\"everyone\",\"who_can_comment\":\"everyone\",\"searchable\":true,\"show_activity_status\":true}'::json
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('privacy_settings');
        });
    }
};
