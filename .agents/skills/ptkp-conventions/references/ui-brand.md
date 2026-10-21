# UI: Brand Guideline PTKP (ringkasan dari AGENTS.md bagian 9a)

Semua antarmuka wajib mengikuti ini. Jangan membuat sistem desain sendiri. Gunakan **variabel CSS
design token**, bukan nilai hardcode di komponen. Jika AGENTS.md 9a berbeda dengan berkas ini,
AGENTS.md menang.

## Warna

| Token | Nilai | Fungsi |
|---|---|---|
| `--color-navy` | `#20288E` | Utama: heading, tombol primer, elemen aktif (`ptkp-navy`) |
| `--color-violet` | `#D861FF` | Aksen; **hanya di atas latar Navy/Deep Navy** (`ptkp-violet`) |
| `--color-deep-navy` | `#141A5C` | Sidebar, header gelap, hover tombol primer (`ptkp-deep`) |
| `--color-mist` | `#EEF0FB` | Latar halaman, kartu sekunder, input disabled (`ptkp-mist`) |
| `--color-white` | `#FFFFFF` | Latar kartu, form, konten |
| `--color-text` | `#20288E` | Heading |
| `--color-text-body` | `#1A1A2E` | Paragraf dan label |
| `--color-border` | `rgba(32,40,142,0.14)` | Border dan divider (`ptkp-border`) |
| `--color-success` | `#1E7E34` | Notifikasi & status sukses/aktif (`ptkp-success`) |
| `--color-warning` | `#B7791F` | Notifikasi peringatan/pending (`ptkp-warning`) |
| `--color-error` | `#C53030` | Notifikasi error/batal/nonaktif (`ptkp-error`) |
| `--color-neutral` | `#4A5568` | Badge/tag netral (`ptkp-neutral`) |

- Violet dilarang sebagai warna teks di atas putih/Mist, dan dilarang sebagai tombol di latar putih/Mist.
- Tailwind CSS v3 digunakan di seluruh komponen dengan mapping palet `ptkp.*` di `tailwind.config.js` merujuk ke CSS variables token di atas.

## Tipografi

- Heading (h1-h4), label navigasi utama, nama tombol penting: **Lexend** Bold 700.
- Semua teks lain: **Inter** 400/500.
- Tidak ada font lain. Minimum 12px. Skala kelipatan 4px (12, 14, 16, 20, 24, 32, 40).

## Spasi (kelipatan 4px)

`--space-1` 4, `--space-2` 8, `--space-3` 12, `--space-4` 16, `--space-5` 24, `--space-6` 32, `--space-7` 48, `--space-8` 64.

## Radius dan bayangan (Seamless Concentric Hierarchy)

- `--radius-xs` 4px: micro-chips, status dots, badge mini.
- `--radius-sm` 6px: input, select, textarea, filter chips.
- `--radius-md` 8px: tombol aksi, alert box, active dropdown.
- `--radius-lg` 12px: kartu utama (card), kontainer panel, modal dialog, table wrapper.
- `--radius-pill` 999px: **hanya badge/tag**.
- Dilarang radius ekstrem 16px atau lebih pada kartu/tombol biasa.
- Datar: `border: 1px solid var(--color-border)`.
- Elevasi kartu halus: `box-shadow: 0 1px 3px rgba(20, 26, 92, 0.04), 0 1px 2px rgba(20, 26, 92, 0.02)`.
- Terangkat (dropdown, modal): `box-shadow: 0 4px 16px rgba(20, 26, 92, 0.08)`.

## Komponen

- **Tombol**: Utama (bg Navy, teks putih), Sekunder (border Navy, transparan), Aksen (bg Violet, hanya di latar gelap), Teks. Semua `radius-md` (8px), teks bahasa Indonesia.
- **Input / Select**: border `--color-border`, radius `radius-sm` (6px), fokus border Navy + ring tipis, error border merah + pesan Indonesia di bawah field, label selalu di atas.
- **Kartu**: bg putih, border `--color-border`, radius `radius-lg` (12px), elevasi halus `shadow-card`, padding header `px-6 py-4`, padding konten `p-6`.
- **Badge**: satu-satunya yang boleh pill (`radius-pill`).

## Shell

Satu shell untuk semua modul: sidebar kiri Deep Navy (teks/ikon putih), header putih atau Navy dengan logo PERURI di kiri atas (min. lebar 120px), konten Mist atau putih, lebar maks 1280px dengan padding kiri-kanan `--space-6`. Utamakan desktop (minimal 1280px).

## Logo

Hanya di latar putih atau Mist. Jangan ubah warna, putar, regangkan, beri bayangan/outline, atau taruh di atas foto ramai.

## Larangan

Gradien warna-warni, glassmorphism, font selain Lexend/Inter, spasi di luar token, komponen baru bila yang ada cukup, bayangan berat atau berwarna, tombol pill, ilustrasi baru di luar elemen warisan arsitektur (atap limas, cerobong berpita, batu kali, kaca lavender) yang hanya boleh sebagai dekorasi subtle.
