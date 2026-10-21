<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Contracts\AuditServiceInterface;
use App\Modules\Platform\Models\OrgUnit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OrgUnitController extends Controller
{
    public function __construct(
        protected AuditServiceInterface $auditService
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', OrgUnit::class);

        $query = OrgUnit::query()->with('parent')->orderBy('level')->orderBy('code');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('code', 'ilike', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $orgUnits = $query->get();

        $parentOptions = OrgUnit::query()
            ->where('is_active', true)
            ->where('level', '<', 4)
            ->orderBy('level')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'level']);

        return Inertia::render('Platform/OrgUnits/Index', [
            'orgUnits' => $orgUnits,
            'parentOptions' => $parentOptions,
            'filters' => $request->only(['search', 'type']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', OrgUnit::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique(OrgUnit::class, 'code')],
            'parent_id' => ['nullable', 'uuid', Rule::exists(OrgUnit::class, 'id')],
            'type' => ['required', 'string', 'in:direksi,divisi,departemen,seksi,lainnya'],
            'level' => ['required', 'integer', 'between:1,4'],
            'is_active' => ['boolean'],
        ]);

        $orgUnit = OrgUnit::create($validated);

        $this->auditService->log(
            action: 'create',
            entityType: 'OrgUnit',
            entityId: $orgUnit->id,
            before: null,
            after: $orgUnit->toArray()
        );

        return redirect()->back()->with('success', "Unit organisasi {$orgUnit->name} berhasil ditambahkan.");
    }

    public function update(Request $request, OrgUnit $orgUnit): RedirectResponse
    {
        Gate::authorize('update', $orgUnit);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique(OrgUnit::class, 'code')->ignore($orgUnit->id),
            ],
            'parent_id' => [
                'nullable',
                'uuid',
                Rule::exists(OrgUnit::class, 'id'),
                Rule::notIn([$orgUnit->id]),
            ],
            'type' => ['required', 'string', 'in:direksi,divisi,departemen,seksi,lainnya'],
            'level' => ['required', 'integer', 'between:1,4'],
            'is_active' => ['boolean'],
        ]);

        $before = $orgUnit->toArray();
        $orgUnit->update($validated);

        $this->auditService->log(
            action: 'update',
            entityType: 'OrgUnit',
            entityId: $orgUnit->id,
            before: $before,
            after: $orgUnit->fresh()?->toArray()
        );

        return redirect()->back()->with('success', "Unit organisasi {$orgUnit->name} berhasil diperbarui.");
    }

    public function destroy(OrgUnit $orgUnit): RedirectResponse
    {
        Gate::authorize('delete', $orgUnit);

        $before = $orgUnit->toArray();
        $orgUnit->update(['is_active' => false]);
        $orgUnit->delete();

        $this->auditService->log(
            action: 'delete',
            entityType: 'OrgUnit',
            entityId: $orgUnit->id,
            before: $before,
            after: null
        );

        return redirect()->back()->with('success', "Unit organisasi {$orgUnit->name} berhasil dinonaktifkan.");
    }
}
