<?php

declare(strict_types=1);

namespace App\Modules\Arsip\Policies;

use App\Models\User;
use App\Modules\Arsip\Models\Letter;
use App\Modules\Platform\Contracts\DirectoryServiceInterface;
use App\Modules\Platform\Contracts\UserResolverInterface;

class LetterPolicy
{
    public function __construct(
        protected UserResolverInterface $userResolver,
        protected DirectoryServiceInterface $directoryService
    ) {}

    /**
     * Determine whether the user can view a list of letters.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the specific letter.
     * Prevents IDOR and protects confidential documents.
     */
    public function view(User $user, Letter $letter): bool
    {
        $employeeId = $user->employee_id ?? $this->userResolver->getCurrentEmployeeId();
        if (! $employeeId) {
            return false;
        }

        // 1. Super-admin or role with 'all' scope
        $scope = $this->userResolver->getActiveScope($user);
        if (($scope['scope'] ?? '') === 'all') {
            return true;
        }

        // 2. Creator of the letter
        if ($letter->creator_employee_id === $employeeId) {
            return true;
        }

        // 3. User is an assigned approver
        $letter->loadMissing(['approvals', 'destinations']);
        if ($letter->approvals->contains('approver_employee_id', $employeeId)) {
            return true;
        }

        // 4. User belongs to origin unit
        $employee = $this->directoryService->getEmployeeById($employeeId);
        $userUnitId = $employee['org_unit_id'] ?? null;

        if ($userUnitId && $letter->origin_unit_id === $userUnitId) {
            return true;
        }

        // 5. User's unit is a designated recipient destination
        if ($userUnitId && $letter->destinations->contains('org_unit_id', $userUnitId)) {
            return true;
        }

        // 6. Published non-confidential letters (announcements, public circulars)
        if (in_array($letter->status, ['published', 'approved'], true) && $letter->confidentiality === 'biasa') {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create new letter drafts.
     */
    public function create(User $user): bool
    {
        return ! empty($user->employee_id ?? $this->userResolver->getCurrentEmployeeId());
    }

    /**
     * Determine whether the user can update the letter.
     */
    public function update(User $user, Letter $letter): bool
    {
        $employeeId = $user->employee_id ?? $this->userResolver->getCurrentEmployeeId();

        return $employeeId === $letter->creator_employee_id
            && in_array($letter->status, ['draft', 'revision_requested'], true);
    }

    /**
     * Determine whether the user can submit the letter for approval.
     */
    public function submit(User $user, Letter $letter): bool
    {
        return $this->update($user, $letter);
    }

    /**
     * Determine whether the user can upload final signed scan.
     */
    public function uploadSigned(User $user, Letter $letter): bool
    {
        $employeeId = $user->employee_id ?? $this->userResolver->getCurrentEmployeeId();

        return $employeeId === $letter->creator_employee_id;
    }
}
