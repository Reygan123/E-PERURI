<?php

namespace App\Modules\Platform\Contracts;

interface AuditServiceInterface
{
    /**
     * Record an audit log entry.
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
        string $module,
        string $action,
        string $auditableType,
        string $auditableId,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $notes = null
    ): void;
}
