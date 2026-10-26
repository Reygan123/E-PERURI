<?php

declare(strict_types=1);

namespace App\Modules\Platform\Services;

use App\Models\User;
use App\Modules\Platform\Contracts\NotificationServiceInterface;
use App\Modules\Platform\Jobs\SendDispatchNotificationJob;

class NotificationService implements NotificationServiceInterface
{
    /**
     * Send a notification to a specific employee or group of employees.
     * Dispatched asynchronously via Laravel Queue worker.
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

        $userIds = User::whereIn('employee_id', $employeeIds)->pluck('id')->all();

        if (empty($userIds)) {
            return;
        }

        SendDispatchNotificationJob::dispatch(
            recipientUserIds: $userIds,
            title: $title,
            message: $message,
            channels: $channels,
            metadata: $metadata
        );
    }
}
