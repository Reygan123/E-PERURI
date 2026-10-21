<?php

namespace App\Modules\Platform\Policies;

use App\Models\User;
use App\Modules\Platform\Contracts\DirectoryServiceInterface;
use App\Modules\Platform\Contracts\UserResolverInterface;
use App\Modules\Platform\Models\Employee;

class EmployeePolicy
{
    public function __construct(
        protected UserResolverInterface $userResolver,
        protected DirectoryServiceInterface $directoryService
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->userResolver->hasPermission('platform.employee.view', $user);
    }

    public function view(User $user, Employee $employee): bool
    {
        if (! $this->userResolver->hasPermission('platform.employee.view', $user)) {
            return false;
        }

        $scope = $this->userResolver->getActiveScope($user);
        if ($scope['scope'] === 'all') {
            return true;
        }

        if ($scope['scope'] === 'own') {
            return $user->employee_id === $employee->id;
        }

        $assignedUnitId = $employee->activeAssignment?->org_unit_id;
        if (! $assignedUnitId) {
            return false;
        }

        $allowedUnitIds = $scope['org_unit_ids'] ?? [];
        if (in_array($assignedUnitId, $allowedUnitIds, true)) {
            return true;
        }

        if ($scope['scope'] === 'unit_tree') {
            foreach ($allowedUnitIds as $allowedId) {
                if ($this->directoryService->isEmployeeInOrgUnit($employee->id, $allowedId, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->userResolver->hasPermission('platform.employee.manage', $user);
    }

    public function update(User $user, Employee $employee): bool
    {
        return $this->userResolver->hasPermission('platform.employee.manage', $user);
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $this->userResolver->hasPermission('platform.employee.manage', $user);
    }
}
