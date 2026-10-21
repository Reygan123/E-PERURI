<?php

namespace App\Modules\Platform\database\seeders;

use App\Modules\Platform\Models\Permission;
use App\Modules\Platform\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Seed initial roles, permissions, and role_permission mappings.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'platform.org_unit.view', 'label' => 'Lihat Unit Organisasi', 'module' => 'platform'],
            ['name' => 'platform.org_unit.manage', 'label' => 'Kelola Unit Organisasi', 'module' => 'platform'],
            ['name' => 'platform.position.view', 'label' => 'Lihat Jabatan', 'module' => 'platform'],
            ['name' => 'platform.position.manage', 'label' => 'Kelola Jabatan', 'module' => 'platform'],
            ['name' => 'platform.employee.view', 'label' => 'Lihat Master Pegawai', 'module' => 'platform'],
            ['name' => 'platform.employee.manage', 'label' => 'Kelola Master Pegawai', 'module' => 'platform'],
            ['name' => 'platform.user.manage', 'label' => 'Kelola Akun User & Peran', 'module' => 'platform'],
            ['name' => 'platform.audit.view', 'label' => 'Lihat Audit Trail', 'module' => 'platform'],
            ['name' => 'platform.numbering.manage', 'label' => 'Kelola Format Penomoran', 'module' => 'platform'],
            ['name' => 'platform.system_parameter.manage', 'label' => 'Kelola Parameter Sistem', 'module' => 'platform'],
        ];

        $permissionModels = [];
        foreach ($permissions as $p) {
            $permissionModels[$p['name']] = Permission::updateOrCreate(
                ['name' => $p['name']],
                [
                    'id' => (string) Str::uuid(),
                    'label' => $p['label'],
                    'module' => $p['module'],
                ]
            );
        }

        // Roles
        $roles = [
            'super-admin' => [
                'label' => 'Super Administrator',
                'permissions' => array_keys($permissionModels),
                'scope' => 'all',
            ],
            'admin-sdm' => [
                'label' => 'Administrator SDM',
                'permissions' => [
                    'platform.org_unit.view',
                    'platform.position.view',
                    'platform.employee.view',
                    'platform.employee.manage',
                    'platform.user.manage',
                ],
                'scope' => 'all',
            ],
            'pimpinan' => [
                'label' => 'Pimpinan Unit Kerja',
                'permissions' => [
                    'platform.org_unit.view',
                    'platform.position.view',
                    'platform.employee.view',
                ],
                'scope' => 'unit_tree',
            ],
            'pegawai' => [
                'label' => 'Pegawai',
                'permissions' => [
                    'platform.org_unit.view',
                ],
                'scope' => 'own',
            ],
        ];

        foreach ($roles as $name => $data) {
            $role = Role::updateOrCreate(
                ['name' => $name],
                [
                    'id' => (string) Str::uuid(),
                    'label' => $data['label'],
                ]
            );

            // Sync permissions with scope
            $syncData = [];
            foreach ($data['permissions'] as $permName) {
                $permission = $permissionModels[$permName];
                $syncData[$permission->id] = ['scope' => $data['scope']];
            }
            $role->permissions()->sync($syncData);
        }
    }
}
