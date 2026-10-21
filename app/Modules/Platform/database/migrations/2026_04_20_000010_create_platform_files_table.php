<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform.files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('uploader_id')->nullable()->index();
            $table->string('original_name', 255);
            $table->string('stored_name', 255);
            $table->string('disk', 50)->default('local');
            $table->string('path', 500);
            $table->string('mime_type', 100);
            $table->bigInteger('size_bytes');
            $table->string('hash_sha256', 64)->nullable()->index();
            $table->string('entity_type', 100)->nullable()->index();
            $table->uuid('entity_id')->nullable()->index();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->foreign('uploader_id')->references('id')->on('platform.employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform.files');
    }
};
