<?php

declare(strict_types=1);

namespace App\Modules\Sdm\Policies;

use App\Models\User;
use App\Modules\Platform\Contracts\DirectoryServiceInterface;
use App\Modules\Platform\Contracts\UserResolverInterface;
use App\Modules\Sdm\Models\JobDescription;

class JobDescriptionPolicy
{
    public function __construct(
        protected DirectoryServiceInterface $directory,
        protected UserResolverInterface $userResolver
    ) {}

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, JobDescription $job): bool
    {
        if ($job->status === 'approved') {
            return true;
        }

        if ($this->isPrivileged($user)) {
            return true;
        }

        if ($job->created_by === $user->id) {
            return true;
        }

        // Check unit scope for in-review or draft documents
        $userUnitId = $this->getUserOrgUnitId($user);
        if ($userUnitId) {
            if ($userUnitId === $job->org_unit_id) {
                return true;
            }

            $subordinateUnitIds = $this->directory->getSubordinateOrgUnitIds($userUnitId);
            if (in_array($job->org_unit_id, $subordinateUnitIds, true)) {
                return true;
            }
        }

        return false;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, JobDescription $job): bool
    {
        if ($job->status !== 'draft') {
            return false;
        }

        if ($this->isPrivileged($user)) {
            return true;
        }

        if ($job->created_by === $user->id) {
            return true;
        }

        $userUnitId = $this->getUserOrgUnitId($user);
        if ($userUnitId) {
            if ($userUnitId === $job->org_unit_id) {
                return true;
            }

            $subordinateUnitIds = $this->directory->getSubordinateOrgUnitIds($userUnitId);
            if (in_array($job->org_unit_id, $subordinateUnitIds, true)) {
                return true;
            }
        }

        return false;
    }

    public function delete(User $user, JobDescription $job): bool
    {
        if ($job->status !== 'draft') {
            return false;
        }

        if ($this->isPrivileged($user)) {
            return true;
        }

        return $job->created_by === $user->id;
    }

    public function approve(User $user, JobDescription $job): bool
    {
        if ($job->status !== 'in_review') {
            return false;
        }

        if ($this->isPrivileged($user)) {
            return true;
        }

        $userUnitId = $this->getUserOrgUnitId($user);
        if (! $userUnitId) {
            return false;
        }

        if ($userUnitId === $job->org_unit_id) {
            return true;
        }

        $subordinateUnitIds = $this->directory->getSubordinateOrgUnitIds($userUnitId);

        return in_array($job->org_unit_id, $subordinateUnitIds, true);
    }

    private function isPrivileged(User $user): bool
    {
        $scope = $this->userResolver->getActiveScope($user);
        if (($scope['scope'] ?? '') === 'all') {
            return true;
        }

        return $this->userResolver->hasRole('super-admin', $user)
            || $this->userResolver->hasRole('admin-sdm', $user)
            || $this->userResolver->hasRole('pimpinan', $user);
    }

    private function getUserOrgUnitId(User $user): ?string
    {
        $employeeId = $user->employee_id ?? $this->userResolver->getCurrentEmployeeId();
        if (! $employeeId) {
            return null;
        }

        $employee = $this->directory->getEmployeeById((string) $employeeId);

        return $employee['org_unit_id'] ?? null;
    }
}
