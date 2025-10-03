<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hunts', function (Blueprint $table) {
            $table->unsignedBigInteger('views_count')->default(0)->after('is_ignored');
            $table->unsignedBigInteger('shares_count')->default(0)->after('views_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hunts', function (Blueprint $table) {
            $table->dropColumn(['views_count', 'shares_count']);
        });
    }
};
