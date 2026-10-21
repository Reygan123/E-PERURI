<?php

namespace App\Modules\Platform\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $format_id
 * @property string $period_key
 * @property int $last_sequence
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read NumberingFormat $format
 */
class NumberingSequence extends Model
{
    use HasUuids;

    protected $table = 'platform.numbering_sequences';

    protected $fillable = [
        'format_id',
        'period_key',
        'last_sequence',
    ];

    protected function casts(): array
    {
        return [
            'last_sequence' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<NumberingFormat, $this>
     */
    public function format(): BelongsTo
    {
        return $this->belongsTo(NumberingFormat::class, 'format_id');
    }
}
