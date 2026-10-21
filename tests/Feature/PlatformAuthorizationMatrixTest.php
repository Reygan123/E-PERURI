<?php

use App\Models\User;
use App\Modules\Platform\Models\AuditLog;
use App\Modules\Platform\Models\Employee;
use App\Modules\Platform\Models\OrgUnit;
use App\Modules\Platform\Models\Position;

beforeEach(function () {
    $this->seed();
});

test('unauthenticated guests are redirected to login across all platform routes', function () {
    $routes = [
        route('platform.org-units.index'),
        route('platform.positions.index'),
        route('platform.employees.index'),
        route('platform.users.index'),
        route('platform.numbering.index'),
        route('platform.audit-logs.index'),
    ];

    foreach ($routes as $url) {
        $response = $this->get($url);
        $response->assertRedirect(route('login'));
    }
});

test('unauthorized users without appropriate permissions receive 403 forbidden', function () {
    $pegawai = User::where('username', 'operatorpk1')->firstOrFail();

    // 1. Audit logs viewer
    $this->actingAs($pegawai)
        ->get(route('platform.audit-logs.index'))
        ->assertForbidden();

    // 2. User & role management
    $this->actingAs($pegawai)
        ->get(route('platform.users.index'))
        ->assertForbidden();

    // 3. Creating org units
    $this->actingAs($pegawai)
        ->post(route('platform.org-units.store'), [
            'name' => 'Unit Ilegal',
            'code' => 'UNIT-ILEGAL',
            'type' => 'seksi',
            'level' => 4,
        ])
        ->assertForbidden();

    // 4. Deleting position
    $pos = Position::firstOrFail();
    $this->actingAs($pegawai)
        ->delete(route('platform.positions.destroy', $pos->id))
        ->assertForbidden();
});

test('unit scope boundary prevents unauthorized cross-unit modifications (IDOR prevention)', function () {
    $superadmin = User::where('username', 'superadmin')->firstOrFail();
    $operatorPk1 = User::where('username', 'operatorpk1')->firstOrFail();

    // Let's create an employee in HR department
    $hrUnit = OrgUnit::where('code', 'DEP-SDM')->firstOrFail();
    $posHr = Position::where('org_unit_id', $hrUnit->id)->firstOrFail();

    $hrEmployee = Employee::create([
        'name' => 'HR Specialist',
        'nip' => 'PTKP-HR-001',
        'email' => 'hr.specialist@ptkp.co.id',
        'nik' => '3201000000000002',
        'is_active' => true,
    ]);

    // Operator PK1 (production unit) attempts to update HR employee record -> forbidden
    $this->actingAs($operatorPk1)
        ->put(route('platform.employees.update', $hrEmployee->id), [
            'name' => 'Hacked Name',
            'nip' => 'PTKP-HR-001',
            'email' => 'hacked@ptkp.co.id',
        ])
        ->assertForbidden();

    // Superadmin (scope all) can update successfully
    $this->actingAs($superadmin)
        ->put(route('platform.employees.update', $hrEmployee->id), [
            'name' => 'HR Specialist Updated',
            'nip' => 'PTKP-HR-001',
            'email' => 'hr.specialist@ptkp.co.id',
        ])
        ->assertRedirect();

    expect($hrEmployee->fresh()->name)->toBe('HR Specialist Updated');
});

test('audit log records are immutable and cannot have route for deletion', function () {
    $superadmin = User::where('username', 'superadmin')->firstOrFail();

    // Trigger an audit entry via platform endpoint
    $this->actingAs($superadmin)->post(route('platform.org-units.store'), [
        'name' => 'Unit Audit Test',
        'code' => 'UNIT-AUD-1',
        'type' => 'seksi',
        'level' => 4,
        'is_active' => true,
    ]);

    $unit = OrgUnit::where('code', 'UNIT-AUD-1')->firstOrFail();
    $log = AuditLog::where('entity_id', $unit->id)->first();
    expect($log)->not->toBeNull();

    // There is no DELETE or PUT route for audit logs
    $this->actingAs($superadmin)
        ->delete("/platform/audit-logs/{$log->id}")
        ->assertNotFound();

    $this->actingAs($superadmin)
        ->put("/platform/audit-logs/{$log->id}", ['action' => 'TAMPERED'])
        ->assertNotFound();
});
