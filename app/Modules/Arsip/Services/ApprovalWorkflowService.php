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
        protected NumberingServiceInterface $numberingService,
        protected DigitalSignatureService $digitalSignatureService
    ) {}

    /**
     * Initiate and register approval steps for a letter.
     *
     * @param  list<array{employee_id: string, role_title: string, action_type?: string}>|null  $customSteps
     */
    public function initiateWorkflow(Letter $letter, ?array $customSteps = null): void
    {
        $letter->loadMissing('type');

        // Rule PTKP: Jika ada revisi, alur diulang dari bawah (langkah 1)
        LetterApproval::where('letter_id', $letter->id)->delete();

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
            title: 'Naskah Dinas Menunggu Persetujuan',
            message: "Surat perihal \"{$letter->subject}\" menunggu persetujuan/paraf Anda.",
            channels: $this->getDispatchChannels(),
            metadata: [
                'type' => 'approval_required',
                'letter_id' => $letter->id,
                'approval_id' => $firstStep->id,
                'letter_type' => $letter->type->name ?? 'Naskah Dinas',
                'subject' => $letter->subject,
                'urgency' => $letter->urgency,
                'action_url' => url('/arsip/persetujuan'),
            ]
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
                        title: 'Naskah Dinas Menunggu Persetujuan',
                        message: "Surat perihal \"{$letter->subject}\" kini menunggu persetujuan Anda.",
                        channels: $this->getDispatchChannels(),
                        metadata: [
                            'type' => 'approval_required',
                            'letter_id' => $letter->id,
                            'approval_id' => $nextStep->id,
                            'letter_type' => $letter->type->name ?? 'Naskah Dinas',
                            'subject' => $letter->subject,
                            'urgency' => $letter->urgency,
                            'action_url' => url('/arsip/persetujuan'),
                        ]
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
                    title: 'Permintaan Revisi Naskah Dinas',
                    message: "Surat \"{$letter->subject}\" dikembalikan dengan permintaan revisi: {$notes}",
                    channels: $this->getDispatchChannels(),
                    metadata: [
                        'type' => 'revision_requested',
                        'letter_id' => $letter->id,
                        'letter_type' => $letter->type->name ?? 'Naskah Dinas',
                        'subject' => $letter->subject,
                        'notes' => $notes,
                        'action_url' => url("/arsip/surat-keluar/{$letter->id}"),
                    ]
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
                    title: 'Naskah Dinas Ditolak',
                    message: "Surat \"{$letter->subject}\" telah ditolak dengan catatan: {$notes}",
                    channels: $this->getDispatchChannels(),
                    metadata: [
                        'type' => 'letter_rejected',
                        'letter_id' => $letter->id,
                        'letter_type' => $letter->type->name ?? 'Naskah Dinas',
                        'subject' => $letter->subject,
                        'notes' => $notes,
                        'action_url' => url("/arsip/surat-keluar/{$letter->id}"),
                    ]
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

        // 2. Set status based on signature requirement & sign digitally if required
        if ($letter->type->signature_type === 'digital') {
            $lastApproval = $letter->approvals()->where('status', 'approved')->latest('decided_at')->first();
            $signerEmployeeId = $lastApproval ? $lastApproval->approver_employee_id : $letter->creator_employee_id;

            $this->digitalSignatureService->signDocument($letter, $signerEmployeeId);
        } else {
            // Manual signature: requires physical printing, signing, and scanning back
            $letter->status = 'approved';
            $letter->save();
        }

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
        $creatorUnitId = $letter->origin_unit_id ?: ($creator['org_unit']['id'] ?? null);
        $creatorUnit = $creatorUnitId ? $this->directoryService->getOrgUnit($creatorUnitId) : null;
        $typeCode = $letter->type->code;

        // 1. Surat Masuk (B): No approvals needed
        if ($typeCode === 'B') {
            return [];
        }

        $steps = [];

        // 2. Surat Memo (E): Sekretaris -> VP SPPS -> Direksi
        if ($typeCode === 'E') {
            $sppsLeader = $this->directoryService->getLeaderEmployeeOfUnit('SEK-PPS');
            if ($sppsLeader && $sppsLeader['id'] !== $letter->creator_employee_id) {
                $steps[] = [
                    'employee_id' => $sppsLeader['id'],
                    'role_title' => 'Kepala Sekretariat Perusahaan (SPPS)',
                    'action_type' => 'approve',
                ];
            }

            $dirut = $this->directoryService->getLeaderEmployeeOfUnit('DIR-UTM');
            if ($dirut) {
                $steps[] = [
                    'employee_id' => $dirut['id'],
                    'role_title' => 'Direktur Utama',
                    'action_type' => 'approve',
                ];
            }

            return $steps;
        }

        // 3. Biaya Pengobatan (BP): Dokter Perusahaan / Bagian SDM
        if ($typeCode === 'BP') {
            $sdmLeader = $this->directoryService->getLeaderEmployeeOfUnit('SDM-OPR')
                ?? $this->directoryService->getLeaderEmployeeOfUnit('DEP-SDM');
            if ($sdmLeader) {
                $steps[] = [
                    'employee_id' => $sdmLeader['id'],
                    'role_title' => 'Dokter Perusahaan / Ka. Pelayanan SDM',
                    'action_type' => 'approve',
                ];
            }

            return ! empty($steps) ? $steps : [[
                'employee_id' => $letter->creator_employee_id,
                'role_title' => 'Dokter Perusahaan (PTKP)',
                'action_type' => 'approve',
            ]];
        }

        // 4. SPB (Permintaan Barang Gudang): Kepala Departemen Pemohon
        if ($typeCode === 'SPB') {
            $deptLeader = $this->resolveDepartmentLeader($creatorUnit);
            if ($deptLeader && $deptLeader['id'] !== $letter->creator_employee_id) {
                $steps[] = [
                    'employee_id' => $deptLeader['id'],
                    'role_title' => 'Kepala Departemen Pengaju',
                    'action_type' => 'approve',
                ];
            } else {
                $dirut = $this->directoryService->getLeaderEmployeeOfUnit('DIR-UTM');
                if ($dirut && $dirut['id'] !== $letter->creator_employee_id) {
                    $steps[] = [
                        'employee_id' => $dirut['id'],
                        'role_title' => 'Direktur Utama',
                        'action_type' => 'approve',
                    ];
                }
            }

            return ! empty($steps) ? $steps : [[
                'employee_id' => $letter->creator_employee_id,
                'role_title' => 'Kepala Departemen Pengaju',
                'action_type' => 'approve',
            ]];
        }

        // 5. Standard Persuratan (Surat Keluar, Nota Dinas, Surat Tugas, Pengumuman, KPTS, SPPD)
        if ($creatorUnit && ($creatorUnit['level'] ?? 0) === 3) {
            // Pembuat berada di Seksi: Staf -> Ka. Seksi -> Ka. Dep
            $sectionLeader = $this->directoryService->getLeaderEmployeeOfUnit($creatorUnit['code']);
            if ($sectionLeader && $sectionLeader['id'] !== $letter->creator_employee_id) {
                $steps[] = [
                    'employee_id' => $sectionLeader['id'],
                    'role_title' => 'Kepala Seksi / Atasan Langsung',
                    'action_type' => 'review',
                ];
            }

            $deptLeader = $this->resolveDepartmentLeader($creatorUnit);
            if ($deptLeader && $deptLeader['id'] !== $letter->creator_employee_id) {
                $steps[] = [
                    'employee_id' => $deptLeader['id'],
                    'role_title' => 'Kepala Departemen',
                    'action_type' => 'approve',
                ];
            }
        } elseif ($creatorUnit && ($creatorUnit['level'] ?? 0) === 2) {
            // Pembuat berada di Departemen: dimulai dari Kepala Departemen
            $deptLeader = $this->directoryService->getLeaderEmployeeOfUnit($creatorUnit['code']);
            if ($deptLeader && $deptLeader['id'] !== $letter->creator_employee_id) {
                $steps[] = [
                    'employee_id' => $deptLeader['id'],
                    'role_title' => 'Kepala Departemen',
                    'action_type' => 'approve',
                ];
            }
        }

        // Rule POH Akuntansi: Bila Departemen Akuntansi (DEP-AKT) kosong, eskalasi ke Direksi
        $deptCode = $creatorUnit['code'] ?? ($creatorUnit['parent_code'] ?? '');
        if ($deptCode === 'DEP-AKT') {
            $dirkeu = $this->directoryService->getLeaderEmployeeOfUnit('DIR-KEU')
                ?? $this->directoryService->getLeaderEmployeeOfUnit('DIR-UTM');
            if ($dirkeu && $dirkeu['id'] !== $letter->creator_employee_id && ! collect($steps)->contains('employee_id', $dirkeu['id'])) {
                $steps[] = [
                    'employee_id' => $dirkeu['id'],
                    'role_title' => 'Direksi (POH Akuntansi)',
                    'action_type' => 'approve',
                ];
            }
        }

        // Step Direksi untuk KPTS, SPPD, ND (disposisi), dan Surat Tugas Direksi
        $needsDireksi = in_array($typeCode, ['KPTS', 'SPPD', 'ND'], true)
            || ($typeCode === 'ST' && str_contains($creatorUnit['code'] ?? '', 'DIR'));

        if ($needsDireksi) {
            $dirut = $this->directoryService->getLeaderEmployeeOfUnit('DIR-UTM');
            if ($dirut && $dirut['id'] !== $letter->creator_employee_id && ! collect($steps)->contains('employee_id', $dirut['id'])) {
                $steps[] = [
                    'employee_id' => $dirut['id'],
                    'role_title' => $typeCode === 'ND' ? 'Direktur Utama (Disposisi / Catatan)' : 'Direktur Utama',
                    'action_type' => 'approve',
                ];
            }
        }

        // Fallback jika belum ada langkah (misal surat dibuat Direktur Utama sendiri)
        if (empty($steps)) {
            $dirut = $this->directoryService->getLeaderEmployeeOfUnit('DIR-UTM');
            $steps[] = [
                'employee_id' => $dirut['id'] ?? $letter->creator_employee_id,
                'role_title' => 'Atasan Langsung / Direksi',
                'action_type' => 'approve',
            ];
        }

        return $steps;
    }

    /**
     * Resolve parent department leader for a given unit.
     *
     * @param  array<string, mixed>|null  $unit
     * @return array<string, mixed>|null
     */
    protected function resolveDepartmentLeader(?array $unit): ?array
    {
        if (! $unit) {
            return null;
        }

        if (($unit['level'] ?? 0) === 2) {
            return $this->directoryService->getLeaderEmployeeOfUnit($unit['code']);
        }

        if (! empty($unit['parent_id'])) {
            $parent = $this->directoryService->getOrgUnit($unit['parent_id']);
            if ($parent && ($parent['level'] ?? 0) === 2) {
                return $this->directoryService->getLeaderEmployeeOfUnit($parent['code']);
            }
        }

        return null;
    }

    protected function getRomanMonth(int $month): string
    {
        $romans = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        return $romans[$month] ?? 'I';
    }

    /**
     * @return list<string>
     */
    protected function getDispatchChannels(): array
    {
        $channels = config('services.notifications.channels', ['database', 'mail', 'whatsapp']);

        /** @var list<string> */
        return is_array($channels) ? array_values($channels) : ['database'];
    }
}
