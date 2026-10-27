<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layanan.work_order_actions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('work_order_id')->index();
            $table->uuid('technician_id')->index();
            $table->string('action_type', 50)->index(); // take_ticket, update_diagnosis, vendor_dispatch, finish_repair, cancel
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->foreign('work_order_id')->references('id')->on('layanan.work_orders')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layanan.work_order_actions');
    }
};
