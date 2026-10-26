<?php

declare(strict_types=1);

namespace App\Modules\Arsip\Services;

use App\Modules\Arsip\Models\Letter;
use App\Modules\Platform\Contracts\DirectoryServiceInterface;
use App\Modules\Platform\Contracts\FilesServiceInterface;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;

class LetterPdfService
{
    public function __construct(
        protected DirectoryServiceInterface $directoryService,
        protected FilesServiceInterface $filesService,
        protected QrCodeService $qrCodeService
    ) {}

    /**
     * Map letter type code to Blade template.
     */
    public function getTemplateName(string $typeCode): string
    {
        return match (strtoupper($typeCode)) {
            'A' => 'arsip.templates.surat-keluar',
            'ND' => 'arsip.templates.nota-dinas',
            'ST' => 'arsip.templates.surat-tugas',
            'E' => 'arsip.templates.surat-memo',
            'KPTS' => 'arsip.templates.surat-keputusan',
            'PENG' => 'arsip.templates.surat-pengumuman',
            'SPB' => 'arsip.templates.spb',
            'BP' => 'arsip.templates.bp',
            'SPPD' => 'arsip.templates.sppd',
            default => 'arsip.templates.surat-keluar',
        };
    }

    /**
     * Render HTML string for a given letter.
     */
    public function renderHtml(Letter $letter): string
    {
        $letter->loadMissing(['type', 'destinations', 'approvals']);

        $typeCode = $letter->type->code ?? 'A';
        $template = $this->getTemplateName($typeCode);

        if (! View::exists($template)) {
            throw new DomainException("Template Blade untuk jenis surat [{$typeCode}] ({$template}) belum tersedia.");
        }

        $originUnit = ! empty($letter->origin_unit_id) ? $this->directoryService->getOrgUnit($letter->origin_unit_id) : null;
        $creator = ! empty($letter->creator_employee_id) ? $this->directoryService->getEmployeeById($letter->creator_employee_id) : null;

        $logoPath = public_path('assets/logo.png');
        $logoDataUri = null;
        if (file_exists($logoPath)) {
            $logoContent = file_get_contents($logoPath);
            if ($logoContent !== false) {
                $logoDataUri = 'data:image/png;base64,'.base64_encode($logoContent);
            }
        }

        $content = is_array($letter->content_body) ? $letter->content_body : [];

        // Check if document has been digitally signed
        $isDigitallySigned = ! empty($letter->signature_hash);
        $qrCodeSvg = null;
        $verificationUrl = null;
        $signatureSigner = null;

        if ($isDigitallySigned) {
            $verificationUrl = url('/verify/'.$letter->signature_hash);
            $qrCodeSvg = $this->qrCodeService->generateSvg($verificationUrl, 84, '#141A5C', 1);
            if ($letter->signer_employee_id) {
                $signatureSigner = $this->directoryService->getEmployeeById($letter->signer_employee_id);
            }
        }

        return View::make($template, [
            'letter' => $letter,
            'content' => $content,
            'originUnit' => $originUnit,
            'creator' => $creator,
            'logoDataUri' => $logoDataUri,
            'isDigitallySigned' => $isDigitallySigned,
            'qrCodeSvg' => $qrCodeSvg,
            'verificationUrl' => $verificationUrl,
            'signatureSigner' => $signatureSigner,
            'signatureMeta' => $letter->signature_metadata ?? [],
        ])->render();
    }

    /**
     * Generate PDF binary string using Browsershot.
     */
    public function generatePdfBinary(Letter $letter): string
    {
        $html = $this->renderHtml($letter);

        $browsershot = new Browsershot;
        $browsershot->setHtml($html);

        $chromePath = config('browsershot.chrome_path');
        if ($chromePath && file_exists($chromePath)) {
            $browsershot->setChromePath($chromePath);
        }

        $nodePath = config('browsershot.node_path');
        if ($nodePath) {
            $browsershot->setNodeBinary($nodePath);
        }

        $npmPath = config('browsershot.npm_path');
        if ($npmPath) {
            $browsershot->setNpmBinary($npmPath);
        }

        $nodeModulesPath = config('browsershot.node_modules_path');
        if ($nodeModulesPath && file_exists($nodeModulesPath)) {
            $browsershot->setNodeModulePath($nodeModulesPath);
        }

        $browsershot
            ->format('A4')
            ->margins(20, 20, 20, 20)
            ->showBackground()
            ->waitUntilNetworkIdle();

        return $browsershot->pdf();
    }

    /**
     * Generate PDF and store it securely using FilesService.
     *
     * @return array<string, mixed> File metadata
     */
    public function generateAndStoreDraftPdf(Letter $letter, ?string $uploadedByEmployeeId = null): array
    {
        $pdfBytes = $this->generatePdfBinary($letter);

        $tempDir = sys_get_temp_dir();
        $tempFileName = 'draft_'.Str::slug($letter->type->code ?? 'surat').'_'.Str::random(10).'.pdf';
        $tempPath = $tempDir.DIRECTORY_SEPARATOR.$tempFileName;

        file_put_contents($tempPath, $pdfBytes);

        try {
            $originalName = 'Draf_'.($letter->type->code ?? 'Surat').'_'.($letter->document_number ? Str::slug($letter->document_number) : date('Ymd_His')).'.pdf';
            $uploadedFile = new UploadedFile(
                path: $tempPath,
                originalName: $originalName,
                mimeType: 'application/pdf',
                error: null,
                test: true
            );

            return $this->filesService->store(
                file: $uploadedFile,
                module: 'arsip',
                category: 'draft_letter',
                uploadedByEmployeeId: $uploadedByEmployeeId ?? $letter->creator_employee_id
            );
        } finally {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    /**
     * Generate signed PDF with embedded QR Code and store it securely.
     *
     * @return array<string, mixed> File metadata
     */
    public function generateAndStoreSignedPdf(Letter $letter, ?string $signerEmployeeId = null): array
    {
        $pdfBytes = $this->generatePdfBinary($letter);

        $tempDir = sys_get_temp_dir();
        $tempFileName = 'signed_'.Str::slug($letter->type->code ?? 'surat').'_'.Str::random(10).'.pdf';
        $tempPath = $tempDir.DIRECTORY_SEPARATOR.$tempFileName;

        file_put_contents($tempPath, $pdfBytes);

        try {
            $originalName = 'Resmi_'.($letter->type->code ?? 'Surat').'_'.($letter->document_number ? Str::slug($letter->document_number) : date('Ymd_His')).'.pdf';
            $uploadedFile = new UploadedFile(
                path: $tempPath,
                originalName: $originalName,
                mimeType: 'application/pdf',
                error: null,
                test: true
            );

            return $this->filesService->store(
                file: $uploadedFile,
                module: 'arsip',
                category: 'signed_letter',
                uploadedByEmployeeId: $signerEmployeeId ?? $letter->creator_employee_id
            );
        } finally {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }
}
