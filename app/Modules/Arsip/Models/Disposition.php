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
 * @property string $sender_employee_id
 * @property string|null $recipient_employee_id
 * @property string|null $recipient_unit_id
 * @property string $instruction
 * @property string|null $notes
 * @property Carbon|string|null $deadline
 * @property string $status
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Letter $letter
 */
class Disposition extends Model
{
    use HasUuids;

    protected $table = 'arsip.dispositions';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'letter_id',
        'sender_employee_id',
        'recipient_employee_id',
        'recipient_unit_id',
        'instruction',
        'notes',
        'deadline',
        'status',
        'completed_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'deadline' => 'date',
        'completed_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Letter, $this>
     */
    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class, 'letter_id');
    }
}
