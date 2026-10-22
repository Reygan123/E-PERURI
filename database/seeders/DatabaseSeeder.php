<?php

namespace Database\Seeders;

use App\Modules\Arsip\Database\Seeders\LetterTypeSeeder;
use App\Modules\Platform\Database\Seeders\DevelopmentUserSeeder;
use App\Modules\Platform\Database\Seeders\NumberingFormatSeeder;
use App\Modules\Platform\Database\Seeders\OrgUnitSeeder;
use App\Modules\Platform\Database\Seeders\RoleAndPermissionSeeder;
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
            NumberingFormatSeeder::class,
            LetterTypeSeeder::class,
        ]);
    }
}
