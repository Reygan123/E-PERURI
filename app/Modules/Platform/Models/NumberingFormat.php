<?php

namespace App\Modules\Platform\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $code
 * @property string $template
 * @property string $reset_period
 * @property string|null $prefix
 * @property string $module
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, NumberingSequence> $sequences
 */
class NumberingFormat extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'platform.numbering_formats';

    protected $fillable = [
        'code',
        'template',
        'reset_period',
        'prefix',
        'module',
    ];

    /**
     * @return HasMany<NumberingSequence, $this>
     */
    public function sequences(): HasMany
    {
        return $this->hasMany(NumberingSequence::class, 'format_id');
    }
}
