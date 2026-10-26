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
 * @property string $sub_competency
 * @property int $proficiency_level
 * @property string $key_indicator
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read JobDescription $jobDescription
 */
class JobCompetency extends Model
{
    use HasUuids;

    protected $table = 'sdm.job_competencies';

    protected $fillable = [
        'job_description_id',
        'sub_competency',
        'proficiency_level', // 1-5
        'key_indicator',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'proficiency_level' => 'integer',
            'sort_order' => 'integer',
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
