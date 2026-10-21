<?php

namespace App\Modules\Platform\Services;

use App\Modules\Platform\Contracts\NumberingServiceInterface;
use App\Modules\Platform\Models\NumberingFormat;
use App\Modules\Platform\Models\NumberingSequence;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class NumberingService implements NumberingServiceInterface
{
    /**
     * Roman numerals map for months.
     *
     * @var array<int, string>
     */
    protected array $romanMonths = [
        1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
        7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
    ];

    /**
     * Generate the next document sequence number atomically.
     *
     * @param  string  $category  e.g. 'MEMO', 'SK', 'ND', 'SURAT_KELUAR'
     * @param  string  $orgUnitCode  e.g. 'DEP-SDM', 'DIR-UTM'
     * @param  array<string, mixed>  $context
     */
    public function generateNumber(string $category, string $orgUnitCode, array $context = []): string
    {
        return DB::transaction(function () use ($category, $orgUnitCode, $context) {
            $format = NumberingFormat::where('code', $category)->first();

            if (! $format) {
                // If format does not exist, create a default standard format
                $format = NumberingFormat::create([
                    'id' => (string) Str::uuid(),
                    'code' => $category,
                    'template' => '{SEQ}/{PREFIX}/PTKP/{UNIT}/{BULAN}/{TAHUN}',
                    'reset_period' => 'yearly',
                    'prefix' => strtoupper($category),
                    'module' => $context['module'] ?? 'platform',
                ]);
            }

            $currentYear = (int) ($context['year'] ?? now()->year);
            $currentMonth = (int) ($context['month'] ?? now()->month);

            $periodKey = match ($format->reset_period) {
                'monthly' => sprintf('%04d-%02d', $currentYear, $currentMonth),
                'never' => 'ALL',
                default => (string) $currentYear,
            };

            // Lock row for atomic sequence increment
            $sequence = NumberingSequence::where('format_id', $format->id)
                ->where('period_key', $periodKey)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                $sequence = NumberingSequence::create([
                    'id' => (string) Str::uuid(),
                    'format_id' => $format->id,
                    'period_key' => $periodKey,
                    'last_sequence' => 1,
                ]);
                $nextSeq = 1;
            } else {
                $nextSeq = $sequence->last_sequence + 1;
                $sequence->update(['last_sequence' => $nextSeq]);
            }

            $seqPadding = (int) ($context['padding'] ?? 3);
            $paddedSeq = str_pad((string) $nextSeq, $seqPadding, '0', STR_PAD_LEFT);
            $romanMonth = $this->romanMonths[$currentMonth] ?? (string) $currentMonth;

            $replacements = [
                '{SEQ}' => $paddedSeq,
                '{PREFIX}' => $format->prefix ?? strtoupper($category),
                '{UNIT}' => strtoupper($orgUnitCode),
                '{BULAN}' => $romanMonth,
                '{TAHUN}' => (string) $currentYear,
            ];

            return str_replace(array_keys($replacements), array_values($replacements), $format->template);
        });
    }

    /**
     * Reserve and record a generated document number with metadata.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function recordNumber(string $documentNumber, string $category, string $orgUnitCode, array $metadata = []): void
    {
        if (empty($documentNumber)) {
            throw new InvalidArgumentException('Document number cannot be empty.');
        }

        // Additional logging or registry tracking can be appended here
    }
}
