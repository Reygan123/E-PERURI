<?php

use App\Modules\Arsip\Database\Seeders\LetterTypeSeeder;
use App\Modules\Arsip\Models\Disposition;
use App\Modules\Arsip\Models\Letter;
use App\Modules\Arsip\Models\LetterApproval;
use App\Modules\Arsip\Models\LetterAttachment;
use App\Modules\Arsip\Models\LetterDestination;
use App\Modules\Arsip\Models\LetterType;
use App\Modules\Platform\Database\Seeders\DevelopmentUserSeeder;
use App\Modules\Platform\Database\Seeders\NumberingFormatSeeder;
use App\Modules\Platform\Database\Seeders\OrgUnitSeeder;
use App\Modules\Platform\Database\Seeders\RoleAndPermissionSeeder;
use App\Modules\Platform\Models\Employee;
use App\Modules\Platform\Models\NumberingFormat;
use App\Modules\Platform\Models\OrgUnit;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => OrgUnitSeeder::class]);
    $this->artisan('db:seed', ['--class' => RoleAndPermissionSeeder::class]);
    $this->artisan('db:seed', ['--class' => DevelopmentUserSeeder::class]);
    $this->artisan('db:seed', ['--class' => NumberingFormatSeeder::class]);
    $this->artisan('db:seed', ['--class' => LetterTypeSeeder::class]);
});

test('arsip schema tables exist in database', function () {
    expect(Schema::hasTable('arsip.letter_types'))->toBeTrue();
    expect(Schema::hasTable('arsip.letters'))->toBeTrue();
    expect(Schema::hasTable('arsip.letter_destinations'))->toBeTrue();
    expect(Schema::hasTable('arsip.letter_approvals'))->toBeTrue();
    expect(Schema::hasTable('arsip.dispositions'))->toBeTrue();
    expect(Schema::hasTable('arsip.letter_attachments'))->toBeTrue();
});

test('letter types are correctly seeded with 10 active and 7 hold types', function () {
    $allTypes = LetterType::all();
    expect($allTypes->count())->toBe(17);

    $activeTypes = LetterType::where('status_group', 'active')->get();
    expect($activeTypes->count())->toBe(10);

    $holdTypes = LetterType::where('status_group', 'hold')->get();
    expect($holdTypes->count())->toBe(7);

    // Verify Surat Keluar (A)
    $suratKeluar = LetterType::where('code', 'A')->first();
    expect($suratKeluar)->not->toBeNull();
    expect($suratKeluar->requires_double_upload)->toBeTrue();
    expect($suratKeluar->signature_type)->toBe('manual');
    expect($suratKeluar->reset_period)->toBe('yearly');

    // Verify SPPD
    $sppd = LetterType::where('code', 'SPPD')->first();
    expect($sppd)->not->toBeNull();
    expect($sppd->reset_period)->toBe('monthly');
    expect($sppd->signature_type)->toBe('hybrid');

    // Verify Surat Masuk (B)
    $suratMasuk = LetterType::where('code', 'B')->first();
    expect($suratMasuk)->not->toBeNull();
    expect($suratMasuk->approval_type)->toBe('none');
    expect($suratMasuk->signature_type)->toBe('none');

    // Verify Memo Direksi (E)
    $memoDir = LetterType::where('code', 'E')->first();
    expect($memoDir)->not->toBeNull();
    expect($memoDir->approval_type)->toBe('memo_dir');
    expect($memoDir->signature_type)->toBe('digital');
});

test('numbering formats for arsip are registered in platform', function () {
    $keluarFormat = NumberingFormat::where('code', 'SURAT_KELUAR')->first();
    expect($keluarFormat)->not->toBeNull();
    expect($keluarFormat->template)->toBe('{SEQ}/{UNIT}/PTKP/{BULAN}/{TAHUN}');
    expect($keluarFormat->module)->toBe('arsip');

    $sppdFormat = NumberingFormat::where('code', 'SURAT_PERINTAH_DINAS')->first();
    expect($sppdFormat)->not->toBeNull();
    expect($sppdFormat->template)->toBe('{SEQ}/{BULAN}/SPD/PTKP/{TAHUN}');
    expect($sppdFormat->reset_period)->toBe('monthly');
});

test('letter model relationships and operations work smoothly', function () {
    $type = LetterType::where('code', 'ND')->firstOrFail();
    $unit = OrgUnit::firstOrFail();
    $employee = Employee::firstOrFail();

    $letter = Letter::create([
        'id' => (string) Str::uuid(),
        'type_id' => $type->id,
        'document_number' => '001/DIR-UT/IV/2026',
        'origin_unit_id' => $unit->id,
        'creator_employee_id' => $employee->id,
        'destination_type' => 'internal_units',
        'subject' => 'Uji Coba Nota Dinas Antar Departemen',
        'letter_date' => now()->toDateString(),
        'status' => 'in_review',
        'confidentiality' => 'biasa',
        'urgency' => 'biasa',
        'is_double_upload_complete' => true,
    ]);

    // Destination
    LetterDestination::create([
        'id' => (string) Str::uuid(),
        'letter_id' => $letter->id,
        'org_unit_id' => $unit->id,
    ]);

    // Approval step
    LetterApproval::create([
        'id' => (string) Str::uuid(),
        'letter_id' => $letter->id,
        'approver_employee_id' => $employee->id,
        'approver_role_title' => 'Kepala Departemen',
        'step_order' => 1,
        'status' => 'pending',
        'action_type' => 'review',
    ]);

    // Disposition
    Disposition::create([
        'id' => (string) Str::uuid(),
        'letter_id' => $letter->id,
        'sender_employee_id' => $employee->id,
        'recipient_employee_id' => $employee->id,
        'instruction' => 'Mohon ditindaklanjuti',
        'status' => 'unread',
    ]);

    // Attachment
    LetterAttachment::create([
        'id' => (string) Str::uuid(),
        'letter_id' => $letter->id,
        'file_id' => (string) Str::uuid(),
        'caption' => 'Lampiran Dokumen',
    ]);

    expect($letter->destinations)->toHaveCount(1);
    expect($letter->approvals)->toHaveCount(1);
    expect($letter->dispositions)->toHaveCount(1);
    expect($letter->attachments)->toHaveCount(1);
    expect($letter->type->code)->toBe('ND');

    // Test soft delete
    $letter->delete();
    expect(Letter::find($letter->id))->toBeNull();
    expect(Letter::withTrashed()->find($letter->id))->not->toBeNull();
});
