<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arsip.letter_destinations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('letter_id')->index();
            $table->uuid('org_unit_id')->index();
            $table->timestampTz('received_at')->nullable();
            $table->timestampsTz();

            $table->foreign('letter_id')->references('id')->on('arsip.letters')->cascadeOnDelete();
            $table->foreign('org_unit_id')->references('id')->on('platform.org_units')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arsip.letter_destinations');
    }
};
