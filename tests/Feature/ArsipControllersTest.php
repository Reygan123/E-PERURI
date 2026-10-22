<?php

use App\Models\User;
use App\Modules\Arsip\Database\Seeders\LetterTypeSeeder;
use App\Modules\Arsip\Models\Letter;
use App\Modules\Arsip\Models\LetterApproval;
use App\Modules\Arsip\Models\LetterType;
use App\Modules\Platform\Database\Seeders\DevelopmentUserSeeder;
use App\Modules\Platform\Database\Seeders\NumberingFormatSeeder;
use App\Modules\Platform\Database\Seeders\OrgUnitSeeder;
use App\Modules\Platform\Database\Seeders\RoleAndPermissionSeeder;
use App\Modules\Platform\Models\Employee;
use App\Modules\Platform\Models\OrgUnit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => OrgUnitSeeder::class]);
    $this->artisan('db:seed', ['--class' => RoleAndPermissionSeeder::class]);
    $this->artisan('db:seed', ['--class' => DevelopmentUserSeeder::class]);
    $this->artisan('db:seed', ['--class' => NumberingFormatSeeder::class]);
    $this->artisan('db:seed', ['--class' => LetterTypeSeeder::class]);
});

test('surat keluar endpoints work end to end', function () {
    $user = User::whereNotNull('employee_id')->firstOrFail();
    $unit = OrgUnit::firstOrFail();

    // 1. Index
    $response = $this->actingAs($user)->get(route('arsip.surat-keluar.index'));
    $response->assertOk();

    // 2. Create Page
    $response = $this->actingAs($user)->get(route('arsip.surat-keluar.create'));
    $response->assertOk();

    // 3. Store
    $storeResponse = $this->actingAs($user)->post(route('arsip.surat-keluar.store'), [
        'subject' => 'Surat Keluar Uji Sistem',
        'origin_unit_id' => $unit->id,
        'destination_type' => 'external_text',
        'destination_text' => 'PT Peruri Digital Security',
        'letter_date' => now()->toDateString(),
        'confidentiality' => 'biasa',
        'urgency' => 'biasa',
        'draft_file' => UploadedFile::fake()->create('draft.pdf', 100, 'application/pdf'),
    ]);
    $storeResponse->assertRedirect();

    $letter = Letter::where('subject', 'Surat Keluar Uji Sistem')->firstOrFail();
    expect($letter->status)->toBe('draft');
    expect($letter->is_double_upload_complete)->toBeFalse();

    // 4. Show
    $response = $this->actingAs($user)->get(route('arsip.surat-keluar.show', $letter->id));
    $response->assertOk();

    // 5. Submit for approval
    $submitResponse = $this->actingAs($user)->post(route('arsip.surat-keluar.submit', $letter->id));
    $submitResponse->assertRedirect();
    expect($letter->fresh()->status)->toBe('in_review');

    // 6. Upload final signed scan
    $uploadResponse = $this->actingAs($user)->post(route('arsip.surat-keluar.upload-signed', $letter->id), [
        'signed_file' => UploadedFile::fake()->create('signed.pdf', 200, 'application/pdf'),
    ]);
    $uploadResponse->assertRedirect();
    expect($letter->fresh()->is_double_upload_complete)->toBeTrue();
    expect($letter->fresh()->status)->toBe('signed');
});

test('surat masuk endpoints work end to end including disposition', function () {
    $user = User::whereNotNull('employee_id')->firstOrFail();
    $unit = OrgUnit::firstOrFail();

    // 1. Index
    $response = $this->actingAs($user)->get(route('arsip.surat-masuk.index'));
    $response->assertOk();

    // 2. Create Page
    $response = $this->actingAs($user)->get(route('arsip.surat-masuk.create'));
    $response->assertOk();

    // 3. Store Surat Masuk
    $storeResponse = $this->actingAs($user)->post(route('arsip.surat-masuk.store'), [
        'document_number' => '999/KEMENPERIN/IV/2026',
        'origin_unit_id' => $unit->id,
        'sender_organization' => 'Kementerian Perindustrian RI',
        'subject' => 'Surat Koordinasi Sertifikasi Kertas',
        'letter_date' => now()->toDateString(),
        'received_date' => now()->toDateString(),
        'confidentiality' => 'biasa',
        'urgency' => 'segera',
        'scanned_file' => UploadedFile::fake()->create('surat_masuk.pdf', 300, 'application/pdf'),
    ]);
    $storeResponse->assertRedirect();

    $letter = Letter::where('subject', 'Surat Koordinasi Sertifikasi Kertas')->firstOrFail();
    expect($letter->status)->toBe('published');

    // 4. Show Page
    $response = $this->actingAs($user)->get(route('arsip.surat-masuk.show', $letter->id));
    $response->assertOk();

    // 5. Create Disposition
    $targetUnit = OrgUnit::skip(1)->first() ?? $unit;
    $dispResponse = $this->actingAs($user)->post(route('arsip.surat-masuk.disposisi.store', $letter->id), [
        'recipient_unit_id' => $targetUnit->id,
        'instruction' => 'Harap dikoordinasikan dengan tim terkait.',
        'deadline' => now()->addDays(5)->toDateString(),
    ]);
    $dispResponse->assertRedirect();

    expect($letter->fresh()->dispositions)->toHaveCount(1);
});

test('approval workbench and decision endpoints process correctly', function () {
    $user = User::whereNotNull('employee_id')->firstOrFail();
    $employee = Employee::find($user->employee_id);
    expect($employee)->not->toBeNull();

    $unit = OrgUnit::firstOrFail();
    $type = LetterType::where('code', 'ND')->firstOrFail();

    $letter = Letter::create([
        'id' => (string) Str::uuid(),
        'type_id' => $type->id,
        'origin_unit_id' => $unit->id,
        'creator_employee_id' => $employee->id,
        'destination_type' => 'internal_units',
        'subject' => 'Nota Dinas Uji Persetujuan',
        'letter_date' => now()->toDateString(),
        'status' => 'in_review',
        'confidentiality' => 'biasa',
        'urgency' => 'biasa',
        'is_double_upload_complete' => true,
    ]);

    $approval = LetterApproval::create([
        'id' => (string) Str::uuid(),
        'letter_id' => $letter->id,
        'approver_employee_id' => $employee->id,
        'approver_role_title' => 'Kepala Unit',
        'step_order' => 1,
        'status' => 'pending',
        'action_type' => 'approve',
    ]);

    // 1. View Workbench Index
    $response = $this->actingAs($user)->get(route('arsip.persetujuan.index'));
    $response->assertOk();

    // 2. Decide Approve
    $decideResponse = $this->actingAs($user)->post(route('arsip.persetujuan.decide', $approval->id), [
        'action' => 'approve',
        'notes' => 'Disetujui untuk diproses.',
    ]);
    $decideResponse->assertRedirect();

    expect($approval->fresh()->status)->toBe('approved');
    expect($letter->fresh()->status)->toBe('published');
});

test('naskah dinas and formulir khusus endpoints operate properly', function () {
    $user = User::whereNotNull('employee_id')->firstOrFail();
    $unit = OrgUnit::firstOrFail();

    // Naskah Dinas Index & Create
    $response = $this->actingAs($user)->get(route('arsip.naskah-dinas.index'));
    $response->assertOk();

    $response = $this->actingAs($user)->get(route('arsip.naskah-dinas.create'));
    $response->assertOk();

    // Formulir Khusus Index & Create
    $response = $this->actingAs($user)->get(route('arsip.khusus.index'));
    $response->assertOk();

    $response = $this->actingAs($user)->get(route('arsip.khusus.create'));
    $response->assertOk();

    // Store SPPD
    $storeSppd = $this->actingAs($user)->post(route('arsip.khusus.store'), [
        'type_code' => 'SPPD',
        'subject' => 'Dinas ke Bandung Rapat Koordinasi',
        'origin_unit_id' => $unit->id,
        'letter_date' => now()->toDateString(),
        'content_body' => [
            'destination_city' => 'Bandung',
            'departure_date' => now()->toDateString(),
            'return_date' => now()->addDays(2)->toDateString(),
            'purpose' => 'Rapat Koordinasi BUMN',
        ],
    ]);
    $storeSppd->assertRedirect();
});
