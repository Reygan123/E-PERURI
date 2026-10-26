<?php

declare(strict_types=1);

namespace App\Modules\Arsip\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $type_id
 * @property string|null $document_number
 * @property string|null $number_suffix
 * @property string $origin_unit_id
 * @property string $creator_employee_id
 * @property string $destination_type
 * @property string|null $destination_text
 * @property string $subject
 * @property Carbon|string $letter_date
 * @property string $status
 * @property string $confidentiality
 * @property string $urgency
 * @property array<string, mixed>|null $content_body
 * @property string|null $draft_file_id
 * @property string|null $signed_file_id
 * @property bool $is_double_upload_complete
 * @property string|null $signature_hash
 * @property Carbon|null $signed_at
 * @property string|null $signer_employee_id
 * @property array<string, mixed>|null $signature_metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read LetterType $type
 * @property-read Collection<int, LetterDestination> $destinations
 * @property-read Collection<int, LetterApproval> $approvals
 * @property-read Collection<int, Disposition> $dispositions
 * @property-read Collection<int, LetterAttachment> $attachments
 */
class Letter extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'arsip.letters';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'type_id',
        'document_number',
        'number_suffix',
        'origin_unit_id',
        'creator_employee_id',
        'destination_type',
        'destination_text',
        'subject',
        'letter_date',
        'status',
        'confidentiality',
        'urgency',
        'content_body',
        'draft_file_id',
        'signed_file_id',
        'is_double_upload_complete',
        'signature_hash',
        'signed_at',
        'signer_employee_id',
        'signature_metadata',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'letter_date' => 'date',
        'content_body' => 'array',
        'is_double_upload_complete' => 'boolean',
        'signed_at' => 'datetime',
        'signature_metadata' => 'array',
    ];

    /**
     * @return BelongsTo<LetterType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(LetterType::class, 'type_id');
    }

    /**
     * @return HasMany<LetterDestination, $this>
     */
    public function destinations(): HasMany
    {
        return $this->hasMany(LetterDestination::class, 'letter_id');
    }

    /**
     * @return HasMany<LetterApproval, $this>
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(LetterApproval::class, 'letter_id')->orderBy('step_order');
    }

    /**
     * @return HasMany<Disposition, $this>
     */
    public function dispositions(): HasMany
    {
        return $this->hasMany(Disposition::class, 'letter_id')->latest();
    }

    /**
     * @return HasMany<LetterAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(LetterAttachment::class, 'letter_id');
    }
}
