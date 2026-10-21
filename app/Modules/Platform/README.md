# Modul Platform — Fondasi & Master Data PT Kertas Padalarang (PTKP)

## 1. Pendahuluan
Modul **Platform** adalah fondasi inti (core foundation) dari seluruh ekosistem aplikasi internal PT Kertas Padalarang v2. Modul ini bertanggung jawab menyediakan layanan identitas pengguna, hierarki organisasi, master data pegawai, sistem penomoran dokumen resmi, buku pencatatan audit log yang tidak dapat diubah (immutable append-only), penyimpanan berkas aman, serta perutean notifikasi terpadu.

Seluruh modul bisnis lain dalam ekosistem PTKP (`Arsip`, `Layanan`, `Sdm`, `Pembelajaran`, `Manufaktur`) berdiri di atas layanan yang disediakan oleh Modul Platform.

---

## 2. Struktur Modul

```
app/Modules/Platform/
├── Contracts/              # Interface publik yang boleh di-inject oleh modul lain
│   ├── AuditServiceInterface.php
│   ├── DirectoryServiceInterface.php
│   ├── FilesServiceInterface.php
│   ├── NotificationServiceInterface.php
│   ├── NumberingServiceInterface.php
│   └── UserResolverInterface.php
├── Database/
│   ├── Migrations/         # 12 migrasi skema platform.*
│   └── Seeders/            # Seeder unit organisasi, jabatan, peran, izin, dan dev users
├── Http/
│   └── Controllers/        # Controller admin CRUD & API master data
│       ├── AuditLogController.php
│       ├── EmployeeController.php
│       ├── NumberingFormatController.php
│       ├── OrgUnitController.php
│       ├── PositionController.php
│       └── UserRoleController.php
├── Models/                 # Eloquent model tabel platform.*
│   ├── AuditLog.php
│   ├── Employee.php
│   ├── EmployeeAssignment.php
│   ├── FileRecord.php
│   ├── NotificationRecord.php
│   ├── NumberingCounter.php
│   ├── NumberingFormat.php
│   ├── NumberingSequence.php
│   ├── OrgUnit.php
│   ├── Permission.php
│   ├── Position.php
│   ├── Role.php
│   ├── SystemParameter.php
│   └── UserRole.php
├── Policies/               # Otorisasi berbasis RBAC + Unit Scope (own, unit, unit_tree, all)
│   ├── EmployeePolicy.php
│   ├── NumberingFormatPolicy.php
│   ├── OrgUnitPolicy.php
│   ├── PositionPolicy.php
│   └── UserPolicy.php
├── Providers/
│   └── PlatformServiceProvider.php
├── Services/               # Implementasi konkret dari Contracts
│   ├── AuditService.php
│   ├── DirectoryService.php
│   ├── FilesService.php
│   ├── NotificationService.php
│   ├── NumberingService.php
│   └── UserResolver.php
└── routes.php              # Rute web dan API internal modul Platform
```

---

## 3. Kontrak Layanan Publik (Public Contracts)

Modul lain **DILARANG** mengakses Eloquent Model modul Platform secara langsung. Modul lain **WAJIB** menggunakan Dependency Injection terhadap salah satu Interface Kontrak Publik berikut:

### 3.1 `DirectoryServiceInterface`
Digunakan untuk menavigasi struktur organisasi dan relasi pegawai:
- `getEmployee(string $id): ?Employee`
- `getOrgUnit(string $id): ?OrgUnit`
- `getEmployeesInUnit(string $orgUnitId, bool $includeSubunits = false): Collection`
- `getSubordinateOrgUnitIds(string $orgUnitId): array`
- `getCurrentAssignment(string $employeeId): ?EmployeeAssignment`
- `isEmployeeInOrgUnit(string $employeeId, string $orgUnitId, bool $recursive = false): bool`

### 3.2 `NumberingServiceInterface`
Digunakan untuk mencetak nomor surat / dokumen naskah dinas resmi secara atomik anti-duplikasi:
- `generateNumber(string $category, string $orgUnitCode, array $context = []): string`
- `recordNumber(string $documentNumber, string $category, string $orgUnitCode, array $metadata = []): void`

### 3.3 `AuditServiceInterface`
Digunakan untuk mencatat aktivitas penting ke buku audit log append-only:
- `log(string $module, string $action, string $auditableType, string $auditableId, ?array $oldValues = null, ?array $newValues = null, ?string $notes = null): void`

### 3.4 `FilesServiceInterface`
Digunakan untuk mengunggah dan memverifikasi integritas berkas digital:
- `store(UploadedFile $file, string $module, string $category, ?string $disk = null): array`
- `get(string $fileId): ?FileRecord`
- `verifyIntegrity(string $fileId): bool` (Pengecekan SHA-256)
- `delete(string $fileId): bool`

### 3.5 `NotificationServiceInterface`
Digunakan untuk mengirim notifikasi ke pegawai:
- `send(string $employeeId, string $title, string $message, string $type = 'info', ?string $actionUrl = null): NotificationRecord`
- `getUnread(string $employeeId): Collection`
- `markAsRead(string $notificationId): bool`

### 3.6 `UserResolverInterface`
Digunakan untuk menyelesaikan konteks pengguna aktif:
- `resolveCurrentUser(): ?User`
- `resolveUserOrgUnit(?User $user = null): ?OrgUnit`
- `resolveUserRoles(?User $user = null): Collection`
- `resolveUserPermissions(?User $user = null): Collection`

---

## 4. Contoh Pemakaian dari Modul Lain

Contoh pada `app/Modules/Arsip/Services/SuratKeluarService.php`:

```php
namespace App\Modules\Arsip\Services;

use App\Modules\Platform\Contracts\DirectoryServiceInterface;
use App\Modules\Platform\Contracts\NumberingServiceInterface;
use App\Modules\Platform\Contracts\AuditServiceInterface;

class SuratKeluarService
{
    public function __construct(
        protected DirectoryServiceInterface $directory,
        protected NumberingServiceInterface $numbering,
        protected AuditServiceInterface $audit
    ) {}

    public function terbitkanSurat(string $unitId, string $perihal): string
    {
        $unit = $this->directory->getOrgUnit($unitId);

        // Generate nomor dokumen atomik
        $nomorSurat = $this->numbering->generateNumber(
            category: 'SURAT_KELUAR',
            orgUnitCode: $unit->code,
            context: ['year' => date('Y'), 'month' => date('n')]
        );

        // Catat ke audit log
        $this->audit->log(
            module: 'arsip',
            action: 'TERBITKAN_SURAT',
            auditableType: 'SuratKeluar',
            auditableId: $suratId,
            newValues: ['nomor' => $nomorSurat, 'perihal' => $perihal]
        );

        return $nomorSurat;
    }
}
```

---

## 5. Daftar Izin Modul Platform

Format penamaan izin: `platform.{entitas}.{aksi}`.

| Kode Izin | Deskripsi |
|---|---|
| `platform.org_units.view` | Melihat struktur unit organisasi |
| `platform.org_units.create` | Membuat unit organisasi baru |
| `platform.org_units.update` | Mengubah informasi unit organisasi |
| `platform.org_units.delete` | Menonaktifkan unit organisasi |
| `platform.positions.view` | Melihat master jabatan |
| `platform.positions.create` | Membuat master jabatan baru |
| `platform.positions.update` | Mengubah master jabatan |
| `platform.positions.delete` | Menonaktifkan master jabatan |
| `platform.employees.view` | Melihat direktori pegawai |
| `platform.employees.create` | Mendaftarkan pegawai baru + akun |
| `platform.employees.update` | Mengubah data pegawai / mutasi |
| `platform.employees.delete` | Menonaktifkan pegawai |
| `platform.users.manage` | Mengelola akun, peran, dan reset password |
| `platform.numbering.manage` | Mengonfigurasi template nomor dokumen |
| `platform.audit_logs.view` | Membaca buku audit log sistem |

---

## 6. Penjaminan Kualitas (Quality Gates)

Modul Platform harus selalu memenuhi standar:
1. `composer lint:test` (Pint PSR-12 clean)
2. `composer analyse` (Larastan Level 5 — 0 errors)
3. `composer deptrac` (0 violations)
4. `composer test` (100% test pass)
5. `npm run build` (Vite client & SSR clean build)
