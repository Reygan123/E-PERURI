<?php

namespace App\Modules\Platform\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $user_id
 * @property string $role_id
 * @property string|null $org_unit_id
 * @property Carbon $valid_from
 * @property Carbon|null $valid_until
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Role $role
 * @property-read OrgUnit|null $orgUnit
 */
class UserRole extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'platform.user_roles';

    protected $fillable = [
        'user_id',
        'role_id',
        'org_unit_id',
        'valid_from',
        'valid_until',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_until' => 'date',
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
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * @return BelongsTo<OrgUnit, $this>
     */
    public function orgUnit(): BelongsTo
    {
        return $this->belongsTo(OrgUnit::class, 'org_unit_id');
    }
}
