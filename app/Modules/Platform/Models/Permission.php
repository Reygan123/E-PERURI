<?php

namespace App\Modules\Platform\Models;

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
 * @property string $module
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Role> $roles
 */
class Permission extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'platform.permissions';

    protected $fillable = [
        'name',
        'label',
        'module',
    ];

    /**
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'platform.role_permissions',
            'permission_id',
            'role_id'
        )->withPivot('scope');
    }
}
