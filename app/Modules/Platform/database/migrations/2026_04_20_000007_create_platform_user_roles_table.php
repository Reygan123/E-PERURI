<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform.user_roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index();
            $table->uuid('role_id')->index();
            $table->uuid('org_unit_id')->nullable()->index(); // scope unit
            $table->date('valid_from');
            $table->date('valid_until')->nullable(); // NULL = no expiry
            $table->timestampsTz();

            $table->foreign('user_id')->references('id')->on('platform.users')->cascadeOnDelete();
            $table->foreign('role_id')->references('id')->on('platform.roles')->cascadeOnDelete();
            $table->foreign('org_unit_id')->references('id')->on('platform.org_units')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform.user_roles');
    }
};
