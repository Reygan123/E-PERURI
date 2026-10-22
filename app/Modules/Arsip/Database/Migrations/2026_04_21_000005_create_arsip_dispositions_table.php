<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arsip.dispositions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('letter_id')->index();
            $table->uuid('sender_employee_id')->index();                 // pejabat pemberi disposisi
            $table->uuid('recipient_employee_id')->nullable()->index();  // staf/pejabat penerima
            $table->uuid('recipient_unit_id')->nullable()->index();      // unit penerima
            $table->string('instruction', 100);                          // 'Tindak lanjuti', 'Selesaikan', 'Pelajari', 'Arsipkan', etc.
            $table->text('notes')->nullable();                           // catatan khusus dari pimpinan
            $table->date('deadline')->nullable();                        // tenggat waktu tindak lanjut
            $table->string('status', 30)->default('sent');               // 'sent', 'in_progress', 'completed'
            $table->timestampTz('completed_at')->nullable();
            $table->timestampsTz();

            $table->foreign('letter_id')->references('id')->on('arsip.letters')->cascadeOnDelete();
            $table->foreign('sender_employee_id')->references('id')->on('platform.employees')->cascadeOnDelete();
            $table->foreign('recipient_employee_id')->references('id')->on('platform.employees')->nullOnDelete();
            $table->foreign('recipient_unit_id')->references('id')->on('platform.org_units')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arsip.dispositions');
    }
};
