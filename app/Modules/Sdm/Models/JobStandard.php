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
 * @property string $indicator_name
 * @property string $target_value
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read JobDescription $jobDescription
 */
class JobStandard extends Model
{
    use HasUuids;

    protected $table = 'sdm.job_standards';

    protected $fillable = [
        'job_description_id',
        'indicator_name',
        'target_value',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
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
