<?php

use App\Models\User;
use App\Modules\Layanan\Database\Seeders\LayananDeviceTypeSeeder;
use App\Modules\Layanan\Models\DeviceType;
use App\Modules\Platform\Database\Seeders\DevelopmentUserSeeder;
use App\Modules\Platform\Database\Seeders\NumberingFormatSeeder;
use App\Modules\Platform\Database\Seeders\OrgUnitSeeder;
use App\Modules\Platform\Database\Seeders\RoleAndPermissionSeeder;
use App\Modules\Platform\Models\NotificationRecord;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => OrgUnitSeeder::class]);
    $this->artisan('db:seed', ['--class' => RoleAndPermissionSeeder::class]);
    $this->artisan('db:seed', ['--class' => DevelopmentUserSeeder::class]);
    $this->artisan('db:seed', ['--class' => NumberingFormatSeeder::class]);
    $this->artisan('db:seed', ['--class' => LayananDeviceTypeSeeder::class]);
});

test('unauthenticated user cannot access notifications feed or index', function () {
    $responseFeed = $this->getJson('/notifications/feed');
    $responseFeed->assertUnauthorized();

    $responseIndex = $this->get('/notifications');
    $responseIndex->assertRedirect('/login');
});

test('authenticated user can retrieve notification feed and unread counter', function () {
    $user = User::where('email', 'superadmin@kertas-padalarang.co.id')->firstOrFail();

    // Create 2 test notifications directly
    NotificationRecord::create([
        'id' => (string) Str::uuid(),
        'user_id' => $user->id,
        'type' => 'work_order_created',
        'channel' => 'database',
        'payload' => [
            'title' => 'Tiket Baru: IT001',
            'message' => 'Permohonan printer rusak di Seksi Mutu',
            'module' => 'layanan',
            'action_url' => '/layanan/work-orders/sample-id',
        ],
        'read_at' => null,
        'sent_at' => now(),
    ]);

    NotificationRecord::create([
        'id' => (string) Str::uuid(),
        'user_id' => $user->id,
        'type' => 'disposition_received',
        'channel' => 'database',
        'payload' => [
            'title' => 'Disposisi Surat Masuk',
            'message' => 'Segera tindak lanjuti surat dinas no 012',
            'module' => 'arsip',
            'action_url' => '/arsip/surat-masuk',
        ],
        'read_at' => null,
        'sent_at' => now(),
    ]);

    $response = $this->actingAs($user)->getJson('/notifications/feed');

    $response->assertOk();
    $response->assertJsonStructure([
        'notifications',
        'unread_count',
        'total',
    ]);
    expect($response->json('unread_count'))->toBeGreaterThanOrEqual(2);
    expect($response->json('total'))->toBeGreaterThanOrEqual(2);
});

test('authenticated user can filter notifications feed by module', function () {
    $user = User::where('email', 'superadmin@kertas-padalarang.co.id')->firstOrFail();

    // Create 1 Layanan notification and 1 Arsip notification
    NotificationRecord::create([
        'id' => (string) Str::uuid(),
        'user_id' => $user->id,
        'type' => 'work_order_created',
        'channel' => 'database',
        'payload' => [
            'title' => 'Tiket Layanan IT',
            'message' => 'Perbaikan laptop',
            'module' => 'layanan',
        ],
        'read_at' => null,
        'sent_at' => now(),
    ]);

    NotificationRecord::create([
        'id' => (string) Str::uuid(),
        'user_id' => $user->id,
        'type' => 'disposition_received',
        'channel' => 'database',
        'payload' => [
            'title' => 'Disposisi Arsip',
            'message' => 'Surat masuk dinas',
            'module' => 'arsip',
        ],
        'read_at' => null,
        'sent_at' => now(),
    ]);

    $responseLayanan = $this->actingAs($user)->getJson('/notifications/feed?module=layanan');
    $responseLayanan->assertOk();
    $layananItems = $responseLayanan->json('notifications');
    foreach ($layananItems as $item) {
        expect($item['module'])->toBe('layanan');
    }

    $responseArsip = $this->actingAs($user)->getJson('/notifications/feed?module=arsip');
    $responseArsip->assertOk();
    $arsipItems = $responseArsip->json('notifications');
    foreach ($arsipItems as $item) {
        expect($item['module'])->toBe('arsip');
    }
});

test('user can mark own notification as read', function () {
    $user = User::where('email', 'superadmin@kertas-padalarang.co.id')->firstOrFail();

    $notif = NotificationRecord::create([
        'id' => (string) Str::uuid(),
        'user_id' => $user->id,
        'type' => 'general',
        'channel' => 'database',
        'payload' => [
            'title' => 'Pemberitahuan Sistem',
            'message' => 'Pengumuman pemeliharaan server',
            'module' => 'platform',
        ],
        'read_at' => null,
        'sent_at' => now(),
    ]);

    expect($notif->read_at)->toBeNull();

    $response = $this->actingAs($user)->postJson("/notifications/{$notif->id}/read");
    $response->assertOk();
    $response->assertJson(['success' => true]);

    $notif->refresh();
    expect($notif->read_at)->not->toBeNull();
});

test('anti-idor: user cannot mark another user notification as read', function () {
    $userA = User::where('email', 'superadmin@kertas-padalarang.co.id')->firstOrFail();
    $userB = User::where('email', 'stafdirut@kertas-padalarang.co.id')->firstOrFail();

    // Notif belongs to user B
    $notifB = NotificationRecord::create([
        'id' => (string) Str::uuid(),
        'user_id' => $userB->id,
        'type' => 'general',
        'channel' => 'database',
        'payload' => [
            'title' => 'Rahasia User B',
            'message' => 'Pesan khusus untuk User B',
            'module' => 'platform',
        ],
        'read_at' => null,
        'sent_at' => now(),
    ]);

    // User A attempts to mark user B's notification as read
    $response = $this->actingAs($userA)->postJson("/notifications/{$notifB->id}/read");
    $response->assertNotFound();

    $notifB->refresh();
    expect($notifB->read_at)->toBeNull();
});

test('user can mark all notifications as read', function () {
    $user = User::where('email', 'superadmin@kertas-padalarang.co.id')->firstOrFail();

    NotificationRecord::create([
        'id' => (string) Str::uuid(),
        'user_id' => $user->id,
        'type' => 'general',
        'channel' => 'database',
        'payload' => ['title' => 'Test 1', 'message' => 'Msg 1', 'module' => 'platform'],
        'read_at' => null,
        'sent_at' => now(),
    ]);

    NotificationRecord::create([
        'id' => (string) Str::uuid(),
        'user_id' => $user->id,
        'type' => 'general',
        'channel' => 'database',
        'payload' => ['title' => 'Test 2', 'message' => 'Msg 2', 'module' => 'platform'],
        'read_at' => null,
        'sent_at' => now(),
    ]);

    $response = $this->actingAs($user)->postJson('/notifications/read-all');
    $response->assertOk();
    $response->assertJson(['success' => true, 'unread_count' => 0]);

    $unreadAfter = NotificationRecord::forUser($user->id)->unread()->count();
    expect($unreadAfter)->toBe(0);
});

test('submitting work order sends notification to IT technician', function () {
    $superadmin = User::where('email', 'superadmin@kertas-padalarang.co.id')->firstOrFail();
    $requester = User::where('email', 'stafdirut@kertas-padalarang.co.id')->firstOrFail();
    $deviceType = DeviceType::firstOrFail();

    $unreadBefore = NotificationRecord::forUser($superadmin->id)->unread()->count();

    $response = $this->actingAs($requester)->post('/layanan/work-orders', [
        'section_name' => 'Ruang Administrasi Lantai 2',
        'device_type_id' => $deviceType->id,
        'device_name' => 'PC Kasir Lenovo M70q',
        'it_inventory_code' => 'IT-PC-012',
        'issue_description' => 'Mati total setelah korsleting listrik',
    ]);

    $response->assertRedirect();

    $unreadAfter = NotificationRecord::forUser($superadmin->id)->unread()->count();
    expect($unreadAfter)->toBeGreaterThan($unreadBefore);

    $latestNotif = NotificationRecord::forUser($superadmin->id)->latest('created_at')->first();
    expect($latestNotif->module)->toBe('layanan');
    expect($latestNotif->title)->toContain('Tiket Baru');
});
