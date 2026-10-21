<?php

namespace App\Modules\Platform\Services;

use App\Modules\Platform\Contracts\FilesServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FilesService implements FilesServiceInterface
{
    /**
     * Store an uploaded file securely with cryptographic hash and metadata.
     *
     * @param  UploadedFile  $file  The uploaded file
     * @param  string  $module  The originating module (e.g. 'arsip', 'layanan')
     * @param  string  $category  The document category (e.g. 'surat_masuk', 'lampiran')
     * @param  string|null  $uploadedByEmployeeId  UUID of uploader
     * @return array<string, mixed> File metadata containing id, disk, path, hash_sha256, mime, size
     */
    public function store(UploadedFile $file, string $module, string $category, ?string $uploadedByEmployeeId = null): array
    {
        $disk = 'local';
        $fileId = (string) Str::uuid();
        $hashSha256 = hash_file('sha256', $file->getRealPath()) ?: '';
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $sizeBytes = $file->getSize();
        $extension = $file->getClientOriginalExtension() ?: 'bin';

        // Secure stored filename outside web root
        $storedName = $fileId.'.'.$extension;
        $directory = 'platform_files/'.$module.'/'.$category.'/'.now()->format('Y/m');
        $storedPath = $file->storeAs($directory, $storedName, ['disk' => $disk]);

        DB::table('platform.files')->insert([
            'id' => $fileId,
            'uploader_id' => $uploadedByEmployeeId,
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $storedName,
            'disk' => $disk,
            'path' => $storedPath ?: $directory.'/'.$storedName,
            'mime_type' => $mimeType,
            'size_bytes' => $sizeBytes,
            'hash_sha256' => $hashSha256,
            'entity_type' => $category,
            'entity_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'id' => $fileId,
            'disk' => $disk,
            'path' => $storedPath ?: $directory.'/'.$storedName,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $mimeType,
            'size_bytes' => $sizeBytes,
            'hash_sha256' => $hashSha256,
        ];
    }

    /**
     * Get storage path for a file record ID.
     */
    public function getPath(string $fileId): ?string
    {
        $record = DB::table('platform.files')->where('id', $fileId)->first();

        return $record?->path;
    }

    /**
     * Verify file integrity by comparing recorded SHA-256 against actual file bytes.
     */
    public function verifyIntegrity(string $fileId): bool
    {
        $record = DB::table('platform.files')->where('id', $fileId)->first();
        if (! $record || ! $record->path) {
            return false;
        }

        if (! Storage::disk($record->disk)->exists($record->path)) {
            return false;
        }

        $fullPath = Storage::disk($record->disk)->path($record->path);
        $currentHash = hash_file('sha256', $fullPath);

        return hash_equals((string) $record->hash_sha256, (string) $currentHash);
    }
}
