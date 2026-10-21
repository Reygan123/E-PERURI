<?php

namespace App\Modules\Platform\Policies;

use App\Models\User;
use App\Modules\Platform\Contracts\DirectoryServiceInterface;
use App\Modules\Platform\Contracts\UserResolverInterface;
use App\Modules\Platform\Models\OrgUnit;

class OrgUnitPolicy
{
    public function __construct(
        protected UserResolverInterface $userResolver,
        protected DirectoryServiceInterface $directoryService
    ) {}

    public function viewAny(User $user): bool
    {
        return $this->userResolver->hasPermission('platform.org_unit.view', $user);
    }

    public function view(User $user, OrgUnit $orgUnit): bool
    {
        if (! $this->userResolver->hasPermission('platform.org_unit.view', $user)) {
            return false;
        }

        $scope = $this->userResolver->getActiveScope($user);
        if ($scope['scope'] === 'all') {
            return true;
        }

        if (in_array($scope['scope'], ['unit', 'unit_tree'], true)) {
            $allowedUnitIds = $scope['org_unit_ids'] ?? [];
            if (in_array($orgUnit->id, $allowedUnitIds, true)) {
                return true;
            }

            if ($scope['scope'] === 'unit_tree') {
                foreach ($allowedUnitIds as $allowedId) {
                    $subordinates = $this->directoryService->getSubordinateOrgUnitIds($allowedId);
                    if (in_array($orgUnit->id, $subordinates, true)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->userResolver->hasPermission('platform.org_unit.manage', $user);
    }

    public function update(User $user, OrgUnit $orgUnit): bool
    {
        return $this->userResolver->hasPermission('platform.org_unit.manage', $user);
    }

    public function delete(User $user, OrgUnit $orgUnit): bool
    {
        return $this->userResolver->hasPermission('platform.org_unit.manage', $user);
    }
}
