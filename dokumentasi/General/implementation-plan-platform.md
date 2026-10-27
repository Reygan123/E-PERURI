# Implementation Plan: Modul Platform PTKP v2

> **Status**: `v2` masih kosong — belum ada proyek Laravel  
> **Fokus saat ini**: Modul Platform (inti ekosistem)  
> **Referensi aturan**: `aturan/AGENTS.md`  
> **Referensi struktur org**: `dokumentasi/struktur-organisasi-ptkp.md`  
> **Terakhir diperbarui**: 21 Oktober 2026

---

## Prinsip Panduan

Sebelum mengerjakan apapun, ingat 3 prinsip dari `AGENTS.md`:
1. **Server adalah satu-satunya yang dipercaya** — tidak ada keputusan dari browser
2. **Arsitektur dulu, fitur kemudian** — hormati batas modul
3. **Bukti, bukan klaim** — tes harus dijalankan dan lulus

---

## Fase 0 — Inisialisasi Proyek & Infrastruktur

**Tujuan**: Menyiapkan fondasi proyek Laravel yang siap dikembangkan secara tim.

### 0.1 Buat Proyek Laravel

- [ ] Install Laravel terbaru (≥ 11) dengan Breeze/starter kit yang dikonfigurasi untuk Inertia + Vue
- [ ] Konfigurasi `.env.example` (tanpa nilai asli)
- [ ] Buat `.env` lokal untuk pengembangan (PostgreSQL, tidak di-commit)
- [ ] Inisialisasi Git repository, buat `.gitignore` lengkap

**Perintah yang akan dijalankan:**
```bash
composer create-project laravel/laravel . "^11.0"
composer require laravel/breeze --dev
php artisan breeze:install vue --ssr
# Konfigurasi Inertia.js
```

### 0.2 Konfigurasi Docker

- [ ] Buat `docker-compose.yml` dengan service: `app` (PHP-FPM), `nginx`, `postgres`, `redis`
- [ ] Satu database PostgreSQL, schema dipisah per modul
- [ ] Volume persistent untuk PostgreSQL
- [ ] File `.env.docker` terpisah

**Service Docker:**
```yaml
services:
  app:        # Laravel PHP-FPM
  nginx:      # Web server
  postgres:   # PostgreSQL (satu instance, multi-schema)
  redis:      # Cache & Queue
```

### 0.3 Konfigurasi Database PostgreSQL

- [ ] Buat semua schema yang diperlukan dalam satu migration awal:
  - `platform`, `arsip`, `layanan`, `elearning`, `sdm`, `mes`
- [ ] Konfigurasi `config/database.php` untuk mendukung multi-schema
- [ ] Buat database user per schema (opsional, untuk keamanan)

### 0.4 Tooling Kualitas Kode

- [ ] **Laravel Pint** — PHP formatter (PSR-12)
- [ ] **Larastan** — Static analysis (konfigurasi level yang disepakati)
- [ ] **Pest** — Testing framework
- [ ] **ESLint + Prettier** — Frontend linting
- [ ] **Deptrac** (atau Pest arch tests) — Tes batas modul
- [ ] Buat `.pint.json`, `phpstan.neon`, `deptrac.yaml`
- [ ] Konfigurasi GitHub Actions / CI pipeline

**Output Fase 0**: Proyek Laravel berjalan di Docker, semua schema PostgreSQL terbuat, semua tooling terkonfigurasi.

---

## Fase 1 — Arsitektur Modul Platform

**Tujuan**: Membangun kerangka modular dan scaffolding modul Platform.

### 1.1 Scaffolding Struktur Modular

- [ ] Buat struktur direktori dasar:
  ```
  app/
    Modules/
      Platform/
        Http/
          Controllers/
          Requests/
          Resources/
        Models/
        Policies/
        Services/
        Events/
        Providers/
        database/
          migrations/
          seeders/
        routes.php
  resources/js/Modules/Platform/Pages/
  ```
- [ ] Buat `PlatformServiceProvider` yang mendaftarkan:
  - Route dari `Modules/Platform/routes.php`
  - Migrasi dari `Modules/Platform/database/migrations/`
  - Policies
- [ ] Daftarkan `PlatformServiceProvider` di `bootstrap/providers.php`
- [ ] Buat test arsitektur awal (Pest arch test) untuk memastikan batas modul terjaga

### 1.2 Buat Interface Layanan Platform

Interface ini adalah kontrak yang akan dipakai modul lain:

- [ ] `interface DirectoryService` — query data org_unit & employee
- [ ] `interface NumberingService` — generate nomor dokumen
- [ ] `interface FilesService` — simpan & ambil berkas
- [ ] `interface AuditService` — catat audit log
- [ ] `interface NotificationService` — kirim notifikasi

Tempatkan di `app/Modules/Platform/Contracts/`.

**Output Fase 1**: Kerangka modul Platform tersedia, interface layanan terdefinisi.

---

## Fase 2 — Schema & Migrasi Platform

**Tujuan**: Membuat schema database `platform` yang lengkap dan benar.

> **Penting**: Kode migrasi yang ada di `ptkp-platform-core` **belum pernah diuji**. Jangan menyalin begitu saja — rancang ulang dari dokumen ini.

### 2.1 Tabel Organisasi (`platform.org_units`)

Berdasarkan `dokumentasi/struktur-organisasi-ptkp.md`:

```sql
platform.org_units
  id              uuid PRIMARY KEY
  parent_id       uuid REFERENCES platform.org_units(id) NULL
  code            varchar(20) UNIQUE NOT NULL      -- misal: DEP-SDM, PRD-PK1
  name            varchar(255) NOT NULL
  type            enum('directorate','staff_unit','department','section')
  level           smallint NOT NULL                -- 0=Dirut, 1=Dir/Staff, 2=Dep, 3=Seksi
  is_active       boolean DEFAULT true
  created_at      timestamptz
  updated_at      timestamptz
  deleted_at      timestamptz NULL                 -- soft delete
```

**37 unit** yang akan di-seed (berdasarkan SK 20 April 2026).

### 2.2 Tabel Jabatan (`platform.positions`)

```sql
platform.positions
  id              uuid PRIMARY KEY
  org_unit_id     uuid REFERENCES platform.org_units(id)
  code            varchar(20) UNIQUE NOT NULL
  name            varchar(255) NOT NULL
  level           smallint NOT NULL                -- grade jabatan
  is_active       boolean DEFAULT true
  created_at      timestamptz
  updated_at      timestamptz
```

### 2.3 Tabel Pegawai (`platform.employees`)

```sql
platform.employees
  id              uuid PRIMARY KEY
  nip             varchar(20) UNIQUE NOT NULL      -- Nomor Induk Pegawai
  name            varchar(255) NOT NULL
  email           varchar(255) UNIQUE NOT NULL
  phone           varchar(20) NULL
  -- Data sensitif dienkripsi:
  nik             text NULL                        -- ENCRYPTED: KTP
  birth_date      date NULL
  gender          enum('male','female') NULL
  is_active       boolean DEFAULT true
  created_at      timestamptz
  updated_at      timestamptz
  deleted_at      timestamptz NULL
```

### 2.4 Tabel Penugasan (`platform.employee_assignments`)

Pola **riwayat bertanggal** (bukan overwrite):

```sql
platform.employee_assignments
  id              uuid PRIMARY KEY
  employee_id     uuid REFERENCES platform.employees(id)
  org_unit_id     uuid REFERENCES platform.org_units(id)
  position_id     uuid REFERENCES platform.positions(id)
  started_at      date NOT NULL
  ended_at        date NULL                        -- NULL = masih aktif
  assignment_type enum('permanent','temporary','acting')
  notes           text NULL
  created_by      uuid REFERENCES platform.employees(id)
  created_at      timestamptz
```

### 2.5 Tabel User (`platform.users`)

```sql
platform.users
  id              uuid PRIMARY KEY
  employee_id     uuid UNIQUE REFERENCES platform.employees(id)
  username        varchar(50) UNIQUE NOT NULL
  password        varchar(255) NOT NULL            -- Hash::make (Argon2id)
  must_change_pwd boolean DEFAULT true
  last_login_at   timestamptz NULL
  is_active       boolean DEFAULT true
  created_at      timestamptz
  updated_at      timestamptz
```

### 2.6 Tabel Peran & Izin (`platform.roles`, `platform.permissions`)

```sql
platform.roles
  id              uuid PRIMARY KEY
  name            varchar(100) UNIQUE NOT NULL     -- misal: 'kepala-seksi'
  label           varchar(255) NOT NULL
  created_at      timestamptz

platform.permissions
  id              uuid PRIMARY KEY
  name            varchar(255) UNIQUE NOT NULL     -- format: 'modul.sumber.aksi'
  label           varchar(255) NOT NULL
  module          varchar(50) NOT NULL             -- platform, arsip, dll.

platform.role_permissions
  role_id         uuid REFERENCES platform.roles(id)
  permission_id   uuid REFERENCES platform.permissions(id)
  scope           enum('own','unit','unit_tree','all')
  PRIMARY KEY (role_id, permission_id)

platform.user_roles
  id              uuid PRIMARY KEY
  user_id         uuid REFERENCES platform.users(id)
  role_id         uuid REFERENCES platform.roles(id)
  org_unit_id     uuid REFERENCES platform.org_units(id) NULL  -- scope unit
  valid_from      date NOT NULL
  valid_until     date NULL                        -- NULL = tidak berbatas
```

### 2.7 Tabel Penomoran (`platform.numbering_formats`, `platform.numbering_sequences`)

```sql
platform.numbering_formats
  id              uuid PRIMARY KEY
  code            varchar(50) UNIQUE NOT NULL      -- misal: 'surat-keluar'
  template        varchar(255) NOT NULL            -- misal: '{PREFIX}/{SEQ}/{BULAN}/{TAHUN}'
  reset_period    enum('never','yearly','monthly')
  prefix          varchar(50) NULL
  module          varchar(50) NOT NULL

platform.numbering_sequences
  id              uuid PRIMARY KEY
  format_id       uuid REFERENCES platform.numbering_formats(id)
  period_key      varchar(20) NOT NULL             -- misal: '2026', '2026-10'
  last_sequence   integer DEFAULT 0
  UNIQUE (format_id, period_key)
```

### 2.8 Tabel Audit Log (`platform.audit_logs`)

```sql
platform.audit_logs
  id              uuid PRIMARY KEY
  user_id         uuid NULL                        -- NULL jika sistem
  employee_id     uuid NULL
  action          varchar(100) NOT NULL            -- 'create','update','delete','login',dll
  module          varchar(50) NOT NULL
  entity_type     varchar(100) NOT NULL            -- nama model/tabel
  entity_id       uuid NULL
  before          jsonb NULL
  after           jsonb NULL
  ip_address      inet NULL
  user_agent      text NULL
  created_at      timestamptz NOT NULL
  -- APPEND-ONLY: tidak ada UPDATE atau DELETE
  -- Dipartisi per tahun
```

### 2.9 Tabel Berkas (`platform.files`)

```sql
platform.files
  id              uuid PRIMARY KEY
  uploader_id     uuid REFERENCES platform.employees(id)
  original_name   varchar(255) NOT NULL            -- hanya metadata
  stored_name     varchar(255) NOT NULL            -- nama acak, di luar web root
  disk            varchar(50) DEFAULT 'local'
  path            varchar(500) NOT NULL
  mime_type       varchar(100) NOT NULL            -- dideteksi dari isi file
  size_bytes      bigint NOT NULL
  entity_type     varchar(100) NULL                -- polymorphic
  entity_id       uuid NULL
  created_at      timestamptz
  deleted_at      timestamptz NULL
```

### 2.10 Tabel Notifikasi (`platform.notifications`)

```sql
platform.notifications
  id              uuid PRIMARY KEY
  user_id         uuid REFERENCES platform.users(id)
  type            varchar(100) NOT NULL
  channel         enum('database','email','whatsapp')
  payload         jsonb NOT NULL
  read_at         timestamptz NULL
  sent_at         timestamptz NULL
  created_at      timestamptz
```

**Output Fase 2**: Semua migrasi tersedia dan dapat berjalan di database kosong.

---

## Fase 3 — Seeder & Data Master

**Tujuan**: Mengisi data awal yang diperlukan untuk pengembangan.

### 3.1 Seeder Org Units (37 unit dari SK)

- [ ] Seed semua unit dari `dokumentasi/struktur-organisasi-ptkp.md`
- [ ] Gunakan data dummy (BUKAN data pegawai asli)
- [ ] Kode unit mengikuti tabel referensi yang disepakati

### 3.2 Seeder Peran Awal

- [ ] Buat peran-peran dasar: `super-admin`, `admin-sdm`, `pimpinan`, `pegawai`
- [ ] Izin-izin awal untuk modul Platform

### 3.3 Seeder User Development

- [ ] Minimal 1 super-admin (data dummy)
- [ ] Minimal beberapa pegawai dummy per unit untuk keperluan testing

**Output Fase 3**: Database dev terisi data dummy yang cukup untuk pengembangan fitur.

---

## Fase 4 — Implementasi Layanan Platform

**Tujuan**: Membangun kelas-kelas layanan inti yang akan dipakai semua modul.

### 4.1 DirectoryService

- [ ] `getEmployee(uuid $id): Employee`
- [ ] `getOrgUnit(uuid $id): OrgUnit`
- [ ] `getEmployeesInUnit(uuid $orgUnitId, bool $includeSubunits): Collection`
- [ ] `getCurrentAssignment(uuid $employeeId): EmployeeAssignment`
- [ ] Implementasi via interface `DirectoryService`

### 4.2 NumberingService

- [ ] `generate(string $formatCode, ?string $periodKey = null): string`
- [ ] Atomik: dikunci di dalam transaksi database
- [ ] Fungsi database (stored function) untuk menghindari race condition
- [ ] **Wajib ada tes konkurensi**

### 4.3 FilesService

- [ ] `store(UploadedFile $file, string $entityType, uuid $entityId): File`
- [ ] `download(uuid $fileId, User $user): StreamedResponse`
- [ ] Validasi tipe dari isi file (bukan ekstensi)
- [ ] Simpan di luar web root

### 4.4 AuditService

- [ ] `log(string $action, string $entityType, ?uuid $entityId, ?array $before, ?array $after): void`
- [ ] Append-only, tidak ada update/delete
- [ ] Auto-inject `user_id` dari `auth()->user()`

### 4.5 NotificationService

- [ ] `send(User $user, string $type, array $payload, array $channels): void`
- [ ] Channel: `database` (selalu), `email` (opsional), `whatsapp` (abstraksi saja dulu)

**Output Fase 4**: Semua layanan Platform siap dipakai modul lain.

---

## Fase 5 — Authentication & Authorization

**Tujuan**: Sistem login, sesi, dan izin berbasis peran dengan scope.

### 5.1 Authentication

- [ ] Login via `username` + `password`
- [ ] Rate limiting pada endpoint login
- [ ] Force change password saat `must_change_pwd = true`
- [ ] Log audit setiap login (berhasil & gagal)
- [ ] Session timeout konfigurabel

### 5.2 Authorization (Policy + Gate)

- [ ] Buat `PlatformPolicy` untuk setiap model
- [ ] Implementasi scope: `own`, `unit`, `unit_tree`, `all`
- [ ] Global scope pada query yang membatasi data sesuai scope user
- [ ] **Deny by default** — tidak ada izin eksplisit = ditolak

### 5.3 Halaman Auth Frontend (Vue + Inertia)

- [ ] Halaman Login (`/login`) — menggunakan palet Navy + Mist sesuai brand guideline
- [ ] Halaman Ganti Password Wajib
- [ ] Layout shell utama: **sidebar Deep Navy** di kiri, header putih/Navy, area konten Mist
- [ ] Logo PERURI di header/sidebar (minimal 120px lebar, latar putih atau Mist)

**Output Fase 5**: User bisa login, sesi aman, izin bekerja dengan scope.

---

## Fase 6a — Design System & Komponen UI

**Tujuan**: Membangun fondasi visual yang konsisten berdasarkan Brand Guideline PT Kertas Padalarang sebelum membangun halaman-halaman fitur.

> Referensi lengkap: `aturan/AGENTS.md` section 9a — Panduan UI/UX Brand Guideline PTKP

### 6a.1 Design Token (CSS Variables)

Buat satu file `resources/css/tokens.css` yang mendefinisikan semua token:

```css
:root {
  /* === WARNA === */
  --color-navy:       #20288E;  /* Peruri Navy — elemen utama */
  --color-violet:     #D861FF;  /* Spark Violet — aksen saja */
  --color-deep-navy:  #141A5C;  /* Deep Navy — sidebar, hover */
  --color-mist:       #EEF0FB;  /* Mist — latar halaman */
  --color-white:      #FFFFFF;
  --color-text:       #20288E;  /* heading */
  --color-text-body:  #1A1A2E;  /* paragraf */
  --color-border:     rgba(32, 40, 142, 0.14);

  /* === TIPOGRAFI === */
  --font-heading: 'Lexend', sans-serif;
  --font-body:    'Inter', sans-serif;

  /* === SPACING (kelipatan 4px) === */
  --space-1: 4px;
  --space-2: 8px;
  --space-3: 12px;
  --space-4: 16px;
  --space-5: 24px;
  --space-6: 32px;
  --space-7: 48px;
  --space-8: 64px;

  /* === BORDER RADIUS (Seamless Concentric Hierarchy) === */
  --radius-xs:   4px;    /* micro-chips, status dots */
  --radius-sm:   6px;    /* input, select, textarea, filter chips */
  --radius-md:   8px;    /* tombol, alert, item navigasi */
  --radius-lg:   12px;   /* kartu utama, modal, table wrapper */
  --radius-pill: 999px;  /* badge/tag saja */

  /* === BAYANGAN & ELEVASI === */
  --shadow-flat:   0 0 0 1px var(--color-border);
  --shadow-card:   0 1px 3px rgba(20, 26, 92, 0.04), 0 1px 2px rgba(20, 26, 92, 0.02);
  --shadow-raised: 0 4px 16px rgba(20, 26, 92, 0.08);
}
```

- [x] Buat `resources/css/tokens.css` dengan semua design token di atas
- [x] Import di `app.css` / entry point CSS utama
- [x] Import Google Fonts: Lexend (700) dan Inter (400, 500)

### 6a.2 Komponen Vue Dasar (Shared Components)

Buat di `resources/js/Components/` (bukan di dalam modul):

**Komponen Wajib:**
- [x] `AppButton.vue` — 4 varian: Utama, Sekunder, Aksen, Teks (radius-md 8px)
- [x] `AppInput.vue` — input text dengan label, error state, placeholder (radius-sm 6px)
- [x] `AppTextarea.vue` — textarea dengan label dan error (radius-sm 6px)
- [x] `AppSelect.vue` — dropdown select terstandar (radius-sm 6px)
- [x] `AppCard.vue` — kartu dengan border 1px, radius-lg (12px), elevasi halus shadow-card
- [x] `AppBadge.vue` — badge pill dengan varian warna (navy, neutral)
- [x] `AppAlert.vue` — notifikasi 4 varian (info/sukses/peringatan/error, radius-md 8px)
- [x] `AppModal.vue` — modal dengan radius-lg (12px), shadow-raised
- [x] `AppTable.vue` — tabel dengan header navy, border subtle, table wrapper seamless
- [x] `AppPagination.vue` — paginasi standar (radius-sm 6px)

**Aturan komponen:**
- Semua komponen memakai CSS variables (`var(--color-navy)`, dll.) dan utilitas Tailwind CSS terkonfigurasi.
- Komponen didokumentasikan dengan contoh props dan slot.

### 6a.3 Shell Aplikasi (AppShell)

- [x] `AppShell.vue` — layout utama: sidebar + header + slot konten
- [x] `AppSidebar.vue` — latar Deep Navy (#141A5C), navigasi modul, item aktif highlight
- [x] `AppHeader.vue` — logo PERURI (min 120px), breadcrumb, menu user
- [x] `AppNavItem.vue` — item navigasi sidebar dengan ikon dan label

**Spesifikasi Shell:**

```
┌─────────────────────────────────────────────────────┐
│  HEADER (bg: white/navy, logo kiri, user menu kanan)│
├──────────────┬──────────────────────────────────────┤
│  SIDEBAR     │  KONTEN UTAMA                        │
│  (bg: Deep   │  (bg: Mist #EEF0FB)                  │
│   Navy)      │  max-width: 1280px                   │
│  w: 240px    │  padding: 32px                       │
│              │                                      │
└──────────────┴──────────────────────────────────────┘
```

### 6a.4 Halaman Kosong & State Kosong

- [x] Komponen `AppEmptyState.vue` — ilustrasi state kosong (mengacu elemen grafis atap limas)
- [x] Komponen `AppLoadingState.vue` — skeleton loader warna Mist/Navy

### 6a.5 Validasi Visual Konsistensi

Sebelum melanjutkan ke Fase 6, lakukan review visual:
- [x] Buka semua komponen di browser dan verifikasi warna sesuai token
- [x] Pastikan tidak ada hardcode warna atau spasi di luar token
- [x] Pastikan font Lexend tampil pada heading dan Inter pada body
- [x] Pastikan tidak ada `border-radius` > 8px pada komponen selain badge

**Output Fase 6a**: Design system siap pakai — semua halaman berikutnya membangun di atas komponen ini.

---

## Fase 6 — CRUD Master Data Platform

**Tujuan**: Antarmuka admin untuk mengelola master data.

### 6.1 Manajemen Unit Organisasi

- [x] Daftar unit dengan tampilan hierarki (tree)
- [x] Tambah / Edit / Nonaktifkan unit
- [x] Policy: hanya admin yang berwenang

### 6.2 Manajemen Jabatan

- [x] Daftar jabatan per unit
- [x] Tambah / Edit / Nonaktifkan jabatan

### 6.3 Manajemen Pegawai

- [x] Daftar pegawai dengan filter unit
- [x] Tambah pegawai baru (dengan user account otomatis, password sementara)
- [x] Edit data pegawai (non-sensitif)
- [x] Riwayat penugasan

### 6.4 Manajemen Peran & Izin

- [x] Daftar peran
- [x] Assign izin ke peran (dengan pilihan scope)
- [x] Assign peran ke user (dengan scope unit dan masa berlaku)

### 6.5 Penomoran Dokumen

- [x] Konfigurasi format nomor per jenis dokumen
- [x] Preview format nomor

**Output Fase 6**: Admin dapat mengelola semua master data dari antarmuka.

---

## Fase 7 — Pengujian Komprehensif

**Tujuan**: Memastikan semua fitur Platform lulus definisi of done.

### 7.1 Tes yang Wajib Ada

Untuk setiap fitur dari Fase 5–6:

| Jenis Tes | Cakupan |
|-----------|---------|
| Feature (HTTP) | Jalur sukses semua endpoint |
| Unauthorized | Tanpa login → 401/redirect |
| Forbidden | Login tapi tidak punya izin → 403 |
| IDOR | Data unit lain → 403/404 |
| Validasi | Input tidak valid → 422 |
| Unit | Logic di Service/Action |

### 7.2 Tes Khusus

- [x] **Tes konkurensi penomoran** — 50 request bersamaan, tidak boleh ada nomor kembar (`ConcurrencyNumberingTest`)
- [x] **Tes scope** — query tidak boleh mengembalikan data di luar scope user (`PlatformAuthorizationMatrixTest`)
- [x] **Tes audit log** — setiap aksi sensitif tercatat dengan benar dan immutable
- [x] **Tes arsitektur** — tidak ada import silang antar modul (`composer deptrac`)
- [x] **Tes policy** — setiap kombinasi scope (own/unit/unit_tree/all)

### 7.3 Static Analysis

- [x] Larastan lulus di level yang ditetapkan (Level 5 — 0 errors)
- [x] Pint lulus tanpa error (`composer lint:test`)
- [x] Vue TypeScript build & SSR bundle lulus (`npm run build`)

**Output Fase 7**: Semua tes hijau, static analysis lulus (54 tests, 222 assertions).

---

## Fase 8 — Dokumentasi & ADR

**Tujuan**: Mendokumentasikan keputusan arsitektur dan panduan modul.

### 8.1 Architecture Decision Records (ADR)

Telah dibuat di `docs/adr/`:

| ADR | Topik | Status |
|-----|-------|:---:|
| `adr-001` | Pilihan modular monolith vs microservice | ✅ Diterima |
| `adr-002` | Penggunaan PostgreSQL schema per modul | ✅ Diterima |
| `adr-003` | Mekanisme penomoran atomik dengan row locking | ✅ Diterima |
| `adr-004` | Pola riwayat bertanggal untuk mutasi pegawai | ✅ Diterima |
| `adr-005` | Enkripsi kolom untuk data sensitif pegawai | ✅ Diterima |
| `adr-006` | Scope otorisasi (own/unit/unit_tree/all) | ✅ Diterima |

### 8.2 README Modul Platform

- [x] Tujuan modul Platform (`app/Modules/Platform/README.md`)
- [x] Layanan yang disediakan (beserta interface-nya)
- [x] Izin yang didefinisikan (format `platform.*.*`)
- [x] Cara menggunakan layanan dari modul lain

### 8.3 Pemeliharaan Skill References

Skill `ptkp-conventions` mereferensikan dua file yang selalu sinkron dengan AGENTS.md:

| File | Isi | Kapan diperbarui |
|------|-----|------------------|
| `.agents/skills/ptkp-conventions/references/authorization.md` | Ringkasan implementasi policy, scope, tes wajib | Setiap kali ada perubahan model otorisasi |
| `.agents/skills/ptkp-conventions/references/ui-brand.md` | Ringkasan design token, komponen, shell, larangan | Setiap kali ada token baru atau aturan visual berubah |

> **Penting**: AGENTS.md adalah sumber kebenaran. Jika ada konflik, AGENTS.md menang. Perbarui references/ agar tetap sinkron setelah mengubah AGENTS.md.

**Output Fase 8**: Dokumentasi lengkap tersedia untuk developer berikutnya.

---

## Urutan Prioritas & Estimasi

```
Fase 0:  Inisialisasi              [Prioritas: SEKARANG]    ~1–2 hari
Fase 1:  Arsitektur Modul          [Prioritas: SEKARANG]    ~1 hari
Fase 2:  Schema & Migrasi          [Prioritas: TINGGI]      ~2–3 hari
Fase 3:  Seeder                    [Prioritas: TINGGI]      ~1 hari
Fase 4:  Layanan Platform          [Prioritas: TINGGI]      ~3–4 hari
Fase 5:  Auth & Otorisasi          [Prioritas: TINGGI]      ~3–4 hari
Fase 6a: Design System & UI        [Prioritas: TINGGI]      ~2–3 hari  ← BARU
Fase 6:  CRUD Master Data          [Prioritas: MENENGAH]    ~4–5 hari
Fase 7:  Pengujian                 [Berjalan sepanjang]     ~ongoing
Fase 8:  Dokumentasi & ADR         [Berjalan sepanjang]     ~ongoing
```

> **Catatan**: Fase 6a (Design System) dikerjakan **sebelum** Fase 6. Semua halaman CRUD di Fase 6 wajib membangun di atas komponen dari Fase 6a. Jangan membangun halaman tanpa design system terlebih dahulu.

---

## Hal yang Belum Diputuskan (Blokir Sebagian Fase)

> Jangan melanjutkan pekerjaan terkait item ini sampai ada keputusan eksplisit.

| Item | Dampak | Keterangan |
|------|--------|------------|
| Kode resmi unit organisasi | Fase 3 (seeder) | Usulan kode sudah ada, perlu konfirmasi PTKP |
| Aturan scope hak akses E-Arsip | Fase 5 (policy) | Ditunda, jangan diasumsikan |
| Format data impor HRIS | Fase 6 (migrasi data lama) | Belum ada dokumen |
| Integrasi gateway WhatsApp | Fase 4.5 | Siapkan abstraksi saja |
| Ukuran VM produksi | Fase 0 (Docker) | Untuk lokal tidak jadi masalah |
| **Token warna notifikasi** (sukses/peringatan/error/badge netral) | Fase 6a (Design System) | Belum ada token resmi — jangan hardcode, usulkan dan minta persetujuan |

---

## Checklist Sebelum Mulai Coding

- [ ] Baca `aturan/AGENTS.md` dan skill `.agents/skills/ptkp-conventions/SKILL.md`
- [ ] Baca `references/authorization.md` sebelum membuat policy atau scope
- [ ] Baca `references/ui-brand.md` sebelum membuat atau mengubah komponen Vue
- [ ] Verifikasi Docker Desktop berjalan dan PostgreSQL bisa diakses
- [ ] Verifikasi versi PHP ≥ 8.3 tersedia
- [ ] Verifikasi Node.js ≥ 20 tersedia
- [ ] Verifikasi Composer tersedia
- [ ] Konfirmasi kode unit organisasi dengan stakeholder PTKP
- [ ] Sepakati level Larastan yang akan dipakai
- [ ] Usulkan token warna notifikasi (sukses/peringatan/error/badge) dan minta persetujuan sebelum Fase 6a
