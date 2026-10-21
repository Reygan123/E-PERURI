<?php

namespace Database\Seeders;

use App\Modules\Platform\database\seeders\DevelopmentUserSeeder;
use App\Modules\Platform\database\seeders\OrgUnitSeeder;
use App\Modules\Platform\database\seeders\RoleAndPermissionSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            OrgUnitSeeder::class,
            RoleAndPermissionSeeder::class,
            DevelopmentUserSeeder::class,
        ]);
    }
}
