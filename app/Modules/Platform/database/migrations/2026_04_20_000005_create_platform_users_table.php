<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform.users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id')->nullable()->unique();
            $table->string('name', 255);
            $table->string('username', 50)->unique();
            $table->string('email', 255)->unique();
            $table->timestampTz('email_verified_at')->nullable();
            $table->string('password', 255);
            $table->boolean('must_change_pwd')->default(true);
            $table->timestampTz('last_login_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestampsTz();

            $table->foreign('employee_id')->references('id')->on('platform.employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform.users');
    }
};
