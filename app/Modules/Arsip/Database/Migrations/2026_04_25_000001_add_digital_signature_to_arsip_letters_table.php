<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arsip.letters', function (Blueprint $table) {
            $table->string('signature_hash', 64)->nullable()->unique();
            $table->timestampTz('signed_at')->nullable()->index();
            $table->uuid('signer_employee_id')->nullable()->index();
            $table->jsonb('signature_metadata')->nullable();

            $table->foreign('signer_employee_id')
                ->references('id')
                ->on('platform.employees')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('arsip.letters', function (Blueprint $table) {
            $table->dropForeign(['signer_employee_id']);
            $table->dropUnique(['signature_hash']);
            $table->dropIndex(['signed_at']);
            $table->dropIndex(['signer_employee_id']);
            $table->dropColumn([
                'signature_hash',
                'signed_at',
                'signer_employee_id',
                'signature_metadata',
            ]);
        });
    }
};
