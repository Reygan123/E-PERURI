# PANDUAN & DAFTAR AKUN PENGUJIAN MANUAL (QA MATRIX) SETIAP ROLE DI SEMUA PORTAL
## EKOSISTEM APLIKASI INTERNAL PT KERTAS PADALARANG (PERURI SECURITY PAPER MILL)
**Dokumen Referensi Pengujian Fungsional, Hak Akses (RBAC), dan Skenario Kolaborasi Antar Peran**

---

| Parameter Dokumen | Informasi Spesifikasi |
| :--- | :--- |
| **Nomor Dokumen** | `QA-REF/PTKP-TI/ACCOUNTS-MATRIX/2026/001` |
| **Tanggal Penerbitan** | 27 Oktober 2026 |
| **Sistem Terdistribusi** | 1. SI WOPPIT (`/layanan/work-orders`)<br>2. Portal Jobdesk (`/sdm/jobdesk`)<br>3. E-Arsip Persuratan (`/arsip`)<br>4. Platform Core & Audit Trail (`/platform`) |
| **Lingkungan Uji (Local)**| `http://localhost:8088` |
| **Default Password** | `password` *(Berlaku seragam untuk seluruh akun)* |
| **Pengguna Dokumen** | Tim QA/QC, Software Tester, Product Owner, & Developer |
| **Status Dokumen** | **RESMI / SIAP DIGUNAKAN (READY FOR MANUAL QA)** |

---

## 1. KETENTUAN DASAR & ARSITEKTUR KREDENSIAL

1. **Akses Masuk Universal:** Login dapat diakses melalui portal [http://localhost:8088/login](http://localhost:8088/login). Seluruh akun dapat login baik menggunakan **Alamat Email Resmi** (`@kertas-padalarang.co.id`) maupun **Username Akun**.
2. **Kredensial Default:** Seluruh akun pengujian diinisialisasi dengan kata sandi: `password`.
3. **Model Otorisasi (RBAC & Scope):**
   - `super-admin`: Hak akses tanpa batas ke seluruh modul, konfigurasi sistem, dan manajemen user.
   - `admin-sdm`: Pengelola master kepegawaian, struktur organisasi, dan maker uraian jabatan.
   - `pimpinan`: Pejabat struktural (Direksi, Kadep, Kasie) dengan kewenangan approval, paraf, dan disposisi.
   - `pegawai`: Seluruh karyawan pelaksana/staf untuk pemohon layanan, konseptor draf, dan viewer katalog.

---

## 2. MATRIKS AKUN PORTAL SI WOPPIT (WORK ORDER FASILITAS TI)
**URL Modul:** `/layanan/work-orders`  
**Fokus Pengujian:** Pengajuan tiket perbaikan perangkat, eskalasi ke teknisi TI, pemrosesan tiket, penyelesaian kendala, notifikasi in-app real-time, dan cetak formulir resmi SPPTI (IT-FR-01).

| Peran dalam Flow QA | Nama Pegawai | Username | Email | Role Sistem | Jabatan & Unit Kerja |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Pemohon Utama (Produksi)** | Fajar Nugraha, S.T. | `stafprd` | `stafprd@kertas-padalarang.co.id` | `pegawai` | Staf Perencanaan Dept Produksi *(Dept Produksi)* |
| **Pemohon (Akuntansi)** | Putri Anggraini, S.Ak. | `stafakt` | `stafakt@kertas-padalarang.co.id` | `pegawai` | Staf Departemen Akuntansi *(Dept Akuntansi)* |
| **Pemohon (Fasilitas Umum)** | Dadang Suhendra | `stafffu` | `stafffu@kertas-padalarang.co.id` | `pegawai` | Staf Fasilitas Umum *(Seksi Pengelolaan FU)* |
| **Teknisi IT / Super Admin** | Super Administrator | `superadmin` | `superadmin@kertas-padalarang.co.id` | `super-admin` | Administrator TI & Sistem *(Direktorat Utama)* |
| **Spesialis TI (Staf TI)** | Farhan Nugraha, S.Kom. | `stafit` | `stafit@kertas-padalarang.co.id` | `pegawai` | Spesialis Aplikasi & Infrastruktur TI *(Seksi TI)* |
| **Pimpinan Seksi TI** | Irfan Maulana, S.Kom., M.T.I. | `kasiepti` | `kasiepti@kertas-padalarang.co.id` | `pimpinan` | Kepala Seksi Pengembangan SDM & TI |
| **Pimpinan Pemohon** | Ir. Gunawan Wibisono | `kadepprd` | `kadepprd@kertas-padalarang.co.id` | `pimpinan` | Kepala Departemen Produksi |

---

## 3. MATRIKS AKUN PORTAL JOBDESK (URAIAN JABATAN & KOMPETENSI)
**URL Modul:** `/sdm/jobdesk`  
**Fokus Pengujian:** Penyusunan draf uraian jabatan (Maker) $\rightarrow$ Pengajuan verifikasi $\rightarrow$ Reviewer unit (Checker) $\rightarrow$ Pengesahan Kadep SDM/Direksi (Approver) disertai generate nomor otomatis & stempel TTE QR SHA-256 BSrE $\rightarrow$ Katalog & cetak bagi pegawai (Viewer).

| Peran dalam Flow QA | Nama Pegawai | Username | Email | Role Sistem | Jabatan & Unit Kerja |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Maker SDM Utama** | Admin SDM (Dev) | `adminsdm` | `adminsdm@kertas-padalarang.co.id` | `admin-sdm` | Spesialis SDM *(Dept Sumber Daya Manusia)* |
| **Maker SDM Operasional** | Nurul Aini, S.A.P. | `stafsdm` | `stafsdm@kertas-padalarang.co.id` | `pegawai` | Staf Personalia *(Seksi SDM Operasional)* |
| **Checker (SDM Operasional)**| Yulianti, S.Psi., M.M. | `kasieopr` | `kasieopr@kertas-padalarang.co.id` | `pimpinan` | Kepala Seksi SDM Operasional |
| **Checker (Pemasaran)** | Rian Hidayat, S.E., M.M. | `kasiepjl` | `kasiepjl@kertas-padalarang.co.id` | `pimpinan` | Kepala Seksi Pemasaran Dan Penjualan |
| **Checker (Pabrik Unit I)** | Kepala Seksi PK1 (Dev) | `kasiepk1` | `kasiepk1@kertas-padalarang.co.id` | `pimpinan` | Kepala Seksi Produksi Kertas Unit I |
| **Approver Pengesah SDM** | Dra. Sri Wahyuni, M.Psi. | `kadepsdm` | `kadepsdm@kertas-padalarang.co.id` | `pimpinan` | Kepala Departemen Sumber Daya Manusia |
| **Approver Direksi (Keuangan)** | Hendra Wijaya, S.E., Ak. | `dirkeu` | `dirkeu@kertas-padalarang.co.id` | `pimpinan` | Direktur Keuangan *(Direktorat Keuangan)* |
| **Approver Direksi (Operasi)** | Dr. Ir. Agus Suryono, M.T. | `dirops` | `dirops@kertas-padalarang.co.id` | `pimpinan` | Direktur Operasi *(Direktorat Operasi)* |
| **Viewer Pegawai (Sales)** | Maya Anggraeni, S.E. | `salespjl` | `salespjl@kertas-padalarang.co.id` | `pegawai` | Account Executive *(Seksi Pemasaran & Penjualan)* |
| **Viewer Pegawai (Pabrik)** | Operator PK1 (Dev) | `operatorpk1` | `operatorpk1@kertas-padalarang.co.id` | `pegawai` | Operator Produksi Kertas Unit I |

---

## 4. MATRIKS AKUN PORTAL E-ARSIP PERSURATAN & TATA NASKAH DINAS
**URL Modul:** `/arsip/surat-masuk`, `/arsip/surat-keluar`, `/arsip/persetujuan`, `/arsip/khusus`  
**Fokus Pengujian:** Pembuatan draf surat dinas keluar, meja paraf/persetujuan berjenjang, penandatanganan elektronik, registrasi surat masuk eksternal, pembuatan lembar disposisi, dan verifikasi QR publik.

| Peran dalam Flow QA | Nama Pegawai | Username | Email | Role Sistem | Jabatan & Unit Kerja |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Konseptor Surat Keluar** | Dadang Suhendra | `stafffu` | `stafffu@kertas-padalarang.co.id` | `pegawai` | Staf Pengelolaan Gedung *(Seksi Fasilitas Umum)* |
| **Konseptor Naskah Korporat** | Annisa Permata, S.I.Kom. | `stafsekretariat` | `stafsekretariat@kertas-padalarang.co.id` | `pegawai` | Staf Tata Usaha *(Sekretariat Perusahaan)* |
| **Petugas Agenda & TU** | Dewi Anggraeni, S.Sos., M.Si.| `kasekretariat` | `kasekretariat@kertas-padalarang.co.id` | `pimpinan` | Kepala Sekretariat Perusahaan |
| **Verifikator / Paraf Atasan** | Asep Saepulloh, S.Sos. | `kasiepfu` | `kasiepfu@kertas-padalarang.co.id` | `pimpinan` | Kepala Seksi Pengelolaan Fasilitas Umum |
| **Pejabat Penandatangan** | Drs. Eko Prasetyo, M.M. | `kadeppfa` | `kadeppfa@kertas-padalarang.co.id` | `pimpinan` | Kepala Departemen Pengadaan & Fasilitas Umum |
| **Penerima Disposisi Direksi**| Dr. Ir. Agus Suryono, M.T. | `dirops` | `dirops@kertas-padalarang.co.id` | `pimpinan` | Direktur Operasi *(Direktorat Operasi)* |

---

## 5. MATRIKS AKUN PLATFORM CORE, MASTER DATA & AUDIT TRAIL
**URL Modul:** `/platform/users`, `/platform/employees`, `/platform/org-units`, `/platform/positions`, `/platform/numbering`, `/platform/audit-logs`  
**Fokus Pengujian:** Manajemen pengguna, pemetaan unit organisasi dan jabatan, konfigurasi format penomoran otomatis dokumen, dan rekam jejak audit trail kepatuhan.

| Peran dalam Flow QA | Nama Pegawai | Username | Email | Role Sistem | Jabatan & Unit Kerja |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Super Administrator** | Super Administrator | `superadmin` | `superadmin@kertas-padalarang.co.id` | `super-admin` | Pengelola Penuh Platform *(Direktorat Utama)* |
| **Administrator SDM** | Admin SDM (Dev) | `adminsdm` | `adminsdm@kertas-padalarang.co.id` | `admin-sdm` | Pengelola Master Pegawai & Posisi Jabatan |
| **Auditor Internal (SPI)** | Bambang Suherman, S.E., Ak.| `auditorspi` | `auditorspi@kertas-padalarang.co.id` | `pegawai` | Auditor Internal *(Satuan Pengawasan Internal)* |
| **Kepala Pengawas SPI** | Drs. M. Taufik Hidayat, CFrA. | `kaspi` | `kaspi@kertas-padalarang.co.id` | `pimpinan` | Kepala Satuan Pengawasan Internal |

---

## 6. PANDUAN LANGKAH SKENARIO PENGUJIAN MANUAL (STEP-BY-STEP QA)

### Skenario A: End-to-End SI WOPPIT (Dua Sesi Browser)
1. **Langkah 1 (Browser Normal):** Login sebagai `stafprd` / `password`. Buka menu **Layanan TI** $\rightarrow$ Klik **"Buat Tiket Baru"** $\rightarrow$ Isi kategori Hardware, deskripsi kerusakan, dan submit tiket.
2. **Langkah 2 (Browser Incognito):** Login sebagai `superadmin` / `password`. Perhatikan ikon lonceng di navbar: indikator merah bertambah, klik untuk membuka popup notifikasi berisi tiket baru `IT0xx`.
3. **Langkah 3:** Klik tiket $\rightarrow$ Klik tombol **"Proses Tiket"** $\rightarrow$ Status berubah menjadi `in_progress`.
4. **Langkah 4 (Browser Normal):** Kembali ke akun `stafprd` $\rightarrow$ Periksa lonceng notifikasi: terima notifikasi "Tiket Sedang Diproses oleh Teknisi".
5. **Langkah 5 (Browser Incognito):** Pada akun `superadmin`, klik **"Selesaikan Tiket"** $\rightarrow$ Masukkan tindakan perbaikan dan sparepart $\rightarrow$ Tiket berstatus `completed`.
6. **Langkah 6 (Browser Normal):** Akun `stafprd` menerima notifikasi penyelesaian $\rightarrow$ Buka tiket $\rightarrow$ Klik **"Cetak Lembar Kerja SPPTI (A4)"**.

### Skenario B: End-to-End Portal Jobdesk (Maker $\rightarrow$ Checker $\rightarrow$ Approver $\rightarrow$ Viewer)
1. **Langkah 1 (Maker):** Login sebagai `adminsdm` $\rightarrow$ Buka **Manajemen Jobdesk** $\rightarrow$ Buat atau edit uraian jabatan $\rightarrow$ Klik tombol **"Ajukan Verifikasi"** (status menjadi `in_review`).
2. **Langkah 2 (Approver):** Login sebagai `kadepsdm` $\rightarrow$ Buka notifikasi in-app bertajuk "Draf Jobdesk Diajukan" $\rightarrow$ Buka detail jobdesk $\rightarrow$ Klik tombol **"Setujui Jobdesk"**.
3. **Langkah 3 (Verifikasi Output):** Sistem secara otomatis menerbitkan Nomor Dokumen resmi dan membubuhkan Stempel Digital SHA-256 BSrE Peruri.
4. **Langkah 4 (Viewer):** Login sebagai `salespjl` $\rightarrow$ Buka katalog jobdesk $\rightarrow$ Pastikan jobdesk berstatus disahkan dapat diakses dan dicetak, namun tombol aksi pengubahan dinonaktifkan (read-only).
