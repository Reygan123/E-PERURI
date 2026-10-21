<?php

namespace App\Modules\Platform\Services;

use App\Models\User;
use App\Modules\Platform\Contracts\NotificationServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NotificationService implements NotificationServiceInterface
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
    ): void {
        $employeeIds = is_array($recipientEmployeeIds) ? $recipientEmployeeIds : [$recipientEmployeeIds];

        $users = User::whereIn('employee_id', $employeeIds)->get();

        foreach ($users as $user) {
            foreach ($channels as $channel) {
                match ($channel) {
                    'database' => $this->sendToDatabase($user, $title, $message, $metadata),
                    'mail' => $this->sendToMail($user, $title, $message, $metadata),
                    'whatsapp' => $this->sendToWhatsApp($user, $title, $message, $metadata),
                    default => null,
                };
            }
        }
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function sendToDatabase(User $user, string $title, string $message, array $metadata): void
    {
        DB::table('platform.notifications')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'type' => $metadata['type'] ?? 'general',
            'channel' => 'database',
            'payload' => json_encode([
                'title' => $title,
                'message' => $message,
                'metadata' => $metadata,
            ]),
            'read_at' => null,
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function sendToMail(User $user, string $title, string $message, array $metadata): void
    {
        Log::info("Dispatching email notification to user {$user->id} ({$user->email}): {$title}");
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function sendToWhatsApp(User $user, string $title, string $message, array $metadata): void
    {
        Log::info("Dispatching WhatsApp notification to user {$user->id}: {$title} - {$message}");
    }
}
