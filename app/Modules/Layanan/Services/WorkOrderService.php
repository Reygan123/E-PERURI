<?php

declare(strict_types=1);

namespace App\Modules\Layanan\Services;

use App\Models\User;
use App\Modules\Layanan\Models\WorkOrder;
use App\Modules\Layanan\Models\WorkOrderAction;
use App\Modules\Platform\Contracts\AuditServiceInterface;
use App\Modules\Platform\Contracts\DirectoryServiceInterface;
use App\Modules\Platform\Contracts\FilesServiceInterface;
use App\Modules\Platform\Contracts\NotificationServiceInterface;
use App\Modules\Platform\Contracts\NumberingServiceInterface;
use App\Modules\Platform\Contracts\UserResolverInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkOrderService
{
    public function __construct(
        protected DirectoryServiceInterface $directory,
        protected NumberingServiceInterface $numbering,
        protected AuditServiceInterface $audit,
        protected UserResolverInterface $userResolver,
        protected FilesServiceInterface $files,
        protected NotificationServiceInterface $notifications
    ) {}

    /**
     * Get paginated work orders with filters and scoping.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, WorkOrder>
     */
    public function getWorkOrdersPaginated(array $filters, User $user, int $perPage = 10): LengthAwarePaginator
    {
        $query = WorkOrder::with(['deviceType'])
            ->orderBy('submitted_at', 'desc');

        // Scoping: non-privileged users only see their own tickets or their unit's tickets
        $isPrivileged = $this->userResolver->hasRole('super-admin', $user)
            || $this->userResolver->hasRole('admin-it', $user)
            || $this->userResolver->hasRole('teknisi-it', $user);

        if (! $isPrivileged) {
            $userUnitId = $this->getUserOrgUnitId($user);
            $query->where(function ($q) use ($user, $userUnitId) {
                $q->where('created_by', $user->id);
                if ($userUnitId) {
                    $q->orWhere('org_unit_id', $userUnitId);
                }
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', (string) $filters['status']);
        }

        if (! empty($filters['device_type_id'])) {
            $query->where('device_type_id', (string) $filters['device_type_id']);
        }

        if (! empty($filters['org_unit_id'])) {
            $query->where('org_unit_id', (string) $filters['org_unit_id']);
        }

        if (! empty($filters['search'])) {
            $search = '%'.(string) $filters['search'].'%';
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'ilike', $search)
                    ->orWhere('device_name', 'ilike', $search)
                    ->orWhere('it_inventory_code', 'ilike', $search)
                    ->orWhere('issue_description', 'ilike', $search)
                    ->orWhere('section_name', 'ilike', $search);
            });
        }

        /** @var LengthAwarePaginator<int, WorkOrder> $paginator */
        $paginator = $query->paginate($perPage);

        return $paginator;
    }

    /**
     * Get detailed ticket data with enriched requester and technician information.
     *
     * @return array<string, mixed>|null
     */
    public function getTicketDetail(string $id): ?array
    {
        $wo = WorkOrder::with(['deviceType', 'actions'])->find($id);
        if (! $wo) {
            return null;
        }

        $requester = $this->directory->getEmployeeById($wo->requester_id);
        $technician = $wo->technician_id ? $this->directory->getEmployeeById($wo->technician_id) : null;
        $unit = $this->directory->getOrgUnit($wo->org_unit_id);

        return [
            'id' => $wo->id,
            'ticket_number' => $wo->ticket_number,
            'reference_memo_number' => $wo->reference_memo_number,
            'status' => $wo->status,
            'submitted_at' => $wo->submitted_at->toIso8601String(),
            'started_at' => $wo->started_at?->toIso8601String(),
            'completed_at' => $wo->completed_at?->toIso8601String(),
            'device_type' => [
                'id' => $wo->deviceType->id,
                'name' => $wo->deviceType->name,
                'code' => $wo->deviceType->code,
            ],
            'device_name' => $wo->device_name,
            'it_inventory_code' => $wo->it_inventory_code,
            'issue_description' => $wo->issue_description,
            'photo_file_id' => $wo->photo_file_id,
            'section_name' => $wo->section_name,
            'examination_result' => $wo->examination_result,
            'estimated_duration' => $wo->estimated_duration,
            'technician_notes' => $wo->technician_notes,
            'created_by' => $wo->created_by,
            'requester' => [
                'id' => $wo->requester_id,
                'name' => is_array($requester) ? ($requester['name'] ?? $requester['full_name'] ?? 'Pegawai') : 'Pegawai',
                'nip' => is_array($requester) ? ($requester['nip'] ?? '-') : '-',
                'email' => is_array($requester) ? ($requester['email'] ?? '-') : '-',
                'org_unit_name' => is_array($unit) ? ($unit['name'] ?? 'Unit Kerja') : 'Unit Kerja',
                'org_unit_code' => is_array($unit) ? ($unit['code'] ?? '-') : '-',
            ],
            'technician' => $technician ? [
                'id' => $wo->technician_id,
                'name' => $technician['name'] ?? $technician['full_name'] ?? 'Teknisi IT',
                'nip' => $technician['nip'] ?? '-',
            ] : null,
            'actions' => $wo->actions->map(fn (WorkOrderAction $a) => [
                'id' => $a->id,
                'action_type' => $a->action_type,
                'notes' => $a->notes,
                'created_at' => $a->created_at?->toIso8601String(),
            ]),
        ];
    }

    /**
     * Get aggregate statistics for work orders metrics.
     *
     * @return array{total: int, pending: int, in_progress: int, completed: int}
     */
    public function getMetrics(User $user): array
    {
        $query = WorkOrder::query();

        $isPrivileged = $this->userResolver->hasRole('super-admin', $user)
            || $this->userResolver->hasRole('admin-it', $user)
            || $this->userResolver->hasRole('teknisi-it', $user);

        if (! $isPrivileged) {
            $userUnitId = $this->getUserOrgUnitId($user);
            $query->where(function ($q) use ($user, $userUnitId) {
                $q->where('created_by', $user->id);
                if ($userUnitId) {
                    $q->orWhere('org_unit_id', $userUnitId);
                }
            });
        }

        return [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
        ];
    }

    /**
     * Get storage path for a file record ID.
     */
    public function getPhotoPath(string $fileId): ?string
    {
        return $this->files->getPath($fileId);
    }

    /**
     * Store new work order submitted by user.
     *
     * @param  array<string, mixed>  $data
     */
    public function storeWorkOrder(array $data, User $user, ?UploadedFile $photo = null): WorkOrder
    {
        return DB::transaction(function () use ($data, $user, $photo) {
            $employeeId = $user->employee_id ?? $this->userResolver->getCurrentEmployeeId();
            $userUnitId = $this->getUserOrgUnitId($user);

            // Handle photo upload if present
            $photoFileId = null;
            if ($photo) {
                $fileMeta = $this->files->store($photo, 'layanan', 'kerusakan_it', $employeeId);
                $photoFileId = $fileMeta['id'] ?? null;
            }

            // Generate atomic ticket number (e.g. IT001, IT002) with automatic collision resolution
            do {
                $ticketNumber = $this->numbering->generateNumber('WO_IT', 'IT', [
                    'module' => 'layanan',
                    'padding' => 3,
                ]);
            } while (WorkOrder::where('ticket_number', $ticketNumber)->exists());

            $fallbackOrgUnitId = (string) (DB::table('platform.org_units')->value('id') ?? Str::uuid());

            $workOrder = WorkOrder::create([
                'ticket_number' => $ticketNumber,
                'reference_memo_number' => $data['reference_memo_number'] ?? null,
                'requester_id' => $employeeId ?? (string) Str::uuid(),
                'org_unit_id' => $userUnitId ?? $data['org_unit_id'] ?? $fallbackOrgUnitId,
                'section_name' => $data['section_name'],
                'device_type_id' => $data['device_type_id'],
                'device_name' => $data['device_name'],
                'it_inventory_code' => $data['it_inventory_code'] ?? null,
                'issue_description' => $data['issue_description'],
                'photo_file_id' => $photoFileId ?? ($data['photo_file_id'] ?? null),
                'status' => 'pending',
                'submitted_at' => Carbon::now(),
                'created_by' => $user->id,
            ]);

            $this->audit->log(
                'layanan',
                'CREATE',
                WorkOrder::class,
                $workOrder->id,
                null,
                ['ticket_number' => $ticketNumber, 'device_name' => $workOrder->device_name]
            );

            // Notify IT personnel / technicians of new work order
            $technicianUserIds = DB::table('platform.users')
                ->join('platform.user_roles', 'platform.users.id', '=', 'platform.user_roles.user_id')
                ->join('platform.roles', 'platform.user_roles.role_id', '=', 'platform.roles.id')
                ->whereIn('platform.roles.name', ['super-admin', 'admin-sdm', 'teknisi-it'])
                ->pluck('platform.users.id')
                ->unique()
                ->all();

            if (! empty($technicianUserIds)) {
                $this->notifications->send(
                    recipientEmployeeIds: $technicianUserIds,
                    title: "Tiket Baru: {$workOrder->ticket_number}",
                    message: "Permohonan perbaikan [{$workOrder->device_name}] di {$workOrder->section_name} diajukan oleh {$user->name}.",
                    channels: ['database'],
                    metadata: [
                        'module' => 'layanan',
                        'action_url' => "/layanan/work-orders/{$workOrder->id}",
                        'icon' => 'tool',
                        'reference_id' => $workOrder->id,
                        'type' => 'work_order_created',
                    ]
                );
            }

            return $workOrder;
        });
    }

    /**
     * Update work order progress by technician.
     *
     * @param  array<string, mixed>  $data
     */
    public function processWorkOrder(WorkOrder $wo, array $data, User $user): WorkOrder
    {
        return DB::transaction(function () use ($wo, $data, $user) {
            $technicianId = (string) ($user->employee_id ?? $this->userResolver->getCurrentEmployeeId() ?? $user->id);

            $updateData = [];
            $actionType = 'update_diagnosis';

            if (! empty($data['examination_result'])) {
                $updateData['examination_result'] = $data['examination_result'];
            }
            if (! empty($data['estimated_duration'])) {
                $updateData['estimated_duration'] = $data['estimated_duration'];
            }
            if (! empty($data['technician_notes'])) {
                $updateData['technician_notes'] = $data['technician_notes'];
            }

            // If taking ticket for the first time
            if ($wo->status === 'pending' || ! empty($data['take_ticket'])) {
                $updateData['status'] = 'in_progress';
                $updateData['started_at'] = Carbon::now();
                $updateData['technician_id'] = $technicianId;
                $actionType = 'take_ticket';
            }

            // If marking as completed
            if (! empty($data['is_completed']) || ($data['status'] ?? null) === 'completed') {
                $updateData['status'] = 'completed';
                $updateData['completed_at'] = Carbon::now();
                $updateData['technician_id'] = $technicianId;
                $actionType = 'finish_repair';
            }

            $oldData = $wo->only(array_keys($updateData));
            $wo->update($updateData);

            WorkOrderAction::create([
                'work_order_id' => $wo->id,
                'technician_id' => $technicianId,
                'action_type' => $actionType,
                'notes' => $data['technician_notes'] ?? ($actionType === 'take_ticket' ? 'Tiket diambil oleh teknisi IT.' : 'Pengerjaan diperbarui.'),
            ]);

            $this->audit->log(
                'layanan',
                'UPDATE',
                WorkOrder::class,
                $wo->id,
                $oldData,
                $updateData
            );

            // Notify ticket requester of progress changes
            if ($actionType === 'take_ticket' && $wo->created_by) {
                $this->notifications->send(
                    recipientEmployeeIds: [$wo->created_by],
                    title: "Tiket Diproses: {$wo->ticket_number}",
                    message: "Permohonan perbaikan [{$wo->device_name}] mulai ditangani oleh teknisi IT.",
                    channels: ['database'],
                    metadata: [
                        'module' => 'layanan',
                        'action_url' => "/layanan/work-orders/{$wo->id}",
                        'icon' => 'tool',
                        'reference_id' => $wo->id,
                        'type' => 'work_order_in_progress',
                    ]
                );
            } elseif ($actionType === 'finish_repair' && $wo->created_by) {
                $this->notifications->send(
                    recipientEmployeeIds: [$wo->created_by],
                    title: "Perbaikan Selesai: {$wo->ticket_number}",
                    message: "Perangkat [{$wo->device_name}] telah selesai diperbaiki dan siap diserahterimakan.",
                    channels: ['database'],
                    metadata: [
                        'module' => 'layanan',
                        'action_url' => "/layanan/work-orders/{$wo->id}",
                        'icon' => 'check',
                        'reference_id' => $wo->id,
                        'type' => 'work_order_completed',
                    ]
                );
            }

            return $wo;
        });
    }

    protected function getUserOrgUnitId(User $user): ?string
    {
        $employeeId = $user->employee_id ?? $this->userResolver->getCurrentEmployeeId();
        if (! $employeeId) {
            return null;
        }

        $employee = $this->directory->getEmployeeById((string) $employeeId);
        if (! is_array($employee)) {
            return null;
        }

        return isset($employee['org_unit_id']) ? (string) $employee['org_unit_id'] : null;
    }
}
