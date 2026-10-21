# ADR-006: Model Otorisasi Berbasis Izin dan Batas Cakupan (Scope)

## Status
Diterima (Accepted)

## Konteks
Sistem internal PT Kertas Padalarang memiliki struktur hierarki organisasi 4 level (Direksi -> Divisi -> Departemen -> Seksi) dan 37 unit kerja. Pengguna dengan peran yang sama (misalnya: "Pimpinan Unit" atau "Operator Administrasi") memiliki tanggung jawab yang berbeda tergantung unit tempat mereka ditugaskan.

Model otorisasi Role-Based Access Control (RBAC) murni yang hanya mengecek `hasRole('pimpinan')` atau `hasPermission('documents.approve')` TIDAK CUKUP, karena akan menimbulkan kerentanan **Insecure Direct Object Reference (IDOR)** — seorang Kepala Seksi di unit Pengadaan dapat menyetujui atau mengubah data pegawai di unit Pengolahan Limbah.

## Keputusan
Kami mengimplementasikan model otorisasi **Role-Based + Scope-Constrained (RBAC + Scope)**.

### Empat Tingkatan Cakupan (Scope Hierarchy)
1. **`own`**: Hanya record milik pegawai/user yang bersangkutan (misal: data profil pribadi, pengajuan cuti pribadi, slip gaji).
2. **`unit`**: Record yang berada di unit organisasi langsung tempat user ditugaskan.
3. **`unit_tree`**: Record di unit organisasi tempat user ditugaskan **beserta seluruh sub-unit di bawahnya** secara rekursif (misal: Kepala Divisi Operasi dapat melihat data di Departemen Produksi dan seluruh Seksi di bawahnya).
4. **`all`**: Record di seluruh organisasi tanpa batasan unit (khusus Direksi, Superadmin, dan Auditor Terpusat).

### Penyimpanan Penugasan Peran
Pada tabel `platform.user_roles`:
```sql
platform.user_roles (
    id UUID PRIMARY KEY,
    user_id UUID REFERENCES platform.users(id),
    role_id UUID REFERENCES platform.roles(id),
    org_unit_id UUID NULL REFERENCES platform.org_units(id), -- Batas unit scope
    valid_from DATE NOT NULL,
    valid_until DATE NULL
)
```

### Evaluasi Otorisasi di Policy
Setiap Policy Laravel mengevaluasi kombinasi izin dan scope melalui helper `DirectoryService`:
```php
public function update(User $user, Employee $employee): bool
{
    // 1. Cek peran Superadmin (scope: all)
    if ($user->hasRole('super-admin')) {
        return true;
    }

    // 2. Cek izin spesifik
    if (! $user->hasPermission('platform.employees.update')) {
        return false;
    }

    // 3. Evaluasi scope unit
    $userScope = $this->resolveUserScope($user, 'platform.employees.update');
    
    return match ($userScope) {
        'all' => true,
        'unit_tree' => $this->directoryService->isEmployeeInOrgUnit(
            $employee->id,
            $user->current_org_unit_id,
            recursive: true
        ),
        'unit' => $this->directoryService->isEmployeeInOrgUnit(
            $employee->id,
            $user->current_org_unit_id,
            recursive: false
        ),
        'own' => $user->employee_id === $employee->id,
        default => false,
    };
}
```

## Konsekuensi
### Positif
- **Pencegahan IDOR Mutlak**: Tidak ada celah manipulasi objek lintas unit kerja tanpa izin sah.
- **Fleksibilitas Multidisiplin**: Seorang pegawai dapat memegang peran di unit A dan peran lain di unit B dengan batasan masa berlaku terpisah.
- **Telah Tervalidasi Pengujian**: Diverifikasi pada `PlatformAuthorizationMatrixTest` dan `AuthAndAuthorizationTest`.

### Negatif / Mitigasi
- Query hirarki unit memerlukan penelusuran sub-unit (dimitigasi dengan caching daftar sub-unit pada `DirectoryService`).
