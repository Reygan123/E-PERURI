<?php

declare(strict_types=1);

namespace App\Modules\Layanan\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Layanan\Http\Requests\ProcessWorkOrderRequest;
use App\Modules\Layanan\Http\Requests\StoreWorkOrderRequest;
use App\Modules\Layanan\Models\DeviceType;
use App\Modules\Layanan\Models\WorkOrder;
use App\Modules\Layanan\Services\WorkOrderService;
use App\Modules\Platform\Contracts\DirectoryServiceInterface;
use App\Modules\Platform\Contracts\UserResolverInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WorkOrderController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected WorkOrderService $workOrderService,
        protected DirectoryServiceInterface $directory,
        protected UserResolverInterface $userResolver
    ) {}

    /**
     * Display a listing of work orders with filtering & statistics.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', WorkOrder::class);

        $filters = $request->only(['status', 'device_type_id', 'search']);
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $workOrders = $this->workOrderService->getWorkOrdersPaginated($filters, $user, 15);
        $metrics = $this->workOrderService->getMetrics($user);
        $deviceTypes = DeviceType::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);

        $isTechnician = $this->userResolver->hasRole('super-admin', $user)
            || $this->userResolver->hasRole('admin-it', $user)
            || $this->userResolver->hasRole('teknisi-it', $user);

        return Inertia::render('Layanan/WorkOrder/Index', [
            'workOrders' => $workOrders,
            'metrics' => $metrics,
            'filters' => $filters,
            'deviceTypes' => $deviceTypes,
            'isTechnician' => $isTechnician,
        ]);
    }

    /**
     * Show form for submitting new IT work order.
     */
    public function create(Request $request): Response
    {
        $this->authorize('create', WorkOrder::class);

        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $employeeId = $user->employee_id ?? $this->userResolver->getCurrentEmployeeId();
        $employee = $employeeId ? $this->directory->getEmployeeById((string) $employeeId) : null;
        $orgUnit = ($employee && isset($employee['org_unit_id']))
            ? $this->directory->getOrgUnit((string) $employee['org_unit_id'])
            : null;

        $deviceTypes = DeviceType::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);

        return Inertia::render('Layanan/WorkOrder/Create', [
            'deviceTypes' => $deviceTypes,
            'requester' => [
                'name' => is_array($employee) ? ($employee['name'] ?? $employee['full_name'] ?? $user->name) : $user->name,
                'nip' => is_array($employee) ? ($employee['nip'] ?? '-') : '-',
                'email' => is_array($employee) ? ($employee['email'] ?? $user->email) : $user->email,
                'org_unit_name' => is_array($orgUnit) ? ($orgUnit['name'] ?? 'Unit Kerja PTKP') : 'Unit Kerja PTKP',
                'org_unit_code' => is_array($orgUnit) ? ($orgUnit['code'] ?? '-') : '-',
            ],
        ]);
    }

    /**
     * Store newly created IT work order.
     */
    public function store(StoreWorkOrderRequest $request): RedirectResponse
    {
        $this->authorize('create', WorkOrder::class);

        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $photo = $request->file('photo');
        $workOrder = $this->workOrderService->storeWorkOrder($request->validated(), $user, $photo);

        return redirect()->route('layanan.work-orders.show', $workOrder->id)
            ->with('success', "Permohonan perbaikan IT berhasil diajukan dengan nomor tiket {$workOrder->ticket_number}.");
    }

    /**
     * Display the specified work order details, timeline, and technician action panel.
     */
    public function show(string $id, Request $request): Response
    {
        $workOrder = WorkOrder::findOrFail($id);
        $this->authorize('view', $workOrder);

        $ticket = $this->workOrderService->getTicketDetail($id);
        if (! $ticket) {
            abort(404);
        }

        $user = $request->user();
        $canProcess = $user ? $user->can('process', $workOrder) : false;

        return Inertia::render('Layanan/WorkOrder/Show', [
            'ticket' => $ticket,
            'canProcess' => $canProcess,
        ]);
    }

    /**
     * Process work order by technician (take ticket, diagnose, complete).
     */
    public function process(ProcessWorkOrderRequest $request, string $id): RedirectResponse
    {
        $workOrder = WorkOrder::findOrFail($id);
        $this->authorize('process', $workOrder);

        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $this->workOrderService->processWorkOrder($workOrder, $request->validated(), $user);

        return redirect()->back()
            ->with('success', 'Status dan catatan penanganan teknis berhasil diperbarui.');
    }

    /**
     * Display printable standard IT-FR-01/REV00 form sheet (A4).
     */
    public function print(string $id): Response
    {
        $workOrder = WorkOrder::findOrFail($id);
        $this->authorize('view', $workOrder);

        $ticket = $this->workOrderService->getTicketDetail($id);
        if (! $ticket) {
            abort(404);
        }

        return Inertia::render('Layanan/WorkOrder/Print', [
            'ticket' => $ticket,
        ]);
    }

    /**
     * Securely stream uploaded physical damage proof photo.
     */
    public function photo(string $id): BinaryFileResponse
    {
        $workOrder = WorkOrder::findOrFail($id);
        $this->authorize('view', $workOrder);

        if (! $workOrder->photo_file_id) {
            abort(404, 'Tidak ada foto bukti kerusakan pada tiket ini.');
        }

        $path = $this->workOrderService->getPhotoPath($workOrder->photo_file_id);
        if (! $path || ! Storage::disk('local')->exists($path)) {
            abort(404, 'Berkas foto tidak ditemukan pada sistem penyimpanan.');
        }

        $fullPath = Storage::disk('local')->path($path);

        return response()->file($fullPath);
    }
}
