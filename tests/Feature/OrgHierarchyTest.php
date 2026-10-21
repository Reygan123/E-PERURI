<?php

use App\Models\User;
use App\Modules\Platform\Models\Employee;
use App\Modules\Platform\Models\OrgUnit;
use App\Modules\Platform\Models\Role;

beforeEach(function () {
    $this->seed();
});

test('org units are seeded with valid hierarchy and 4 levels', function () {
    $dirUtm = OrgUnit::where('code', 'DIR-UTM')->first();
    expect($dirUtm)->not->toBeNull()
        ->and($dirUtm->level)->toBe(0)
        ->and($dirUtm->parent_id)->toBeNull();

    $dirOps = OrgUnit::where('code', 'DIR-OPS')->first();
    expect($dirOps)->not->toBeNull()
        ->and($dirOps->parent_id)->toBe($dirUtm->id)
        ->and($dirOps->level)->toBe(1);

    $depPrd = OrgUnit::where('code', 'DEP-PRD')->first();
    expect($depPrd)->not->toBeNull()
        ->and($depPrd->parent_id)->toBe($dirOps->id)
        ->and($depPrd->level)->toBe(2);

    $secPk1 = OrgUnit::where('code', 'PRD-PK1')->first();
    expect($secPk1)->not->toBeNull()
        ->and($secPk1->parent_id)->toBe($depPrd->id)
        ->and($secPk1->level)->toBe(3);
});

test('roles are seeded with proper permissions and scopes', function () {
    $superAdmin = Role::where('name', 'super-admin')->first();
    expect($superAdmin)->not->toBeNull()
        ->and($superAdmin->permissions->count())->toBeGreaterThanOrEqual(10);

    $pimpinan = Role::where('name', 'pimpinan')->first();
    expect($pimpinan)->not->toBeNull();

    $firstPerm = $pimpinan->permissions->first();
    expect($firstPerm)->not->toBeNull()
        ->and($firstPerm->pivot->scope)->toBe('unit_tree');
});

test('development users are created and linked to employee assignments', function () {
    $superUser = User::where('username', 'superadmin')->first();
    expect($superUser)->not->toBeNull()
        ->and($superUser->employee_id)->not->toBeNull();

    $employee = Employee::find($superUser->employee_id);
    expect($employee)->not->toBeNull()
        ->and($employee->nip)->toBe('PTKP-001')
        ->and($employee->activeAssignment)->not->toBeNull();
});
