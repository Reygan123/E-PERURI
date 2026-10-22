<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arsip.letter_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('letter_id')->index();
            $table->uuid('approver_employee_id')->index();
            $table->string('approver_role_title', 100);                  // e.g. 'Kepala Seksi', 'Kepala Departemen', 'Direksi', 'Dokter'
            $table->smallInteger('step_order');                           // 1, 2, 3...
            $table->string('status', 30)->default('pending');             // 'pending', 'approved', 'rejected', 'revision_requested'
            $table->string('action_type', 30)->default('review');         // 'review', 'sign_digital', 'sign_manual', 'disposition'
            $table->text('notes')->nullable();                            // catatan review atau revisi
            $table->string('signature_hash', 255)->nullable();            // cryptographic token TTD digital
            $table->timestampTz('decided_at')->nullable();
            $table->timestampsTz();

            $table->foreign('letter_id')->references('id')->on('arsip.letters')->cascadeOnDelete();
            $table->foreign('approver_employee_id')->references('id')->on('platform.employees')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arsip.letter_approvals');
    }
};
