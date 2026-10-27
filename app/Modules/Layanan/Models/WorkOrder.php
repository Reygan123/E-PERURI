<?php

declare(strict_types=1);

namespace App\Modules\Layanan\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $ticket_number
 * @property string|null $reference_memo_number
 * @property string $requester_id
 * @property string $org_unit_id
 * @property string $section_name
 * @property string $device_type_id
 * @property string $device_name
 * @property string|null $it_inventory_code
 * @property string $issue_description
 * @property string|null $photo_file_id
 * @property string $status
 * @property Carbon $submitted_at
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property string|null $technician_id
 * @property string|null $examination_result
 * @property string|null $estimated_duration
 * @property string|null $technician_notes
 * @property string $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read DeviceType $deviceType
 * @property-read Collection<int, WorkOrderAction> $actions
 */
class WorkOrder extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'layanan.work_orders';

    protected $fillable = [
        'ticket_number',
        'reference_memo_number',
        'requester_id',
        'org_unit_id',
        'section_name',
        'device_type_id',
        'device_name',
        'it_inventory_code',
        'issue_description',
        'photo_file_id',
        'status',
        'submitted_at',
        'started_at',
        'completed_at',
        'technician_id',
        'examination_result',
        'estimated_duration',
        'technician_notes',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<DeviceType, $this>
     */
    public function deviceType(): BelongsTo
    {
        return $this->belongsTo(DeviceType::class, 'device_type_id');
    }

    /**
     * @return HasMany<WorkOrderAction, $this>
     */
    public function actions(): HasMany
    {
        return $this->hasMany(WorkOrderAction::class, 'work_order_id')->orderBy('created_at', 'asc');
    }
}
