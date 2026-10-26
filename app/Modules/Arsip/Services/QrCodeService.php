<?php

declare(strict_types=1);

namespace App\Modules\Arsip\Services;

/**
 * Pure PHP ISO/IEC 18004 compliant QR Code Generator.
 * Produces crisp, standalone SVG strings or Data URIs without external dependencies.
 */
class QrCodeService
{
    /**
     * Galois Field GF(256) log and exp tables.
     *
     * @var array<int, int>
     */
    protected static array $exp = [];

    /**
     * @var array<int, int>
     */
    protected static array $log = [];

    protected static bool $gfInitialized = false;

    /**
     * Generate SVG string for given text.
     */
    public function generateSvg(string $text, int $size = 140, string $color = '#141A5C', int $quietZone = 2): string
    {
        $matrix = $this->encodeToMatrix($text);
        $moduleCount = count($matrix);
        $totalModules = $moduleCount + ($quietZone * 2);

        $pathD = '';
        for ($r = 0; $r < $moduleCount; $r++) {
            for ($c = 0; $c < $moduleCount; $c++) {
                if ($matrix[$r][$c]) {
                    $x = $c + $quietZone;
                    $y = $r + $quietZone;
                    $pathD .= "M{$x},{$y}h1v1h-1z ";
                }
            }
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$totalModules.' '.$totalModules.'" width="'.$size.'" height="'.$size.'" shape-rendering="crispEdges">'
            .'<rect width="100%" height="100%" fill="#ffffff"/>'
            .'<path d="'.trim($pathD).'" fill="'.$color.'"/>'
            .'</svg>';
    }

    /**
     * Generate base64 Data URI for inline embedding in HTML/PDF.
     */
    public function generateDataUri(string $text, int $size = 140, string $color = '#141A5C', int $quietZone = 2): string
    {
        $svg = $this->generateSvg($text, $size, $color, $quietZone);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    /**
     * Encode text into boolean 2D matrix (true = dark module).
     *
     * @return array<int, array<int, bool>>
     */
    public function encodeToMatrix(string $text): array
    {
        self::initGf();

        $dataBytes = array_values(unpack('C*', $text) ?: []);
        $dataLen = count($dataBytes);

        // Select lowest version (ECC Level M) that fits data
        // Capacity table for Byte Mode, ECC Level M:
        // v1: 14 bytes, v2: 26, v3: 42, v4: 62, v5: 84, v6: 106, v7: 122, v8: 152, v9: 180, v10: 213
        $versionCapacities = [
            1 => 14, 2 => 26, 3 => 42, 4 => 62, 5 => 84,
            6 => 106, 7 => 122, 8 => 152, 9 => 180, 10 => 213,
        ];

        $version = 1;
        foreach ($versionCapacities as $v => $cap) {
            if ($dataLen <= $cap) {
                $version = $v;
                break;
            }
            $version = $v;
        }

        $eccInfo = self::getEccInfo($version);
        $totalDataCodewords = $eccInfo['total_data_codewords'];
        $ecCodewordsPerBlock = $eccInfo['ec_codewords_per_block'];
        $blocks = $eccInfo['blocks'];

        // 1. Build BitStream (Byte mode 0100 + count indicator + bytes + terminator + padding)
        $bits = [];
        // Mode indicator 0100 (4 bits)
        self::appendBits($bits, 4, 4);
        // Character count indicator (8 bits for v1-9, 16 bits for v10+)
        $countBits = ($version < 10) ? 8 : 16;
        self::appendBits($bits, $dataLen, $countBits);
        // Data bytes
        foreach ($dataBytes as $b) {
            self::appendBits($bits, $b, 8);
        }
        // Terminator 0000 (up to 4 bits)
        $maxDataBits = $totalDataCodewords * 8;
        $terminatorLen = min(4, $maxDataBits - count($bits));
        if ($terminatorLen > 0) {
            self::appendBits($bits, 0, $terminatorLen);
        }
        // Pad to byte boundary
        while (count($bits) % 8 !== 0) {
            $bits[] = 0;
        }
        // Pad bytes (0xEC, 0x11)
        $padBytes = [0xEC, 0x11];
        $padIdx = 0;
        while (count($bits) < $maxDataBits) {
            self::appendBits($bits, $padBytes[$padIdx % 2], 8);
            $padIdx++;
        }

        // Convert bits to data codewords
        $dataCodewords = [];
        for ($i = 0; $i < count($bits); $i += 8) {
            $val = 0;
            for ($b = 0; $b < 8; $b++) {
                $val = ($val << 1) | $bits[$i + $b];
            }
            $dataCodewords[] = $val;
        }

        // 2. Partition into blocks and calculate ECC codewords
        $dataBlocks = [];
        $ecBlocks = [];
        $cwOffset = 0;

        $genPoly = self::getGeneratorPoly($ecCodewordsPerBlock);

        foreach ($blocks as $blockSpec) {
            $numBlocks = $blockSpec['count'];
            $dataCount = $blockSpec['data_codewords'];
            for ($b = 0; $b < $numBlocks; $b++) {
                $bData = array_slice($dataCodewords, $cwOffset, $dataCount);
                $cwOffset += $dataCount;
                $bEc = self::calculateRsEcc($bData, $genPoly, $ecCodewordsPerBlock);
                $dataBlocks[] = $bData;
                $ecBlocks[] = $bEc;
            }
        }

        // 3. Interleave data codewords, then EC codewords
        $finalCodewords = [];
        $maxDataLen = 0;
        foreach ($dataBlocks as $db) {
            $maxDataLen = max($maxDataLen, count($db));
        }
        for ($i = 0; $i < $maxDataLen; $i++) {
            foreach ($dataBlocks as $db) {
                if ($i < count($db)) {
                    $finalCodewords[] = $db[$i];
                }
            }
        }
        for ($i = 0; $i < $ecCodewordsPerBlock; $i++) {
            foreach ($ecBlocks as $eb) {
                if ($i < count($eb)) {
                    $finalCodewords[] = $eb[$i];
                }
            }
        }

        // Remainder bits (for v2-6, mostly 0 or 7 bits)
        $remainderBits = self::getRemainderBits($version);
        $finalBits = [];
        foreach ($finalCodewords as $cw) {
            self::appendBits($finalBits, $cw, 8);
        }
        for ($i = 0; $i < $remainderBits; $i++) {
            $finalBits[] = 0;
        }

        // 4. Construct Matrix
        $size = 17 + (4 * $version);
        $matrix = array_fill(0, $size, array_fill(0, $size, null));
        $reserved = array_fill(0, $size, array_fill(0, $size, false));

        // Function patterns: Finder patterns
        self::placeFinder($matrix, $reserved, 0, 0);
        self::placeFinder($matrix, $reserved, $size - 7, 0);
        self::placeFinder($matrix, $reserved, 0, $size - 7);

        // Separators
        self::placeSeparators($matrix, $reserved, $size);

        // Alignment patterns
        $alignPos = self::getAlignmentPositions($version);
        if (! empty($alignPos)) {
            $alignCoords = [];
            foreach ($alignPos as $r) {
                foreach ($alignPos as $c) {
                    // Do not overlap with finder patterns
                    if (($r < 9 && $c < 9) || ($r < 9 && $c > $size - 9) || ($r > $size - 9 && $c < 9)) {
                        continue;
                    }
                    $alignCoords[] = [$r, $c];
                }
            }
            foreach ($alignCoords as [$ar, $ac]) {
                self::placeAlignment($matrix, $reserved, $ar, $ac);
            }
        }

        // Timing patterns
        for ($i = 8; $i < $size - 8; $i++) {
            if ($matrix[6][$i] === null) {
                $matrix[6][$i] = ($i % 2 === 0);
                $reserved[6][$i] = true;
            }
            if ($matrix[$i][6] === null) {
                $matrix[$i][6] = ($i % 2 === 0);
                $reserved[$i][6] = true;
            }
        }

        // Dark module
        $matrix[(4 * $version) + 9][8] = true;
        $reserved[(4 * $version) + 9][8] = true;

        // Reserve format information areas
        self::reserveFormatAreas($reserved, $size);

        // 5. Place Data Bits
        $bitIdx = 0;
        $numBits = count($finalBits);
        $dir = -1; // -1 = up, 1 = down
        $col = $size - 1;

        while ($col > 0) {
            if ($col === 6) {
                $col--; // Skip vertical timing pattern
            }
            $rowStart = ($dir === -1) ? ($size - 1) : 0;
            $rowEnd = ($dir === -1) ? -1 : $size;

            for ($row = $rowStart; $row !== $rowEnd; $row += $dir) {
                for ($c = 0; $c < 2; $c++) {
                    $currCol = $col - $c;
                    if (! $reserved[$row][$currCol]) {
                        $val = ($bitIdx < $numBits) ? ($finalBits[$bitIdx] === 1) : false;
                        $matrix[$row][$currCol] = $val;
                        $bitIdx++;
                    }
                }
            }

            $dir = -$dir;
            $col -= 2;
        }

        // 6. Find optimal mask pattern (0..7) and apply
        $bestMask = 0;
        $bestPenalty = PHP_INT_MAX;
        $bestMatrix = [];

        for ($mask = 0; $mask < 8; $mask++) {
            $maskedMatrix = self::applyMask($matrix, $reserved, $mask, $size);
            self::applyFormatInfo($maskedMatrix, $mask, $size);
            $penalty = self::calculatePenalty($maskedMatrix, $size);
            if ($penalty < $bestPenalty) {
                $bestPenalty = $penalty;
                $bestMask = $mask;
                $bestMatrix = $maskedMatrix;
            }
        }

        return $bestMatrix;
    }

    protected static function initGf(): void
    {
        if (self::$gfInitialized) {
            return;
        }

        self::$exp = array_fill(0, 512, 0);
        self::$log = array_fill(0, 256, 0);

        $x = 1;
        for ($i = 0; $i < 255; $i++) {
            self::$exp[$i] = $x;
            self::$log[$x] = $i;
            $x <<= 1;
            if ($x & 0x100) {
                $x ^= 0x11D; // Primitive polynomial x^8 + x^4 + x^3 + x^2 + 1
            }
        }
        for ($i = 255; $i < 512; $i++) {
            self::$exp[$i] = self::$exp[$i - 255];
        }

        self::$gfInitialized = true;
    }

    /**
     * @param  list<int>  $bits
     */
    protected static function appendBits(array &$bits, int $value, int $length): void
    {
        for ($i = $length - 1; $i >= 0; $i--) {
            $bits[] = ($value >> $i) & 1;
        }
    }

    /**
     * @return array{total_data_codewords: int, ec_codewords_per_block: int, blocks: list<array{count: int, data_codewords: int}>}
     */
    protected static function getEccInfo(int $version): array
    {
        // Specifications for ECC Level M
        $table = [
            1 => ['total' => 16, 'ec' => 10, 'blocks' => [['count' => 1, 'data' => 16]]],
            2 => ['total' => 28, 'ec' => 16, 'blocks' => [['count' => 1, 'data' => 28]]],
            3 => ['total' => 44, 'ec' => 26, 'blocks' => [['count' => 1, 'data' => 44]]],
            4 => ['total' => 64, 'ec' => 18, 'blocks' => [['count' => 2, 'data' => 32]]],
            5 => ['total' => 86, 'ec' => 24, 'blocks' => [['count' => 2, 'data' => 43]]],
            6 => ['total' => 108, 'ec' => 16, 'blocks' => [['count' => 4, 'data' => 27]]],
            7 => ['total' => 124, 'ec' => 18, 'blocks' => [['count' => 4, 'data' => 31]]],
            8 => ['total' => 154, 'ec' => 22, 'blocks' => [['count' => 2, 'data' => 38], ['count' => 2, 'data' => 39]]],
            9 => ['total' => 182, 'ec' => 22, 'blocks' => [['count' => 3, 'data' => 36], ['count' => 2, 'data' => 37]]],
            10 => ['total' => 216, 'ec' => 26, 'blocks' => [['count' => 4, 'data' => 43], ['count' => 1, 'data' => 44]]],
        ];

        $info = $table[$version] ?? $table[10];
        $blocks = [];
        foreach ($info['blocks'] as $b) {
            $blocks[] = ['count' => $b['count'], 'data_codewords' => $b['data']];
        }

        return [
            'total_data_codewords' => $info['total'],
            'ec_codewords_per_block' => $info['ec'],
            'blocks' => $blocks,
        ];
    }

    /**
     * @return list<int>
     */
    protected static function getGeneratorPoly(int $ecLen): array
    {
        $poly = [1];
        for ($i = 0; $i < $ecLen; $i++) {
            $factor = [1, self::$exp[$i]];
            $newPoly = array_fill(0, count($poly) + 1, 0);
            for ($p = 0; $p < count($poly); $p++) {
                for ($f = 0; $f < 2; $f++) {
                    $term = ($f === 0) ? $poly[$p] : self::gfMul($poly[$p], $factor[1]);
                    $newPoly[$p + $f] ^= $term;
                }
            }
            $poly = $newPoly;
        }

        return $poly;
    }

    protected static function gfMul(int $x, int $y): int
    {
        if ($x === 0 || $y === 0) {
            return 0;
        }

        return self::$exp[self::$log[$x] + self::$log[$y]];
    }

    /**
     * @param  list<int>  $data
     * @param  list<int>  $genPoly
     * @return list<int>
     */
    protected static function calculateRsEcc(array $data, array $genPoly, int $ecLen): array
    {
        $ecc = array_fill(0, $ecLen, 0);
        foreach ($data as $byte) {
            $factor = $byte ^ $ecc[0];
            array_shift($ecc);
            $ecc[] = 0;
            if ($factor !== 0) {
                for ($i = 0; $i < $ecLen; $i++) {
                    $ecc[$i] ^= self::gfMul($genPoly[$i + 1], $factor);
                }
            }
        }

        return $ecc;
    }

    protected static function getRemainderBits(int $version): int
    {
        return match ($version) {
            2, 3, 4, 5, 6 => 7,
            default => 0,
        };
    }

    /**
     * @return list<int>
     */
    protected static function getAlignmentPositions(int $version): array
    {
        return match ($version) {
            2 => [6, 18],
            3 => [6, 22],
            4 => [6, 26],
            5 => [6, 30],
            6 => [6, 34],
            7 => [6, 22, 38],
            8 => [6, 24, 42],
            9 => [6, 26, 46],
            10 => [6, 28, 50],
            default => [],
        };
    }

    /**
     * @param  array<int, array<int, bool|null>>  $matrix
     * @param  array<int, array<int, bool>>  $reserved
     */
    protected static function placeFinder(array &$matrix, array &$reserved, int $r, int $c): void
    {
        for ($y = 0; $y < 7; $y++) {
            for ($x = 0; $x < 7; $x++) {
                $isDark = ($y === 0 || $y === 6 || $x === 0 || $x === 6 || ($y >= 2 && $y <= 4 && $x >= 2 && $x <= 4));
                $matrix[$r + $y][$c + $x] = $isDark;
                $reserved[$r + $y][$c + $x] = true;
            }
        }
    }

    /**
     * @param  array<int, array<int, bool|null>>  $matrix
     * @param  array<int, array<int, bool>>  $reserved
     */
    protected static function placeSeparators(array &$matrix, array &$reserved, int $size): void
    {
        // Top-left
        for ($i = 0; $i < 8; $i++) {
            if ($i < $size) {
                $matrix[7][$i] = false;
                $reserved[7][$i] = true;
                $matrix[$i][7] = false;
                $reserved[$i][7] = true;
            }
        }
        // Top-right
        for ($i = 0; $i < 8; $i++) {
            $col = $size - 8 + $i;
            if ($col >= 0 && $col < $size) {
                $matrix[7][$col] = false;
                $reserved[7][$col] = true;
                $matrix[$i][$size - 8] = false;
                $reserved[$i][$size - 8] = true;
            }
        }
        // Bottom-left
        for ($i = 0; $i < 8; $i++) {
            $row = $size - 8 + $i;
            if ($row >= 0 && $row < $size) {
                $matrix[$row][7] = false;
                $reserved[$row][7] = true;
                $matrix[$size - 8][$i] = false;
                $reserved[$size - 8][$i] = true;
            }
        }
    }

    /**
     * @param  array<int, array<int, bool|null>>  $matrix
     * @param  array<int, array<int, bool>>  $reserved
     */
    protected static function placeAlignment(array &$matrix, array &$reserved, int $r, int $c): void
    {
        for ($y = -2; $y <= 2; $y++) {
            for ($x = -2; $x <= 2; $x++) {
                $isDark = ($y === -2 || $y === 2 || $x === -2 || $x === 2 || ($y === 0 && $x === 0));
                $matrix[$r + $y][$c + $x] = $isDark;
                $reserved[$r + $y][$c + $x] = true;
            }
        }
    }

    /**
     * @param  array<int, array<int, bool>>  $reserved
     */
    protected static function reserveFormatAreas(array &$reserved, int $size): void
    {
        for ($i = 0; $i < 9; $i++) {
            $reserved[8][$i] = true;
            $reserved[$i][8] = true;
        }
        for ($i = 0; $i < 8; $i++) {
            $reserved[8][$size - 1 - $i] = true;
            $reserved[$size - 1 - $i][8] = true;
        }
    }

    /**
     * @param  array<int, array<int, bool|null>>  $matrix
     * @param  array<int, array<int, bool>>  $reserved
     * @return array<int, array<int, bool>>
     */
    protected static function applyMask(array $matrix, array $reserved, int $mask, int $size): array
    {
        $out = array_fill(0, $size, array_fill(0, $size, false));
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                $val = (bool) $matrix[$r][$c];
                if (! $reserved[$r][$c]) {
                    $invert = match ($mask) {
                        0 => ($r + $c) % 2 === 0,
                        1 => $r % 2 === 0,
                        2 => $c % 3 === 0,
                        3 => ($r + $c) % 3 === 0,
                        4 => ((int) floor($r / 2) + (int) floor($c / 3)) % 2 === 0,
                        5 => (($r * $c) % 2) + (($r * $c) % 3) === 0,
                        6 => ((($r * $c) % 2) + (($r * $c) % 3)) % 2 === 0,
                        7 => ((($r + $c) % 2) + (($r * $c) % 3)) % 2 === 0,
                        default => false,
                    };
                    if ($invert) {
                        $val = ! $val;
                    }
                }
                $out[$r][$c] = $val;
            }
        }

        return $out;
    }

    /**
     * @param  array<int, array<int, bool>>  $matrix
     */
    protected static function applyFormatInfo(array &$matrix, int $mask, int $size): void
    {
        // ECC Level M = 00 binary, masked with mask (3 bits) => 5 bits
        $formatData = (0 << 3) | $mask;
        $rem = $formatData << 10;
        $gen = 0x537;
        for ($i = 4; $i >= 0; $i--) {
            if ($rem & (1 << ($i + 10))) {
                $rem ^= ($gen << $i);
            }
        }
        $formatBits = (($formatData << 10) | $rem) ^ 0x5412;

        // Place on matrix (15 bits)
        $bits = [];
        for ($i = 14; $i >= 0; $i--) {
            $bits[] = (($formatBits >> $i) & 1) === 1;
        }

        // Top-left
        $coords1 = [
            [8, 0], [8, 1], [8, 2], [8, 3], [8, 4], [8, 5],
            [8, 7], [8, 8], [7, 8], [5, 8], [4, 8], [3, 8],
            [2, 8], [1, 8], [0, 8],
        ];
        foreach ($coords1 as $idx => [$r, $c]) {
            $matrix[$r][$c] = $bits[$idx];
        }

        // Split around top-right & bottom-left
        $coords2 = [
            [$size - 1, 8], [$size - 2, 8], [$size - 3, 8], [$size - 4, 8],
            [$size - 5, 8], [$size - 6, 8], [$size - 7, 8],
            [8, $size - 8], [8, $size - 7], [8, $size - 6], [8, $size - 5],
            [8, $size - 4], [8, $size - 3], [8, $size - 2], [8, $size - 1],
        ];
        foreach ($coords2 as $idx => [$r, $c]) {
            $matrix[$r][$c] = $bits[$idx];
        }
    }

    /**
     * @param  array<int, array<int, bool>>  $matrix
     */
    protected static function calculatePenalty(array $matrix, int $size): int
    {
        $penalty = 0;

        // Condition 1: 5+ consecutive same color in row/col
        for ($r = 0; $r < $size; $r++) {
            $count = 1;
            for ($c = 1; $c < $size; $c++) {
                if ($matrix[$r][$c] === $matrix[$r][$c - 1]) {
                    $count++;
                    if ($count === 5) {
                        $penalty += 3;
                    } elseif ($count > 5) {
                        $penalty++;
                    }
                } else {
                    $count = 1;
                }
            }
        }
        for ($c = 0; $c < $size; $c++) {
            $count = 1;
            for ($r = 1; $r < $size; $r++) {
                if ($matrix[$r][$c] === $matrix[$r - 1][$c]) {
                    $count++;
                    if ($count === 5) {
                        $penalty += 3;
                    } elseif ($count > 5) {
                        $penalty++;
                    }
                } else {
                    $count = 1;
                }
            }
        }

        // Condition 2: 2x2 same color blocks
        for ($r = 0; $r < $size - 1; $r++) {
            for ($c = 0; $c < $size - 1; $c++) {
                $val = $matrix[$r][$c];
                if ($matrix[$r + 1][$c] === $val && $matrix[$r][$c + 1] === $val && $matrix[$r + 1][$c + 1] === $val) {
                    $penalty += 3;
                }
            }
        }

        return $penalty;
    }
}
