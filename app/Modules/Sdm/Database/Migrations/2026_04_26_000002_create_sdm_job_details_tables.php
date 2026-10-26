<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tugas Pokok & Rincian Aktivitas
        Schema::create('sdm.job_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('job_description_id');
            $table->string('activity_name', 255);
            $table->text('activity_details');
            $table->integer('sort_order')->default(0);
            $table->timestampsTz();

            $table->foreign('job_description_id')
                ->references('id')
                ->on('sdm.job_descriptions')
                ->onDelete('cascade');
        });

        // 2. Tanggung Jawab & Wewenang
        Schema::create('sdm.job_governances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('job_description_id');
            $table->string('type', 30); // responsibility, authority
            $table->text('description');
            $table->integer('sort_order')->default(0);
            $table->timestampsTz();

            $table->foreign('job_description_id')
                ->references('id')
                ->on('sdm.job_descriptions')
                ->onDelete('cascade');
        });

        // 3. Hubungan Kerja (Internal & Eksternal beserta rincian "Dalam Hal" ala Telkom Univ)
        Schema::create('sdm.job_working_relations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('job_description_id');
            $table->string('relation_type', 20); // internal, external
            $table->string('entity_name', 255);
            $table->text('matter_description'); // Rincian "Dalam Hal"
            $table->integer('sort_order')->default(0);
            $table->timestampsTz();

            $table->foreign('job_description_id')
                ->references('id')
                ->on('sdm.job_descriptions')
                ->onDelete('cascade');
        });

        // 4. Standar Kompetensi Berjenjang (Proficiency Level 1-5 ala Telkom Univ)
        Schema::create('sdm.job_competencies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('job_description_id');
            $table->string('sub_competency', 255);
            $table->smallInteger('proficiency_level')->default(1); // 1 sampai 5
            $table->text('key_indicator');
            $table->integer('sort_order')->default(0);
            $table->timestampsTz();

            $table->foreign('job_description_id')
                ->references('id')
                ->on('sdm.job_descriptions')
                ->onDelete('cascade');
        });

        // 5. Spesifikasi & Kualifikasi (Pendidikan, Pengalaman, Sertifikasi, Hard/Soft Skills)
        Schema::create('sdm.job_specifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('job_description_id')->unique();
            $table->string('education_level', 255);
            $table->string('work_experience', 255);
            $table->string('certifications', 255)->nullable();
            $table->string('trainings', 255)->nullable();
            $table->jsonb('hard_skills')->default('[]');
            $table->jsonb('soft_skills')->default('[]');
            $table->timestampsTz();

            $table->foreign('job_description_id')
                ->references('id')
                ->on('sdm.job_descriptions')
                ->onDelete('cascade');
        });

        // 6. Indikator Keberhasilan (KPI & Standar Kinerja)
        Schema::create('sdm.job_standards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('job_description_id');
            $table->string('indicator_name', 255);
            $table->string('target_value', 255);
            $table->integer('sort_order')->default(0);
            $table->timestampsTz();

            $table->foreign('job_description_id')
                ->references('id')
                ->on('sdm.job_descriptions')
                ->onDelete('cascade');
        });

        // 7. Alur Persetujuan & Validasi Dokumen (Maker, Checker, Approver)
        Schema::create('sdm.job_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('job_description_id');
            $table->string('stage', 30); // maker, checker, approver, hrd_ratification
            $table->string('role_title', 100);
            $table->uuid('approver_user_id')->nullable();
            $table->string('approver_name', 255);
            $table->string('decision', 30)->default('pending'); // pending, approved, revision_requested
            $table->text('notes')->nullable();
            $table->timestampTz('decided_at')->nullable();
            $table->timestampsTz();

            $table->foreign('job_description_id')
                ->references('id')
                ->on('sdm.job_descriptions')
                ->onDelete('cascade');
            $table->index(['job_description_id', 'stage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sdm.job_approvals');
        Schema::dropIfExists('sdm.job_standards');
        Schema::dropIfExists('sdm.job_specifications');
        Schema::dropIfExists('sdm.job_competencies');
        Schema::dropIfExists('sdm.job_working_relations');
        Schema::dropIfExists('sdm.job_governances');
        Schema::dropIfExists('sdm.job_tasks');
    }
};
