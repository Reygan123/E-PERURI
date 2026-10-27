<?php

declare(strict_types=1);

namespace App\Modules\Layanan\Database\Seeders;

use App\Models\User;
use App\Modules\Layanan\Models\DeviceType;
use App\Modules\Layanan\Models\WorkOrder;
use App\Modules\Layanan\Models\WorkOrderAction;
use App\Modules\Platform\Models\Employee;
use App\Modules\Platform\Models\NumberingFormat;
use App\Modules\Platform\Models\NumberingSequence;
use App\Modules\Platform\Models\OrgUnit;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LayananWorkOrderSeeder extends Seeder
{
    /**
     * Run the work order seeds with realistic PTKP IT scenarios.
     */
    public function run(): void
    {
        $superadminUser = User::where('email', 'superadmin@kertas-padalarang.co.id')->first();
        $fallbackUser = User::first();
        $user = $superadminUser ?? $fallbackUser;

        if (! $user) {
            return;
        }

        $employees = Employee::where('is_active', true)->get();
        $requester = $employees->first() ?? Employee::first();
        $technician = $employees->count() > 1 ? $employees->skip(1)->first() : $requester;

        $orgUnits = OrgUnit::where('is_active', true)->get();
        $unitA = $orgUnits->where('code', 'DIR-UTM')->first() ?? $orgUnits->first();
        $unitB = $orgUnits->where('code', '!=', 'DIR-UTM')->first() ?? $unitA;

        $typePrinter = DeviceType::where('code', 'PRINTER')->first() ?? DeviceType::first();
        $typeHardware = DeviceType::where('code', 'HARDWARE')->first() ?? DeviceType::first();
        $typeNetwork = DeviceType::where('code', 'NETWORK')->first() ?? DeviceType::first();

        if (! $requester || ! $unitA || ! $typePrinter || ! $typeHardware || ! $typeNetwork) {
            return;
        }

        // Tiket 1: Selesai (Printer HP LaserJet)
        $ticket1 = WorkOrder::updateOrCreate(
            ['ticket_number' => 'IT001'],
            [
                'reference_memo_number' => '014/MEMO-INT/SDM/X/2026',
                'requester_id' => $requester->id,
                'org_unit_id' => $unitA->id,
                'section_name' => 'Seksi Tata Usaha & Kearsipan',
                'device_type_id' => $typePrinter->id,
                'device_name' => 'Printer HP LaserJet Pro M404dn',
                'it_inventory_code' => 'IT-PRN-SDM-003',
                'issue_description' => 'Hasil cetakan bergaris hitam memanjang di sisi kanan kertas dan roller penarik kertas sering macet (paper jam).',
                'photo_file_id' => null,
                'status' => 'completed',
                'submitted_at' => Carbon::now()->subDays(3)->setTime(8, 30),
                'started_at' => Carbon::now()->subDays(3)->setTime(9, 15),
                'completed_at' => Carbon::now()->subDays(3)->setTime(13, 45),
                'technician_id' => $technician?->id,
                'examination_result' => 'repaired_internally',
                'estimated_duration' => '4 Jam',
                'technician_notes' => 'Pembersihan fuser unit, penggantian roller karet penarik kertas, dan penggantian toner cartridge. Unit telah diuji coba 20 lembar cetak dokumen sekuriti dan berfungsi normal.',
                'created_by' => $user->id,
            ]
        );

        WorkOrderAction::firstOrCreate(
            [
                'work_order_id' => $ticket1->id,
                'action_type' => 'finish_repair',
            ],
            [
                'technician_id' => $technician?->id,
                'notes' => 'Perbaikan selesai dan unit diserahterimakan kembali ke pemohon.',
                'created_at' => Carbon::now()->subDays(3)->setTime(13, 45),
            ]
        );

        // Tiket 2: Sedang Dikerjakan (PC Desktop Blue Screen)
        $ticket2 = WorkOrder::updateOrCreate(
            ['ticket_number' => 'IT002'],
            [
                'reference_memo_number' => '048/ND/PROD-K1/X/2026',
                'requester_id' => $requester->id,
                'org_unit_id' => $unitB->id,
                'section_name' => 'Seksi Produksi Kertas Unit I',
                'device_type_id' => $typeHardware->id,
                'device_name' => 'PC Desktop Lenovo ThinkCentre M70q',
                'it_inventory_code' => 'IT-PC-PROD-012',
                'issue_description' => 'Komputer di ruang kontrol mesin kertas mati mendadak dan muncul layar biru (blue screen memory management).',
                'photo_file_id' => null,
                'status' => 'in_progress',
                'submitted_at' => Carbon::now()->subDays(1)->setTime(10, 0),
                'started_at' => Carbon::now()->subDays(1)->setTime(11, 30),
                'completed_at' => null,
                'technician_id' => $technician?->id,
                'examination_result' => 'part_replacement',
                'estimated_duration' => '1 Hari Kerja',
                'technician_notes' => 'Pemeriksaan hardware mendeteksi modul RAM DDR4 8GB mengalami bad address. Sedang dilakukan penggantian RAM baru dan instalasi ulang driver grafis.',
                'created_by' => $user->id,
            ]
        );

        WorkOrderAction::firstOrCreate(
            [
                'work_order_id' => $ticket2->id,
                'action_type' => 'take_ticket',
            ],
            [
                'technician_id' => $technician?->id,
                'notes' => 'Tiket diambil oleh teknisi IT dan perangkat dibawa ke laboratorium servis.',
                'created_at' => Carbon::now()->subDays(1)->setTime(11, 30),
            ]
        );

        // Tiket 3: Menunggu Antrean (Wi-Fi Access Point)
        WorkOrder::updateOrCreate(
            ['ticket_number' => 'IT003'],
            [
                'reference_memo_number' => null,
                'requester_id' => $requester->id,
                'org_unit_id' => $unitA->id,
                'section_name' => 'Ruang Rapat Gedung Administrasi',
                'device_type_id' => $typeNetwork->id,
                'device_name' => 'Access Point Cisco Catalyst Ruang Rapat',
                'it_inventory_code' => 'IT-NET-AP-005',
                'issue_description' => 'Koneksi internet nirkabel (Wi-Fi) di ruang rapat utama sering putus-nyambung saat peserta rapat melebihi 15 orang.',
                'photo_file_id' => null,
                'status' => 'pending',
                'submitted_at' => Carbon::now()->subHours(2),
                'started_at' => null,
                'completed_at' => null,
                'technician_id' => null,
                'examination_result' => null,
                'estimated_duration' => null,
                'technician_notes' => null,
                'created_by' => $user->id,
            ]
        );

        // Advance sequence for WO_IT so new work orders don't collide with seeded IT001-IT003
        $format = NumberingFormat::where('code', 'WO_IT')->first();
        if ($format) {
            $currentYear = (string) now()->year;
            NumberingSequence::updateOrCreate(
                ['format_id' => $format->id, 'period_key' => $currentYear],
                ['last_sequence' => 3]
            );
        }
    }
}
