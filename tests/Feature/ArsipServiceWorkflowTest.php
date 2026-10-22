<?php

use App\Modules\Arsip\Database\Seeders\LetterTypeSeeder;
use App\Modules\Arsip\Models\Letter;
use App\Modules\Arsip\Models\LetterType;
use App\Modules\Arsip\Services\ApprovalWorkflowService;
use App\Modules\Arsip\Services\DispositionService;
use App\Modules\Arsip\Services\LetterService;
use App\Modules\Platform\Database\Seeders\DevelopmentUserSeeder;
use App\Modules\Platform\Database\Seeders\NumberingFormatSeeder;
use App\Modules\Platform\Database\Seeders\OrgUnitSeeder;
use App\Modules\Platform\Database\Seeders\RoleAndPermissionSeeder;
use App\Modules\Platform\Models\Employee;
use App\Modules\Platform\Models\OrgUnit;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => OrgUnitSeeder::class]);
    $this->artisan('db:seed', ['--class' => RoleAndPermissionSeeder::class]);
    $this->artisan('db:seed', ['--class' => DevelopmentUserSeeder::class]);
    $this->artisan('db:seed', ['--class' => NumberingFormatSeeder::class]);
    $this->artisan('db:seed', ['--class' => LetterTypeSeeder::class]);
});

test('letter service creates draft and enforces double upload blocking for surat keluar', function () {
    $letterService = app(LetterService::class);
    $creator = Employee::firstOrFail();
    $unit = OrgUnit::firstOrFail();
    $suratKeluarType = LetterType::where('code', 'A')->firstOrFail();

    // 1. Create first Surat Keluar (requires double upload)
    $letter1 = $letterService->createDraft([
        'type_id' => $suratKeluarType->id,
        'origin_unit_id' => $unit->id,
        'subject' => 'Surat Keluar Perdana',
        'destination_type' => 'external_text',
        'destination_text' => 'PT Mitra Sejahtera',
    ], $creator->id);

    expect($letter1)->not->toBeNull();
    expect($letter1->is_double_upload_complete)->toBeFalse();

    // 2. Attempt to create a SECOND Surat Keluar while first is incomplete -> Should fail with DomainException
    expect(fn () => $letterService->createDraft([
        'type_id' => $suratKeluarType->id,
        'origin_unit_id' => $unit->id,
        'subject' => 'Surat Keluar Kedua (Harus Diblokir)',
    ], $creator->id))->toThrow(DomainException::class);

    // 3. Upload signed final scan for letter 1
    $dummyScan = UploadedFile::fake()->create('scan_basah.pdf', 500, 'application/pdf');
    $updatedLetter1 = $letterService->uploadSignedScan($letter1, $dummyScan, $creator->id);
    expect($updatedLetter1->is_double_upload_complete)->toBeTrue();
    expect($updatedLetter1->status)->toBe('signed');

    // 4. Now creating second Surat Keluar should succeed
    $letter2 = $letterService->createDraft([
        'type_id' => $suratKeluarType->id,
        'origin_unit_id' => $unit->id,
        'subject' => 'Surat Keluar Kedua (Berhasil)',
    ], $creator->id);

    expect($letter2)->not->toBeNull();
});

test('letter service generates official document number with backdate suffix support', function () {
    $letterService = app(LetterService::class);
    $creator = Employee::firstOrFail();
    $unit = OrgUnit::where('code', 'DIR-UT')->first() ?? OrgUnit::firstOrFail();
    $type = LetterType::where('code', 'A')->firstOrFail();

    $letter = $letterService->createDraft([
        'type_id' => $type->id,
        'origin_unit_id' => $unit->id,
        'subject' => 'Surat Dengan Nomor Backdate',
    ], $creator->id);

    // Assign number with suffix 'A' (e.g. 001.A/DIR-UT/PTKP/IV/2026)
    $docNumber = $letterService->assignDocumentNumber($letter, 'A');

    expect($docNumber)->toContain('.A/');
    expect($letter->fresh()->document_number)->toBe($docNumber);
    expect($letter->fresh()->number_suffix)->toBe('A');
});

test('approval workflow processes review, revision, and approval decision flow', function () {
    $letterService = app(LetterService::class);
    $workflowService = app(ApprovalWorkflowService::class);
    $creator = Employee::firstOrFail();
    $unit = OrgUnit::firstOrFail();
    $type = LetterType::where('code', 'ND')->firstOrFail();

    $letter = $letterService->createDraft([
        'type_id' => $type->id,
        'origin_unit_id' => $unit->id,
        'subject' => 'Nota Dinas Workflow Test',
    ], $creator->id);

    // Custom 2-step approval: Reviewer -> Final Approver
    $approver1 = Employee::skip(1)->first() ?? $creator;
    $approver2 = Employee::skip(2)->first() ?? $creator;

    $letterService->submitForApproval($letter, $creator->id, [
        [
            'employee_id' => $approver1->id,
            'role_title' => 'Kepala Seksi',
            'action_type' => 'review',
        ],
        [
            'employee_id' => $approver2->id,
            'role_title' => 'Kepala Departemen',
            'action_type' => 'approve',
        ],
    ]);

    $letter->refresh();
    expect($letter->status)->toBe('in_review');
    expect($letter->approvals)->toHaveCount(2);

    $step1 = $letter->approvals->where('step_order', 1)->first();
    $step2 = $letter->approvals->where('step_order', 2)->first();

    expect($step1->status)->toBe('pending');
    expect($step2->status)->toBe('waiting');

    // Step 1 decides: Review approved
    $workflowService->processDecision($step1, 'approve', 'Sesuai, lanjutkan.', $approver1->id);

    expect($step1->fresh()->status)->toBe('approved');
    expect($step2->fresh()->status)->toBe('pending');

    // Step 2 decides: Final Approve & Digital Sign
    $workflowService->processDecision($step2, 'approve', 'Disetujui untuk diterbitkan.', $approver2->id);

    expect($step2->fresh()->status)->toBe('approved');
    expect($step2->fresh()->signature_hash)->not->toBeNull();

    $letter->refresh();
    // Nota dinas is digital signature, so it transitions to published and gets assigned a number
    expect($letter->status)->toBe('published');
    expect($letter->document_number)->not->toBeNull();
});

test('disposition service creates, tracks, and completes disposition flow', function () {
    $letterService = app(LetterService::class);
    $dispositionService = app(DispositionService::class);
    $sender = Employee::firstOrFail();
    $recipient = Employee::skip(1)->first() ?? $sender;
    $unit = OrgUnit::firstOrFail();
    $type = LetterType::where('code', 'B')->firstOrFail(); // Surat Masuk

    $letter = $letterService->createDraft([
        'type_id' => $type->id,
        'origin_unit_id' => $unit->id,
        'subject' => 'Surat Masuk dari Kemenperin',
    ], $sender->id);

    $disposition = $dispositionService->createDisposition($letter, $sender->id, [
        'recipient_employee_id' => $recipient->id,
        'instruction' => 'Tolong buatkan kajian teknis dalam 3 hari kerja.',
        'deadline' => now()->addDays(3)->toDateString(),
    ]);

    expect($disposition)->not->toBeNull();
    expect($disposition->status)->toBe('unread');

    // Recipient marks as read
    $readDisposition = $dispositionService->markAsRead($disposition, $recipient->id);
    expect($readDisposition->status)->toBe('read');

    // Recipient completes disposition
    $completed = $dispositionService->completeDisposition($disposition, $recipient->id, 'Kajian teknis telah selesai.');
    expect($completed->status)->toBe('completed');
    expect($completed->completed_at)->not->toBeNull();
});
