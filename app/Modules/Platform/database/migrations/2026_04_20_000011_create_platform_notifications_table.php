<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform.notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index();
            $table->string('type', 100);
            $table->string('channel', 20); // database, email, whatsapp
            $table->jsonb('payload');
            $table->timestampTz('read_at')->nullable();
            $table->timestampTz('sent_at')->nullable();
            $table->timestampsTz();

            $table->foreign('user_id')->references('id')->on('platform.users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform.notifications');
    }
};
