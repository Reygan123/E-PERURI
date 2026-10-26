<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS sdm');

        Schema::create('sdm.job_descriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('position_id');
            $table->uuid('org_unit_id');
            $table->uuid('employee_id')->nullable();
            $table->string('document_number', 100)->unique()->nullable();
            $table->integer('revision_number')->default(0);
            $table->string('sotk_reference', 255)->default('SK 009/KPTS/PTKP/IV/2026');
            $table->string('job_group', 100);
            $table->string('grade_recommendation', 50);
            $table->text('job_summary');
            $table->uuid('direct_superior_id')->nullable();
            $table->text('subordinate_notes')->nullable();
            $table->text('operating_challenges')->nullable();
            $table->text('environmental_conditions')->nullable();
            $table->text('incidental_duties')->nullable();
            $table->string('status', 30)->default('draft'); // draft, in_review, approved, archived
            $table->string('tte_hash', 128)->nullable();
            $table->timestampTz('tte_signed_at')->nullable();
            $table->uuid('created_by');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('position_id');
            $table->index('org_unit_id');
            $table->index('employee_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sdm.job_descriptions');
    }
};
