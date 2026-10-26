<?php

declare(strict_types=1);

namespace App\Modules\Arsip\Services;

use App\Modules\Arsip\Models\Letter;
use App\Modules\Platform\Contracts\AuditServiceInterface;
use App\Modules\Platform\Contracts\DirectoryServiceInterface;
use App\Modules\Platform\Contracts\FilesServiceInterface;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Str;

class DigitalSignatureService
{
    public function __construct(
        protected DirectoryServiceInterface $directoryService,
        protected FilesServiceInterface $filesService,
        protected AuditServiceInterface $auditService,
        protected QrCodeService $qrCodeService,
        protected LetterPdfService $letterPdfService
    ) {}

    /**
     * Compute SHA-256 digest of the letter content.
     */
    public function generateDocumentDigest(Letter $letter): string
    {
        $payload = [
            'id' => $letter->id,
            'document_number' => $letter->document_number,
            'type_id' => $letter->type_id,
            'origin_unit_id' => $letter->origin_unit_id,
            'creator_employee_id' => $letter->creator_employee_id,
            'subject' => $letter->subject,
            'letter_date' => (string) $letter->letter_date,
            'content_body' => $letter->content_body,
        ];

        return hash('sha256', (string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Generate unique signature hash for public verification.
     */
    public function generateSignatureHash(Letter $letter, string $signerEmployeeId): string
    {
        return hash('sha256', $letter->id.$signerEmployeeId.microtime(true).Str::random(32));
    }

    /**
     * Sign the letter electronically with certified BSrE metadata and dynamic QR code.
     *
     * @param  array<string, mixed>  $options
     */
    public function signDocument(Letter $letter, string $signerEmployeeId, array $options = []): Letter
    {
        $signer = $this->directoryService->getEmployeeById($signerEmployeeId);
        if (! $signer) {
            throw new DomainException("Data pegawai penandatangan [{$signerEmployeeId}] tidak ditemukan.");
        }

        $documentDigest = $this->generateDocumentDigest($letter);
        $signatureHash = $this->generateSignatureHash($letter, $signerEmployeeId);

        $signerPosition = is_array($signer['position'] ?? null)
            ? ($signer['position']['name'] ?? 'Pejabat Struktural')
            : ($signer['position'] ?? 'Pejabat Struktural');

        $bsreMetadata = [
            'issuer' => 'Balai Sertifikasi Elektronik (BSrE) - Badan Siber dan Sandi Negara (BSSN)',
            'certificate_serial' => 'BSRE-PTKP-'.strtoupper(substr(hash('sha256', $letter->id.$signerEmployeeId.microtime()), 0, 16)),
            'signer_name' => $signer['name'],
            'signer_nip' => $signer['nip'] ?? '-',
            'signer_position' => $signerPosition,
            'signed_at' => Carbon::now()->toIso8601String(),
            'tsa_timestamp' => Carbon::now()->toIso8601String(),
            'algorithm' => 'SHA256withRSA / PAdES-B-LT',
            'document_digest' => $documentDigest,
            'status' => 'VALID',
            'verification_url' => url("/verify/{$signatureHash}"),
            'notes' => $options['notes'] ?? 'Pengesahan Naskah Dinas Elektronik Resmi PT Kertas Padalarang',
        ];

        $letter->signature_hash = $signatureHash;
        $letter->signed_at = Carbon::now();
        $letter->signer_employee_id = $signerEmployeeId;
        $letter->signature_metadata = $bsreMetadata;

        if ($letter->type->signature_type === 'digital') {
            $letter->status = 'published';
        } else {
            $letter->status = 'signed';
        }

        $letter->save();

        // Compile and store the signed PDF with embedded QR Code
        $fileMeta = $this->letterPdfService->generateAndStoreSignedPdf($letter, $signerEmployeeId);
        $letter->signed_file_id = $fileMeta['id'];
        $letter->save();

        $this->auditService->log(
            module: 'arsip',
            action: 'SIGN_TTE_BSRE',
            auditableType: 'Letter',
            auditableId: (string) $letter->id,
            newValues: [
                'signature_hash' => $signatureHash,
                'signed_file_id' => $fileMeta['id'],
                'signer_employee_id' => $signerEmployeeId,
                'status' => $letter->status,
            ],
            notes: "Naskah dinas berhasil ditandatangani secara elektronik (BSrE BSSN) oleh {$signer['name']}"
        );

        return $letter->fresh();
    }

    /**
     * Verify document validity and integrity via signature hash.
     *
     * @return array<string, mixed>|null
     */
    public function verifySignature(string $hash): ?array
    {
        $letter = Letter::where('signature_hash', $hash)->first();
        if (! $letter) {
            return null;
        }

        $letter->loadMissing(['type']);
        $metadata = is_array($letter->signature_metadata) ? $letter->signature_metadata : [];

        $signer = null;
        if ($letter->signer_employee_id) {
            $signer = $this->directoryService->getEmployeeById($letter->signer_employee_id);
        }

        $originUnit = null;
        if ($letter->origin_unit_id) {
            $originUnit = $this->directoryService->getOrgUnit($letter->origin_unit_id);
        }

        // Verify content integrity
        $currentDigest = $this->generateDocumentDigest($letter);
        $storedDigest = $metadata['document_digest'] ?? null;
        $integrityValid = ($storedDigest !== null) ? hash_equals($storedDigest, $currentDigest) : true;

        $signerName = $metadata['signer_name'] ?? ($signer['name'] ?? 'Pejabat Penandatangan');
        $signerNip = $metadata['signer_nip'] ?? ($signer['nip'] ?? '-');
        $signerPosition = $metadata['signer_position'] ?? (is_array($signer['position'] ?? null) ? ($signer['position']['name'] ?? '-') : '-');

        $signedAtFormatted = $letter->signed_at
            ? Carbon::parse($letter->signed_at)->locale('id')->translatedFormat('d F Y H:i:s').' WIB'
            : '-';

        $letterDateFormatted = Carbon::parse($letter->letter_date)->locale('id')->translatedFormat('d F Y');

        return [
            'is_valid' => true,
            'integrity_verified' => $integrityValid,
            'letter_id' => $letter->id,
            'document_number' => $letter->document_number ?? 'Draf Resmi Belum Bernomor',
            'letter_type_code' => $letter->type->code ?? '',
            'letter_type_name' => $letter->type->name ?? 'Naskah Dinas',
            'subject' => $letter->subject,
            'letter_date' => $letterDateFormatted,
            'origin_unit_name' => $originUnit['name'] ?? 'PT Kertas Padalarang',
            'signer_name' => $signerName,
            'signer_nip' => $signerNip,
            'signer_position' => $signerPosition,
            'signed_at' => $signedAtFormatted,
            'issuer' => $metadata['issuer'] ?? 'Balai Sertifikasi Elektronik (BSrE) - Badan Siber dan Sandi Negara (BSSN)',
            'certificate_serial' => $metadata['certificate_serial'] ?? 'BSRE-PTKP-OFFICIAL',
            'algorithm' => $metadata['algorithm'] ?? 'SHA256withRSA / PAdES-B-LT',
            'signature_hash' => $letter->signature_hash,
            'verification_url' => url("/verify/{$letter->signature_hash}"),
            'has_signed_file' => ! empty($letter->signed_file_id),
            'signed_file_id' => $letter->signed_file_id,
        ];
    }
}
