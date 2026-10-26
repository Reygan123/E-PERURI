<?php

use App\Models\User;
use App\Modules\Arsip\Database\Seeders\LetterTypeSeeder;
use App\Modules\Arsip\Models\Letter;
use App\Modules\Arsip\Models\LetterType;
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

test('letter policy protects documents from unauthorized cross-unit access (IDOR prevention)', function () {
    $users = User::whereNotNull('employee_id')->take(2)->get();
    expect($users->count())->toBeGreaterThanOrEqual(2);

    $creatorUser = $users[0];
    $otherUser = $users[1];

    $type = LetterType::where('code', 'A')->firstOrFail();
    $creatorEmployee = Employee::findOrFail($creatorUser->employee_id);
    $creatorUnitId = $creatorEmployee->activeAssignment?->org_unit_id ?? OrgUnit::firstOrFail()->id;

    // Create a draft letter belonging to creatorUser
    $letter = Letter::create([
        'id' => (string) Str::uuid(),
        'type_id' => $type->id,
        'origin_unit_id' => $creatorUnitId,
        'creator_employee_id' => $creatorUser->employee_id,
        'destination_type' => 'external_text',
        'destination_text' => 'Pihak Ketiga Rahasia',
        'subject' => 'Dokumen Sangat Rahasia Direksi',
        'letter_date' => now()->toDateString(),
        'status' => 'draft',
        'confidentiality' => 'sangat_rahasia',
        'urgency' => 'segera',
        'is_double_upload_complete' => false,
    ]);

    // 1. Creator can access their own letter
    $creatorResponse = $this->actingAs($creatorUser)->get(route('arsip.surat-keluar.show', $letter->id));
    $creatorResponse->assertOk();

    // 2. Other user who has non-matching unit / not superadmin cannot access (403 Forbidden)
    // Make sure otherUser has normal scope
    $otherResponse = $this->actingAs($otherUser)->get(route('arsip.surat-keluar.show', $letter->id));

    // If otherUser has 'all' scope (like superadmin), test with a fresh non-privileged employee user
    if ($otherResponse->status() === 200) {
        $otherUnit = OrgUnit::where('id', '!=', $creatorUnitId)->firstOrFail();
        $otherEmployee = Employee::create([
            'id' => (string) Str::uuid(),
            'nip' => '9999999999',
            'nik' => '3201019999990001',
            'name' => 'Staf Luar Unit',
            'email' => 'stafluar@ptkp.co.id',
            'employment_status' => 'permanent',
            'join_date' => now()->toDateString(),
            'is_active' => true,
        ]);

        $nonPrivilegedUser = User::create([
            'id' => (string) Str::uuid(),
            'name' => 'Staf Luar',
            'username' => 'stafluar',
            'email' => 'stafluar@ptkp.co.id',
            'password' => bcrypt('Password123!'),
            'employee_id' => $otherEmployee->id,
            'is_active' => true,
            'must_change_pwd' => false,
        ]);
        $forbiddenResponse = $this->actingAs($nonPrivilegedUser)->get(route('arsip.surat-keluar.show', $letter->id));
        $forbiddenResponse->assertForbidden();
    } else {
        $otherResponse->assertForbidden();
    }
});
