<?php

namespace App\Modules\Platform\Services;

use App\Models\User;
use App\Modules\Platform\Contracts\AuditServiceInterface;
use App\Modules\Platform\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditService implements AuditServiceInterface
{
    /**
     * Record an immutable audit log entry.
     *
     * @param  string  $module  e.g. 'platform', 'arsip', 'layanan'
     * @param  string  $action  e.g. 'CREATE', 'UPDATE', 'DELETE', 'APPROVE', 'REJECT'
     * @param  string  $auditableType  Model or resource class/name
     * @param  string  $auditableId  Target record UUID
     * @param  array<string, mixed>|null  $oldValues  Previous state before change
     * @param  array<string, mixed>|null  $newValues  New state after change
     * @param  string|null  $notes  Optional context notes
     */
    public function log(
        string $module = 'platform',
        string $action = '',
        string $auditableType = '',
        string $auditableId = '',
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $notes = null,
        ?string $entityType = null,
        ?string $entityId = null,
        ?array $before = null,
        ?array $after = null
    ): void {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();

        $resolvedType = $entityType ?: $auditableType;
        $resolvedId = $entityId ?: $auditableId;
        $resolvedBefore = $before ?? $oldValues;
        $resolvedAfter = $after ?? $newValues;

        AuditLog::create([
            'id' => (string) Str::uuid(),
            'user_id' => $currentUser?->id,
            'employee_id' => $currentUser?->employee_id,
            'action' => strtoupper($action),
            'module' => strtolower($module),
            'entity_type' => $resolvedType,
            'entity_id' => $resolvedId,
            'before' => $resolvedBefore,
            'after' => $resolvedAfter,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
