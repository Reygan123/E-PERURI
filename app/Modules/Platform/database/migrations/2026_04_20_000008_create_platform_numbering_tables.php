<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform.numbering_formats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique(); // misal: surat-keluar, memo
            $table->string('template', 255); // misal: {PREFIX}/{SEQ}/{BULAN}/{TAHUN}
            $table->string('reset_period', 20)->default('yearly'); // never, yearly, monthly
            $table->string('prefix', 50)->nullable();
            $table->string('module', 50)->index();
            $table->timestampsTz();
        });

        Schema::create('platform.numbering_sequences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('format_id')->index();
            $table->string('period_key', 20); // misal: '2026', '2026-10'
            $table->integer('last_sequence')->default(0);
            $table->timestampsTz();

            $table->unique(['format_id', 'period_key']);
            $table->foreign('format_id')->references('id')->on('platform.numbering_formats')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform.numbering_sequences');
        Schema::dropIfExists('platform.numbering_formats');
    }
};
