<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layanan.work_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ticket_number', 50)->unique();
            $table->string('reference_memo_number', 100)->nullable();
            $table->uuid('requester_id')->index();
            $table->uuid('org_unit_id')->index();
            $table->string('section_name', 150);
            $table->uuid('device_type_id')->index();
            $table->string('device_name', 150);
            $table->string('it_inventory_code', 100)->nullable();
            $table->text('issue_description');
            $table->uuid('photo_file_id')->nullable()->index();
            $table->string('status', 30)->default('pending')->index(); // pending, in_progress, completed, cancelled
            $table->timestampTz('submitted_at')->index();
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->uuid('technician_id')->nullable()->index();
            $table->string('examination_result', 50)->nullable(); // repaired_internally, third_party_vendor, part_replacement, unrepairable
            $table->string('estimated_duration', 100)->nullable();
            $table->text('technician_notes')->nullable();
            $table->uuid('created_by')->index();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->foreign('device_type_id')->references('id')->on('layanan.device_types')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layanan.work_orders');
    }
};
