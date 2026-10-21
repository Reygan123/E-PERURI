<?php

namespace App\Modules\Platform\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property string $label
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Permission> $permissions
 * @property-read Collection<int, User> $users
 */
class Role extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'platform.roles';

    protected $fillable = [
        'name',
        'label',
    ];

    /**
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'platform.role_permissions',
            'role_id',
            'permission_id'
        )->withPivot('scope');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'platform.user_roles',
            'role_id',
            'user_id'
        )->withPivot('org_unit_id', 'valid_from', 'valid_until');
    }
}
