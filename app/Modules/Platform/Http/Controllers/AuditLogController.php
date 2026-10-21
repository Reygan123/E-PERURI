<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Contracts\UserResolverInterface;
use App\Modules\Platform\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function __construct(
        protected UserResolverInterface $userResolver
    ) {}

    public function index(Request $request): Response
    {
        // Deny by default: check permission platform.audit.view or super-admin role
        $user = $request->user();
        if (! $this->userResolver->hasPermission('platform.audit.view', $user) && ! $this->userResolver->hasRole('super-admin', $user)) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat Buku Audit Log.');
        }

        $query = AuditLog::query()->orderByDesc('created_at');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('entity_type', 'ilike', "%{$search}%")
                    ->orWhere('action', 'ilike', "%{$search}%")
                    ->orWhere('user_agent', 'ilike', "%{$search}%");
            });
        }

        if ($module = $request->input('module')) {
            $query->where('module', $module);
        }

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $auditLogs = $query->paginate(20)->withQueryString();

        $modules = AuditLog::query()->distinct()->pluck('module');
        $actions = AuditLog::query()->distinct()->pluck('action');

        return Inertia::render('Platform/AuditLogs/Index', [
            'auditLogs' => $auditLogs,
            'modules' => $modules,
            'actions' => $actions,
            'filters' => $request->only(['search', 'module', 'action', 'date_from', 'date_to']),
        ]);
    }
}
