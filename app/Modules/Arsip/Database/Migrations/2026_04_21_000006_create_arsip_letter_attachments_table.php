<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arsip.letter_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('letter_id')->index();
            $table->uuid('file_id')->index();
            $table->string('caption', 255)->nullable();
            $table->timestampsTz();

            $table->foreign('letter_id')->references('id')->on('arsip.letters')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arsip.letter_attachments');
    }
};
