# ADR-004: Pola Riwayat Bertanggal untuk Mutasi Pegawai & Penugasan

## Status
Diterima (Accepted)

## Konteks
Dalam siklus karier pegawai di PT Kertas Padalarang, seorang pegawai dapat mengalami berbagai mutasi:
- Promosi atau rotasi unit kerja.
- Penugasan definitif (permanent), penugasan sementara (temporary), atau pelaksana tugas (acting/Plt).
- Perubahan jabatan struktural maupun fungsional.

Sistem tidak boleh menimpa (overwrite) data riwayat lama, melainkan harus mempertahankan rekaman historis lengkap untuk keperluan audit, analisis jenjang karier, pelacakan wewenang penandatanganan dokumen masa lalu, dan laporan kepegawaian.

## Keputusan
Kami mengadopsi **Pola Riwayat Bertanggal (Dated History / Effective Dating Pattern)** pada tabel penugasan `platform.employee_assignments`.

### Struktur Data
```sql
platform.employee_assignments (
    id UUID PRIMARY KEY,
    employee_id UUID REFERENCES platform.employees(id),
    org_unit_id UUID REFERENCES platform.org_units(id),
    position_id UUID REFERENCES platform.positions(id),
    started_at DATE NOT NULL,
    ended_at DATE NULL,               -- NULL menandakan penugasan yang SEDANG AKTIF
    assignment_type VARCHAR(20) NOT NULL, -- 'permanent', 'temporary', 'acting'
    notes TEXT NULL,
    created_by UUID NULL
)
```

### Mekanisme Mutasi
Saat pegawai dipindahtugaskan / dimutasi:
1. Penugasan aktif saat ini (`ended_at IS NULL`) ditutup dengan menyetel `ended_at = started_at_baru - 1 hari`.
2. Rekaman penugasan baru dibuat dengan `started_at = tanggal_mulai_efektif` dan `ended_at = NULL`.
3. Seluruh proses dieksekusi di dalam `DB::transaction` dan dicatat ke `platform.audit_logs`.

### Querying Posisi Aktif vs Historis
- **Penugasan Aktif**:
  ```php
  public function activeAssignment(): HasOne
  {
      return $this->hasOne(EmployeeAssignment::class)
          ->whereNull('ended_at')
          ->latest('started_at');
  }
  ```
- **Wewenang pada Tanggal Tertentu di Masa Lalu**:
  ```sql
  WHERE employee_id = :id AND started_at <= :date AND (ended_at IS NULL OR ended_at >= :date)
  ```

## Konsekuensi
### Positif
- **Integritas Historis 100%**: Tidak ada data riwayat yang hilang atau tertimpa.
- **Auditabilitas Temporal**: Sistem dapat memverifikasi wewenang seseorang secara retrospektif saat menandatangani dokumen di tanggal lampau.
- **Dukungan Multirangkap**: Fleksibel mendukung penugasan sementara tanpa membatalkan penugasan utama bila diperlukan.

### Negatif / Mitigasi
- Memerlukan relasi `activeAssignment` eksplisit agar pengembang tidak salah mengambil rekaman historis yang sudah berakhir (`ended_at IS NOT NULL`).
