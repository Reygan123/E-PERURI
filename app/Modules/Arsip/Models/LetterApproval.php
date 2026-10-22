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
 * @property string $approver_employee_id
 * @property string $approver_role_title
 * @property int $step_order
 * @property string $status
 * @property string $action_type
 * @property string|null $notes
 * @property string|null $signature_hash
 * @property Carbon|null $decided_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Letter $letter
 */
class LetterApproval extends Model
{
    use HasUuids;

    protected $table = 'arsip.letter_approvals';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'letter_id',
        'approver_employee_id',
        'approver_role_title',
        'step_order',
        'status',
        'action_type',
        'notes',
        'signature_hash',
        'decided_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'step_order' => 'integer',
        'decided_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Letter, $this>
     */
    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class, 'letter_id');
    }
}
