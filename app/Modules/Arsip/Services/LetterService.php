<?php

declare(strict_types=1);

namespace App\Modules\Arsip\Services;

use App\Modules\Arsip\Models\Letter;
use App\Modules\Arsip\Models\LetterAttachment;
use App\Modules\Arsip\Models\LetterDestination;
use App\Modules\Arsip\Models\LetterType;
use App\Modules\Platform\Contracts\AuditServiceInterface;
use App\Modules\Platform\Contracts\DirectoryServiceInterface;
use App\Modules\Platform\Contracts\FilesServiceInterface;
use App\Modules\Platform\Contracts\NumberingServiceInterface;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LetterService
{
    public function __construct(
        protected NumberingServiceInterface $numberingService,
        protected DirectoryServiceInterface $directoryService,
        protected FilesServiceInterface $filesService,
        protected AuditServiceInterface $auditService,
        protected ApprovalWorkflowService $workflowService,
        protected LetterPdfService $letterPdfService
    ) {}

    /**
     * Create a new draft letter with validation against PTKP operational rules.
     *
     * @param  array<string, mixed>  $data
     * @param  list<string>  $destinationUnitIds
     * @param  list<UploadedFile>  $additionalAttachments
     */
    public function createDraft(
        array $data,
        string $creatorEmployeeId,
        ?UploadedFile $draftFile = null,
        array $destinationUnitIds = [],
        array $additionalAttachments = []
    ): Letter {
        return DB::transaction(function () use ($data, $creatorEmployeeId, $draftFile, $destinationUnitIds, $additionalAttachments) {
            $type = LetterType::where('id', (string) $data['type_id'])->firstOrFail();

            // Rule PTKP: Double Upload Check for Surat Keluar (Code: A)
            if ($type->requires_double_upload) {
                $hasIncompleteSuratKeluar = Letter::where('creator_employee_id', $creatorEmployeeId)
                    ->where('type_id', $type->id)
                    ->where('is_double_upload_complete', false)
                    ->exists();

                if ($hasIncompleteSuratKeluar) {
                    throw new DomainException(
                        'Pembuatan Surat Keluar baru ditolak: Anda masih memiliki Surat Keluar sebelumnya yang belum mengunggah berkas scan basah final. Harap selesaikan unggahan terlebih dahulu.'
                    );
                }
            }

            // 1. Upload initial draft file if manually provided
            $draftFileId = null;
            if ($draftFile) {
                $fileMetadata = $this->filesService->store($draftFile, 'arsip', 'draft_letter', $creatorEmployeeId);
                $draftFileId = $fileMetadata['id'];
            }

            // 2. Persist Letter record
            $letterId = (string) Str::uuid();
            $letter = Letter::create([
                'id' => $letterId,
                'type_id' => $type->id,
                'origin_unit_id' => $data['origin_unit_id'],
                'creator_employee_id' => $creatorEmployeeId,
                'destination_type' => $data['destination_type'] ?? 'internal_units',
                'destination_text' => $data['destination_text'] ?? null,
                'subject' => $data['subject'],
                'letter_date' => $data['letter_date'] ?? now()->toDateString(),
                'status' => 'draft',
                'confidentiality' => $data['confidentiality'] ?? 'biasa',
                'urgency' => $data['urgency'] ?? 'biasa',
                'content_body' => $data['content_body'] ?? null,
                'draft_file_id' => $draftFileId,
                'is_double_upload_complete' => ! $type->requires_double_upload,
            ]);

            // Auto-generate digital draft PDF if no manual file provided (except Surat Masuk)
            if (! $draftFileId && $type->code !== 'B') {
                $pdfMeta = $this->letterPdfService->generateAndStoreDraftPdf($letter, $creatorEmployeeId);
                $letter->update(['draft_file_id' => $pdfMeta['id']]);
            }

            // 3. Create destinations
            foreach ($destinationUnitIds as $targetUnitId) {
                LetterDestination::create([
                    'id' => (string) Str::uuid(),
                    'letter_id' => $letter->id,
                    'org_unit_id' => $targetUnitId,
                ]);
            }

            // 4. Attach additional files
            foreach ($additionalAttachments as $attachment) {
                $meta = $this->filesService->store($attachment, 'arsip', 'lampiran_surat', $creatorEmployeeId);
                LetterAttachment::create([
                    'id' => (string) Str::uuid(),
                    'letter_id' => $letter->id,
                    'file_id' => $meta['id'],
                    'caption' => $attachment->getClientOriginalName(),
                ]);
            }

            // 5. Audit Log
            $this->auditService->log(
                module: 'arsip',
                action: 'CREATE_DRAFT',
                auditableType: 'Letter',
                auditableId: (string) $letter->id,
                newValues: [
                    'subject' => $letter->subject,
                    'type_id' => $letter->type_id,
                    'origin_unit_id' => $letter->origin_unit_id,
                    'status' => $letter->status,
                ],
                notes: 'Pembuatan draf surat baru'
            );

            return $letter->load(['type', 'destinations', 'attachments']);
        });
    }

    /**
     * Update an existing draft or revision-requested letter.
     *
     * @param  array<string, mixed>  $data
     * @param  list<string>  $destinationUnitIds
     * @param  list<UploadedFile>  $additionalAttachments
     */
    public function updateDraft(
        Letter $letter,
        array $data,
        string $actorEmployeeId,
        ?UploadedFile $draftFile = null,
        array $destinationUnitIds = [],
        array $additionalAttachments = []
    ): Letter {
        return DB::transaction(function () use ($letter, $data, $actorEmployeeId, $draftFile, $destinationUnitIds, $additionalAttachments) {
            if ($letter->creator_employee_id !== $actorEmployeeId) {
                throw new DomainException('Hanya pembuat naskah dinas yang berwenang mengubah dokumen ini.');
            }

            if (! in_array($letter->status, ['draft', 'revision_requested'], true)) {
                throw new DomainException('Dokumen ini sedang diproses atau telah disetujui, dan tidak dapat diubah lagi.');
            }

            // 1. Upload new draft file if provided
            if ($draftFile) {
                $fileMetadata = $this->filesService->store($draftFile, 'arsip', 'draft_letter', $actorEmployeeId);
                $letter->draft_file_id = $fileMetadata['id'];
            }

            // 2. Update basic fields
            $letter->subject = $data['subject'] ?? $letter->subject;
            $letter->letter_date = $data['letter_date'] ?? $letter->letter_date;
            $letter->confidentiality = $data['confidentiality'] ?? $letter->confidentiality;
            $letter->urgency = $data['urgency'] ?? $letter->urgency;
            if (isset($data['content_body'])) {
                $letter->content_body = $data['content_body'];
            }
            if (isset($data['destination_text'])) {
                $letter->destination_text = $data['destination_text'];
            }
            $letter->save();

            // 3. Update destinations if provided
            if (! empty($destinationUnitIds)) {
                LetterDestination::where('letter_id', $letter->id)->delete();
                foreach ($destinationUnitIds as $targetUnitId) {
                    LetterDestination::create([
                        'id' => (string) Str::uuid(),
                        'letter_id' => $letter->id,
                        'org_unit_id' => $targetUnitId,
                    ]);
                }
            }

            // 4. Regenerate PDF draft if digital document
            $letter->loadMissing('type');
            if (! $draftFile && $letter->type->code !== 'B') {
                $pdfMeta = $this->letterPdfService->generateAndStoreDraftPdf($letter, $actorEmployeeId);
                $letter->update(['draft_file_id' => $pdfMeta['id']]);
            }

            // 5. Store additional attachments
            foreach ($additionalAttachments as $attachment) {
                $attMeta = $this->filesService->store($attachment, 'arsip', 'letter_attachment', $actorEmployeeId);
                LetterAttachment::create([
                    'id' => (string) Str::uuid(),
                    'letter_id' => $letter->id,
                    'file_id' => $attMeta['id'],
                    'title' => $attachment->getClientOriginalName(),
                ]);
            }

            $this->auditService->log(
                module: 'arsip',
                action: 'UPDATE_DRAFT',
                auditableType: 'Letter',
                auditableId: (string) $letter->id,
                newValues: [
                    'subject' => $letter->subject,
                    'status' => $letter->status,
                ],
                notes: 'Pembaruan isi draf surat oleh pembuat'
            );

            return $letter->load(['type', 'destinations', 'attachments']);
        });
    }

    /**
     * Submit draft letter into review and approval workflow.
     *
     * @param  list<array{employee_id: string, role_title: string, action_type?: string}>|null  $customApproverSteps
     */
    public function submitForApproval(Letter $letter, string $actorEmployeeId, ?array $customApproverSteps = null): Letter
    {
        return DB::transaction(function () use ($letter, $actorEmployeeId, $customApproverSteps) {
            if ($letter->status !== 'draft' && $letter->status !== 'revision_requested') {
                throw new DomainException('Hanya surat berstatus draf atau revisi yang dapat diajukan persetujuan.');
            }

            // Generate or advance workflow steps
            $this->workflowService->initiateWorkflow($letter, $customApproverSteps);

            $letter->update(['status' => 'in_review']);

            $this->auditService->log(
                module: 'arsip',
                action: 'SUBMIT_APPROVAL',
                auditableType: 'Letter',
                auditableId: (string) $letter->id,
                notes: 'Pengajuan surat ke alur review dan persetujuan oleh pegawai '.$actorEmployeeId
            );

            return $letter->fresh(['type', 'approvals']);
        });
    }

    /**
     * Generate sequential document number using NumberingService.
     */
    public function assignDocumentNumber(Letter $letter, ?string $backdateSuffix = null): string
    {
        return DB::transaction(function () use ($letter, $backdateSuffix) {
            $letter->loadMissing('type');

            $unitInfo = $this->directoryService->getOrgUnit($letter->origin_unit_id);
            $unitCode = $unitInfo['code'] ?? 'PTKP';

            $date = Carbon::parse($letter->letter_date);
            $category = $letter->type->numbering_format_code ?? 'SURAT_KELUAR';

            $context = [
                'month' => $date->format('m'),
                'year' => $date->format('Y'),
                'BULAN' => $this->getRomanMonth((int) $date->format('n')),
                'TAHUN' => $date->format('Y'),
                'UNIT' => $unitCode,
            ];

            // Generate atomic number
            $docNumber = $this->numberingService->generateNumber($category, $unitCode, $context);

            // Handle backdate suffix if supplied (e.g. 104.A)
            if ($backdateSuffix) {
                $parts = explode('/', $docNumber);
                $parts[0] .= '.'.trim($backdateSuffix);
                $docNumber = implode('/', $parts);
            }

            // Record committed number
            $this->numberingService->recordNumber($docNumber, $category, $unitCode, [
                'letter_id' => $letter->id,
                'type' => $letter->type->code,
                'subject' => $letter->subject,
            ]);

            $letter->update([
                'document_number' => $docNumber,
                'number_suffix' => $backdateSuffix,
            ]);

            $this->auditService->log(
                module: 'arsip',
                action: 'ASSIGN_NUMBER',
                auditableType: 'Letter',
                auditableId: (string) $letter->id,
                newValues: ['document_number' => $docNumber],
                notes: 'Penerbitan nomor surat resmi'
            );

            return $docNumber;
        });
    }

    /**
     * Upload signed scan file to complete double upload requirement.
     */
    public function uploadSignedScan(Letter $letter, UploadedFile $signedFile, string $employeeId): Letter
    {
        return DB::transaction(function () use ($letter, $signedFile, $employeeId) {
            $fileMeta = $this->filesService->store($signedFile, 'arsip', 'signed_letter', $employeeId);

            $letter->update([
                'signed_file_id' => $fileMeta['id'],
                'is_double_upload_complete' => true,
                'status' => 'signed',
            ]);

            $this->auditService->log(
                module: 'arsip',
                action: 'UPLOAD_SIGNED_SCAN',
                auditableType: 'Letter',
                auditableId: (string) $letter->id,
                newValues: [
                    'signed_file_id' => $fileMeta['id'],
                    'is_double_upload_complete' => true,
                    'status' => 'signed',
                ],
                notes: 'Pengunggahan berkas scan basah final'
            );

            return $letter->fresh();
        });
    }

    /**
     * Regenerate draft PDF (e.g. after updating content_body or for preview).
     */
    public function regenerateDraftPdf(Letter $letter): Letter
    {
        return DB::transaction(function () use ($letter) {
            $pdfMeta = $this->letterPdfService->generateAndStoreDraftPdf($letter);
            $letter->update(['draft_file_id' => $pdfMeta['id']]);

            $this->auditService->log(
                module: 'arsip',
                action: 'REGENERATE_DRAFT_PDF',
                auditableType: 'Letter',
                auditableId: (string) $letter->id,
                newValues: ['draft_file_id' => $pdfMeta['id']],
                notes: 'Pembaruan otomatis dokumen PDF draf surat'
            );

            return $letter->fresh();
        });
    }

    /**
     * Helper to convert numeric month (1-12) to Roman numeral.
     */
    protected function getRomanMonth(int $month): string
    {
        $romans = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        return $romans[$month] ?? 'I';
    }
}
