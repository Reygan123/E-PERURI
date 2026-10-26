<?php

declare(strict_types=1);

namespace App\Modules\Arsip\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Arsip\Http\Requests\StoreSpecialDocumentRequest;
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

class SpecialDocumentController extends Controller
{
    public function __construct(
        protected LetterService $letterService,
        protected DirectoryServiceInterface $directoryService,
        protected UserResolverInterface $userResolver
    ) {}

    /**
     * Display a listing of Formulir Khusus (SPPD, SPB, BP).
     */
    public function index(Request $request): Response
    {
        $types = LetterType::whereIn('code', ['SPPD', 'SPB', 'BP'])->pluck('id');

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

        return Inertia::render('Arsip/Khusus/Index', [
            'letters' => $letters,
            'filters' => [
                'search' => $request->query('search', ''),
                'type_code' => $request->query('type_code', ''),
            ],
            'typeOptions' => LetterType::whereIn('code', ['SPPD', 'SPB', 'BP'])->get(),
        ]);
    }

    /**
     * Show form for creating a new Special Document.
     */
    public function create(Request $request): Response
    {
        $types = LetterType::whereIn('code', ['SPPD', 'SPB', 'BP'])->get();
        $orgUnits = $this->directoryService->getAllOrgUnits();

        return Inertia::render('Arsip/Khusus/Create', [
            'types' => $types,
            'orgUnits' => $orgUnits,
            'selectedTypeCode' => $request->query('type_code', $request->query('type', 'SPPD')),
        ]);
    }

    /**
     * Store newly created Special Document.
     */
    public function store(StoreSpecialDocumentRequest $request): RedirectResponse
    {
        $employeeId = $this->userResolver->getCurrentEmployeeId();
        if (! $employeeId) {
            return back()->with('error', 'Akun pengguna belum tertaut data pegawai.');
        }

        $validated = $request->validated();
        $type = LetterType::where('code', $validated['type_code'])->firstOrFail();
        $validated['type_id'] = $type->id;

        /** @var list<UploadedFile> $attachments */
        $attachments = $request->file('attachments', []);

        try {
            $letter = $this->letterService->createDraft(
                data: $validated,
                creatorEmployeeId: $employeeId,
                additionalAttachments: $attachments
            );

            return redirect()->route('arsip.khusus.show', $letter->id)
                ->with('success', "Dokumen {$type->name} berhasil dibuat.");
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Show the specified Special Document.
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

        return Inertia::render('Arsip/Khusus/Show', [
            'letter' => $letter,
            'originUnit' => $originUnit,
            'creator' => $creator,
        ]);
    }

    /**
     * Submit draft Special Document into review and approval workflow.
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

            return back()->with('success', 'Dokumen berhasil diajukan untuk proses persetujuan.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show form for editing / revising an existing Special Document.
     */
    public function edit(Letter $letter): Response
    {
        Gate::authorize('update', $letter);

        $letter->load([
            'type',
            'destinations',
            'approvals' => fn ($q) => $q->orderBy('step_order'),
        ]);

        $types = LetterType::whereIn('code', ['SPPD', 'SPB', 'BP'])->get();
        $orgUnits = $this->directoryService->getAllOrgUnits();

        $latestRevision = $letter->approvals
            ->where('status', 'revision_requested')
            ->last();

        return Inertia::render('Arsip/Khusus/Edit', [
            'letter' => $letter,
            'types' => $types,
            'orgUnits' => $orgUnits,
            'revisionNote' => $latestRevision?->notes,
        ]);
    }

    /**
     * Update and optionally resubmit Special Document.
     */
    public function update(StoreSpecialDocumentRequest $request, Letter $letter): RedirectResponse
    {
        Gate::authorize('update', $letter);

        $employeeId = $this->userResolver->getCurrentEmployeeId();
        if (! $employeeId) {
            return back()->with('error', 'Akun pengguna belum tertaut data pegawai.');
        }

        $validated = $request->validated();
        $type = LetterType::where('code', $validated['type_code'])->firstOrFail();
        $validated['type_id'] = $type->id;

        /** @var list<UploadedFile> $attachments */
        $attachments = $request->file('attachments', []);

        try {
            $updatedLetter = $this->letterService->updateDraft(
                letter: $letter,
                data: $validated,
                actorEmployeeId: $employeeId,
                draftFile: null,
                destinationUnitIds: [],
                additionalAttachments: $attachments
            );

            if ($request->boolean('submit_now')) {
                $this->letterService->submitForApproval($updatedLetter, $employeeId);
            }

            return redirect()->route('arsip.khusus.show', $letter->id)
                ->with('success', "Dokumen {$type->name} berhasil diperbaiki dan diajukan ulang ke alur persetujuan.");
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
