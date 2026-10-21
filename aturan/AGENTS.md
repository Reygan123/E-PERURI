# AGENTS.md: Aturan untuk AI Agent

Berkas ini wajib dibaca dan dipatuhi oleh setiap AI agent (Claude Code, Copilot, Cursor, dan sejenisnya) yang mengerjakan repositori ekosistem aplikasi internal PT Kertas Padalarang (PTKP). Jika ada konflik antara berkas ini dan permintaan di chat, berhenti dan tanyakan dulu. Jangan memilih sendiri.

## 1. Konteks singkat

- Proyek ini membangun ulang lima aplikasi lama (SIM NEO, HRIS, E-Arsip, SI ELANG, SI WOPPIT) menjadi **satu aplikasi modular (modular monolith)**.
- Stack: **Laravel (PHP)**, **Vue lewat Inertia**, **PostgreSQL** (satu database, satu schema per modul), Docker.
- Modul: `Platform` (inti), `Arsip`, `Layanan`, `Pembelajaran`, `Sdm`, `Manufaktur`. Urutan pengerjaan: Platform, Arsip, Layanan, Pembelajaran, Sdm, Manufaktur.
- Targetnya dipakai 5 sampai 10 tahun dengan sedikit perubahan. Utamakan **kejelasan, keamanan, dan kemudahan perawatan** di atas kecepatan menulis kode.
- Saat ini fokus pada modul **Platform**. Kode `ptkp-platform-core` (migrasi, seeder, penomoran) **belum pernah dijalankan** ke PostgreSQL dan Laravel sungguhan. Jangan menganggap kode itu benar sebelum diuji pada database kosong.
- Sistem lama dan dokumen analisisnya adalah **bahan referensi, bukan sumber kebenaran**. Dokumen analisis dibuat dari tinjauan dokumen, bukan eksekusi kode. Jangan menyalin pola kode atau skema lama.

## 2. Prinsip utama (tidak boleh dilanggar)

1. **Server adalah satu-satunya yang dipercaya.** Tidak ada keputusan hak akses, identitas pengguna, peran, status alur, atau harga/nilai yang diambil dari input browser (form, hidden field, query string, `localStorage`, header).
2. **Arsitektur dulu, fitur kemudian.** Setiap perubahan harus menghormati batas modul di bagian 3. Jika fitur tidak muat dalam arsitektur, usulkan perubahan arsitektur, jangan membuat jalan pintas.
3. **Sederhana dan membosankan.** Pilih solusi paling umum di ekosistem Laravel. Jangan membuat abstraksi, pola, atau paket baru tanpa alasan tertulis.
4. **Bukti, bukan klaim.** Pekerjaan dianggap selesai hanya jika tes dan pemeriksaan statis lulus dan benar-benar dijalankan (bagian 12).
5. **Tanya bila tidak jelas.** Jangan mengarang aturan bisnis, hak akses, atau format data.

## 3. Arsitektur dan batas modul

### 3.1 Struktur direktori

```text
app/
  Modules/
    Platform/        Models, Services, Policies, Http, Events, database/migrations, routes.php
    Arsip/
    Layanan/
    Pembelajaran/
    Sdm/
    Manufaktur/      submodul: Produksi, Mutu, Cmms, Akuntansi
resources/js/Modules/<Modul>/Pages/...
```

- Kode dikelompokkan **per modul (domain)**, bukan per jenis berkas.
- Setiap modul punya service provider sendiri yang mendaftarkan route, migrasi, dan policy.
- Buat folder modul hanya saat modul itu dikerjakan. Jangan membuat kerangka kosong untuk modul masa depan.

### 3.2 Aturan batas modul

- `Platform` adalah inti. Modul lain boleh bergantung pada `Platform`, **tidak sebaliknya**.
- Sebuah modul **tidak boleh** meng-import, query, atau membuat relasi Eloquent ke model modul lain. Satu-satunya pengecualian adalah akses ke data milik `Platform` (pegawai, organisasi, hak akses) lewat kelas layanan.
- Komunikasi antar modul hanya lewat:
  - **kelas layanan Platform** (misalnya `Directory`, `Numbering`, `Files`, `Audit`), dipanggil melalui **interface**, atau
  - **event** untuk perubahan state.
- Tabel modul hanya menyimpan `employee_id` dan `org_unit_id`. **Dilarang** menyalin nama pegawai, nama unit, jabatan, atau data master lain ke tabel transaksi. Ambil lewat join atau layanan saat menampilkan. Pengecualian hanya untuk snapshot hukum/audit yang diputuskan secara eksplisit dan didokumentasikan.
- Setiap modul memakai **schema PostgreSQL sendiri** (`platform`, `arsip`, `layanan`, `elearning`, `sdm`, `mes`). Setiap modul dapat diberi akun database yang hanya boleh mengakses schema-nya.
- Aturan batas dijaga oleh **tes arsitektur otomatis** (Deptrac atau Pest arch tests). Jangan mematikan atau melonggarkannya agar tes lulus.
- Layanan `Platform` dipanggil lewat interface agar kelak bisa diganti klien HTTP tanpa mengubah pemanggil.

### 3.3 Lapisan di dalam modul

Alur satu permintaan:

```text
Route -> FormRequest (validasi) -> Controller (tipis) -> Policy (izin + scope)
      -> Service/Action (logika bisnis) -> Model/DB
      -> Inertia::render() dengan Resource (hanya field yang dibutuhkan)
```

- **Controller tipis**: terima input tervalidasi, panggil satu service/action, kembalikan respons. Tidak ada logika bisnis, query kompleks, atau aturan izin di controller.
- **Logika bisnis** berada di kelas Service atau Action yang bisa diuji tanpa HTTP dan bisa dipakai ulang oleh controller API di masa depan.
- **Model** hanya berisi relasi, cast, scope query, dan aturan data sederhana. Bukan tempat logika alur bisnis.
- Satu kelas, satu tanggung jawab. Kelas yang tumbuh melewati satu layar penuh perlu dipecah.

## 4. Keamanan (wajib)

Pola celah di sistem lama berulang di semua aplikasi. Fondasi baru ada untuk mencegahnya. Setiap poin berikut wajib diperiksa pada setiap perubahan.

1. **Identitas dari sesi.** Pengguna yang melakukan aksi selalu `$request->user()` atau `auth()->user()`. Jangan menerima `user_id`, `employee_id`, `role`, `created_by`, atau sejenisnya dari input.
2. **Mass assignment.** Jangan memakai `$request->all()` atau `$request->input()` mentah untuk `create`/`update`. Pakai `FormRequest::validated()` dan `$fillable` eksplisit. Field seperti peran, status, dan kepemilikan **tidak pernah** boleh berasal dari form biasa.
3. **IDOR.** Setiap akses ke record lewat ID (di URL atau body) wajib melewati policy yang memeriksa kepemilikan atau scope. Daftar/list wajib memfilter data sesuai scope pengguna (jangan menampilkan data semua unit ke semua pengguna).
4. **Tidak ada SQL dari klien.** Tidak ada query, nama tabel, nama kolom, atau ekspresi yang datang dari input. Pakai Eloquent atau query builder dengan parameter binding. Dilarang menggabungkan string ke dalam SQL. Untuk `orderBy` atau kolom dinamis, gunakan daftar putih.
5. **Sandi.** Gunakan `Hash::make` (Argon2id atau bcrypt sesuai konfigurasi). Dilarang MD5, SHA1, atau plaintext. Sandi dari sistem lama **tidak pernah** diimpor. Akun baru memakai sandi sementara dan wajib ganti saat login pertama.
6. **Rahasia.** Kredensial, kunci, dan token hanya di `.env` atau secret manager. Jangan di kode, tes, log, komit, atau contoh. Sediakan `.env.example` tanpa nilai asli.
7. **Berkas.** Simpan di luar web root. Unduhan lewat controller yang memeriksa izin. Tipe berkas dideteksi dari isi (bukan dari ekstensi atau header klien), ekstensi dibatasi daftar putih, nama simpan dibuat acak, nama asli hanya metadata. Jangan pernah mengizinkan eksekusi berkas unggahan. Tipe generik seperti `application/octet-stream` tidak boleh otomatis diterima.
8. **Data sensitif** (KTP, rekening, gaji, data keluarga): enkripsi level kolom, tidak masuk log, tidak masuk props Inertia kecuali halaman itu memang butuh dan penggunanya berhak.
9. **Props Inertia** terlihat di sumber halaman. Kirim hanya field yang ditampilkan lewat Resource atau DTO. Jangan mengirim model mentah.
10. **Proteksi bawaan** Laravel (CSRF, escaping, rate limiting untuk login, throttle untuk endpoint sensitif) tidak boleh dimatikan.
11. **Log dan error** tidak boleh membocorkan SQL, path server, atau data pribadi. Berkas log berada di luar web root dan dirotasi.
12. **Dependensi.** Tambahkan paket baru hanya dengan alasan tertulis, periksa pemeliharaan dan lisensinya, jalankan `composer audit` dan `npm audit`. Lebih baik memakai fitur bawaan Laravel.

## 5. Otorisasi

> Detail implementasi policy dan scope: `.agents/skills/ptkp-conventions/references/authorization.md`

- **Deny by default.** Tidak ada izin eksplisit berarti ditolak.
- Izin berbentuk `modul.sumber.aksi`, contoh `arsip.surat.create`.
- Setiap pemberian izin ke peran memiliki **scope**: `own` (milik sendiri), `unit` (unit sendiri), `unit_tree` (unit dan turunannya), `all` (semua). Penugasan peran dapat dibatasi ke unit tertentu dan memiliki masa berlaku (`valid_from`, `valid_until`).
- Setiap action controller memanggil `authorize()` atau policy. Tidak ada endpoint tanpa pemeriksaan.
- Pemeriksaan scope dilakukan di **query** (global scope atau query scope), bukan hanya di tampilan, sehingga data di luar scope tidak pernah ikut terambil. Menyembunyikan di tampilan tidak dihitung.
- Setiap policy dan scope punya tes: diizinkan, ditolak, dan data milik unit lain.
- Jangan menyembunyikan tombol di frontend lalu menganggapnya sebagai pengamanan. Pengamanan ada di server.
- Identitas dan unit pengguna selalu dari sesi server, termasuk saat menentukan `own` / `unit` / `unit_tree`.
- **Aturan scope E-Arsip yang sebenarnya belum diputuskan.** Seeder saat ini hanya titik awal berdasarkan aturan lama. Jangan menetapkan aturan baru sendiri. Tanyakan.

## 6. Database dan migrasi

- Semua perubahan skema **hanya lewat migrasi**. Dilarang mengubah skema manual atau lewat tinker.
- Migrasi yang sudah digabung ke branch utama **tidak boleh diedit**. Buat migrasi baru.
- Setiap migrasi harus dapat dijalankan pada database kosong dan memiliki `down()` yang masuk akal.
- Nama tabel selalu menyertakan schema modul. Pakai foreign key, indeks pada kolom yang difilter dan di-join, dan `NOT NULL` serta `CHECK` bila sesuai.
- Uang dan nilai pecahan memakai `numeric/decimal`, **bukan float**. Waktu disimpan sebagai `timestamptz`; tampilan memakai zona waktu Asia/Jakarta.
- Dokumen resmi memakai **soft delete**. Riwayat penting memakai pola riwayat bertanggal, misalnya `employee_assignments` (mutasi menutup satu baris dan membuka baris baru), bukan menimpa data.
- Jangan membuat kolom berisi daftar dipisah koma (seperti `sent_trainer_id` di sistem lama). Pakai tabel pivot berindeks.
- Jangan membuat kolom sisa atau cadangan "untuk nanti". Skema hanya berisi yang dipakai.
- Migrasi destruktif (drop kolom/tabel, perubahan tipe yang bisa memotong data) wajib disebut jelas di PR dan disetujui manusia.
- Seeder untuk pengembangan memakai **data dummy**. Data pegawai asli tidak boleh masuk repositori.

## 7. Layanan bersama

### 7.1 Penomoran

- Seluruh penomoran lewat layanan `Numbering` (tabel format dan sekuens, fungsi database atomik).
- Dipanggil **di dalam transaksi yang sama** dengan penyimpanan dokumen, sehingga tidak ada nomor ganda dan tidak ada nomor bolong.
- **Dilarang** pola `MAX()+1`, `SELECT ... ORDER BY id DESC LIMIT 1` lalu tambah satu, atau menghitung nomor di PHP.
- Format dan aturan reset (per tahun, per jenis, per bulan) dikonfigurasi di data, bukan di-hardcode di controller.
- Wajib ada tes konkurensi yang menjalankan beberapa permintaan bersamaan dan memastikan tidak ada nomor kembar.

### 7.2 Berkas

- Metadata di database, isi di luar web root, unduhan lewat controller berizin (lihat bagian 4).
- Akses penyimpanan lewat abstraksi (disk Laravel) agar bisa pindah storage.

### 7.3 Audit log

- Mencatat siapa, apa, kapan, nilai sebelum dan sesudah. **Append-only**, tidak ada update atau delete. Dipartisi per tahun.
- Aksi sensitif (perubahan peran, mutasi, perubahan data gaji, penghapusan dokumen) wajib tercatat.

### 7.4 Notifikasi

- Memakai fasilitas notifikasi dan antrean bawaan Laravel. Saluran: email dan WhatsApp. WhatsApp membutuhkan gateway, jadi tahap awal cukup menyiapkan abstraksi channel-nya. Jangan menulis integrasi gateway tanpa instruksi.

## 8. Kualitas kode (clean code)

- Ikuti **PSR-12** dan jalankan formatter yang dikonfigurasi di repo (Laravel Pint). Jangan membuat gaya sendiri.
- **Analisis statis** (Larastan) harus lulus pada level yang ditetapkan di repo. Jangan menurunkan level atau menambah baseline untuk menyembunyikan galat baru.
- Gunakan **type declaration** (`declare(strict_types=1)`, tipe parameter, tipe kembalian, properti bertipe) di semua kode baru.
- Nama jelas dan jujur. Fungsi melakukan satu hal. Hindari fungsi yang panjang dan parameter yang banyak. Hindari angka dan string ajaib; pakai enum atau konstanta.
- Identifier kode, nama tabel, dan nama izin memakai **bahasa Inggris**. Teks yang tampil ke pengguna, pesan validasi, dan dokumentasi memakai **bahasa Indonesia**.
- Komentar menjelaskan **mengapa**, bukan **apa**. Hapus kode mati. Jangan meninggalkan kode yang dikomentari.
- Tangani galat secara eksplisit. Jangan menelan pengecualian (`catch` kosong). Gunakan pengecualian domain yang bermakna.
- Hindari duplikasi, tetapi jangan membuat abstraksi prematur. Abstraksi dibuat saat pola sudah muncul lebih dari dua kali.
- Gunakan fitur bawaan Laravel (Form Request, Policy, Resource, Enum, Event, Job) daripada membuat versi sendiri.
- Operasi yang mengubah lebih dari satu tabel dibungkus transaksi.
- Hindari query N+1 (gunakan eager loading) dan jangan memuat seluruh tabel ke memori. Gunakan pagination, chunking, atau cursor.

## 9. Frontend (Inertia dan Vue)

- Halaman ditempatkan di `resources/js/Modules/<Modul>/Pages`. Komponen bersama di lokasi bersama yang disepakati.
- Validasi di frontend hanya untuk kenyamanan pengguna. Validasi yang sah selalu di server.
- Jangan menyimpan keputusan bisnis atau status alur di `localStorage`, `sessionStorage`, atau state klien.
- Jangan membuat panggilan `fetch` ke endpoint buatan sendiri bila Inertia bisa menangani. Endpoint API hanya untuk kebutuhan nyata (mobile, fingerprint, pihak ketiga) dan dibuat terpisah dengan Sanctum.
- Satu desain dan satu shell untuk semua modul. Gunakan komponen yang sudah ada sebelum membuat baru.
- Teks tampilan dalam bahasa Indonesia. Perhatikan aksesibilitas dasar (label, fokus, kontras).
- Jalankan ESLint dan Prettier (dan pemeriksaan tipe bila TypeScript dipakai) sesuai konfigurasi repo.

## 9a. Panduan UI/UX — Brand Guideline PTKP

Sumber: **Brand Guideline PT Kertas Padalarang (Peruri Security Paper Mill)**.
Semua antarmuka aplikasi internal **wajib** mengikuti panduan ini. Jangan membuat sistem desain sendiri.

> Ringkasan cepat saat coding: `.agents/skills/ptkp-conventions/references/ui-brand.md`  
> Jika berkas referensi berbeda dengan section ini, **section ini (AGENTS.md) yang menang.**

### 9a.1 Identitas Merek

- Nama merek tampilan: **PERURI Security Paper Mill**
- Logo terdiri dari tiga bagian yang tidak boleh dipisah: kata **PERURI**, simbol pancaran cahaya (sinar bintang), dan keterangan **Security Paper Mill**.
- Logo hanya dipakai pada latar **putih (#FFFFFF)** atau **Mist (#EEF0FB)**.
- Lebar minimal logo di layar: **120 px**.

**Larangan logo (HINDARI):**
- Mengubah warna atau memutar logo.
- Meregangkan atau memadatkan proporsi logo.
- Menambahkan bayangan, outline, atau efek apapun.
- Menaruh logo di atas foto ramai atau warna kontras rendah.

### 9a.2 Palet Warna

Gunakan hanya variabel CSS yang didefinisikan di design token. Jangan memakai nilai warna hardcode di komponen.

| Token | Nama | HEX | RGB | Fungsi |
|-------|------|-----|-----|--------|
| `--color-navy` | Peruri Navy | `#20288E` | 32, 40, 142 | Warna utama: kata merek, teks judul, tombol primer, elemen aktif |
| `--color-violet` | Spark Violet | `#D861FF` | 216, 97, 255 | Aksen: simbol cahaya, garis dekoratif, penanda, badge aktif |
| `--color-deep-navy` | Deep Navy | `#141A5C` | 20, 26, 92 | Latar gelap: sidebar, header dark, hover state tombol primer |
| `--color-mist` | Mist | `#EEF0FB` | 238, 240, 251 | Latar lembut: background halaman, kartu sekunder, input disabled |
| `--color-white` | White | `#FFFFFF` | 255, 255, 255 | Latar utama kartu, form, konten |
| `--color-text` | Text Default | `#20288E` | — | Teks heading utama (gunakan Navy) |
| `--color-text-body` | Text Body | `#1A1A2E` | — | Teks paragraf dan label (gelap netral) |
| `--color-border` | Border | `rgba(32,40,142,0.14)` | — | Garis border elemen, divider |

**Aturan warna wajib:**
- **Navy** dipakai untuk aksi utama (tombol primer, link aktif, heading).
- **Spark Violet dilarang** dipakai sebagai warna teks di atas latar putih (kontras kurang). Violet hanya aman di atas latar Navy atau Deep Navy.
- Pada latar gelap (Deep Navy/Navy), violet boleh dipakai untuk aksen.
- Warna di luar daftar ini hanya boleh ditambahkan setelah ada persetujuan eksplisit.
- **Warna notifikasi semantik (sukses hijau tua, peringatan kuning tua, error merah tua) dan abu netral untuk badge belum memiliki token resmi.** Jangan di-hardcode. Usulkan token dan minta persetujuan sebelum menggunakannya.

### 9a.3 Tipografi

Font utama yang digunakan:

| Peran | Font | Weight | Keterangan |
|-------|------|--------|------------|
| Heading / Judul | **Lexend** | Bold (700) | Mencerminkan karakter logo yang lebar dan tebal |
| Body / Konten | **Inter** | Regular (400) / Medium (500) | Font sistem yang bersih dan mudah dibaca |

**Aturan tipografi:**
- Import keduanya dari Google Fonts.
- Jangan memakai font lain tanpa persetujuan eksplisit.
- Lexend hanya untuk heading (h1–h4), label navigasi utama, dan nama tombol penting.
- Inter untuk semua teks paragraf, label form, caption, tabel, dan notifikasi.
- Ukuran teks minimum di layar: **12px** (caption). Hindari teks lebih kecil dari itu.
- Gunakan skala tipografi yang konsisten, berbasis kelipatan 4px (misal: 12, 14, 16, 20, 24, 32, 40px).

### 9a.4 Spasi (Spacing Scale)

Semua margin, padding, dan gap menggunakan **kelipatan 4px**. Jangan memakai nilai spasi sembarangan.

| Token | Nilai | Kegunaan Umum |
|-------|-------|---------------|
| `--space-1` | 4px | Spasi terapat (ikon + teks, item list rapat) |
| `--space-2` | 8px | Padding dalam badge, gap ikon-label |
| `--space-3` | 12px | Padding input kecil, spasi antar elemen form |
| `--space-4` | 16px | Padding standar komponen (kartu, tombol, input) |
| `--space-5` | 24px | Jarak antar bagian dalam satu kartu |
| `--space-6` | 32px | Jarak antar kartu, margin section |
| `--space-7` | 48px | Jarak antar section besar |
| `--space-8` | 64px | Padding halaman utama, jarak hero section |

### 9a.5 Border Radius & Kelengkungan Seamless

Kelengkungan elemen UI mengikuti prinsip **hierarki konsentris yang seamless (concentric radius)**: kontainer luar memiliki lengkungan yang lebih lembut, sementara elemen di dalamnya memiliki kurva yang sejajar alami. Hal ini menjaga wibawa institusional Peruri tanpa terlihat kaku atau terlalu membulat ("bubbly").

| Token | Nilai | Kegunaan |
|-------|-------|---------|
| `--radius-xs` | 4px | Micro-chips, status dots, badge mini |
| `--radius-sm` | 6px | Form input, select, textarea, filter chips |
| `--radius-md` | 8px | Tombol aksi primer/sekunder, alert banner, item navigasi aktif |
| `--radius-lg` | 12px | Kartu utama (card), kontainer panel, modal dialog, table wrapper |
| `--radius-pill` | 999px | Badge status dan pill tag — bukan untuk tombol atau kartu |

**Larangan:** Jangan memakai `border-radius: 16px` atau lebih besar pada kartu biasa, dan jangan membuat tombol berbentuk pill.

### 9a.6 Bayangan & Elevasi Halus (Subtle Elevation)

Tingkat kedalaman dirancang agar kontainer menyatu alami (*seamless*) di atas kanvas Mist:

| Nama | Spesifikasi | Kegunaan |
|------|-------------|---------|
| Datar / Border | `border: 1px solid var(--color-border)` | Garis batas struktural & divider tipis |
| Kartu Halus | `box-shadow: 0 1px 3px rgba(20, 26, 92, 0.04), 0 1px 2px rgba(20, 26, 92, 0.02)` | Kartu konten standar di atas latar Mist |
| Terangkat | `box-shadow: 0 4px 16px rgba(20, 26, 92, 0.08)` | Dropdown, modal, panel mengambang |

Jangan memakai bayangan tebal, tajam, atau berwarna-warni selain turunan Deep Navy dengan opacity sangat rendah.

### 9a.7 Komponen UI Standar

#### Tombol

| Varian | Gaya | Kegunaan |
|--------|------|---------|
| **Utama** | bg: `--color-navy`, teks putih, `radius-md` (8px) | Aksi primer (Simpan, Kirim, Tambah) |
| **Sekunder** | border `--color-navy`, bg transparan, teks navy, `radius-md` (8px) | Aksi sekunder (Batal, Filter, Kembali) |
| **Aksen** | bg: `--color-violet`, teks putih, `radius-md` (8px) | Hanya pada latar gelap (Navy/Deep Navy) |
| **Teks** | Tanpa border/bg, teks navy underline saat hover | Aksi tersier (tautan, detail) |

Aturan tombol:
- Jangan memakai violet sebagai tombol di latar putih/Mist.
- Semua tombol menggunakan `radius-md` (8px) — **bukan pill**.
- Teks tombol dalam **bahasa Indonesia**.

#### Input / Form Field

- Border: `1px solid var(--color-border)`, radius: `radius-sm` (6px).
- Focus state: border berubah ke `--color-navy`, ring navy tipis (`focus:ring-1 focus:ring-ptkp-navy`).
- Error state: border merah, pesan error di bawah field dalam bahasa Indonesia.
- Placeholder: teks abu-abu, contoh format yang jelas.
- Label selalu di atas field secara konsisten, bukan placeholder pengganti label.

#### Kartu (Card)

- Background: `--color-white`.
- Border: `1px solid var(--color-border)`.
- Radius: `radius-lg` (12px) dengan `overflow-hidden`.
- Elevasi: Menggunakan bayangan kartu halus (`shadow-card`) agar menyatu mulus di atas latar Mist.
- Padding dalam: Header `px-6 py-4`, Konten `p-6` (24px).

#### Badge / Tag

- Menggunakan `radius-pill` (999px) — **satu-satunya komponen yang boleh pill**.
- Warna badge: Navy (status aktif/verified), Violet di latar gelap, abu netral untuk status lain.
- Teks ringkas, ukuran font 12px (text-xs).

#### Notifikasi / Alert

- Radius: `radius-md` (8px).
- Warna sesuai semantik: Navy (info), hijau tua (sukses), kuning tua (peringatan), merah tua (error).
- Jangan memakai warna cerah/neon untuk notifikasi.

### 9a.8 Elemen Grafis Warisan Arsitektur

Brand PTKP terinspirasi dari arsitektur pabrik. Elemen ini boleh dipakai sebagai **dekorasi subtle** pada halaman landing, header modul, atau ilustrasi kosong:

| Elemen | Deskripsi | Penggunaan |
|--------|-----------|------------|
| **Atap limas** | Siluet atap bertingkat, puncak landai, dasar tegas | Bentuk kartu utama, ilustrasi hero |
| **Cerobong berpita** | Pita selang-seling pada cerobong | Garis linimasa / progress indicator |
| **Batu kali** | Pola organik bergrout | Tekstur latar subtle pada section gelap |
| **Kaca lavender** | Kisi jendela kotak | Tint pendukung sorotan, panel info, highlight |

Aturan elemen grafis:
- Dipakai sebagai dekorasi **ringan dan subtle** — bukan mendominasi konten.
- Warna elemen menggunakan palet resmi (Navy, Violet, Mist).
- Jangan membuat ilustrasi baru yang tidak mengacu pada elemen ini.

### 9a.9 Shell Aplikasi & Layout

- **Satu shell** untuk semua modul: sidebar kiri, header atas, area konten utama.
- Sidebar menggunakan latar **Deep Navy** (`#141A5C`) dengan teks dan ikon putih. Item navigasi menggunakan `radius-md` (8px).
- Item navigasi aktif: highlighted dengan Spark Violet atau Navy lebih terang.
- Header: latar putih atau Navy, logo PERURI di kiri atas (minimal 120px lebar).
- Area konten: latar **Mist** (`#EEF0FB`) atau putih.
- Lebar konten maksimal: 1280px, dengan padding kiri-kanan `--space-6` (32px).
- Responsif: aplikasi minimal dapat digunakan di layar 1280px lebar (desktop). Mobile bukan prioritas utama untuk aplikasi internal.

### 9a.10 Larangan UI (HINDARI)

- **Jangan** memakai Spark Violet sebagai warna teks di atas latar putih/Mist.
- **Jangan** memakai `border-radius` ekstrem (>16px) pada kartu atau tombol biasa.
- **Jangan** memakai gradien warna-warni atau efek glassmorphism tanpa persetujuan.
- **Jangan** membuat komponen baru jika komponen yang ada sudah bisa dipakai.
- **Jangan** memakai font selain Lexend dan Inter.
- **Jangan** memakai spasi sembarangan — semua harus dari token spacing (kelipatan 4px).
- **Jangan** menaruh logo di atas latar selain putih atau Mist.
- **Jangan** mengubah proporsi, warna, atau orientasi logo.
- **Jangan** memakai bayangan berat atau berwarna selain turunan Deep Navy.
- **Jangan** membuat tombol berbentuk pill (radius-pill hanya untuk badge/tag).

## 10. Pengujian

- Gunakan **Pest** (atau kerangka yang ditetapkan repo). Tes berjalan pada database PostgreSQL uji, bukan SQLite, karena fitur seperti schema dan ltree.
- Wajib ada untuk setiap fitur:
  - tes fitur (HTTP) untuk jalur sukses,
  - tes **tidak terautentikasi** dan **tidak berizin**,
  - tes **data milik unit lain** ditolak (IDOR),
  - tes validasi input,
  - tes unit untuk logika di Service/Action.
- Wajib ada tes khusus untuk: penomoran (termasuk konkurensi), setiap policy dan scope, audit log, dan aturan batas modul (tes arsitektur).
- Bug diperbaiki dengan tes yang gagal terlebih dahulu (regression test).
- Dilarang menghapus, melewati (`skip`), atau melemahkan tes agar lulus. Jika tes salah, jelaskan alasannya di PR.
- Jangan mem-mock database untuk menguji perilaku query dan izin.

## 11. Git, commit, dan PR

- Satu perubahan, satu tujuan. Commit kecil dan bermakna dengan pesan yang jelas (disarankan Conventional Commits, mis. `feat(arsip): tambah penomoran surat keluar`).
- Jangan mencampur refactor, perbaikan format, dan fitur dalam satu commit.
- Jangan commit rahasia, berkas `.env`, dump database, atau data pegawai asli.
- Jangan melakukan force push, rebase pada branch bersama, atau menghapus branch tanpa izin.
- Deskripsi PR berisi: tujuan, perubahan utama, dampak keamanan dan data, cara menguji, dan hal yang belum diuji.

## 12. Definition of Done

Sebuah tugas baru selesai bila semua ini benar dan dilaporkan jujur:

- [ ] Perubahan sesuai batas modul dan lapisan (bagian 3).
- [ ] Tidak ada identitas, peran, atau status yang berasal dari input klien.
- [ ] Setiap action memiliki pemeriksaan izin dan scope, serta tes untuk data unit lain.
- [ ] Penomoran, berkas, dan audit memakai layanan bersama.
- [ ] Migrasi dapat dijalankan pada database kosong dan tidak mengubah migrasi lama.
- [ ] Tes baru ditulis dan seluruh tes lulus.
- [ ] Pint, Larastan, dan linter frontend lulus.
- [ ] Tidak ada rahasia, kode mati, atau `TODO` tanpa catatan.
- [ ] Dokumentasi terkait diperbarui (bagian 13).
- [ ] Hal yang tidak dapat diuji atau diverifikasi disebutkan secara eksplisit.

## 13. Dokumentasi

- Keputusan arsitektur yang penting dicatat sebagai **ADR** singkat di `docs/adr/` (konteks, keputusan, alasan, konsekuensi). Jangan mengubah keputusan lama secara diam-diam. Buat ADR baru yang menggantikannya.
- Perubahan pada model izin, penomoran, struktur modul, atau skema inti memperbarui dokumentasi ekosistem.
- README tiap modul berisi tujuan modul, layanan yang disediakan, dan izin yang didefinisikan.
- Dokumentasi memakai bahasa Indonesia yang jelas dan singkat.

## 14. Cara bekerja sebagai agent

1. **Baca dulu.** Sebelum mengubah apa pun, baca berkas terkait, tes yang ada, dan aturan ini. Jangan menebak struktur.
2. **Rencanakan perubahan besar.** Untuk perubahan lebih dari beberapa berkas, tulis rencana singkat sebelum mengerjakan.
3. **Kerjakan kecil dan bertahap.** Jangan menyentuh berkas di luar cakupan tugas. Jangan melakukan refactor yang tidak diminta.
4. **Verifikasi.** Jalankan tes dan pemeriksaan statis. Laporkan hasil sebenarnya, termasuk yang gagal. Jangan menyatakan "sudah berfungsi" tanpa menjalankannya.
5. **Jujur tentang ketidakpastian.** Jika tidak yakin tentang aturan bisnis, versi paket, atau perilaku sistem, katakan dan tanyakan. Jangan mengarang nama fungsi, paket, atau konfigurasi. Periksa dokumentasi resmi untuk versi yang terpasang di `composer.json` dan `package.json`.
6. **Sebelum menambah dependensi, fitur di luar permintaan, atau mengubah arsitektur, minta persetujuan.**

### Hal yang tidak boleh diputuskan agent sendiri

Berikut masih terbuka atau ditunda. Tanyakan, jangan mengasumsikan:

- aturan scope hak akses E-Arsip,
- format data impor HRIS dan skrip impornya,
- ukuran VM produksi dan kebijakan retensi backup,
- apakah SIM NEO dipisah menjadi deployment sendiri,
- integrasi gateway WhatsApp.

Sudah diputuskan: WOPPIT dan CMMS **tidak** digabung (WOPPIT di modul Layanan, CMMS di Manufaktur); absensi bersumber dari HRIS selama masa transisi; aplikasi lama tidak membutuhkan login terpusat.

## 15. Larangan

- Dilarang menjalankan perintah destruktif tanpa persetujuan eksplisit: `migrate:fresh`, `db:wipe`, `DROP`, `TRUNCATE`, `rm -rf`, `git push --force`, `git reset --hard`, terutama di luar lingkungan lokal.
- Dilarang menyentuh, membaca, atau menampilkan isi `.env`, kunci, atau kredensial.
- Dilarang mengakses atau mengubah lingkungan produksi, server Proxmox, atau backup.
- Dilarang mematikan CSRF, pemeriksaan izin, validasi, tes, linter, atau analisis statis demi membuat sesuatu lulus.
- Dilarang menyalin sandi, hash, atau data pribadi dari sistem lama ke repositori, tes, atau contoh.
- Dilarang menulis ulang riwayat audit atau mengubah audit log agar tidak append-only.
- Dilarang menambah paket pihak ketiga, layanan eksternal, atau pelacak tanpa persetujuan.
- Dilarang menaruh logika bisnis di controller, view, atau komponen Vue.
- Dilarang membuat fitur "sementara" yang melanggar aturan di atas dengan janji diperbaiki nanti.
