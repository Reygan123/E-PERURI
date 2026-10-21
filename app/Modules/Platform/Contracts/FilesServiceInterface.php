<?php

namespace App\Modules\Platform\Contracts;

use Illuminate\Http\UploadedFile;

interface FilesServiceInterface
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
    public function store(UploadedFile $file, string $module, string $category, ?string $uploadedByEmployeeId = null): array;

    /**
     * Get storage path or stream URL for a file record ID.
     */
    public function getPath(string $fileId): ?string;

    /**
     * Verify file integrity by comparing recorded SHA-256 against actual file bytes.
     */
    public function verifyIntegrity(string $fileId): bool;
}
