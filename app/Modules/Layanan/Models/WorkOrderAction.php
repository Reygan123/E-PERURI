<?php

declare(strict_types=1);

namespace App\Modules\Layanan\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $work_order_id
 * @property string $technician_id
 * @property string $action_type
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read WorkOrder $workOrder
 */
class WorkOrderAction extends Model
{
    use HasUuids;

    protected $table = 'layanan.work_order_actions';

    protected $fillable = [
        'work_order_id',
        'technician_id',
        'action_type',
        'notes',
    ];

    /**
     * @return BelongsTo<WorkOrder, $this>
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }
}
