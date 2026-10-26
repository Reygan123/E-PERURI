<?php

declare(strict_types=1);

namespace App\Modules\Platform\Jobs;

use App\Models\User;
use App\Modules\Platform\Mail\LetterApprovalNotificationMail;
use App\Modules\Platform\Services\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class SendDispatchNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  list<string>  $recipientUserIds
     * @param  list<string>  $channels
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public array $recipientUserIds,
        public string $title,
        public string $message,
        public array $channels = ['database'],
        public array $metadata = []
    ) {}

    public function handle(WhatsAppChannel $whatsAppChannel): void
    {
        $users = User::whereIn('id', $this->recipientUserIds)->get();

        foreach ($users as $user) {
            foreach ($this->channels as $channel) {
                try {
                    match ($channel) {
                        'database' => $this->deliverToDatabase($user),
                        'mail' => $this->deliverToMail($user),
                        'whatsapp' => $this->deliverToWhatsApp($user, $whatsAppChannel),
                        default => null,
                    };
                } catch (Throwable $e) {
                    Log::error("Failed delivering notification via [{$channel}] to user [{$user->id}]: {$e->getMessage()}");
                }
            }
        }
    }

    protected function deliverToDatabase(User $user): void
    {
        DB::table('platform.notifications')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'type' => $this->metadata['type'] ?? 'general',
            'channel' => 'database',
            'payload' => json_encode([
                'title' => $this->title,
                'message' => $this->message,
                'metadata' => $this->metadata,
            ]),
            'read_at' => null,
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function deliverToMail(User $user): void
    {
        if (empty($user->email)) {
            return;
        }

        Mail::to($user->email)->send(
            new LetterApprovalNotificationMail(
                recipientName: $user->name,
                notificationTitle: $this->title,
                notificationMessage: $this->message,
                letterMetadata: $this->metadata
            )
        );
    }

    protected function deliverToWhatsApp(User $user, WhatsAppChannel $whatsAppChannel): void
    {
        // Try getting phone from employee record if available, fallback to user attribute
        $employee = $user->employee;
        $phone = $employee ? $employee->phone : ($user->phone ?? null);

        if (empty($phone)) {
            Log::info("WhatsApp dispatch skipped for user [{$user->name}]: no phone number recorded.");

            return;
        }

        $actionUrl = $this->metadata['action_url'] ?? config('app.url').'/arsip/persetujuan';
        $letterType = $this->metadata['letter_type'] ?? 'Naskah Dinas';
        $subject = $this->metadata['subject'] ?? $this->title;
        $creatorName = $this->metadata['creator_name'] ?? 'Rekan Kerja';
        $urgency = ucfirst((string) ($this->metadata['urgency'] ?? 'Biasa'));

        $formattedWaMessage = "*Pemberitahuan Persetujuan Naskah Dinas - E-Arsip PTKP*\n"
            ."--------------------------------------------------\n"
            ."Yth. Bapak/Ibu *{$user->name}*,\n\n"
            ."Terdapat 1 naskah dinas baru yang memerlukan persetujuan/tindak lanjut Anda:\n"
            ."• *Jenis Naskah:* {$letterType}\n"
            ."• *Perihal:* {$subject}\n"
            ."• *Perancang:* {$creatorName}\n"
            ."• *Tingkat Urgensi:* {$urgency}\n\n"
            ."Silakan akses Meja Persetujuan melalui tautan berikut:\n"
            ."{$actionUrl}\n\n"
            .'_Pesan otomatis Sistem Tata Kelola E-Arsip PT Kertas Padalarang_';

        $whatsAppChannel->send((string) $phone, $formattedWaMessage);
    }
}
