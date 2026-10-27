<?php

declare(strict_types=1);

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Contracts\NotificationServiceInterface;
use App\Modules\Platform\Http\Resources\NotificationResource;
use App\Modules\Platform\Models\NotificationRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    /**
     * Allowed modules for filtering notifications.
     */
    private const ALLOWED_MODULES = ['layanan', 'arsip', 'sdm', 'platform'];

    public function __construct(
        protected NotificationServiceInterface $notificationService
    ) {}

    /**
     * Get real-time JSON feed of unread count and latest notifications.
     */
    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['unread_count' => 0, 'notifications' => [], 'total' => 0]);
        }

        $module = $request->query('module');
        $moduleStr = (is_string($module) && in_array($module, self::ALLOWED_MODULES, true)) ? $module : null;
        $limit = min(50, max(1, (int) $request->query('limit', 15)));

        $data = $this->notificationService->getNotificationsForUser(
            userId: $user->id,
            module: $moduleStr,
            limit: $limit
        );

        return response()->json($data);
    }

    /**
     * Mark a single notification as read with strict ownership enforcement (Anti-IDOR) and UUID validation.
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        if (! Str::isUuid($id)) {
            return response()->json([
                'success' => false,
                'message' => 'Format ID notifikasi tidak valid.',
            ], 422);
        }

        $success = $this->notificationService->markAsRead($id, $user->id);

        if (! $success) {
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan atau Anda tidak memiliki akses.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'unread_count' => $this->notificationService->getUnreadCount($user->id),
        ]);
    }

    /**
     * Mark all notifications as read for current user, optionally filtered by module.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $module = $request->input('module');
        $moduleStr = (is_string($module) && in_array($module, self::ALLOWED_MODULES, true)) ? $module : null;

        $updatedCount = $this->notificationService->markAllAsRead($user->id, $moduleStr);

        return response()->json([
            'success' => true,
            'updated_count' => $updatedCount,
            'unread_count' => $this->notificationService->getUnreadCount($user->id),
        ]);
    }

    /**
     * Full page view for Unified Notification Center.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $selectedModule = $request->query('module');
        $selectedModuleStr = (is_string($selectedModule) && in_array($selectedModule, self::ALLOWED_MODULES, true))
            ? $selectedModule
            : null;

        $search = $request->query('search');
        $searchStr = is_string($search) ? mb_substr(trim($search), 0, 100) : null;
        if ($searchStr === '') {
            $searchStr = null;
        }

        $paginated = $this->notificationService->paginateNotificationsForUser(
            userId: $user->id,
            module: $selectedModuleStr,
            search: $searchStr,
            perPage: 20
        );

        $paginated->through(fn (NotificationRecord $record) => (new NotificationResource($record))->resolve());

        $unreadCount = $this->notificationService->getUnreadCount($user->id);

        return Inertia::render('Platform/Notifications/Index', [
            'notifications' => $paginated,
            'unreadCount' => $unreadCount,
            'filters' => [
                'module' => $selectedModuleStr ?? 'all',
                'search' => $searchStr ?? '',
            ],
        ]);
    }
}
