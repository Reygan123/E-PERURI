# ADR-002: Penggunaan PostgreSQL Schema per Modul

## Status
Diterima (Accepted)

## Konteks
Dalam arsitektur modular monolith, persistensi data membutuhkan isolasi yang jelas agar skema data tiap modul tidak saling bertabrakan atau mengakses tabel secara sembarangan. 

Pilihan yang dipertimbangkan:
1. Satu database publik (`public`) dengan prefix nama tabel (misal: `platform_users`, `arsip_documents`).
2. Multiple database connection terpisah per modul.
3. Satu database PostgreSQL dengan pemisahan **PostgreSQL Schema** per modul (`platform`, `arsip`, `layanan`, `sdm`, `pembelajaran`, `manufaktur`).

## Keputusan
Kami memutuskan untuk menggunakan **PostgreSQL Schema per Modul** dalam satu database fisik `ptkp_v2`.

Setiap modul memiliki skema PostgreSQL tersendiri:
- `platform.*` (unit organisasi, jabatan, pegawai, penugasan, users, roles, permissions, user_roles, numbering, audit_logs, files, notifications)
- `arsip.*` (naskah dinas, surat masuk, surat keluar, disposisi, berkas arsip)
- `layanan.*` (tiket helpdesk, permohonan fasilitas, peminjaman)
- `sdm.*` (evaluasi kinerja, cuti, lembur, absensi)
- `pembelajaran.*` (pelatihan, sertifikasi, materi uji)
- `manufaktur.*` (order produksi, batch kertas keamanan, inspeksi mutu)

### Konvensi Model & Migrasi
- Setiap migrasi secara eksplisit menyebutkan nama skema:
  ```php
  Schema::create('platform.org_units', function (Blueprint $table) { ... });
  ```
- Setiap Eloquent Model secara eksplisit mendefinisikan properti `$table`:
  ```php
  protected $table = 'platform.org_units';
  ```
- Dalam validasi Laravel, nama koneksi tidak boleh membingungkan parser (gunakan `Rule::unique(OrgUnit::class, 'code')` daripada string `'unique:platform.org_units,code'`).

## Konsekuensi
### Positif
- **Isolasi Logis Kuat**: Tabel per modul terpisah rapi di level database engine.
- **Kemudahan Backup & Restore**: Tiap modul dapat di-dump atau di-restore secara independen (`pg_dump -n platform`).
- **Single Connection Pool**: Menggunakan satu koneksi database default tanpa beban multi-connection overhead di PHP/Laravel.
- **Cross-Schema Query Terkendali**: Masih memungkinkan foreign key antar skema atau join analitik reporting jika diotorisasi di level database.

### Negatif / Mitigasi
- Database SQLite tidak mendukung PostgreSQL schema natively (pengujian otomatis menggunakan database PostgreSQL uji atau mock container Docker).
