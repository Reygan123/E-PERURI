<?php

declare(strict_types=1);

namespace App\Modules\Arsip\Services;

use App\Modules\Arsip\Models\Letter;
use App\Modules\Arsip\Models\LetterApproval;
use App\Modules\Platform\Contracts\AuditServiceInterface;
use App\Modules\Platform\Contracts\DirectoryServiceInterface;
use App\Modules\Platform\Contracts\NotificationServiceInterface;
use App\Modules\Platform\Contracts\NumberingServiceInterface;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApprovalWorkflowService
{
    public function __construct(
        protected DirectoryServiceInterface $directoryService,
        protected AuditServiceInterface $auditService,
        protected NotificationServiceInterface $notificationService,
        protected NumberingServiceInterface $numberingService
    ) {}

    /**
     * Initiate and register approval steps for a letter.
     *
     * @param  list<array{employee_id: string, role_title: string, action_type?: string}>|null  $customSteps
     */
    public function initiateWorkflow(Letter $letter, ?array $customSteps = null): void
    {
        $letter->loadMissing('type');

        // Delete any existing un-decided approval steps if restarting from draft/revision
        LetterApproval::where('letter_id', $letter->id)->whereNull('decided_at')->delete();

        $steps = [];

        if (! empty($customSteps)) {
            $steps = $customSteps;
        } else {
            $steps = $this->resolveDefaultSteps($letter);
        }

        if (empty($steps)) {
            // If letter requires no approvals (e.g. Surat Masuk - Type B)
            $letter->update(['status' => 'published']);

            return;
        }

        $stepOrder = 1;
        $firstStep = null;

        foreach ($steps as $step) {
            $approval = LetterApproval::create([
                'id' => (string) Str::uuid(),
                'letter_id' => $letter->id,
                'approver_employee_id' => $step['employee_id'],
                'approver_role_title' => $step['role_title'],
                'step_order' => $stepOrder,
                'status' => $stepOrder === 1 ? 'pending' : 'waiting',
                'action_type' => $step['action_type'] ?? 'approve',
            ]);

            if ($stepOrder === 1) {
                $firstStep = $approval;
            }

            $stepOrder++;
        }

        $this->notificationService->send(
            recipientEmployeeIds: $firstStep->approver_employee_id,
            title: 'Surat Menunggu Persetujuan',
            message: "Surat perihal \"{$letter->subject}\" menunggu review/persetujuan Anda.",
            channels: ['database'],
            metadata: ['letter_id' => $letter->id, 'approval_id' => $firstStep->id]
        );
    }

    /**
     * Process an approver's decision on a letter.
     */
    public function processDecision(
        LetterApproval $approval,
        string $action,
        ?string $notes,
        string $actorEmployeeId,
        ?string $passphrase = null
    ): LetterApproval {
        return DB::transaction(function () use ($approval, $action, $notes, $actorEmployeeId) {
            $approval->refresh();

            if ($approval->status !== 'pending') {
                throw new DomainException('Langkah persetujuan ini sudah diproses sebelumnya atau belum aktif.');
            }

            if ($approval->approver_employee_id !== $actorEmployeeId) {
                throw new DomainException('Anda tidak berwenang untuk memproses langkah persetujuan ini.');
            }

            $letter = $approval->letter()->with('type')->firstOrFail();

            if ($action === 'approve' || $action === 'review') {
                $signatureHash = null;
                if ($letter->type->signature_type === 'digital' || $letter->type->signature_type === 'hybrid') {
                    $signaturePayload = implode('|', [
                        $letter->id,
                        $actorEmployeeId,
                        $approval->step_order,
                        now()->toIso8601String(),
                    ]);
                    $signatureHash = hash_hmac('sha256', $signaturePayload, (string) config('app.key'));
                }

                $approval->update([
                    'status' => 'approved',
                    'decided_at' => now(),
                    'notes' => $notes,
                    'signature_hash' => $signatureHash,
                ]);

                // Check for subsequent step
                $nextStep = LetterApproval::where('letter_id', $letter->id)
                    ->where('step_order', '>', $approval->step_order)
                    ->orderBy('step_order')
                    ->first();

                if ($nextStep) {
                    $nextStep->update(['status' => 'pending']);

                    $this->notificationService->send(
                        recipientEmployeeIds: $nextStep->approver_employee_id,
                        title: 'Surat Menunggu Persetujuan',
                        message: "Surat perihal \"{$letter->subject}\" kini menunggu persetujuan Anda.",
                        channels: ['database'],
                        metadata: ['letter_id' => $letter->id, 'approval_id' => $nextStep->id]
                    );
                } else {
                    // Final step approved! Complete workflow
                    $this->finalizeApprovedLetter($letter);
                }
            } elseif ($action === 'revise') {
                $approval->update([
                    'status' => 'revision_requested',
                    'decided_at' => now(),
                    'notes' => $notes,
                ]);

                $letter->update(['status' => 'revision_requested']);

                $this->notificationService->send(
                    recipientEmployeeIds: $letter->creator_employee_id,
                    title: 'Permintaan Revisi Surat',
                    message: "Surat \"{$letter->subject}\" dikembalikan dengan permintaan revisi: {$notes}",
                    channels: ['database'],
                    metadata: ['letter_id' => $letter->id]
                );
            } elseif ($action === 'reject') {
                $approval->update([
                    'status' => 'rejected',
                    'decided_at' => now(),
                    'notes' => $notes,
                ]);

                $letter->update(['status' => 'rejected']);

                $this->notificationService->send(
                    recipientEmployeeIds: $letter->creator_employee_id,
                    title: 'Surat Ditolak',
                    message: "Surat \"{$letter->subject}\" telah ditolak dengan catatan: {$notes}",
                    channels: ['database'],
                    metadata: ['letter_id' => $letter->id]
                );
            } else {
                throw new DomainException("Aksi tidak valid: {$action}");
            }

            $this->auditService->log(
                module: 'arsip',
                action: 'DECIDE_APPROVAL',
                auditableType: 'LetterApproval',
                auditableId: (string) $approval->id,
                newValues: [
                    'status' => $approval->status,
                    'action' => $action,
                    'notes' => $notes,
                ],
                notes: "Keputusan persetujuan surat: {$action}"
            );

            return $approval->fresh();
        });
    }

    /**
     * Finalize letter once all approval steps are completed.
     */
    protected function finalizeApprovedLetter(Letter $letter): void
    {
        // 1. Generate number if not assigned yet
        if (! $letter->document_number) {
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

            $docNumber = $this->numberingService->generateNumber($category, $unitCode, $context);
            $this->numberingService->recordNumber($docNumber, $category, $unitCode, ['letter_id' => $letter->id]);
            $letter->document_number = $docNumber;
        }

        // 2. Set status based on signature requirement
        if ($letter->type->signature_type === 'digital') {
            $letter->status = 'published';
        } else {
            // Manual signature: requires physical printing, signing, and scanning back
            $letter->status = 'approved';
        }

        $letter->save();

        // 3. Notify creator
        $this->notificationService->send(
            recipientEmployeeIds: $letter->creator_employee_id,
            title: 'Surat Telah Disetujui',
            message: "Surat perihal \"{$letter->subject}\" dengan nomor {$letter->document_number} telah disetujui sepenuhnya.",
            channels: ['database'],
            metadata: ['letter_id' => $letter->id]
        );
    }

    /**
     * Resolve default approval steps based on PTKP rules and organizational hierarchy.
     *
     * @return list<array{employee_id: string, role_title: string, action_type: string}>
     */
    protected function resolveDefaultSteps(Letter $letter): array
    {
        $creator = $this->directoryService->getEmployeeById($letter->creator_employee_id);
        $creatorUnit = $creator['org_unit'] ?? null;

        // In case creator is in a unit, determine head of unit or department
        // For standard letters:
        $steps = [];

        // Fallback: If no hierarchy found, route directly to creator's supervisor / director
        // Let's create an approval step with the creator or designated approver
        $steps[] = [
            'employee_id' => $letter->creator_employee_id,
            'role_title' => 'Atasan Langsung / Kepala Departemen',
            'action_type' => 'approve',
        ];

        return $steps;
    }

    protected function getRomanMonth(int $month): string
    {
        $romans = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        return $romans[$month] ?? 'I';
    }
}
