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
use Illuminate\Support\Str;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => OrgUnitSeeder::class]);
    $this->artisan('db:seed', ['--class' => RoleAndPermissionSeeder::class]);
    $this->artisan('db:seed', ['--class' => DevelopmentUserSeeder::class]);
    $this->artisan('db:seed', ['--class' => NumberingFormatSeeder::class]);
    $this->artisan('db:seed', ['--class' => JobdeskSeeder::class]);
});

test('QA-CAT-01: unauthenticated requests to all jobdesk endpoints are securely redirected to login', function () {
    $dummyId = (string) Str::uuid();

    $this->get('/sdm/jobdesk')->assertRedirect('/login');
    $this->get('/sdm/jobdesk/create')->assertRedirect('/login');
    $this->post('/sdm/jobdesk', [])->assertRedirect('/login');
    $this->get("/sdm/jobdesk/{$dummyId}")->assertRedirect('/login');
    $this->get("/sdm/jobdesk/{$dummyId}/edit")->assertRedirect('/login');
    $this->put("/sdm/jobdesk/{$dummyId}", [])->assertRedirect('/login');
    $this->post("/sdm/jobdesk/{$dummyId}/submit")->assertRedirect('/login');
    $this->post("/sdm/jobdesk/{$dummyId}/approve", [])->assertRedirect('/login');
    $this->get("/sdm/jobdesk/{$dummyId}/print")->assertRedirect('/login');
});

test('QA-CAT-02: catalog filter interactions (status, unit, job_group, search keyword)', function () {
    $user = User::where('email', 'superadmin@kertas-padalarang.co.id')->firstOrFail();

    // 1. Filter by status 'approved'
    $resApproved = $this->actingAs($user)->get('/sdm/jobdesk?status=approved');
    $resApproved->assertOk();
    $resApproved->assertInertia(fn ($page) => $page
        ->component('Sdm/Jobdesk/Index')
        ->where('filters.status', 'approved')
    );

    // 2. Filter by status 'draft'
    $resDraft = $this->actingAs($user)->get('/sdm/jobdesk?status=draft');
    $resDraft->assertOk();
    $resDraft->assertInertia(fn ($page) => $page
        ->component('Sdm/Jobdesk/Index')
        ->where('filters.status', 'draft')
    );

    // 3. Search with non-existent keyword returns empty collection
    $resEmpty = $this->actingAs($user)->get('/sdm/jobdesk?search=KATA_KUNCI_TIDAK_MUNGKIN_ADA_9999');
    $resEmpty->assertOk();
    $resEmpty->assertInertia(fn ($page) => $page
        ->component('Sdm/Jobdesk/Index')
        ->has('jobs.data', 0)
    );

    // 4. Filter by existing Org Unit
    $unit = OrgUnit::firstOrFail();
    $resUnit = $this->actingAs($user)->get("/sdm/jobdesk?org_unit_id={$unit->id}");
    $resUnit->assertOk();
    $resUnit->assertInertia(fn ($page) => $page
        ->component('Sdm/Jobdesk/Index')
        ->where('filters.org_unit_id', $unit->id)
    );
});

test('QA-FORM-01: editor creation validation rejects invalid or missing payload', function () {
    $user = User::where('email', 'superadmin@kertas-padalarang.co.id')->firstOrFail();

    // Empty payload
    $res = $this->actingAs($user)->post('/sdm/jobdesk', []);
    $res->assertSessionHasErrors(['position_id', 'org_unit_id', 'job_summary']);

    // Invalid proficiency level (> 5)
    $position = Position::firstOrFail();
    $unit = OrgUnit::firstOrFail();
    $invalidCompPayload = [
        'position_id' => $position->id,
        'org_unit_id' => $unit->id,
        'job_summary' => 'Uraian jabatan ringkas pengujian validasi.',
        'job_group' => 'Operasional',
        'grade_recommendation' => 'Grade 7',
        'competencies' => [
            [
                'sub_competency' => 'Kompetensi Tidak Valid',
                'proficiency_level' => 99, // Harus 1-5
                'key_indicator' => 'Indikator melebihi batas.',
            ],
        ],
    ];

    $resComp = $this->actingAs($user)->post('/sdm/jobdesk', $invalidCompPayload);
    $resComp->assertSessionHasErrors(['competencies.0.proficiency_level']);
});

test('QA-FORM-02: complete editor create, update, and dynamic list synchronization', function () {
    $user = User::where('email', 'superadmin@kertas-padalarang.co.id')->firstOrFail();
    $position = Position::firstOrFail();
    $unit = OrgUnit::firstOrFail();

    // 1. Create Jobdesk with 3 tasks, 2 responsibilities, 2 authorities, 2 relations, 2 competencies
    $createPayload = [
        'position_id' => $position->id,
        'org_unit_id' => $unit->id,
        'job_group' => 'Struktural & Manajemen',
        'grade_recommendation' => 'Grade 14',
        'sotk_reference' => 'SK Direksi No. 009/KPTS/PTKP/IV/2026',
        'job_summary' => 'Memimpin dan mengkoordinasikan implementasi program pemeliharaan preventif mesin cetak pabrik Padalarang.',
        'operating_challenges' => 'Mencegah downtime mesin melebihi 2% per kuartal.',
        'environmental_conditions' => 'Area pabrik sekuriti dengan suhu bervariasi.',
        'tasks' => [
            ['activity_name' => 'Tugas A', 'activity_details' => 'Prosedur A1 dan A2.'],
            ['activity_name' => 'Tugas B', 'activity_details' => 'Prosedur B1 dan B2.'],
            ['activity_name' => 'Tugas C', 'activity_details' => 'Prosedur C1 dan C2.'],
        ],
        'responsibilities' => [
            'Akuntabilitas target output lembaran kertas sekuriti.',
            'Kepatuhan keselamatan kerja staf pelaksana.',
        ],
        'authorities' => [
            'Otorisasi jadwal perawatan mesin berkala.',
            'Pengajuan pengadaan suku cadang mesin impor.',
        ],
        'working_relations' => [
            'internal' => [
                ['entity' => 'Departemen Logistik & Pengadaan', 'matter' => 'Pengadaan bahan baku pulp kapas.'],
            ],
            'external' => [
                ['entity' => 'Peruri Pusat Karawang', 'matter' => 'Sinkronisasi standar spesifikasi kertas uang.'],
            ],
        ],
        'competencies' => [
            ['sub_competency' => 'Kepemimpinan Operasional Pabrik', 'proficiency_level' => 4, 'key_indicator' => 'Mengarahkan tim shift.'],
            ['sub_competency' => 'Manajemen Mutu ISO 9001', 'proficiency_level' => 3, 'key_indicator' => 'Audit internal rutin.'],
        ],
        'specification' => [
            'education_level' => 'S1 Teknik Mesin / Industri',
            'work_experience' => 'Minimal 5 tahun pengalaman di pabrik kertas.',
            'certifications' => 'Sertifikasi Ahli K3 & Manajemen Proyek',
            'trainings' => 'Tata Kelola Kertas Sekuriti PTKP',
        ],
    ];

    $resCreate = $this->actingAs($user)->post('/sdm/jobdesk', $createPayload);
    $resCreate->assertRedirect();
    $resCreate->assertSessionHas('success');

    $job = JobDescription::where('position_id', $position->id)->latest('created_at')->firstOrFail();
    expect($job->tasks()->count())->toBe(3);
    expect($job->governances()->count())->toBe(4);
    expect($job->workingRelations()->count())->toBe(2);
    expect($job->competencies()->count())->toBe(2);

    // 2. Access Edit page
    $resEditPage = $this->actingAs($user)->get("/sdm/jobdesk/{$job->id}/edit");
    $resEditPage->assertOk();
    $resEditPage->assertInertia(fn ($page) => $page
        ->component('Sdm/Jobdesk/Editor')
        ->where('mode', 'edit')
        ->has('job.tasks', 3)
    );

    // 3. Update Jobdesk: Reduce to 1 task, add new working relation
    $updatePayload = array_merge($createPayload, [
        'job_summary' => 'Deskripsi jabatan yang telah dimutakhirkan melalui proses review berkala.',
        'tasks' => [
            ['activity_name' => 'Tugas Tunggal Revisi', 'activity_details' => 'Detail baru hasil revisi.'],
        ],
        'working_relations' => [
            'internal' => [
                ['entity' => 'Unit Laboratorium QC', 'matter' => 'Pengujian gramatur harian.'],
                ['entity' => 'Unit Gudang Barang Jadi', 'matter' => 'Serah terima kertas siap kirim.'],
            ],
            'external' => [],
        ],
    ]);

    $resUpdate = $this->actingAs($user)->put("/sdm/jobdesk/{$job->id}", $updatePayload);
    $resUpdate->assertRedirect("/sdm/jobdesk/{$job->id}");
    $resUpdate->assertSessionHas('success');

    $freshJob = $job->fresh();
    expect($freshJob->job_summary)->toBe('Deskripsi jabatan yang telah dimutakhirkan melalui proses review berkala.');
    expect($freshJob->tasks()->count())->toBe(1);
    expect($freshJob->workingRelations()->count())->toBe(2);
    expect($freshJob->workingRelations()->first()->entity_name)->toBe('Unit Laboratorium QC');
});

test('QA-FLOW-01: approval workflow rejection and re-submission flow', function () {
    $user = User::where('email', 'superadmin@kertas-padalarang.co.id')->firstOrFail();
    $position = Position::firstOrFail();
    $unit = OrgUnit::firstOrFail();

    // 1. Create a draft
    $job = JobDescription::create([
        'position_id' => $position->id,
        'org_unit_id' => $unit->id,
        'job_group' => 'Fungsional',
        'grade_recommendation' => 'Grade 10',
        'job_summary' => 'Jobdesk pengujian alur penolakan dan revisi dokumen.',
        'status' => 'draft',
        'created_by' => $user->id,
    ]);

    // 2. Submit for review
    $resSubmit = $this->actingAs($user)->post("/sdm/jobdesk/{$job->id}/submit");
    $resSubmit->assertRedirect("/sdm/jobdesk/{$job->id}");
    expect($job->fresh()->status)->toBe('in_review');

    // 3. Checker requests revision (Rejection)
    $rejectPayload = [
        'stage' => 'checker',
        'decision' => 'rejected',
        'notes' => 'Tolong tambahkan butir tugas terkait pengujian air limbah pabrik.',
    ];

    $resReject = $this->actingAs($user)->post("/sdm/jobdesk/{$job->id}/approve", $rejectPayload);
    $resReject->assertRedirect("/sdm/jobdesk/{$job->id}");
    $resReject->assertSessionHas('success');

    // Status returns to draft
    expect($job->fresh()->status)->toBe('draft');

    // 4. Maker fixes and re-submits
    $resReSubmit = $this->actingAs($user)->post("/sdm/jobdesk/{$job->id}/submit");
    $resReSubmit->assertRedirect("/sdm/jobdesk/{$job->id}");
    expect($job->fresh()->status)->toBe('in_review');

    // 5. Approver approves (Finalize)
    $approvePayload = [
        'stage' => 'approver',
        'decision' => 'approved',
        'notes' => 'Revisi telah sesuai standar mutu ISO 14001, disahkan.',
    ];

    $resApprove = $this->actingAs($user)->post("/sdm/jobdesk/{$job->id}/approve", $approvePayload);
    $resApprove->assertRedirect("/sdm/jobdesk/{$job->id}");

    $finalJob = $job->fresh();
    expect($finalJob->status)->toBe('approved');
    expect($finalJob->document_number)->not->toBeNull();
    expect($finalJob->tte_hash)->not->toBeNull();
});

test('QA-SEC-01: IDOR and scope authorization boundary protection', function () {
    // Normal user from specific unit
    $regularUser = User::where('username', 'stafdirut')->first();
    if (! $regularUser) {
        $this->markTestSkipped('Regular user not found');
    }

    $adminUser = User::where('email', 'superadmin@kertas-padalarang.co.id')->firstOrFail();
    $position = Position::firstOrFail();
    $unit = OrgUnit::where('code', '!=', 'DIR-UTM')->firstOrFail();

    // Create a jobdesk draft owned by admin
    $adminJob = JobDescription::create([
        'position_id' => $position->id,
        'org_unit_id' => $unit->id,
        'job_group' => 'Operasional',
        'grade_recommendation' => 'Grade 8',
        'job_summary' => 'Dokumen rahasia unit lain.',
        'status' => 'draft',
        'created_by' => $adminUser->id,
    ]);

    // Regular user attempting to modify another unit's draft should receive 403 Forbidden
    $resForbidden = $this->actingAs($regularUser)->put("/sdm/jobdesk/{$adminJob->id}", [
        'job_summary' => 'Mencoba injeksi perubahan ilegal.',
    ]);
    $resForbidden->assertForbidden();

    // Regular user attempting to approve another unit's draft should receive 403 Forbidden
    $resApproveForbidden = $this->actingAs($regularUser)->post("/sdm/jobdesk/{$adminJob->id}/approve", [
        'stage' => 'checker',
        'decision' => 'approved',
    ]);
    $resApproveForbidden->assertForbidden();
});

test('QA-PRN-01: print sheet returns valid A4 printable data and layout', function () {
    $user = User::where('email', 'superadmin@kertas-padalarang.co.id')->firstOrFail();
    $job = JobDescription::firstOrFail();

    $resPrint = $this->actingAs($user)->get("/sdm/jobdesk/{$job->id}/print");
    $resPrint->assertOk();
    $resPrint->assertInertia(fn ($page) => $page
        ->component('Sdm/Jobdesk/Print')
        ->has('job.document_number')
        ->has('job.sotk_reference')
        ->has('job.tasks')
        ->has('job.working_relations.internal')
        ->has('job.competencies')
    );
});
