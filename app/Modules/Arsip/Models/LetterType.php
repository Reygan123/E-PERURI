<?php

declare(strict_types=1);

namespace App\Modules\Arsip\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $code
 * @property string $name
 * @property string|null $numbering_format_code
 * @property string $signature_type
 * @property string $approval_type
 * @property string $reset_period
 * @property bool $requires_double_upload
 * @property bool $is_active
 * @property string $status_group
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Letter> $letters
 */
class LetterType extends Model
{
    use HasUuids;

    protected $table = 'arsip.letter_types';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'code',
        'name',
        'numbering_format_code',
        'signature_type',
        'approval_type',
        'reset_period',
        'requires_double_upload',
        'is_active',
        'status_group',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'requires_double_upload' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * @return HasMany<Letter, $this>
     */
    public function letters(): HasMany
    {
        return $this->hasMany(Letter::class, 'type_id');
    }
}
