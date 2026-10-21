<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform.employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nip', 30)->unique();
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('phone', 30)->nullable();
            $table->text('nik')->nullable(); // encrypted KTP
            $table->date('birth_date')->nullable();
            $table->string('gender', 10)->nullable(); // male, female
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform.employees');
    }
};
