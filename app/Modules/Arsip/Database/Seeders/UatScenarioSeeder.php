<?php

declare(strict_types=1);

namespace App\Modules\Arsip\Database\Seeders;

use App\Models\User;
use App\Modules\Arsip\Models\Disposition;
use App\Modules\Arsip\Models\Letter;
use App\Modules\Arsip\Models\LetterApproval;
use App\Modules\Arsip\Models\LetterType;
use App\Modules\Platform\Models\OrgUnit;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UatScenarioSeeder extends Seeder
{
    /**
     * Seed predefined letters and scenarios for Guided UAT sessions.
     */
    public function run(): void
    {
        // 1. Resolve Reference Units & Employees
        $unitDirUtm = OrgUnit::where('code', 'DIR-UTM')->first();
        $unitDepSdm = OrgUnit::where('code', 'DEP-SDM')->first();
        $unitSecPk1 = OrgUnit::where('code', 'PRD-PK1')->first();
        $unitDepTek = OrgUnit::where('code', 'DEP-TEK')->first();
        $unitDepAkt = OrgUnit::where('code', 'DEP-AKT')->first() ?? OrgUnit::where('code', 'DIR-KEU')->first();
        $unitDirKeu = OrgUnit::where('code', 'DIR-KEU')->first();

        $userSuperAdmin = User::where('username', 'superadmin')->first();
        $userAdminSdm = User::where('username', 'admin_sdm')->first();
        $userKasiePk1 = User::where('username', 'kasiepk1')->first();
        $userOperatorPk1 = User::where('username', 'operatorpk1')->first();
        $userKadepPrd = User::where('username', 'kadepprd')->first();
        $userKadepTek = User::where('username', 'kadeptek')->first();
        $userKadepSdm = User::where('username', 'kadepsdm')->first();
        $userDirKeu = User::where('username', 'dirkeu')->first();

        // 2. Resolve Letter Types
        $typeA = LetterType::where('code', 'A')->first();
        $typeB = LetterType::where('code', 'B')->first();
        $typeNd = LetterType::where('code', 'ND')->first();
        $typeE = LetterType::where('code', 'E')->first();
        $typeSppd = LetterType::where('code', 'SPPD')->first();
        $typeBp = LetterType::where('code', 'BP')->first();

        if (! $typeA || ! $typeB || ! $typeNd || ! $userSuperAdmin) {
            return;
        }

        $today = Carbon::now()->toDateString();

        $defaultUnitId = $unitDirUtm ? $unitDirUtm->id : $typeB->id;
        $defaultCreatorId = (string) $userSuperAdmin->employee_id;
        $adminSdmId = $userAdminSdm ? (string) $userAdminSdm->employee_id : $defaultCreatorId;
        $operatorPk1Id = $userOperatorPk1 ? (string) $userOperatorPk1->employee_id : $defaultCreatorId;

        $secPk1UnitId = $unitSecPk1 ? $unitSecPk1->id : $defaultUnitId;
        $depTekUnitId = $unitDepTek ? $unitDepTek->id : $defaultUnitId;
        $depAktUnitId = $unitDepAkt ? $unitDepAkt->id : $defaultUnitId;
        $depSdmUnitId = $unitDepSdm ? $unitDepSdm->id : $defaultUnitId;

        // -------------------------------------------------------------
        // SCENARIO 1 (UAT-01): Registrasi Surat Masuk & Disposisi
        // -------------------------------------------------------------
        $letterUat1 = Letter::updateOrCreate(
            ['subject' => '[UAT-01] Surat Permohonan Kebutuhan Kertas Uang Pecahan Baru dari Bank Indonesia'],
            [
                'type_id' => $typeB->id,
                'document_number' => 'BI/DPU/2026/04/109',
                'origin_unit_id' => $defaultUnitId,
                'creator_employee_id' => $adminSdmId,
                'destination_type' => 'internal_units',
                'destination_text' => 'Direktur Utama PT Kertas Padalarang',
                'letter_date' => $today,
                'status' => 'published',
                'confidentiality' => 'rahasia',
                'urgency' => 'segera',
                'content_body' => [
                    'nomor_surat_pengirim' => 'BI/DPU/2026/04/109',
                    'instansi_pengirim' => 'Bank Indonesia - Departemen Pengelolaan Uang',
                    'tanggal_diterima' => $today,
                    'ringkasan' => 'Permohonan kesiapan kapasitas lini produksi kertas uang spesimen tahun anggaran 2026/2027.',
                ],
            ]
        );

        // Clean existing dispositions for this UAT letter to keep it reproducible
        Disposition::where('letter_id', $letterUat1->id)->delete();

        // Initial Disposition from Direktur Utama to Kadep Produksi
        if ($userSuperAdmin->employee_id && $userKadepPrd?->employee_id) {
            Disposition::create([
                'id' => (string) Str::uuid(),
                'letter_id' => $letterUat1->id,
                'sender_employee_id' => $userSuperAdmin->employee_id,
                'recipient_employee_id' => $userKadepPrd->employee_id,
                'recipient_unit_id' => $unitSecPk1?->id,
                'instruction' => 'Tindak lanjuti',
                'notes' => 'Tindak lanjuti persiapan kapasitas lini mesin PK-1 dan koordinasikan dengan Bagian Teknik.',
                'deadline' => Carbon::now()->addDays(7)->toDateString(),
                'status' => 'sent',
            ]);
        }

        // -------------------------------------------------------------
        // SCENARIO 2 (UAT-02): Surat Keluar Dinas & Penomoran Atomik
        // -------------------------------------------------------------
        $letterUat2 = Letter::updateOrCreate(
            ['subject' => '[UAT-02] Pengantar Pengiriman Sampel Kertas Paspor Cetakan Batch 14'],
            [
                'type_id' => $typeA->id,
                'document_number' => null, // Belum bernomor, akan terbit saat pengesahan final
                'origin_unit_id' => $secPk1UnitId,
                'creator_employee_id' => $operatorPk1Id,
                'destination_type' => 'external_text',
                'destination_text' => 'Kepala Divisi Percetakan Paspor Ditjen Imigrasi',
                'letter_date' => $today,
                'status' => 'in_review',
                'confidentiality' => 'terbatas',
                'urgency' => 'segera',
                'content_body' => [
                    'lampiran' => '1 (Satu) Berkas Hasil Uji Lab',
                    'destination_address' => 'Gedung Sentra Imigrasi, Jl. H.R. Rasuna Said Kav. 8-9, Jakarta Selatan',
                    'body_text' => 'Bersama surat ini kami sampaikan spesimen lembar kertas paspor hasil running mesin Paper Machine 1 (PK-1) untuk kebutuhan validasi uji cetak intaglio dan benang pengaman.',
                ],
            ]
        );

        LetterApproval::where('letter_id', $letterUat2->id)->delete();
        if ($userKasiePk1?->employee_id) {
            LetterApproval::create([
                'id' => (string) Str::uuid(),
                'letter_id' => $letterUat2->id,
                'step_order' => 1,
                'approver_employee_id' => $userKasiePk1->employee_id,
                'approver_role_title' => 'Kepala Seksi Paper Machine 1 (PK-1)',
                'action_type' => 'paraf',
                'status' => 'pending',
            ]);
        }
        if ($userKadepPrd?->employee_id) {
            LetterApproval::create([
                'id' => (string) Str::uuid(),
                'letter_id' => $letterUat2->id,
                'step_order' => 2,
                'approver_employee_id' => $userKadepPrd->employee_id,
                'approver_role_title' => 'Kepala Departemen Produksi',
                'action_type' => 'approve',
                'status' => 'waiting',
            ]);
        }

        // -------------------------------------------------------------
        // SCENARIO 3 (UAT-03): Siklus Revisi Naskah Dinas (Rule 52)
        // -------------------------------------------------------------
        $letterUat3 = Letter::updateOrCreate(
            ['subject' => '[UAT-03] Usulan Penggantian Sparepart Katup Uap Boiler Unit 2'],
            [
                'type_id' => $typeNd->id,
                'document_number' => null,
                'origin_unit_id' => $depTekUnitId,
                'creator_employee_id' => $operatorPk1Id,
                'destination_type' => 'internal_units',
                'destination_text' => 'Kepala Departemen Teknik',
                'letter_date' => $today,
                'status' => 'revision_requested', // Sesuai Skenario 3: Siap diedit kembali oleh perancang
                'confidentiality' => 'biasa',
                'urgency' => 'segera',
                'content_body' => [
                    'body_text' => 'Berdasarkan inspeksi rutin shift pagi, terindikasi adanya kebocoran minor pada packing katup tekanan uap 12 bar. Diusulkan pengadaan suku cadang pengganti darurat.',
                    'latar_belakang' => 'Keausan material gasket akibat beban termal operasional tinggi.',
                ],
            ]
        );

        LetterApproval::where('letter_id', $letterUat3->id)->delete();
        if ($userKadepTek?->employee_id) {
            LetterApproval::create([
                'id' => (string) Str::uuid(),
                'letter_id' => $letterUat3->id,
                'step_order' => 1,
                'approver_employee_id' => $userKadepTek->employee_id,
                'approver_role_title' => 'Kepala Departemen Teknik',
                'action_type' => 'approve',
                'status' => 'revision_requested',
                'notes' => 'Mohon sertakan estimasi biaya suku cadang dan jadwal pengerjaan agar tidak bentrok dengan jadwal shutdown mingguan.',
                'decided_at' => Carbon::now()->subHours(2),
            ]);
        }

        // -------------------------------------------------------------
        // SCENARIO 4 (UAT-04): Eskalasi POH Akuntansi ke Direktur Keuangan
        // -------------------------------------------------------------
        if ($typeE) {
            $letterUat4 = Letter::updateOrCreate(
                ['subject' => '[UAT-04] Rekonsiliasi Saldo Kas Operasional dan Giro Bank Mandiri Maret 2026'],
                [
                    'type_id' => $typeE->id,
                    'document_number' => null,
                    'origin_unit_id' => $depAktUnitId,
                    'creator_employee_id' => $adminSdmId,
                    'destination_type' => 'internal_units',
                    'destination_text' => 'Direktur Keuangan, SDM & Manajemen Risiko',
                    'letter_date' => $today,
                    'status' => 'in_review',
                    'confidentiality' => 'rahasia',
                    'urgency' => 'biasa',
                    'content_body' => [
                        'body_text' => 'Disampaikan laporan rekonsiliasi kas dan rekening koran operasional per 31 Maret 2026 dengan selisih kliring nol (nihil).',
                    ],
                ]
            );

            LetterApproval::where('letter_id', $letterUat4->id)->delete();
            if ($userDirKeu?->employee_id) {
                LetterApproval::create([
                    'id' => (string) Str::uuid(),
                    'letter_id' => $letterUat4->id,
                    'step_order' => 1,
                    'approver_employee_id' => $userDirKeu->employee_id,
                    'approver_role_title' => 'Direktur Keuangan (POH Kadep Akuntansi)',
                    'action_type' => 'approve',
                    'status' => 'pending',
                ]);
            }
        }

        // -------------------------------------------------------------
        // SCENARIO 5 (UAT-05): Formulir Khusus (SPPD & Biaya Pengobatan)
        // -------------------------------------------------------------
        if ($typeSppd) {
            $letterUat5A = Letter::updateOrCreate(
                ['subject' => '[UAT-05A] SPPD Perjalanan Dinas Pelatihan Sertifikasi Boiler Class 1'],
                [
                    'type_id' => $typeSppd->id,
                    'document_number' => '045/SPPD/SDM/IV/2026',
                    'origin_unit_id' => $depSdmUnitId,
                    'creator_employee_id' => $adminSdmId,
                    'destination_type' => 'internal_units',
                    'destination_text' => 'Balai Besar Pengembangan Keselamatan Kerja Bandung',
                    'letter_date' => $today,
                    'status' => 'in_review',
                    'confidentiality' => 'biasa',
                    'urgency' => 'biasa',
                    'content_body' => [
                        'maksud_perjalanan' => 'Mengikuti Uji Kompetensi Operator Pesawat Uap Kelas 1 Kemenaker RI',
                        'tempat_berangkat' => 'Pabrik PTKP Padalarang',
                        'tempat_tujuan' => 'Balai K3 Bandung',
                        'lama_perjalanan' => '3 Hari (27 s.d 29 April 2026)',
                        'kendaraan' => 'Kendaraan Operasional Perusahaan',
                        'pengikut' => '1. Ahmad Sutisna (Operator Boiler)',
                    ],
                ]
            );

            LetterApproval::where('letter_id', $letterUat5A->id)->delete();
            if ($userKadepSdm?->employee_id) {
                LetterApproval::create([
                    'id' => (string) Str::uuid(),
                    'letter_id' => $letterUat5A->id,
                    'step_order' => 1,
                    'approver_employee_id' => $userKadepSdm->employee_id,
                    'approver_role_title' => 'Kepala Departemen SDM',
                    'action_type' => 'approve',
                    'status' => 'pending',
                ]);
            }
        }

        if ($typeBp) {
            $letterUat5B = Letter::updateOrCreate(
                ['subject' => '[UAT-05B] Klaim Biaya Pengobatan Rawat Jalan Spesialis THT'],
                [
                    'type_id' => $typeBp->id,
                    'document_number' => '012/BP/SDM/IV/2026',
                    'origin_unit_id' => $depSdmUnitId,
                    'creator_employee_id' => $adminSdmId,
                    'destination_type' => 'internal_units',
                    'destination_text' => 'Bagian Kesejahteraan Karyawan Departemen SDM',
                    'letter_date' => $today,
                    'status' => 'in_review',
                    'confidentiality' => 'biasa',
                    'urgency' => 'biasa',
                    'content_body' => [
                        'nama_karyawan' => 'Ujang Suherman',
                        'departemen' => 'Produksi (PK-1)',
                        'nama_pasien' => 'Ujang Suherman (Karyawan)',
                        'hubungan' => 'Karyawan',
                        'rumah_sakit' => 'RS Dustira Cimahi',
                        'total_biaya' => 'Rp 450.000,-',
                        'diagnosa' => 'Gangguan Pendengaran Akibat Bising (NIHL) Ringan',
                    ],
                ]
            );

            LetterApproval::where('letter_id', $letterUat5B->id)->delete();
            if ($userKadepSdm?->employee_id) {
                LetterApproval::create([
                    'id' => (string) Str::uuid(),
                    'letter_id' => $letterUat5B->id,
                    'step_order' => 1,
                    'approver_employee_id' => $userKadepSdm->employee_id,
                    'approver_role_title' => 'Kepala Departemen SDM',
                    'action_type' => 'approve',
                    'status' => 'pending',
                ]);
            }
        }
    }
}
