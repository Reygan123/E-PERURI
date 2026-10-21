# UI: Brand Guideline PTKP (ringkasan dari AGENTS.md bagian 9a)

Semua antarmuka wajib mengikuti ini. Jangan membuat sistem desain sendiri. Gunakan **variabel CSS
design token**, bukan nilai hardcode di komponen. Jika AGENTS.md 9a berbeda dengan berkas ini,
AGENTS.md menang.

## Warna

| Token | Nilai | Fungsi |
|---|---|---|
| `--color-navy` | `#20288E` | Utama: heading, tombol primer, elemen aktif |
| `--color-violet` | `#D861FF` | Aksen; **hanya di atas latar Navy/Deep Navy** |
| `--color-deep-navy` | `#141A5C` | Sidebar, header gelap, hover tombol primer |
| `--color-mist` | `#EEF0FB` | Latar halaman, kartu sekunder, input disabled |
| `--color-white` | `#FFFFFF` | Latar kartu, form, konten |
| `--color-text` | `#20288E` | Heading |
| `--color-text-body` | `#1A1A2E` | Paragraf dan label |
| `--color-border` | `rgba(32,40,142,0.14)` | Border dan divider |

- Violet dilarang sebagai warna teks di atas putih/Mist, dan dilarang sebagai tombol di latar putih/Mist.
- Warna notifikasi (hijau tua sukses, kuning tua peringatan, merah tua error) dan abu netral untuk badge **belum punya token**. Jangan di-hardcode. Tanyakan atau usulkan token dan minta persetujuan.

## Tipografi

- Heading (h1-h4), label navigasi utama, nama tombol penting: **Lexend** Bold 700.
- Semua teks lain: **Inter** 400/500.
- Tidak ada font lain. Minimum 12px. Skala kelipatan 4px (12, 14, 16, 20, 24, 32, 40).

## Spasi (kelipatan 4px)

`--space-1` 4, `--space-2` 8, `--space-3` 12, `--space-4` 16, `--space-5` 24, `--space-6` 32, `--space-7` 48, `--space-8` 64.

## Radius dan bayangan

- `--radius-sm` 4px: tombol, input, kartu (default). `--radius-md` 8px: panel besar, modal, drawer. `--radius-pill` 999px: **hanya badge/tag**.
- Dilarang radius 12px atau lebih untuk kartu/tombol biasa.
- Datar: `border: 1px solid var(--color-border)`. Terangkat (dropdown, modal): `box-shadow: 0 2px 8px rgba(32,40,142,0.14)`. Tidak ada bayangan lain.

## Komponen

- **Tombol**: Utama (bg Navy, teks putih), Sekunder (border Navy, transparan), Aksen (bg Violet, hanya di latar gelap), Teks. Semua `radius-sm`, teks bahasa Indonesia.
- **Input**: border `--color-border`, radius 4px, fokus border Navy, error border merah + pesan Indonesia di bawah field, label selalu di atas (bukan placeholder).
- **Kartu**: bg putih, border `--color-border`, radius 4px, padding `--space-5`, bayangan hanya bila interaktif/mengambang.
- **Badge**: satu-satunya yang boleh pill.

## Shell

Satu shell untuk semua modul: sidebar kiri Deep Navy (teks/ikon putih), header putih atau Navy dengan logo PERURI di kiri atas (min. lebar 120px), konten Mist atau putih, lebar maks 1280px dengan padding kiri-kanan `--space-6`. Utamakan desktop (minimal 1280px).

## Logo

Hanya di latar putih atau Mist. Jangan ubah warna, putar, regangkan, beri bayangan/outline, atau taruh di atas foto ramai.

## Larangan

Gradien warna-warni, glassmorphism, font selain Lexend/Inter, spasi di luar token, komponen baru bila yang ada cukup, bayangan berat atau berwarna, tombol pill, ilustrasi baru di luar elemen warisan arsitektur (atap limas, cerobong berpita, batu kali, kaca lavender) yang hanya boleh sebagai dekorasi subtle.
