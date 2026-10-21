# ADR-003: Mekanisme Penomoran Dokumen Atomik & Anti-Duplikasi

## Status
Diterima (Accepted)

## Konteks
Sebagai BUMN penyedia kertas berharga dan dokumen sekuriti, PT Kertas Padalarang memiliki tata naskah dinas resmi dan penomoran dokumen legal yang sangat ketat (Surat Keputusan, Perjanjian Kerja Sama, Memo Internal, Surat Tugas, Berita Acara). Penomoran dokumen tidak boleh memiliki celah menghasilkan:
- Nomor ganda (duplicate numbers).
- Nomor lompat yang tidak tercatat (missing sequence).
- Race condition saat beberapa pengguna mengklik tombol terbitkan surat secara simultan di jam sibuk.

## Keputusan
Kami mengimplementasikan **Atomic Sequential Numbering Service** menggunakan:
1. Tabel konfigurasi format template (`platform.numbering_formats`).
2. Tabel pencatat urutan sequence per periode (`platform.numbering_sequences`).
3. **Pessimistic Row Locking (`SELECT ... FOR UPDATE`)** di dalam transaksi database ACID (`DB::transaction`).

### Algoritma Penomoran
```php
DB::transaction(function () use ($category, $orgUnitCode, $context) {
    // 1. Ambil format dokumen
    $format = NumberingFormat::where('code', $category)->first();

    // 2. Tentukan kunci periode (Tahunan: '2026', Bulanan: '2026-04', Abadi: 'ALL')
    $periodKey = ...;

    // 3. Kunci baris sequence untuk transaksi ini secara eksklusif
    $sequence = NumberingSequence::where('format_id', $format->id)
        ->where('period_key', $periodKey)
        ->lockForUpdate()
        ->first();

    // 4. Tambah nilai sequence tepat 1 dan simpan
    $nextSeq = $sequence->last_sequence + 1;
    $sequence->update(['last_sequence' => $nextSeq]);

    // 5. Formatkan ke template nomor ({SEQ}/{PREFIX}/PTKP/{UNIT}/{BULAN}/{TAHUN})
    return str_replace(...);
});
```

### Reset Period Terdukung
- `yearly`: Reset ke `001` setiap pergantian tahun takwim.
- `monthly`: Reset ke `001` setiap awal bulan takwim baru.
- `never`: Urutan terus bertambah tanpa reset.

## Konsekuensi
### Positif
- **Zero Race Condition**: Baris tabel sequence dikunci secara eksklusif oleh PostgreSQL selama fraksi milidetik saat transaksi berjalan, menjamin urutan 100% konsisten.
- **Telah Teruji Konkurensi**: Diverifikasi dengan automated test 50 permintaan simultan tanpa duplikasi (`ConcurrencyNumberingTest`).
- **Auditabilitas Penuh**: Format dan nomor yang dihasilkan tercatat rapi di sistem.

### Negatif / Mitigasi
- Transaksi locking singkat memblokir proses lain pada kategori dan periode yang sama selama beberapa milidetik. Transaksi dibuat sesingkat mungkin tanpa operasi I/O eksternal di dalam blok lock.
