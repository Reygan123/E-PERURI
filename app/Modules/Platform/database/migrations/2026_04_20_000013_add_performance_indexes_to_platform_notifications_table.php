<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform.notifications', function (Blueprint $table) {
            $table->index(['user_id', 'read_at'], 'platform_notifications_user_read_idx');
            $table->index(['user_id', 'created_at'], 'platform_notifications_user_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('platform.notifications', function (Blueprint $table) {
            $table->dropIndex('platform_notifications_user_read_idx');
            $table->dropIndex('platform_notifications_user_created_idx');
        });
    }
};
