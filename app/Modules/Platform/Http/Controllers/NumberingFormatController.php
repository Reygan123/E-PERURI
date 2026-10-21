<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Contracts\AuditServiceInterface;
use App\Modules\Platform\Contracts\NumberingServiceInterface;
use App\Modules\Platform\Models\NumberingFormat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class NumberingFormatController extends Controller
{
    public function __construct(
        protected AuditServiceInterface $auditService,
        protected NumberingServiceInterface $numberingService
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', NumberingFormat::class);

        $formats = NumberingFormat::query()
            ->with('sequences')
            ->orderBy('module')
            ->orderBy('code')
            ->get();

        return Inertia::render('Platform/Numbering/Index', [
            'formats' => $formats,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', NumberingFormat::class);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique(NumberingFormat::class, 'code')],
            'module' => ['required', 'string', 'max:50'],
            'template' => ['required', 'string', 'max:255'],
            'reset_period' => ['required', 'string', 'in:never,yearly,monthly'],
            'prefix' => ['nullable', 'string', 'max:50'],
        ]);

        $format = NumberingFormat::create($validated);

        $this->auditService->log(
            action: 'create',
            entityType: 'NumberingFormat',
            entityId: $format->id,
            before: null,
            after: $format->toArray()
        );

        return redirect()->back()->with('success', "Format penomoran {$format->code} berhasil dibuat.");
    }

    public function update(Request $request, NumberingFormat $numberingFormat): RedirectResponse
    {
        Gate::authorize('update', $numberingFormat);

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique(NumberingFormat::class, 'code')->ignore($numberingFormat->id),
            ],
            'module' => ['required', 'string', 'max:50'],
            'template' => ['required', 'string', 'max:255'],
            'reset_period' => ['required', 'string', 'in:never,yearly,monthly'],
            'prefix' => ['nullable', 'string', 'max:50'],
        ]);

        $before = $numberingFormat->toArray();
        $numberingFormat->update($validated);

        $this->auditService->log(
            action: 'update',
            entityType: 'NumberingFormat',
            entityId: $numberingFormat->id,
            before: $before,
            after: $numberingFormat->fresh()?->toArray()
        );

        return redirect()->back()->with('success', "Format penomoran {$numberingFormat->code} berhasil diperbarui.");
    }

    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'template' => ['required', 'string'],
            'prefix' => ['nullable', 'string'],
        ]);

        $now = Carbon::now();
        $romanMonths = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $bulanRomawi = $romanMonths[$now->month - 1];

        $preview = str_replace(
            ['{PREFIX}', '{SEQ}', '{BULAN}', '{TAHUN}'],
            [$validated['prefix'] ?? 'DOC', '0001', $bulanRomawi, (string) $now->year],
            $validated['template']
        );

        return response()->json(['preview' => $preview]);
    }
}
