<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform.positions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('org_unit_id')->index();
            $table->string('code', 50)->unique();
            $table->string('name', 255);
            $table->smallInteger('level'); // grade jabatan
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            $table->foreign('org_unit_id')->references('id')->on('platform.org_units')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform.positions');
    }
};
