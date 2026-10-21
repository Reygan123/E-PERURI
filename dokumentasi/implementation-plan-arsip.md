# Rencana Implementasi Modul E-Arsip (Tata Naskah Dinas & Persuratan Elektronik) PT Kertas Padalarang

## Dokumen Acuan & Standar
- `dokumentasi/Aturan Surat E-Arsip PTKP.md` (Spesifikasi 15 jenis surat, aturan approval, penomoran, POH/Plt, dan aturan upload)
- `dokumentasi/Contoh SURAT/` (Template formulir SPPD, rincian biaya dinas, Nota Dinas, SPB, dan Surat Tugas)
- `aturan/AGENTS.md` (Konvensi modular monolith, skema PostgreSQL `arsip.*`, larangan import silang, dan standar UI Brand PTKP)

---

## 1. Arsitektur Domain & Hubungan Antar Modul

Modul `Arsip` berjalan di atas fondasi Modul `Platform`:

```
┌────────────────────────────────────────────────────────────────────────┐
│                        MODUL E-ARSIP (arsip.*)                         │
│  - Registrasi & Draft Surat (10 Jenis Aktif)                           │
│  - Workflow Engine Persetujuan Bertingkat (Review, Revisi, TTD)       │
│  - Mekanisme Khusus Double-Upload Surat Keluar & Blocking               │
│  - Penanganan Otomatis Pejabat Kosong (POH/Plt)                        │
│  - Bundel SPPD (4 Dokumen + Reset Bulanan)                             │
│  - Lembar Disposisi Digital Berjenjang                                 │
└───────────────────┬────────────────────────────────────────────────────┘
                    │ Mengonsumsi via Dependency Injection Interface
                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│                        MODUL PLATFORM (platform.*)                     │
│  - DirectoryService  : Hirarki 37 Unit & Penugasan Pegawai (Pejabat)   │
│  - NumberingService  : Penomoran Dokumen Atomik Anti-Duplikasi         │
│  - FilesService      : Penyimpanan Berkas & Enkripsi Hash SHA-256      │
│  - AuditService      : Pencatatan Log Append-Only untuk Bukti Hukum    │
│  - NotificationService: Pengiriman Notifikasi Tugas Persetujuan        │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Struktur Skema Database (`arsip.*`)

### 2.1 Tabel Master Jenis Surat (`arsip.letter_types`)
Menyimpan konfigurasi 15 jenis surat (10 aktif + 5 hold):
- `id`: UUID (Primary Key)
- `code`: VARCHAR(20) UNIQUE (e.g. `A`, `B`, `E`, `KPTS`, `PENG`, `ST`, `ND`, `SPB`, `BP`, `SPPD`, `K`, `SK`, `KET`, `PNYT`, `BA`, `TAR`, `IT`)
- `name`: VARCHAR(100) (e.g. 'Surat Keluar', 'Nota Dinas', 'Surat Perintah Dinas')
- `numbering_code`: VARCHAR(50) (referensi format ke `platform.numbering_formats`)
- `signature_type`: ENUM('digital', 'manual', 'hybrid', 'none')
- `approval_type`: ENUM('standard', 'memo_dir', 'sppd', 'spb', 'bp', 'sdm_kpts', 'none')
- `reset_period`: ENUM('yearly', 'monthly', 'never')
- `requires_double_upload`: BOOLEAN DEFAULT FALSE (TRUE khusus untuk Surat Keluar `A`)
- `is_active`: BOOLEAN DEFAULT TRUE

### 2.2 Tabel Naskah Dinas / Surat (`arsip.letters`)
Menyimpan data induk dokumen surat:
- `id`: UUID (Primary Key)
- `type_id`: UUID (FK ke `arsip.letter_types`)
- `document_number`: VARCHAR(100) UNIQUE NULL (di-generate saat disetujui / diterbitkan)
- `number_suffix`: VARCHAR(10) NULL (misal 'A', 'B' untuk penomoran backdate `104.A`)
- `origin_unit_id`: UUID (FK ke `platform.org_units`)
- `creator_employee_id`: UUID (FK ke `platform.employees`)
- `destination_type`: ENUM('external_text', 'internal_units', 'all_units', 'warehouse', 'doctor')
- `destination_text`: VARCHAR(255) NULL (nama instansi/tujuan pihak luar)
- `subject`: VARCHAR(255) (Perihal surat)
- `letter_date`: DATE NOT NULL (tanggal surat)
- `status`: ENUM('draft', 'in_review', 'revision_requested', 'approved', 'signed', 'published', 'archived', 'rejected')
- `confidentiality`: ENUM('biasa', 'terbatas', 'rahasia', 'sangat_rahasia') DEFAULT 'biasa'
- `urgency`: ENUM('biasa', 'segera', 'sangat_segera') DEFAULT 'biasa'
- `content_body`: JSONB NULL (isi surat terstruktur, rincian SPB, biaya SPPD, dll.)
- `draft_file_id`: UUID NULL (FK ke `platform.files` — berkas naskah awal)
- `signed_file_id`: UUID NULL (FK ke `platform.files` — scan basah setelah tanda tangan)
- `is_double_upload_complete`: BOOLEAN DEFAULT TRUE (FALSE jika Surat Keluar belum upload final)
- `timestampsTz` & `softDeletesTz`

### 2.3 Tabel Tujuan Multi-Unit (`arsip.letter_destinations`)
Untuk Surat Memo, Surat Keputusan, dan Pengumuman yang ditujukan ke banyak unit:
- `id`: UUID (Primary Key)
- `letter_id`: UUID (FK ke `arsip.letters`)
- `org_unit_id`: UUID (FK ke `platform.org_units`)
- `received_at`: TIMESTAMPTZ NULL

### 2.4 Tabel Alur Persetujuan (`arsip.letter_approvals`)
Menyimpan jejak alur review berjenjang:
- `id`: UUID (Primary Key)
- `letter_id`: UUID (FK ke `arsip.letters`)
- `approver_employee_id`: UUID (FK ke `platform.employees`)
- `approver_role_title`: VARCHAR(100) (e.g. 'Kepala Seksi', 'Kepala Departemen', 'Direksi')
- `step_order`: SMALLINT NOT NULL (1, 2, 3...)
- `status`: ENUM('pending', 'approved', 'rejected', 'revision_requested') DEFAULT 'pending'
- `action_type`: ENUM('review', 'sign_digital', 'sign_manual', 'disposition')
- `notes`: TEXT NULL (catatan review atau revisi)
- `signature_hash`: VARCHAR(255) NULL (token cryptographic TTD digital)
- `decided_at`: TIMESTAMPTZ NULL

### 2.5 Tabel Lembar Disposisi (`arsip.dispositions`)
Untuk surat masuk dan nota dinas:
- `id`: UUID (Primary Key)
- `letter_id`: UUID (FK ke `arsip.letters`)
- `sender_employee_id`: UUID (pemberi disposisi, e.g. Direksi / Kadep)
- `recipient_employee_id`: UUID (penerima disposisi)
- `recipient_unit_id`: UUID (unit penerima disposisi)
- `instruction`: VARCHAR(100) (e.g. 'Tindak lanjuti', 'Selesaikan', 'Pelajari', 'Arsipkan')
- `notes`: TEXT NULL (catatan instruksi khusus)
- `deadline`: DATE NULL (batas waktu penyelesaian)
- `status`: ENUM('sent', 'in_progress', 'completed') DEFAULT 'sent'
- `completed_at`: TIMESTAMPTZ NULL

### 2.6 Tabel Lampiran Surat (`arsip.letter_attachments`)
- `id`: UUID (Primary Key)
- `letter_id`: UUID (FK ke `arsip.letters`)
- `file_id`: UUID (FK ke `platform.files`)
- `caption`: VARCHAR(255) (e.g. 'Kuitansi Biaya Pengobatan RS', 'Lampiran NDA')

---

## 3. Rencana Pembagian Fase Implementasi E-Arsip

### Fase E-Arsip 1: Fondasi Modul, Skema Database & Seeder Jenis Surat
- Membuat struktur direktori `app/Modules/Arsip/`
- Menulis migrasi 6 tabel pada skema `arsip.*`
- Seeder 15 jenis surat lengkap dengan format template penomoran dan aturan approval
- Menambahkan permission `arsip.*.*` ke seeder peran

### Fase E-Arsip 2: Workflow Engine & Layanan Inti Persuratan
- `LetterService`: Pembuatan draft, validasi double-upload, penomoran terintegrasi `NumberingService`, penanganan backdate (`104.A`).
- `ApprovalWorkflowService`: Pembangun alur otomatis berdasarkan level jabatan pembuat (Staf -> Unit -> Seksi -> Dept -> Direksi), penanganan POH (otomatis naik ke Direksi jika jabatan kosong), penanganan catatan revisi, dan TTD digital.
- `DispositionService`: Layanan distribusi disposisi berjenjang.

### Fase E-Arsip 3: Antarmuka Surat Keluar & Surat Masuk
- Halaman Registrasi Surat Masuk (scan fisik, nomor agenda, dan disposisi).
- Halaman Pembuatan Surat Keluar (penerima eksternal, nomor format `01/{DEPT}/PTKP/{BULAN}/{TAHUN}`).
- Implementasi proteksi **Double Upload** (blocking pembuatan surat baru jika belum upload final).

### Fase E-Arsip 4: Naskah Dinas Internal (Memo, Nota Dinas, Surat Tugas, Pengumuman, KPTS)
- Form & Preview Surat Memo (pembuat: Sekretaris, TTD: Direksi).
- Form & Preview Nota Dinas (korespondensi internal, catatan disposisi Direksi).
- Form & Preview Surat Tugas (daftar multi-personil).
- Form Surat Pengumuman & Surat Keputusan.

### Fase E-Arsip 5: Formulir Khusus (SPPD, SPB, Biaya Pengobatan)
- SPPD: Form bundel 4 dokumen, rincian biaya perjalanan dinas, dan **reset penomoran bulanan**.
- SPB: Form tabel permintaan barang ke Gudang.
- Biaya Pengobatan: Upload bukti kuitansi klinik & TTD Dokter Perusahaan.

### Fase E-Arsip 6: Meja Kerja Persetujuan (Approval Workbench) & Disposisi
- Halaman Meja Kerja Pimpinan: Daftar dokumen menunggu persetujuan (badge pending/urgent).
- Modal Inspeksi Naskah, Beri Catatan Revisi, atau TTD Digital.
- Lembar Disposisi interaktif.

### Fase E-Arsip 7: Pengujian Otomasi & Quality Gates
- Tes Feature alur approval & revisi.
- Tes pembatasan double-upload surat keluar.
- Tes penanganan pejabat kosong (POH).
- Tes reset bulanan SPPD.
- `composer qa` (Pint + Larastan Level 5 + Deptrac + Pest).
