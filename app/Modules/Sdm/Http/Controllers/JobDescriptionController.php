<?php

declare(strict_types=1);

namespace App\Modules\Sdm\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Contracts\DirectoryServiceInterface;
use App\Modules\Sdm\Http\Requests\JobApprovalDecisionRequest;
use App\Modules\Sdm\Http\Requests\StoreJobDescriptionRequest;
use App\Modules\Sdm\Http\Requests\UpdateJobDescriptionRequest;
use App\Modules\Sdm\Models\JobDescription;
use App\Modules\Sdm\Services\JobDescriptionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class JobDescriptionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected JobDescriptionService $jobdeskService,
        protected DirectoryServiceInterface $directory
    ) {}

    /**
     * Display katalog direktori jabatan ala Telkom University.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', JobDescription::class);

        $filters = $request->only(['search', 'status', 'org_unit_id', 'job_group']);
        $jobs = $this->jobdeskService->getKatalogList($filters, 25);

        $orgUnits = DB::table('platform.org_units')
            ->where('is_active', true)
            ->select('id', 'name', 'code', 'type')
            ->orderBy('name')
            ->get();

        return Inertia::render('Sdm/Jobdesk/Index', [
            'jobs' => $jobs,
            'filters' => $filters,
            'orgUnits' => $orgUnits,
        ]);
    }

    /**
     * Display detailed corporate job description preview with 10 sections.
     */
    public function show(string $id): Response
    {
        $jobModel = JobDescription::findOrFail($id);
        $this->authorize('view', $jobModel);

        $job = $this->jobdeskService->getJobDetail($id);
        abort_if(! $job, 404, 'Job Description tidak ditemukan.');

        return Inertia::render('Sdm/Jobdesk/Show', [
            'job' => $job,
        ]);
    }

    /**
     * Show editor form for creating a new job description.
     */
    public function create(): Response
    {
        $this->authorize('create', JobDescription::class);

        $orgUnits = DB::table('platform.org_units')
            ->where('is_active', true)
            ->select('id', 'name', 'code', 'type')
            ->orderBy('name')
            ->get();

        $positions = DB::table('platform.positions')
            ->where('is_active', true)
            ->select('id', 'name', 'code', 'level', DB::raw("CONCAT('Grade ', level) as echelon"))
            ->orderBy('name')
            ->get();

        $employees = DB::table('platform.employees')
            ->where('is_active', true)
            ->select('id', 'nip', 'name', DB::raw('"name" as "full_name"'))
            ->orderBy('name')
            ->get();

        return Inertia::render('Sdm/Jobdesk/Editor', [
            'mode' => 'create',
            'orgUnits' => $orgUnits,
            'positions' => $positions,
            'employees' => $employees,
            'job' => null,
        ]);
    }

    /**
     * Store a newly created job description.
     */
    public function store(StoreJobDescriptionRequest $request): RedirectResponse
    {
        $this->authorize('create', JobDescription::class);

        $job = $this->jobdeskService->storeJob($request->validated(), $request->user());

        return redirect()->route('sdm.jobdesk.show', $job->id)
            ->with('success', 'Draf Job Description berhasil disimpan.');
    }

    /**
     * Show editor form for editing existing job description.
     */
    public function edit(string $id): Response
    {
        $jobModel = JobDescription::findOrFail($id);
        $this->authorize('update', $jobModel);

        $job = $this->jobdeskService->getJobDetail($id);
        abort_if(! $job, 404, 'Job Description tidak ditemukan.');

        $orgUnits = DB::table('platform.org_units')
            ->where('is_active', true)
            ->select('id', 'name', 'code', 'type')
            ->orderBy('name')
            ->get();

        $positions = DB::table('platform.positions')
            ->where('is_active', true)
            ->select('id', 'name', 'code', 'level', DB::raw("CONCAT('Grade ', level) as echelon"))
            ->orderBy('name')
            ->get();

        $employees = DB::table('platform.employees')
            ->where('is_active', true)
            ->select('id', 'nip', 'name', DB::raw('"name" as "full_name"'))
            ->orderBy('name')
            ->get();

        return Inertia::render('Sdm/Jobdesk/Editor', [
            'mode' => 'edit',
            'orgUnits' => $orgUnits,
            'positions' => $positions,
            'employees' => $employees,
            'job' => $job,
        ]);
    }

    /**
     * Update existing job description.
     */
    public function update(UpdateJobDescriptionRequest $request, string $id): RedirectResponse
    {
        $job = JobDescription::findOrFail($id);
        $this->authorize('update', $job);

        $this->jobdeskService->updateJob($job, $request->validated(), $request->user());

        return redirect()->route('sdm.jobdesk.show', $job->id)
            ->with('success', 'Perubahan Job Description berhasil disimpan.');
    }

    /**
     * Submit job description for review and approval workflow.
     */
    public function submit(Request $request, string $id): RedirectResponse
    {
        $job = JobDescription::findOrFail($id);
        $this->authorize('update', $job);

        $this->jobdeskService->submitForApproval($job, $request->user());

        return redirect()->route('sdm.jobdesk.show', $job->id)
            ->with('success', 'Job Description telah berhasil diajukan untuk verifikasi.');
    }

    /**
     * Process approval or rejection.
     */
    public function approve(JobApprovalDecisionRequest $request, string $id): RedirectResponse
    {
        $job = JobDescription::findOrFail($id);
        $this->authorize('approve', $job);

        $this->jobdeskService->processApproval(
            $job,
            $request->input('stage'),
            $request->input('decision'),
            $request->input('notes'),
            $request->user()
        );

        $msg = $request->input('decision') === 'approved'
            ? 'Persetujuan Job Description berhasil diproses.'
            : 'Revisi Job Description telah dikembalikan ke pembuat.';

        return redirect()->route('sdm.jobdesk.show', $job->id)
            ->with('success', $msg);
    }

    /**
     * Printable A4 Sheet View.
     */
    public function print(string $id): Response
    {
        $jobModel = JobDescription::findOrFail($id);
        $this->authorize('view', $jobModel);

        $job = $this->jobdeskService->getJobDetail($id);
        abort_if(! $job, 404, 'Job Description tidak ditemukan.');

        return Inertia::render('Sdm/Jobdesk/Print', [
            'job' => $job,
        ]);
    }
}
