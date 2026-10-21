<?php

namespace App\Modules\Platform\Services;

use App\Models\User;
use App\Modules\Platform\Contracts\UserResolverInterface;
use App\Modules\Platform\Models\Permission;
use App\Modules\Platform\Models\UserRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserResolverService implements UserResolverInterface
{
    /**
     * Get the currently authenticated user's ID.
     */
    public function getCurrentUserId(): ?string
    {
        return Auth::id();
    }

    /**
     * Get the currently authenticated employee's record ID.
     */
    public function getCurrentEmployeeId(): ?string
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user?->employee_id;
    }

    /**
     * Check if the authenticated user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        $activeRoles = UserRole::where('user_id', $user->id)
            ->where('valid_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('valid_until')->orWhere('valid_until', '>=', now());
            })
            ->pluck('role_id');

        return Permission::where('name', $permission)
            ->whereHas('roles', function ($query) use ($activeRoles) {
                $query->whereIn('role_id', $activeRoles);
            })
            ->exists();
    }

    /**
     * Check if the authenticated user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return UserRole::where('user_id', $user->id)
            ->whereHas('role', function ($query) use ($role) {
                $query->where('name', $role);
            })
            ->where('valid_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('valid_until')->orWhere('valid_until', '>=', now());
            })
            ->exists();
    }

    /**
     * Get all active scope restrictions for the authenticated user.
     *
     * @return array<string, mixed>
     */
    public function getActiveScope(): array
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return ['scope' => 'none'];
        }

        $activeUserRoles = UserRole::where('user_id', $user->id)
            ->where('valid_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('valid_until')->orWhere('valid_until', '>=', now());
            })
            ->get();

        if ($activeUserRoles->isEmpty()) {
            return ['scope' => 'none'];
        }

        $roleIds = $activeUserRoles->pluck('role_id')->all();

        // Check if any role has 'all' scope
        $hasAllScope = DB::table('platform.role_permissions')
            ->whereIn('role_id', $roleIds)
            ->where('scope', 'all')
            ->exists();

        if ($hasAllScope) {
            return ['scope' => 'all'];
        }

        $hasUnitTree = DB::table('platform.role_permissions')
            ->whereIn('role_id', $roleIds)
            ->where('scope', 'unit_tree')
            ->exists();

        $hasUnit = DB::table('platform.role_permissions')
            ->whereIn('role_id', $roleIds)
            ->where('scope', 'unit')
            ->exists();

        $scopeType = 'own';
        if ($hasUnitTree) {
            $scopeType = 'unit_tree';
        } elseif ($hasUnit) {
            $scopeType = 'unit';
        }

        /** @var list<string> $orgUnitIds */
        $orgUnitIds = $activeUserRoles->pluck('org_unit_id')->filter()->values()->all();

        return [
            'scope' => $scopeType,
            'employee_id' => $user->employee_id,
            'org_unit_ids' => array_values(array_unique($orgUnitIds)),
        ];
    }
}
