<?php

declare(strict_types=1);

namespace App\Modules\Arsip\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $letter_id
 * @property string $org_unit_id
 * @property Carbon|null $received_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Letter $letter
 */
class LetterDestination extends Model
{
    use HasUuids;

    protected $table = 'arsip.letter_destinations';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'letter_id',
        'org_unit_id',
        'received_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'received_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Letter, $this>
     */
    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class, 'letter_id');
    }
}
