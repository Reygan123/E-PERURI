<?php

declare(strict_types=1);

namespace App\Modules\Platform\database\seeders;

use App\Modules\Platform\Models\OrgUnit;
use Illuminate\Database\Seeder;

class OrgUnitSeeder extends Seeder
{
    /**
     * Run the database seeds for all 37 PTKP organizational units (SK 009/KPTS/PTKP/IV/2026).
     */
    public function run(): void
    {
        // 1. Level 0 - Direktur Utama
        $dirUtm = OrgUnit::updateOrCreate(
            ['code' => 'DIR-UTM'],
            [
                'parent_id' => null,
                'name' => 'Direktorat Utama',
                'type' => 'directorate',
                'level' => 0,
                'is_active' => true,
            ]
        );

        // 2. Level 1 - Di bawah Direktorat Utama (Staff Units & Direktorat)
        $unitsLevel1 = [
            [
                'code' => 'SEK-PPS',
                'name' => 'Sekretariat Perusahaan dan Perencanaan Strategis',
                'type' => 'staff_unit',
                'level' => 1,
            ],
            [
                'code' => 'SPI',
                'name' => 'Satuan Pengawasan Internal',
                'type' => 'staff_unit',
                'level' => 1,
            ],
            [
                'code' => 'DIR-OPS',
                'name' => 'Direktorat Operasi',
                'type' => 'directorate',
                'level' => 1,
            ],
            [
                'code' => 'DIR-KEU',
                'name' => 'Direktorat Keuangan',
                'type' => 'directorate',
                'level' => 1,
            ],
        ];

        $level1Models = [];
        foreach ($unitsLevel1 as $u) {
            $level1Models[$u['code']] = OrgUnit::updateOrCreate(
                ['code' => $u['code']],
                [
                    'parent_id' => $dirUtm->id,
                    'name' => $u['name'],
                    'type' => $u['type'],
                    'level' => $u['level'],
                    'is_active' => true,
                ]
            );
        }

        // 3. Level 2 - Departemen di bawah DIR-OPS
        $depOps = [
            ['code' => 'DEP-PRD', 'name' => 'Departemen Produksi'],
            ['code' => 'DEP-TEK', 'name' => 'Departemen Teknik'],
            ['code' => 'DEP-JKK', 'name' => 'Departemen Jaminan Keandalan Dan K3LH'],
            ['code' => 'DEP-RND', 'name' => 'Riset Dan Pengembangan'],
        ];

        $level2Models = [];
        foreach ($depOps as $d) {
            $level2Models[$d['code']] = OrgUnit::updateOrCreate(
                ['code' => $d['code']],
                [
                    'parent_id' => $level1Models['DIR-OPS']->id,
                    'name' => $d['name'],
                    'type' => 'department',
                    'level' => 2,
                    'is_active' => true,
                ]
            );
        }

        // 4. Level 2 - Departemen di bawah DIR-KEU (termasuk Seksi Tata Kelola langsung di bawah DIR-KEU per SK)
        $depKeu = [
            ['code' => 'DEP-PFA', 'name' => 'Departemen Pengadaan Dan Fasilitas Umum', 'type' => 'department', 'level' => 2],
            ['code' => 'DEP-SDM', 'name' => 'Departemen Sumber Daya Manusia', 'type' => 'department', 'level' => 2],
            ['code' => 'DEP-KEU', 'name' => 'Departemen Keuangan', 'type' => 'department', 'level' => 2],
            ['code' => 'DEP-AKT', 'name' => 'Departemen Akuntansi', 'type' => 'department', 'level' => 2],
            ['code' => 'DEP-PSR', 'name' => 'Departemen Pemasaran', 'type' => 'department', 'level' => 2],
            ['code' => 'SEK-TKR', 'name' => 'Seksi Tata Kelola, Pengelolaan Risiko, Dan Sistem Manajemen Terpadu', 'type' => 'section', 'level' => 2],
        ];

        foreach ($depKeu as $d) {
            $level2Models[$d['code']] = OrgUnit::updateOrCreate(
                ['code' => $d['code']],
                [
                    'parent_id' => $level1Models['DIR-KEU']->id,
                    'name' => $d['name'],
                    'type' => $d['type'],
                    'level' => $d['level'],
                    'is_active' => true,
                ]
            );
        }

        // 5. Level 3 - Seksi-seksi di bawah Departemen
        $sections = [
            // Bawah DEP-PRD
            ['parent' => 'DEP-PRD', 'code' => 'PRD-PCP', 'name' => 'Seksi Perencanaan Dan Pengendalian Persediaan Produksi'],
            ['parent' => 'DEP-PRD', 'code' => 'PRD-PK1', 'name' => 'Seksi Produksi Kertas Unit I'],
            ['parent' => 'DEP-PRD', 'code' => 'PRD-PK2', 'name' => 'Seksi Produksi Kertas Unit II'],
            ['parent' => 'DEP-PRD', 'code' => 'PRD-FIN', 'name' => 'Seksi Finishing Produksi'],
            ['parent' => 'DEP-PRD', 'code' => 'PRD-BLR', 'name' => 'Seksi Boiler'],

            // Bawah DEP-TEK
            ['parent' => 'DEP-TEK', 'code' => 'TEK-BGL', 'name' => 'Seksi Bengkel'],
            ['parent' => 'DEP-TEK', 'code' => 'TEK-PKP', 'name' => 'Seksi Perencanaan Dan Pengendalian Pemeliharaan'],
            ['parent' => 'DEP-TEK', 'code' => 'TEK-PMM', 'name' => 'Seksi Pemeliharaan Mekanik'],
            ['parent' => 'DEP-TEK', 'code' => 'TEK-PML', 'name' => 'Seksi Pemeliharaan Listrik, Instrumentasi Dan Panel DC'],

            // Bawah DEP-JKK
            ['parent' => 'DEP-JKK', 'code' => 'JKK-PKU', 'name' => 'Seksi Pengendalian Kualitas'],
            ['parent' => 'DEP-JKK', 'code' => 'JKK-K3L', 'name' => 'Seksi K3 Dan Lingkungan Hidup'],

            // Bawah DEP-PFA
            ['parent' => 'DEP-PFA', 'code' => 'PFA-PGD', 'name' => 'Seksi Pengadaan'],
            ['parent' => 'DEP-PFA', 'code' => 'PFA-PFU', 'name' => 'Seksi Pengelolaan Fasilitas Umum'],
            ['parent' => 'DEP-PFA', 'code' => 'PFA-GDG', 'name' => 'Seksi Gudang'],

            // Bawah DEP-SDM
            ['parent' => 'DEP-SDM', 'code' => 'SDM-PTI', 'name' => 'Seksi Pengembangan SDM Dan Teknologi Informasi'],
            ['parent' => 'DEP-SDM', 'code' => 'SDM-OPR', 'name' => 'Seksi SDM Operasional'],
            ['parent' => 'DEP-SDM', 'code' => 'SDM-PGM', 'name' => 'Seksi Pengamanan'],

            // Bawah DEP-KEU
            ['parent' => 'DEP-KEU', 'code' => 'KEU-AGR', 'name' => 'Seksi Anggaran'],
            ['parent' => 'DEP-KEU', 'code' => 'KEU-DAT', 'name' => 'Seksi Pengelolaan Dana, Aset, Dan TJSL'],

            // Bawah DEP-AKT
            ['parent' => 'DEP-AKT', 'code' => 'AKT-UPJ', 'name' => 'Seksi Akuntansi Umum Dan Perpajakan'],
            ['parent' => 'DEP-AKT', 'code' => 'AKT-BPS', 'name' => 'Seksi Akuntansi Biaya Dan Pengelolaan Persediaan'],

            // Bawah DEP-PSR
            ['parent' => 'DEP-PSR', 'code' => 'PSR-PJL', 'name' => 'Seksi Pemasaran Dan Penjualan'],
            ['parent' => 'DEP-PSR', 'code' => 'PSR-PPG', 'name' => 'Seksi Pengelolaan Penjualan Dan Pengiriman'],
            ['parent' => 'DEP-PSR', 'code' => 'PSR-KPL', 'name' => 'Seksi Kerjasama Produk Dan Layanan'],
        ];

        foreach ($sections as $s) {
            OrgUnit::updateOrCreate(
                ['code' => $s['code']],
                [
                    'parent_id' => $level2Models[$s['parent']]->id,
                    'name' => $s['name'],
                    'type' => 'section',
                    'level' => 3,
                    'is_active' => true,
                ]
            );
        }
    }
}
