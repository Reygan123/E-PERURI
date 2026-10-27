<?php

declare(strict_types=1);

namespace App\Modules\Platform\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;

interface NotificationServiceInterface
{
    /**
     * Send a notification to a specific employee or group of employees.
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
    ): void;

    /**
     * Get count of unread notifications for a user.
     */
    public function getUnreadCount(string $userId): int;

    /**
     * Get notifications list for user with optional module filter and pagination limit.
     *
     * @return array<string, mixed> Array containing 'notifications', 'unread_count', 'total'
     */
    public function getNotificationsForUser(string $userId, ?string $module = null, int $limit = 20): array;

    /**
     * Mark a single notification as read, enforcing user ownership.
     */
    public function markAsRead(string $notificationId, string $userId): bool;

    /**
     * Mark all unread notifications as read for a user, optionally scoped to a module.
     */
    public function markAllAsRead(string $userId, ?string $module = null): int;

    /**
     * Paginate notifications for a user with optional module filter and search term.
     *
     * @return LengthAwarePaginator<int, mixed>
     */
    public function paginateNotificationsForUser(
        string $userId,
        ?string $module = null,
        ?string $search = null,
        int $perPage = 20
    ): LengthAwarePaginator;
}
