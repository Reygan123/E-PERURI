<?php

use App\Models\User;
use App\Modules\Platform\Database\Seeders\DevelopmentUserSeeder;
use App\Modules\Platform\Database\Seeders\NumberingFormatSeeder;
use App\Modules\Platform\Database\Seeders\OrgUnitSeeder;
use App\Modules\Platform\Database\Seeders\RoleAndPermissionSeeder;
use App\Modules\Platform\Models\OrgUnit;
use App\Modules\Platform\Models\Position;
use App\Modules\Sdm\Database\Seeders\JobdeskSeeder;
use App\Modules\Sdm\Models\JobDescription;
use App\Modules\Sdm\Services\JobDescriptionService;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => OrgUnitSeeder::class]);
    $this->artisan('db:seed', ['--class' => RoleAndPermissionSeeder::class]);
    $this->artisan('db:seed', ['--class' => DevelopmentUserSeeder::class]);
    $this->artisan('db:seed', ['--class' => NumberingFormatSeeder::class]);
    $this->artisan('db:seed', ['--class' => JobdeskSeeder::class]);
});

test('unauthenticated user cannot access sdm jobdesk catalog', function () {
    $response = $this->get('/sdm/jobdesk');
    $response->assertRedirect('/login');
});

test('authenticated user can view jobdesk catalog and search items', function () {
    $user = User::where('email', 'superadmin@kertas-padalarang.co.id')->firstOrFail();

    $response = $this->actingAs($user)->get('/sdm/jobdesk');
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Sdm/Jobdesk/Index')
        ->has('jobs.data')
        ->has('orgUnits')
    );

    // Test filter search
    $searchResponse = $this->actingAs($user)->get('/sdm/jobdesk?search=PK1');
    $searchResponse->assertOk();
});

test('user can view detailed job description with 10 sections', function () {
    $user = User::where('email', 'superadmin@kertas-padalarang.co.id')->firstOrFail();
    $job = JobDescription::firstOrFail();

    $response = $this->actingAs($user)->get("/sdm/jobdesk/{$job->id}");
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Sdm/Jobdesk/Show')
        ->has('job.tasks')
        ->has('job.working_relations.internal')
        ->has('job.competencies')
    );
});

test('user can view printable a4 sheet', function () {
    $user = User::where('email', 'superadmin@kertas-padalarang.co.id')->firstOrFail();
    $job = JobDescription::firstOrFail();

    $response = $this->actingAs($user)->get("/sdm/jobdesk/{$job->id}/print");
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Sdm/Jobdesk/Print')
        ->has('job')
    );
});

test('user can create a new job description draft with dynamic sub-details', function () {
    $user = User::where('email', 'superadmin@kertas-padalarang.co.id')->firstOrFail();
    $position = Position::firstOrFail();
    $orgUnit = OrgUnit::firstOrFail();

    $payload = [
        'position_id' => $position->id,
        'org_unit_id' => $orgUnit->id,
        'job_group' => 'Operasional & Manufaktur Pabrik',
        'grade_recommendation' => 'Grade 8',
        'job_summary' => 'Bertanggung jawab penuh atas pengawasan proses pembentukan kertas pengaman anti-pemalsuan.',
        'tasks' => [
            [
                'activity_name' => 'Kalibrasi Silinder Cetak',
                'activity_details' => 'Melakukan inspeksi visual dan penyetelan celah rol silinder.',
            ],
            [
                'activity_name' => 'Pemeriksaan Bahan Kimia Aditif',
                'activity_details' => 'Memastikan takaran larutan sizing dan retention agent tepat.',
            ],
        ],
        'responsibilities' => [
            'Menjaga toleransi gramatur kertas sesuai pesanan Bank Indonesia.',
            'Memastikan nol kecelakaan kerja di lini pembentukan lembaran.',
        ],
        'authorities' => [
            'Menghentikan sementara mesin jika terdeteksi robekan kertas berulang.',
        ],
        'working_relations' => [
            'internal' => [
                [
                    'entity' => 'Laboratorium Pengujian Mutu (QC)',
                    'matter' => 'Validasi hasil uji ketahanan lipat dan watermark.',
                ],
            ],
            'external' => [
                [
                    'entity' => 'Auditor Sistem Manajemen Keamanan Bank Sentral',
                    'matter' => 'Pemeriksaan rekonsiliasi lembar rusak dan afalan berharga.',
                ],
            ],
        ],
        'competencies' => [
            [
                'sub_competency' => 'Operasi Mesin Fourdrinier',
                'proficiency_level' => 4,
                'key_indicator' => 'Mampu memimpin startup dan shutdown darurat lini mesin.',
            ],
        ],
        'specification' => [
            'education_level' => 'D3 / S1 Teknik Kimia / Mesin',
            'work_experience' => 'Minimal 3 tahun pada industri kertas sekuriti.',
            'certifications' => 'Sertifikasi Ahli K3 Umum',
            'trainings' => 'SOP Pengamanan Kertas Khusus PTKP',
        ],
    ];

    $response = $this->actingAs($user)->post('/sdm/jobdesk', $payload);
    $response->assertRedirect();

    $newJob = JobDescription::where('job_summary', $payload['job_summary'])->first();
    expect($newJob)->not->toBeNull();
    expect($newJob->status)->toBe('draft');
    expect($newJob->tasks()->count())->toBe(2);
    expect($newJob->governances()->count())->toBe(3); // 2 resp + 1 auth
    expect($newJob->workingRelations()->count())->toBe(2); // 1 internal + 1 external
    expect($newJob->competencies()->count())->toBe(1);
    expect($newJob->competencies()->first()->proficiency_level)->toBe(4);
});

test('approval workflow advances job from draft to review and approved with document numbering', function () {
    $service = app(JobDescriptionService::class);
    $user = User::where('email', 'superadmin@kertas-padalarang.co.id')->firstOrFail();
    $position = Position::firstOrFail();
    $orgUnit = OrgUnit::firstOrFail();

    // 1. Create draft
    $job = $service->storeJob([
        'position_id' => $position->id,
        'org_unit_id' => $orgUnit->id,
        'job_group' => 'Fungsional',
        'grade_recommendation' => 'Grade 11',
        'job_summary' => 'Uraian jabatan untuk pengujian alur persetujuan digital dan penomoran resmi.',
    ], $user);

    expect($job->status)->toBe('draft');
    expect($job->document_number)->toBeNull();

    // 2. Submit for approval
    $submittedJob = $service->submitForApproval($job, $user);
    expect($submittedJob->status)->toBe('in_review');

    // 3. Process approval by Kadep SDM
    $approvedJob = $service->processApproval(
        $submittedJob,
        'sdm_kadep',
        'approved',
        'Disetujui secara resmi untuk diberlakukan.',
        $user
    );

    expect($approvedJob->status)->toBe('approved');
    expect($approvedJob->document_number)->not->toBeNull();
    expect($approvedJob->document_number)->toContain('/PTKP/');
    expect($approvedJob->tte_hash)->not->toBeNull();
    expect(strlen($approvedJob->tte_hash))->toBe(64); // SHA-256
});
