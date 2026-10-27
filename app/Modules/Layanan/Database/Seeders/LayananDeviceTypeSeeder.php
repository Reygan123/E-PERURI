<?php

declare(strict_types=1);

namespace App\Modules\Layanan\Database\Seeders;

use App\Modules\Layanan\Models\DeviceType;
use Illuminate\Database\Seeder;

class LayananDeviceTypeSeeder extends Seeder
{
    /**
     * Run the device types seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'code' => 'HARDWARE',
                'name' => 'Komputer & Laptop',
                'description' => 'Unit PC Desktop, All-in-One, Laptop Operasional, dan Mini PC.',
                'is_active' => true,
            ],
            [
                'code' => 'PRINTER',
                'name' => 'Printer & Scanner',
                'description' => 'Printer Laser, Inkjet, Dot Matrix Nota, Barcode Label Printer, dan Flatbed Scanner.',
                'is_active' => true,
            ],
            [
                'code' => 'NETWORK',
                'name' => 'Jaringan & Komunikasi',
                'description' => 'Koneksi Wi-Fi, Kabel LAN, Switch Hub, Router Kantor, dan IP Phone.',
                'is_active' => true,
            ],
            [
                'code' => 'SOFTWARE',
                'name' => 'Perangkat Lunak & Aplikasi',
                'description' => 'Sistem Operasi, Aplikasi Perkantoran, Antivirus, Email Dinas, dan Sistem Internal.',
                'is_active' => true,
            ],
            [
                'code' => 'PERIPHERAL',
                'name' => 'Periferal & Daya (Catu Daya)',
                'description' => 'Unit UPS, Monitor Tambahan, Keyboard, Mouse, dan Barcode Scanner Fisik.',
                'is_active' => true,
            ],
            [
                'code' => 'OTHER',
                'name' => 'Peralatan IT Lainnya',
                'description' => 'Proyektor Ruang Rapat, CCTV Area Kerja, dan Perangkat Khusus Lainnya.',
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            DeviceType::updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }
}
