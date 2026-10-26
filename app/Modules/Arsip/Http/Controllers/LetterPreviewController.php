<?php

declare(strict_types=1);

namespace App\Modules\Arsip\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Arsip\Models\Letter;
use App\Modules\Arsip\Services\LetterPdfService;
use App\Modules\Platform\Contracts\FilesServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LetterPreviewController extends Controller
{
    public function __construct(
        protected LetterPdfService $letterPdfService,
        protected FilesServiceInterface $filesService
    ) {}

    /**
     * Preview letter as PDF stream in browser.
     */
    public function preview(Request $request, Letter $letter): Response|BinaryFileResponse
    {
        Gate::authorize('view', $letter);

        $letter->loadMissing(['type']);

        // 1. If document is officially signed, serve the permanent signed file
        if ($letter->signed_file_id) {
            $path = $this->filesService->getPath($letter->signed_file_id);
            if ($path && Storage::disk('local')->exists($path)) {
                $fullPath = Storage::disk('local')->path($path);
                $filename = ($letter->document_number ?: 'Dokumen_TTE').'.pdf';

                return response()->file($fullPath, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="'.$filename.'"',
                ]);
            }
        }

        // 2. For Surat Masuk (uploaded external document)
        if (($letter->type->code ?? '') === 'B' && $letter->draft_file_id) {
            $path = $this->filesService->getPath($letter->draft_file_id);
            if ($path && Storage::disk('local')->exists($path)) {
                $fullPath = Storage::disk('local')->path($path);
                $filename = ($letter->document_number ?: 'Surat_Masuk').'.pdf';

                return response()->file($fullPath, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="'.$filename.'"',
                ]);
            }
        }

        // 3. For draft / under-review system generated documents, generate fresh draft PDF
        $meta = $this->letterPdfService->generateAndStoreDraftPdf($letter);
        $letter->update(['draft_file_id' => $meta['id']]);

        $path = $this->filesService->getPath($meta['id']);
        if ($path && Storage::disk('local')->exists($path)) {
            $fullPath = Storage::disk('local')->path($path);
            $filename = ($letter->document_number ?: 'Draft').'.pdf';

            return response()->file($fullPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]);
        }

        // Direct generation fallback
        $pdfBytes = $this->letterPdfService->generatePdfBinary($letter);

        return response($pdfBytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="letter-preview.pdf"',
        ]);
    }

    /**
     * Preview rendered HTML directly for inspection / debugging.
     */
    public function previewHtml(Request $request, Letter $letter): Response
    {
        Gate::authorize('view', $letter);

        $html = $this->letterPdfService->renderHtml($letter);

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }
}
