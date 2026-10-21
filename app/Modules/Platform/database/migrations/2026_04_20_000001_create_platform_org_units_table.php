<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform.org_units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('parent_id')->nullable()->index();
            $table->string('code', 20)->unique();
            $table->string('name', 255);
            $table->string('type', 30); // directorate, staff_unit, department, section
            $table->smallInteger('level'); // 0=Dirut, 1=Dir/Staff, 2=Dep, 3=Seksi
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        Schema::table('platform.org_units', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('platform.org_units')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform.org_units');
    }
};
