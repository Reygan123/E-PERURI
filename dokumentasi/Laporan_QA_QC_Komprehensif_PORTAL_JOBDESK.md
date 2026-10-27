# LAPORAN QUALITY ASSURANCE & QUALITY CONTROL KOMPREHENSIF
## PORTAL JOBDESK DIGITAL & DIREKTORI JABATAN KORPORAT
### PENGUJIAN END-TO-END MULTI-ROLE & PUSAT NOTIFIKASI IN-APP
**PT KERTAS PADALARANG (PERURI SECURITY PAPER MILL)**

---

| Parameter Dokumen | Informasi Spesifikasi |
| :--- | :--- |
| **Nomor Dokumen** | `QA-QC/PTKP-SDM/JOBDESK-MULTIKOLAB/2026/001` |
| **Tanggal Pengujian** | 27 Oktober 2026 |
| **Sistem Teruji** | Portal Jobdesk Digital & Direktori Jabatan Korporat & In-App Notification Center |
| **Modul Aplikasi** | Modul `Sdm` terintegrasi dengan Modul `Platform` (Auth, Numbering, RBAC, Notifikasi) |
| **Versi Rilis** | `v2.0.0-PROD-RC` |
| **Aktor Pengujian** | 1. **Penyusun / Maker SDM:** Admin SDM (`adminsdm@kertas-padalarang.co.id`)<br>2. **Penyetuju / Approver:** Dra. Sri Wahyuni, M.Psi. (`kadepsdm@kertas-padalarang.co.id` - Kepala Departemen SDM) |
| **Status Kelayakan** | **PASSED 100% (LAYAK PRODUKSI / READY FOR GO-LIVE)** |

---

## 1. RINGKASAN EKSEKUTIF & METRIK MUTU JOBDESK

Pengujian Quality Assurance (QA) dan Quality Control (QC) ini berfokus pada **Portal Jobdesk Digital & Direktori Jabatan Korporat (Modul Sdm)** untuk membuktikan keandalan tata kelola profil jabatan 10 seksi berstandar SK Direksi 009/KPTS/PTKP/IV/2026, validasi dynamic builder studio wizard 5-tab, integritas alur persetujuan berjenjang (*Maker-Checker-Approver*), pengiriman notifikasi in-app otomatis, penomoran atomik dokumen resmi, dan pengesahan digital TTE ber-hashing SHA-256.

### Metrik Pengujian:
- **Automated Feature Tests (JobdeskFeatureTest, SdmJobDescriptionTest, JobdeskQaQcTest):** **26 Skenario Lulus 100%** (**248 Assertions**, 0 Gagal).
- **Static Code Analysis (Larastan Level 5):** **0 Error** pada modul `Sdm` dan service-service pendukungnya.
- **Deptrac Architectural Layering:** **0 Violations** (Modul Sdm terisolasi mandiri, tidak ada kebocoran relasi ke modul Layanan/Arsip).
- **Alur Multi-Role Struktural SDM:** Maker (Admin SDM) $\rightarrow$ Notifikasi In-App Pengajuan Review $\rightarrow$ Approver (Kadep SDM) Menelaah 10 Seksi $\rightarrow$ Pengesahan Dokumen dengan TTE $\rightarrow$ Notifikasi In-App Kelulusan ke Maker $\rightarrow$ Cetak ISO A4 Ber-watermark & QR Code.

---

## 2. ARSITEKTUR & KEAMANAN SISTEM JOBDESK

1. **Pemisahan Skema Database:** Seluruh tabel uraian jabatan `sdm.job_descriptions`, rincian tugas `sdm.jd_tasks`, akuntabilitas `sdm.jd_governances`, relasi kerja `sdm.jd_working_relations`, matriks kompetensi `sdm.jd_competencies`, dan riwayat persetujuan `sdm.jd_approvals` berada pada skema PostgreSQL `sdm`.
2. **Pencegahan Celah IDOR (Insecure Direct Object Reference):** Diimplementasikan melalui `JobDescriptionPolicy`:
   - Penyusunan draf dibatasi pada pengelola SDM dengan permission `sdm.manage` (`admin-sdm`).
   - Pengesahan dokumen (`approve`) dikunci mutlak hanya untuk pimpinan unit kerja yang berwenang (`sdm.approve` / `pimpinan`). User tanpa izin ditolak dengan HTTP 403 Forbidden.
3. **Penerbitan Nomor Dokumen Atomik & Hashing TTE BSrE:**
   - Saat dokumen disahkan pimpinan, `NumberingService` secara atomik menerbitkan nomor resmi berformat `001/JD/PTKP/SDM/IV/2026`.
   - Dibuat ringkasan kriptografis SHA-256 (`tte_hash`) yang mengikat ID dokumen, nomor dokumen, identitas penandatangan, dan timestamp ISO-8601 untuk menjamin auditabilitas tanda tangan elektronik digital.
4. **Sanitasi Dynamic Builder Array:**
   - Validasi payload multi-dimensi melalui `StoreJobDescriptionRequest` dan `UpdateJobDescriptionRequest`, memastikan butir tugas pokok, relasi internal/eksternal, dan rating Proficiency Level (PL 1 s.d. 5) tersimpan secara konsisten.

---

## 3. MATRIKS ALUR PENGUJIAN MULTI-ROLE & NOTIFIKASI IN-APP

| Langkah | Aktor / Peran | Halaman / Fitur | Aksi Pengujian | Hasil yang Diamati | Status |
| :---: | :--- | :--- | :--- | :--- | :---: |
| **01** | Admin SDM (Maker) | `/login` | Otentikasi sesi menggunakan akun `adminsdm@kertas-padalarang.co.id` | Login berhasil, hak akses Admin SDM aktif | **PASS** |
| **02** | Admin SDM (Maker) | `/sdm/jobdesk` | Membuka katalog direktori seluruh jabatan korporat | Direktori memuat tabel posisi, indikator status, filter unit kerja | **PASS** |
| **03** | Admin SDM (Maker) | `/sdm/jobdesk/create` | Mengisi Studio Wizard: Tab 1 (Posisi & Unit), Tab 2 (Summary & Tugas Pokok), Tab 3 (Wewenang), Tab 4 (Relasi Kerja), Tab 5 (Kompetensi PL 1–5) | Input formulir dinamis tersusun rapi, sub-detail tervalidasi | **PASS** |
| **04** | Admin SDM (Maker) | `/sdm/jobdesk/{id}` | Menyimpan draf dan mengklik tombol "Ajukan Review" | Status dokumen berubah menjadi `in_review`, tahap checker terbuat | **PASS** |
| **05** | Kadep SDM (Approver) | `/dashboard` (Navbar Header) | Login sebagai `kadepsdm@kertas-padalarang.co.id`, membuka lonceng notifikasi | **In-App Notification Popup Muncul:** Badge unread merah menyala, notifikasi "Draf Jobdesk Diajukan: [Jabatan]" dari Admin SDM tampil | **PASS** |
| **06** | Kadep SDM (Approver) | `/sdm/jobdesk/{id}` | Membuka dokumen lengkap untuk menelaah 10 seksi | 10 seksi utuh tersaji (Identitas, Bagan Struktur, Tugas Pokok, Wewenang, Tanggung Jawab, Relasi, Tantangan, PL 1-5, Spek, Kondisi) | **PASS** |
| **07** | Kadep SDM (Approver) | Modal Pengesahan Dokumen | Membuka modal pengesahan, mencatat instruksi kepatuhan SOTK 2026, dan mengklik "Sahkan Dokumen" | Dokumen resmi disahkan: status menjadi `approved`, nomor dokumen terbit, hash TTE dibuat | **PASS** |
| **08** | Admin SDM (Maker) | `/notifications` | Admin SDM memeriksa pusat notifikasi in-app | **Notifikasi Masuk:** "Jobdesk Resmi Disahkan: [Jabatan] - Nomor Dokumen: 001/JD/PTKP/SDM/IV/2026 dan TTE BSrE." | **PASS** |
| **09** | Kedua Pihak | `/sdm/jobdesk/{id}/print` | Mengakses lembar cetak dokumen resmi A4 | Dokumen ISO A4 siap cetak dengan watermark Peruri Security Paper Mill dan QR Code verifikasi TTE | **PASS** |

---

## 4. BUKTI VISUAL TANGKAPAN LAYAR (SCREENSHOT WALKTHROUGH)

1. **Layar 1 (Login Maker SDM):** Admin SDM masuk ke sistem (`01_jobdesk_maker_login.png`).
2. **Layar 2 (Katalog Jabatan):** Tampilan direktori jabatan dengan metrik statistik (`02_jobdesk_maker_catalog.png`).
3. **Layar 3 (Studio Wizard 5-Tab):** Pengisian formulir struktural, tugas, dan kompetensi (`03_jobdesk_maker_wizard.png`).
4. **Layar 4 (Draf Diajukan Review):** Status dokumen berubah ke *In Review* (`04_jobdesk_maker_submitted_review.png`).
5. **Layar 5 (Notifikasi Kadep SDM):** Pop-up notifikasi in-app draf baru pada akun pimpinan (`05_jobdesk_approver_notification.png`).
6. **Layar 6 (Telaah Dokumen 10 Seksi):** Pratinjau dokumen utuh standar SK Direksi (`06_jobdesk_approver_preview_10sec.png`).
7. **Layar 7 (Modal Pengesahan & TTE):** Dialog pengesahan pimpinan dengan catatan telaah (`07_jobdesk_approver_modal_sign.png`).
8. **Layar 8 (Notifikasi Disahkan ke Maker):** Feed notifikasi pada Admin SDM saat dokumen disahkan (`08_jobdesk_maker_notification_approved.png`).
9. **Layar 9 (Lembar Cetak A4 ISO & Watermark):** Dokumen fisik resmi ber-watermark Peruri SPM dan QR Code verifikasi digital (`09_jobdesk_print_a4_watermark.png`).

---

## 5. KESIMPULAN & PENGESAHAN KELAYAKAN RILIS (SIGN-OFF)

Portal Jobdesk Digital & Direktori Jabatan Korporat PT Kertas Padalarang dinyatakan **LULUS 100% (PASSED)** dengan predikat **EXCELLENT (ENTERPRISE GRADE)** dan memenuhi standar tata kelola organisasi BUMN:
- Alur *Maker-Checker-Approver* berjalan disiplin dan aman.
- Integrasi *in-app notification center* memberikan transparansi status dokumen secara seketika.
- Lembar cetak dokumen A4 ISO siap arsip dan dilengkapi pengamanan tanda tangan elektronik BSrE.

**STATUS: READY FOR PRODUCTION GO-LIVE.**
