<?php

declare(strict_types=1);

namespace App\Modules\Platform\Services;

use App\Models\User;
use App\Modules\Platform\Contracts\NotificationServiceInterface;
use App\Modules\Platform\Http\Resources\NotificationResource;
use App\Modules\Platform\Jobs\SendDispatchNotificationJob;
use App\Modules\Platform\Models\NotificationRecord;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationService implements NotificationServiceInterface
{
    /**
     * Send a notification to a specific employee or group of employees.
     * In-app database notifications are delivered synchronously for immediate visibility,
     * while external channels (mail, whatsapp) are dispatched asynchronously.
     *
     * @param  string|list<string>  $recipientEmployeeIds
     * @param  string  $title  Notification title
     * @param  string  $message  Notification body text
     * @param  list<string>  $channels  Channels to dispatch to: 'database', 'mail', 'whatsapp'
     * @param  array<string, mixed>  $metadata  Additional payload or action URL
     */
    public function send(
        string|array $recipientEmployeeIds,
        string $title,
        string $message,
        array $channels = ['database'],
        array $metadata = []
    ): void {
        $employeeIds = is_array($recipientEmployeeIds) ? $recipientEmployeeIds : [$recipientEmployeeIds];

        // Resolve both by employee_id AND directly by user id
        $userIdsByEmployee = User::whereIn('employee_id', $employeeIds)->pluck('id')->all();
        $userIdsDirect = User::whereIn('id', $employeeIds)->pluck('id')->all();
        $userIds = array_values(array_unique(array_merge($userIdsByEmployee, $userIdsDirect)));

        if (empty($userIds)) {
            return;
        }

        // Deliver database notifications immediately and synchronously for instant in-app visibility
        if (in_array('database', $channels, true)) {
            $now = now();
            $inserts = [];
            foreach ($userIds as $userId) {
                $inserts[] = [
                    'id' => (string) Str::uuid(),
                    'user_id' => $userId,
                    'type' => $metadata['type'] ?? 'general',
                    'channel' => 'database',
                    'payload' => json_encode([
                        'title' => $title,
                        'message' => $message,
                        'module' => $metadata['module'] ?? 'platform',
                        'action_url' => $metadata['action_url'] ?? null,
                        'icon' => $metadata['icon'] ?? null,
                        'metadata' => $metadata,
                    ]),
                    'read_at' => null,
                    'sent_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            if (! empty($inserts)) {
                DB::table('platform.notifications')->insert($inserts);
            }
        }

        // Dispatch external asynchronous channels (mail, whatsapp) to queue
        $externalChannels = array_values(array_diff($channels, ['database']));
        if (! empty($externalChannels)) {
            SendDispatchNotificationJob::dispatch(
                recipientUserIds: $userIds,
                title: $title,
                message: $message,
                channels: $externalChannels,
                metadata: $metadata
            );
        }
    }

    /**
     * Get count of unread notifications for a user.
     */
    public function getUnreadCount(string $userId): int
    {
        return NotificationRecord::forUser($userId)->unread()->count();
    }

    /**
     * Get notifications list for user with optional module filter and pagination limit.
     *
     * @return array<string, mixed>
     */
    public function getNotificationsForUser(string $userId, ?string $module = null, int $limit = 20): array
    {
        $query = NotificationRecord::forUser($userId)
            ->latest('created_at');

        if ($module !== null && $module !== '' && $module !== 'all') {
            $query->forModule($module);
        }

        $total = (clone $query)->count();
        $unreadCount = NotificationRecord::forUser($userId)->unread()->count();
        $items = $query->limit($limit)->get();

        return [
            'notifications' => NotificationResource::collection($items)->resolve(),
            'unread_count' => $unreadCount,
            'total' => $total,
        ];
    }

    /**
     * Mark a single notification as read, enforcing user ownership (Anti-IDOR).
     */
    public function markAsRead(string $notificationId, string $userId): bool
    {
        $notification = NotificationRecord::forUser($userId)
            ->where('id', $notificationId)
            ->first();

        if (! $notification) {
            return false;
        }

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        return true;
    }

    /**
     * Mark all unread notifications as read for a user, optionally scoped to a module.
     */
    public function markAllAsRead(string $userId, ?string $module = null): int
    {
        $query = NotificationRecord::forUser($userId)->unread();

        if ($module !== null && $module !== '' && $module !== 'all') {
            $query->forModule($module);
        }

        return $query->update(['read_at' => now()]);
    }

    /**
     * Paginate notifications for a user with optional module filter and search term.
     *
     * @return LengthAwarePaginator<int, NotificationRecord>
     */
    public function paginateNotificationsForUser(
        string $userId,
        ?string $module = null,
        ?string $search = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        $query = NotificationRecord::forUser($userId)
            ->latest('created_at');

        if ($module !== null && $module !== '' && $module !== 'all') {
            $query->forModule($module);
        }

        if ($search !== null && trim($search) !== '') {
            $query->search($search);
        }

        /** @var LengthAwarePaginator<int, NotificationRecord> $paginated */
        $paginated = $query->paginate($perPage)->withQueryString();

        return $paginated;
    }
}
