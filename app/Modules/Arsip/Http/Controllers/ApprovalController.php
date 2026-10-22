<?php

declare(strict_types=1);

namespace App\Modules\Arsip\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Arsip\Http\Requests\DecideApprovalRequest;
use App\Modules\Arsip\Models\LetterApproval;
use App\Modules\Arsip\Services\ApprovalWorkflowService;
use App\Modules\Platform\Contracts\DirectoryServiceInterface;
use App\Modules\Platform\Contracts\UserResolverInterface;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ApprovalController extends Controller
{
    public function __construct(
        protected ApprovalWorkflowService $workflowService,
        protected DirectoryServiceInterface $directoryService,
        protected UserResolverInterface $userResolver
    ) {}

    /**
     * Display the approval workbench (inbox and completed decisions).
     */
    public function index(): Response
    {
        $employeeId = $this->userResolver->getCurrentEmployeeId() ?? '';

        $pendingApprovals = LetterApproval::where('approver_employee_id', $employeeId)
            ->where('status', 'pending')
            ->with(['letter.type'])
            ->latest('created_at')
            ->get();

        $historyApprovals = LetterApproval::where('approver_employee_id', $employeeId)
            ->whereIn('status', ['approved', 'rejected', 'revision_requested'])
            ->with(['letter.type'])
            ->latest('decided_at')
            ->take(30)
            ->get();

        return Inertia::render('Arsip/Persetujuan/Index', [
            'pendingApprovals' => $pendingApprovals,
            'historyApprovals' => $historyApprovals,
        ]);
    }

    /**
     * Process an approval decision (Approve, Review, Revise, Reject).
     */
    public function decide(DecideApprovalRequest $request, LetterApproval $approval): RedirectResponse
    {
        $employeeId = $this->userResolver->getCurrentEmployeeId();
        if (! $employeeId) {
            return back()->with('error', 'Akun pengguna belum tertaut data pegawai.');
        }

        $validated = $request->validated();

        try {
            $this->workflowService->processDecision(
                approval: $approval,
                action: $validated['action'],
                notes: $validated['notes'] ?? null,
                actorEmployeeId: $employeeId,
                passphrase: $validated['passphrase'] ?? null
            );

            $actionIndonesian = match ($validated['action']) {
                'approve' => 'disetujui',
                'review' => 'telah ditinjau (paraf)',
                'revise' => 'dikembalikan untuk revisi',
                'reject' => 'ditolak',
                default => 'diproses',
            };

            return back()->with('success', "Persetujuan surat berhasil {$actionIndonesian}.");
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
