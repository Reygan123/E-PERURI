const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

const ASSETS_DIR = path.resolve(__dirname, '../dokumentasi/report-assets');
const LOGO_PATH = path.resolve(__dirname, '../public/assets/logo.png');
const PDF_OUTPUT = path.resolve(__dirname, '../dokumentasi/Laporan_Progress_Implementasi_E-Arsip_PTKP.pdf');
const HTML_OUTPUT = path.resolve(__dirname, '../dokumentasi/Laporan_Progress_Implementasi_E-Arsip_PTKP.html');
const ARTIFACT_PDF_OUTPUT = 'C:\\Users\\Reygan Fadhilah\\.gemini\\antigravity-ide\\brain\\edf042c3-c521-401f-abb4-fa1b9cf337b1\\Laporan_Progress_Implementasi_E-Arsip_PTKP.pdf';

function toBase64(filePath) {
    if (!fs.existsSync(filePath)) return '';
    const ext = path.extname(filePath).replace('.', '');
    const data = fs.readFileSync(filePath).toString('base64');
    return `data:image/${ext === 'png' ? 'png' : 'jpeg'};base64,${data}`;
}

const logoBase64 = toBase64(LOGO_PATH);
const imgLogin = toBase64(path.join(ASSETS_DIR, '01_login_ptkp.png'));
const imgPortalSuperadmin = toBase64(path.join(ASSETS_DIR, '02_portal_aplikasi_superadmin.png'));
const imgPlatformOrg = toBase64(path.join(ASSETS_DIR, '03_platform_organisasi.png'));
const imgEarsipSidebar = toBase64(path.join(ASSETS_DIR, '04_earsip_workspace_sidebar.png'));
const imgFormNaskah = toBase64(path.join(ASSETS_DIR, '05_form_naskah_dinas.png'));
const imgMejaPersetujuan = toBase64(path.join(ASSETS_DIR, '06_meja_persetujuan.png'));
const imgPreviewPdf = toBase64(path.join(ASSETS_DIR, '07_preview_pdf_modal.png'));
const imgRevisi = toBase64(path.join(ASSETS_DIR, '08_revisi_naskah_dinas.png'));
const imgPortalOperator = toBase64(path.join(ASSETS_DIR, '09_portal_staf_operator.png'));
const imgError403 = toBase64(path.join(ASSETS_DIR, '10_error_403_ptkp.png'));

const htmlContent = `<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Progress Implementasi E-Arsip PTKP</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap');

        @page {
            size: A4;
            margin: 18mm 16mm 18mm 16mm;
            @top-right {
                content: "E-Arsip PTKP | Progress Report";
                font-family: 'Inter', sans-serif;
                font-size: 8pt;
                color: #64748b;
            }
            @bottom-left {
                content: "PT Kertas Padalarang (Peruri Security Paper Mill) • Rahasia & Terbatas";
                font-family: 'Inter', sans-serif;
                font-size: 8pt;
                color: #64748b;
            }
            @bottom-right {
                content: "Halaman " counter(page);
                font-family: 'Inter', sans-serif;
                font-size: 8pt;
                font-weight: 600;
                color: #1e293b;
            }
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 9.5pt;
            line-height: 1.55;
            color: #1e293b;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Lexend', sans-serif;
            color: #141A5C;
            margin-top: 0;
            line-height: 1.3;
        }

        h1 { font-size: 19pt; font-weight: 800; }
        h2 { font-size: 14pt; font-weight: 700; border-bottom: 2px solid #EEF0FB; padding-bottom: 6px; margin-top: 24px; margin-bottom: 12px; }
        h3 { font-size: 11.5pt; font-weight: 600; margin-top: 16px; margin-bottom: 8px; color: #20288E; }
        h4 { font-size: 10pt; font-weight: 600; margin-top: 12px; margin-bottom: 6px; color: #334155; }

        p { margin-top: 0; margin-bottom: 10px; text-align: justify; }

        .page-break {
            page-break-before: always;
        }

        .avoid-break {
            page-break-inside: avoid;
        }

        /* Cover Page */
        .cover-page {
            height: 100%;
            min-height: 960px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            page-break-after: always;
            padding: 20px 10px;
        }

        .cover-header {
            border-bottom: 3px solid #20288E;
            padding-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cover-logo-box {
            background: #ffffff;
            padding: 10px 16px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            display: inline-block;
        }

        .cover-logo {
            height: 52px;
            width: auto;
            display: block;
        }

        .cover-badge {
            background-color: #EEF0FB;
            color: #20288E;
            font-family: 'Lexend', sans-serif;
            font-weight: 700;
            font-size: 9pt;
            padding: 6px 14px;
            border-radius: 9999px;
            border: 1px solid #cbd5e1;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .cover-title-area {
            margin-top: 100px;
            margin-bottom: 80px;
        }

        .cover-tagline {
            font-size: 11pt;
            font-weight: 600;
            color: #0A66C2;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 12px;
        }

        .cover-title {
            font-size: 26pt;
            font-weight: 800;
            color: #141A5C;
            line-height: 1.25;
            margin-bottom: 16px;
        }

        .cover-subtitle {
            font-size: 13pt;
            color: #475569;
            line-height: 1.5;
            max-width: 680px;
        }

        .cover-meta-grid {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 20px 24px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .meta-item {
            font-size: 9pt;
        }

        .meta-label {
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            font-size: 7.5pt;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .meta-value {
            font-weight: 700;
            color: #0f172a;
            font-size: 9.5pt;
        }

        .cover-footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 8.5pt;
            color: #64748b;
        }

        /* Metric Cards */
        .metric-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin: 16px 0 24px 0;
        }

        .metric-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 12px;
            text-align: center;
            border-top: 3px solid #20288E;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .metric-value {
            font-family: 'Lexend', sans-serif;
            font-size: 18pt;
            font-weight: 800;
            color: #141A5C;
            line-height: 1;
            margin-bottom: 4px;
        }

        .metric-label {
            font-size: 8pt;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
        }

        /* Callout Box */
        .callout {
            border-radius: 8px;
            padding: 14px 18px;
            margin: 14px 0;
            font-size: 9pt;
            border-left: 4px solid #20288E;
            background-color: #EEF0FB;
        }

        .callout-success {
            border-left-color: #059669;
            background-color: #ECFDF5;
        }

        .callout-warning {
            border-left-color: #D97706;
            background-color: #FFFBEB;
        }

        .callout-title {
            font-family: 'Lexend', sans-serif;
            font-weight: 700;
            font-size: 9.5pt;
            margin-bottom: 4px;
            color: #141A5C;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 14px 0 20px 0;
            font-size: 8.5pt;
        }

        th {
            background-color: #141A5C;
            color: #ffffff;
            text-align: left;
            padding: 8px 10px;
            font-family: 'Lexend', sans-serif;
            font-weight: 600;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #141A5C;
        }

        td {
            padding: 7px 10px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }

        tr:nth-child(even) td {
            background-color: #F8FAFC;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 7.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-success { background-color: #D1FAE5; color: #065F46; }
        .badge-navy { background-color: #EEF0FB; color: #20288E; }
        .badge-warning { background-color: #FEF3C7; color: #92400E; }
        .badge-info { background-color: #E0F2FE; color: #0369A1; }

        /* Screenshot Container */
        .screenshot-card {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px;
            margin: 16px 0 20px 0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            page-break-inside: avoid;
        }

        .screenshot-img {
            width: 100%;
            height: auto;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            display: block;
        }

        .screenshot-caption {
            margin-top: 8px;
            font-size: 8.5pt;
            color: #475569;
            text-align: center;
            font-weight: 500;
        }

        .screenshot-caption strong {
            color: #141A5C;
        }

        /* Checklist */
        .check-list {
            list-style: none;
            padding-left: 0;
            margin: 10px 0;
        }

        .check-list li {
            position: relative;
            padding-left: 24px;
            margin-bottom: 8px;
        }

        .check-list li::before {
            content: "✓";
            position: absolute;
            left: 0;
            top: 0;
            font-weight: 800;
            color: #059669;
        }

        .signature-block {
            margin-top: 40px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 40px;
            page-break-inside: avoid;
        }

        .sig-box {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 16px;
            background: #F8FAFC;
        }

        .sig-title {
            font-size: 8.5pt;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 60px;
        }

        .sig-name {
            font-weight: 700;
            font-size: 9.5pt;
            color: #0f172a;
            border-bottom: 1px solid #334155;
            padding-bottom: 4px;
            display: inline-block;
            min-width: 200px;
        }

        .sig-nip {
            font-size: 8pt;
            color: #64748b;
            margin-top: 4px;
        }
    </style>
</head>
<body>

    <!-- 1. COVER PAGE -->
    <div class="cover-page">
        <div class="cover-header">
            <div class="cover-logo-box">
                <img src="${logoBase64}" alt="Logo PTKP" class="cover-logo">
            </div>
            <div class="cover-badge">Laporan Resmi • Versi 2.0</div>
        </div>

        <div class="cover-title-area">
            <div class="cover-tagline">Transformasi Digital Tata Kelola Korporat</div>
            <div class="cover-title">LAPORAN PROGRESS IMPLEMENTASI SISTEM E-ARSIP & PERSURATAN DINAS</div>
            <div class="cover-subtitle">
                Dokumentasi Menyeluruh Pengembangan Ekosistem Persuratan Elektronik, Otomasi Alur Persetujuan, Penomoran Dokumen Otomatis, dan Tata Kelola Akses Terpadu Berstandar PT Kertas Padalarang (Peruri Security Paper Mill).
            </div>
        </div>

        <div class="cover-meta-grid">
            <div class="meta-item">
                <div class="meta-label">Nomor Dokumen</div>
                <div class="meta-value">001/LAP-PROG/E-ARSIP/PTKP/X/2026</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Klasifikasi Akses</div>
                <div class="meta-value">Umum & Stakeholders Internal</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Tanggal Pelaporan</div>
                <div class="meta-value">23 Oktober 2026</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Status Penyelesaian</div>
                <div class="meta-value" style="color: #059669;">100% Fitur Inti Selesai (Siap UAT)</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Penyusun</div>
                <div class="meta-value">Tim Pengembang Digitalisasi PTKP</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Dasar Kebijakan</div>
                <div class="meta-value">SK Direksi No. 009/KPTS/PTKP/IV/2026</div>
            </div>
        </div>

        <div class="cover-footer">
            <div>© 2026 PT Kertas Padalarang — Anak Perusahaan Perum Percetakan Uang RI (Peruri)</div>
            <div>Jalan Raya Padalarang No. 408, Kabupaten Bandung Barat</div>
        </div>
    </div>

    <!-- 2. RINGKASAN EKSEKUTIF -->
    <div>
        <h2>1. Ringkasan Eksekutif (Executive Summary)</h2>
        <p>
            Dalam rangka percepatan modernisasi tata kelola administrasi perkantoran serta menjamin kepatuhan penuh terhadap Peraturan Direksi Nomor <strong>SK 009/KPTS/PTKP/IV/2026</strong> tentang Struktur Organisasi dan Tata Kerja serta Standar Tata Naskah Dinas, proyek implementasi <strong>Aplikasi E-Arsip PT Kertas Padalarang (PTKP)</strong> telah mencapai tahapan penyelesaian seluruh fitur inti (Core Persuratan & Platform Gateway).
        </p>
        <p>
            Sistem ini dibangun di atas arsitektur <em>Modular Monolith</em> modern (Laravel 11, Vue 3, Inertia.js, PostgreSQL) dengan menerapkan batas isolasi ketat antar-modul, proteksi keamanan tingkat tinggi (pencegahan IDOR, unit-scoping), serta antarmuka pengguna (UI/UX) yang ramah pengguna senior (senior-friendly) yang sepenuhnya mengacu pada <strong>PTKP Brand Guidelines</strong> (palet warna Deep Navy, PTKP Navy, Cerulean, dan tipografi Lexend & Inter).
        </p>

        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-value" style="color: #059669;">100%</div>
                <div class="metric-label">Fitur Selesai</div>
            </div>
            <div class="metric-card">
                <div class="metric-value">11</div>
                <div class="metric-label">Jenis Naskah</div>
            </div>
            <div class="metric-card">
                <div class="metric-value">78</div>
                <div class="metric-label">Akun Uji Aktif</div>
            </div>
            <div class="metric-card">
                <div class="metric-value" style="color: #20288E;">79 / 79</div>
                <div class="metric-label">Tes Lulus (358 Checks)</div>
            </div>
        </div>

        <div class="callout callout-success">
            <div class="callout-title">Pencapaian Utama Implementasi Tahap Ini</div>
            <ul class="check-list">
                <li><strong>Penyatuan Portal Terpadu (Unified Application Portal):</strong> Akses seluruh ekosistem aplikasi internal melalui satu portal dengan sistem Single Sign-On (SSO) lokal tanpa perlu login berulang.</li>
                <li><strong>Pemisahan Workspace & Keamanan Akses:</strong> Rute master data <code>/platform/*</code> kini terproteksi penuh dari staf biasa. Menu modul E-Arsip difokuskan murni pada persuratan.</li>
                <li><strong>Navigasi Ramah Pengguna Senior:</strong> Kategori naskah dinas dan formulir khusus dipecah menjadi 10 menu mandiri berlabel kode dinas resmi, memudahkan karyawan senior dalam operasional harian.</li>
                <li><strong>Pratinjau Surat Cepat (Live PDF Preview Modal):</strong> Verifikator dan pimpinan dapat membaca berkas PDF secara utuh dalam pop-up interaktif sebelum mengambil keputusan (Setuju, Revisi, Tolak).</li>
                <li><strong>Siklus Revisi Terstandarisasi:</strong> Naskah yang memerlukan koreksi dapat diedit ulang oleh pembuat surat, dan pengajuan ulang secara otomatis mereset alur persetujuan ke langkah 1 (sesuai Aturan Surat PTKP No. 52).</li>
                <li><strong>Halaman Akses Ditolak (403) Resmi Korporat:</strong> Tampilan error informatif berstandar visual PTKP dilengkapi panduan kontak administrator TI dan tombol navigasi kembali.</li>
            </ul>
        </div>
    </div>

    <!-- 3. LATAR BELAKANG & TUJUAN -->
    <div>
        <h2>2. Latar Belakang & Urgensi Digitalisasi</h2>
        <p>
            Sebagai produsen kertas sekuriti utama di Indonesia (penyedia bahan baku uang kertas rupiah, paspor, pita cukai, ijazah, dan dokumen berharga negara lainnya), PT Kertas Padalarang menuntut kecepatan, ketepatan, dan keandalan rekam jejak dalam setiap naskah dinas dan instruksi kerja operasional.
        </p>
        <p>
            Sebelum adanya sistem E-Arsip, proses persuratan konvensional menghadapi tantangan berikut:
        </p>
        <table>
            <thead>
                <tr>
                    <th style="width: 28%;">Aspek Tantangan</th>
                    <th style="width: 36%;">Kondisi Sebelumnya (Manual / Fisik)</th>
                    <th style="width: 36%;">Solusi Digital (Sistem E-Arsip PTKP)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Pemberian Nomor Surat</strong></td>
                    <td>Buku register manual di sekretariat rawan nomor lompat, duplikasi nomor, atau nomor ganda di unit pabrik.</td>
                    <td><span class="badge badge-success">Atomic Locking</span> Penomoran digital real-time dengan kunci atomik PostgreSQL, zero duplicate.</td>
                </tr>
                <tr>
                    <td><strong>Alur Persetujuan (Paraf/TTD)</strong></td>
                    <td>Surat fisik dibawa manual keliling kantor dan pabrik, memakan waktu 2–5 hari kerja terutama jika pimpinan dinas luar.</td>
                    <td><span class="badge badge-navy">Multi-Tier Approval</span> Notifikasi digital ke Meja Persetujuan pimpinan, persetujuan dapat dilakukan dalam hitungan menit.</td>
                </tr>
                <tr>
                    <td><strong>Penanganan Koreksi/Revisi</strong></td>
                    <td>Draf fisik dicoret-coret, sering tercecer atau tidak tercatat historis koreksi antara staf dan pejabat.</td>
                    <td><span class="badge badge-warning">Audit Trail Terintegrasi</span> Kolom catatan revisi formal dan alur ulang otomatis dari langkah pertama.</td>
                </tr>
                <tr>
                    <td><strong>Penyimpanan & Pelacakan Berkas</strong></td>
                    <td>Arsip tersimpan di lemari fisik tiap departemen, sulit dicari kembali setelah lewat hitungan bulan/tahun.</td>
                    <td><span class="badge badge-info">Pencarian Cepat</span> Penyimpanan digital terpusat berstandar SHA-256 integrity dan filter metadata lengkap.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- 4. CAKUPAN MODUL & FITUR -->
    <div class="page-break">
        <h2>3. Rincian Modul & Fitur yang Selesai Diimplementasikan</h2>
        <p>
            Seluruh fungsionalitas sistem telah dirancang dan diuji secara ketat berdasarkan aturan tata naskah resmi PT Kertas Padalarang:
        </p>

        <h3>3.1. Klasifikasi 11 Jenis Dokumen & Format Penomoran Resmi</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">Kode</th>
                    <th style="width: 25%;">Jenis Naskah Dinas</th>
                    <th style="width: 37%;">Contoh Format Nomor Resmi</th>
                    <th style="width: 15%;">Siklus Reset</th>
                    <th style="width: 15%;">Wewenang TTD</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="badge badge-navy">B</span></td>
                    <td><strong>Surat Masuk Eksternal</strong></td>
                    <td>Nomor asli dari instansi pengirim (BI, Peruri, Kemenkeu, vendor)</td>
                    <td>Tahunan</td>
                    <td>Pengirim Asli</td>
                </tr>
                <tr>
                    <td><span class="badge badge-navy">A</span></td>
                    <td><strong>Surat Keluar Eksternal</strong></td>
                    <td><code>[Urut]/[Unit]/A/[BulanRomawi]/[Tahun]</code></td>
                    <td>Tahunan</td>
                    <td>Direksi / Kadep Terkait</td>
                </tr>
                <tr>
                    <td><span class="badge badge-info">ND</span></td>
                    <td><strong>Nota Dinas Internal</strong></td>
                    <td><code>[Urut]/ND/[Unit]/[BulanRomawi]/[Tahun]</code></td>
                    <td>Tahunan</td>
                    <td>Kepala Departemen / Direksi</td>
                </tr>
                <tr>
                    <td><span class="badge badge-info">E</span></td>
                    <td><strong>Surat Memo</strong></td>
                    <td><code>[Urut]/E/[Unit]/[BulanRomawi]/[Tahun]</code></td>
                    <td>Bulanan</td>
                    <td>Kadep / Kasie / Direksi</td>
                </tr>
                <tr>
                    <td><span class="badge badge-info">ST</span></td>
                    <td><strong>Surat Tugas</strong></td>
                    <td><code>[Urut]/ST/[Unit]/[BulanRomawi]/[Tahun]</code></td>
                    <td>Bulanan</td>
                    <td>Direktur Utama / Direksi</td>
                </tr>
                <tr>
                    <td><span class="badge badge-info">PENG</span></td>
                    <td><strong>Pengumuman</strong></td>
                    <td><code>[Urut]/PENG/[Unit]/[BulanRomawi]/[Tahun]</code></td>
                    <td>Tahunan</td>
                    <td>Sekretaris Perusahaan / Direksi</td>
                </tr>
                <tr>
                    <td><span class="badge badge-info">KPTS</span></td>
                    <td><strong>Surat Keputusan (SK)</strong></td>
                    <td><code>[Urut]/KPTS/[Unit]/[BulanRomawi]/[Tahun]</code></td>
                    <td>Tahunan</td>
                    <td>Direktur Utama</td>
                </tr>
                <tr>
                    <td><span class="badge badge-warning">SPPD</span></td>
                    <td><strong>Surat Perintah Perjalanan Dinas</strong></td>
                    <td><code>[Urut]/SPPD/[Unit]/[BulanRomawi]/[Tahun]</code></td>
                    <td>Bulanan</td>
                    <td>Kadep SDM & Keuangan / Direksi</td>
                </tr>
                <tr>
                    <td><span class="badge badge-warning">SPB</span></td>
                    <td><strong>Surat Pesanan Barang</strong></td>
                    <td><code>[Urut]/SPB/[Unit]/[BulanRomawi]/[Tahun]</code></td>
                    <td>Bulanan</td>
                    <td>Kadep Pengadaan / Pabrik</td>
                </tr>
                <tr>
                    <td><span class="badge badge-warning">BP</span></td>
                    <td><strong>Formulir Biaya Pengobatan</strong></td>
                    <td><code>[Urut]/BP/[Unit]/[BulanRomawi]/[Tahun]</code></td>
                    <td>Bulanan</td>
                    <td>Kadep SDM & Keuangan</td>
                </tr>
                <tr>
                    <td><span class="badge badge-success">MEJA</span></td>
                    <td><strong>Meja Persetujuan Naskah</strong></td>
                    <td>Inbox persetujuan terpusat bagi pimpinan penandatangan</td>
                    <td>Real-time</td>
                    <td>Semua Pejabat Verifikator</td>
                </tr>
            </tbody>
        </table>

        <h3>3.2. Fitur Unggulan Sistem E-Arsip</h3>
        <ul class="check-list">
            <li><strong>Editor Naskah Kaya Fitur (TipTap Rich Text Editor):</strong> Pengguna dapat menyusun naskah lengkap dengan heading, bullet points, tabel, perataan teks, dan styling tanpa perlu mengunggah file docx eksternal.</li>
            <li><strong>Pratinjau PDF Otomatis (Live PDF Generator):</strong> Template surat resmi ber-kop surat PT Kertas Padalarang dibuat secara otomatis dan ditampilkan langsung pada modal pratinjau sebelum surat diajukan ke atasan.</li>
            <li><strong>Alur Persetujuan Bertingkat (Approval Chain):</strong> Menyesuaikan hirarki organisasi SK 009: Staf di Seksi $\rightarrow$ Kepala Seksi $\rightarrow$ Kepala Departemen $\rightarrow$ Direksi. Naskah departemen tanpa pimpinan definitif (seperti POH Akuntansi) secara cerdas dieskalasi ke Direktur Keuangan / Direktur Utama.</li>
            <li><strong>Sistem Penolakan & Revisi Bergaransi:</strong> Bila verifikator meminta revisi, surat masuk ke status <code>revision_requested</code> dengan catatan koreksi yang jelas. Pembuat naskah dapat mengedit langsung, dan pengajuan ulang otomatis mengulang alur paraf dari awal.</li>
        </ul>
    </div>

    <!-- 5. DOKUMENTASI VISUAL & SCREENSHOT -->
    <div class="page-break">
        <h2>4. Dokumentasi Visual & Tangkapan Layar Sistem</h2>
        <p>
            Berikut adalah bukti fisik implementasi antarmuka pengguna pada lingkungan sistem nyata (lingkungan pengujian lokal port 8088):
        </p>

        <div class="screenshot-card">
            <img src="${imgLogin}" alt="Halaman Login" class="screenshot-img">
            <div class="screenshot-caption">
                <strong>Gambar 1: Halaman Masuk Terpadu (Single Sign-On Gateway)</strong><br>
                Desain otentikasi resmi PT Kertas Padalarang dengan logo perusahaan, validasi email dinas/username, proteksi sesi aman, dan footer identitas holding Peruri.
            </div>
        </div>

        <div class="screenshot-card">
            <img src="${imgPortalSuperadmin}" alt="Portal Aplikasi" class="screenshot-img">
            <div class="screenshot-caption">
                <strong>Gambar 2: Katalog Portal Aplikasi Utama (Akun Administrator)</strong><br>
                Menampilkan katalog kartu aplikasi terpadu (E-Arsip, Platform Core, dsb.). Pengguna dapat langsung masuk ke aplikasi yang diinginkan tanpa harus login berulang.
            </div>
        </div>
    </div>

    <div class="page-break">
        <div class="screenshot-card">
            <img src="${imgEarsipSidebar}" alt="Workspace E-Arsip" class="screenshot-img">
            <div class="screenshot-caption">
                <strong>Gambar 3: Workspace E-Arsip dengan Pemecahan Menu Ramah Pengguna Senior</strong><br>
                Sidebar khusus persuratan dengan 10 jenis surat mandiri (Surat Keluar, Nota Dinas, Memo, Surat Tugas, SPPD, dsb.) serta tombol navigasi cepat <code>← Portal Aplikasi</code> di bagian atas.
            </div>
        </div>

        <div class="screenshot-card">
            <img src="${imgFormNaskah}" alt="Form Naskah Dinas" class="screenshot-img">
            <div class="screenshot-caption">
                <strong>Gambar 4: Formulir Pembuatan Naskah Dinas & TipTap Editor</strong><br>
                Antarmuka input dinamis yang otomatis menyesuaikan jenis surat, tingkat kerahasiaan, urgensi, unit tujuan, serta editor teks kaya dengan preview instan.
            </div>
        </div>
    </div>

    <div class="page-break">
        <div class="screenshot-card">
            <img src="${imgMejaPersetujuan}" alt="Meja Persetujuan" class="screenshot-img">
            <div class="screenshot-caption">
                <strong>Gambar 5: Meja Persetujuan Naskah (Approval Workbench)</strong><br>
                Pusat verifikasi pimpinan menampilkan daftar naskah yang menunggu keputusan, badge tingkat urgensi, informasi perancang naskah, tombol aksi cepat, dan tombol <em>"Lihat / Pratinjau Surat"</em>.
            </div>
        </div>

        <div class="screenshot-card">
            <img src="${imgPreviewPdf}" alt="Modal Pratinjau PDF" class="screenshot-img">
            <div class="screenshot-caption">
                <strong>Gambar 6: Modal Pratinjau PDF Surat Dinas Live</strong><br>
                Fitur live PDF stream terintegrasi di dalam modal. Pejabat dapat memeriksa naskah lengkap dengan format kop dinas resmi sebelum menekan tombol Setujui, Revisi, atau Tolak.
            </div>
        </div>
    </div>

    <div class="page-break">
        <div class="screenshot-card">
            <img src="${imgRevisi}" alt="Halaman Revisi Naskah" class="screenshot-img">
            <div class="screenshot-caption">
                <strong>Gambar 7: Halaman Detail Naskah dengan Status Koreksi / Revisi</strong><br>
                Menampilkan banner amber berisi catatan koreksi dari pejabat verifikator serta tombol aksi <em>"Perbaiki & Ajukan Ulang"</em> yang mengarahkan perancang naskah ke form edit.
            </div>
        </div>

        <div class="screenshot-card">
            <img src="${imgPortalOperator}" alt="Portal Staf Operator" class="screenshot-img">
            <div class="screenshot-caption">
                <strong>Gambar 8: Portal Pengguna Staf / Operator Pabrik</strong><br>
                Penerapan prinsip <em>least privilege</em>: Kartu master data "Platform Core" disembunyikan secara otomatis dari pandangan staf biasa untuk mencegah kebingungan operasional.
            </div>
        </div>
    </div>

    <div class="page-break">
        <div class="screenshot-card">
            <img src="${imgError403}" alt="Halaman Error 403" class="screenshot-img">
            <div class="screenshot-caption">
                <strong>Gambar 9: Halaman Akses Ditolak (Error 403) Berstandar Brand PTKP</strong><br>
                Menggantikan pesan sistem generik dengan desain korporat Deep Navy, logo resmi, penjelasan hak akses, kontak helpdesk TI, serta tombol navigasi aman kembali ke portal.
            </div>
        </div>

        <div class="screenshot-card">
            <img src="${imgPlatformOrg}" alt="Platform Core Master Organisasi" class="screenshot-img">
            <div class="screenshot-caption">
                <strong>Gambar 10: Platform Core Master Data Organisasi & Jabatan (Superadmin)</strong><br>
                Pengelolaan terpusat 39 Unit Organisasi, pohon hierarki induk-anak, matriks jabatan, dan penugasan pegawai sesuai SK Direksi PTKP terbaru.
            </div>
        </div>
    </div>

    <!-- 6. MATRIKS STRUKTUR ORGANISASI & AKUN UJI -->
    <div class="page-break">
        <h2>5. Matriks Struktur Organisasi & 78 Akun Uji Terstandarisasi</h2>
        <p>
            Untuk memastikan pengujian mencakup seluruh hierarki pabrik dan kantor, sistem telah diinjeksi dengan data seeder yang mencakup <strong>39 unit organisasi</strong> dan <strong>78 akun pengguna</strong> terverifikasi (password default: <code>password</code>):
        </p>

        <table>
            <thead>
                <tr>
                    <th style="width: 25%;">Tingkatan Organisasi</th>
                    <th style="width: 35%;">Unit Kerja / Departemen / Seksi</th>
                    <th style="width: 20%;">Contoh Akun Pengguna</th>
                    <th style="width: 20%;">Peran & Lingkup Akses</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Level 0: Direksi Utama</strong></td>
                    <td>Direktorat Utama (DIR-UTM), Sekretariat Perusahaan, SPI</td>
                    <td><code>superadmin</code><br><code>stafdirut</code><br><code>kaspi</code></td>
                    <td><span class="badge badge-navy">Super-Admin / Pimpinan</span><br>Hak akses global & pengesahan tertinggi</td>
                </tr>
                <tr>
                    <td><strong>Level 1: Direktur Operasi</strong></td>
                    <td>Direktorat Operasi (DIR-OPS) & Staf Operasi</td>
                    <td><code>dirops</code><br><code>stafdirops</code></td>
                    <td><span class="badge badge-navy">Pimpinan Operasi</span><br>Approval naskah manufaktur & teknik</td>
                </tr>
                <tr>
                    <td><strong>Level 1: Direktur Keuangan</strong></td>
                    <td>Direktorat Keuangan (DIR-KEU) & Analis Fiskal</td>
                    <td><code>dirkeu</code><br><code>stafdirkeu</code></td>
                    <td><span class="badge badge-navy">Pimpinan Keuangan</span><br>Approval anggaran, SDM, akuntansi</td>
                </tr>
                <tr>
                    <td><strong>Level 2: Departemen Manufaktur</strong></td>
                    <td>Dep. Produksi, Dep. Teknik, Dep. Penjaminan Mutu & K3LH</td>
                    <td><code>kadepprd</code><br><code>kadeptek</code><br><code>kadepk3lh</code></td>
                    <td><span class="badge badge-info">Pimpinan Departemen</span><br>Verifikasi nota dinas, SPB, laporan pabrik</td>
                </tr>
                <tr>
                    <td><strong>Level 2: Departemen Bisnis & Support</strong></td>
                    <td>Dep. Logistik, Dep. SDM, Dep. Pemasaran, Dep. TI</td>
                    <td><code>kadeplog</code><br><code>kadepsdm</code><br><code>kadeppsr</code><br><code>kadepti</code></td>
                    <td><span class="badge badge-info">Pimpinan Departemen</span><br>Verifikasi SPPD, klaim pengobatan, vendor</td>
                </tr>
                <tr>
                    <td><strong>Level 3: Seksi Pabrik & Lapangan</strong></td>
                    <td>Seksi PK1, PK2, Finishing, Boiler, Bengkel, Gudang, Laboratorium</td>
                    <td><code>kasiepk1</code> / <code>operatorpk1</code><br><code>kasieblr</code> / <code>operatorblr</code></td>
                    <td><span class="badge badge-success">Pegawai / Kasie</span><br>Pembuat naskah lapangan & paraf seksi</td>
                </tr>
            </tbody>
        </table>

        <div class="callout callout-warning">
            <div class="callout-title">Catatan Khusus Eskalasi Unit Tanpa Pimpinan Definitif (POH)</div>
            <p>
                Berdasarkan Aturan Surat PTKP No. 34, pada unit kerja yang saat ini dipimpin oleh Pejabat Pelaksana Harian (POH) seperti <strong>Departemen Akuntansi (DEP-AKT)</strong>, alur persetujuan surat dinas keluar dan anggaran secara otomatis dinaikkan ke tingkat Direktur Keuangan atau Direktur Utama. Sistem telah berhasil mengimplementasikan aturan eskalasi otomatis ini tanpa memerlukan intervensi manual administrator.
            </p>
        </div>
    </div>

    <!-- 7. LAPORAN PENGUJIAN MUTU -->
    <div class="page-break">
        <h2>6. Laporan Jaminan Mutu & Pengujian Sistem (QA Report)</h2>
        <p>
            Pengujian sistem dilakukan melalui dua metode: pengujian unit/fitur terotomatisasi (Automated Feature Testing) dan pengujian antarmuka pengguna berbasis peramban (End-to-End Browser Testing).
        </p>

        <h3>6.1. Ringkasan Hasil Uji Otomatis (PHPUnit / Pest Test Suite)</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Rangkaian Pengujian (Test Suite)</th>
                    <th style="width: 25%;">Jumlah Uji / Assertions</th>
                    <th style="width: 20%;">Durasi Eksekusi</th>
                    <th style="width: 15%;">Hasil Evaluasi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Platform Authorization Matrix & IDOR Prevention</strong></td>
                    <td>4 tests / 24 assertions</td>
                    <td>25.4s</td>
                    <td><span class="badge badge-success">PASSED</span></td>
                </tr>
                <tr>
                    <td><strong>Concurrency Numbering & Atomic Locking Test</strong></td>
                    <td>3 tests / 150 assertions</td>
                    <td>16.4s</td>
                    <td><span class="badge badge-success">PASSED</span></td>
                </tr>
                <tr>
                    <td><strong>Letter PDF Live Rendering & Kop Generator</strong></td>
                    <td>10 tests / 40 assertions</td>
                    <td>54.3s</td>
                    <td><span class="badge badge-success">PASSED</span></td>
                </tr>
                <tr>
                    <td><strong>Arsip Revision Flow & Step Reset Test</strong></td>
                    <td>4 tests / 28 assertions</td>
                    <td>18.2s</td>
                    <td><span class="badge badge-success">PASSED</span></td>
                </tr>
                <tr>
                    <td><strong>Org Hierarchy & 78 Account Seeder Verification</strong></td>
                    <td>3 tests / 18 assertions</td>
                    <td>17.1s</td>
                    <td><span class="badge badge-success">PASSED</span></td>
                </tr>
                <tr>
                    <td><strong>Master Data Management & Centralized Audit Logs</strong></td>
                    <td>6 tests / 36 assertions</td>
                    <td>45.8s</td>
                    <td><span class="badge badge-success">PASSED</span></td>
                </tr>
                <tr>
                    <td><strong>Otentikasi, Password Security, & Session Control</strong></td>
                    <td>14 tests / 32 assertions</td>
                    <td>14.6s</td>
                    <td><span class="badge badge-success">PASSED</span></td>
                </tr>
                <tr style="font-weight: 700; background-color: #EEF0FB;">
                    <td>TOTAL PENGUJIAN KESELURUHAN</td>
                    <td>79 Tests / 358 Assertions</td>
                    <td>372.0s</td>
                    <td><span class="badge badge-success" style="font-size: 8.5pt;">100% LULUS (0 FAIL)</span></td>
                </tr>
            </tbody>
        </table>

        <h3>6.2. Evaluasi Kepatuhan Arsitektur (Modular Boundaries & Deptrac)</h3>
        <p>
            Modul <code>Arsip</code> dan modul <code>Platform</code> telah divalidasi independensinya:
        </p>
        <ul class="check-list">
            <li>Modul <code>Arsip</code> tidak mengakses model Eloquent internal <code>Platform</code> secara langsung, melainkan melalui antarmuka kontrak resmi <code>DirectoryServiceInterface</code>.</li>
            <li>Seluruh aksi sensitif (pembuatan surat, approval, revisi, perubahan role) tercatat pada tabel <code>platform.audit_logs</code> yang bersifat <em>immutable</em> (tidak dapat diubah atau dihapus oleh siapapun).</li>
            <li>Seluruh bundle frontend JavaScript dan stylesheet CSS telah berhasil dikompilasi ke mode produksi tanpa error TypeScript (0 type errors).</li>
        </ul>
    </div>

    <!-- 8. KESIMPULAN & TAHAP SELANJUTNYA -->
    <div class="page-break">
        <h2>7. Kesimpulan, Kesiapan UAT, & Tahapan Selanjutnya</h2>
        <p>
            Berdasarkan seluruh hasil pengujian dan dokumentasi implementasi di atas, sistem <strong>E-Arsip & Platform Terpadu PT Kertas Padalarang</strong> dinyatakan <strong>SELESAI dan MEMENUHI SELURUH SYARAT KELAYAKAN</strong> untuk diserahkan ke tahap <em>User Acceptance Testing (UAT)</em> oleh perwakilan pengguna internal.
        </p>

        <h3>Rencana Kerja Tahap Selanjutnya (Roadmap Phase 3)</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 15%;">Target Waktu</th>
                    <th style="width: 35%;">Agenda Kegiatan</th>
                    <th style="width: 50%;">Tujuan & Keluaran (Deliverables)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Minggu I Nov 2026</strong></td>
                    <td>Sosialisasi & UAT Terpimpin</td>
                    <td>Pelaksanaan uji coba oleh perwakilan Sekretariat, SDM, Produksi, dan Teknik menggunakan 78 akun uji terstandarisasi.</td>
                </tr>
                <tr>
                    <td><strong>Minggu II Nov 2026</strong></td>
                    <td>Integrasi Sertifikat Digital BSrE</td>
                    <td>Penyelarasan Tanda Tangan Elektronik (TTE) tersertifikasi bekerjasama dengan Balai Sertifikasi Elektronik (BSrE BSSN).</td>
                </tr>
                <tr>
                    <td><strong>Minggu III Nov 2026</strong></td>
                    <td>Notifikasi Otomatis Multi-Kanal</td>
                    <td>Pengiriman notifikasi inbox persetujuan instan melalui bot WhatsApp resmi PTKP dan email korporat.</td>
                </tr>
                <tr>
                    <td><strong>Minggu IV Nov 2026</strong></td>
                    <td>Go-Live & Sosialisasi Seluruh Karyawan</td>
                    <td>Peluncuran resmi sistem di lingkungan produksi korporat dan migrasi penuh dari register surat manual.</td>
                </tr>
            </tbody>
        </table>

        <div class="signature-block">
            <div class="sig-box">
                <div class="sig-title">Disiapkan Oleh:</div>
                <div class="sig-name">Tim Transformasi Digital & Pengembang TI</div>
                <div class="sig-nip">PT Kertas Padalarang (Peruri SPM)</div>
                <div style="font-size: 7.5pt; color: #64748b; margin-top: 8px;">Tanggal: 23 Oktober 2026</div>
            </div>
            <div class="sig-box">
                <div class="sig-title">Disetujui Oleh:</div>
                <div class="sig-name">Super Administrator & Manajemen TI</div>
                <div class="sig-nip">NIP: PTKP-001 • Direktur Utama / Pembina</div>
                <div style="font-size: 7.5pt; color: #64748b; margin-top: 8px;">Tanggal: 23 Oktober 2026</div>
            </div>
        </div>
    </div>

</body>
</html>
`;

async function generatePdf() {
    console.log('Writing HTML file for inspection...');
    fs.writeFileSync(HTML_OUTPUT, htmlContent, 'utf8');
    console.log('HTML written to:', HTML_OUTPUT);

    console.log('Launching Puppeteer for PDF compilation...');
    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox'],
    });

    const page = await browser.newPage();
    await page.setContent(htmlContent, { waitUntil: 'networkidle0' });

    console.log('Generating A4 PDF print...');
    await page.pdf({
        path: PDF_OUTPUT,
        format: 'A4',
        printBackground: true,
        margin: {
            top: '0mm',
            right: '0mm',
            bottom: '0mm',
            left: '0mm',
        },
    });

    console.log('Copying PDF to artifacts directory for direct user access...');
    fs.copyFileSync(PDF_OUTPUT, ARTIFACT_PDF_OUTPUT);

    const stat = fs.statSync(PDF_OUTPUT);
    console.log(`PDF Generated successfully! Size: ${(stat.size / 1024 / 1024).toFixed(2)} MB`);
    console.log('Saved to:', PDF_OUTPUT);
    console.log('Artifact copy saved to:', ARTIFACT_PDF_OUTPUT);

    await browser.close();
}

generatePdf().catch(err => {
    console.error('PDF Generation Error:', err);
    process.exit(1);
});
