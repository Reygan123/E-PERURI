# ADR-005: Enkripsi Kolom untuk Data Sensitif Pegawai (At-Rest Encryption)

## Status
Diterima (Accepted)

## Konteks
Sesuai kepatuhan Undang-Undang Perlindungan Data Pribadi (UU PDP No. 27 Tahun 2022) dan standar keamanan informasi BUMN, data identitas pribadi pegawai (seperti NIK - Nomor Induk Kependudukan, nomor rekening gaji, data sensitif keluarga) tidak boleh disimpan dalam format plaintext telanjang pada database.

Jika terjadi kebocoran dump database fisik atau unauthorized database access, data identitas sensitif pegawai harus tetap terlindungi.

## Keputusan
Kami mengimplementasikan **Column-Level Encryption (At-Rest)** pada atribut sensitif model Eloquent menggunakan mekanisme enkripsi AES-256-CBC bawaan Laravel (`Illuminate\Database\Eloquent\Casts\EncryptedAttribute` / `'encrypted'`).

### Implementasi pada Model `Employee`
```php
namespace App\Modules\Platform\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $casts = [
        'nik' => 'encrypted',
        'birth_date' => 'date',
        'is_active' => 'boolean',
    ];
}
```

### Karakteristik:
1. **Enkripsi Otomatis saat Simpan**: Saat `Employee::create(['nik' => '3201...'])` dipanggil, Laravel secara transparan mengenkripsi string menggunakan kunci aplikasi `APP_KEY` dan cipher AES-256-CBC sebelum query SQL `INSERT` dikirim ke PostgreSQL.
2. **Dekripsi Otomatis saat Akses**: Saat `$employee->nik` diakses di kode aplikasi, nilai didekripsi kembali secara otomatis.
3. **Penyimpanan Fisik**: Pada kolom database `platform.employees.nik` (tipe teks/varchar), isi yang tersimpan berupa ciphertext terenkripsi (misal: `eyJpdiI6I...`).

## Konsekuensi
### Positif
- **Kepatuhan Regulasi**: Memenuhi persyaratan UU PDP dan audit kepatuhan keamanan data BUMN.
- **Transparansi Developer**: Pengembang tidak perlu secara manual memanggil `Crypt::encryptString()` dan `Crypt::decryptString()` pada setiap operasi CRUD.
- **Telah Tervalidasi Pengujian**: Diverifikasi pada `PlatformMasterDataTest` bahwa data tersimpan terenkripsi namun dapat diakses secara transparan oleh aplikasi.

### Negatif / Mitigasi
- Pencarian langsung dengan `WHERE nik = ?` biasa tidak dapat dilakukan secara langsung pada ciphertext acak. Jika di masa depan diperlukan pencarian NIK, akan ditambahkan kolom hash satu arah (`nik_hash = hash_hmac('sha256', $nik, $salt)`) sebagai indeks pencarian buta (blind index).
- Manajemen `APP_KEY`: Kunci enkripsi aplikasi harus diamankan secara ketat dan dicadangkan secara aman. Rotasi `APP_KEY` harus menggunakan script re-encryption terencana.
