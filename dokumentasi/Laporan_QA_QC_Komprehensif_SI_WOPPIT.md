# LAPORAN QUALITY ASSURANCE & QUALITY CONTROL KOMPREHENSIF
## SISTEM INFORMASI WOPPIT (WORK ORDER PERBAIKAN PERANGKAT IT)
### PENGUJIAN END-TO-END MULTI-ROLE & PUSAT NOTIFIKASI IN-APP
**PT KERTAS PADALARANG (PERURI SECURITY PAPER MILL)**

---

| Parameter Dokumen | Informasi Spesifikasi |
| :--- | :--- |
| **Nomor Dokumen** | `QA-QC/PTKP-TI/WOPPIT-MULTIKOLAB/2026/001` |
| **Tanggal Pengujian** | 27 Oktober 2026 |
| **Sistem Teruji** | Sistem Informasi Work Order Perbaikan Perangkat IT (**SI WOPPIT**) & In-App Notification Center |
| **Modul Aplikasi** | Modul `Layanan` (Work Orders) terintegrasi dengan Modul `Platform` (Auth, RBAC, Notifikasi) |
| **Versi Rilis** | `v2.0.0-PROD-RC` |
| **Aktor Pengujian** | 1. **Pemohon / Staf Unit Lain:** Fajar Nugraha, S.T. (`stafprd@kertas-padalarang.co.id` - Staf Departemen Produksi)<br>2. **Tim Teknisi IT:** Super Administrator / Farhan Nugraha, S.Kom. (`superadmin@kertas-padalarang.co.id` - Staf IT) |
| **Status Kelayakan** | **PASSED 100% (LAYAK PRODUKSI / READY FOR GO-LIVE)** |

---

## 1. RINGKASAN EKSEKUTIF & METRIK MUTU WOPPIT

Pengujian Quality Assurance (QA) dan Quality Control (QC) ini berfokus secara mendalam pada **Sistem Informasi WOPPIT (Work Order Perbaikan Perangkat IT)** untuk membuktikan keandalan kolaborasi lintas peran (*multi-role user journey*), validitas penomoran tiket antrean, efektivitas sistem peringatan (*in-app notification center*), dan integritas lembar kerja fisik standar ISO (Form IT-FR-01).

### Metrik Pengujian:
- **Automated Feature Tests (LayananWorkOrderTest & NotificationFeatureTest):** **18 Skenario Lulus 100%** (**115 Assertions**, 0 Gagal).
- **Static Code Analysis (Larastan Level 5):** **0 Error** pada modul `Layanan` dan service-service pendukungnya.
- **Deptrac Architectural Layering:** **0 Violations** (Modul Layanan tidak pernah mengakses entitas SDM secara langsung, berinteraksi via Platform Contracts).
- **Alur Multi-Role Lintas Departemen:** Pemohon (Staf Produksi) $\rightarrow$ Notifikasi In-App Tim IT $\rightarrow$ Teknisi IT Mendiagnosa $\rightarrow$ Notifikasi Update ke Pemohon $\rightarrow$ Teknisi Menyelesaikan $\rightarrow$ Notifikasi Selesai ke Pemohon $\rightarrow$ Lembar Cetak A4 IT-FR-01.

---

## 2. ARSITEKTUR & KEAMANAN SISTEM WOPPIT

1. **Pemisahan Skema Database:** Tabel transaksi `layanan.work_orders` dan riwayat tindakan teknisi `layanan.work_order_actions` terisolasi di skema Postgres `layanan`. Relasi ke pengguna dan unit kerja hanya melalui UUID referensial.
2. **Pencegahan Celah IDOR (Insecure Direct Object Reference):** Diimplementasikan melalui `WorkOrderPolicy`:
   - Pegawai biasa hanya dapat melihat tiket yang dibuat oleh dirinya sendiri atau di unit kerjanya.
   - Hak akses memproses (*process*) dan menyelesaikan (*complete*) perbaikan dikunci secara mutlak hanya untuk personel ber-role `super-admin`, `admin-it`, `teknisi-it`, atau pimpinan berwenang. Pegawai dari departemen luar dilarang memproses tiketnya sendiri.
3. **Pencegahan Kondisi Lomba (Race Condition Penomoran Tiket):**
   - Menggunakan `DB::transaction` terkunci (*pessimistic lock*) dan atomic sequence PostgreSQL untuk penerbitan nomor tiket (`IT001`, `IT002`, dst.), mengeliminasi celah *duplicate key violation* (`SQLSTATE 23505`).
4. **Sanitasi Input & Keamanan Berkas Foto Kerusakan:**
   - Divalidasi via `StoreWorkOrderRequest` dengan aturan ketat: jenis hardware valid, panjang deskripsi minimal, MIME-type foto (JPG/PNG/WEBP), dan kuota maksimal 5MB. Berkas di-stream privat untuk mencegah eksekusi skrip backdoor.

---

## 3. MATRIKS ALUR PENGUJIAN MULTI-ROLE & NOTIFIKASI IN-APP

| Langkah | Aktor / Peran | Halaman / Fitur | Aksi Pengujian | Hasil yang Diamati | Status |
| :---: | :--- | :--- | :--- | :--- | :---: |
| **01** | Staf Produksi | `/login` | Otentikasi sesi menggunakan akun `stafprd@kertas-padalarang.co.id` | Login berhasil, dialihkan ke portal | **PASS** |
| **02** | Staf Produksi | `/layanan/work-orders/create` | Mengisi formulir: lokasi ruangan, memilih kategori Printer, nama alat, memo dinas, deskripsi gangguan, dan upload foto kerusakan | Input form tervalidasi reaktif, pratinjau live tiket aktif | **PASS** |
| **03** | Staf Produksi | `/layanan/work-orders/create` | Mengklik tombol "Kirim Permohonan Perbaikan IT" | Tiket tersimpan di basis data, nomor tiket otomatis diterbitkan, dialihkan ke detail tiket | **PASS** |
| **04** | Tim Teknisi IT | `/dashboard` (Navbar Header) | Login sebagai `superadmin`, memeriksa lonceng notifikasi | **In-app Notification Popup Muncul:** Badge counter merah menyala, rincian notifikasi tiket baru dari Staf Produksi terpampang jelas | **PASS** |
| **05** | Tim Teknisi IT | `/layanan/work-orders` | Membuka Papan Antrean Work Order IT | Tiket baru masuk di kolom status "Menunggu Tindakan (Pending)" secara real-time | **PASS** |
| **06** | Tim Teknisi IT | `/layanan/work-orders/{id}` | Mengklik tombol "Mulai Penanganan", menginput diagnosa teknis dan mengklik "Ambil Tiket" | Status tiket berubah menjadi `in_progress`, modal konfirmasi tertutup | **PASS** |
| **07** | Staf Produksi | `/notifications` | Staf Produksi memeriksa pusat notifikasi in-app | **Notifikasi Masuk:** "Tiket Diproses: IT### - Mulai ditangani oleh teknisi IT." | **PASS** |
| **08** | Tim Teknisi IT | `/layanan/work-orders/{id}` | Teknisi IT mengklik tombol "Selesaikan Servis", mencatat tindakan perbaikan tuntas | Status tiket berubah menjadi `completed` dengan timestamp penyelesaian | **PASS** |
| **09** | Staf Produksi | `/notifications` | Staf Produksi memeriksa pusat notifikasi in-app | **Notifikasi Masuk:** "Perbaikan Selesai: IT### - Perangkat siap diserahterimakan." | **PASS** |
| **10** | Kedua Pihak | `/layanan/work-orders/{id}/print` | Membuka lembar kerja resmi cetak A4 | Layout A4 presisi, kop resmi Peruri SPM, matriks 3 tanda tangan (Pelapor, Teknisi, Pimpinan) | **PASS** |

---

## 4. BUKTI VISUAL TANGKAPAN LAYAR (SCREENSHOT WALKTHROUGH)

1. **Layar 1 (Login Pemohon):** Staf Produksi mengakses pintu masuk portal resmi (`01_woppit_requester_login.png`).
2. **Layar 2 (Form Pengajuan):** Staf Produksi mengisi data gangguan printer dan foto kerusakan (`02_woppit_requester_form_filled.png`).
3. **Layar 3 (Tiket Terbit):** Tiket resmi berstatus *Pending* berhasil diterbitkan (`03_woppit_requester_ticket_submitted.png`).
4. **Layar 4 (Notifikasi Tim IT):** Pop-up notifikasi in-app tiket baru pada akun teknisi IT (`04_woppit_it_notification_popup.png`).
5. **Layar 5 (Papan Antrean IT):** Dashboard antrean tiket servis menampilkan order baru (`05_woppit_it_queue_board.png`).
6. **Layar 6 (Modal Diagnosa Teknisi):** Input diagnosa awal teknisi dan penugasan perbaikan (`06_woppit_it_modal_process.png`).
7. **Layar 7 (Notifikasi Progres ke Pemohon):** Feed notifikasi pada akun Staf Produksi saat tiket mulai dikerjakan (`07_woppit_requester_notification_progress.png`).
8. **Layar 8 (Penyelesaian Servis):** Teknisi menyelesaikan servis dan mencatat rekomendasi pemeliharaan (`08_woppit_it_finish_repair_modal.png`).
9. **Layar 9 (Notifikasi Selesai ke Pemohon):** Feed notifikasi pada akun Staf Produksi saat perangkat siap diambil (`09_woppit_requester_notification_completed.png`).
10. **Layar 10 (Lembar Cetak A4 IT-FR-01):** Dokumen lembar kerja fisik resmi standar ISO berlogo Peruri SPM (`10_woppit_print_a4_official.png`).

---

## 5. KESIMPULAN & PENGESAHAN KELAYAKAN RILIS (SIGN-OFF)

Sistem Informasi WOPPIT (Work Order Perbaikan Perangkat IT) dinyatakan **LULUS 100% (PASSED)** dan memenuhi standar mutu perangkat lunak korporat BUMN:
- Alur lintas departemen (Pemohon $\leftrightarrow$ Teknisi IT) sinkron dan transparan.
- Sistem notifikasi in-app real-time bekerja akurat tanpa kegagalan pengiriman.
- Ketahanan keamanan otorisasi anti-IDOR dan atomisitas penomoran terbukti tangguh.

**STATUS: READY FOR PRODUCTION GO-LIVE.**
