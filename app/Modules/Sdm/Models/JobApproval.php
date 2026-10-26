<?php

declare(strict_types=1);

namespace App\Modules\Sdm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $job_description_id
 * @property string $stage
 * @property string $role_title
 * @property string|null $approver_user_id
 * @property string $approver_name
 * @property string $decision
 * @property string|null $notes
 * @property Carbon|null $decided_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read JobDescription $jobDescription
 */
class JobApproval extends Model
{
    use HasUuids;

    protected $table = 'sdm.job_approvals';

    protected $fillable = [
        'job_description_id',
        'stage', // maker, checker, approver, hrd_ratification
        'role_title',
        'approver_user_id',
        'approver_name',
        'decision', // pending, approved, rejected
        'notes',
        'decided_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'decided_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<JobDescription, $this>
     */
    public function jobDescription(): BelongsTo
    {
        return $this->belongsTo(JobDescription::class, 'job_description_id');
    }
}
