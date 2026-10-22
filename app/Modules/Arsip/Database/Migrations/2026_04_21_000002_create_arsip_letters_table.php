<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arsip.letters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('type_id')->index();
            $table->string('document_number', 100)->nullable()->index();
            $table->string('number_suffix', 10)->nullable(); // e.g. 'A', 'B' untuk backdate 104.A
            $table->uuid('origin_unit_id')->index();
            $table->uuid('creator_employee_id')->index();
            $table->string('destination_type', 30)->default('internal_units'); // 'external_text', 'internal_units', 'all_units', 'warehouse', 'doctor'
            $table->string('destination_text', 255)->nullable();              // jika tujuan eksternal pihak luar
            $table->string('subject', 255);                                   // perihal surat
            $table->date('letter_date');                                      // tanggal surat
            $table->string('status', 30)->default('draft');                   // 'draft', 'in_review', 'revision_requested', 'approved', 'signed', 'published', 'archived', 'rejected'
            $table->string('confidentiality', 20)->default('biasa');          // 'biasa', 'terbatas', 'rahasia', 'sangat_rahasia'
            $table->string('urgency', 20)->default('biasa');                  // 'biasa', 'segera', 'sangat_segera'
            $table->jsonb('content_body')->nullable();                        // payload form terstruktur (rincian barang SPB, personil ST, dsb)
            $table->uuid('draft_file_id')->nullable();                        // upload berkas awal
            $table->uuid('signed_file_id')->nullable();                       // upload hasil scan basah paska tanda tangan
            $table->boolean('is_double_upload_complete')->default(true);      // FALSE jika Surat Keluar belum upload final
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->foreign('type_id')->references('id')->on('arsip.letter_types')->cascadeOnDelete();
            $table->foreign('origin_unit_id')->references('id')->on('platform.org_units')->cascadeOnDelete();
            $table->foreign('creator_employee_id')->references('id')->on('platform.employees')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arsip.letters');
    }
};
