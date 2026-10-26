# FORMULIR PENILAIAN UAT & BERITA ACARA SERAH TERIMA (BAST)
## SISTEM TATA KELOLA PERSURATAN ELEKTRONIK (E-ARSIP)
### PT KERTAS PADALARANG (PERURI SECURITY PAPER MILL)

---

## BAGIAN I: IDENTITAS PENGUJI (TESTER PROFILE)

| Field | Keterangan |
| :--- | :--- |
| **Nama Lengkap Penguji** | : ............................................................................................ |
| **NIP / NRP** | : ............................................................................................ |
| **Departemen / Unit Kerja** | : ............................................................................................ |
| **Jabatan Struktural** | : ............................................................................................ |
| **Tanggal Pengujian** | : ............................................................................................ |
| **Perangkat yang Digunakan** | : [ ] Komputer / Laptop Pabrik &nbsp;&nbsp;&nbsp; [ ] Smartphone / Tablet |

---

## BAGIAN II: LEMBAR KERJA PENGUJIAN SKENARIO (SCENARIO CHECKLIST)

Beri tanda centang (**✓**) pada kolom **LULUS** atau **GAGAL** sesuai hasil pengamatan Anda:

| Kode | Skenario Pengujian | Hasil yang Diharapkan | Lulus | Gagal | Catatan Penguji |
| :---: | :--- | :--- | :---: | :---: | :--- |
| **UAT-01** | **Surat Masuk & Disposisi** | Surat dari Bank Indonesia terbaca jelas; Disposisi bertingkat berhasil dibuat dan diterima pejabat lini bawah. | [ &nbsp; ] | [ &nbsp; ] | |
| **UAT-02** | **Surat Keluar & Penomoran Atomik** | Draf surat dapat diparaf Kasie PK1, disahkan Kadep Produksi, dan nomor resmi terbit otomatis tanpa duplikasi. | [ &nbsp; ] | [ &nbsp; ] | |
| **UAT-03** | **Siklus Revisi Naskah (Rule 52)** | Catatan revisi pimpinan tampil jelas; tombol edit aktif; pengajuan ulang mereset persetujuan ke langkah 1. | [ &nbsp; ] | [ &nbsp; ] | |
| **UAT-04** | **Eskalasi POH Akuntansi** | Naskah dinas unit Akuntansi otomatis dialihkan ke meja Direktur Keuangan tanpa kendala routing. | [ &nbsp; ] | [ &nbsp; ] | |
| **UAT-05** | **Formulir Khusus (SPPD & BP)** | Formulir SPPD dan Klaim Biaya Pengobatan berhasil diisi, rincian biaya tervalidasi, dan disahkan Kadep SDM. | [ &nbsp; ] | [ &nbsp; ] | |
| **UAT-06** | **Uji Keamanan & Error 403** | Operator pabrik yang mencoba membuka menu master platform ditolak dengan tampilan Error 403 PTKP resmi. | [ &nbsp; ] | [ &nbsp; ] | |
| **UAT-07** | **TTE BSrE & QR Code Publik** | Lembar PDF memuat QR Code aktif; pemindaian membuka portal verifikasi publik resmi BSrE BSSN tanpa login. | [ &nbsp; ] | [ &nbsp; ] | |

---

## BAGIAN III: KUESIONER EVALUASI KEPUASAN PENGGUNA (USABILITY SURVEY)

Berikan nilai antara **1 s.d. 5** untuk setiap pernyataan berikut:
- **1** = Sangat Tidak Setuju (STS)
- **2** = Tidak Setuju (TS)
- **3** = Cukup / Netral (N)
- **4** = Setuju (S)
- **5** = Sangat Setuju (SS)

| No | Aspek Penilaian Antarmuka & Fungsionalitas | Skor (1 - 5) | Catatan Tambahan |
| :---: | :--- | :---: | :--- |
| 1 | **Kemudahan Navigasi:** Menu persuratan yang dipecah per jenis surat (Surat Keluar, Masuk, Naskah Dinas, Formulir Khusus) sangat memudahkan pencarian dokumen bagi seluruh staf. | [ &nbsp;&nbsp;&nbsp; ] | |
| 2 | **Kenyamanan Visual (*Senior-Friendly*):** Ukuran teks, warna Deep Navy/Mist, serta kontras tombol aksi mudah dibaca dan nyaman di mata untuk pemakaian jangka panjang. | [ &nbsp;&nbsp;&nbsp; ] | |
| 3 | **Kecepatan Pratinjau PDF:** Dokumen surat dapat dipratinjau secara instan langsung di peramban tanpa perlu mengunduh berkas terlebih dahulu. | [ &nbsp;&nbsp;&nbsp; ] | |
| 4 | **Kejelasan Alur Persetujuan:** Riwayat paraf, catatan revisi, dan status dokumen (*Draft, In Review, Approved, Published*) terpampang sangat jelas. | [ &nbsp;&nbsp;&nbsp; ] | |
| 5 | **Kemudahan Akses Verifikasi QR Code:** Pemindaian QR Code pada dokumen fisik langsung membuktikan keaslian surat melalui portal publik secara cepat dan akurat. | [ &nbsp;&nbsp;&nbsp; ] | |
| 6 | **Efektivitas Notifikasi Multi-Kanal:** Pemberitahuan persetujuan yang dikirimkan melalui WhatsApp dan Email korporat sangat membantu kecepatan respon pejabat penandatangan. | [ &nbsp;&nbsp;&nbsp; ] | |
| 7 | **Kepuasan Menyeluruh:** Sistem E-Arsip PTKP versi baru ini siap menggantikan alur kerja persuratan manual kertas konvensional di lingkungan pabrik. | [ &nbsp;&nbsp;&nbsp; ] | |

**Total Skor:** ............. / 35 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; **Persentase Kepuasan:** ............. %

---

## BAGIAN IV: SARAN & MASUKAN PENGEMBANGAN (FEEDBACK)

........................................................................................................................................................................
........................................................................................................................................................................
........................................................................................................................................................................

---

<br><br>

## BAGIAN V: BERITA ACARA SERAH TERIMA HASIL UAT (BAST)
### NOMOR: BAST-UAT/012/PTKP-TI/IV/2026

Pada hari ini, ..................... tanggal ......... bulan ................... tahun Dua Ribu Dua Puluh Enam, bertempat di Kantor Pusat PT Kertas Padalarang, Jalan Cihaliwung No. 181 Padalarang, telah dilaksanakan Pengujian Penerimaan Pengguna (*User Acceptance Testing*) terhadap:

- **Nama Sistem:** Sistem Tata Kelola Persuratan Elektronik (E-Arsip) PT Kertas Padalarang
- **Versi Rilis:** Versi 2.0 (Fase 3 - Lanjutan)
- **Cakupan Pengujian:**
  1. Penomoran Atomik Otomatis & Alur Persetujuan Naskah Dinas
  2. Integrasi Tanda Tangan Elektronik Tersertifikasi BSrE BSSN & QR Code Verifikasi Publik
  3. Notifikasi Multi-Kanal Asynchronous (WhatsApp Gateway & Email Korporat)
  4. Penyesuaian Antarmuka Ramah Pengguna Senior & Separasi Menu

Berdasarkan hasil pengujian terpimpin yang telah dilaksanakan bersama seluruh perwakilan unit kerja (Direksi, Departemen Produksi, Teknik, SDM, Keuangan, dan Satuan Pengawasan Intern), para pihak menyatakan bahwa:

> **SISTEM DINYATAKAN DITERIMA DENGAN HASIL BAIK (LULUS)**  
> dan dinyatakan layak untuk diterapkan penuh (*Go-Live*) pada seluruh lini operasional PT Kertas Padalarang.

Demikian Berita Acara ini dibuat dalam rangkap secukupnya untuk dipergunakan sebagaimana mestinya.

<br>

<table style="width: 100%; text-align: center; font-size: 11pt;">
  <tr>
    <td style="width: 50%;">
      <strong>PIHAK PENGEMBANG</strong><br>
      Tim Implementasi Teknologi Informasi<br>
      PT Kertas Padalarang<br>
      <br><br><br><br>
      ( ..................................................... )<br>
      NIP.
    </td>
    <td style="width: 50%;">
      <strong>PIHAK PENGGUNA (USER)</strong><br>
      Perwakilan Pengguna E-Arsip<br>
      PT Kertas Padalarang<br>
      <br><br><br><br>
      ( ..................................................... )<br>
      NIP.
    </td>
  </tr>
  <tr>
    <td colspan="2" style="padding-top: 40px;">
      Mengetahui / Menyetujui,<br>
      <strong>DIREKSI PT KERTAS PADALARANG</strong><br>
      <br><br><br><br>
      ( ..................................................... )<br>
      Direktur Utama
    </td>
  </tr>
</table>
