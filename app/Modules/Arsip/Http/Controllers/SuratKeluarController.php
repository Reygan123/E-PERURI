<?php

declare(strict_types=1);

namespace App\Modules\Arsip\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Arsip\Http\Requests\StoreSuratKeluarRequest;
use App\Modules\Arsip\Models\Letter;
use App\Modules\Arsip\Models\LetterType;
use App\Modules\Arsip\Services\LetterService;
use App\Modules\Platform\Contracts\DirectoryServiceInterface;
use App\Modules\Platform\Contracts\UserResolverInterface;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;
use Inertia\Response;

class SuratKeluarController extends Controller
{
    public function __construct(
        protected LetterService $letterService,
        protected DirectoryServiceInterface $directoryService,
        protected UserResolverInterface $userResolver
    ) {}

    /**
     * Display a listing of Surat Keluar (Type Code: A).
     */
    public function index(Request $request): Response
    {
        $employeeId = $this->userResolver->getCurrentEmployeeId() ?? '';
        $type = LetterType::where('code', 'A')->firstOrFail();

        $query = Letter::where('type_id', $type->id)
            ->with(['type'])
            ->latest('created_at');

        // Search filter
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('document_number', 'like', "%{$search}%")
                    ->orWhere('destination_text', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $letters = $query->paginate(15)->withQueryString();

        // Check if current user is blocked by double upload rule
        $incompleteLetter = null;
        if ($employeeId !== '') {
            $incompleteLetter = Letter::where('creator_employee_id', $employeeId)
                ->where('type_id', $type->id)
                ->where('is_double_upload_complete', false)
                ->first(['id', 'subject', 'document_number', 'status']);
        }

        return Inertia::render('Arsip/SuratKeluar/Index', [
            'letters' => $letters,
            'filters' => [
                'search' => $request->query('search', ''),
                'status' => $request->query('status', ''),
            ],
            'canCreate' => $incompleteLetter === null,
            'incompleteLetter' => $incompleteLetter,
        ]);
    }

    /**
     * Show the form for creating a new Surat Keluar.
     */
    public function create(): Response|RedirectResponse
    {
        $employeeId = $this->userResolver->getCurrentEmployeeId() ?? '';
        $type = LetterType::where('code', 'A')->firstOrFail();

        // Check double upload blocking
        if ($employeeId !== '') {
            $incompleteLetter = Letter::where('creator_employee_id', $employeeId)
                ->where('type_id', $type->id)
                ->where('is_double_upload_complete', false)
                ->first();

            if ($incompleteLetter) {
                return redirect()->route('arsip.surat-keluar.index')
                    ->with('error', 'Pembuatan Surat Keluar baru diblokir: Anda masih memiliki Surat Keluar sebelumnya yang belum mengunggah berkas scan basah final.');
            }
        }

        $orgUnits = $this->directoryService->getAllOrgUnits();

        return Inertia::render('Arsip/SuratKeluar/Create', [
            'type' => $type,
            'orgUnits' => $orgUnits,
        ]);
    }

    /**
     * Store a newly created Surat Keluar in storage.
     */
    public function store(StoreSuratKeluarRequest $request): RedirectResponse
    {
        $employeeId = $this->userResolver->getCurrentEmployeeId();
        if (! $employeeId) {
            return back()->with('error', 'Akun pengguna Anda belum tertaut dengan data pegawai.');
        }

        $type = LetterType::where('code', 'A')->firstOrFail();
        $validated = $request->validated();
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

            return redirect()->route('arsip.surat-keluar.show', $letter->id)
                ->with('success', 'Draf Surat Keluar berhasil dibuat.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified Surat Keluar.
     */
    public function show(Letter $letter): Response
    {
        $letter->load([
            'type',
            'destinations',
            'approvals',
            'attachments',
        ]);

        $originUnit = $this->directoryService->getOrgUnit($letter->origin_unit_id);
        $creator = $this->directoryService->getEmployeeById($letter->creator_employee_id);

        return Inertia::render('Arsip/SuratKeluar/Show', [
            'letter' => $letter,
            'originUnit' => $originUnit,
            'creator' => $creator,
        ]);
    }

    /**
     * Submit draft letter into review and approval workflow.
     */
    public function submit(Letter $letter): RedirectResponse
    {
        $employeeId = $this->userResolver->getCurrentEmployeeId();
        if (! $employeeId) {
            return back()->with('error', 'Akun pengguna belum tertaut data pegawai.');
        }

        try {
            $this->letterService->submitForApproval($letter, $employeeId);

            return back()->with('success', 'Surat berhasil diajukan untuk proses persetujuan.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Upload signed scan file to complete double upload requirement.
     */
    public function uploadSigned(Request $request, Letter $letter): RedirectResponse
    {
        $request->validate([
            'signed_file' => ['required', 'file', 'mimes:pdf', 'max:15360'],
        ], [
            'signed_file.required' => 'Berkas scan basah wajib diunggah.',
            'signed_file.mimes' => 'Berkas harus dalam format PDF.',
            'signed_file.max' => 'Ukuran berkas maksimal 15MB.',
        ]);

        $employeeId = $this->userResolver->getCurrentEmployeeId();
        if (! $employeeId) {
            return back()->with('error', 'Akun pengguna belum tertaut data pegawai.');
        }

        /** @var UploadedFile $file */
        $file = $request->file('signed_file');

        try {
            $this->letterService->uploadSignedScan($letter, $file, $employeeId);

            return back()->with('success', 'Berkas scan basah final berhasil diunggah. Surat telah berstatus resmi.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
