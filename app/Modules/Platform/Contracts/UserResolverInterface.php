<?php

namespace App\Modules\Platform\Contracts;

use App\Models\User;

interface UserResolverInterface
{
    /**
     * Get the currently authenticated user's ID.
     */
    public function getCurrentUserId(): ?string;

    /**
     * Get the currently authenticated employee's record ID.
     */
    public function getCurrentEmployeeId(): ?string;

    /**
     * Check if the authenticated user has a specific permission.
     */
    public function hasPermission(string $permission, ?User $user = null): bool;

    /**
     * Check if the authenticated user has a specific role.
     */
    public function hasRole(string $role, ?User $user = null): bool;

    /**
     * Get all active scope restrictions for the authenticated user.
     *
     * @return array<string, mixed>
     */
    public function getActiveScope(?User $user = null): array;
}
