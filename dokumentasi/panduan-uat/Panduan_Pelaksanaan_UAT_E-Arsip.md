# PANDUAN PELAKSANAAN USER ACCEPTANCE TESTING (UAT) TERPIMPIN
## SISTEM TATA KELOLA PERSURATAN ELEKTRONIK (E-ARSIP)
### PT KERTAS PADALARANG (PERURI SECURITY PAPER MILL)

---

## 1. PENDAHULUAN & TUJUAN PENGUJIAN

Dokumen ini merupakan panduan operasional bagi para penguji (*testers*) dalam pelaksanaan **User Acceptance Testing (UAT) Terpimpin** untuk ekosistem aplikasi internal **E-Arsip PT Kertas Padalarang (PTKP)**.

### 1.1. Tujuan UAT
1. **Validasi Kesesuaian Bisnis:** Memastikan alur registrasi, persetujuan berjenjang, penomoran atomik, dan disposisi naskah dinas selaras 100% dengan **SK Direksi No. 009/KPTS/PTKP/IV/2026** dan aturan tata naskah dinas PTKP.
2. **Uji Penerimaan Pengguna (*User Acceptance*):** Memastikan antarmuka baru yang ramah pengguna senior (*senior-friendly*), pratinjau dokumen cepat, dan navigasi menu per jenis surat dapat dioperasikan secara intuitif oleh seluruh jajaran karyawan.
3. **Verifikasi Keandalan Sistem:** Menguji keandalan notifikasi multi-kanal (WhatsApp, Email korporat, in-app alert), pembatasan akses data (*multi-tenant security*), serta validasi Tanda Tangan Elektronik (TTE BSrE - BSSN).

### 1.2. Lingkungan Pengujian
- **URL Aplikasi:** `http://127.0.0.1:8088` (atau `http://arsip-uat.kertas-padalarang.co.id`)
- **Portal Verifikasi Publik:** `http://127.0.0.1:8088/verify/{hash}`
- **Password Default Seluruh Akun Uji:** `password123` (atau sesuai tabel akun)

---

## 2. MATRIKS 78 AKUN PENGUJIAN TERSTANDARISASI

Seluruh departemen dan jabatan struktural dalam bagan organisasi PTKP telah disediakan akun pengujian khusus:

| No | Unit Kerja / Departemen | Kode Unit | Username Uji | Jabatan Struktural | Peran Sistem |
| :---: | :--- | :---: | :--- | :--- | :--- |
| 1 | **Direksi** | `DIR-UTM` | `dirutama` / `superadmin` | Direktur Utama | Approval Final / Disposisi |
| 2 | Direksi | `DIR-KEU` | `dirkeu` | Direktur Keuangan, SDM & MR | Approval Final / POH Akuntansi |
| 3 | Direksi | `DIR-PROD` | `dirprod` | Direktur Produksi & Komersial | Approval Final |
| 4 | **Sekretariat Perusahaan** | `SEC-PRSH` | `sekretariat` | Kepala Sekretariat Perusahaan | Verifikator Surat Masuk/Keluar |
| 5 | Sekretariat Perusahaan | `SEC-PRSH` | `stafsekretariat` | Staf Sekretariat Perusahaan | Operator Agenda Persuratan |
| 6 | **Satuan Pengawasan Intern** | `SPI` | `kadepspi` | Kepala Satuan Pengawasan Intern | Verifikator Audit |
| 7 | Satuan Pengawasan Intern | `SPI` | `auditorspi` | Auditor Internal | Staf Pembuat Nota Dinas |
| 8 | **Departemen SDM & Umum** | `DEP-SDM` | `kadepsdm` | Kepala Departemen SDM & Umum | Verifikator SPPD & Klaim BP |
| 9 | Departemen SDM & Umum | `DEP-SDM` | `admin_sdm` | Staf SDM & Fasilitas Pabrik | Pengelola Dokumen SDM |
| 10 | **Departemen Akuntansi & Keuangan** | `DEP-AKT` | `stafakt` | Staf Akuntansi | Pembuat SPB & Memo Keuangan |
| 11 | **Departemen Produksi** | `DEP-PRD` | `kadepprd` | Kepala Departemen Produksi | Approval Lini Produksi |
| 12 | Seksi Paper Machine 1 | `PRD-PK1` | `kasiepk1` | Kepala Seksi PM-1 | Paraf Berjenjang PK-1 |
| 13 | Seksi Paper Machine 1 | `PRD-PK1` | `operatorpk1` | Operator Mesin PK-1 | Pembuat Draf Laporan Produksi |
| 14 | Seksi Paper Machine 2 | `PRD-PK2` | `kasiepk2` | Kepala Seksi PM-2 | Paraf Berjenjang PK-2 |
| 15 | Seksi Paper Machine 2 | `PRD-PK2` | `operatorpk2` | Operator Mesin PK-2 | Pembuat Draf Laporan Produksi |
| 16 | **Departemen Teknik & Utilitas** | `DEP-TEK` | `kadeptek` | Kepala Departemen Teknik | Approval Pemeliharaan Mesin |
| 17 | Seksi Boiler & Pembangkit | `TEK-BLR` | `kasieblr` | Kepala Seksi Boiler & Tenaga | Verifikator Pekerjaan Utilitas |
| 18 | Seksi Boiler & Pembangkit | `TEK-BLR` | `operatorblr` | Operator Boiler | Pembuat SPPD Kalibrasi |
| 19 | Seksi Kimia & Laboratorium | `QC-LAB` | `kasiekim` | Kepala Laboratorium QC | Verifikator Mutu Kertas |
| 20 | Seksi Kimia & Laboratorium | `QC-LAB` | `operatorkim` | Operator Analis Kimia | Pengaju Klaim BP / Reagen |
| 21 | **Departemen Pemasaran & Logistik** | `DEP-LOG` | `kadeplog` | Kepala Departemen Logistik | Approval Pengiriman Kertas |
| 22 | Seksi Gudang Bahan Baku | `LOG-GDG` | `kasiegdg` | Kepala Seksi Gudang & Ekspedisi | Verifikator Surat Jalan |

*(Catatan: Daftar lengkap 78 akun pengujian tercatat di database dan dapat diakses menggunakan kata sandi standar `password123`).*

---

## 3. SKENARIO PENGUJIAN UAT TERPIMPIN

### SKENARIO UAT-01: Registrasi Surat Masuk & Disposisi Multi-Tingkat
- **Tujuan:** Menguji alur pencatatan surat masuk dari pihak eksternal, pratinjau pindaian PDF, dan instruksi disposisi dari pimpinan ke bawahan.
- **Data Uji:** Naskah yang telah disediakan: `[UAT-01] Surat Permohonan Kebutuhan Kertas Uang Pecahan Baru dari Bank Indonesia`.

| Langkah | Aksi Penguji | Akun Penguji | Hasil yang Diharapkan | Status |
| :---: | :--- | :--- | :--- | :---: |
| 1 | Buka menu **E-Arsip > Surat Masuk**. | `admin_sdm` / `stafsekretariat` | Halaman daftar Surat Masuk terbuka, menampilkan dokumen `[UAT-01]`. | [ ] Lulus |
| 2 | Klik tombol **"Lihat"** pada naskah `[UAT-01]`. | `admin_sdm` | Dokumen terbuka pada modal/tab pratinjau dengan rincian pengirim BI dan ringkasan isi surat. | [ ] Lulus |
| 3 | Buka tab **"Riwayat Disposisi"**. | `admin_sdm` | Terlihat instruksi awal dari Direktur Utama kepada Kepala Departemen Produksi. | [ ] Lulus |
| 4 | Logout, lalu login sebagai Kepala Departemen Produksi. | `kadepprd` | Dokumen `[UAT-01]` muncul di daftar surat masuk unit produksi dengan tanda disposisi aktif. | [ ] Lulus |
| 5 | Klik tombol **"Buat Disposisi Lanjutan"**, isi instruksi: *"Kasie PK1 siapkan mesin uji coba"* ke penerima `kasiepk1`, klik **Kirim Disposisi**. | `kadepprd` | Disposisi berjenjang berhasil disimpan; notifikasi in-app terkirim ke `kasiepk1`. | [ ] Lulus |

---

### SKENARIO UAT-02: Surat Keluar Dinas, Paraf Berjenjang & Penomoran Atomik Otomatis
- **Tujuan:** Memvalidasi pengajuan naskah keluar, paraf oleh Kepala Seksi, pengesahan final oleh Kepala Departemen, serta penerbitan nomor surat otomatis yang anti-duplikasi (*atomic counter*).
- **Data Uji:** Dokumen `[UAT-02] Pengantar Pengiriman Sampel Kertas Paspor Cetakan Batch 14`.

| Langkah | Aksi Penguji | Akun Penguji | Hasil yang Diharapkan | Status |
| :---: | :--- | :--- | :--- | :---: |
| 1 | Login dan periksa menu **Persetujuan Naskah**. | `kasiepk1` | Muncul 1 tugas persetujuan: surat `[UAT-02]` dengan status `pending` (Langkah 1: Paraf Kasie). | [ ] Lulus |
| 2 | Buka pratinjau naskah dan teliti susunan draf kop PTKP dan perihal. | `kasiepk1` | Format layout PDF tampil rapi dan presisi sesuai standar Kop PT Kertas Padalarang. | [ ] Lulus |
| 3 | Klik tombol **"Beri Paraf / Setujui"**, masukkan catatan: *"Sudah diperiksa, spesimen sesuai standard"* lalu klik **Kirim**. | `kasiepk1` | Keputusan paraf tersimpan; status naskah berlanjut ke Langkah 2 (Kepala Departemen Produksi). | [ ] Lulus |
| 4 | Logout, lalu login sebagai Kepala Departemen Produksi. | `kadepprd` | Surat `[UAT-02]` kini berstatus `pending` di meja persetujuan `kadepprd`. | [ ] Lulus |
| 5 | Buka naskah dan klik tombol **"Pengesahan Final"**. | `kadepprd` | Sistem mengesahkan naskah: **Nomor Surat Resmi terbit otomatis** (format `.../PRD-PK1/.../2026`) dan status berubah menjadi `published` / `signed`. | [ ] Lulus |

---

### SKENARIO UAT-03: Siklus Revisi Naskah Dinas (Rule 52: Reset Alur ke Langkah 1)
- **Tujuan:** Memastikan bila pemeriksa meminta perbaikan, naskah dikembalikan ke perancang, tombol edit aktif, dan alur persetujuan mengulang dari awal (Langkah 1).
- **Data Uji:** Dokumen `[UAT-03] Usulan Penggantian Sparepart Katup Uap Boiler Unit 2`.

| Langkah | Aksi Penguji | Akun Penguji | Hasil yang Diharapkan | Status |
| :---: | :--- | :--- | :--- | :---: |
| 1 | Login dan buka menu **E-Arsip > Naskah Dinas**. | `operatorpk1` | Dokumen `[UAT-03]` memiliki status oranye: `revision_requested` (*Permintaan Revisi*). | [ ] Lulus |
| 2 | Klik tombol **"Lihat"** pada naskah `[UAT-03]`. | `operatorpk1` | Di bagian atas naskah tampil banner peringatan revisi dari Kadep Teknik: *"Mohon sertakan estimasi biaya..."*. | [ ] Lulus |
| 3 | Klik tombol **"Ubah / Edit Naskah"**. | `operatorpk1` | Formulir editor TipTap terbuka dengan isi naskah lama yang dapat disunting kembali. | [ ] Lulus |
| 4 | Tambahkan teks rincian anggaran pada editor: *"Estimasi biaya suku cadang: Rp 12.500.000,-"* lalu klik **Simpan & Ajukan Ulang**. | `operatorpk1` | Perubahan tersimpan; alur persetujuan berhasil direset kembali ke Langkah 1. | [ ] Lulus |

---

### SKENARIO UAT-04: Eskalasi Otomatis POH Akuntansi ke Direktur Keuangan
- **Tujuan:** Memvalidasi bahwa surat yang berasal dari Departemen Akuntansi (yang saat ini belum memiliki pejabat definitif) otomatis dialihkan ke Direktur Keuangan tanpa gagal alur (*fail-safe delegation*).
- **Data Uji:** Dokumen `[UAT-04] Rekonsiliasi Saldo Kas Operasional dan Giro Bank Mandiri Maret 2026`.

| Langkah | Aksi Penguji | Akun Penguji | Hasil yang Diharapkan | Status |
| :---: | :--- | :--- | :--- | :---: |
| 1 | Login dan buka menu **Persetujuan Naskah**. | `dirkeu` | Naskah `[UAT-04]` langsung masuk ke meja Direktur Keuangan sebagai pejabat POH Akuntansi. | [ ] Lulus |
| 2 | Klik **"Pratinjau PDF"**. | `dirkeu` | Lembar Memorandum keuangan tampil lengkap dengan kop Direksi PT Kertas Padalarang. | [ ] Lulus |
| 3 | Berikan keputusan **"Setujui"**. | `dirkeu` | Naskah resmi disahkan dengan otoritas Direktur Keuangan. | [ ] Lulus |

---

### SKENARIO UAT-05: Formulir Operasional Khusus (SPPD & Biaya Pengobatan)
- **Tujuan:** Memvalidasi pencatatan dan penerbitan formulir operasional internal harian pabrik.
- **Data Uji:** `[UAT-05A]` (SPPD Balai K3) & `[UAT-05B]` (Klaim BP Rawat Jalan).

| Langkah | Aksi Penguji | Akun Penguji | Hasil yang Diharapkan | Status |
| :---: | :--- | :--- | :--- | :---: |
| 1 | Buka menu **E-Arsip > Formulir Khusus**. | `kadepsdm` | Tampil naskah dinas khusus SPPD `[UAT-05A]` dan BP `[UAT-05B]`. | [ ] Lulus |
| 2 | Buka naskah `[UAT-05A]` (SPPD). | `kadepsdm` | Tabel rincian perjalanan dinas, tujuan Balai K3, lama hari, dan akomodasi tertera lengkap. | [ ] Lulus |
| 3 | Buka naskah `[UAT-05B]` (Biaya Pengobatan). | `kadepsdm` | Rincian kuitansi RS Dustira Cimahi sebesar Rp 450.000,- terekam jelas. | [ ] Lulus |
| 4 | Setujui kedua pengajuan tersebut. | `kadepsdm` | Dokumen berpindah ke status `approved` / `published`. | [ ] Lulus |

---

### SKENARIO UAT-06: Uji Batas Hak Akses, Proteksi Data & Validasi Error 403
- **Tujuan:** Memastikan pengguna staf/operator pabrik tidak dapat mengakses data konfigurasi platform master atau naskah unit kerja lain (mencegah pelanggaran keamanan & IDOR).
- **Akun Penguji:** `operatorpk1` (Operator Mesin PK-1).

| Langkah | Aksi Penguji | URL yang Dituju | Hasil yang Diharapkan | Status |
| :---: | :--- | :--- | :--- | :---: |
| 1 | Login sebagai operator pabrik. | `operatorpk1` | Masuk ke antarmuka aplikasi E-Arsip. | [ ] Lulus |
| 2 | Coba ketikkan URL manajemen pengguna pada address bar: | `http://127.0.0.1:8088/platform/users` | Akses **ditolak**; Tampil halaman **Error 403 Akses Terbatas** resmi ber-brand PTKP dengan tombol kembali ke dashboard. | [ ] Lulus |
| 3 | Coba ketikkan URL unit organisasi: | `http://127.0.0.1:8088/platform/org-units` | Akses **ditolak** (403 Forbidden). | [ ] Lulus |
| 4 | Coba ketikkan URL audit log sistem: | `http://127.0.0.1:8088/platform/audit-logs` | Akses **ditolak** (403 Forbidden). | [ ] Lulus |

---

### SKENARIO UAT-07: Verifikasi Keabsahan Tanda Tangan Elektronik (TTE BSrE) via QR Code
- **Tujuan:** Memvalidasi pemindaian QR Code pada lembar naskah dinas dan verifikasi publik di portal resmi tanpa perlu login.

| Langkah | Aksi Penguji | Tautan / Tindakan | Hasil yang Diharapkan | Status |
| :---: | :--- | :--- | :--- | :---: |
| 1 | Buka dokumen resmi yang telah ditandatangani secara digital (misal hasil Skenario 2). | Buka Pratinjau PDF | Pada blok tanda tangan tampil stempel digital resmi BSrE BSSN dan **QR Code aktif**. | [ ] Lulus |
| 2 | Pindai QR Code dengan smartphone atau buka tautan verifikasi peramban: | `http://127.0.0.1:8088/verify/{hash}` | Halaman publik **Verifikasi Dokumen Elektronik** terbuka. | [ ] Lulus |
| 3 | Periksa elemen verifikasi yang tampil. | Layar smartphone / browser | Tampil lencana hijau **"KEABSAHAN NASKAH DINAS TERVERIFIKASI"**, nomor surat resmi, nama pejabat penandatangan, NIP, serial sertifikat BSrE, dan waktu penandatanganan. | [ ] Lulus |
| 4 | Uji coba memasukkan kode hash acak/palsu: | `http://127.0.0.1:8088/verify/kode-palsu-1234` | Tampil lencana merah **"DOKUMEN TIDAK TERDAFTAR ATAU TIDAK VALID"** dengan pesan keamanan resmi. | [ ] Lulus |

---

## 4. KRITERIA KELULUSAN UAT (DEFINITION OF DONE)

Pengujian dinyatakan **LULUS (DITERIMA)** apabila:
1. Seluruh 7 skenario pengujian utama mendapatkan status **LULUS** dari para perwakilan penguji.
2. Tidak ditemukan *Critical Bug* (sistem macet, nomor surat duplikat, atau kebocoran akses data antar-unit).
3. Hasil survei kepuasan antarmuka (kemudahan menu, kejelasan teks, kecepatan pratinjau) mencapai nilai rata-rata minimal **85%**.
4. Berita Acara Serah Terima (BAST) UAT telah ditandatangani bersama oleh perwakilan Manajemen PTKP dan Tim Pengembang.
