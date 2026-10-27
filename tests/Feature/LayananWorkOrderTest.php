<?php

use App\Models\User;
use App\Modules\Layanan\Database\Seeders\LayananDeviceTypeSeeder;
use App\Modules\Layanan\Database\Seeders\LayananWorkOrderSeeder;
use App\Modules\Layanan\Models\DeviceType;
use App\Modules\Layanan\Models\WorkOrder;
use App\Modules\Platform\Database\Seeders\DevelopmentUserSeeder;
use App\Modules\Platform\Database\Seeders\NumberingFormatSeeder;
use App\Modules\Platform\Database\Seeders\OrgUnitSeeder;
use App\Modules\Platform\Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => OrgUnitSeeder::class]);
    $this->artisan('db:seed', ['--class' => RoleAndPermissionSeeder::class]);
    $this->artisan('db:seed', ['--class' => DevelopmentUserSeeder::class]);
    $this->artisan('db:seed', ['--class' => NumberingFormatSeeder::class]);
    $this->artisan('db:seed', ['--class' => LayananDeviceTypeSeeder::class]);
    $this->artisan('db:seed', ['--class' => LayananWorkOrderSeeder::class]);
});

test('unauthenticated user cannot access work order list or create form', function () {
    $responseIndex = $this->get('/layanan/work-orders');
    $responseIndex->assertRedirect('/login');

    $responseCreate = $this->get('/layanan/work-orders/create');
    $responseCreate->assertRedirect('/login');
});

test('authenticated user can view work orders catalog and metrics', function () {
    $user = User::where('email', 'superadmin@kertas-padalarang.co.id')->firstOrFail();

    $response = $this->actingAs($user)->get('/layanan/work-orders');
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Layanan/WorkOrder/Index')
        ->has('workOrders.data')
        ->has('metrics')
        ->has('deviceTypes')
        ->where('metrics.total', fn ($val) => $val >= 3)
    );
});

test('authenticated user can submit new work order and receive atomic ticket number', function () {
    Storage::fake('local');

    $user = User::where('email', 'stafdirut@kertas-padalarang.co.id')->firstOrFail();
    $deviceType = DeviceType::where('code', 'HARDWARE')->firstOrFail();

    $payload = [
        'section_name' => 'Ruang Administrasi Produksi Unit I',
        'device_type_id' => $deviceType->id,
        'device_name' => 'Laptop Dell Latitude 5420 Ruang Tata Usaha',
        'it_inventory_code' => 'IT-PC-2025-099',
        'issue_description' => 'Komputer mendadak mati saat membuka software pelaporan produksi.',
        'reference_memo_number' => '102/MEMO/PROD/IV/2026',
        'photo' => UploadedFile::fake()->image('kerusakan.jpg', 600, 400),
    ];

    $response = $this->actingAs($user)->post('/layanan/work-orders', $payload);

    $workOrder = WorkOrder::where('device_name', $payload['device_name'])->first();
    expect($workOrder)->not->toBeNull();
    expect($workOrder->status)->toBe('pending');
    expect($workOrder->ticket_number)->toStartWith('IT');
    expect($workOrder->photo_file_id)->not->toBeNull();

    $response->assertRedirect("/layanan/work-orders/{$workOrder->id}");
});

test('validation prevents submission with missing required fields', function () {
    $user = User::where('email', 'stafdirut@kertas-padalarang.co.id')->firstOrFail();

    $response = $this->actingAs($user)->post('/layanan/work-orders', [
        'section_name' => '',
        'device_type_id' => '',
        'device_name' => '',
        'issue_description' => 'abc', // less than 5 chars
    ]);

    $response->assertSessionHasErrors(['section_name', 'device_type_id', 'device_name', 'issue_description']);
});

test('regular employee without technician role is forbidden from processing work orders', function () {
    $regularUser = User::where('email', 'stafdirut@kertas-padalarang.co.id')->firstOrFail();
    $workOrder = WorkOrder::where('status', 'pending')->firstOrFail();

    // Regular employee tries to claim ticket
    $response = $this->actingAs($regularUser)->post("/layanan/work-orders/{$workOrder->id}/process", [
        'take_ticket' => true,
    ]);

    $response->assertForbidden();
});

test('technician can claim ticket, submit diagnosis, and complete repair', function () {
    $technician = User::where('email', 'superadmin@kertas-padalarang.co.id')->firstOrFail();
    $workOrder = WorkOrder::where('status', 'pending')->firstOrFail();

    // 1. Claim ticket (take ticket)
    $responseClaim = $this->actingAs($technician)->post("/layanan/work-orders/{$workOrder->id}/process", [
        'take_ticket' => true,
        'status' => 'in_progress',
    ]);
    $responseClaim->assertRedirect();

    $workOrder->refresh();
    expect($workOrder->status)->toBe('in_progress');
    expect($workOrder->started_at)->not->toBeNull();

    // 2. Update diagnosis
    $responseDiagnosis = $this->actingAs($technician)->post("/layanan/work-orders/{$workOrder->id}/process", [
        'examination_result' => 'repaired_internally',
        'estimated_duration' => '2 Jam Kerja',
        'technician_notes' => 'Pembersihan heatsink processor dan penggantian pasta termal.',
    ]);
    $responseDiagnosis->assertRedirect();

    $workOrder->refresh();
    expect($workOrder->examination_result)->toBe('repaired_internally');
    expect($workOrder->estimated_duration)->toBe('2 Jam Kerja');

    // 3. Complete repair
    $responseComplete = $this->actingAs($technician)->post("/layanan/work-orders/{$workOrder->id}/process", [
        'is_completed' => true,
        'status' => 'completed',
        'technician_notes' => 'Perbaikan tuntas, komputer kembali berjalan normal.',
    ]);
    $responseComplete->assertRedirect();

    $workOrder->refresh();
    expect($workOrder->status)->toBe('completed');
    expect($workOrder->completed_at)->not->toBeNull();
});

test('user can access printable a4 standard form IT-FR-01', function () {
    $user = User::where('email', 'superadmin@kertas-padalarang.co.id')->firstOrFail();
    $workOrder = WorkOrder::firstOrFail();

    $response = $this->actingAs($user)->get("/layanan/work-orders/{$workOrder->id}/print");
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Layanan/WorkOrder/Print')
        ->has('ticket.ticket_number')
        ->has('ticket.requester')
    );
});
