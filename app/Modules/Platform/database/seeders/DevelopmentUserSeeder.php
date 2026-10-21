<?php

namespace App\Modules\Platform\database\seeders;

use App\Models\User;
use App\Modules\Platform\Models\Employee;
use App\Modules\Platform\Models\EmployeeAssignment;
use App\Modules\Platform\Models\OrgUnit;
use App\Modules\Platform\Models\Position;
use App\Modules\Platform\Models\Role;
use App\Modules\Platform\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DevelopmentUserSeeder extends Seeder
{
    /**
     * Seed development users and employee assignments for testing.
     */
    public function run(): void
    {
        $superAdminRole = Role::where('name', 'super-admin')->firstOrFail();
        $adminSdmRole = Role::where('name', 'admin-sdm')->firstOrFail();
        $pimpinanRole = Role::where('name', 'pimpinan')->firstOrFail();
        $pegawaiRole = Role::where('name', 'pegawai')->firstOrFail();

        $dirUtm = OrgUnit::where('code', 'DIR-UTM')->firstOrFail();
        $depSdm = OrgUnit::where('code', 'DEP-SDM')->firstOrFail();
        $secPk1 = OrgUnit::where('code', 'PRD-PK1')->firstOrFail();

        // 1. Positions
        $posDirut = Position::updateOrCreate(
            ['code' => 'POS-DIRUT'],
            [
                'id' => (string) Str::uuid(),
                'org_unit_id' => $dirUtm->id,
                'name' => 'Direktur Utama',
                'level' => 1,
                'is_active' => true,
            ]
        );

        $posAdminSdm = Position::updateOrCreate(
            ['code' => 'POS-ADM-SDM'],
            [
                'id' => (string) Str::uuid(),
                'org_unit_id' => $depSdm->id,
                'name' => 'Spesialis SDM',
                'level' => 3,
                'is_active' => true,
            ]
        );

        $posKasiePk1 = Position::updateOrCreate(
            ['code' => 'POS-KASIE-PK1'],
            [
                'id' => (string) Str::uuid(),
                'org_unit_id' => $secPk1->id,
                'name' => 'Kepala Seksi Produksi Kertas Unit I',
                'level' => 3,
                'is_active' => true,
            ]
        );

        $posOperatorPk1 = Position::updateOrCreate(
            ['code' => 'POS-OPR-PK1'],
            [
                'id' => (string) Str::uuid(),
                'org_unit_id' => $secPk1->id,
                'name' => 'Operator Produksi Kertas Unit I',
                'level' => 5,
                'is_active' => true,
            ]
        );

        // 2. Employees & Users
        $devUsers = [
            [
                'nip' => 'PTKP-001',
                'name' => 'Super Administrator (Dev)',
                'username' => 'superadmin',
                'email' => 'superadmin@kertas-padalarang.co.id',
                'position_id' => $posDirut->id,
                'org_unit_id' => $dirUtm->id,
                'role' => $superAdminRole,
                'scope_unit_id' => null,
            ],
            [
                'nip' => 'PTKP-010',
                'name' => 'Admin SDM (Dev)',
                'username' => 'adminsdm',
                'email' => 'adminsdm@kertas-padalarang.co.id',
                'position_id' => $posAdminSdm->id,
                'org_unit_id' => $depSdm->id,
                'role' => $adminSdmRole,
                'scope_unit_id' => $depSdm->id,
            ],
            [
                'nip' => 'PTKP-020',
                'name' => 'Kepala Seksi PK1 (Dev)',
                'username' => 'kasiepk1',
                'email' => 'kasiepk1@kertas-padalarang.co.id',
                'position_id' => $posKasiePk1->id,
                'org_unit_id' => $secPk1->id,
                'role' => $pimpinanRole,
                'scope_unit_id' => $secPk1->id,
            ],
            [
                'nip' => 'PTKP-021',
                'name' => 'Operator PK1 (Dev)',
                'username' => 'operatorpk1',
                'email' => 'operatorpk1@kertas-padalarang.co.id',
                'position_id' => $posOperatorPk1->id,
                'org_unit_id' => $secPk1->id,
                'role' => $pegawaiRole,
                'scope_unit_id' => $secPk1->id,
            ],
        ];

        foreach ($devUsers as $u) {
            $employee = Employee::updateOrCreate(
                ['nip' => $u['nip']],
                [
                    'id' => (string) Str::uuid(),
                    'name' => $u['name'],
                    'email' => $u['email'],
                    'phone' => '081234567890',
                    'nik' => '3217000000000001',
                    'birth_date' => '1990-01-01',
                    'gender' => 'male',
                    'is_active' => true,
                ]
            );

            // Assignment
            EmployeeAssignment::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'position_id' => $u['position_id'],
                ],
                [
                    'id' => (string) Str::uuid(),
                    'org_unit_id' => $u['org_unit_id'],
                    'started_at' => '2026-04-20',
                    'ended_at' => null,
                    'assignment_type' => 'permanent',
                    'notes' => 'Penugasan SK 2026',
                ]
            );

            // User account
            $user = User::updateOrCreate(
                ['username' => $u['username']],
                [
                    'id' => (string) Str::uuid(),
                    'employee_id' => $employee->id,
                    'name' => $u['name'],
                    'email' => $u['email'],
                    'password' => Hash::make('password'),
                    'must_change_pwd' => false,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            // User role assignment
            UserRole::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'role_id' => $u['role']->id,
                ],
                [
                    'id' => (string) Str::uuid(),
                    'org_unit_id' => $u['scope_unit_id'],
                    'valid_from' => '2026-04-20',
                    'valid_until' => null,
                ]
            );
        }
    }
}
