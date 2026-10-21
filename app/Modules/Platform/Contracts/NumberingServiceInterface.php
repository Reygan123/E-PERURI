<?php

namespace App\Modules\Platform\Contracts;

interface NumberingServiceInterface
{
    /**
     * Generate the next document sequence number based on template and metadata.
     *
     * @param  string  $category  e.g. 'MEMO', 'SK', 'ND', 'SURAT_KELUAR'
     * @param  string  $orgUnitCode  e.g. 'DEP-SDM', 'DIR-UT'
     * @param  array<string, mixed>  $context  Additional tokens (e.g. month, year, classification)
     * @return string Generated document number (e.g. '045/SK/PTKP/IV/2026')
     */
    public function generateNumber(string $category, string $orgUnitCode, array $context = []): string;

    /**
     * Reserve and commit a generated document number with metadata.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function recordNumber(string $documentNumber, string $category, string $orgUnitCode, array $metadata = []): void;
}
