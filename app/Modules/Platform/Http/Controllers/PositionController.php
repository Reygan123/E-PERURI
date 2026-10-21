<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Contracts\AuditServiceInterface;
use App\Modules\Platform\Models\OrgUnit;
use App\Modules\Platform\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PositionController extends Controller
{
    public function __construct(
        protected AuditServiceInterface $auditService
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Position::class);

        $query = Position::query()->with('orgUnit')->orderBy('name');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('code', 'ilike', "%{$search}%");
            });
        }

        if ($orgUnitId = $request->input('org_unit_id')) {
            $query->where('org_unit_id', $orgUnitId);
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $positions = $query->paginate(15)->withQueryString();

        $orgUnits = OrgUnit::query()
            ->where('is_active', true)
            ->orderBy('level')
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return Inertia::render('Platform/Positions/Index', [
            'positions' => $positions,
            'orgUnits' => $orgUnits,
            'filters' => $request->only(['search', 'org_unit_id', 'type']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Position::class);

        $validated = $request->validate([
            'org_unit_id' => ['required', 'uuid', Rule::exists(OrgUnit::class, 'id')],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique(Position::class, 'code')],
            'type' => ['required', 'string', 'in:structural,functional'],
            'level' => ['nullable', 'integer', 'between:1,10'],
            'is_active' => ['boolean'],
        ]);

        $position = Position::create($validated);

        $this->auditService->log(
            action: 'create',
            entityType: 'Position',
            entityId: $position->id,
            before: null,
            after: $position->toArray()
        );

        return redirect()->back()->with('success', "Jabatan {$position->name} berhasil ditambahkan.");
    }

    public function update(Request $request, Position $position): RedirectResponse
    {
        Gate::authorize('update', $position);

        $validated = $request->validate([
            'org_unit_id' => ['required', 'uuid', Rule::exists(OrgUnit::class, 'id')],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique(Position::class, 'code')->ignore($position->id),
            ],
            'type' => ['required', 'string', 'in:structural,functional'],
            'level' => ['nullable', 'integer', 'between:1,10'],
            'is_active' => ['boolean'],
        ]);

        $before = $position->toArray();
        $position->update($validated);

        $this->auditService->log(
            action: 'update',
            entityType: 'Position',
            entityId: $position->id,
            before: $before,
            after: $position->fresh()?->toArray()
        );

        return redirect()->back()->with('success', "Jabatan {$position->name} berhasil diperbarui.");
    }

    public function destroy(Position $position): RedirectResponse
    {
        Gate::authorize('delete', $position);

        $before = $position->toArray();
        $position->update(['is_active' => false]);
        $position->delete();

        $this->auditService->log(
            action: 'delete',
            entityType: 'Position',
            entityId: $position->id,
            before: $before,
            after: null
        );

        return redirect()->back()->with('success', "Jabatan {$position->name} berhasil dinonaktifkan.");
    }
}
