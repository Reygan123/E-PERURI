# Aturan Surat E-Arsip PT Kertas Padalarang

Dokumen ini merapikan catatan mentah tentang jenis surat, penomoran, dan alur persetujuan di E-Arsip. Kode departemen yang belum ada memakai kode default sementara dan belum final.

## 1. Ringkasan jenis surat

| Jenis surat | Kode | Status | TTD digital |
| --- | --- | --- | --- |
| Surat Keluar | A | Aktif | Tidak |
| Surat Masuk | B | Aktif | Tidak |
| Surat Memo | E | Aktif | Ya |
| Surat Keputusan | KPTS | Aktif | Tidak (manual) |
| Surat Pengumuman | PENG | Aktif | Ya |
| Surat Tugas | ST | Aktif | Ya |
| Nota Dinas | (tanpa kode) | Aktif | Ya |
| Surat Permintaan Barang | SPB | Aktif | Ya |
| Surat Biaya Pengobatan | BP | Aktif | Ya |
| Surat Perintah Dinas | SPPD | Aktif | Sebagian |
| Surat Perjanjian/Kontrak | K | HOLD, koordinasi SPPS | Belum disebut |
| Surat Kuasa | SK | HOLD, koordinasi SPPS | Belum disebut |
| Surat Keterangan | KET | HOLD, koordinasi SPPS | Belum disebut |
| Surat Pernyataan | PNYT | HOLD, koordinasi SPPS | Belum disebut |
| Surat Berita Acara | BA | HOLD, koordinasi SPPS | Belum disebut |
| Surat Pengantar | TAR | HOLD, koordinasi SPPS | Belum disebut |
| Surat Information & Technology | IT | HOLD | Belum disebut |

## 2. Aturan umum

**Penomoran**

- Nomor surat mengikuti surat yang ada di masing-masing departemen, memakai singkatan departemen (lihat bagian 3).
- Contoh format di dokumen ini memakai SDM, bulan XI, dan tahun 2022. Singkatan departemen, nomor urut, bulan (angka Romawi), dan tahun menyesuaikan surat yang dibuat.
- Format baku dan penomoran global berlaku untuk: Surat Keterangan, Kuasa, Keputusan, Pernyataan, Pengumuman, Berita Acara, Tugas, Pengantar, Perjalanan Dinas (SPPD), dan Biaya Pengobatan.
- Penomoran diulang dari 01 **setiap bulan** untuk SPPD dan **setiap tahun** untuk semua jenis surat lain.

**Unggah dokumen (Surat Keluar)**

- Surat wajib diunggah dua kali: sebelum dan sesudah tanda tangan.
- Selama dua unggahan itu belum lengkap, pembuat tidak bisa membuat surat keluar baru.
- Aturan ini dibuat karena banyak surat terlambat diunggah ke sistem.
- Masalah yang sering terjadi: lupa mengunggah dan mengunggah PDF yang salah.

**Kepala Departemen kosong (POH)**

- Saat ini: Akuntansi.
- Selama jabatan kosong, persetujuan dan tanda tangan Kepala Departemen dilakukan oleh Direksi.

**Alur persetujuan standar**

- Staf → Kepala Unit → Kepala Seksi → Kepala Departemen. Berlaku untuk Surat Keluar, Surat Keputusan, Surat Pengumuman, dan Nota Dinas.
- Alur dimulai dari jabatan pembuat. Jika pembuatnya Kepala Unit, persetujuan hanya dari Kepala Seksi dan Kepala Departemen.
- Jika ada revisi, alur diulang dari bawah.
- Surat Memo, Surat Tugas, SPB, dan SPPD memiliki alur sendiri, dijelaskan per jenis.

## 3. Kode departemen untuk penomoran

| Departemen | Kode |
| --- | --- |
| SDM dan IT | SDM |
| Pemasaran | PEMAS |
| Akuntansi | AKN |
| Keuangan | KEU |
| Direksi | DIR |
| Satuan Pengawas Intern | SPI |
| Produksi | PROD |
| Teknik | TEK |
| Dapasum | DPS (default sementara) |
| TKRSMT | TKR (default sementara) |
| SPPS | SPPS (default sementara) |
| Jamindal | JMD (default sementara) |
| Risbang | RSB (default sementara) |
| Perencanaan Produksi (P4) | P4 (default sementara) |

Kode bertanda default sementara adalah usulan awal dan belum final. Kode lain sudah ditetapkan.

## 4. Aturan per jenis surat (aktif)

### Surat Keluar (A)

- **Pembuat:** semua role.
- **Tujuan:** pihak luar, diisi manual.
- **Alur persetujuan:** Staf → Kepala Unit → Kepala Seksi → Kepala Departemen (alur standar).
- **Penandatangan:** Kepala Departemen.
- **Format nomor:** 01/SDM/PTKP/XI/2022
- **Aturan khusus:** surat bertanggal mundur (backdate) memakai titik, misalnya 104.A, dengan sufiks A sampai D. Unggah dua kali wajib dilakukan (lihat bagian 2).

### Surat Masuk (B)

- **Pembuat:** semua role (menginput scan surat).
- **Sumber:** surat fisik dari pihak luar.
- **Isi di sistem:** hanya hasil scan surat. Nomor tetap dibuat.
- **Alur persetujuan:** tidak ada.
- **Format nomor:** 01/SDM/XI/2022

### Surat Memo (E), TTD digital

- **Pembuat:** sekretaris.
- **Tujuan:** satu departemen, beberapa departemen, atau semua departemen.
- **Pihak terlibat:** sekretaris, VP SPPS, Direksi.
- **Alur persetujuan:** Sekretaris → VP SPPS → Direksi.
- **Penandatangan:** Direksi (digital).
- **Format nomor:** 01/DIR.1/PTKP/XI/2022

### Surat Keputusan (KPTS)

- **Pembuat:** SDM.
- **Tujuan:** seluruh departemen (pilih lebih dari satu).
- **Isi:** kebijakan dari direksi atau pimpinan.
- **Alur persetujuan:** Staf SDM → Kepala Unit → Kepala Seksi → Kepala Departemen → Direksi.
- **Aksi yang tersedia:** setujui, tolak, revisi, tanda tangan.
- **Penandatangan:** Direksi, tanda tangan manual.
- **Format nomor:** 01/KPTS/PTKP/XI/2022

### Surat Pengumuman (PENG), TTD digital

- **Pembuat:** SDM dan SPPS.
- **Isi:** pengumuman seperti libur dan cuti bersama.
- **Tujuan:** seluruh departemen (pilih lebih dari satu).
- **Alur persetujuan:** hanya lingkup departemen pembuat: Staf → Kepala Unit → Kepala Seksi → Kepala Departemen.
- **Penandatangan:** Kepala Departemen yang membuat surat. Jika dibuat SDM, ditandatangani Kepala Departemen SDM. Jika dibuat SPPS, ditandatangani Kepala Departemen SPPS.
- **Format nomor:** 01/PENG/PTKP/XI/2022

### Surat Tugas (ST), TTD digital

- **Pembuat:** masing-masing departemen dan Direksi.
- **Alur dan penandatangan:**
  - Dari Direksi: persetujuan akhir dan tanda tangan oleh Direksi.
  - Dari departemen: persetujuan akhir dan tanda tangan oleh Kepala Departemen.
- **Format nomor:** 01/ST/PTKP/XI/2022

### Nota Dinas, TTD digital

- **Pembuat:** staf.
- **Tujuan:** antar departemen atau langsung ke Direksi.
- **Alur persetujuan:** Staf → Kepala Unit → Kepala Seksi → Kepala Departemen → Direksi. Setiap atasan wajib menyetujui.
- **Revisi:** jika ada revisi, surat kembali dan persetujuan diulang dari bawah.
- **Peran Direksi:** hanya memberi catatan atau disposisi. Direksi tidak dapat menyetujui atau menolak.
- **Penandatangan:** Kepala Departemen.
- **Format nomor:** 01/SDM/XI/2022 (bagian SDM, nomor, bulan, dan tahun menyesuaikan).

### Surat Permintaan Barang (SPB), TTD digital

- **Pembuat:** masing-masing departemen.
- **Tujuan:** gudang.
- **Alur persetujuan:** Kepala Departemen yang mengajukan.
- **Penandatangan:** Kepala Departemen pengaju.
- **Format nomor:** 01/SPB/SDM/XI/2022

### Surat Biaya Pengobatan (BP), TTD digital

- **Pembuat:** Klinik.
- **Tujuan:** Dokter.
- **Lampiran:** kuitansi atau bukti pengobatan.
- **Alur persetujuan:** hanya Dokter Perusahaan (PTKP).
- **Penandatangan:** Dokter Perusahaan (PTKP), tanda tangan saja tanpa nama dan keterangan lain.
- **Format nomor:** 01/XI/BP/PTKP/2022

## 5. Surat Perintah Dinas (SPPD)

### Ketentuan SPPD, TTD digital sebagian

- **Pembuat:** semua role dan departemen.
- **Tujuan:** Direksi.
- **Keluaran:** tiga dokumen, yaitu SPPD, SKPDLK, dan Perhitungan Biaya Luar Dinas.
- **Isi surat (4 halaman):** SPPD → Nota Dinas → SKPDLK → Perhitungan Biaya Luar Dinas.
- **Penandatangan dan urutan:**
  1. Direksi (digital)
  2. Kepala Departemen (digital)
  3. Pemohon (tanda tangan manual pada hasil cetak)
- **Penomoran:** diulang setiap bulan.
- **Format nomor:** 01/XI/SPD/PTKP/2022

## 6. Ditahan (HOLD)

Jenis berikut menunggu koordinasi dengan SPPS. Aturannya belum ditetapkan, hanya formatnya.

### Surat Perjanjian/Kontrak (K)

- **Status:** HOLD, menunggu koordinasi SPPS.
- **Pembuat:** semua departemen. Penomoran semua SDM sama.
- **Tujuan:** pihak eksternal atau vendor.
- **Alur persetujuan:** User departemen mana pun → Kepala Departemen → SPPS. Persetujuan Direksi juga disebut dalam catatan.
- **Format nomor:** 01/K/PTKP/XI/2022

### Format nomor jenis lain yang ditahan

- Surat Kuasa (SK): 01/SK/PTKP/XI/2022
- Surat Keterangan (KET): 01/KET/PTKP/XI/2022
- Surat Pernyataan (PNYT): 01/PNYT/PTKP/XI/2022
- Surat Berita Acara (BA): 01/BA/PTKP/XI/2022
- Surat Pengantar (TAR): 01/TAR/PTKP/XI/2022
- Surat Information & Technology (IT): 01/IT/XI/2022

## 7. Catatan

1. Kode departemen default sementara (Dapasum, TKRSMT, SPPS, Jamindal, Risbang, P4) belum final.
2. Jenis surat berstatus HOLD belum berlaku sampai ada koordinasi dengan SPPS.
