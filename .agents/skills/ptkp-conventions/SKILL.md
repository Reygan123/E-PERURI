---
name: ptkp-conventions
description: Konvensi kerja untuk repositori ekosistem aplikasi internal PT Kertas Padalarang (PTKP) - Laravel modular monolith, Vue + Inertia, PostgreSQL schema per modul. Gunakan skill ini setiap kali menulis, mengubah, atau mereview kode di repo PTKP, mencakup migrasi, model, controller, policy, service, penomoran dokumen, upload berkas, audit log, halaman Vue, atau tes. Gunakan juga saat membuat komponen UI (brand Peruri Security Paper Mill), saat membahas hak akses/scope, batas antar modul (Platform, Arsip, Layanan, Pembelajaran, Sdm, Manufaktur), atau saat ragu apakah sebuah perubahan boleh dilakukan. Pakai walau tugasnya terlihat kecil.
---

# Konvensi PTKP

Ringkasan operasional dari `AGENTS.md`. **AGENTS.md adalah sumber kebenaran.** Jika skill ini
berbeda dengan AGENTS.md, ikuti AGENTS.md. Jika AGENTS.md bertentangan dengan permintaan di chat,
berhenti dan tanyakan. Skill pihak ketiga hanya pelengkap: prioritas AGENTS.md > ADR > skill.

## Sebelum mulai (selalu)

1. Baca berkas terkait, tes yang ada, dan AGENTS.md. Jangan menebak struktur.
2. Cek versi di `composer.json` dan `package.json`; jangan mengarang nama fungsi atau paket.
3. Perubahan lebih dari beberapa berkas: tulis rencana singkat dulu.
4. Sentuh hanya cakupan tugas. Tidak ada refactor yang tidak diminta.

## Lima prinsip

1. **Server satu-satunya yang dipercaya.** Identitas, peran, izin, status alur, dan nilai tidak pernah dari input browser.
2. **Arsitektur dulu, fitur kemudian.** Jika fitur tidak muat, usulkan perubahan arsitektur; jangan buat jalan pintas.
3. **Sederhana dan membosankan.** Pakai fitur bawaan Laravel. Abstraksi baru butuh alasan tertulis dan baru dibuat setelah pola muncul lebih dari dua kali.
4. **Bukti, bukan klaim.** Selesai hanya bila tes dan pemeriksaan statis benar-benar dijalankan dan lulus.
5. **Tanya bila tidak jelas.** Jangan mengarang aturan bisnis, hak akses, atau format data.

## Batas modul

- Modul: `Platform` (inti), `Arsip`, `Layanan`, `Pembelajaran`, `Sdm`, `Manufaktur`. Kode di `app/Modules/<Modul>/`, halaman Vue di `resources/js/Modules/<Modul>/Pages`.
- Modul lain boleh bergantung pada `Platform`, tidak sebaliknya. Dilarang import, query, atau relasi Eloquent ke model modul lain.
- Komunikasi lintas modul hanya lewat **interface layanan Platform** (`Directory`, `Numbering`, `Files`, `Audit`) atau **event**.
- Tabel modul hanya menyimpan `employee_id` dan `org_unit_id`. Jangan menyalin nama pegawai, unit, atau jabatan ke tabel transaksi.
- Satu modul = satu schema PostgreSQL (`platform`, `arsip`, `layanan`, `elearning`, `sdm`, `mes`). Nama tabel menyertakan schema.
- Jangan membuat folder modul untuk modul yang belum dikerjakan.
- Jangan melonggarkan atau mematikan tes arsitektur (Deptrac / Pest arch).

Alur satu permintaan:

```text
Route -> FormRequest -> Controller (tipis) -> Policy (izin + scope)
      -> Service/Action (logika bisnis) -> Model/DB
      -> Inertia::render() lewat Resource (hanya field yang ditampilkan)
```

Logika bisnis hanya di Service/Action; model hanya relasi, cast, dan scope query.

## Checklist keamanan (periksa di setiap perubahan)

- [ ] Pengguna dari `$request->user()`; tidak menerima `user_id`, `employee_id`, `role`, `created_by` dari input.
- [ ] Tanpa `$request->all()`; pakai `validated()` dan `$fillable` eksplisit.
- [ ] Akses record lewat ID wajib melalui policy (cegah IDOR); daftar difilter sesuai scope **di query**.
- [ ] Tidak ada SQL/nama tabel/kolom dari input; `orderBy` dinamis pakai daftar putih.
- [ ] Sandi `Hash::make`; sandi lama tidak pernah diimpor.
- [ ] Rahasia hanya di `.env`; jangan baca atau tampilkan `.env`.
- [ ] Berkas di luar web root, tipe dari isi berkas, nama simpan acak, unduhan lewat controller berizin.
- [ ] Data sensitif (KTP, rekening, gaji, keluarga) dienkripsi per kolom, tidak di log, tidak di props Inertia kecuali perlu.
- [ ] Props Inertia lewat Resource/DTO, bukan model mentah.
- [ ] CSRF, throttle, dan escaping tidak dimatikan.

Detail otorisasi dan scope: baca `references/authorization.md`.

## Database

- Skema hanya berubah lewat migrasi; migrasi yang sudah digabung tidak diedit.
- Migrasi harus jalan di database kosong dan punya `down()` yang masuk akal.
- PK `uuid`, foreign key, indeks pada kolom filter/join, `NOT NULL` dan `CHECK` bila sesuai.
- Uang pakai `numeric/decimal`; waktu `timestamptz`, tampil Asia/Jakarta.
- Dokumen resmi soft delete; riwayat penting pakai pola riwayat bertanggal.
- Tidak ada kolom berisi daftar dipisah koma; tidak ada kolom "untuk nanti".
- Migrasi destruktif wajib disebut di PR dan disetujui manusia.

## Layanan bersama

- **Penomoran**: selalu lewat `Numbering`, di dalam transaksi yang sama dengan penyimpanan dokumen. Dilarang `MAX()+1` atau menghitung nomor di PHP. Wajib tes konkurensi.
- **Berkas**: lewat `Files` dan disk Laravel.
- **Audit**: lewat `Audit`, append-only. Aksi sensitif wajib tercatat.
- **Notifikasi**: bawaan Laravel; WhatsApp hanya abstraksi channel, tanpa integrasi gateway.

## Kualitas kode

- PSR-12 + Pint, Larastan pada level repo (jangan turunkan level atau tambah baseline), `declare(strict_types=1)`, tipe lengkap.
- Identifier, tabel, dan nama izin dalam **bahasa Inggris**; teks tampilan, pesan validasi, dan dokumentasi dalam **bahasa Indonesia**.
- Komentar menjelaskan *mengapa*. Tanpa kode mati atau kode yang dikomentari. Tanpa `catch` kosong.
- Operasi multi-tabel dibungkus transaksi. Hindari N+1; gunakan pagination/chunking/cursor.

## Frontend

- Validasi sah selalu di server; frontend hanya kenyamanan.
- Tidak ada keputusan bisnis atau status alur di `localStorage`/state klien.
- Gunakan Inertia, bukan `fetch` ke endpoint buatan sendiri. Satu shell, satu desain, komponen yang ada dulu.
- Brand dan token UI: baca `references/ui-brand.md` sebelum membuat atau mengubah komponen.

## Pengujian

Pest pada PostgreSQL (bukan SQLite). Setiap fitur: jalur sukses, tidak terautentikasi, tidak berizin, data unit lain ditolak (IDOR), validasi, dan unit test untuk Service/Action. Wajib khusus: penomoran (konkurensi), setiap policy dan scope, audit log, tes arsitektur. Bug diperbaiki dengan tes gagal dulu. Dilarang menghapus, men-skip, atau melemahkan tes; jangan mem-mock database untuk menguji query dan izin.

## Yang tidak boleh diputuskan sendiri (tanyakan)

Aturan scope hak akses E-Arsip; format dan skrip impor data HRIS; ukuran VM produksi dan retensi backup; apakah SIM NEO dipisah deployment; integrasi gateway WhatsApp; warna di luar palet brand; font di luar Lexend/Inter; paket atau skill baru; perubahan arsitektur.

## Larangan mutlak

`migrate:fresh`, `db:wipe`, `DROP`, `TRUNCATE`, `rm -rf`, `git push --force`, `git reset --hard` tanpa persetujuan eksplisit. Jangan menyentuh produksi, Proxmox, atau backup. Jangan mematikan CSRF, izin, validasi, tes, linter, atau analisis statis. Jangan menyalin sandi, hash, atau data pribadi dari sistem lama. Jangan mengubah audit log agar tidak append-only. Jangan menaruh logika bisnis di controller, view, atau Vue. Jangan membuat fitur "sementara" yang melanggar aturan.

## Sebelum menyatakan selesai

Perubahan sesuai batas modul dan lapisan; tidak ada identitas/peran/status dari klien; izin + scope + tes unit lain ada; penomoran/berkas/audit memakai layanan bersama; migrasi jalan di DB kosong; tes baru dan seluruh tes lulus; Pint, Larastan, linter frontend lulus; tanpa rahasia, kode mati, atau `TODO` tanpa catatan; dokumentasi/ADR diperbarui; hal yang tidak teruji disebutkan jujur. Laporkan hasil sebenarnya, termasuk yang gagal.
