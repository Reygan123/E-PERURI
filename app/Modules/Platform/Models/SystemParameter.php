<?php

namespace App\Modules\Platform\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $key
 * @property string $value
 * @property string $type
 * @property string|null $description
 * @property string $module
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class SystemParameter extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'platform.system_parameters';

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'module',
    ];
}
