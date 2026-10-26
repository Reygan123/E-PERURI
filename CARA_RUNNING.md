# Panduan Menjalankan Project (Cara Running)
## Ekosistem Aplikasi Internal & E-Arsip PT Kertas Padalarang (PTKP)
**Peruri Security Paper Mill — Versi 2 (Modular Monolith)**

Dokumen ini berisi panduan lengkap langkah demi langkah untuk melakukan instalasi, konfigurasi basis data, migrasi, kompilasi aset, dan menjalankan aplikasi di lingkungan lokal/development.

---

## 1. Prasyarat Sistem (Prerequisites)

Sebelum memulai, pastikan perangkat Anda telah terpasang perangkat lunak berikut:

| Perangkat Lunak | Versi Minimal | Keterangan |
| :--- | :--- | :--- |
| **PHP** | `^8.2` | Ekstensi wajib: `pdo_pgsql`, `pgsql`, `mbstring`, `openssl`, `bcmath`, `curl`, `xml`, `fileinfo`, `gd`, `zip` |
| **Composer** | `^2.5` | Dependency Manager PHP |
| **Node.js** | `^18.x` atau `^20.x` | JavaScript Runtime (LTS direkomendasikan) |
| **NPM** | `^9.x` atau `^10.x` | Package Manager Frontend |
| **Docker & Docker Compose** | Versi Terbaru | Untuk menjalankan PostgreSQL 16 & Redis secara terisolasi |
| *Alternatif Database* | PostgreSQL 16 & Redis 7 | Jika tidak menggunakan Docker (diinstal langsung di OS) |

---

## 2. Struktur Arsitektur & Skema Basis Data

Aplikasi ini mengadopsi arsitektur **Modular Monolith** dengan basis data **PostgreSQL Multi-Schema**.
Setiap modul memiliki skema tabel terpisah:
- `platform`: Manajemen pengguna, unit organisasi, jabatan, format penomoran naskah, audit log, autentikasi.
- `arsip`: Naskah dinas, surat masuk, surat keluar, disposisi, workflow persetujuan, TTE BSrE, histori revisi.
- `layanan`: Helpdesk & permohonan layanan umum internal.
- `pembelajaran`: Modul LMS / pelatihan internal.
- `sdm`: Administrasi kepegawaian & mutasi.
- `manufaktur`: Manajemen produksi kertas sekuriti.

> [!IMPORTANT]
> Skema-skema di atas harus diinisialisasi terlebih dahulu di PostgreSQL sebelum menjalankan migrasi tabel. Jika menggunakan Docker Compose, skema ini dibuat otomatis melalui skrip `docker/postgres/init-schemas.sql`.

---

## 3. Langkah-Langkah Instalasi & Setup

### Langkah 1: Clone & Buka Folder Project
```bash
cd c:\Project\PERURI\Project\v2
```

### Langkah 2: Siapkan Berkas Konfigurasi Lingkungan (`.env`)
Salin berkas `.env.example` menjadi `.env`:
```bash
# Windows PowerShell:
Copy-Item .env.example .env

# Atau Linux/Git Bash:
cp .env.example .env
```

Pastikan variabel basis data pada file `.env` telah disesuaikan:
```ini
APP_NAME="PTKP Internal Ecosystem"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8088

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5434
DB_DATABASE=ptkp_v2
DB_USERNAME=ptkp_user
DB_PASSWORD=secret
DB_SCHEMA=platform,arsip,public

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

REDIS_HOST=127.0.0.1
REDIS_PORT=6380
```

> [!NOTE]
> Port database default pada Docker Compose diset ke **`5434`** dan Redis ke **`6380`** agar tidak bentrok dengan instance PostgreSQL default port `5432` atau Redis port `6379` yang mungkin sedang berjalan di komputer lokal Anda.

---

### Langkah 3: Jalankan Database PostgreSQL & Redis via Docker

Jalankan container database dan cache di latar belakang:
```bash
docker compose up -d
```

Verifikasi bahwa container berjalan dengan status sehat (`healthy`):
```bash
docker compose ps
```
*Container yang berjalan: `ptkp_v2_postgres` (port 5434->5432) dan `ptkp_v2_redis` (port 6380->6379).*

*(Opsional: Jika menggunakan PostgreSQL Lokal tanpa Docker)*:
Masuk ke psql dan jalankan pembuatan schema secara manual:
```sql
CREATE DATABASE ptkp_v2;
CREATE USER ptkp_user WITH ENCRYPTED PASSWORD 'secret';
GRANT ALL PRIVILEGES ON DATABASE ptkp_v2 TO ptkp_user;
\c ptkp_v2
CREATE SCHEMA IF NOT EXISTS platform;
CREATE SCHEMA IF NOT EXISTS arsip;
CREATE SCHEMA IF NOT EXISTS layanan;
CREATE SCHEMA IF NOT EXISTS pembelajaran;
CREATE SCHEMA IF NOT EXISTS sdm;
CREATE SCHEMA IF NOT EXISTS manufaktur;
GRANT ALL ON SCHEMA platform, arsip, layanan, pembelajaran, sdm, manufaktur, public TO ptkp_user;
```

---

### Langkah 4: Install Dependensi PHP (Composer)

Jalankan instalasi pustaka backend:
```bash
composer install
```

Generate application encryption key:
```bash
php artisan key:generate
```

---

### Langkah 5: Jalankan Migrasi & Database Seeder

Eksekusi seluruh migrasi tabel lintas skema dan isi data master (Unit Organisasi, Jabatan, Role, Format Penomoran, Jenis Naskah Dinas, Akun Pengujian 78 Pegawai):

```bash
php artisan migrate:fresh --seed
```

*(Opsional)* Jalankan seeder khusus skenario simulasi UAT & TTE BSrE:
```bash
php artisan db:seed --class=App\Modules\Arsip\Database\Seeders\UatScenarioSeeder
```

---

### Langkah 6: Install Dependensi Frontend & Kompilasi Aset

Install seluruh dependensi Node.js (termasuk Vue 3, Inertia, Tailwind CSS, TipTap Editor, dan Puppeteer):
```bash
npm install
```

Kompilasi aset produksi dan Server-Side Rendering (SSR) bundle:
```bash
npm run build
```

---

## 4. Cara Menjalankan Aplikasi (Development Server)

Untuk menjalankan seluruh fungsi aplikasi secara penuh (termasuk frontend reaktif, backend API, dan notifikasi latar belakang), disarankan membuka **3 terminal**:

### Terminal 1: Web Server Backend (Laravel)
```bash
php artisan serve --port=8088
```
Aplikasi dapat diakses melalui browser pada: **[http://localhost:8088](http://localhost:8088)**

### Terminal 2: Frontend Asset Bundler (Vite Hot-Reload)
Jika Anda sedang melakukan pengubahan tampilan (Vue / CSS):
```bash
npm run dev
```

### Terminal 3: Queue Worker (Notifikasi & Background Jobs)
Diperlukan untuk memproses antrean email, notifikasi WhatsApp, dan render PDF TTE:
```bash
php artisan queue:work
```

---

## 5. Kredensial Login Pengujian (Development Accounts)

Seluruh akun pengujian menggunakan kata sandi (password) default yang seragam:
> **Password:** `password`

Berikut adalah beberapa akun utama yang dapat langsung digunakan sesuai peran:

| Peran / Hak Akses | Username | Nama Pegawai & Jabatan | Unit Kerja |
| :--- | :--- | :--- | :--- |
| **Super Administrator** | `superadmin` | Super Administrator (Dev) / Direktur Utama | Kantor Direksi Utama (`DIR-UTM`) |
| **Sekretaris Direksi** | `stafdirut` | Nadia Salsabila, S.I.Kom. (Sekretaris Dirut) | Kantor Direksi Utama (`DIR-UTM`) |
| **Pimpinan / Kadiv Arsip** | `kadivarsip` | Kepala Divisi Arsip & Dokumentasi | Divisi Arsip & Legal |
| **Staf / Verifikator Arsip** | `stafarsip` | Verifikator Naskah Dinas & Agenda | Bagian Tata Usaha & Kearsipan |
| **Pimpinan SDM & Hukum** | `kadivsdm` | Kepala Divisi SDM, Hukum & Kepatuhan | Divisi SDM & Hukum |
| **Staf SDM** | `stafsdm` | Analis Kepegawaian & Mutasi | Bagian Sumber Daya Manusia |
| **Pimpinan Keuangan** | `pimpkeu` | Kepala Departemen Keuangan & Akuntansi | Departemen Keuangan |
| **Staf Keuangan** | `stafkeu` | Verifikator Pembayaran & SPB | Bagian Akuntansi & Anggaran |
| **Pimpinan Produksi** | `pimpprod` | Kepala Bagian Produksi Kertas Uang | Divisi Manufaktur Kertas |

> [!TIP]
> Daftar lengkap ke-78 akun pegawai untuk seluruh 39 unit organisasi tertera pada dokumen:
> [`dokumentasi/panduan-uat/Panduan_Pelaksanaan_UAT_E-Arsip.md`](file:///c:/Project/PERURI/Project/v2/dokumentasi/panduan-uat/Panduan_Pelaksanaan_UAT_E-Arsip.md).

---

## 6. Verifikasi Publik Naskah Dinas & TTE BSrE

Aplikasi dilengkapi fitur verifikasi keabsahan dokumen tanda tangan elektronik tanpa perlu login:
- Format URL verifikasi publik:
  `http://localhost:8088/verify/{hash_sha256}`
- QR Code yang tertera pada dokumen PDF hasil unduhan akan mengarah langsung ke URL portal verifikasi publik ini untuk memvalidasi sertifikat BSrE BSSN dan integritas dokumen.

---

## 7. Perintah Pengujian Kualitas Kode (QA & QC)

Untuk memastikan kode memenuhi standar arsitektur dan lulus seluruh tes otomatis:

```bash
# 1. Jalankan Seluruh Test Suite Otomatis (86 tests, 414 assertions)
php artisan test

# 2. Analisis Statis Kode (PHPStan / Larastan) - Target: 0 Errors
vendor/bin/phpstan analyse --memory-limit=1G

# 3. Validasi Batas Arsitektur Modular Monolith (Deptrac) - Target: 0 Violations
vendor/bin/deptrac analyse --config-file=deptrac.yaml

# 4. Pengecekan Standar Gaya Kode (Laravel Pint)
vendor/bin/pint --test

# 5. Pengecekan Strict Typings Vue / TypeScript
npx vue-tsc --noEmit

# 6. Pengecekan Linter Frontend (ESLint)
npm run lint
```

---

## 8. Pemecahan Masalah Umum (Troubleshooting)

1. **Galat `schema "platform" does not exist` saat migrasi**:
   - Penyebab: Basis data PostgreSQL belum membuat schema sebelum Laravel menjalankan migrasi.
   - Solusi: Pastikan menjalankan database lewat `docker compose up -d` (skrip `docker/postgres/init-schemas.sql` dieksekusi otomatis saat pembuatan container pertama kali), atau buat schema secara manual melalui pgAdmin/DBeaver/psql.

2. **Galat `Unable to locate file in Vite manifest`**:
   - Penyebab: Berkas halaman Vue baru belum terindeks pada berkas `public/build/manifest.json`.
   - Solusi: Jalankan perintah kompilasi `npm run build`.

3. **Database Testing Error saat menjalankan `php artisan test`**:
   - Pengujian otomatis menggunakan basis data `ptkp_v2_testing`.
   - Pastikan database testing telah dibuat dan dimigrasi:
     ```powershell
     # Di Windows PowerShell:
     $env:DB_DATABASE="ptkp_v2_testing"; php artisan migrate
     ```

4. **Izin Berkas (Storage & Cache Directory)**:
   - Pastikan direktori `storage` dan `bootstrap/cache` memiliki izin tulis:
     ```bash
     php artisan storage:link
     ```

---
*© 2026 PT Kertas Padalarang — Peruri Security Paper Mill. Tim Pengembang Sistem Informasi Terintegrasi.*
