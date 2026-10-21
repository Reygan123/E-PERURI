<?php

namespace App\Modules\Platform\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $nip
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string|null $nik
 * @property Carbon|null $birth_date
 * @property string|null $gender
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User|null $user
 * @property-read Collection<int, EmployeeAssignment> $assignments
 * @property-read EmployeeAssignment|null $activeAssignment
 */
class Employee extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'platform.employees';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nip',
        'name',
        'email',
        'phone',
        'nik',
        'birth_date',
        'gender',
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
            'nik' => 'encrypted',
            'birth_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasOne<User, $this>
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'employee_id');
    }

    /**
     * @return HasMany<EmployeeAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(EmployeeAssignment::class, 'employee_id');
    }

    /**
     * @return HasOne<EmployeeAssignment, $this>
     */
    public function activeAssignment(): HasOne
    {
        return $this->hasOne(EmployeeAssignment::class, 'employee_id')
            ->whereNull('ended_at')
            ->latest('started_at');
    }
}
