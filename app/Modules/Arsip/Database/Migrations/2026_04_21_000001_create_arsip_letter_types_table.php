<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS arsip');

        Schema::create('arsip.letter_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique(); // e.g. A, B, E, KPTS, PENG, ST, ND, SPB, BP, SPPD, K, SK, KET, PNYT, BA, TAR, IT
            $table->string('name', 100);          // e.g. 'Surat Keluar', 'Surat Masuk', 'Nota Dinas'
            $table->string('numbering_format_code', 50)->nullable();
            $table->string('signature_type', 20)->default('digital'); // 'digital', 'manual', 'hybrid', 'none'
            $table->string('approval_type', 30)->default('standard'); // 'standard', 'memo_dir', 'sppd', 'spb', 'bp', 'sdm_kpts', 'none'
            $table->string('reset_period', 20)->default('yearly');    // 'yearly', 'monthly', 'never'
            $table->boolean('requires_double_upload')->default(false); // true khusus Surat Keluar (A)
            $table->boolean('is_active')->default(true);
            $table->string('status_group', 20)->default('active');     // 'active', 'hold'
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arsip.letter_types');
    }
};
