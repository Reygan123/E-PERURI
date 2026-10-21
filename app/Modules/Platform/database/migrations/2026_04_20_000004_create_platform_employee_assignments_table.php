<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform.employee_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id')->index();
            $table->uuid('org_unit_id')->index();
            $table->uuid('position_id')->index();
            $table->date('started_at');
            $table->date('ended_at')->nullable()->index(); // NULL = currently active
            $table->string('assignment_type', 20)->default('permanent'); // permanent, temporary, acting
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestampsTz();

            $table->foreign('employee_id')->references('id')->on('platform.employees')->cascadeOnDelete();
            $table->foreign('org_unit_id')->references('id')->on('platform.org_units')->cascadeOnDelete();
            $table->foreign('position_id')->references('id')->on('platform.positions')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('platform.employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform.employee_assignments');
    }
};
