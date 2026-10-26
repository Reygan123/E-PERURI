<?php

use App\Models\User;
use App\Modules\Arsip\Database\Seeders\LetterTypeSeeder;
use App\Modules\Arsip\Models\Letter;
use App\Modules\Arsip\Models\LetterType;
use App\Modules\Arsip\Services\LetterPdfService;
use App\Modules\Platform\Database\Seeders\DevelopmentUserSeeder;
use App\Modules\Platform\Database\Seeders\NumberingFormatSeeder;
use App\Modules\Platform\Database\Seeders\OrgUnitSeeder;
use App\Modules\Platform\Database\Seeders\RoleAndPermissionSeeder;
use App\Modules\Platform\Models\Employee;
use App\Modules\Platform\Models\OrgUnit;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => OrgUnitSeeder::class]);
    $this->artisan('db:seed', ['--class' => RoleAndPermissionSeeder::class]);
    $this->artisan('db:seed', ['--class' => DevelopmentUserSeeder::class]);
    $this->artisan('db:seed', ['--class' => NumberingFormatSeeder::class]);
    $this->artisan('db:seed', ['--class' => LetterTypeSeeder::class]);
});

test('letter pdf service renders html for all digital document types', function (string $typeCode, array $content) {
    $unit = OrgUnit::firstOrFail();
    $employee = Employee::firstOrFail();
    $type = LetterType::where('code', $typeCode)->firstOrFail();

    $letter = Letter::create([
        'id' => (string) Str::uuid(),
        'type_id' => $type->id,
        'origin_unit_id' => $unit->id,
        'creator_employee_id' => $employee->id,
        'destination_type' => 'internal_units',
        'subject' => 'Uji Template '.$typeCode,
        'letter_date' => now()->toDateString(),
        'status' => 'draft',
        'confidentiality' => 'biasa',
        'urgency' => 'biasa',
        'content_body' => $content,
    ]);

    /** @var LetterPdfService $service */
    $service = app(LetterPdfService::class);
    $html = $service->renderHtml($letter);

    expect($html)->toBeString()
        ->toContain('KERTAS PADALARANG')
        ->toContain('Uji Template '.$typeCode);
})->with([
    ['A', ['body' => '<p>Isi surat dinas resmi keluar.</p>', 'signer_title' => 'Direktur Utama', 'signer_name' => 'Budi Santoso']],
    ['ND', ['body' => '<p>Isi nota dinas internal.</p>', 'signer_title' => 'Kepala Divisi IT', 'signer_name' => 'Ahmad Fauzi']],
    ['ST', ['task_description' => 'Melaksanakan audit sistem informasi', 'employees' => [['name' => 'Siti Aminah', 'nik' => '12345', 'position' => 'Auditor', 'department' => 'SPI']]]],
    ['E', ['body' => '<p>Isi memorandum operasional.</p>', 'signer_title' => 'Direktur', 'signer_name' => 'Hendro']],
    ['KPTS', ['considerations' => ['Bahwa perlu penataan arsip'], 'legal_bases' => ['UU No 43 Tahun 2009'], 'decisions' => ['Menetapkan standar baru']]],
    ['PENG', ['body' => '<p>Pengumuman libur bersama hari raya.</p>', 'signer_title' => 'Sekretariat Perusahaan', 'signer_name' => 'Rina']],
    ['SPB', ['purpose' => 'Pengadaan Server Baru', 'items' => [['name' => 'Server Rack 2U', 'qty' => 1, 'unit' => 'Unit', 'notes' => 'Urgent']]]],
    ['BP', ['patient_name' => 'Andi Wijaya', 'treatment_type' => 'Rawat Inap', 'cost_claimed' => 1500000]],
    ['SPPD', ['destination_city' => 'Jakarta', 'purpose' => 'Rapat Koordinasi BUMN', 'transport_type' => 'Kereta Api', 'departure_date' => now()->toDateString(), 'return_date' => now()->addDays(2)->toDateString()]],
]);

test('preview html route returns 200 for authorized user', function () {
    $user = User::whereNotNull('employee_id')->firstOrFail();
    $employee = Employee::find($user->employee_id);
    $unit = OrgUnit::firstOrFail();
    $type = LetterType::where('code', 'ND')->firstOrFail();

    $letter = Letter::create([
        'id' => (string) Str::uuid(),
        'type_id' => $type->id,
        'origin_unit_id' => $unit->id,
        'creator_employee_id' => $employee->id,
        'destination_type' => 'internal_units',
        'subject' => 'Preview HTML Test',
        'letter_date' => now()->toDateString(),
        'status' => 'draft',
        'confidentiality' => 'biasa',
        'urgency' => 'biasa',
        'content_body' => ['body' => '<p>Konten nota dinas test preview.</p>'],
    ]);

    $response = $this->actingAs($user)->get(route('arsip.letters.preview-html', $letter->id));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    expect($response->getContent())->toContain('Preview HTML Test');
});
