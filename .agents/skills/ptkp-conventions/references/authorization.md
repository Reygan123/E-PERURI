# Otorisasi dan scope

Baca saat membuat policy, query berbasis scope, peran, atau izin.

## Model

- **Deny by default.** Tanpa izin eksplisit berarti ditolak.
- Format izin: `modul.sumber.aksi`, mis. `arsip.surat.create`.
- Setiap pemberian izin ke peran punya scope: `own`, `unit`, `unit_tree`, `all`.
- Penugasan peran ke pengguna dapat dibatasi ke unit tertentu dan punya masa berlaku (`valid_from`, `valid_until`).

## Aturan implementasi

- Setiap action controller memanggil `authorize()` atau policy. Tidak ada endpoint tanpa pemeriksaan.
- Scope diterapkan **di query** (global scope atau query scope), sehingga data di luar scope tidak pernah ikut terambil. Menyembunyikan di tampilan tidak dihitung.
- Menyembunyikan tombol di frontend bukan pengamanan.
- Identitas dan unit pengguna selalu dari sesi server, termasuk saat menentukan `own` / `unit` / `unit_tree`.

## Tes wajib per policy dan scope

Untuk setiap kombinasi yang relevan (`own`, `unit`, `unit_tree`, `all`): diizinkan, ditolak, dan **data milik unit lain** (harus 403/404). Tanpa login harus 401/redirect.

## Jangan diasumsikan

Aturan scope E-Arsip belum diputuskan. Seeder yang ada hanya titik awal dari aturan lama. Tanyakan, jangan menetapkan aturan baru.
