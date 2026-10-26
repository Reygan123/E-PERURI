<?php

declare(strict_types=1);

namespace App\Modules\Arsip\Database\Seeders;

use App\Modules\Arsip\Models\Disposition;
use App\Modules\Arsip\Models\Letter;
use App\Modules\Arsip\Models\LetterApproval;
use App\Modules\Arsip\Models\LetterDestination;
use App\Modules\Arsip\Models\LetterType;
use App\Modules\Platform\Models\Employee;
use App\Modules\Platform\Models\OrgUnit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArsipDummySeeder extends Seeder
{
    /**
     * Run the database seeders for comprehensive Arsip dummy data.
     */
    public function run(): void
    {
        // 1. Resolve Reference Entities
        $empSuperAdmin = Employee::where('nip', 'PTKP-001')->firstOrFail();
        $empAdminSdm = Employee::where('nip', 'PTKP-010')->firstOrFail();
        $empKasiePk1 = Employee::where('nip', 'PTKP-020')->firstOrFail();
        $empOperatorPk1 = Employee::where('nip', 'PTKP-021')->firstOrFail();

        $unitDirUtm = OrgUnit::where('code', 'DIR-UTM')->firstOrFail();
        $unitDepSdm = OrgUnit::where('code', 'DEP-SDM')->firstOrFail();
        $unitSecPk1 = OrgUnit::where('code', 'PRD-PK1')->firstOrFail();
        $unitGudang = OrgUnit::where('code', 'PFA-GDG')->first();
        $unitPemasaran = OrgUnit::where('code', 'PSR-PJL')->first();
        $unitTeknik = OrgUnit::where('code', 'DEP-TEK')->first();

        $typeA = LetterType::where('code', 'A')->firstOrFail();
        $typeB = LetterType::where('code', 'B')->firstOrFail();
        $typeNd = LetterType::where('code', 'ND')->firstOrFail();
        $typeSt = LetterType::where('code', 'ST')->firstOrFail();
        $typePeng = LetterType::where('code', 'PENG')->firstOrFail();
        $typeE = LetterType::where('code', 'E')->firstOrFail();
        $typeKpts = LetterType::where('code', 'KPTS')->firstOrFail();
        $typeSppd = LetterType::where('code', 'SPPD')->firstOrFail();
        $typeSpb = LetterType::where('code', 'SPB')->firstOrFail();
        $typeBp = LetterType::where('code', 'BP')->firstOrFail();

        // 2. Prepare Sample PDF File in Storage
        $samplePdfContent = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 595 842]/Parent 2 0 R/Resources<<>>>>endobj\nxref\n0 4\n0000000000 65535 f \n0000000009 00000 n \n0000000052 00000 n \n0000000101 00000 n \ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n178\n%%EOF\n";
        $pdfPath = 'platform_files/arsip/sample/dokumen_resmi_ptkp.pdf';
        Storage::disk('local')->put($pdfPath, $samplePdfContent);

        $createDummyFile = function (string $name, string $category, string $uploaderId) use ($pdfPath, $samplePdfContent): string {
            $fileId = (string) Str::uuid();
            DB::table('platform.files')->insert([
                'id' => $fileId,
                'uploader_id' => $uploaderId,
                'original_name' => $name,
                'stored_name' => $fileId.'.pdf',
                'disk' => 'local',
                'path' => $pdfPath,
                'mime_type' => 'application/pdf',
                'size_bytes' => strlen($samplePdfContent),
                'hash_sha256' => hash('sha256', $samplePdfContent),
                'entity_type' => $category,
                'entity_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $fileId;
        };

        $fileScanSm1 = $createDummyFile('Scan_Surat_BI_Permintaan_Sampel.pdf', 'surat_masuk', $empOperatorPk1->id);
        $fileScanSm2 = $createDummyFile('Scan_Surat_Indah_Kiat_Pulp.pdf', 'surat_masuk', $empOperatorPk1->id);
        $fileScanSm3 = $createDummyFile('Scan_Surat_Sucofindo_Audit_ISO.pdf', 'surat_masuk', $empAdminSdm->id);
        $fileScanSk1 = $createDummyFile('Scan_Basah_SK_001_BI.pdf', 'signed_letter', $empSuperAdmin->id);

        // =========================================================================
        // 3. SEED SURAT MASUK (KODE B)
        // =========================================================================

        // SM-001: Surat Masuk dari Bank Indonesia (Ada disposisi Dirut ke Kasie PK1)
        $sm1 = Letter::create([
            'id' => (string) Str::uuid(),
            'type_id' => $typeB->id,
            'document_number' => '28/DPU/BI/EXT/X/2026',
            'origin_unit_id' => $unitDirUtm->id,
            'creator_employee_id' => $empOperatorPk1->id,
            'destination_type' => 'external_text',
            'destination_text' => 'Bank Indonesia - Departemen Pengelolaan Uang',
            'subject' => 'Permintaan Pengujian Sampel Kertas Uang Pecahan Baru TE 2026',
            'letter_date' => '2026-10-14',
            'status' => 'published',
            'confidentiality' => 'rahasia',
            'urgency' => 'sangat_segera',
            'signed_file_id' => $fileScanSm1,
            'is_double_upload_complete' => true,
            'content_body' => [
                'received_date' => '2026-10-15',
                'sender_organization' => 'Bank Indonesia (Departemen Pengelolaan Uang)',
                'summary' => 'Permintaan resmi spesifikasi teknis dan pengiriman 50 rim lembar sampel kertas sekuriti berbahan serat abaka untuk persiapan uang pecahan baru TE 2026.',
            ],
            'created_at' => now()->subDays(8),
        ]);

        Disposition::create([
            'id' => (string) Str::uuid(),
            'letter_id' => $sm1->id,
            'sender_employee_id' => $empSuperAdmin->id,
            'recipient_employee_id' => $empKasiePk1->id,
            'recipient_unit_id' => $unitSecPk1->id,
            'instruction' => 'Tindak lanjuti segera',
            'notes' => 'Segera siapkan sampel kertas dari lini PK1 sesuai spesifikasi BI. Lakukan uji tarik dan ketahanan air sebelum dikirimkan paling lambat akhir pekan ini.',
            'deadline' => '2026-10-28',
            'status' => 'sent',
            'created_at' => now()->subDays(7),
        ]);

        // SM-002: Surat Masuk dari PT Indah Kiat Pulp & Paper (Belum disposisi)
        Letter::create([
            'id' => (string) Str::uuid(),
            'type_id' => $typeB->id,
            'document_number' => 'IKPP/LOG/1029/X/2026',
            'origin_unit_id' => $unitSecPk1->id,
            'creator_employee_id' => $empOperatorPk1->id,
            'destination_type' => 'external_text',
            'destination_text' => 'PT Indah Kiat Pulp & Paper Tbk',
            'subject' => 'Pemberitahuan Jadwal Pengapalan Bahan Baku Pulp Kayu Putih ECF',
            'letter_date' => '2026-10-18',
            'status' => 'published',
            'confidentiality' => 'biasa',
            'urgency' => 'biasa',
            'signed_file_id' => $fileScanSm2,
            'is_double_upload_complete' => true,
            'content_body' => [
                'received_date' => '2026-10-19',
                'sender_organization' => 'PT Indah Kiat Pulp & Paper Tbk',
                'summary' => 'Konfirmasi pengapalan 120 ton bahan baku pulp jenis bleached kraft pulp via Pelabuhan Tanjung Priok menuju Gudang Bahan Baku PTKP Padalarang.',
            ],
            'created_at' => now()->subDays(4),
        ]);

        // SM-003: Surat Masuk dari Sucofindo (Disposisi sedang diproses)
        $sm3 = Letter::create([
            'id' => (string) Str::uuid(),
            'type_id' => $typeB->id,
            'document_number' => 'SCFD/BDG/SURV/098/2026',
            'origin_unit_id' => $unitDepSdm->id,
            'creator_employee_id' => $empAdminSdm->id,
            'destination_type' => 'external_text',
            'destination_text' => 'PT Sucofindo (Persero) - Cabang Bandung',
            'subject' => 'Jadwal Rencana Audit Surveilans ISO 9001:2015 & ISO 14001:2015',
            'letter_date' => '2026-10-20',
            'status' => 'published',
            'confidentiality' => 'terbatas',
            'urgency' => 'segera',
            'signed_file_id' => $fileScanSm3,
            'is_double_upload_complete' => true,
            'content_body' => [
                'received_date' => '2026-10-21',
                'sender_organization' => 'PT Sucofindo (Persero)',
                'summary' => 'Pemberitahuan audit sistem manajemen mutu dan lingkungan semester II tahun 2026 yang akan dilaksanakan pada tanggal 10-12 November 2026.',
            ],
            'created_at' => now()->subDays(2),
        ]);

        Disposition::create([
            'id' => (string) Str::uuid(),
            'letter_id' => $sm3->id,
            'sender_employee_id' => $empSuperAdmin->id,
            'recipient_employee_id' => $empAdminSdm->id,
            'recipient_unit_id' => $unitDepSdm->id,
            'instruction' => 'Koordinasikan dengan unit terkait',
            'notes' => 'Siapkan seluruh dokumen audit internal dan pastikan unit produksi serta pengolahan limbah siap menghadapi audit surveilans.',
            'deadline' => '2026-11-05',
            'status' => 'in_progress',
            'created_at' => now()->subDays(1),
        ]);

        // =========================================================================
        // 4. SEED SURAT KELUAR (KODE A)
        // =========================================================================

        // SK-001: Surat Keluar Resmi (Signed, Scan Basah Lengkap)
        Letter::create([
            'id' => (string) Str::uuid(),
            'type_id' => $typeA->id,
            'document_number' => '001/DIR-UTM/A/X/2026',
            'origin_unit_id' => $unitDirUtm->id,
            'creator_employee_id' => $empSuperAdmin->id,
            'destination_type' => 'external_text',
            'destination_text' => 'Gubernur Bank Indonesia cq. Kepala Departemen Pengelolaan Uang',
            'subject' => 'Laporan Kesiapan Fasilitas Produksi Kertas Uang Emisi Baru',
            'letter_date' => '2026-10-10',
            'status' => 'signed',
            'confidentiality' => 'rahasia',
            'urgency' => 'segera',
            'signed_file_id' => $fileScanSk1,
            'is_double_upload_complete' => true,
            'content_body' => [
                'body' => '<p>Sehubungan dengan rencana penerbitan uang rupiah emisi baru, bersama ini kami sampaikan laporan kesiapan teknis mesin kertas Fourdrinier Unit I dan ketersediaan serat pengaman berteknologi tinggi di fasilitas produksi PT Kertas Padalarang.</p><p>Seluruh sarana pengujian laboratorium dan jaminan keamanan telah terkalibrasi sesuai standar Bank Indonesia.</p>',
                'signer_title' => 'Direktur Utama',
                'signer_name' => 'Super Administrator (Dev)',
            ],
            'created_at' => now()->subDays(12),
        ]);

        // SK-002: Surat Keluar Approved (Menunggu Upload Scan Basah Final -> Uji Blocking)
        $sk2 = Letter::create([
            'id' => (string) Str::uuid(),
            'type_id' => $typeA->id,
            'document_number' => '002/PRD-PK1/A/X/2026',
            'origin_unit_id' => $unitSecPk1->id,
            'creator_employee_id' => $empOperatorPk1->id,
            'destination_type' => 'external_text',
            'destination_text' => 'PT Pos Logistik Indonesia - KC Padalarang',
            'subject' => 'Permohonan Pengawalan dan Ekspedisi Khusus Pengiriman Kertas Sekuriti',
            'letter_date' => '2026-10-21',
            'status' => 'approved',
            'confidentiality' => 'terbatas',
            'urgency' => 'segera',
            'is_double_upload_complete' => false, // Sengaja false untuk uji double upload guard
            'content_body' => [
                'body' => '<p>Dengan hormat, kami mengajukan permohonan pengawalan dan armada tertutup untuk pengiriman 30 palet kertas berharga dari pabrik Padalarang ke Kawasan Percetakan Uang Karawang pada tanggal 26 Oktober 2026.</p>',
                'signer_title' => 'Kepala Seksi Produksi Kertas Unit I',
                'signer_name' => 'Kepala Seksi PK1 (Dev)',
            ],
            'created_at' => now()->subDays(2),
        ]);

        LetterApproval::create([
            'id' => (string) Str::uuid(),
            'letter_id' => $sk2->id,
            'approver_employee_id' => $empKasiePk1->id,
            'approver_role_title' => 'Kepala Seksi Produksi Kertas Unit I',
            'step_order' => 1,
            'status' => 'approved',
            'action_type' => 'approve',
            'notes' => 'Surat keluar telah disetujui. Silakan cetak fisik, mintakan tanda tangan basah pimpinan, dan unggah scan hasil tanda tangan basah final.',
            'decided_at' => now()->subDays(1),
            'created_at' => now()->subDays(2),
        ]);

        // SK-003: Surat Keluar Menunggu Approval Direktur Utama (Antrean Inbox superadmin)
        $sk3 = Letter::create([
            'id' => (string) Str::uuid(),
            'type_id' => $typeA->id,
            'document_number' => null,
            'origin_unit_id' => $unitDepSdm->id,
            'creator_employee_id' => $empAdminSdm->id,
            'destination_type' => 'external_text',
            'destination_text' => 'Kementerian Ketenagakerjaan Republik Indonesia',
            'subject' => 'Permohonan Kerjasama Program Magang Industri Manufaktur Kertas Pengaman',
            'letter_date' => '2026-10-22',
            'status' => 'in_review',
            'confidentiality' => 'biasa',
            'urgency' => 'segera',
            'is_double_upload_complete' => true,
            'content_body' => [
                'body' => '<p>Bersama ini kami mengajukan permohonan kerjasama program magang bersertifikat nasional bidang kimia pulp dan teknologi mesin kertas untuk 20 peserta didik vokasi tahun 2026/2027.</p>',
                'signer_title' => 'Direktur Utama',
                'signer_name' => 'Super Administrator (Dev)',
            ],
            'created_at' => now()->subHours(6),
        ]);

        LetterApproval::create([
            'id' => (string) Str::uuid(),
            'letter_id' => $sk3->id,
            'approver_employee_id' => $empSuperAdmin->id,
            'approver_role_title' => 'Direktur Utama',
            'step_order' => 1,
            'status' => 'pending', // Menunggu persetujuan superadmin!
            'action_type' => 'approve',
            'created_at' => now()->subHours(6),
        ]);

        // SK-004: Surat Keluar Draf (Dibuat oleh superadmin)
        Letter::create([
            'id' => (string) Str::uuid(),
            'type_id' => $typeA->id,
            'document_number' => null,
            'origin_unit_id' => $unitDirUtm->id,
            'creator_employee_id' => $empSuperAdmin->id,
            'destination_type' => 'external_text',
            'destination_text' => 'Kementerian Perindustrian Republik Indonesia',
            'subject' => 'Permohonan Dukungan Kebijakan Penggunaan Bahan Baku Ramah Lingkungan',
            'letter_date' => '2026-10-22',
            'status' => 'draft',
            'confidentiality' => 'biasa',
            'urgency' => 'biasa',
            'is_double_upload_complete' => true,
            'content_body' => [
                'body' => '<p>Draf pengajuan program dekarbonisasi dan transisi energi hijau di industri kertas pengaman nasional tahun 2026-2030.</p>',
                'signer_title' => 'Direktur Utama',
                'signer_name' => 'Super Administrator (Dev)',
            ],
            'created_at' => now()->subHours(10),
        ]);

        // =========================================================================
        // 5. SEED NASKAH DINAS (ND, ST, PENG, E, KPTS)
        // =========================================================================

        // ND-001: Nota Dinas Menunggu Approval Kasie PK1 (Antrean Inbox kasiepk1)
        $nd1 = Letter::create([
            'id' => (string) Str::uuid(),
            'type_id' => $typeNd->id,
            'document_number' => null,
            'origin_unit_id' => $unitSecPk1->id,
            'creator_employee_id' => $empOperatorPk1->id,
            'destination_type' => 'internal_units',
            'destination_text' => 'Seksi Gudang & Departemen Pengadaan',
            'subject' => 'Permohonan Penggantian Darurat Felt Mesin Cetak Kertas PK1',
            'letter_date' => '2026-10-22',
            'status' => 'in_review',
            'confidentiality' => 'biasa',
            'urgency' => 'segera',
            'is_double_upload_complete' => true,
            'content_body' => [
                'body' => '<p>Dilaporkan bahwa felt pada seksi pengepresan (press section) mesin PK1 mengalami keausan signifikan setelah beroperasi selama 2.400 jam. Dimohon persetujuan penggantian dengan stok felt cadangan di gudang guna menghindari risiko cacat gramatur kertas uang.</p>',
                'signer_title' => 'Operator Produksi Kertas Unit I',
                'signer_name' => 'Operator PK1 (Dev)',
            ],
            'created_at' => now()->subHours(14),
        ]);

        if ($unitGudang) {
            LetterDestination::create([
                'id' => (string) Str::uuid(),
                'letter_id' => $nd1->id,
                'org_unit_id' => $unitGudang->id,
            ]);
        }

        LetterApproval::create([
            'id' => (string) Str::uuid(),
            'letter_id' => $nd1->id,
            'approver_employee_id' => $empKasiePk1->id,
            'approver_role_title' => 'Kepala Seksi Produksi Kertas Unit I',
            'step_order' => 1,
            'status' => 'pending', // Menunggu persetujuan kasiepk1!
            'action_type' => 'approve',
            'created_at' => now()->subHours(14),
        ]);

        // ND-002: Nota Dinas Published (Disetujui Dirut dengan Digital Hash)
        $nd2 = Letter::create([
            'id' => (string) Str::uuid(),
            'type_id' => $typeNd->id,
            'document_number' => '001/ND/DEP-SDM/X/2026',
            'origin_unit_id' => $unitDepSdm->id,
            'creator_employee_id' => $empAdminSdm->id,
            'destination_type' => 'internal_units',
            'destination_text' => 'Direktorat Utama',
            'subject' => 'Usulan Pelaksanaan Sertifikasi Kompetensi K3 Operator Boiler Kelas 1',
            'letter_date' => '2026-10-12',
            'status' => 'published',
            'confidentiality' => 'biasa',
            'urgency' => 'biasa',
            'is_double_upload_complete' => true,
            'content_body' => [
                'body' => '<p>Mengajukan usulan keikutsertaan 4 (empat) orang operator boiler Seksi Utilitas dalam sertifikasi resmi Kemenaker RI guna memenuhi regulasi bejana uap dan keselamatan kerja bejana tekan pabrik.</p>',
                'signer_title' => 'Kepala Departemen SDM',
                'signer_name' => 'Admin SDM (Dev)',
            ],
            'created_at' => now()->subDays(10),
        ]);

        LetterApproval::create([
            'id' => (string) Str::uuid(),
            'letter_id' => $nd2->id,
            'approver_employee_id' => $empSuperAdmin->id,
            'approver_role_title' => 'Direktur Utama',
            'step_order' => 1,
            'status' => 'approved',
            'action_type' => 'approve',
            'notes' => 'Disetujui. Koordinasikan dengan bagian keuangan untuk alokasi anggaran pelatihan triwulan IV.',
            'signature_hash' => hash('sha256', 'APPROVED|ND-002|PTKP-001|20261012'),
            'decided_at' => now()->subDays(9),
            'created_at' => now()->subDays(10),
        ]);

        // ND-003: Nota Dinas Dikembalikan untuk Revisi (Status revision_requested)
        $nd3 = Letter::create([
            'id' => (string) Str::uuid(),
            'type_id' => $typeNd->id,
            'document_number' => null,
            'origin_unit_id' => $unitSecPk1->id,
            'creator_employee_id' => $empOperatorPk1->id,
            'destination_type' => 'internal_units',
            'destination_text' => 'Departemen Sumber Daya Manusia',
            'subject' => 'Permohonan Penambahan Jam Lembur Regu B Produksi Kertas',
            'letter_date' => '2026-10-18',
            'status' => 'revision_requested',
            'confidentiality' => 'biasa',
            'urgency' => 'biasa',
            'is_double_upload_complete' => true,
            'content_body' => [
                'body' => '<p>Permohonan penambahan jadwal lembur 8 jam untuk mengejar target penyelesaian pesanan kertas materai 10.000 rim.</p>',
                'signer_title' => 'Operator Produksi Kertas Unit I',
                'signer_name' => 'Operator PK1 (Dev)',
            ],
            'created_at' => now()->subDays(4),
        ]);

        LetterApproval::create([
            'id' => (string) Str::uuid(),
            'letter_id' => $nd3->id,
            'approver_employee_id' => $empKasiePk1->id,
            'approver_role_title' => 'Kepala Seksi Produksi Kertas Unit I',
            'step_order' => 1,
            'status' => 'revision_requested',
            'action_type' => 'revise',
            'notes' => 'Mohon lampirkan rincian nama anggota regu B dan estimasi pencapaian tonase harian sebelum diajukan kembali ke SDM.',
            'decided_at' => now()->subDays(3),
            'created_at' => now()->subDays(4),
        ]);

        // ST-001: Surat Tugas Published
        Letter::create([
            'id' => (string) Str::uuid(),
            'type_id' => $typeSt->id,
            'document_number' => '001/ST/DIR-UTM/X/2026',
            'origin_unit_id' => $unitDirUtm->id,
            'creator_employee_id' => $empSuperAdmin->id,
            'destination_type' => 'internal_units',
            'destination_text' => 'Kawasan Percetakan Uang Karawang',
            'subject' => 'Penugasan Tim Teknis Kalibrasi Mesin Cetak Kertas Sekuriti',
            'letter_date' => '2026-10-16',
            'status' => 'published',
            'confidentiality' => 'biasa',
            'urgency' => 'segera',
            'is_double_upload_complete' => true,
            'content_body' => [
                'task_description' => 'Melaksanakan uji kalibrasi sensor ketebalan optik dan verifikasi watermark kertas uang pada mesin percetakan di Karawang.',
                'employees' => [
                    ['name' => 'Kepala Seksi PK1 (Dev)', 'nik' => 'PTKP-020', 'position' => 'Kepala Seksi Produksi', 'department' => 'Seksi PK1'],
                    ['name' => 'Operator PK1 (Dev)', 'nik' => 'PTKP-021', 'position' => 'Operator Produksi Kertas', 'department' => 'Seksi PK1'],
                ],
                'start_date' => '2026-10-18',
                'end_date' => '2026-10-20',
                'destination_location' => 'Pabrik Percetakan Uang Karawang, Jawa Barat',
            ],
            'created_at' => now()->subDays(7),
        ]);

        // PENG-001: Pengumuman Published
        Letter::create([
            'id' => (string) Str::uuid(),
            'type_id' => $typePeng->id,
            'document_number' => '001/PENG/DIR-UTM/X/2026',
            'origin_unit_id' => $unitDirUtm->id,
            'creator_employee_id' => $empSuperAdmin->id,
            'destination_type' => 'all_units',
            'destination_text' => 'Seluruh Pegawai PT Kertas Padalarang',
            'subject' => 'Pelaksanaan Program Keselamatan Kerja dan Bulan K3 Nasional di Lingkungan Pabrik',
            'letter_date' => '2026-10-19',
            'status' => 'published',
            'confidentiality' => 'biasa',
            'urgency' => 'biasa',
            'is_double_upload_complete' => true,
            'content_body' => [
                'body' => '<p>Diberitahukan kepada seluruh karyawan dan karyawati PT Kertas Padalarang bahwa dalam rangka peringatan Bulan K3 Nasional, diwajibkan pemakaian APD lengkap (helm pelindung, kacamata kerja, dan ear-plug) di area berisiko tinggi.</p>',
                'signer_title' => 'Sekretariat Perusahaan',
                'signer_name' => 'Super Administrator (Dev)',
            ],
            'created_at' => now()->subDays(3),
        ]);

        // E-001: Surat Memo Direksi Published
        Letter::create([
            'id' => (string) Str::uuid(),
            'type_id' => $typeE->id,
            'document_number' => '001/E/DIR-UTM/X/2026',
            'origin_unit_id' => $unitDirUtm->id,
            'creator_employee_id' => $empSuperAdmin->id,
            'destination_type' => 'all_units',
            'destination_text' => 'Seluruh Kepala Departemen dan Kepala Seksi',
            'subject' => 'Instruksi Pengendalian Efisiensi Konsumsi Uap Boiler dan Listrik Pabrik',
            'letter_date' => '2026-10-05',
            'status' => 'published',
            'confidentiality' => 'terbatas',
            'urgency' => 'segera',
            'is_double_upload_complete' => true,
            'content_body' => [
                'body' => '<p>Dalam rangka optimasi biaya operasional manufaktur kertas, seluruh penanggung jawab unit produksi diinstruksikan mematikan sistem konveyor dan pompa sirkulasi air pada jam jeda pergantian shift.</p>',
                'signer_title' => 'Direktur Utama',
                'signer_name' => 'Super Administrator (Dev)',
            ],
            'created_at' => now()->subDays(17),
        ]);

        // KPTS-001: Surat Keputusan Direksi Approved
        Letter::create([
            'id' => (string) Str::uuid(),
            'type_id' => $typeKpts->id,
            'document_number' => '001/KPTS/DIR-UTM/X/2026',
            'origin_unit_id' => $unitDirUtm->id,
            'creator_employee_id' => $empSuperAdmin->id,
            'destination_type' => 'all_units',
            'destination_text' => 'Seluruh Unit Kerja PT Kertas Padalarang',
            'subject' => 'Penetapan Standar Operasional Prosedur Pengujian Fisika-Kimia Kertas Pengaman',
            'letter_date' => '2026-10-01',
            'status' => 'approved',
            'confidentiality' => 'terbatas',
            'urgency' => 'biasa',
            'is_double_upload_complete' => true,
            'content_body' => [
                'considerations' => [
                    'Bahwa untuk meningkatkan mutu hasil produksi kertas pengaman berstandar internasional, dipandang perlu memperbarui standar uji laboratorium.',
                    'Bahwa sehubungan dengan butir a, perlu ditetapkan Surat Keputusan Direksi.',
                ],
                'legal_bases' => [
                    'Undang-Undang Nomor 43 Tahun 2009 tentang Kearsipan.',
                    'Surat Keputusan Direksi PTKP Nomor SK 009/2026 tentang Struktur Organisasi.',
                ],
                'decisions' => [
                    'Menetapkan SOP baru nomor SOP-MUTU-024 sebagai acuan wajib kendali mutu lini produksi kertas.',
                    'Keputusan ini mulai berlaku sejak tanggal ditetapkan dengan ketentuan diadakan perbaikan bila dipandang perlu.',
                ],
                'signer_title' => 'Direktur Utama',
                'signer_name' => 'Super Administrator (Dev)',
            ],
            'created_at' => now()->subDays(22),
        ]);

        // =========================================================================
        // 6. SEED FORMULIR KHUSUS (SPPD, SPB, BP)
        // =========================================================================

        // SPPD-001: Perjalanan Dinas Published
        Letter::create([
            'id' => (string) Str::uuid(),
            'type_id' => $typeSppd->id,
            'document_number' => '001/SPPD/PRD-PK1/X/2026',
            'origin_unit_id' => $unitSecPk1->id,
            'creator_employee_id' => $empKasiePk1->id,
            'destination_type' => 'internal_units',
            'destination_text' => 'Kantor Pusat Peruri - Blok M Jakarta Selatan',
            'subject' => 'Perjalanan Dinas Rapat Koordinasi Rencana Produksi Kertas Paspor RI',
            'letter_date' => '2026-10-15',
            'status' => 'published',
            'confidentiality' => 'biasa',
            'urgency' => 'segera',
            'is_double_upload_complete' => true,
            'content_body' => [
                'destination_city' => 'Jakarta Selatan',
                'purpose' => 'Menghadiri rapat keselarasan spesifikasi teknis kertas paspor elektronik antara PT Kertas Padalarang dan Peruri Holding.',
                'transport_type' => 'Kereta Cepat Whoosh & Transportasi Lokal',
                'departure_date' => '2026-10-17',
                'return_date' => '2026-10-18',
                'estimated_cost' => 1850000,
            ],
            'created_at' => now()->subDays(8),
        ]);

        // SPB-001: Permintaan Barang Gudang Approved
        Letter::create([
            'id' => (string) Str::uuid(),
            'type_id' => $typeSpb->id,
            'document_number' => '001/SPB/PRD-PK1/X/2026',
            'origin_unit_id' => $unitSecPk1->id,
            'creator_employee_id' => $empOperatorPk1->id,
            'destination_type' => 'internal_units',
            'destination_text' => 'Seksi Gudang Logistik',
            'subject' => 'Permintaan Sparepart Bearing SKF dan Seal Pompa Vakum Mesin Kertas PK1',
            'letter_date' => '2026-10-20',
            'status' => 'approved',
            'confidentiality' => 'biasa',
            'urgency' => 'segera',
            'is_double_upload_complete' => true,
            'content_body' => [
                'purpose' => 'Penggantian preventif bearing motor utama pompa vakum nomor 2 lini PK1.',
                'items' => [
                    ['name' => 'Deep Groove Ball Bearing SKF 6312-2Z', 'qty' => 4, 'unit' => 'Pcs', 'notes' => 'Spesifikasi tahan panas'],
                    ['name' => 'Mechanical Seal Pompa Vakum 2.5 Inch', 'qty' => 2, 'unit' => 'Set', 'notes' => 'Original part'],
                    ['name' => 'Gemuk Pelumas High Temp Grease Shell Gadus', 'qty' => 5, 'unit' => 'Tube', 'notes' => 'Untuk pelumasan poros'],
                ],
            ],
            'created_at' => now()->subDays(2),
        ]);

        // BP-001: Biaya Pengobatan Pegawai In Review
        $bp1 = Letter::create([
            'id' => (string) Str::uuid(),
            'type_id' => $typeBp->id,
            'document_number' => null,
            'origin_unit_id' => $unitSecPk1->id,
            'creator_employee_id' => $empOperatorPk1->id,
            'destination_type' => 'internal_units',
            'destination_text' => 'Departemen Sumber Daya Manusia (Klinik Perusahaan)',
            'subject' => 'Permohonan Penggantian Biaya Pengobatan Rawat Jalan dan Kacamata Kerja',
            'letter_date' => '2026-10-22',
            'status' => 'in_review',
            'confidentiality' => 'terbatas',
            'urgency' => 'biasa',
            'is_double_upload_complete' => true,
            'content_body' => [
                'patient_name' => 'Operator PK1 (Dev)',
                'relationship' => 'Pegawai Sendiri',
                'treatment_type' => 'Pemeriksaan Mata & Penggantian Lensa Silinder Kerja',
                'clinic_hospital' => 'Klinik Mata Padalarang Medika',
                'cost_claimed' => 750000,
            ],
            'created_at' => now()->subHours(8),
        ]);

        LetterApproval::create([
            'id' => (string) Str::uuid(),
            'letter_id' => $bp1->id,
            'approver_employee_id' => $empAdminSdm->id,
            'approver_role_title' => 'Spesialis SDM & Kesejahteraan Pegawai',
            'step_order' => 1,
            'status' => 'pending', // Menunggu persetujuan adminsdm!
            'action_type' => 'approve',
            'created_at' => now()->subHours(8),
        ]);
    }
}
