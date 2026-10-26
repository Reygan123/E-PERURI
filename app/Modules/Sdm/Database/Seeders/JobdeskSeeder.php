<?php

declare(strict_types=1);

namespace App\Modules\Sdm\Database\Seeders;

use App\Models\User;
use App\Modules\Sdm\Models\JobApproval;
use App\Modules\Sdm\Models\JobCompetency;
use App\Modules\Sdm\Models\JobDescription;
use App\Modules\Sdm\Models\JobGovernance;
use App\Modules\Sdm\Models\JobSpecification;
use App\Modules\Sdm\Models\JobStandard;
use App\Modules\Sdm\Models\JobTask;
use App\Modules\Sdm\Models\JobWorkingRelation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JobdeskSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::where('username', 'superadmin')->first() ?? User::first();
        if (! $superadmin) {
            return;
        }

        // 1. Software Engineer / Fullstack Developer (Biro TI / Platform)
        $this->seedSoftwareEngineer($superadmin);

        // 2. Operator Mesin Pembentukan Kertas Sekuriti (Pabrik Kertas Uang PK1)
        $this->seedOperatorPk1($superadmin);

        // 3. Analis Pengujian Mutu & Laboratorium (Departemen QC & Lab)
        $this->seedQcAnalyst($superadmin);

        // 4. Kepala Seksi Personalia & Hubungan Industrial (Departemen SDM & Umum)
        $this->seedKasiPersonalia($superadmin);
    }

    protected function seedSoftwareEngineer(User $author): void
    {
        $unit = DB::table('platform.org_units')->where('code', 'SEK-PPS')->first()
            ?? DB::table('platform.org_units')->first();
        $position = DB::table('platform.positions')->where('name', 'like', '%Sistem%')->first()
            ?? DB::table('platform.positions')->first();

        $jobId = (string) Str::uuid();

        $job = JobDescription::create([
            'id' => $jobId,
            'position_id' => $position->id,
            'org_unit_id' => $unit->id,
            'document_number' => '042/JD/PTKP/TI/IV/2026',
            'revision_number' => 0,
            'sotk_reference' => 'SK 009/KPTS/PTKP/IV/2026',
            'job_group' => 'Teknologi Informasi & Digitalisasi',
            'grade_recommendation' => 'Grade 12 (Senior Professional)',
            'job_summary' => 'Merancang, membangun, menguji, dan memelihara aplikasi digital internal PT Kertas Padalarang (Modular Monolith, E-Arsip, Jobdesk, MES) dengan standar keamanan, integritas, dan ketersediaan tinggi.',
            'subordinate_notes' => "1. Junior Frontend Developer\n2. Junior Backend Developer",
            'operating_challenges' => "1. Menjaga ketersediaan sistem 24/7 untuk lini operasional pabrik kertas uang tanpa toleransi downtime.\n2. Integrasi antar-modul multi-schema PostgreSQL dengan kepatuhan batas modul ketat (Deptrac 0 layer violations).",
            'environmental_conditions' => 'Kantor pusat perusahaan dengan fasilitas kerja hybrid (WFO/WFH) dan ruang server berpendingin presisi.',
            'incidental_duties' => 'Melakukan riset teknologi terbaru untuk inovasi digital perusahaan serta pendampingan teknis bagi pengguna di pabrik.',
            'status' => 'approved',
            'tte_hash' => hash('sha256', "JOBDESK|{$jobId}|042/JD/PTKP/TI/IV/2026"),
            'tte_signed_at' => now(),
            'created_by' => $author->id,
        ]);

        // Tasks
        JobTask::create([
            'job_description_id' => $job->id,
            'activity_name' => 'Perancangan Arsitektur Perangkat Lunak',
            'activity_details' => "[1]. Menganalisis kebutuhan proses bisnis unit operasional pabrik dan manajemen.\n[2]. Merancang skema basis data PostgreSQL multi-schema dan kontrak layanan publik (Platform Contracts).",
            'sort_order' => 1,
        ]);
        JobTask::create([
            'job_description_id' => $job->id,
            'activity_name' => 'Pengembangan Kode Program & API',
            'activity_details' => "[1]. Menulis kode backend Laravel 11 dan frontend Vue 3 + Inertia yang bersih dan terstandarisasi PSR-12.\n[2]. Mengimplementasikan logika TTE BSrE, QR Code mandiri, dan notifikasi multi-kanal.",
            'sort_order' => 2,
        ]);
        JobTask::create([
            'job_description_id' => $job->id,
            'activity_name' => 'Penjaminan Kualitas & Pengujian Otomatis',
            'activity_details' => "[1]. Menulis automated test suite (Pest) untuk pengujian fungsional dan otorisasi IDOR.\n[2]. Memastikan analisis statis Larastan level 6/7 dan audit Deptrac bebas error.",
            'sort_order' => 3,
        ]);

        // Governances
        JobGovernance::create([
            'job_description_id' => $job->id,
            'type' => 'responsibility',
            'description' => 'Bertanggung jawab penuh atas keandalan, keamanan, dan ketersediaan sistem aplikasi korporat.',
            'sort_order' => 1,
        ]);
        JobGovernance::create([
            'job_description_id' => $job->id,
            'type' => 'responsibility',
            'description' => 'Menjaga kerahasiaan data master kepegawaian dan kunci enkripsi sertifikat TTE BSrE.',
            'sort_order' => 2,
        ]);
        JobGovernance::create([
            'job_description_id' => $job->id,
            'type' => 'authority',
            'description' => 'Memberikan persetujuan pull request dan code review bagi seluruh pengembang aplikasi.',
            'sort_order' => 1,
        ]);
        JobGovernance::create([
            'job_description_id' => $job->id,
            'type' => 'authority',
            'description' => 'Menghentikan sementara deployment jika ditemukan celah kerentanan keamanan kritis.',
            'sort_order' => 2,
        ]);

        // Working Relations (ala Telkom Univ)
        JobWorkingRelation::create([
            'job_description_id' => $job->id,
            'relation_type' => 'internal',
            'entity_name' => 'Seluruh Departemen & Seksi PTKP',
            'matter_description' => 'Pengumpulan kebutuhan fitur sistem, pelatihan pengguna, dan penanganan tiket keluhan teknis.',
            'sort_order' => 1,
        ]);
        JobWorkingRelation::create([
            'job_description_id' => $job->id,
            'relation_type' => 'internal',
            'entity_name' => 'Satuan Pengawas Intern (SPI)',
            'matter_description' => 'Audit jejak rekam sistem dan penyediaan data kepatuhan tata kelola TI.',
            'sort_order' => 2,
        ]);
        JobWorkingRelation::create([
            'job_description_id' => $job->id,
            'relation_type' => 'external',
            'entity_name' => 'Balai Sertifikasi Elektronik (BSrE - BSSN)',
            'matter_description' => 'Penerbitan dan integrasi sertifikat tanda tangan elektronik dokumen kedinasan.',
            'sort_order' => 1,
        ]);

        // Competencies with Proficiency Level 1-5 (ala Telkom Univ)
        JobCompetency::create([
            'job_description_id' => $job->id,
            'sub_competency' => 'Software Architecture & Modular Monolith Design',
            'proficiency_level' => 4,
            'key_indicator' => 'Mampu merancang batas bounded context antar-modul tanpa pelanggaran arsitektur dan mengisolasi skema database.',
            'sort_order' => 1,
        ]);
        JobCompetency::create([
            'job_description_id' => $job->id,
            'sub_competency' => 'Fullstack Web Engineering (Laravel & Vue.js)',
            'proficiency_level' => 4,
            'key_indicator' => 'Mampu mengimplementasikan fitur reaktif Inertia.js, TypeScript, dan Laravel Service Layer berkinerja tinggi.',
            'sort_order' => 2,
        ]);
        JobCompetency::create([
            'job_description_id' => $job->id,
            'sub_competency' => 'Database Optimization & Row-Level Locking',
            'proficiency_level' => 3,
            'key_indicator' => 'Mampu menyusun transaksi atomik pesimistik untuk penomoran dokumen anti-tabrakan.',
            'sort_order' => 3,
        ]);

        // Specification
        JobSpecification::create([
            'job_description_id' => $job->id,
            'education_level' => 'S1 Teknik Informatika / Sistem Informasi / Ilmu Komputer',
            'work_experience' => 'Minimal 3 tahun pengalaman di bidang Software Engineering',
            'certifications' => 'Certified Secure Software Lifecycle Professional / AWS Solutions Architect (Diutamakan)',
            'trainings' => 'Advanced PostgreSQL Optimization, Web Application Security OWASP Top 10',
            'hard_skills' => ['PHP 8.3 / Laravel 11', 'Vue 3 / TypeScript', 'PostgreSQL Multi-Schema', 'Docker & CI/CD Pipelines'],
            'soft_skills' => ['Analytical Thinking', 'Cross-Functional Teamwork', 'Problem Solving under High Pressure'],
        ]);

        // Standards (KPI)
        JobStandard::create([
            'job_description_id' => $job->id,
            'indicator_name' => 'Ketersediaan & Uptime Sistem Portal',
            'target_value' => 'Availability ≥ 99.8% setiap bulan',
            'sort_order' => 1,
        ]);
        JobStandard::create([
            'job_description_id' => $job->id,
            'indicator_name' => 'Waktu Penyelesaian Bug / Insiden Kritis',
            'target_value' => 'MTTR < 4 jam untuk insiden keparahan tinggi',
            'sort_order' => 2,
        ]);
        JobStandard::create([
            'job_description_id' => $job->id,
            'indicator_name' => 'Kualitas Kode & Standar Analisis Statis',
            'target_value' => '100% lulus Pest test suite, 0 error Larastan, 0 violation Deptrac',
            'sort_order' => 3,
        ]);

        // Approvals
        JobApproval::create([
            'job_description_id' => $job->id,
            'stage' => 'maker',
            'role_title' => 'Pengusul / Tim IT',
            'approver_user_id' => $author->id,
            'approver_name' => 'Ahmad Fauzi, S.Kom.',
            'decision' => 'approved',
            'notes' => 'Rancangan deskripsi jabatan selesai disusun.',
            'decided_at' => now()->subDays(5),
        ]);
        JobApproval::create([
            'job_description_id' => $job->id,
            'stage' => 'checker',
            'role_title' => 'Pemeriksa / Kepala Seksi TI',
            'approver_user_id' => $author->id,
            'approver_name' => 'Budi Santoso, S.T.',
            'decision' => 'approved',
            'notes' => 'Tugas pokok dan kualifikasi keahlian sesuai dengan kebutuhan modernisasi.',
            'decided_at' => now()->subDays(3),
        ]);
        JobApproval::create([
            'job_description_id' => $job->id,
            'stage' => 'approver',
            'role_title' => 'Penyetuju / Kepala Departemen',
            'approver_user_id' => $author->id,
            'approver_name' => 'Dr. Hendra Wijaya, M.Sc.',
            'decision' => 'approved',
            'notes' => 'Disetujui sebagai acuan resmi spesifikasi jabatan analis sistem PTKP.',
            'decided_at' => now()->subDay(),
        ]);
    }

    protected function seedOperatorPk1(User $author): void
    {
        $unit = DB::table('platform.org_units')->where('code', 'like', '%PK1%')->first()
            ?? DB::table('platform.org_units')->first();
        $position = DB::table('platform.positions')->where('name', 'like', '%Operator%')->first()
            ?? DB::table('platform.positions')->first();

        $jobId = (string) Str::uuid();

        $job = JobDescription::create([
            'id' => $jobId,
            'position_id' => $position->id,
            'org_unit_id' => $unit->id,
            'document_number' => '015/JD/PTKP/PK1/IV/2026',
            'revision_number' => 0,
            'sotk_reference' => 'SK 009/KPTS/PTKP/IV/2026',
            'job_group' => 'Operasional Pabrik & Produksi Kertas Uang',
            'grade_recommendation' => 'Grade 08 (Pelaksana Utama)',
            'job_summary' => 'Mengoperasikan mesin pembentukan lembaran kertas sekuriti (Fourdrinier & Cylinder Vat), memantau parameter gramatur, formasi, dan penanaman benang pengaman guna menghasilkan kertas uang bermutu tinggi.',
            'subordinate_notes' => '1. Helper / Operator Pembantu Mesin',
            'operating_challenges' => "1. Menjaga kestabilan gramatur kertas sekuriti di tengah variasi kelembaban dan kualitas bubur selulosa.\n2. Bekerja di lingkungan mesin berputar bersuhu tinggi dengan kepatuhan K3 dan pengawasan bahan sekuriti ketat.",
            'environmental_conditions' => 'Lini pabrik mesin kertas basah, suhu hangat, kebisingan sedang, wajib APD lengkap (earplug, safety shoes, helm).',
            'incidental_duties' => 'Membantu proses pembersihan saringan kawat (wire mesh) dan penggantian silinder cetak benang air.',
            'status' => 'approved',
            'tte_hash' => hash('sha256', "JOBDESK|{$jobId}|015/JD/PTKP/PK1/IV/2026"),
            'tte_signed_at' => now(),
            'created_by' => $author->id,
        ]);

        JobTask::create([
            'job_description_id' => $job->id,
            'activity_name' => 'Pengoperasian Mesin Kertas Sekuriti',
            'activity_details' => "[1]. Menjalankan startup dan shutdown mesin sesuai SOP baku PTKP.\n[2]. Memonitor aliran suspensi bubur kertas pada headbox dan silinder pembentuk.",
            'sort_order' => 1,
        ]);
        JobTask::create([
            'job_description_id' => $job->id,
            'activity_name' => 'Pengawasan Penyisipan Benang Pengaman',
            'activity_details' => "[1]. Memastikan benang pengaman sekuriti tertanam presisi pada posisi marka lembaran kertas.\n[2]. Melaporkan segera anomali deviasi benang pengaman ke pengawas shift.",
            'sort_order' => 2,
        ]);

        JobGovernance::create([
            'job_description_id' => $job->id,
            'type' => 'responsibility',
            'description' => 'Bertanggung jawab atas kesesuaian gramatur kertas uang terhadap batas toleransi Bank Indonesia.',
            'sort_order' => 1,
        ]);
        JobGovernance::create([
            'job_description_id' => $job->id,
            'type' => 'authority',
            'description' => 'Berwenang melakukan penghentian darurat (emergency stop) jika terjadi lilitan kertas pada silinder press.',
            'sort_order' => 1,
        ]);

        JobWorkingRelation::create([
            'job_description_id' => $job->id,
            'relation_type' => 'internal',
            'entity_name' => 'Departemen QC & Laboratorium',
            'matter_description' => 'Penyerahan sampel lembaran kertas per gulungan untuk pengujian ketahanan tarik dan kelembaban.',
            'sort_order' => 1,
        ]);
        JobWorkingRelation::create([
            'job_description_id' => $job->id,
            'relation_type' => 'internal',
            'entity_name' => 'Departemen Teknik & Pemeliharaan',
            'matter_description' => 'Pelaporan getaran abnormal pompa vakum atau kebocoran uap pengering.',
            'sort_order' => 2,
        ]);

        JobCompetency::create([
            'job_description_id' => $job->id,
            'sub_competency' => 'Operasional Mesin Cylinder Mold & Fourdrinier',
            'proficiency_level' => 3,
            'key_indicator' => 'Mampu mengendalikan kecepatan mesin dan ketebalan formasi serat kertas sekuriti secara mandiri.',
            'sort_order' => 1,
        ]);
        JobCompetency::create([
            'job_description_id' => $job->id,
            'sub_competency' => 'Keselamatan & Kesehatan Kerja (K3 Pabrik Kertas)',
            'proficiency_level' => 3,
            'key_indicator' => 'Menerapkan prosedur lockout/tagout (LOTO) dan identifikasi bahaya di area mesin panas.',
            'sort_order' => 2,
        ]);

        JobSpecification::create([
            'job_description_id' => $job->id,
            'education_level' => 'SMK Teknik Mesin / Kimia Industri / D3 Teknologi Kertas',
            'work_experience' => 'Minimal 2 tahun pengalaman di operasional mesin industri continuous process',
            'certifications' => 'Sertifikat K3 Dasar Pabrik Kertas',
            'trainings' => 'Pelatihan Teknologi Pembuatan Kertas Khusus, Pengoperasian DCS Mesin',
            'hard_skills' => ['Kontrol Tekanan Vakum Mesin Kertas', 'Pengukuran Gramatur & Ketebalan', 'Penanganan Benang Sekuriti'],
            'soft_skills' => ['Ketelitian Tinggi', 'Disiplin Waktu Kerja Shift', 'Komunikasi Tanggap Bahaya'],
        ]);

        JobStandard::create([
            'job_description_id' => $job->id,
            'indicator_name' => 'Pencapaian Target Tonase & OEE Mesin PK1',
            'target_value' => 'OEE Mesin ≥ 85% per bulan',
            'sort_order' => 1,
        ]);
        JobStandard::create([
            'job_description_id' => $job->id,
            'indicator_name' => 'Toleransi Gramatur Kertas Sekuriti',
            'target_value' => 'Penyimpangan gramatur < ± 1.5% dari spesifikasi',
            'sort_order' => 2,
        ]);

        JobApproval::create([
            'job_description_id' => $job->id,
            'stage' => 'maker',
            'role_title' => 'Pembuat / Pengusul',
            'approver_user_id' => $author->id,
            'approver_name' => 'Deden Kurniawan',
            'decision' => 'approved',
            'notes' => 'Spesifikasi operator mesin PK1 diperbarui.',
            'decided_at' => now()->subDays(6),
        ]);
        JobApproval::create([
            'job_description_id' => $job->id,
            'stage' => 'checker',
            'role_title' => 'Kepala Seksi Mesin PK1',
            'approver_user_id' => $author->id,
            'approver_name' => 'Ir. Agus Supriyanto',
            'decision' => 'approved',
            'notes' => 'Rincian tugas dan standar gramatur telah sesuai SOP BI.',
            'decided_at' => now()->subDays(4),
        ]);
        JobApproval::create([
            'job_description_id' => $job->id,
            'stage' => 'approver',
            'role_title' => 'Kepala Departemen Produksi PK1',
            'approver_user_id' => $author->id,
            'approver_name' => 'Bambang Sudibyo, M.M.',
            'decision' => 'approved',
            'notes' => 'Disahkan untuk penugasan operator pabrik.',
            'decided_at' => now()->subDays(2),
        ]);
    }

    protected function seedQcAnalyst(User $author): void
    {
        $unit = DB::table('platform.org_units')->where('code', 'like', '%QCL%')->first()
            ?? DB::table('platform.org_units')->where('code', 'like', '%DIR-OPS%')->first()
            ?? DB::table('platform.org_units')->first();
        $position = DB::table('platform.positions')->where('name', 'like', '%Analis%')->first()
            ?? DB::table('platform.positions')->skip(1)->first();

        $jobId = (string) Str::uuid();

        $job = JobDescription::create([
            'id' => $jobId,
            'position_id' => $position->id,
            'org_unit_id' => $unit->id,
            'document_number' => '021/JD/PTKP/QC/IV/2026',
            'revision_number' => 0,
            'sotk_reference' => 'SK 009/KPTS/PTKP/IV/2026',
            'job_group' => 'Penjaminan Mutu & Pengujian Laboratorium',
            'grade_recommendation' => 'Grade 10 (Senior Analyst)',
            'job_summary' => 'Melaksanakan pengujian fisis, kimiawi, dan optis terhadap bahan baku, pulp, serta lembaran kertas uang dan kertas sekuriti guna memastikan kepatuhan penuh terhadap standar mutu SNI dan ISO 9001/17025.',
            'subordinate_notes' => '1. Teknisi Laboratorium / Sampler',
            'operating_challenges' => 'Menjaga akurasi alat uji laboratorium berkalibrasi tinggi dan memberikan hasil analisis cepat saat mesin produksi sedang berjalan.',
            'environmental_conditions' => 'Laboratorium pengujian ber-AC dengan suhu 23°C ± 1°C dan kelembaban 50% ± 2% (Standar ISO 187).',
            'incidental_duties' => 'Menyusun sertifikat analisis (COA) untuk pengiriman produk ekspor kertas sekuriti.',
            'status' => 'approved',
            'tte_hash' => hash('sha256', "JOBDESK|{$jobId}|021/JD/PTKP/QC/IV/2026"),
            'tte_signed_at' => now(),
            'created_by' => $author->id,
        ]);

        JobTask::create([
            'job_description_id' => $job->id,
            'activity_name' => 'Pengujian Parameter Fisis Kertas',
            'activity_details' => "[1]. Menguji ketahanan tarik (tensile strength), ketahanan sobek (tear index), dan ketahanan lipat (folding endurance).\n[2]. Mengukur ketahanan air (Cobb test) dan porositas udara Bendtsen.",
            'sort_order' => 1,
        ]);
        JobTask::create([
            'job_description_id' => $job->id,
            'activity_name' => 'Verifikasi Fitur Sekuriti Optis',
            'activity_details' => "[1]. Memeriksa kontras tanda air (watermark) menggunakan densitometer transmisi.\n[2]. Memverifikasi fluoresensi serat sekuriti di bawah sinar ultraviolet (UV-A dan UV-C).",
            'sort_order' => 2,
        ]);

        JobCompetency::create([
            'job_description_id' => $job->id,
            'sub_competency' => 'Metode Pengujian Laboratorium Kertas (ISO 17025)',
            'proficiency_level' => 4,
            'key_indicator' => 'Mampu mengoperasikan instrumen L&W Tester dan mengkalkulasi ketidakpastian pengukuran.',
            'sort_order' => 1,
        ]);

        JobSpecification::create([
            'job_description_id' => $job->id,
            'education_level' => 'D3 / S1 Kimia Murni / Teknik Kimia / Analisis Kimia',
            'work_experience' => 'Minimal 2 tahun di laboratorium pengujian material kertas / tekstil / polimer',
            'certifications' => 'Sertifikasi Personel Laboratorium Penguji (ISO/IEC 17025)',
            'trainings' => 'Analisis Spektrofotometri, Kalibrasi Alat Uji Fisis Kertas',
            'hard_skills' => ['Pengujian Tensile & Tear', 'Inspeksi UV & Watermark', 'Kalkulasi Statistik QC'],
            'soft_skills' => ['Akurasi Detail', 'Integritas Hasil Uji', 'Komunikasi Antar-Departemen'],
        ]);

        JobStandard::create([
            'job_description_id' => $job->id,
            'indicator_name' => 'Kecepatan Rilis Hasil Uji Laboratorium',
            'target_value' => 'Hasil pengujian rutin selesai < 45 menit dari pengambilan sampel',
            'sort_order' => 1,
        ]);

        JobApproval::create([
            'job_description_id' => $job->id,
            'stage' => 'maker',
            'role_title' => 'Pengusul / Analis Kimia',
            'approver_user_id' => $author->id,
            'approver_name' => 'Siti Nurhaliza, S.Si.',
            'decision' => 'approved',
            'notes' => 'Draft spesifikasi analis laboratorium selesai.',
            'decided_at' => now()->subDays(3),
        ]);
        JobApproval::create([
            'job_description_id' => $job->id,
            'stage' => 'approver',
            'role_title' => 'Kepala Departemen QC & Laboratorium',
            'approver_user_id' => $author->id,
            'approver_name' => 'Dra. Ratna Juwita, Apt.',
            'decision' => 'approved',
            'notes' => 'Disetujui untuk panduan kompetensi analis pengujian kertas.',
            'decided_at' => now()->subDay(),
        ]);
    }

    protected function seedKasiPersonalia(User $author): void
    {
        $unit = DB::table('platform.org_units')->where('code', 'like', '%SDM%')->first()
            ?? DB::table('platform.org_units')->first();
        $position = DB::table('platform.positions')->where('name', 'like', '%Kepala Seksi%')->first()
            ?? DB::table('platform.positions')->first();

        $jobId = (string) Str::uuid();

        $job = JobDescription::create([
            'id' => $jobId,
            'position_id' => $position->id,
            'org_unit_id' => $unit->id,
            'document_number' => '008/JD/PTKP/SDM/IV/2026',
            'revision_number' => 0,
            'sotk_reference' => 'SK 009/KPTS/PTKP/IV/2026',
            'job_group' => 'Manajemen Sumber Daya Manusia & Hubungan Industrial',
            'grade_recommendation' => 'Grade 13 (Manajerial Eselon III)',
            'job_summary' => 'Memimpin perencanaan, administrasi kepegawaian, kompensasi & benefit, hubungan industrial ketenagakerjaan, serta pemenuhan kepatuhan ketenagakerjaan di lingkungan PT Kertas Padalarang.',
            'subordinate_notes' => "1. Staf Administrasi Personalia\n2. Staf Hubungan Industrial & Kesejahteraan\n3. Staf Penggajian (Payroll)",
            'operating_challenges' => 'Menjaga keharmonisan hubungan industrial dengan serikat pekerja pabrik di tengah penyesuaian target produktivitas dan kepatuhan regulasi ketenagakerjaan.',
            'environmental_conditions' => 'Ruang kerja perkantoran Departemen SDM dengan aktivitas interaksi koordinasi intensif.',
            'incidental_duties' => 'Mewakili perusahaan dalam mediasi ketenagakerjaan dengan Dinas Tenaga Kerja dan instansi terkait.',
            'status' => 'approved',
            'tte_hash' => hash('sha256', "JOBDESK|{$jobId}|008/JD/PTKP/SDM/IV/2026"),
            'tte_signed_at' => now(),
            'created_by' => $author->id,
        ]);

        JobTask::create([
            'job_description_id' => $job->id,
            'activity_name' => 'Pengelolaan Administrasi Kepegawaian & Mutasi',
            'activity_details' => "[1]. Merumuskan surat keputusan penugasan, promosi, dan mutasi pegawai sesuai SK Direksi.\n[2]. Memverifikasi ketepatan data presensi shift pabrik dan perhitungan lembur.",
            'sort_order' => 1,
        ]);
        JobTask::create([
            'job_description_id' => $job->id,
            'activity_name' => 'Pembinaan Hubungan Industrial & Kesejahteraan',
            'activity_details' => "[1]. Memfasilitasi forum komunikasi berkala antara manajemen dan Serikat Pekerja PTKP.\n[2]. Mengawasi pelaksanaan jaminan kesehatan BPJS dan program perlindungan pensiun.",
            'sort_order' => 2,
        ]);

        JobCompetency::create([
            'job_description_id' => $job->id,
            'sub_competency' => 'Hukum Ketenagakerjaan & Hubungan Industrial',
            'proficiency_level' => 4,
            'key_indicator' => 'Menguasai UU Ketenagakerjaan, PP Turunan, dan Perjanjian Kerja Bersama (PKB) korporat.',
            'sort_order' => 1,
        ]);

        JobSpecification::create([
            'job_description_id' => $job->id,
            'education_level' => 'S1 Hukum / Manajemen SDM / Psikologi Organisasi',
            'work_experience' => 'Minimal 5 tahun di bidang Human Resources / Industrial Relations',
            'certifications' => 'Sertifikasi BNSP Manajer SDM / Hubungan Industrial',
            'trainings' => 'Negosiasi Perjanjian Kerja Bersama, Manajemen Kompensasi & Benefit',
            'hard_skills' => ['Hubungan Industrial & Mediasi', 'Payroll & BPJS Ketenagakerjaan', 'Penyusunan Peraturan Perusahaan'],
            'soft_skills' => ['Kepemimpinan Kolaboratif', 'Komunikasi Persuasif', 'Manajemen Konflik'],
        ]);

        JobStandard::create([
            'job_description_id' => $job->id,
            'indicator_name' => 'Tingkat Kepatuhan Regulasi Ketenagakerjaan',
            'target_value' => '100% kepatuhan regulasi ketenagakerjaan dan zero sengketa PHI',
            'sort_order' => 1,
        ]);

        JobApproval::create([
            'job_description_id' => $job->id,
            'stage' => 'maker',
            'role_title' => 'Pengusul',
            'approver_user_id' => $author->id,
            'approver_name' => 'Wahyudi, S.H.',
            'decision' => 'approved',
            'notes' => 'Job description Kasi Personalia telah dimutakhirkan.',
            'decided_at' => now()->subDays(4),
        ]);
        JobApproval::create([
            'job_description_id' => $job->id,
            'stage' => 'approver',
            'role_title' => 'Kepala Departemen SDM & Umum',
            'approver_user_id' => $author->id,
            'approver_name' => 'Drs. Rahmat Hidayat, M.M.',
            'decision' => 'approved',
            'notes' => 'Disahkan untuk acuan evaluasi kinerja jabatan struktural SDM.',
            'decided_at' => now()->subDay(),
        ]);
    }
}
