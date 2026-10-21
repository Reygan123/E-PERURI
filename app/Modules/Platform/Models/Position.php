<?php

namespace App\Modules\Platform\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $org_unit_id
 * @property string $code
 * @property string $name
 * @property int $level
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read OrgUnit $orgUnit
 */
class Position extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'platform.positions';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'org_unit_id',
        'code',
        'name',
        'level',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<OrgUnit, $this>
     */
    public function orgUnit(): BelongsTo
    {
        return $this->belongsTo(OrgUnit::class, 'org_unit_id');
    }

    /**
     * @return HasMany<EmployeeAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(EmployeeAssignment::class, 'position_id');
    }
}
