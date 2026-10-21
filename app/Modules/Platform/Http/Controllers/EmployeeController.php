<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Platform\Contracts\AuditServiceInterface;
use App\Modules\Platform\Models\Employee;
use App\Modules\Platform\Models\EmployeeAssignment;
use App\Modules\Platform\Models\OrgUnit;
use App\Modules\Platform\Models\Position;
use App\Modules\Platform\Models\Role;
use App\Modules\Platform\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function __construct(
        protected AuditServiceInterface $auditService
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Employee::class);

        $query = Employee::query()->with([
            'activeAssignment.orgUnit',
            'activeAssignment.position',
            'user',
        ])->orderBy('name');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('nip', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        if ($orgUnitId = $request->input('org_unit_id')) {
            $query->whereHas('activeAssignment', function ($q) use ($orgUnitId) {
                $q->where('org_unit_id', $orgUnitId);
            });
        }

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $employees = $query->paginate(15)->withQueryString();

        $orgUnits = OrgUnit::query()
            ->where('is_active', true)
            ->orderBy('level')
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $positions = Position::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'org_unit_id']);

        return Inertia::render('Platform/Employees/Index', [
            'employees' => $employees,
            'orgUnits' => $orgUnits,
            'positions' => $positions,
            'filters' => $request->only(['search', 'org_unit_id', 'is_active']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Employee::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:20', Rule::unique(Employee::class, 'nip')],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(Employee::class, 'email'),
                Rule::unique(User::class, 'email'),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'gender' => ['nullable', 'string', 'in:male,female'],
            'birth_date' => ['nullable', 'date'],
            'nik' => ['nullable', 'string', 'max:30'],
            'org_unit_id' => ['required', 'uuid', Rule::exists(OrgUnit::class, 'id')],
            'position_id' => ['required', 'uuid', Rule::exists(Position::class, 'id')],
            'assignment_type' => ['required', 'string', 'in:permanent,temporary,acting'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9._-]+$/', Rule::unique(User::class, 'username')],
            'initial_password' => ['nullable', 'string', 'min:8'],
        ]);

        $defaultPassword = $validated['initial_password'] ?? 'PTKP'.date('Y').'!';

        DB::transaction(function () use ($validated, $defaultPassword, $request) {
            /** @var Employee $employee */
            $employee = Employee::create([
                'name' => $validated['name'],
                'nip' => $validated['nip'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'nik' => $validated['nik'] ?? null,
                'is_active' => true,
            ]);

            // Penugasan awal
            EmployeeAssignment::create([
                'employee_id' => $employee->id,
                'org_unit_id' => $validated['org_unit_id'],
                'position_id' => $validated['position_id'],
                'started_at' => Carbon::now()->toDateString(),
                'ended_at' => null,
                'assignment_type' => $validated['assignment_type'],
                'notes' => 'Penugasan awal sistem',
                'created_by' => $request->user()?->employee_id,
            ]);

            // Buat akun user
            /** @var User $user */
            $user = User::create([
                'employee_id' => $employee->id,
                'name' => $validated['name'],
                'username' => strtolower($validated['username']),
                'email' => $validated['email'],
                'password' => Hash::make($defaultPassword),
                'must_change_pwd' => true,
                'is_active' => true,
            ]);

            // Peran dasar 'pegawai'
            $pegawaiRole = Role::where('name', 'pegawai')->first();
            if ($pegawaiRole) {
                UserRole::create([
                    'user_id' => $user->id,
                    'role_id' => $pegawaiRole->id,
                    'org_unit_id' => $validated['org_unit_id'],
                    'valid_from' => Carbon::now()->toDateString(),
                    'valid_until' => null,
                ]);
            }

            $this->auditService->log(
                action: 'create',
                entityType: 'Employee',
                entityId: $employee->id,
                before: null,
                after: [
                    'employee' => $employee->toArray(),
                    'username' => $user->username,
                    'org_unit_id' => $validated['org_unit_id'],
                ]
            );
        });

        return redirect()->back()->with('success', "Pegawai {$validated['name']} berhasil ditambahkan. Akun pengguna telah dibuat dengan password sementara: {$defaultPassword}");
    }

    public function show(Employee $employee): Response
    {
        Gate::authorize('view', $employee);

        $employee->load([
            'assignments.orgUnit',
            'assignments.position',
            'user.userRoles.role',
            'user.userRoles.orgUnit',
        ]);

        $orgUnits = OrgUnit::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $positions = Position::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'org_unit_id']);

        return Inertia::render('Platform/Employees/Show', [
            'employee' => $employee,
            'orgUnits' => $orgUnits,
            'positions' => $positions,
        ]);
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        Gate::authorize('update', $employee);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip' => [
                'required',
                'string',
                'max:20',
                Rule::unique(Employee::class, 'nip')->ignore($employee->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(Employee::class, 'email')->ignore($employee->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'gender' => ['nullable', 'string', 'in:male,female'],
            'birth_date' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ]);

        $before = $employee->toArray();
        $employee->update($validated);

        // Update name in user if exists
        if ($employee->user) {
            $employee->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'is_active' => $validated['is_active'] ?? $employee->is_active,
            ]);
        }

        $this->auditService->log(
            action: 'update',
            entityType: 'Employee',
            entityId: $employee->id,
            before: $before,
            after: $employee->fresh()?->toArray()
        );

        return redirect()->back()->with('success', "Data pegawai {$employee->name} berhasil diperbarui.");
    }

    public function assign(Request $request, Employee $employee): RedirectResponse
    {
        Gate::authorize('update', $employee);

        $validated = $request->validate([
            'org_unit_id' => ['required', 'uuid', Rule::exists(OrgUnit::class, 'id')],
            'position_id' => ['required', 'uuid', Rule::exists(Position::class, 'id')],
            'started_at' => ['required', 'date'],
            'assignment_type' => ['required', 'string', 'in:permanent,temporary,acting'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($employee, $validated, $request) {
            // Tutup penugasan aktif sebelumnya (pola riwayat bertanggal)
            /** @var EmployeeAssignment|null $activeAssignment */
            $activeAssignment = $employee->activeAssignment;
            if ($activeAssignment) {
                $endDate = Carbon::parse($validated['started_at'])->subDay()->toDateString();
                $activeAssignment->update(['ended_at' => $endDate]);
            }

            // Buat penugasan baru
            $newAssignment = EmployeeAssignment::create([
                'employee_id' => $employee->id,
                'org_unit_id' => $validated['org_unit_id'],
                'position_id' => $validated['position_id'],
                'started_at' => $validated['started_at'],
                'ended_at' => null,
                'assignment_type' => $validated['assignment_type'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()?->employee_id,
            ]);

            $this->auditService->log(
                action: 'reassign',
                entityType: 'EmployeeAssignment',
                entityId: $newAssignment->id,
                before: $activeAssignment?->toArray(),
                after: $newAssignment->toArray()
            );
        });

        return redirect()->back()->with('success', "Penugasan baru pegawai {$employee->name} berhasil disimpan.");
    }
}
