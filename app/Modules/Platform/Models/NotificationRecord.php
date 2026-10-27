<?php

declare(strict_types=1);

namespace App\Modules\Platform\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $user_id
 * @property string $type
 * @property string $channel
 * @property array<string, mixed> $payload
 * @property Carbon|null $read_at
 * @property Carbon|null $sent_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read string $title
 * @property-read string $message
 * @property-read string $module
 * @property-read string|null $action_url
 * @property-read bool $is_read
 */
class NotificationRecord extends Model
{
    use HasUuids;

    protected $table = 'platform.notifications';

    protected $fillable = [
        'id',
        'user_id',
        'type',
        'channel',
        'payload',
        'read_at',
        'sent_at',
    ];

    /**
     * @var list<string>
     */
    protected $appends = [
        'title',
        'message',
        'module',
        'action_url',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'read_at' => 'datetime',
            'sent_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @param  Builder<NotificationRecord>  $query
     * @return Builder<NotificationRecord>
     */
    public function scopeForUser(Builder $query, string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * @param  Builder<NotificationRecord>  $query
     * @return Builder<NotificationRecord>
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    /**
     * @param  Builder<NotificationRecord>  $query
     * @return Builder<NotificationRecord>
     */
    public function scopeForModule(Builder $query, string $module): Builder
    {
        return $query->where(function (Builder $q) use ($module) {
            $q->where('payload->module', $module)
                ->orWhere('payload->metadata->module', $module);
        });
    }

    /**
     * @param  Builder<NotificationRecord>  $query
     * @return Builder<NotificationRecord>
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        $term = '%'.mb_strtolower(trim($search)).'%';

        return $query->where(function (Builder $q) use ($term) {
            $q->whereRaw("LOWER(payload->>'title') LIKE ?", [$term])
                ->orWhereRaw("LOWER(payload->>'message') LIKE ?", [$term]);
        });
    }

    public function getTitleAttribute(): string
    {
        return (string) ($this->payload['title'] ?? 'Notifikasi Sistem');
    }

    public function getMessageAttribute(): string
    {
        return (string) ($this->payload['message'] ?? '');
    }

    public function getModuleAttribute(): string
    {
        return (string) ($this->payload['module'] ?? ($this->payload['metadata']['module'] ?? 'platform'));
    }

    public function getActionUrlAttribute(): ?string
    {
        $url = $this->payload['action_url'] ?? ($this->payload['metadata']['action_url'] ?? null);

        return $url ? (string) $url : null;
    }

    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }
}
