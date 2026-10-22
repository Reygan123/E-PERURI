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
 * @property string $file_id
 * @property string|null $caption
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Letter $letter
 */
class LetterAttachment extends Model
{
    use HasUuids;

    protected $table = 'arsip.letter_attachments';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'letter_id',
        'file_id',
        'caption',
    ];

    /**
     * @return BelongsTo<Letter, $this>
     */
    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class, 'letter_id');
    }
}
