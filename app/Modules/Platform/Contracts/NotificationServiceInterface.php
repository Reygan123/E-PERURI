<?php

namespace App\Modules\Platform\Contracts;

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
}
