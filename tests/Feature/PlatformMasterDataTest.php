<?php

use App\Models\User;
use App\Modules\Platform\Models\AuditLog;
use App\Modules\Platform\Models\Employee;
use App\Modules\Platform\Models\NumberingFormat;
use App\Modules\Platform\Models\OrgUnit;
use App\Modules\Platform\Models\Position;
use App\Modules\Platform\Models\Role;
use App\Modules\Platform\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Support\Str;

beforeEach(function () {
    // Seed essential roles and development users
    $this->artisan('db:seed', ['--class' => 'App\\Modules\\Platform\\database\\seeders\\RoleAndPermissionSeeder']);
    $this->artisan('db:seed', ['--class' => 'App\\Modules\\Platform\\database\\seeders\\OrgUnitSeeder']);
    $this->artisan('db:seed', ['--class' => 'App\\Modules\\Platform\\database\\seeders\\DevelopmentUserSeeder']);
});

test('superadmin can manage org units with audit logging', function () {
    $superadmin = User::where('username', 'superadmin')->firstOrFail();

    // 1. Index page
    $response = $this->actingAs($superadmin)->get(route('platform.org-units.index'));
    $response->assertOk();

    // 2. Store new unit
    $storeResponse = $this->actingAs($superadmin)->post(route('platform.org-units.store'), [
        'name' => 'Unit Uji Coba Produksi',
        'code' => 'UNIT-TEST-1',
        'type' => 'seksi',
        'level' => 4,
        'is_active' => true,
    ]);
    $storeResponse->assertRedirect();

    $unit = OrgUnit::where('code', 'UNIT-TEST-1')->first();
    expect($unit)->not->toBeNull();
    expect($unit->name)->toBe('Unit Uji Coba Produksi');

    // Verify audit log
    $log = AuditLog::where('entity_type', 'OrgUnit')
        ->where('entity_id', $unit->id)
        ->where('action', 'CREATE')
        ->first();
    expect($log)->not->toBeNull();

    // 3. Update unit
    $updateResponse = $this->actingAs($superadmin)->put(route('platform.org-units.update', $unit->id), [
        'name' => 'Unit Uji Coba Diperbarui',
        'code' => 'UNIT-TEST-1',
        'type' => 'seksi',
        'level' => 4,
        'is_active' => true,
    ]);
    $updateResponse->assertRedirect();
    expect($unit->fresh()->name)->toBe('Unit Uji Coba Diperbarui');

    // 4. Deactivate unit
    $deleteResponse = $this->actingAs($superadmin)->delete(route('platform.org-units.destroy', $unit->id));
    $deleteResponse->assertRedirect();
    expect($unit->fresh()->trashed())->toBeTrue();
});

test('superadmin can manage master positions', function () {
    $superadmin = User::where('username', 'superadmin')->firstOrFail();
    $orgUnit = OrgUnit::where('is_active', true)->firstOrFail();

    // 1. Index
    $this->actingAs($superadmin)->get(route('platform.positions.index'))->assertOk();

    // 2. Store
    $response = $this->actingAs($superadmin)->post(route('platform.positions.store'), [
        'org_unit_id' => $orgUnit->id,
        'name' => 'Kepala Unit Uji Coba',
        'code' => 'POS-TEST-1',
        'type' => 'structural',
        'level' => 3,
        'is_active' => true,
    ]);
    $response->assertRedirect();

    $pos = Position::where('code', 'POS-TEST-1')->first();
    expect($pos)->not->toBeNull();
    expect($pos->name)->toBe('Kepala Unit Uji Coba');

    // 3. Update
    $this->actingAs($superadmin)->put(route('platform.positions.update', $pos->id), [
        'org_unit_id' => $orgUnit->id,
        'name' => 'Kepala Unit Uji Coba Update',
        'code' => 'POS-TEST-1',
        'type' => 'structural',
        'level' => 3,
        'is_active' => true,
    ])->assertRedirect();
    expect($pos->fresh()->name)->toBe('Kepala Unit Uji Coba Update');
});

test('superadmin can register employee with user account and record mutations', function () {
    $superadmin = User::where('username', 'superadmin')->firstOrFail();
    $orgUnit = OrgUnit::where('is_active', true)->firstOrFail();
    $position = Position::where('org_unit_id', $orgUnit->id)->first() ?? Position::create([
        'org_unit_id' => $orgUnit->id,
        'name' => 'Staf Pengujian',
        'code' => 'STF-TEST-'.Str::random(4),
        'type' => 'functional',
        'level' => 4,
        'is_active' => true,
    ]);

    // 1. Register Employee
    $response = $this->actingAs($superadmin)->post(route('platform.employees.store'), [
        'name' => 'Budi Pratama S.T.',
        'nip' => 'PTKP-2026-999',
        'email' => 'budi.pratama@ptkp.co.id',
        'phone' => '081298765432',
        'gender' => 'male',
        'birth_date' => '1990-05-15',
        'nik' => '3201234567890001',
        'org_unit_id' => $orgUnit->id,
        'position_id' => $position->id,
        'assignment_type' => 'permanent',
        'username' => 'budi.pratama',
        'initial_password' => 'PTKP2026!secret',
    ]);
    $response->assertRedirect();

    // Verify Employee record
    $employee = Employee::where('nip', 'PTKP-2026-999')->first();
    expect($employee)->not->toBeNull();
    expect($employee->name)->toBe('Budi Pratama S.T.');
    expect($employee->nik)->toBe('3201234567890001'); // Encrypted cast

    // Verify linked User record
    $user = User::where('username', 'budi.pratama')->first();
    expect($user)->not->toBeNull();
    expect($user->employee_id)->toBe($employee->id);
    expect($user->must_change_pwd)->toBeTrue();

    // Verify initial active assignment
    $assignment = $employee->activeAssignment;
    expect($assignment)->not->toBeNull();
    expect($assignment->position_id)->toBe($position->id);
    expect($assignment->ended_at)->toBeNull();

    // 2. View Employee Show page
    $this->actingAs($superadmin)->get(route('platform.employees.show', $employee->id))->assertOk();

    // 3. Record new mutation (riwayat bertanggal)
    $newOrgUnit = OrgUnit::where('id', '!=', $orgUnit->id)->firstOrFail();
    $newPos = Position::create([
        'org_unit_id' => $newOrgUnit->id,
        'name' => 'Kepala Seksi Baru',
        'code' => 'KASIE-BARU-'.Str::random(4),
        'type' => 'structural',
        'level' => 4,
        'is_active' => true,
    ]);

    $mutateResponse = $this->actingAs($superadmin)->post(route('platform.employees.assign', $employee->id), [
        'org_unit_id' => $newOrgUnit->id,
        'position_id' => $newPos->id,
        'started_at' => Carbon::now()->addDays(2)->toDateString(),
        'assignment_type' => 'permanent',
        'notes' => 'Promosi Jabatan SK 123/2026',
    ]);
    $mutateResponse->assertRedirect();

    // Previous assignment should now have ended_at set
    expect($assignment->fresh()->ended_at)->not->toBeNull();
    // New active assignment should be on new position
    expect($employee->fresh()->activeAssignment->position_id)->toBe($newPos->id);
});

test('superadmin can manage user roles, scopes, and passwords', function () {
    $superadmin = User::where('username', 'superadmin')->firstOrFail();
    $targetUser = User::where('username', 'operatorpk1')->firstOrFail();
    $role = Role::where('name', 'pimpinan')->firstOrFail();
    $unit = OrgUnit::where('is_active', true)->firstOrFail();

    // 1. Assign new role
    $assignResponse = $this->actingAs($superadmin)->post(route('platform.users.roles.assign', $targetUser->id), [
        'role_id' => $role->id,
        'org_unit_id' => $unit->id,
        'valid_from' => Carbon::now()->toDateString(),
    ]);
    $assignResponse->assertRedirect();

    $userRole = UserRole::where('user_id', $targetUser->id)->where('role_id', $role->id)->first();
    expect($userRole)->not->toBeNull();
    expect($userRole->org_unit_id)->toBe($unit->id);

    // 2. Revoke role
    $revokeResponse = $this->actingAs($superadmin)->delete(route('platform.users.roles.revoke', $userRole->id));
    $revokeResponse->assertRedirect();
    expect(UserRole::find($userRole->id))->toBeNull();

    // 3. Toggle user active status
    $toggleResponse = $this->actingAs($superadmin)->post(route('platform.users.toggle-status', $targetUser->id));
    $toggleResponse->assertRedirect();
    expect($targetUser->fresh()->is_active)->toBeFalse();

    // 4. Reset password
    $resetResponse = $this->actingAs($superadmin)->post(route('platform.users.reset-password', $targetUser->id));
    $resetResponse->assertRedirect();
    expect($targetUser->fresh()->must_change_pwd)->toBeTrue();
});

test('numbering formats can be configured and live previewed', function () {
    $superadmin = User::where('username', 'superadmin')->firstOrFail();

    // 1. Index
    $this->actingAs($superadmin)->get(route('platform.numbering.index'))->assertOk();

    // 2. Store new format
    $storeResponse = $this->actingAs($superadmin)->post(route('platform.numbering.store'), [
        'code' => 'surat-tugas-test',
        'module' => 'platform',
        'template' => '{PREFIX}/ST/{SEQ}/{BULAN}/{TAHUN}',
        'reset_period' => 'yearly',
        'prefix' => 'PTKP',
    ]);
    $storeResponse->assertRedirect();

    $format = NumberingFormat::where('code', 'surat-tugas-test')->first();
    expect($format)->not->toBeNull();

    // 3. Preview endpoint
    $previewResponse = $this->actingAs($superadmin)->postJson(route('platform.numbering.preview'), [
        'template' => '{PREFIX}/ST/{SEQ}/{BULAN}/{TAHUN}',
        'prefix' => 'PTKP',
    ]);
    $previewResponse->assertOk();
    $year = date('Y');
    expect($previewResponse->json('preview'))->toContain('PTKP/ST/0001/');
    expect($previewResponse->json('preview'))->toContain("/{$year}");
});

test('audit logs page enforces access control and displays logs', function () {
    $superadmin = User::where('username', 'superadmin')->firstOrFail();
    $pegawai = User::where('username', 'operatorpk1')->firstOrFail();

    // Superadmin has access
    $this->actingAs($superadmin)->get(route('platform.audit-logs.index'))->assertOk();

    // Regular employee denied
    $this->actingAs($pegawai)->get(route('platform.audit-logs.index'))->assertForbidden();
});
