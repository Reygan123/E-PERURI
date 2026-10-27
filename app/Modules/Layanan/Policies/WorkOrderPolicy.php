<?php

declare(strict_types=1);

namespace App\Modules\Layanan\Policies;

use App\Models\User;
use App\Modules\Layanan\Models\WorkOrder;
use App\Modules\Platform\Contracts\DirectoryServiceInterface;
use App\Modules\Platform\Contracts\UserResolverInterface;

class WorkOrderPolicy
{
    public function __construct(
        protected DirectoryServiceInterface $directory,
        protected UserResolverInterface $userResolver
    ) {}

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, WorkOrder $wo): bool
    {
        if ($this->isPrivilegedOrTechnician($user)) {
            return true;
        }

        if ($wo->created_by === $user->id) {
            return true;
        }

        $userUnitId = $this->getUserOrgUnitId($user);
        if ($userUnitId) {
            if ($userUnitId === $wo->org_unit_id) {
                return true;
            }

            $subordinateUnitIds = $this->directory->getSubordinateOrgUnitIds($userUnitId);
            if (in_array($wo->org_unit_id, $subordinateUnitIds, true)) {
                return true;
            }
        }

        return false;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, WorkOrder $wo): bool
    {
        // Hanya pembuat yang boleh ubah tiket selama status pending, atau IT admin
        if ($this->isPrivilegedOrTechnician($user)) {
            return true;
        }

        return $wo->status === 'pending' && $wo->created_by === $user->id;
    }

    public function process(User $user, WorkOrder $wo): bool
    {
        // Hanya teknisi IT atau administrator yang boleh memproses tiket
        return $this->isPrivilegedOrTechnician($user);
    }

    public function complete(User $user, WorkOrder $wo): bool
    {
        // Hanya teknisi IT atau administrator yang boleh menyelesaikan tiket
        return $this->isPrivilegedOrTechnician($user);
    }

    public function delete(User $user, WorkOrder $wo): bool
    {
        return $this->userResolver->hasRole('super-admin', $user)
            || $this->userResolver->hasRole('admin-it', $user);
    }

    protected function isPrivilegedOrTechnician(User $user): bool
    {
        return $this->userResolver->hasRole('super-admin', $user)
            || $this->userResolver->hasRole('admin-it', $user)
            || $this->userResolver->hasRole('teknisi-it', $user)
            || $this->userResolver->hasRole('pimpinan', $user);
    }

    protected function getUserOrgUnitId(User $user): ?string
    {
        $employeeId = $user->employee_id ?? $this->userResolver->getCurrentEmployeeId();
        if (! $employeeId) {
            return null;
        }

        $employee = $this->directory->getEmployeeById((string) $employeeId);
        if (! is_array($employee)) {
            return null;
        }

        return isset($employee['org_unit_id']) ? (string) $employee['org_unit_id'] : null;
    }
}
