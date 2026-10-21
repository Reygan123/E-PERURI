<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Platform\Contracts\AuditServiceInterface;
use App\Modules\Platform\Models\OrgUnit;
use App\Modules\Platform\Models\Role;
use App\Modules\Platform\Models\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserRoleController extends Controller
{
    public function __construct(
        protected AuditServiceInterface $auditService
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', User::class);

        $query = User::query()->with([
            'employee.activeAssignment.orgUnit',
            'userRoles.role',
            'userRoles.orgUnit',
        ])->orderBy('username');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'ilike', "%{$search}%")
                    ->orWhere('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        if ($roleId = $request->input('role_id')) {
            $query->whereHas('userRoles', function ($q) use ($roleId) {
                $q->where('role_id', $roleId);
            });
        }

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $users = $query->paginate(15)->withQueryString();

        $roles = Role::query()->orderBy('name')->get(['id', 'name', 'label']);
        $orgUnits = OrgUnit::query()->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);

        return Inertia::render('Platform/Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'orgUnits' => $orgUnits,
            'filters' => $request->only(['search', 'role_id', 'is_active']),
        ]);
    }

    public function assignRole(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $validated = $request->validate([
            'role_id' => ['required', 'uuid', Rule::exists(Role::class, 'id')],
            'org_unit_id' => ['nullable', 'uuid', Rule::exists(OrgUnit::class, 'id')],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
        ]);

        $userRole = UserRole::create([
            'user_id' => $user->id,
            'role_id' => $validated['role_id'],
            'org_unit_id' => $validated['org_unit_id'] ?? null,
            'valid_from' => $validated['valid_from'],
            'valid_until' => $validated['valid_until'] ?? null,
        ]);

        $this->auditService->log(
            action: 'assign_role',
            entityType: 'UserRole',
            entityId: $userRole->id,
            before: null,
            after: $userRole->toArray()
        );

        return redirect()->back()->with('success', "Peran berhasil ditambahkan pada pengguna {$user->username}.");
    }

    public function revokeRole(UserRole $userRole): RedirectResponse
    {
        Gate::authorize('update', $userRole->user);

        $before = $userRole->toArray();
        $userRole->delete();

        $this->auditService->log(
            action: 'revoke_role',
            entityType: 'UserRole',
            entityId: $userRole->id,
            before: $before,
            after: null
        );

        return redirect()->back()->with('success', 'Peran pengguna berhasil dicabut.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $before = $user->toArray();
        $newStatus = ! $user->is_active;
        $user->update(['is_active' => $newStatus]);

        $this->auditService->log(
            action: $newStatus ? 'activate_user' : 'deactivate_user',
            entityType: 'User',
            entityId: $user->id,
            before: $before,
            after: $user->fresh()?->toArray()
        );

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()->with('success', "Akun pengguna {$user->username} berhasil {$statusText}.");
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $temporaryPassword = 'PTKP'.date('Y').'!'.rand(100, 999);
        $user->update([
            'password' => Hash::make($temporaryPassword),
            'must_change_pwd' => true,
        ]);

        $this->auditService->log(
            action: 'reset_password',
            entityType: 'User',
            entityId: $user->id,
            before: null,
            after: ['must_change_pwd' => true]
        );

        return redirect()->back()->with('success', "Password untuk pengguna {$user->username} berhasil di-reset menjadi: {$temporaryPassword} (wajib ganti saat login berikutnya).");
    }
}
