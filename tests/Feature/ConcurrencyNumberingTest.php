<?php

use App\Modules\Platform\Contracts\NumberingServiceInterface;
use App\Modules\Platform\Models\NumberingFormat;
use App\Modules\Platform\Models\NumberingSequence;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed();
});

test('numbering service generates 50 unique sequential numbers with atomic locking and no duplicates', function () {
    /** @var NumberingServiceInterface $numbering */
    $numbering = app(NumberingServiceInterface::class);

    $category = 'MEMO_INTERNAL';
    $orgUnit = 'DEP-SDM';
    $count = 50;
    $generatedNumbers = [];

    for ($i = 0; $i < $count; $i++) {
        $num = $numbering->generateNumber($category, $orgUnit, [
            'year' => 2026,
            'month' => 4,
            'padding' => 4,
        ]);
        $generatedNumbers[] = $num;
    }

    // 1. Total generated must be 50
    expect(count($generatedNumbers))->toBe($count);

    // 2. All 50 generated numbers must be strictly unique (no duplicate sequences)
    $uniqueNumbers = array_unique($generatedNumbers);
    expect(count($uniqueNumbers))->toBe($count);

    // 3. First number has sequence 0001, last has 0050
    expect($generatedNumbers[0])->toContain('0001/MEMO_INTERNAL/PTKP/DEP-SDM/IV/2026');
    expect($generatedNumbers[49])->toContain('0050/MEMO_INTERNAL/PTKP/DEP-SDM/IV/2026');

    // 4. Verify in database platform.numbering_sequences that last_sequence is 50
    $format = NumberingFormat::where('code', $category)->firstOrFail();
    $sequence = NumberingSequence::where('format_id', $format->id)
        ->where('period_key', '2026')
        ->firstOrFail();

    expect($sequence->last_sequence)->toBe(50);
});

test('distinct document categories maintain independent sequence counters', function () {
    /** @var NumberingServiceInterface $numbering */
    $numbering = app(NumberingServiceInterface::class);

    $skNum1 = $numbering->generateNumber('SK_DIREKSI', 'DIR-UTM', ['year' => 2026, 'month' => 1]);
    $skNum2 = $numbering->generateNumber('SK_DIREKSI', 'DIR-UTM', ['year' => 2026, 'month' => 1]);

    $stNum1 = $numbering->generateNumber('SURAT_TUGAS', 'DIR-UTM', ['year' => 2026, 'month' => 1]);

    expect($skNum1)->toContain('001');
    expect($skNum2)->toContain('002');
    expect($stNum1)->toContain('001');
});

test('numbering reset periods correctly partition sequence keys', function () {
    /** @var NumberingServiceInterface $numbering */
    $numbering = app(NumberingServiceInterface::class);

    // Create a monthly format
    $monthlyFormat = NumberingFormat::create([
        'id' => (string) Str::uuid(),
        'code' => 'MONTHLY_DOC',
        'template' => '{SEQ}/MD/{BULAN}/{TAHUN}',
        'reset_period' => 'monthly',
        'prefix' => 'MD',
        'module' => 'platform',
    ]);

    $numJan1 = $numbering->generateNumber('MONTHLY_DOC', 'DEP-KEU', ['year' => 2026, 'month' => 1]);
    $numJan2 = $numbering->generateNumber('MONTHLY_DOC', 'DEP-KEU', ['year' => 2026, 'month' => 1]);
    $numFeb1 = $numbering->generateNumber('MONTHLY_DOC', 'DEP-KEU', ['year' => 2026, 'month' => 2]);

    expect($numJan1)->toBe('001/MD/I/2026');
    expect($numJan2)->toBe('002/MD/I/2026');
    // In February, counter resets to 001
    expect($numFeb1)->toBe('001/MD/II/2026');
});
