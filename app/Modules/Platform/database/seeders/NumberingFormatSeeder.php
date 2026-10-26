<?php

namespace App\Modules\Platform\Database\Seeders;

use App\Modules\Platform\Models\NumberingFormat;
use Illuminate\Database\Seeder;

class NumberingFormatSeeder extends Seeder
{
    /**
     * Run numbering format seeds for system-wide document types.
     */
    public function run(): void
    {
        $formats = [
            // === ARSIP: 10 AKTIF ===
            [
                'code' => 'SURAT_KELUAR',
                'module' => 'arsip',
                'template' => '{SEQ}/{UNIT}/PTKP/{BULAN}/{TAHUN}',
                'reset_period' => 'yearly',
                'prefix' => 'A',
            ],
            [
                'code' => 'SURAT_MASUK',
                'module' => 'arsip',
                'template' => '{SEQ}/{UNIT}/{BULAN}/{TAHUN}',
                'reset_period' => 'yearly',
                'prefix' => 'B',
            ],
            [
                'code' => 'MEMO_DIREKSI',
                'module' => 'arsip',
                'template' => '{SEQ}/DIR.1/PTKP/{BULAN}/{TAHUN}',
                'reset_period' => 'yearly',
                'prefix' => 'E',
            ],
            [
                'code' => 'SURAT_KEPUTUSAN',
                'module' => 'arsip',
                'template' => '{SEQ}/KPTS/PTKP/{BULAN}/{TAHUN}',
                'reset_period' => 'yearly',
                'prefix' => 'KPTS',
            ],
            [
                'code' => 'SURAT_PENGUMUMAN',
                'module' => 'arsip',
                'template' => '{SEQ}/PENG/PTKP/{BULAN}/{TAHUN}',
                'reset_period' => 'yearly',
                'prefix' => 'PENG',
            ],
            [
                'code' => 'SURAT_TUGAS',
                'module' => 'arsip',
                'template' => '{SEQ}/ST/PTKP/{BULAN}/{TAHUN}',
                'reset_period' => 'yearly',
                'prefix' => 'ST',
            ],
            [
                'code' => 'NOTA_DINAS',
                'module' => 'arsip',
                'template' => '{SEQ}/{UNIT}/{BULAN}/{TAHUN}',
                'reset_period' => 'yearly',
                'prefix' => 'ND',
            ],
            [
                'code' => 'SURAT_PERMINTAAN_BARANG',
                'module' => 'arsip',
                'template' => '{SEQ}/SPB/{UNIT}/{BULAN}/{TAHUN}',
                'reset_period' => 'yearly',
                'prefix' => 'SPB',
            ],
            [
                'code' => 'BIAYA_PENGOBATAN',
                'module' => 'arsip',
                'template' => '{SEQ}/{BULAN}/BP/PTKP/{TAHUN}',
                'reset_period' => 'yearly',
                'prefix' => 'BP',
            ],
            [
                'code' => 'SURAT_PERINTAH_DINAS',
                'module' => 'arsip',
                'template' => '{SEQ}/{BULAN}/SPD/PTKP/{TAHUN}',
                'reset_period' => 'monthly', // reset per bulan
                'prefix' => 'SPPD',
            ],

            // === ARSIP: 7 HOLD (KOORDINASI SPPS) ===
            [
                'code' => 'SURAT_KONTRAK',
                'module' => 'arsip',
                'template' => '{SEQ}/K/PTKP/{BULAN}/{TAHUN}',
                'reset_period' => 'yearly',
                'prefix' => 'K',
            ],
            [
                'code' => 'SURAT_KUASA',
                'module' => 'arsip',
                'template' => '{SEQ}/SK/PTKP/{BULAN}/{TAHUN}',
                'reset_period' => 'yearly',
                'prefix' => 'SK',
            ],
            [
                'code' => 'SURAT_KETERANGAN',
                'module' => 'arsip',
                'template' => '{SEQ}/KET/PTKP/{BULAN}/{TAHUN}',
                'reset_period' => 'yearly',
                'prefix' => 'KET',
            ],
            [
                'code' => 'SURAT_PERNYATAAN',
                'module' => 'arsip',
                'template' => '{SEQ}/PNYT/PTKP/{BULAN}/{TAHUN}',
                'reset_period' => 'yearly',
                'prefix' => 'PNYT',
            ],
            [
                'code' => 'BERITA_ACARA',
                'module' => 'arsip',
                'template' => '{SEQ}/BA/PTKP/{BULAN}/{TAHUN}',
                'reset_period' => 'yearly',
                'prefix' => 'BA',
            ],
            [
                'code' => 'SURAT_PENGANTAR',
                'module' => 'arsip',
                'template' => '{SEQ}/TAR/PTKP/{BULAN}/{TAHUN}',
                'reset_period' => 'yearly',
                'prefix' => 'TAR',
            ],
            [
                'code' => 'SURAT_IT',
                'module' => 'arsip',
                'template' => '{SEQ}/IT/{BULAN}/{TAHUN}',
                'reset_period' => 'yearly',
                'prefix' => 'IT',
            ],
        ];

        foreach ($formats as $f) {
            NumberingFormat::updateOrCreate(
                ['code' => $f['code']],
                [
                    'module' => $f['module'],
                    'template' => $f['template'],
                    'reset_period' => $f['reset_period'],
                    'prefix' => $f['prefix'],
                ]
            );
        }
    }
}
