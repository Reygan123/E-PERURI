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
 * @property string|null $education_level
 * @property string|null $work_experience
 * @property string|null $certifications
 * @property string|null $trainings
 * @property array<string>|null $hard_skills
 * @property array<string>|null $soft_skills
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read JobDescription $jobDescription
 */
class JobSpecification extends Model
{
    use HasUuids;

    protected $table = 'sdm.job_specifications';

    protected $fillable = [
        'job_description_id',
        'education_level',
        'work_experience',
        'certifications',
        'trainings',
        'hard_skills',
        'soft_skills',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hard_skills' => 'array',
            'soft_skills' => 'array',
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
