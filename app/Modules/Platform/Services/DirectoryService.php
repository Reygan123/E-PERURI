<?php

namespace App\Modules\Platform\Services;

use App\Modules\Platform\Contracts\DirectoryServiceInterface;
use App\Modules\Platform\Models\Employee;
use App\Modules\Platform\Models\OrgUnit;

class DirectoryService implements DirectoryServiceInterface
{
    /**
     * Get active employee details by employee UUID.
     *
     * @return array<string, mixed>|null
     */
    public function getEmployeeById(string $employeeId): ?array
    {
        $employee = Employee::with(['activeAssignment.orgUnit', 'activeAssignment.position'])
            ->find($employeeId);

        if (! $employee || ! $employee->is_active) {
            return null;
        }

        $assignment = $employee->activeAssignment;

        return [
            'id' => $employee->id,
            'nip' => $employee->nip,
            'name' => $employee->name,
            'email' => $employee->email,
            'phone' => $employee->phone,
            'org_unit' => $assignment?->orgUnit ? [
                'id' => $assignment->orgUnit->id,
                'code' => $assignment->orgUnit->code,
                'name' => $assignment->orgUnit->name,
                'level' => $assignment->orgUnit->level,
            ] : null,
            'position' => $assignment?->position ? [
                'id' => $assignment->position->id,
                'code' => $assignment->position->code,
                'name' => $assignment->position->name,
            ] : null,
        ];
    }

    /**
     * Get active employee details by NIP.
     *
     * @return array<string, mixed>|null
     */
    public function getEmployeeByNip(string $nip): ?array
    {
        $employee = Employee::with(['activeAssignment.orgUnit', 'activeAssignment.position'])
            ->where('nip', $nip)
            ->first();

        if (! $employee || ! $employee->is_active) {
            return null;
        }

        return $this->getEmployeeById($employee->id);
    }

    /**
     * Get organizational unit details by UUID or code.
     *
     * @return array<string, mixed>|null
     */
    public function getOrgUnit(string $idOrCode): ?array
    {
        $unit = OrgUnit::with('parent')
            ->where('id', $idOrCode)
            ->orWhere('code', $idOrCode)
            ->first();

        if (! $unit || ! $unit->is_active) {
            return null;
        }

        return [
            'id' => $unit->id,
            'parent_id' => $unit->parent_id,
            'code' => $unit->code,
            'name' => $unit->name,
            'type' => $unit->type,
            'level' => $unit->level,
            'parent_code' => $unit->parent?->code,
            'parent_name' => $unit->parent?->name,
        ];
    }

    /**
     * Get all subordinate organizational unit IDs for a given parent unit ID.
     *
     * @return list<string>
     */
    public function getSubordinateOrgUnitIds(string $orgUnitId): array
    {
        $subordinates = [];
        $queue = [$orgUnitId];

        while (! empty($queue)) {
            $currentParentId = array_shift($queue);
            $children = OrgUnit::where('parent_id', $currentParentId)
                ->where('is_active', true)
                ->pluck('id')
                ->all();

            foreach ($children as $childId) {
                $subordinates[] = $childId;
                $queue[] = $childId;
            }
        }

        return $subordinates;
    }

    /**
     * Check if an employee belongs to an organizational unit or its descendants.
     */
    public function isEmployeeInOrgUnit(string $employeeId, string $orgUnitId, bool $recursive = true): bool
    {
        $employee = Employee::with('activeAssignment')->find($employeeId);
        if (! $employee || ! $employee->activeAssignment) {
            return false;
        }

        $assignedUnitId = $employee->activeAssignment->org_unit_id;
        if ($assignedUnitId === $orgUnitId) {
            return true;
        }

        if (! $recursive) {
            return false;
        }

        $subordinateIds = $this->getSubordinateOrgUnitIds($orgUnitId);

        return in_array($assignedUnitId, $subordinateIds, true);
    }
}
