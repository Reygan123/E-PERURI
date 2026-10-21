<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform.roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100)->unique();
            $table->string('label', 255);
            $table->timestampsTz();
        });

        Schema::create('platform.permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255)->unique(); // format: modul.sumber.aksi
            $table->string('label', 255);
            $table->string('module', 50)->index(); // platform, arsip, layanan, dll
            $table->timestampsTz();
        });

        Schema::create('platform.role_permissions', function (Blueprint $table) {
            $table->uuid('role_id');
            $table->uuid('permission_id');
            $table->string('scope', 20)->default('all'); // own, unit, unit_tree, all
            $table->primary(['role_id', 'permission_id']);

            $table->foreign('role_id')->references('id')->on('platform.roles')->cascadeOnDelete();
            $table->foreign('permission_id')->references('id')->on('platform.permissions')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform.role_permissions');
        Schema::dropIfExists('platform.permissions');
        Schema::dropIfExists('platform.roles');
    }
};
