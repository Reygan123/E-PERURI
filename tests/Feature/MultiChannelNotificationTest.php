<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Platform\database\seeders\DevelopmentUserSeeder;
use App\Modules\Platform\database\seeders\OrgUnitSeeder;
use App\Modules\Platform\database\seeders\RoleAndPermissionSeeder;
use App\Modules\Platform\Jobs\SendDispatchNotificationJob;
use App\Modules\Platform\Mail\LetterApprovalNotificationMail;
use App\Modules\Platform\Services\Channels\WhatsAppChannel;
use App\Modules\Platform\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MultiChannelNotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => OrgUnitSeeder::class]);
        $this->artisan('db:seed', ['--class' => RoleAndPermissionSeeder::class]);
        $this->artisan('db:seed', ['--class' => DevelopmentUserSeeder::class]);
    }

    public function test_whatsapp_channel_normalizes_phone_numbers_correctly(): void
    {
        $channel = new WhatsAppChannel;

        $this->assertEquals('6281234567890', $channel->normalizePhoneNumber('081234567890'));
        $this->assertEquals('6281234567890', $channel->normalizePhoneNumber('6281234567890'));
        $this->assertEquals('6281234567890', $channel->normalizePhoneNumber('+62 812-3456-7890'));
        $this->assertEquals('6285712345678', $channel->normalizePhoneNumber('85712345678'));
        $this->assertEquals('', $channel->normalizePhoneNumber(''));
    }

    public function test_notification_service_dispatches_queue_job(): void
    {
        Queue::fake();

        $user = User::where('username', 'superadmin')->firstOrFail();
        $service = app(NotificationService::class);

        $service->send(
            recipientEmployeeIds: $user->employee_id,
            title: 'Uji Notifikasi Antrean',
            message: 'Pesan pengujian notifikasi queue.',
            channels: ['database', 'mail', 'whatsapp'],
            metadata: ['letter_type' => 'Nota Dinas', 'subject' => 'Uji Coba Sistem']
        );

        Queue::assertPushed(SendDispatchNotificationJob::class, function ($job) use ($user) {
            return in_array($user->id, $job->recipientUserIds)
                && $job->title === 'Uji Notifikasi Antrean'
                && in_array('whatsapp', $job->channels)
                && in_array('mail', $job->channels);
        });
    }

    public function test_send_dispatch_notification_job_delivers_database_and_mail(): void
    {
        Mail::fake();

        $user = User::where('username', 'superadmin')->firstOrFail();
        config(['services.whatsapp.driver' => 'array']);

        $job = new SendDispatchNotificationJob(
            recipientUserIds: [$user->id],
            title: 'Naskah Dinas Perlu Paraf',
            message: 'Terdapat 1 naskah dinas baru yang perlu diparaf.',
            channels: ['database', 'mail', 'whatsapp'],
            metadata: [
                'letter_type' => 'Surat Keluar',
                'subject' => 'Pengujian Pengiriman Kertas Uang',
                'creator_name' => 'Operator PK1',
                'urgency' => 'segera',
                'action_url' => 'http://127.0.0.1:8088/arsip/persetujuan',
            ]
        );

        $job->handle(new WhatsAppChannel);

        // 1. Verify database notification record created
        $record = DB::table('platform.notifications')
            ->where('user_id', $user->id)
            ->where('channel', 'database')
            ->latest('created_at')
            ->first();

        $this->assertNotNull($record);
        $payload = json_decode($record->payload, true);
        $this->assertEquals('Naskah Dinas Perlu Paraf', $payload['title']);
        $this->assertEquals('Surat Keluar', $payload['metadata']['letter_type']);

        // 2. Verify Mail sent
        Mail::assertSent(LetterApprovalNotificationMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email)
                && $mail->notificationTitle === 'Naskah Dinas Perlu Paraf'
                && $mail->recipientName === $user->name;
        });
    }
}
