<?php

declare(strict_types=1);

namespace App\Modules\Arsip\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Arsip\Http\Requests\CreateDispositionRequest;
use App\Modules\Arsip\Http\Requests\StoreSuratMasukRequest;
use App\Modules\Arsip\Models\Letter;
use App\Modules\Arsip\Models\LetterAttachment;
use App\Modules\Arsip\Models\LetterType;
use App\Modules\Arsip\Services\DispositionService;
use App\Modules\Arsip\Services\LetterService;
use App\Modules\Platform\Contracts\AuditServiceInterface;
use App\Modules\Platform\Contracts\DirectoryServiceInterface;
use App\Modules\Platform\Contracts\FilesServiceInterface;
use App\Modules\Platform\Contracts\UserResolverInterface;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SuratMasukController extends Controller
{
    public function __construct(
        protected LetterService $letterService,
        protected DispositionService $dispositionService,
        protected FilesServiceInterface $filesService,
        protected DirectoryServiceInterface $directoryService,
        protected AuditServiceInterface $auditService,
        protected UserResolverInterface $userResolver
    ) {}

    /**
     * Display a listing of Surat Masuk (Type Code: B).
     */
    public function index(Request $request): Response
    {
        $type = LetterType::where('code', 'B')->firstOrFail();

        $query = Letter::where('type_id', $type->id)
            ->with(['type', 'dispositions'])
            ->latest('created_at');

        // Search filter
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('document_number', 'like', "%{$search}%")
                    ->orWhere('destination_text', 'like', "%{$search}%");
            });
        }

        $letters = $query->paginate(15)->withQueryString();

        return Inertia::render('Arsip/SuratMasuk/Index', [
            'letters' => $letters,
            'filters' => [
                'search' => $request->query('search', ''),
            ],
        ]);
    }

    /**
     * Show the form for recording a new Surat Masuk.
     */
    public function create(): Response
    {
        $type = LetterType::where('code', 'B')->firstOrFail();
        $orgUnits = $this->directoryService->getAllOrgUnits();

        return Inertia::render('Arsip/SuratMasuk/Create', [
            'type' => $type,
            'orgUnits' => $orgUnits,
        ]);
    }

    /**
     * Store a newly scanned Surat Masuk into archive.
     */
    public function store(StoreSuratMasukRequest $request): RedirectResponse
    {
        $employeeId = $this->userResolver->getCurrentEmployeeId();
        if (! $employeeId) {
            return back()->with('error', 'Akun pengguna belum tertaut data pegawai.');
        }

        $type = LetterType::where('code', 'B')->firstOrFail();
        $validated = $request->validated();

        /** @var UploadedFile $scannedFile */
        $scannedFile = $request->file('scanned_file');

        /** @var list<UploadedFile> $attachments */
        $attachments = $request->file('attachments', []);

        try {
            $letter = DB::transaction(function () use ($validated, $type, $employeeId, $scannedFile, $attachments) {
                // Store scanned physical letter
                $fileMeta = $this->filesService->store($scannedFile, 'arsip', 'surat_masuk', $employeeId);

                $letter = Letter::create([
                    'id' => (string) Str::uuid(),
                    'type_id' => $type->id,
                    'document_number' => $validated['document_number'], // Nomor surat fisik asal
                    'origin_unit_id' => $validated['origin_unit_id'],
                    'creator_employee_id' => $employeeId,
                    'destination_type' => 'external_text',
                    'destination_text' => $validated['sender_organization'],
                    'subject' => $validated['subject'],
                    'letter_date' => $validated['letter_date'],
                    'status' => 'published', // Surat masuk langsung berstatus aktif/tercatat
                    'confidentiality' => $validated['confidentiality'],
                    'urgency' => $validated['urgency'],
                    'signed_file_id' => $fileMeta['id'],
                    'is_double_upload_complete' => true,
                    'content_body' => [
                        'received_date' => $validated['received_date'],
                        'sender_organization' => $validated['sender_organization'],
                    ],
                ]);

                // Additional attachments
                foreach ($attachments as $attachment) {
                    $meta = $this->filesService->store($attachment, 'arsip', 'lampiran_surat_masuk', $employeeId);
                    LetterAttachment::create([
                        'id' => (string) Str::uuid(),
                        'letter_id' => $letter->id,
                        'file_id' => $meta['id'],
                        'caption' => $attachment->getClientOriginalName(),
                    ]);
                }

                $this->auditService->log(
                    module: 'arsip',
                    action: 'REGISTER_SURAT_MASUK',
                    auditableType: 'Letter',
                    auditableId: (string) $letter->id,
                    newValues: [
                        'document_number' => $letter->document_number,
                        'subject' => $letter->subject,
                        'sender' => $validated['sender_organization'],
                    ],
                    notes: 'Pencatatan surat masuk baru'
                );

                return $letter;
            });

            return redirect()->route('arsip.surat-masuk.show', $letter->id)
                ->with('success', 'Surat Masuk berhasil dicatat ke sistem.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Display details and disposition sheet for the specified Surat Masuk.
     */
    public function show(Letter $letter): Response
    {
        Gate::authorize('view', $letter);

        $letter->load([
            'type',
            'attachments',
            'dispositions',
        ]);

        $recipientUnit = $this->directoryService->getOrgUnit($letter->origin_unit_id);
        $orgUnits = $this->directoryService->getAllOrgUnits();

        return Inertia::render('Arsip/SuratMasuk/Show', [
            'letter' => $letter,
            'recipientUnit' => $recipientUnit,
            'orgUnits' => $orgUnits,
        ]);
    }

    /**
     * Create a new disposition sheet on this letter.
     */
    public function createDisposition(CreateDispositionRequest $request, Letter $letter): RedirectResponse
    {
        $employeeId = $this->userResolver->getCurrentEmployeeId();
        if (! $employeeId) {
            return back()->with('error', 'Akun pengguna belum tertaut data pegawai.');
        }

        try {
            $this->dispositionService->createDisposition(
                letter: $letter,
                senderEmployeeId: $employeeId,
                data: $request->validated()
            );

            return back()->with('success', 'Lembar disposisi berhasil diterbitkan.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
