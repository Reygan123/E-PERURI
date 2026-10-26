<?php

namespace App\Modules\Arsip\Database\Seeders;

use App\Modules\Arsip\Models\LetterType;
use Illuminate\Database\Seeder;

class LetterTypeSeeder extends Seeder
{
    /**
     * Run database seeds for 17 Letter Types in PTKP.
     */
    public function run(): void
    {
        $types = [
            // === 10 SURAT AKTIF ===
            [
                'code' => 'A',
                'name' => 'Surat Keluar',
                'format_code' => 'SURAT_KELUAR',
                'signature_type' => 'manual',
                'approval_type' => 'standard',
                'reset_period' => 'yearly',
                'requires_double_upload' => true,
                'status_group' => 'active',
            ],
            [
                'code' => 'B',
                'name' => 'Surat Masuk',
                'format_code' => 'SURAT_MASUK',
                'signature_type' => 'none',
                'approval_type' => 'none',
                'reset_period' => 'yearly',
                'requires_double_upload' => false,
                'status_group' => 'active',
            ],
            [
                'code' => 'E',
                'name' => 'Surat Memo Direksi',
                'format_code' => 'MEMO_DIREKSI',
                'signature_type' => 'digital',
                'approval_type' => 'memo_dir',
                'reset_period' => 'yearly',
                'requires_double_upload' => false,
                'status_group' => 'active',
            ],
            [
                'code' => 'KPTS',
                'name' => 'Surat Keputusan',
                'format_code' => 'SURAT_KEPUTUSAN',
                'signature_type' => 'manual',
                'approval_type' => 'sdm_kpts',
                'reset_period' => 'yearly',
                'requires_double_upload' => false,
                'status_group' => 'active',
            ],
            [
                'code' => 'PENG',
                'name' => 'Surat Pengumuman',
                'format_code' => 'SURAT_PENGUMUMAN',
                'signature_type' => 'digital',
                'approval_type' => 'standard',
                'reset_period' => 'yearly',
                'requires_double_upload' => false,
                'status_group' => 'active',
            ],
            [
                'code' => 'ST',
                'name' => 'Surat Tugas',
                'format_code' => 'SURAT_TUGAS',
                'signature_type' => 'digital',
                'approval_type' => 'standard',
                'reset_period' => 'yearly',
                'requires_double_upload' => false,
                'status_group' => 'active',
            ],
            [
                'code' => 'ND',
                'name' => 'Nota Dinas',
                'format_code' => 'NOTA_DINAS',
                'signature_type' => 'digital',
                'approval_type' => 'standard',
                'reset_period' => 'yearly',
                'requires_double_upload' => false,
                'status_group' => 'active',
            ],
            [
                'code' => 'SPB',
                'name' => 'Surat Permintaan Barang (Gudang)',
                'format_code' => 'SURAT_PERMINTAAN_BARANG',
                'signature_type' => 'digital',
                'approval_type' => 'spb',
                'reset_period' => 'yearly',
                'requires_double_upload' => false,
                'status_group' => 'active',
            ],
            [
                'code' => 'BP',
                'name' => 'Surat Biaya Pengobatan',
                'format_code' => 'BIAYA_PENGOBATAN',
                'signature_type' => 'digital',
                'approval_type' => 'bp',
                'reset_period' => 'yearly',
                'requires_double_upload' => false,
                'status_group' => 'active',
            ],
            [
                'code' => 'SPPD',
                'name' => 'Surat Perintah Dinas (SPPD)',
                'format_code' => 'SURAT_PERINTAH_DINAS',
                'signature_type' => 'hybrid',
                'approval_type' => 'sppd',
                'reset_period' => 'monthly', // SPPD diulang setiap bulan
                'requires_double_upload' => false,
                'status_group' => 'active',
            ],

            // === 7 SURAT HOLD (KOORDINASI SPPS) ===
            [
                'code' => 'K',
                'name' => 'Surat Perjanjian / Kontrak',
                'format_code' => 'SURAT_KONTRAK',
                'signature_type' => 'manual',
                'approval_type' => 'standard',
                'reset_period' => 'yearly',
                'requires_double_upload' => false,
                'status_group' => 'hold',
            ],
            [
                'code' => 'SK',
                'name' => 'Surat Kuasa',
                'format_code' => 'SURAT_KUASA',
                'signature_type' => 'manual',
                'approval_type' => 'standard',
                'reset_period' => 'yearly',
                'requires_double_upload' => false,
                'status_group' => 'hold',
            ],
            [
                'code' => 'KET',
                'name' => 'Surat Keterangan',
                'format_code' => 'SURAT_KETERANGAN',
                'signature_type' => 'digital',
                'approval_type' => 'standard',
                'reset_period' => 'yearly',
                'requires_double_upload' => false,
                'status_group' => 'hold',
            ],
            [
                'code' => 'PNYT',
                'name' => 'Surat Pernyataan',
                'format_code' => 'SURAT_PERNYATAAN',
                'signature_type' => 'digital',
                'approval_type' => 'standard',
                'reset_period' => 'yearly',
                'requires_double_upload' => false,
                'status_group' => 'hold',
            ],
            [
                'code' => 'BA',
                'name' => 'Berita Acara',
                'format_code' => 'BERITA_ACARA',
                'signature_type' => 'digital',
                'approval_type' => 'standard',
                'reset_period' => 'yearly',
                'requires_double_upload' => false,
                'status_group' => 'hold',
            ],
            [
                'code' => 'TAR',
                'name' => 'Surat Pengantar',
                'format_code' => 'SURAT_PENGANTAR',
                'signature_type' => 'digital',
                'approval_type' => 'standard',
                'reset_period' => 'yearly',
                'requires_double_upload' => false,
                'status_group' => 'hold',
            ],
            [
                'code' => 'IT',
                'name' => 'Surat Information & Technology',
                'format_code' => 'SURAT_IT',
                'signature_type' => 'digital',
                'approval_type' => 'standard',
                'reset_period' => 'yearly',
                'requires_double_upload' => false,
                'status_group' => 'hold',
            ],
        ];

        foreach ($types as $t) {
            LetterType::updateOrCreate(
                ['code' => $t['code']],
                [
                    'name' => $t['name'],
                    'numbering_format_code' => $t['format_code'],
                    'signature_type' => $t['signature_type'],
                    'approval_type' => $t['approval_type'],
                    'reset_period' => $t['reset_period'],
                    'requires_double_upload' => $t['requires_double_upload'],
                    'status_group' => $t['status_group'],
                    'is_active' => $t['status_group'] === 'active',
                ]
            );
        }
    }
}
