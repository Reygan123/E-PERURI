const fs = require('fs');
const path = require('path');
const puppeteer = require('puppeteer');

// Direktori berkas
const baseDir = __dirname;
const screenshotsDir = path.join(baseDir, 'screenshots');
const outputPdfPath = path.join(baseDir, 'Buku_Panduan_dan_Laporan_Progress_Jobdesk_PTKP.pdf');
const outputMdPath = path.join(baseDir, 'Buku_Panduan_dan_Laporan_Progress_Jobdesk_PTKP.md');

// Helper membaca gambar sebagai Base64
function getBase64Image(filename) {
    const filePath = path.join(screenshotsDir, filename);
    if (!fs.existsSync(filePath)) {
        console.warn(`File tidak ditemukan: ${filePath}`);
        return '';
    }
    const bitmap = fs.readFileSync(filePath);
    return `data:image/png;base64,${bitmap.toString('base64')}`;
}

// Muat seluruh screenshot
console.log('Memuat data tangkapan layar (screenshots)...');
const img01 = getBase64Image('01_menu_jobdesk_dashboard.png');
const img02 = getBase64Image('02_katalog_direktori_jabatan.png');
const img03 = getBase64Image('03_formulir_tab1_identitas.png');
const img04 = getBase64Image('04_formulir_tab2_tugas_pokok.png');
const img05 = getBase64Image('05_formulir_tab3_wewenang.png');
const img06 = getBase64Image('06_formulir_tab4_hubungan_kerja.png');
const img07 = getBase64Image('07_formulir_tab5_kompetensi_spesifikasi.png');
const img08 = getBase64Image('08_dokumen_seksi_identitas_bagan.png');
const img09 = getBase64Image('09_dokumen_seksi_tugas_wewenang.png');
const img10 = getBase64Image('10_dokumen_persetujuan_tte_bsre.png');
const img11 = getBase64Image('11_lembar_cetak_a4_resmi.png');

console.log('Menyusun naskah HTML untuk pembuatan PDF...');

const htmlContent = `<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Buku Panduan & Laporan Progres Aplikasi Jobdesk Digital PTKP</title>
<style>
    @page {
        size: A4;
        margin: 20mm 15mm 20mm 15mm;
        @bottom-right {
            content: "Halaman " counter(page);
            font-size: 9pt;
            color: #64748b;
        }
    }
    
    * {
        box-sizing: border-box;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    body {
        font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
        color: #1e293b;
        line-height: 1.6;
        font-size: 11pt;
        margin: 0;
        padding: 0;
        background-color: #ffffff;
    }

    h1, h2, h3, h4 {
        color: #141A5C;
        font-weight: 700;
        margin-top: 0;
    }

    h1 {
        font-size: 20pt;
        line-height: 1.3;
        border-bottom: 3px solid #20288E;
        padding-bottom: 8px;
        margin-bottom: 16px;
    }

    h2 {
        font-size: 15pt;
        border-left: 5px solid #20288E;
        padding-left: 12px;
        margin-top: 24px;
        margin-bottom: 12px;
        background-color: #f8fafc;
        padding-top: 4px;
        padding-bottom: 4px;
    }

    h3 {
        font-size: 12.5pt;
        margin-top: 18px;
        margin-bottom: 8px;
        color: #20288E;
    }

    p {
        margin-top: 0;
        margin-bottom: 12px;
        text-align: justify;
    }

    .page-break {
        page-break-before: always;
    }

    /* Sampul Depan */
    .cover-page {
        text-align: center;
        padding: 40px 20px 20px 20px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .cover-header {
        border-bottom: 2px solid #141A5C;
        padding-bottom: 20px;
        margin-bottom: 40px;
    }

    .company-name {
        font-size: 16pt;
        font-weight: 800;
        letter-spacing: 2px;
        color: #141A5C;
        margin-bottom: 4px;
    }

    .company-sub {
        font-size: 11pt;
        font-weight: 600;
        color: #475569;
        letter-spacing: 1px;
    }

    .cover-title-box {
        margin: 50px 0;
        padding: 30px;
        background: linear-gradient(135deg, #141A5C 0%, #20288E 100%);
        color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(20, 26, 92, 0.2);
    }

    .cover-title-box h1 {
        color: #ffffff;
        border-bottom: 2px solid #93c5fd;
        font-size: 24pt;
        margin-bottom: 15px;
    }

    .cover-title-box .subtitle {
        font-size: 13pt;
        color: #e0e7ff;
        font-weight: 400;
        line-height: 1.5;
    }

    .cover-meta {
        background-color: #f1f5f9;
        padding: 20px;
        border-radius: 8px;
        display: inline-block;
        margin: 0 auto;
        text-align: left;
        border-left: 4px solid #141A5C;
        font-size: 10.5pt;
    }

    .cover-meta table td {
        padding: 4px 10px;
    }

    /* Kotak Info Penting & Tips */
    .note-box {
        background-color: #eff6ff;
        border-left: 4px solid #3b82f6;
        padding: 14px 18px;
        border-radius: 0 8px 8px 0;
        margin: 16px 0;
        font-size: 10.5pt;
    }

    .note-box strong {
        color: #1d4ed8;
    }

    .tip-box {
        background-color: #f0fdf4;
        border-left: 4px solid #22c55e;
        padding: 14px 18px;
        border-radius: 0 8px 8px 0;
        margin: 16px 0;
        font-size: 10.5pt;
    }

    .tip-box strong {
        color: #15803d;
    }

    /* Screenshot Container */
    .screenshot-card {
        margin: 18px 0 24px 0;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }

    .screenshot-card img {
        width: 100%;
        display: block;
        max-height: 380px;
        object-fit: contain;
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .screenshot-caption {
        padding: 10px 14px;
        background-color: #f8fafc;
        font-size: 9.5pt;
        color: #475569;
        font-weight: 600;
        border-left: 4px solid #20288E;
    }

    /* Tabel Rapi */
    table.data-table {
        width: 100%;
        border-collapse: collapse;
        margin: 16px 0;
        font-size: 10pt;
    }

    table.data-table th {
        background-color: #141A5C;
        color: #ffffff;
        text-align: left;
        padding: 10px 12px;
        font-weight: 600;
    }

    table.data-table td {
        padding: 9px 12px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: top;
    }

    table.data-table tr:nth-child(even) td {
        background-color: #f8fafc;
    }

    /* Langkah Berurutan */
    .step-badge {
        display: inline-block;
        background-color: #20288E;
        color: #ffffff;
        font-weight: bold;
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 9.5pt;
        margin-right: 6px;
    }

    .btn-badge {
        display: inline-block;
        background-color: #e2e8f0;
        color: #0f172a;
        font-weight: bold;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 9.5pt;
        border: 1px solid #cbd5e1;
    }

    .btn-navy {
        background-color: #141A5C;
        color: #ffffff;
        border: none;
    }

    .btn-green {
        background-color: #15803d;
        color: #ffffff;
        border: none;
    }

    .btn-yellow {
        background-color: #b45309;
        color: #ffffff;
        border: none;
    }

    ul, ol {
        margin-top: 0;
        margin-bottom: 14px;
        padding-left: 24px;
    }

    li {
        margin-bottom: 6px;
    }
</style>
</head>
<body>

<!-- ==================== HALAMAN 1: SAMPUL DEPAN ==================== -->
<div class="cover-page">
    <div class="cover-header">
        <div class="company-name">PT KERTAS PADALARANG</div>
        <div class="company-sub">PERURI SECURITY PAPER MILL — UNIT USAHA BAHAN BAKU UANG KERTAS & DOKUMEN SEKURITI</div>
    </div>

    <div class="cover-title-box">
        <h1>BUKU PANDUAN PENGGUNA<br>& LAPORAN PROGRES</h1>
        <div class="subtitle">
            Sistem Informasi Jobdesk Digital (Portal Jobdesk Korporat)<br>
            Pedoman Praktis, Ramah, dan Mudah Dipahami untuk Bapak/Ibu Pimpinan & Seluruh Karyawan
        </div>
    </div>

    <div class="cover-meta">
        <table style="border: none;">
            <tr>
                <td style="font-weight: bold; color: #141A5C; width: 140px;">Nomor Dokumen</td>
                <td>: <strong>MAN-SDM/PTKP/JOBDESK/2026/001</strong></td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #141A5C;">Versi Sistem</td>
                <td>: <strong>v2.0.0 (Resmi Siap Operasional)</strong></td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #141A5C;">Tanggal Terbit</td>
                <td>: <strong>26 Oktober 2026</strong></td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #141A5C;">Penyusun</td>
                <td>: <strong>Tim Pengembangan Sistem Informasi SDM & IT</strong></td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #141A5C;">Sasaran Pembaca</td>
                <td>: <strong>Dewan Direksi, Kepala Departemen (Kadep), Kepala Seksi (Kasi), dan Staf</strong></td>
            </tr>
        </table>
    </div>

    <div style="font-size: 9.5pt; color: #64748b; margin-top: 40px;">
        © 2026 PT Kertas Padalarang — Seluruh Hak Cipta Dilindungi Undang-Undang.
    </div>
</div>

<div class="page-break"></div>

<!-- ==================== HALAMAN 2: KATA PENGANTAR ==================== -->
<h1>KATA PENGANTAR</h1>

<p><strong>Assalamu’alaikum Warahmatullahi Wabarakatuh,</strong><br>
<strong>Salam Sejahtera bagi Kita Semua,</strong></p>

<p>
Puji dan syukur senantiasa kita panjatkan ke hadirat Tuhan Yang Maha Esa atas terselesaikannya pembangunan <strong>Sistem Informasi Jobdesk Digital (Portal Jobdesk)</strong> di lingkungan <strong>PT Kertas Padalarang (Peruri Security Paper Mill)</strong>.
</p>

<p>
Buku panduan ini kami susun secara khusus dengan pendekatan bahasa yang santun, sederhana, runtut, dan mudah dipahami oleh seluruh kalangan pimpinan, terutama Bapak dan Ibu Pimpinan senior yang menghendaki kejelasan proses kerja tanpa dipusingkan oleh istilah-istilah teknis komputer yang rumit. 
</p>

<p>
Selama ini, berkas uraian jabatan (*Job Description*) sering kali tersimpan dalam bentuk berkas kertas di lemari arsip atau berkas Word/Excel yang tersebar di komputer masing-masing. Hal tersebut kerap menimbulkan kendala ketika sewaktu-waktu dibutuhkan, formatnya tidak seragam, atau sulit memastikan siapa pejabat yang berwenang menandatangani.
</p>

<p>
Hadirnya Portal Jobdesk Digital ini bertujuan untuk memudahkan Bapak dan Ibu sekalian. Segala data jabatan kini tertata rapi dalam satu "buku besar digital" yang aman, transparan, memiliki alur persetujuan yang sah dengan tanda tangan elektronik, serta dapat dicetak kapan saja ke kertas standar A4 dengan satu kali klik.
</p>

<div class="note-box">
    <strong>Pesan Kami untuk Bapak & Ibu Pimpinan:</strong><br>
    Sistem ini dirancang untuk meringankan dan mempermudah pekerjaan harian Bapak/Ibu. Segala tombol telah diberi petunjuk yang jelas, ukuran tulisan nyaman di mata, dan tidak perlu khawatir melakukan kesalahan karena setiap langkah dilengkapi panduan konfirmasi yang aman.
</div>

<p>
Kami menyampaikan terima kasih dan apresiasi yang setinggi-tingginya kepada Dewan Direksi, Departemen Sumber Daya Manusia (SDM), dan seluruh pihak yang telah memberikan arahan, masukan, dan dukungan penuh hingga portal ini selesai 100% dan siap digunakan bersama.
</p>

<p style="margin-top: 30px;">
Padalarang, 26 Oktober 2026<br>
<strong>Hormat kami,</strong><br><br><br>
<strong>Tim Pengembang Sistem Informasi SDM & IT</strong><br>
PT Kertas Padalarang
</p>

<div class="page-break"></div>

<!-- ==================== HALAMAN 3: BAB 1 & BAB 2 ==================== -->
<h1>BAB 1. MENGENAL PORTAL JOBDESK DIGITAL</h1>

<h2>1.1. Apa Itu Uraian Jabatan (Jobdesk)?</h2>
<p>
Secara sederhana, <strong>Jobdesk</strong> (*Job Description* / Uraian Jabatan) adalah <strong>surat pedoman resmi</strong> yang menjelaskan:
</p>
<ul>
    <li><strong>Siapa nama jabatannya</strong> dan di bagian unit kerja mana jabatan tersebut berada.</li>
    <li><strong>Siapa atasan langsungnya</strong> yang berhak memberikan tugas dan melakukan pembinaan.</li>
    <li><strong>Apa saja tugas sehari-hari</strong> yang wajib dikerjakan oleh pejabat atau karyawan tersebut.</li>
    <li><strong>Apa saja tanggung jawab dan batas hak wewenangnya</strong> agar tidak terjadi saling lempar tanggung jawab atau melangkahi wewenang bagian lain.</li>
    <li><strong>Hubungan kerja dengan pihak mana saja</strong> (misalnya hubungan harian dengan bagian Gudang, Logistik, atau mitra Peruri Pusat).</li>
    <li><strong>Kualifikasi yang dibutuhkan</strong>, seperti syarat pendidikan, pengalaman, dan keahlian teknis.</li>
</ul>

<h2>1.2. Mengapa Dibuat Sistem Digital?</h2>
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 20%;">Kondisi Lama (Manual)</th>
            <th style="width: 40%;">Kendala yang Sering Dihadapi</th>
            <th style="width: 40%;">Solusi Baru dengan Portal Jobdesk</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Kertas Fisik & File Word Tersebar</strong></td>
            <td>Berkas sering terselip di lemari arsip, rusak, atau saat dibutuhkan mencari di komputer lama tidak ketemu.</td>
            <td><strong>Semua Tersimpan Terpusat:</strong> Cukup buka website perusahaan, semua jabatan dari Direksi hingga Pelaksana terlihat rapi seketika.</td>
        </tr>
        <tr>
            <td><strong>Format Berbeda-beda</strong></td>
            <td>Tiap departemen memiliki bentuk tabel sendiri-sendiri sehingga tidak seragam saat diaudit.</td>
            <td><strong>Format Resmi Standar SK Direksi:</strong> Semua menggunakan 10 seksi korporat yang seragam dan elegan sesuai standar BUMN.</td>
        </tr>
        <tr>
            <td><strong>Tanda Tangan Menunggu Fisik</strong></td>
            <td>Meminta paraf atasan harus mencetak kertas, menitipkan map ke meja, dan sering tertunda karena kesibukan rapat pimpinan.</td>
            <td><strong>Persetujuan Digital (1 Klik):</strong> Pimpinan cukup menekan tombol 'Setujui' di layar komputer. Dokumen otomatis sah dengan stempel QR Code resmi.</td>
        </tr>
    </tbody>
</table>

<div class="page-break"></div>

<!-- ==================== HALAMAN 4: BAB 3 FITUR UTAMA ==================== -->
<h1>BAB 2. FITUR-FITUR UTAMA YANG MEMUDAHKAN</h1>

<p>
Aplikasi ini dilengkapi 5 fitur utama yang dirancang sangat mudah digunakan, bahkan bagi pengguna yang belum terbiasa dengan aplikasi komputer canggih:
</p>

<h3>1. Katalog Perpustakaan Jabatan (Pencarian Sangat Cepat)</h3>
<p>
Bapak/Ibu dapat mencari nama jabatan apa saja cukup dengan mengetikkan sepatah kata di kolom pencarian (misalnya: <em>"Produksi"</em>, <em>"Operator"</em>, atau <em>"Logistik"</em>). Dalam hitungan detik, seluruh data langsung tersaji lengkap dengan statusnya (apakah sudah sah, masih draf, atau sedang ditinjau atasan).
</p>

<h3>2. Formulir Pintar 5 Langkah (Wizard Form)</h3>
<p>
Penyusunan jobdesk tidak lagi membuat pusing karena dibagi menjadi 5 langkah sederhana (*tab*):
</p>
<ol>
    <li><strong>Identitas & Posisi:</strong> Memilih jabatan, nama unit, dan atasan langsung.</li>
    <li><strong>Tugas Pokok:</strong> Menambahkan rincian tugas pokok dengan tombol <span class="btn-badge">+ Tambah Butir Tugas</span>.</li>
    <li><strong>Tanggung Jawab & Wewenang:</strong> Menentukan batas hak keputusan karyawan.</li>
    <li><strong>Hubungan Kerja:</strong> Mengisi koordinasi dengan bagian lain dengan rincian <em>"Dalam Hal"</em> (mengadopsi standar modern Telkom University).</li>
    <li><strong>Standar & Kompetensi:</strong> Menentukan tingkat kecakapan (Level 1 sampai 5) dan syarat pendidikan minimum.</li>
</ol>

<h3>3. Bagan Struktur Organisasi Otomatis</h3>
<p>
Bapak/Ibu tidak perlu lagi repot menggambar kotak-kotak struktur organisasi di Microsoft Word. Begitu nama atasan dan jabatan dipilih, sistem akan langsung <strong>menggambar bagan silsilah organisasi secara otomatis</strong> yang sangat rapi dan enak dilihat.
</p>

<h3>4. Meja Persetujuan Digital (Alur Sah Berjenjang)</h3>
<p>
Sistem memfasilitasi alur kerja berjenjang:
</p>
<ul>
    <li><strong>Penyusun (Maker):</strong> Staf menyusun draf rincian uraian jabatan.</li>
    <li><strong>Pemeriksa (Checker - Kepala Seksi/Kasi):</strong> Memeriksa kebenaran tugas operasional stafnya.</li>
    <li><strong>Pengesah (Approver - Kepala Departemen/Kadep & SDM):</strong> Memberikan persetujuan akhir yang mengikat secara resmi.</li>
</ul>

<h3>5. Cetak Lembar Dokumen A4 Resmi Ber-QR Code TTE BSrE</h3>
<p>
Bila sewaktu-waktu dibutuhkan lembar fisik untuk lampiran SK, rapat, atau keperluan audit BPK/ISO, sistem menyediakan <strong>tombol Cetak Langsung</strong> dengan kop resmi Peruri Security Paper Mill, garis margin 20mm presisi, dan kotak QR Code TTE yang sah.
</p>

<div class="page-break"></div>

<!-- ==================== HALAMAN 5: BAB 4 PANDUAN LANGKAH DEMI LANGKAH ==================== -->
<h1>BAB 3. PANDUAN PENGGUNAAN BERGAMBAR (TATA CARA PRAKTIS)</h1>

<p>
Berikut ini adalah panduan langkah demi langkah cara menggunakan aplikasi, lengkap dengan gambar layar untuk memandu Bapak/Ibu:
</p>

<h2>Langkah 1: Masuk ke Menu Jobdesk dari Halaman Utama (Dashboard)</h2>
<p>
Setelah Bapak/Ibu membuka alamat web sistem PTKP, pada halaman beranda utama akan tampil kartu menu aplikasi. Arahkan kursor dan klik pada kartu bertuliskan <strong>"Portal Jobdesk & Direktori Jabatan"</strong> (kartu berwarna biru dongker dengan ikon dokumen dan lencana SDM).
</p>

<div class="screenshot-card">
    <img src="${img01}" alt="Menu Dashboard Portal Jobdesk">
    <div class="screenshot-caption">Gambar 1. Menu Portal Jobdesk pada Dashboard Utama Aplikasi PTKP</div>
</div>

<div class="tip-box">
    <strong>Petunjuk untuk Bapak/Ibu:</strong><br>
    Bila Bapak/Ibu berada di halaman lain, menu ini juga selalu tersedia di bilah samping (*Sidebar*) sebelah kiri pada kelompok menu <strong>"SUMBER DAYA MANUSIA" &gt; "Jobdesk"</strong>.
</div>

<div class="page-break"></div>

<!-- ==================== HALAMAN 6: LANGKAH 2 KATALOG ==================== -->
<h2>Langkah 2: Menjelajahi Buku Daftar Jabatan (Katalog)</h2>
<p>
Halaman ini adalah "perpustakaan digital" yang menampilkan seluruh jabatan di perusahaan.
</p>

<div class="screenshot-card">
    <img src="${img02}" alt="Katalog Direktori Jabatan">
    <div class="screenshot-caption">Gambar 2. Tampilan Katalog Direktori Jabatan dengan Kartu Angka dan Filter</div>
</div>

<p><strong>Bagian-bagian penting pada layar ini:</strong></p>
<ol>
    <li><strong>4 Kartu Angka di Bagian Atas:</strong> Memberikan ringkasan cepat kepada pimpinan mengenai jumlah total jabatan, berapa yang sudah resmi disahkan, berapa yang sedang menunggu persetujuan, dan berapa yang masih draf.</li>
    <li><strong>Kotak Pencarian:</strong> Ketikkan nama jabatan yang ingin dicari (misal: <em>"Operator"</em> atau <em>"Kepala Seksi"</em>).</li>
    <li><strong>Pilihan Status & Unit Kerja:</strong> Bapak/Ibu dapat memilih untuk melihat jabatan di departemen tertentu saja.</li>
    <li><strong>Tombol Biru '+ Tambah Jobdesk':</strong> Digunakan untuk membuat uraian jabatan baru.</li>
    <li><strong>Tombol Mata (Lihat Detail):</strong> Klik ikon mata di sebelah kanan baris untuk membuka dokumen lengkap jabatan tersebut.</li>
</ol>

<div class="page-break"></div>

<!-- ==================== HALAMAN 7: LANGKAH 3 WIZARD TAB 1 & 2 ==================== -->
<h2>Langkah 3: Mengisi atau Mengubah Uraian Jabatan (Formulir Wizard)</h2>
<p>
Saat menekan tombol <strong>"+ Tambah Jobdesk"</strong> atau tombol <strong>"Edit"</strong>, Bapak/Ibu akan dipandu melewati formulir yang sangat teratur:
</p>

<h3>Bagian 1: Identitas & Posisi (Tab 1)</h3>
<p>
Pada bagian ini, pilih nama jabatan dari daftar master posisi yang sudah terdaftar, unit organisasinya, atasan langsungnya, serta ketikkan ringkasan tujuan umum jabatan dalam 1–2 paragraf.
</p>

<div class="screenshot-card">
    <img src="${img03}" alt="Formulir Wizard Tab 1 Identitas">
    <div class="screenshot-caption">Gambar 3. Pengisian Identitas Jabatan, Atasan, dan Ringkasan Tugas</div>
</div>

<h3>Bagian 2: Tugas Pokok & Prosedur (Tab 2)</h3>
<p>
Ketikkan apa saja pekerjaan utama yang dilakukan. Cukup klik tombol biru <strong>"+ Tambah Butir Tugas"</strong> untuk menambah baris baru. Setiap tugas dapat diisi nama kegiatannya dan rincian langkah kerjanya.
</p>

<div class="screenshot-card">
    <img src="${img04}" alt="Formulir Wizard Tab 2 Tugas Pokok">
    <div class="screenshot-caption">Gambar 4. Pengisian Daftar Tugas Pokok Secara Runtut dan Teratur</div>
</div>

<div class="page-break"></div>

<!-- ==================== HALAMAN 8: LANGKAH 3 WIZARD TAB 3 & 4 ==================== -->
<h3>Bagian 3: Tanggung Jawab & Hak Wewenang (Tab 3)</h3>
<p>
Bagian ini sangat penting untuk mencegah keraguan staf di lapangan.
</p>
<ul>
    <li><strong>Tanggung Jawab:</strong> Hal-hal apa saja yang menjadi akuntabilitas pejabat tersebut (contoh: menjaga target output mesin, kepatuhan keselamatan kerja staf pelaksana).</li>
    <li><strong>Wewenang:</strong> Keputusan apa saja yang berhak diambil sendiri oleh pejabat tersebut tanpa perlu izin atasan (contoh: menegur staf yang tidak memakai APD, menghentikan mesin bila terjadi keadaan darurat).</li>
</ul>

<div class="screenshot-card">
    <img src="${img05}" alt="Formulir Wizard Tab 3 Tanggung Jawab dan Wewenang">
    <div class="screenshot-caption">Gambar 5. Pengisian Poin-Poin Tanggung Jawab dan Batas Hak Keputusan</div>
</div>

<h3>Bagian 4: Hubungan Kerja Internal & Eksternal (Tab 4)</h3>
<p>
Menjelaskan dengan siapa saja jabatan ini berkoordinasi dalam bekerja sehari-hari, lengkap dengan kolom <strong>"Dalam Hal"</strong> agar tujuan koordinasinya jelas dan tidak mengambang.
</p>

<div class="screenshot-card">
    <img src="${img06}" alt="Formulir Wizard Tab 4 Hubungan Kerja">
    <div class="screenshot-caption">Gambar 6. Matriks Hubungan Kerja Format Modern (Format Telkom University)</div>
</div>

<div class="page-break"></div>

<!-- ==================== HALAMAN 9: LANGKAH 3 WIZARD TAB 5 ==================== -->
<h3>Bagian 5: Kompetensi & Spesifikasi Jabatan (Tab 5)</h3>
<p>
Pada langkah terakhir ini, Bapak/Ibu dapat menentukan kualifikasi pegawai yang pas untuk menduduki jabatan tersebut:
</p>
<ul>
    <li><strong>Tingkat Kemahiran (PL 1 sampai 5):</strong> Cukup klik tombol angka 1 (Paham Teori Dasar), 2 (Mampu Menerapkan), 3 (Mampu Menganalisis), 4 (Mampu Mengevaluasi), atau 5 (Ahli/Inovator).</li>
    <li><strong>Pendidikan Minimum & Pengalaman:</strong> Misalnya S1 Teknik Kimia dengan pengalaman minimal 3 tahun di pabrik kertas.</li>
</ul>

<div class="screenshot-card">
    <img src="${img07}" alt="Formulir Wizard Tab 5 Kompetensi dan Spesifikasi">
    <div class="screenshot-caption">Gambar 7. Penentuan Level Keahlian (PL 1-5) dan Syarat Pendidikan Minimum</div>
</div>

<div class="tip-box">
    <strong>Cara Menyimpan:</strong><br>
    Setelah semua tab terisi, klik tombol biru tua <strong>"Simpan Draf Jabatan"</strong> di bagian bawah kanan. Sistem akan menyimpan berkas secara otomatis dan menampilkan halaman pratinjau dokumen.
</div>

<div class="page-break"></div>

<!-- ==================== HALAMAN 10: LANGKAH 4 PRATINJAU DOKUMEN ==================== -->
<h2>Langkah 4: Melihat Lembar Dokumen Resmi & Struktur Hierarki</h2>
<p>
Setelah disimpan, dokumen akan tampil dalam format resmi korporat 10 seksi standar PT Kertas Padalarang:
</p>

<div class="screenshot-card">
    <img src="${img08}" alt="Dokumen Resmi Seksi 1 dan Bagan Struktur">
    <div class="screenshot-caption">Gambar 8. Tampilan Lembar Dokumen Resmi Bagian Atas dan Bagan Silsilah Jabatan</div>
</div>

<p>
Perhatikan pada Gambar 8 di atas: sistem secara cerdas menampilkan <strong>Bagan Struktur Hierarki</strong>. Bapak/Ibu pimpinan dapat melihat langsung siapa atasan langsung dari posisi ini, dan jika memiliki staf bawahan, kotak bawahannya pun akan tergambar rapi secara otomatis.
</p>

<div class="screenshot-card">
    <img src="${img09}" alt="Dokumen Resmi Seksi Tugas dan Wewenang">
    <div class="screenshot-caption">Gambar 9. Tampilan Rincian Tugas Pokok, Tanggung Jawab, dan Wewenang</div>
</div>

<div class="page-break"></div>

<!-- ==================== HALAMAN 11: LANGKAH 5 PERSETUJUAN PIMPINAN ==================== -->
<h2>Langkah 5: Cara Bapak/Ibu Pimpinan Memberikan Persetujuan (Approve)</h2>
<p>
Inilah fitur yang paling banyak digunakan oleh Bapak dan Ibu Pimpinan (Kepala Seksi, Kepala Departemen, dan Pejabat SDM):
</p>

<div class="screenshot-card">
    <img src="${img10}" alt="Kolom Tanda Tangan Digital BSrE">
    <div class="screenshot-caption">Gambar 10. Matriks Persetujuan Berjenjang Resmi dengan Tanda Tangan Digital</div>
</div>

<p><strong>Panduan Sangat Mudah untuk Bapak/Ibu Pimpinan:</strong></p>
<ol>
    <li>
        <strong>Bila Bapak/Ibu Menyetujui Dokumen Ini:</strong><br>
        Klik tombol hijau bertuliskan <span class="btn-badge btn-green">Setujui Dokumen</span> di pojok kanan atas. Sistem akan meminta konfirmasi sekali lagi, lalu dokumen dinyatakan <strong>SAH (APPROVED)</strong>. Sistem otomatis menerbitkan <strong>Nomor Surat SOP Resmi</strong> dan <strong>Stempel Digital QR Code</strong>.
    </li>
    <li>
        <strong>Bila Bapak/Ibu Menghendaki Ada Perbaikan / Revisi:</strong><br>
        Klik tombol kuning bertuliskan <span class="btn-badge btn-yellow">Minta Revisi</span>. Akan muncul jendela dialog kecil. Bapak/Ibu cukup mengetikkan apa saja yang perlu diperbaiki (misalnya: <em>"Tolong tambahkan butir tugas pemeriksaan mesin shift malam"</em>), lalu klik simpan. Dokumen akan kembali ke staf penyusun untuk diperbaiki sesuai arahan Bapak/Ibu.
    </li>
</ol>

<div class="note-box">
    <strong>Apakah Dokumen Ini Sah Secara Hukum?</strong><br>
    <strong>Ya, Sangat Sah.</strong> Tanda tangan pada sistem ini telah terintegrasi dengan standar sertifikasi elektronik (BSrE Peruri). Setiap dokumen yang telah disetujui memiliki kode unik (Hash SHA-256) yang tidak dapat dipalsukan atau diubah-ubah oleh pihak manapun.
</div>

<div class="page-break"></div>

<!-- ==================== HALAMAN 12: LANGKAH 6 CETAK A4 ==================== -->
<h2>Langkah 6: Mencetak Dokumen ke Kertas A4 atau Menyimpan Jadi PDF</h2>
<p>
Bila sewaktu-waktu Bapak/Ibu membutuhkan lembar fisik untuk lampiran laporan atau rapat direksi:
</p>
<ol>
    <li>Klik tombol putih berbingkai bertuliskan <span class="btn-badge">Cetak Dokumen (A4)</span> di bagian atas halaman.</li>
    <li>Layar akan menampilkan lembar cetak resmi dengan kop surat <strong>PT KERTAS PADALARANG — PERURI SECURITY PAPER MILL</strong>, lengkap dengan tanda air (*watermark*) resmi dan kotak QR Code tanda tangan.</li>
    <li>Klik tombol melayang warna biru bertuliskan <span class="btn-badge btn-navy">Cetak / Unduh PDF</span> di pojok kanan bawah.</li>
    <li>Pilih printer yang terhubung ke komputer Bapak/Ibu, atau pilih <em>"Save as PDF"</em> bila ingin menyimpannya sebagai berkas di komputer atau handphone.</li>
</ol>

<div class="screenshot-card">
    <img src="${img11}" alt="Lembar Cetak Standar Kertas A4 Resmi">
    <div class="screenshot-caption">Gambar 11. Lembar Cetak Kertas A4 Standar ISO Margin 20mm Siap Cetak</div>
</div>

<div class="page-break"></div>

<!-- ==================== HALAMAN 13: BAB 5 LAPORAN PROGRES ==================== -->
<h1>BAB 4. LAPORAN KEMAJUAN PROGRES APLIKASI (PROGRESS REPORT)</h1>

<p>
Kami laporkan kepada Bapak/Ibu Direksi dan Pimpinan bahwa pembangunan Sistem Informasi Jobdesk Digital saat ini telah mencapai <strong>tahap penyelesaian akhir (100% Selesai & Lolos Uji)</strong>. Berikut rincian kemajuannya:
</p>

<h2>4.1. Tabel Status Pencapaian Fitur</h2>
<table class="data-table">
    <thead>
        <tr>
            <th style="width: 35%;">Bagian / Modul Aplikasi</th>
            <th style="width: 20%;">Status Kemajuan</th>
            <th style="width: 45%;">Keterangan & Hasil Uji</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Katalog Direktori Jabatan</strong></td>
            <td><strong style="color: #15803d;">100% Selesai</strong></td>
            <td>Pencarian cepat, filter unit kerja, filter status, dan kartu ringkasan jumlah jabatan berfungsi sempurna.</td>
        </tr>
        <tr>
            <td><strong>Studio Formulir Wizard (5 Tab)</strong></td>
            <td><strong style="color: #15803d;">100% Selesai</strong></td>
            <td>Input bertahap, penambahan butir tugas, tanggung jawab, wewenang, dan hubungan kerja bekerja lancar.</td>
        </tr>
        <tr>
            <td><strong>Pratinjau Dokumen 10 Seksi</strong></td>
            <td><strong style="color: #15803d;">100% Selesai</strong></td>
            <td>Layout resmi SK Direksi, bagan silsilah atasan/bawahan otomatis tergambar, tipografi nyaman dibaca.</td>
        </tr>
        <tr>
            <td><strong>Alur Persetujuan Bertingkat</strong></td>
            <td><strong style="color: #15803d;">100% Selesai</strong></td>
            <td>Alur Maker-Checker-Approver, fitur catatan penolakan revisi, dan pengesahan sah berstempel digital.</td>
        </tr>
        <tr>
            <td><strong>Lembar Cetak Kertas A4</strong></td>
            <td><strong style="color: #15803d;">100% Selesai</strong></td>
            <td>Presisi margin 20mm, kop resmi Peruri Security Paper Mill, dan QR Code verifikasi TTE siap cetak.</td>
        </tr>
        <tr>
            <td><strong>Keamanan Data & Otorisasi</strong></td>
            <td><strong style="color: #15803d;">100% Selesai</strong></td>
            <td>Bebas celah kebocoran antar unit (bebas IDOR), terproteksi login aman, data tersimpan terenkripsi.</td>
        </tr>
    </tbody>
</table>

<h2>4.2. Sertifikasi Pengujian Mutu (QA/QC Summary)</h2>
<p>
Sebelum diserahkan kepada Bapak/Ibu, sistem ini telah melalui rangkaian uji ketahanan otomatis yang ketat:
</p>
<ul>
    <li><strong>Uji Fungsional Otomatis (Pest Testing):</strong> 99 dari 99 skenario pengujian dinyatakan <strong>LULUS (100% Passed)</strong> tanpa ada satu pun yang gagal.</li>
    <li><strong>Uji Beban & Kebersihan Kode (Larastan Level 5):</strong> 0 kesalahan (*Zero Errors*) di seluruh 132 berkas sistem.</li>
    <li><strong>Uji Kerapian Tampilan di Browser:</strong> Telah diuji menggunakan peramban modern (Google Chrome, Microsoft Edge) dengan hasil tampilan yang tajam dan tidak berantakan.</li>
</ul>

<div class="tip-box">
    <strong>Kesimpulan Tim Mutu:</strong><br>
    Sistem Informasi Jobdesk Digital telah dinyatakan <strong>SANGAT LAYAK OPERASIONAL (READY FOR PRODUCTION)</strong> dan siap digunakan secara resmi oleh seluruh jajaran manajemen PT Kertas Padalarang.
</div>

<div class="page-break"></div>

<!-- ==================== HALAMAN 14: BAB 6 TANYA JAWAB ==================== -->
<h1>BAB 5. TANYA JAWAB UMUM (FAQ RAMAH PIMPINAN)</h1>

<p>
Berikut adalah rangkuman jawaban atas pertanyaan-pertanyaan yang kerap ditanyakan oleh Bapak/Ibu Pimpinan:
</p>

<h3>1. "Apakah saya yang sudah senior dan tidak terlalu mahir komputer bisa menggunakan sistem ini?"</h3>
<p>
<strong>Sangat bisa dan sangat mudah.</strong> Seluruh sistem menggunakan Bahasa Indonesia yang santun dan akrab. Setiap tombol diberi warna dan tulisan yang jelas (misalnya: Hijau untuk Setujui, Kuning untuk Minta Revisi, Biru untuk Cetak). Bapak/Ibu tidak perlu mengetik kode-kode rumit.
</p>

<h3>2. "Bagaimana bila saya salah menekan tombol atau salah mengisi data?"</h3>
<p>
<strong>Jangan khawatir.</strong> Sebelum data tersimpan atau disahkan, sistem selalu menampilkan jendela konfirmasi: <em>"Apakah Bapak/Ibu yakin?"</em>. Selain itu, selama dokumen masih berstatus draf, data dapat diubah kapan saja tanpa merusak arsip yang ada.
</p>

<h3>3. "Apakah pejabat dari bagian lain bisa mengubah isi jobdesk di departemen saya?"</h3>
<p>
<strong>Sama sekali tidak bisa.</strong> Sistem kami dilengkapi keamanan berlapis (*Scope Unit Isolation*). Pejabat dari Departemen Produksi hanya berhak menyusun dan menyetujui jabatan di lingkungan Produksi. Departemen lain hanya dapat membaca bila diberikan hak akses baca.
</p>

<h3>4. "Bila komputer kantor sedang mati atau saya sedang di luar dinas, apakah saya bisa memeriksa jobdesk dari handphone?"</h3>
<p>
<strong>Bisa.</strong> Tampilan aplikasi bersifat responsif (*Mobile Friendly*). Bapak/Ibu dapat membukanya melalui peramban di ponsel pintar (smartphone) atau tablet dengan nyaman.
</p>

<h3>5. "Kapan sosialisasi langsung akan diadakan?"</h3>
<p>
Tim SDM & IT siap mengadakan sesi pendampingan langsung secara santai di meja kerja Bapak/Ibu pimpinan (*one-on-one coaching*) atau melalui forum rapat koordinasi departemen agar Bapak/Ibu merasa nyaman dan lancar mengoperasikannya.
</p>

<div class="page-break"></div>

<!-- ==================== HALAMAN 15: PENUTUP & KONTAK BANTUAN ==================== -->
<h1>PENUTUP & LAYANAN BANTUAN</h1>

<p>
Demikian Buku Panduan Pengguna dan Laporan Kemajuan Sistem Informasi Jobdesk Digital ini kami persembahkan. Besar harapan kami, inovasi ini dapat memberikan manfaat nyata dalam meningkatkan tata kelola organisasi, kejelasan tugas, dan produktivitas kerja di <strong>PT Kertas Padalarang (Peruri Security Paper Mill)</strong> yang kita banggakan bersama.
</p>

<div class="note-box" style="margin-top: 30px;">
    <h3 style="margin-top: 0; color: #141A5C;">PUSAT BANTUAN & PENDAMPINGAN TEKNIS</h3>
    <p style="margin-bottom: 6px;">Bila Bapak dan Ibu membutuhkan panduan langsung, konsultasi, atau mengalami kendala saat menggunakan aplikasi, silakan menghubungi tim kami yang selalu siap sedia membantu:</p>
    <table style="width: 100%; border: none; font-size: 10.5pt;">
        <tr>
            <td style="width: 180px; font-weight: bold; padding: 4px 0;">Departemen Terkait</td>
            <td>: <strong>Departemen Sumber Daya Manusia (SDM) & TI</strong></td>
        </tr>
        <tr>
            <td style="font-weight: bold; padding: 4px 0;">Lokasi Kantor</td>
            <td>: Gedung Kantor Pusat PTKP, Jl. Cihaliwung No. 1, Padalarang</td>
        </tr>
        <tr>
            <td style="font-weight: bold; padding: 4px 0;">Telepon / Ekstensi Internal</td>
            <td>: Ext. 214 (SDM) / Ext. 108 (Teknologi Informasi)</td>
        </tr>
        <tr>
            <td style="font-weight: bold; padding: 4px 0;">Waktu Layanan</td>
            <td>: Hari Kerja (Senin – Jumat), Pukul 08.00 – 16.30 WIB</td>
        </tr>
    </table>
</div>

<p style="text-align: center; margin-top: 60px; color: #64748b; font-size: 10pt;">
<strong>PT KERTAS PADALARANG</strong><br>
Jl. Cihaliwung No. 1, Kertamulya, Kec. Padalarang, Kabupaten Bandung Barat, Jawa Barat 40553<br>
<em>Website Intranet: http://portal.kertas-padalarang.co.id</em>
</p>

</body>
</html>
`;

// Tulis file HTML sementara untuk proses build PDF
const tempHtmlPath = path.join(baseDir, 'temp_doc_for_pdf.html');
fs.writeFileSync(tempHtmlPath, htmlContent, 'utf8');
console.log('File HTML panduan berhasil dibuat di:', tempHtmlPath);

// Jalankan Puppeteer untuk konversi ke PDF
(async () => {
    try {
        console.log('Membuka Puppeteer untuk merender berkas PDF...');
        const browser = await puppeteer.launch({
            headless: 'new',
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        const page = await browser.newPage();
        await page.goto(`file://${tempHtmlPath}`, { waitUntil: 'networkidle0' });
        
        console.log('Menghasilkan berkas PDF ukuran A4...');
        await page.pdf({
            path: outputPdfPath,
            format: 'A4',
            printBackground: true,
            margin: {
                top: '15mm',
                bottom: '15mm',
                left: '12mm',
                right: '12mm'
            },
            displayHeaderFooter: true,
            headerTemplate: '<div></div>',
            footerTemplate: '<div style="font-size: 9pt; color: #64748b; width: 100%; text-align: right; padding-right: 15mm;">Halaman <span class="pageNumber"></span> dari <span class="totalPages"></span> | Buku Panduan Jobdesk PTKP</div>'
        });

        await browser.close();
        
        // Hapus file HTML sementara
        fs.unlinkSync(tempHtmlPath);
        
        console.log('================================================================');
        console.log('BERHASIL! Berkas PDF resmi telah diterbitkan:');
        console.log(outputPdfPath);
        console.log('================================================================');
    } catch (err) {
        console.error('Terjadi kesalahan saat membuat PDF:', err);
        process.exit(1);
    }
})();
