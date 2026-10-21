# ADR-001: Pilihan Arsitektur Modular Monolith vs Microservices

## Status
Diterima (Accepted)

## Konteks
Ekosistem aplikasi internal PT Kertas Padalarang (PTKP) mencakup berbagai fungsi operasional perusahaan:
- Modul Inti / Master Data (Platform)
- Modul Kearsipan Surat & Dokumen (Arsip)
- Modul Layanan Internal & Pengajuan (Layanan)
- Modul Manajemen SDM & Penilaian (Sdm)
- Modul Pembelajaran & Sertifikasi (Pembelajaran)
- Modul Operasional Manufaktur Kertas Keamanan (Manufaktur)

Tim pengembang memerlukan arsitektur yang:
1. Memastikan batasan domain (boundaries) antar modul tetap tegas dan terisolasi.
2. Meminimalkan kompleksitas operasional infrastruktur (tidak memerlukan service mesh, distributed tracing, network latency, atau multi-cluster orchestration yang berat).
3. Mendukung konsistensi transaksi data antar domain secara atomik dan audit terpusat.
4. Memudahkan pengembangan, pengujian otomasi, dan deployment dalam satu repositori terpadu.

## Keputusan
Kami memutuskan untuk menggunakan **Modular Monolith** berbasis Laravel 11.

Setiap modul ditempatkan dalam namespace tersendiri di `app/Modules/{ModuleName}/` dengan struktur domain mandiri:
- `Contracts/`: Kontrak antarmuka publik yang boleh dikonsumsi oleh modul lain.
- `Services/`: Implementasi bisnis logika internal modul.
- `Models/`: Eloquent model yang memetakan tabel pada schema database modul tersebut.
- `Http/Controllers/`: Controller untuk melayani request HTTP / Inertia.
- `Policies/`: Aturan otorisasi berbasis izin dan unit scope.
- `Database/Migrations/`: Migrasi tabel terisolasi.
- `routes.php`: Rute internal modul.

### Aturan Komunikasi Antar Modul
- **DILARANG** mengimpor Model Eloquent antar modul secara langsung.
- Komunikasi antar modul **WAJIB** melalui Interface Kontrak Publik yang terdaftar di Service Container Laravel (misal: `DirectoryServiceInterface`, `NumberingServiceInterface`, `AuditServiceInterface`).
- Batasan arsitektur ini diverifikasi dan ditegakkan secara otomatis melalui **Deptrac** (`composer deptrac`).

## Konsekuensi
### Positif
- **Deployment Sederhana**: Seluruh ekosistem di-deploy sebagai satu unit aplikasi Laravel + Vite SSR.
- **Konsistensi ACID**: Transaksi database tetap dapat memanfaatkan transaksi database PostgreSQL lokal tanpa overhead 2-Phase Commit (2PC).
- **Performa Tinggi**: Tidak ada network overhead / serialization latency antar modul.
- **Enforcement Kuat**: Deptrac menjamin arsitektur tidak bocor seiring waktu.

### Negatif / Mitigasi
- Modul berjalan dalam satu runtime PHP. Jika terjadi unhandled fatal error di level kernel, seluruh aplikasi terdampak (dimitigasi dengan isolated error handling dan testing komprehensif).
- Skalabilitas horizontal dilakukan di tingkat aplikasi monolith secara keseluruhan, bukan per modul individual.
