<?php

declare(strict_types=1);

namespace App\Modules\Sdm\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $position_id
 * @property string $org_unit_id
 * @property string|null $employee_id
 * @property string|null $document_number
 * @property int $revision_number
 * @property string $sotk_reference
 * @property string $job_group
 * @property string $grade_recommendation
 * @property string $job_summary
 * @property string|null $direct_superior_id
 * @property string|null $subordinate_notes
 * @property string|null $operating_challenges
 * @property string|null $environmental_conditions
 * @property string|null $incidental_duties
 * @property string $status
 * @property string|null $tte_hash
 * @property Carbon|null $tte_signed_at
 * @property string $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, JobTask> $tasks
 * @property-read Collection<int, JobGovernance> $governances
 * @property-read Collection<int, JobWorkingRelation> $workingRelations
 * @property-read Collection<int, JobCompetency> $competencies
 * @property-read JobSpecification|null $specification
 * @property-read Collection<int, JobStandard> $standards
 * @property-read Collection<int, JobApproval> $approvals
 */
class JobDescription extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'sdm.job_descriptions';

    protected $fillable = [
        'position_id',
        'org_unit_id',
        'employee_id',
        'document_number',
        'revision_number',
        'sotk_reference',
        'job_group',
        'grade_recommendation',
        'job_summary',
        'direct_superior_id',
        'subordinate_notes',
        'operating_challenges',
        'environmental_conditions',
        'incidental_duties',
        'status',
        'tte_hash',
        'tte_signed_at',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'revision_number' => 'integer',
            'tte_signed_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<JobTask, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(JobTask::class, 'job_description_id')->orderBy('sort_order');
    }

    /**
     * @return HasMany<JobGovernance, $this>
     */
    public function governances(): HasMany
    {
        return $this->hasMany(JobGovernance::class, 'job_description_id')->orderBy('sort_order');
    }

    /**
     * @return HasMany<JobWorkingRelation, $this>
     */
    public function workingRelations(): HasMany
    {
        return $this->hasMany(JobWorkingRelation::class, 'job_description_id')->orderBy('sort_order');
    }

    /**
     * @return HasMany<JobCompetency, $this>
     */
    public function competencies(): HasMany
    {
        return $this->hasMany(JobCompetency::class, 'job_description_id')->orderBy('sort_order');
    }

    /**
     * @return HasOne<JobSpecification, $this>
     */
    public function specification(): HasOne
    {
        return $this->hasOne(JobSpecification::class, 'job_description_id');
    }

    /**
     * @return HasMany<JobStandard, $this>
     */
    public function standards(): HasMany
    {
        return $this->hasMany(JobStandard::class, 'job_description_id')->orderBy('sort_order');
    }

    /**
     * @return HasMany<JobApproval, $this>
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(JobApproval::class, 'job_description_id')->orderBy('created_at');
    }
}
