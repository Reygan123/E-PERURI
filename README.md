# Ekosistem Aplikasi Internal & E-Arsip PT Kertas Padalarang (PTKP)
**Peruri Security Paper Mill — Versi 2 (Modular Monolith)**

Sistem Enterprise Resource Management terintegrasi PT Kertas Padalarang berbasis Laravel 11 Modular Monolith, Vue 3 + Inertia.js, Tailwind CSS, dan PostgreSQL 16 (Multi-Schema).

---

## 🚀 Panduan Cepat Menjalankan Aplikasi (Quick Start)

Panduan instalasi dan menjalankan sistem secara lengkap dan terstruktur telah didokumentasikan di berkas:
👉 **[CARA_RUNNING.md](file:///c:/Project/PERURI/Project/v2/CARA_RUNNING.md)**

Ringkasan perintah untuk menjalankan di lokal:
```bash
# 1. Jalankan container PostgreSQL & Redis
docker compose up -d

# 2. Setup backend & database
composer install
php artisan key:generate
php artisan migrate:fresh --seed

# 3. Setup frontend
npm install
npm run build

# 4. Jalankan aplikasi
php artisan serve --port=8088
# Di terminal terpisah untuk hot-reload frontend & queue worker:
npm run dev
php artisan queue:work
```

---

## 📚 Modul Sistem

Sistem ini dirancang menggunakan arsitektur **Modular Monolith** dengan isolasi skema PostgreSQL:

1. **Platform (`platform`)**: Autentikasi, otorisasi RBAC berlingkup unit (*Scoped Roles*), direktori pegawai 39 unit organisasi, manajemen jabatan berjenjang, penomoran naskah dinas atomik, dan audit log tidak terhapuskan (*immutable*).
2. **E-Arsip (`arsip`)**: Registrasi & agenda surat masuk/keluar, disposisi berjenjang, siklus nota dinas & persetujuan, penanganan revisi naskah berjenjang, Tanda Tangan Elektronik (TTE BSrE BSSN) terintegrasi QR Code vektor, dan portal verifikasi dokumen publik (`/verify/{hash}`).
3. **Layanan (`layanan`)**: Manajemen permohonan layanan & tiket helpdesk internal.
4. **Pembelajaran (`pembelajaran`)**: Portal pelatihan dan LMS internal pegawai.
5. **SDM (`sdm`)**: Administrasi kepegawaian, mutasi jabatan, dan cuti.
6. **Manufaktur (`manufaktur`)**: Pelacakan proses produksi kertas sekuriti.

---

## 🔑 Akun Uji Coba Default

Semua akun pengujian menggunakan kata sandi: `password`

- **Super Administrator:** `superadmin`
- **Sekretaris Direksi:** `stafdirut`
- **Pimpinan Arsip:** `kadivarsip`
- **Staf Arsip & Tata Usaha:** `stafarsip`
- **Pimpinan SDM:** `kadivsdm`
- **Pimpinan Keuangan:** `pimpkeu`

*Daftar lengkap 78 akun pengujian mencakup seluruh tingkatan unit kerja tersedia di [CARA_RUNNING.md](file:///c:/Project/PERURI/Project/v2/CARA_RUNNING.md) dan [Panduan UAT](file:///c:/Project/PERURI/Project/v2/dokumentasi/panduan-uat/Panduan_Pelaksanaan_UAT_E-Arsip.md).*

---

## 🛡️ Standar Kualitas & Quality Assurance (QA/QC)

Sistem telah diaudit secara menyeluruh dan memenuhi kriteria:
- **PHPStan / Larastan:** 0 errors
- **Deptrac Architecture:** 0 layer boundary violations
- **Laravel Pint:** 100% PSR-12 compliant
- **Automated Tests:** 86 tests passed (414 assertions)
- **Frontend Typings:** `vue-tsc` pass (0 errors)

---
*© 2026 PT Kertas Padalarang — Peruri Security Paper Mill. Hak cipta dilindungi undang-undang.*
