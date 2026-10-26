<?php

declare(strict_types=1);

namespace App\Modules\Arsip\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Arsip\Http\Requests\StoreNaskahDinasRequest;
use App\Modules\Arsip\Models\Letter;
use App\Modules\Arsip\Models\LetterType;
use App\Modules\Arsip\Services\LetterService;
use App\Modules\Platform\Contracts\DirectoryServiceInterface;
use App\Modules\Platform\Contracts\UserResolverInterface;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class NaskahDinasController extends Controller
{
    public function __construct(
        protected LetterService $letterService,
        protected DirectoryServiceInterface $directoryService,
        protected UserResolverInterface $userResolver
    ) {}

    /**
     * Display a listing of Naskah Dinas (ND, E, ST, PENG, KPTS).
     */
    public function index(Request $request): Response
    {
        $types = LetterType::whereIn('code', ['ND', 'E', 'ST', 'PENG', 'KPTS'])->pluck('id');

        $query = Letter::whereIn('type_id', $types)
            ->with(['type'])
            ->latest('created_at');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('document_number', 'like', "%{$search}%");
            });
        }

        if ($typeCode = $request->query('type_code')) {
            $query->whereHas('type', function ($q) use ($typeCode) {
                $q->where('code', $typeCode);
            });
        }

        $letters = $query->paginate(15)->withQueryString();

        return Inertia::render('Arsip/NaskahDinas/Index', [
            'letters' => $letters,
            'filters' => [
                'search' => $request->query('search', ''),
                'type_code' => $request->query('type_code', ''),
            ],
            'typeOptions' => LetterType::whereIn('code', ['ND', 'E', 'ST', 'PENG', 'KPTS'])->get(),
        ]);
    }

    /**
     * Show form for creating a new Naskah Dinas.
     */
    public function create(Request $request): Response
    {
        $types = LetterType::whereIn('code', ['ND', 'E', 'ST', 'PENG', 'KPTS'])->get();
        $orgUnits = $this->directoryService->getAllOrgUnits();

        return Inertia::render('Arsip/NaskahDinas/Create', [
            'types' => $types,
            'orgUnits' => $orgUnits,
            'defaultTypeCode' => $request->query('type_code', 'ND'),
        ]);
    }

    /**
     * Store newly created Naskah Dinas.
     */
    public function store(StoreNaskahDinasRequest $request): RedirectResponse
    {
        $employeeId = $this->userResolver->getCurrentEmployeeId();
        if (! $employeeId) {
            return back()->with('error', 'Akun pengguna belum tertaut data pegawai.');
        }

        $validated = $request->validated();
        $type = LetterType::where('code', $validated['type_code'])->firstOrFail();
        $validated['type_id'] = $type->id;

        /** @var UploadedFile|null $draftFile */
        $draftFile = $request->file('draft_file');

        /** @var list<UploadedFile> $attachments */
        $attachments = $request->file('attachments', []);

        /** @var list<string> $destinationUnitIds */
        $destinationUnitIds = $validated['destination_unit_ids'] ?? [];

        try {
            $letter = $this->letterService->createDraft(
                data: $validated,
                creatorEmployeeId: $employeeId,
                draftFile: $draftFile,
                destinationUnitIds: $destinationUnitIds,
                additionalAttachments: $attachments
            );

            if ($request->boolean('submit_now')) {
                $this->letterService->submitForApproval($letter, $employeeId);
            }

            return redirect()->route('arsip.naskah-dinas.show', $letter->id)
                ->with('success', 'Naskah Dinas berhasil dibuat.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Show the specified Naskah Dinas.
     */
    public function show(Letter $letter): Response
    {
        Gate::authorize('view', $letter);

        $letter->load([
            'type',
            'destinations',
            'approvals',
            'attachments',
        ]);

        $originUnit = $this->directoryService->getOrgUnit($letter->origin_unit_id);
        $creator = $this->directoryService->getEmployeeById($letter->creator_employee_id);

        return Inertia::render('Arsip/NaskahDinas/Show', [
            'letter' => $letter,
            'originUnit' => $originUnit,
            'creator' => $creator,
        ]);
    }

    /**
     * Submit draft Naskah Dinas into review and approval workflow.
     */
    public function submit(Letter $letter): RedirectResponse
    {
        Gate::authorize('submit', $letter);

        $employeeId = $this->userResolver->getCurrentEmployeeId();
        if (! $employeeId) {
            return back()->with('error', 'Akun pengguna belum tertaut data pegawai.');
        }

        try {
            $this->letterService->submitForApproval($letter, $employeeId);

            return back()->with('success', 'Naskah Dinas berhasil diajukan untuk proses persetujuan.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show form for editing / revising an existing Naskah Dinas.
     */
    public function edit(Letter $letter): Response
    {
        Gate::authorize('update', $letter);

        $letter->load([
            'type',
            'destinations',
            'approvals' => fn ($q) => $q->orderBy('step_order'),
        ]);

        $types = LetterType::whereIn('code', ['ND', 'E', 'ST', 'PENG', 'KPTS'])->get();
        $orgUnits = $this->directoryService->getAllOrgUnits();

        $latestRevision = $letter->approvals
            ->where('status', 'revision_requested')
            ->last();

        return Inertia::render('Arsip/NaskahDinas/Edit', [
            'letter' => $letter,
            'types' => $types,
            'orgUnits' => $orgUnits,
            'revisionNote' => $latestRevision?->notes,
        ]);
    }

    /**
     * Update and optionally resubmit Naskah Dinas.
     */
    public function update(StoreNaskahDinasRequest $request, Letter $letter): RedirectResponse
    {
        Gate::authorize('update', $letter);

        $employeeId = $this->userResolver->getCurrentEmployeeId();
        if (! $employeeId) {
            return back()->with('error', 'Akun pengguna belum tertaut data pegawai.');
        }

        $validated = $request->validated();
        $type = LetterType::where('code', $validated['type_code'])->firstOrFail();
        $validated['type_id'] = $type->id;

        /** @var UploadedFile|null $draftFile */
        $draftFile = $request->file('draft_file');

        /** @var list<UploadedFile> $attachments */
        $attachments = $request->file('attachments', []);

        /** @var list<string> $destinationUnitIds */
        $destinationUnitIds = $validated['destination_unit_ids'] ?? [];

        try {
            $updatedLetter = $this->letterService->updateDraft(
                letter: $letter,
                data: $validated,
                actorEmployeeId: $employeeId,
                draftFile: $draftFile,
                destinationUnitIds: $destinationUnitIds,
                additionalAttachments: $attachments
            );

            if ($request->boolean('submit_now')) {
                $this->letterService->submitForApproval($updatedLetter, $employeeId);
            }

            return redirect()->route('arsip.naskah-dinas.show', $letter->id)
                ->with('success', 'Naskah Dinas berhasil diperbaiki dan diajukan ulang ke alur persetujuan.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
