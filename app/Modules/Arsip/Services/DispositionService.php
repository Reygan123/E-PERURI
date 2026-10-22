<?php

declare(strict_types=1);

namespace App\Modules\Arsip\Services;

use App\Modules\Arsip\Models\Disposition;
use App\Modules\Arsip\Models\Letter;
use App\Modules\Platform\Contracts\AuditServiceInterface;
use App\Modules\Platform\Contracts\NotificationServiceInterface;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DispositionService
{
    public function __construct(
        protected AuditServiceInterface $auditService,
        protected NotificationServiceInterface $notificationService
    ) {}

    /**
     * Create a new disposition for a letter.
     *
     * @param  array{recipient_employee_id?: string|null, recipient_unit_id?: string|null, instruction: string, notes?: string|null, deadline?: string|null}  $data
     */
    public function createDisposition(Letter $letter, string $senderEmployeeId, array $data): Disposition
    {
        return DB::transaction(function () use ($letter, $senderEmployeeId, $data) {
            if (! isset($data['recipient_employee_id']) && ! isset($data['recipient_unit_id'])) {
                throw new DomainException('Disposisi harus memiliki penerima pegawai atau unit.');
            }

            $disposition = Disposition::create([
                'id' => (string) Str::uuid(),
                'letter_id' => $letter->id,
                'sender_employee_id' => $senderEmployeeId,
                'recipient_employee_id' => $data['recipient_employee_id'] ?? null,
                'recipient_unit_id' => $data['recipient_unit_id'] ?? null,
                'instruction' => $data['instruction'],
                'notes' => $data['notes'] ?? null,
                'deadline' => $data['deadline'] ?? null,
                'status' => 'unread',
            ]);

            // Notify recipient if targeted to a specific employee
            if (! empty($data['recipient_employee_id'])) {
                $this->notificationService->send(
                    recipientEmployeeIds: $data['recipient_employee_id'],
                    title: 'Disposisi Surat Masuk',
                    message: "Anda menerima lembar disposisi untuk surat \"{$letter->subject}\": {$data['instruction']}",
                    channels: ['database'],
                    metadata: ['letter_id' => $letter->id, 'disposition_id' => $disposition->id]
                );
            }

            $this->auditService->log(
                module: 'arsip',
                action: 'CREATE_DISPOSITION',
                auditableType: 'Disposition',
                auditableId: (string) $disposition->id,
                newValues: [
                    'letter_id' => $letter->id,
                    'instruction' => $data['instruction'],
                    'recipient_employee_id' => $data['recipient_employee_id'] ?? null,
                ],
                notes: 'Penerbitan lembar disposisi surat'
            );

            return $disposition;
        });
    }

    /**
     * Mark a disposition as read by recipient.
     */
    public function markAsRead(Disposition $disposition, string $actorEmployeeId): Disposition
    {
        if ($disposition->recipient_employee_id && $disposition->recipient_employee_id !== $actorEmployeeId) {
            throw new DomainException('Anda tidak berwenang untuk membaca disposisi ini.');
        }

        if ($disposition->status === 'unread') {
            $disposition->update(['status' => 'read']);
        }

        return $disposition->fresh();
    }

    /**
     * Complete a disposition with closing notes.
     */
    public function completeDisposition(Disposition $disposition, string $actorEmployeeId, ?string $completionNotes = null): Disposition
    {
        return DB::transaction(function () use ($disposition, $actorEmployeeId, $completionNotes) {
            if ($disposition->recipient_employee_id && $disposition->recipient_employee_id !== $actorEmployeeId) {
                throw new DomainException('Anda tidak berwenang untuk menyelesaikan disposisi ini.');
            }

            $disposition->update([
                'status' => 'completed',
                'completed_at' => now(),
                'notes' => $completionNotes ? ($disposition->notes ? $disposition->notes."\n[Selesai]: ".$completionNotes : $completionNotes) : $disposition->notes,
            ]);

            $this->auditService->log(
                module: 'arsip',
                action: 'COMPLETE_DISPOSITION',
                auditableType: 'Disposition',
                auditableId: (string) $disposition->id,
                newValues: ['status' => 'completed'],
                notes: 'Penyelesaian tindak lanjut disposisi'
            );

            return $disposition->fresh();
        });
    }
}
