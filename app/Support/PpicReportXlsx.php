<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

/**
 * Pembuat file .xlsx untuk Summary Report - PPIC (Master Schedule), tanpa paket tambahan.
 * Hanya butuh ekstensi PHP "zip" (ZipArchive), sama seperti MaterialXlsx.
 *
 * Isi: banner judul + dua logo perusahaan, lalu tabel master (Preparation / Production / Finishing).
 */
class PpicReportXlsx
{
    // Nomor style = urutan <xf> pada styles.xml (lihat stylesXml())
    private const S_DEFAULT      = 0;
    private const S_BANNER_TITLE = 1;
    private const S_BANNER_SUB   = 2;
    private const S_META         = 3;
    private const S_HEAD         = 4;
    private const S_HEAD_PREP    = 5;
    private const S_HEAD_PROD    = 6;
    private const S_HEAD_QC      = 7;
    private const S_TEXT         = 8;
    private const S_CENTER       = 9;
    private const S_PCT_RED      = 10;
    private const S_PCT_YELLOW   = 11;
    private const S_PCT_GREEN    = 12;
    private const S_TOT_PREP     = 13;
    private const S_TOT_PROD     = 14;
    private const S_TOT_QC       = 15;
    private const S_PCT_PLAIN    = 16;
    private const S_ACTUAL       = 17;
    private const S_GAP_POS      = 18;
    private const S_GAP_NEG      = 19;
    private const S_ST_ON        = 20;
    private const S_ST_LEAD      = 21;
    private const S_ST_RISK      = 22;
    private const S_ST_DELAY     = 23;
    private const S_ST_NONE      = 24;

    // Lebar kolom Status (karakter) -- dipakai menghitung posisi logo kanan
    private const STATUS_COL_WIDTH = 18;
    // Tinggi logo di banner (piksel)
    private const LOGO_PX = 56;

    /** @var array<int, array<int, array{v: mixed, s: int}>> */
    private array $cells = [];

    /** @var string[] */
    private array $merges = [];

    /** @var array<int, int> */
    private array $rowHeights = [];

    /**
     * Buat file .xlsx sementara dan kembalikan path-nya.
     *
     * $data: title, subtitle, prepDivisions, productionTasks, qcTasks, qaqcTasks, rows (array), logos (array path PNG: [kiri, kanan])
     */
    public static function create(array $data): string
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP "zip" belum aktif.');
        }

        return (new self())->generate($data);
    }

    private function generate(array $data): string
    {
        $prep = $data['prepDivisions'];
        $prod = $data['productionTasks'];
        $qc   = $data['qcTasks'];
        $qaqc = $data['qaqcTasks'];

        // Posisi kolom
        $colPrepStart = 11;
        $colPrepTotal = $colPrepStart + count($prep);
        $colProdStart = $colPrepTotal + 1;
        $colProdTotal = $colProdStart + count($prod);
        $colQcStart   = $colProdTotal + 1;
        $colQcTotal   = $colQcStart + count($qc);
        $colQaqcStart = $colQcTotal + 1;
        $colQaqcTotal = $colQaqcStart + count($qaqc);
        $colPlan      = $colQaqcTotal + 1;
        $colActual    = $colPlan + 1;
        $colGap       = $colPlan + 2;
        $colStatus    = $colPlan + 3;
        $lastCol      = $colStatus;

        // ---------------- Banner (baris 1-2) + info cetak (baris 3) ----------------
        $this->put(1, 1, (string) $data['title'], self::S_BANNER_TITLE);
        $this->merge(1, 1, 1, $lastCol, self::S_BANNER_TITLE);
        $this->put(2, 1, (string) $data['subtitle'], self::S_BANNER_SUB);
        $this->merge(2, 1, 2, $lastCol, self::S_BANNER_SUB);
        $this->put(3, 1, 'Dicetak: ' . now()->format('d M Y H:i') . ' WIB   |   Warna persen: 0-50% merah, 51-99% kuning, 100% hijau', self::S_META);
        $this->rowHeights[1] = 36;
        $this->rowHeights[2] = 22;

        // ---------------- Header tabel (baris 5-6) ----------------
        $h1 = 5;
        $hm = 6;   // baris tengah: sub-kelompok Production (Tahap Preparation / Tahap Production)
        $h2 = 7;   // baris terakhir header
        $this->rowHeights[$h1] = 22;
        $this->rowHeights[$hm] = 22;
        $this->rowHeights[$h2] = 34;

        $simple = [
            1 => 'NO', 2 => 'KODE PROYEK', 3 => 'NAMA PROYEK', 4 => 'CUSTOMER', 5 => "CONTRACT\nREVIEW", 6 => 'DUE DATE',
            7 => "TIME CONSUMPTION\n(DAY)", 8 => "TIME CONSUMPTION\n(HOUR)", 9 => 'QTY', 10 => 'FABRICATOR',
            $colPlan => 'PLANNING (%)', $colActual => "TOTAL\nPERCENTAGE", $colGap => 'GAP', $colStatus => 'STATUS',
        ];
        foreach ($simple as $col => $label) {
            $this->put($h1, $col, $label, self::S_HEAD);
            $this->merge($h1, $col, $h2, $col, self::S_HEAD);
        }

        $this->put($h1, $colPrepStart, 'PREPARATION (' . (int) array_sum(array_column($prep, 'weight')) . '%)', self::S_HEAD_PREP);
        $this->merge($h1, $colPrepStart, $h1, $colPrepTotal, self::S_HEAD_PREP);
        $this->put($h1, $colProdStart, 'PRODUCTION (' . (int) array_sum(array_column($prod, 'weight')) . '%)', self::S_HEAD_PROD);
        $this->merge($h1, $colProdStart, $h1, $colProdTotal, self::S_HEAD_PROD);
        $this->put($h1, $colQcStart, 'FINISH PROCESS (' . array_sum(\App\Models\Project::$qcTasks) . '%)', self::S_HEAD_QC);
        $this->merge($h1, $colQcStart, $h1, $colQcTotal, self::S_HEAD_QC);
        $this->put($h1, $colQaqcStart, 'QA/QC (' . \App\Models\Project::QAQC_TOTAL . '%)', self::S_HEAD_QC);
        $this->merge($h1, $colQaqcStart, $h1, $colQaqcTotal, self::S_HEAD_QC);

        $c = $colPrepStart;
        foreach ($prep as $div) {
            $this->put($hm, $c, mb_strtoupper((string) $div['label']), self::S_HEAD_PREP);
            $this->merge($hm, $c, $h2, $c, self::S_HEAD_PREP);
            $c++;
        }
        $this->put($hm, $colPrepTotal, 'TOTAL %', self::S_TOT_PREP);
        $this->merge($hm, $colPrepTotal, $h2, $colPrepTotal, self::S_TOT_PREP);

        // Sub-kelompok Production: Tahap Preparation dulu, lalu Tahap Production
        $prepKeys = array_keys(\App\Models\Project::$productionStages['preparation']['tasks']);
        $prodPrep = array_filter($prod, fn($t, $k) => in_array($k, $prepKeys, true), ARRAY_FILTER_USE_BOTH);
        $prodMain = array_diff_key($prod, $prodPrep);
        $c = $colProdStart;
        foreach ([['PREPARATION', $prodPrep], ['PRODUCTION', $prodMain]] as [$subLabel, $subTasks]) {
            if (count($subTasks) === 0) {
                continue;
            }
            $this->put($hm, $c, $subLabel . ' (' . (float) array_sum(array_column($subTasks, 'weight')) . '%)', self::S_HEAD_PROD);
            $this->merge($hm, $c, $hm, $c + count($subTasks) - 1, self::S_HEAD_PROD);
            foreach ($subTasks as $task) {
                $this->put($h2, $c++, mb_strtoupper((string) $task['label']), self::S_HEAD_PROD);
            }
        }
        $this->put($hm, $colProdTotal, 'TOTAL %', self::S_TOT_PROD);
        $this->merge($hm, $colProdTotal, $h2, $colProdTotal, self::S_TOT_PROD);

        $c = $colQcStart;
        foreach ($qc as $task) {
            $this->put($hm, $c, mb_strtoupper((string) $task['label']), self::S_HEAD_QC);
            $this->merge($hm, $c, $h2, $c, self::S_HEAD_QC);
            $c++;
        }
        $this->put($hm, $colQcTotal, 'TOTAL %', self::S_TOT_QC);
        $this->merge($hm, $colQcTotal, $h2, $colQcTotal, self::S_TOT_QC);

        $c = $colQaqcStart;
        foreach ($qaqc as $task) {
            $this->put($hm, $c, mb_strtoupper((string) $task['label']), self::S_HEAD_QC);
            $this->merge($hm, $c, $h2, $c, self::S_HEAD_QC);
            $c++;
        }
        $this->put($hm, $colQaqcTotal, 'TOTAL %', self::S_TOT_QC);
        $this->merge($hm, $colQaqcTotal, $h2, $colQaqcTotal, self::S_TOT_QC);

        // ---------------- Baris data ----------------
        $r    = $h2 + 1;
        $rows = array_values($data['rows']);

        foreach ($rows as $i => $row) {
            $qty = $row['qty'] ?? '';

            $this->put($r, 1, $i + 1, self::S_CENTER);
            $this->put($r, 2, (string) $row['code'], self::S_TEXT);
            $this->put($r, 3, (string) $row['name'], self::S_TEXT);
            $this->put($r, 4, (string) $row['customer'], self::S_CENTER);
            $this->put($r, 5, (string) $row['contract'], self::S_CENTER);
            $this->put($r, 6, (string) $row['due'], self::S_CENTER);
            $this->put($r, 7, $row['time_days'] ?? '-', self::S_CENTER);
            $this->put($r, 8, $row['time_hours'] !== null ? round((float) $row['time_hours'], 2) : '-', self::S_CENTER);
            $this->put($r, 9, is_numeric($qty) ? (int) $qty : (string) $qty, self::S_CENTER);
            $this->put($r, 10, $row['fabricator'] ?: '-', self::S_CENTER);

            $c = $colPrepStart;
            foreach ($prep as $divKey => $div) {
                $v = (int) $row['prep_percents'][$divKey];
                $this->put($r, $c++, $v, self::pctStyle($v));
            }
            $this->put($r, $colPrepTotal, (int) $row['prep_group_pct'], self::S_TOT_PREP);

            $c = $colProdStart;
            foreach ($prod as $taskKey => $task) {
                if ($row['production_cells'][$taskKey] === null) { $this->put($r, $c++, '-', self::S_PCT_PLAIN); continue; }
                $v = (int) $row['production_cells'][$taskKey];
                $this->put($r, $c++, $v, self::pctStyle($v));
            }
            $this->put($r, $colProdTotal, (int) $row['production_group_pct'], self::S_TOT_PROD);

            $c = $colQcStart;
            foreach ($qc as $taskKey => $task) {
                if ($row['qc_cells'][$taskKey] === null) { $this->put($r, $c++, '-', self::S_PCT_PLAIN); continue; }
                $v = (int) $row['qc_cells'][$taskKey];
                $this->put($r, $c++, $v, self::pctStyle($v));
            }
            $this->put($r, $colQcTotal, (int) $row['qc_group_pct'], self::S_TOT_QC);

            $c = $colQaqcStart;
            foreach ($qaqc as $taskKey => $task) {
                if ($row['qaqc_cells'][$taskKey] === null) { $this->put($r, $c++, '-', self::S_PCT_PLAIN); continue; }
                $v = (int) $row['qaqc_cells'][$taskKey];
                $this->put($r, $c++, $v, self::pctStyle($v));
            }
            $this->put($r, $colQaqcTotal, (int) $row['qaqc_group_pct'], self::S_TOT_QC);

            $gap = (int) $row['gap'];
            $this->put($r, $colPlan, (int) $row['plan'], self::S_PCT_PLAIN);
            $this->put($r, $colActual, (int) $row['actual'], self::S_ACTUAL);
            $this->put($r, $colGap, $gap, $gap >= 0 ? self::S_GAP_POS : self::S_GAP_NEG);
            $this->put($r, $colStatus, (string) $row['status']['label'], self::statusStyle((string) $row['status']['code']));

            $r++;
        }

        if (count($rows) === 0) {
            $this->put($r, 1, 'Tidak ada proyek pada periode yang dipilih.', self::S_CENTER);
            $this->merge($r, 1, $r, $lastCol, self::S_CENTER);
            $r++;
        }

        // ---------------- Lebar kolom ----------------
        $widths = [1 => 5, 2 => 17, 3 => 40, 4 => 11, 5 => 13, 6 => 12, 7 => 19, 8 => 19, 9 => 7, 10 => 14];

        $c = $colPrepStart;
        foreach ($prep as $div) {
            $widths[$c++] = max(10, mb_strlen((string) $div['label']) + 3);
        }
        $c = $colProdStart;
        foreach ($prod as $task) {
            $widths[$c++] = max(10, mb_strlen((string) $task['label']) + 3);
        }
        $c = $colQcStart;
        foreach ($qc as $task) {
            $widths[$c++] = max(10, mb_strlen((string) $task['label']) + 3);
        }

        $c = $colQaqcStart;
        foreach ($qaqc as $task) {
            $widths[$c++] = max(10, mb_strlen((string) $task['label']) + 3);
        }

        $widths[$colQaqcTotal] = 9;
        $widths[$colPrepTotal] = 9;
        $widths[$colProdTotal] = 9;
        $widths[$colQcTotal]   = 9;
        $widths[$colPlan]      = 13;
        $widths[$colActual]    = 14;
        $widths[$colGap]       = 9;
        $widths[$colStatus]    = self::STATUS_COL_WIDTH;

        // ---------------- Logo (opsional; dilewati kalau file tidak ada) ----------------
        $logos = [];
        foreach ((array) ($data['logos'] ?? []) as $logoPath) {
            if (is_string($logoPath) && is_file($logoPath)) {
                $logos[] = $logoPath;
            }
        }
        $hasLogos = count($logos) > 0;

        $sheet = $this->sheetXml($widths, $h2, $hasLogos);

        $path = tempnam(sys_get_temp_dir(), 'ppic');
        $zip  = new ZipArchive();

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Gagal membuat file Excel.');
        }

        $zip->addFromString('[Content_Types].xml', self::contentTypesXml($hasLogos));
        $zip->addFromString('_rels/.rels', self::rootRelsXml());
        $zip->addFromString('xl/workbook.xml', self::workbookXml($h1, $h2));
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRelsXml());
        $zip->addFromString('xl/styles.xml', self::stylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);

        if ($hasLogos) {
            $zip->addFromString('xl/worksheets/_rels/sheet1.xml.rels', self::sheetRelsXml());
            $zip->addFromString('xl/drawings/drawing1.xml', self::drawingXml($logos, $lastCol));
            $zip->addFromString('xl/drawings/_rels/drawing1.xml.rels', self::drawingRelsXml(count($logos)));
            foreach ($logos as $index => $logoPath) {
                $zip->addFromString('xl/media/image' . ($index + 1) . '.png', (string) file_get_contents($logoPath));
            }
        }

        $zip->close();

        return $path;
    }

    private static function pctStyle(int $value): int
    {
        if ($value >= 100) {
            return self::S_PCT_GREEN;
        }

        return $value >= 51 ? self::S_PCT_YELLOW : self::S_PCT_RED;
    }

    private static function statusStyle(string $code): int
    {
        return match ($code) {
            'ON_TRACK' => self::S_ST_ON,
            'LEADING'  => self::S_ST_LEAD,
            'AT_RISK'  => self::S_ST_RISK,
            'CLOSED_LATE' => self::S_ST_RISK,
            'DELAYED'  => self::S_ST_DELAY,
            default    => self::S_ST_NONE,
        };
    }

    // ---------------------------------------------------------------------
    // Pengelola sel
    // ---------------------------------------------------------------------

    private function put(int $row, int $col, $value, int $style = self::S_DEFAULT): void
    {
        $this->cells[$row][$col] = ['v' => $value, 's' => $style];
    }

    // Gabung sel; sel lain di area gabungan diberi style yang sama supaya warna/border tampil
    private function merge(int $r1, int $c1, int $r2, int $c2, int $style): void
    {
        if ($r1 === $r2 && $c1 === $c2) {
            return;
        }

        for ($r = $r1; $r <= $r2; $r++) {
            for ($c = $c1; $c <= $c2; $c++) {
                if (!isset($this->cells[$r][$c])) {
                    $this->cells[$r][$c] = ['v' => '', 's' => $style];
                }
            }
        }

        $this->merges[] = self::ref($r1, $c1) . ':' . self::ref($r2, $c2);
    }

    private static function colName(int $col): string
    {
        $name = '';
        while ($col > 0) {
            $mod  = ($col - 1) % 26;
            $name = chr(65 + $mod) . $name;
            $col  = intdiv($col - 1, 26);
        }

        return $name;
    }

    private static function ref(int $row, int $col): string
    {
        return self::colName($col) . $row;
    }

    private static function escape(string $text): string
    {
        // Buang karakter kontrol yang tidak valid di XML
        $text = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', $text) ?? '';

        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private static function numberText($value): string
    {
        if (is_int($value)) {
            return (string) $value;
        }

        $value = (float) $value;
        if ($value == 0) {
            return '0';
        }

        return rtrim(rtrim(sprintf('%.4F', $value), '0'), '.');
    }

    // ---------------------------------------------------------------------
    // XML
    // ---------------------------------------------------------------------

    private function sheetXml(array $widths, int $headerLastRow, bool $hasLogos): string
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
        $xml .= '<sheetPr><pageSetUpPr fitToPage="1"/></sheetPr>';

        // Bekukan header tabel + 3 kolom pertama (No, Kode, Nama) supaya enak digeser
        $topLeft = 'D' . ($headerLastRow + 1);
        $xml .= '<sheetViews><sheetView showGridLines="0" workbookViewId="0">';
        $xml .= '<pane xSplit="3" ySplit="' . $headerLastRow . '" topLeftCell="' . $topLeft . '" activePane="bottomRight" state="frozen"/>';
        $xml .= '<selection pane="topRight" activeCell="D1" sqref="D1"/>';
        $xml .= '<selection pane="bottomLeft" activeCell="A' . ($headerLastRow + 1) . '" sqref="A' . ($headerLastRow + 1) . '"/>';
        $xml .= '<selection pane="bottomRight" activeCell="' . $topLeft . '" sqref="' . $topLeft . '"/>';
        $xml .= '</sheetView></sheetViews>';
        $xml .= '<sheetFormatPr defaultRowHeight="15"/>';

        $xml .= '<cols>';
        ksort($widths);
        foreach ($widths as $col => $width) {
            $xml .= '<col min="' . $col . '" max="' . $col . '" width="' . $width . '" customWidth="1"/>';
        }
        $xml .= '</cols>';

        $xml .= '<sheetData>';
        $rows = $this->cells;
        ksort($rows);
        foreach ($rows as $rowNumber => $cols) {
            ksort($cols);
            $height = isset($this->rowHeights[$rowNumber])
                ? ' ht="' . $this->rowHeights[$rowNumber] . '" customHeight="1"'
                : '';
            $xml .= '<row r="' . $rowNumber . '"' . $height . '>';
            foreach ($cols as $colNumber => $cell) {
                $xml .= $this->cellXml($rowNumber, $colNumber, $cell);
            }
            $xml .= '</row>';
        }
        $xml .= '</sheetData>';

        if (count($this->merges) > 0) {
            $xml .= '<mergeCells count="' . count($this->merges) . '">';
            foreach ($this->merges as $range) {
                $xml .= '<mergeCell ref="' . $range . '"/>';
            }
            $xml .= '</mergeCells>';
        }

        $xml .= '<pageMargins left="0.3" right="0.3" top="0.4" bottom="0.4" header="0.2" footer="0.2"/>';
        $xml .= '<pageSetup paperSize="8" orientation="landscape" fitToWidth="1" fitToHeight="0"/>';

        if ($hasLogos) {
            $xml .= '<drawing r:id="rId1"/>';
        }

        $xml .= '</worksheet>';

        return $xml;
    }

    private function cellXml(int $row, int $col, array $cell): string
    {
        $ref   = self::ref($row, $col);
        $style = (int) $cell['s'];
        $value = $cell['v'];

        if ($value === '' || $value === null) {
            return '<c r="' . $ref . '" s="' . $style . '"/>';
        }

        if (is_int($value) || is_float($value)) {
            return '<c r="' . $ref . '" s="' . $style . '"><v>' . self::numberText($value) . '</v></c>';
        }

        return '<c r="' . $ref . '" s="' . $style . '" t="inlineStr"><is><t xml:space="preserve">'
            . self::escape((string) $value) . '</t></is></c>';
    }

    private static function contentTypesXml(bool $hasLogos): string
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">';
        $xml .= '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>';
        $xml .= '<Default Extension="xml" ContentType="application/xml"/>';
        if ($hasLogos) {
            $xml .= '<Default Extension="png" ContentType="image/png"/>';
        }
        $xml .= '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
        $xml .= '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        $xml .= '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        if ($hasLogos) {
            $xml .= '<Override PartName="/xl/drawings/drawing1.xml" ContentType="application/vnd.openxmlformats-officedocument.drawing+xml"/>';
        }
        $xml .= '</Types>';

        return $xml;
    }

    private static function rootRelsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML;
    }

    private static function workbookXml(int $firstTitleRow, int $lastTitleRow): string
    {
        // Baris header tabel diulang di setiap halaman saat dicetak
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Master Schedule" sheetId="1" r:id="rId1"/></sheets>'
            . '<definedNames><definedName name="_xlnm.Print_Titles" localSheetId="0">\'Master Schedule\'!$' . $firstTitleRow . ':$' . $lastTitleRow . '</definedName></definedNames>'
            . '</workbook>';
    }

    private static function workbookRelsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
XML;
    }

    private static function sheetRelsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing" Target="../drawings/drawing1.xml"/>
</Relationships>
XML;
    }

    private static function drawingRelsXml(int $count): string
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        for ($i = 1; $i <= $count; $i++) {
            $xml .= '<Relationship Id="rId' . $i . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/image' . $i . '.png"/>';
        }
        $xml .= '</Relationships>';

        return $xml;
    }

    /**
     * Logo di banner: logo pertama di kiri, logo kedua di kanan (kolom Status).
     * Ukuran mengikuti rasio asli gambar; 1 piksel = 9525 EMU.
     */
    private static function drawingXml(array $logos, int $lastCol): string
    {
        $emu    = 9525;
        $height = self::LOGO_PX * $emu;

        $xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<xdr:wsDr xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';

        foreach ($logos as $index => $logoPath) {
            $size  = @getimagesize($logoPath);
            $width = $height;
            if (is_array($size) && $size[1] > 0) {
                $width = (int) round($height * $size[0] / $size[1]);
            }

            if ($index === 0) {
                // Kiri: kolom pertama
                $col    = 0;
                $colOff = 8 * $emu;
            } else {
                // Kanan: di dalam kolom terakhir (Status), rata kanan
                $statusPx = self::STATUS_COL_WIDTH * 7 + 5;
                $col      = $lastCol - 1;
                $colOff   = max(0, $statusPx - (int) round($width / $emu) - 8) * $emu;
            }
            $rowOff = 10 * $emu;
            $id     = $index + 2;
            $rid    = 'rId' . ($index + 1);

            $xml .= '<xdr:oneCellAnchor>';
            $xml .= '<xdr:from><xdr:col>' . $col . '</xdr:col><xdr:colOff>' . $colOff . '</xdr:colOff><xdr:row>0</xdr:row><xdr:rowOff>' . $rowOff . '</xdr:rowOff></xdr:from>';
            $xml .= '<xdr:ext cx="' . $width . '" cy="' . $height . '"/>';
            $xml .= '<xdr:pic>';
            $xml .= '<xdr:nvPicPr><xdr:cNvPr id="' . $id . '" name="Logo ' . ($index + 1) . '"/><xdr:cNvPicPr><a:picLocks noChangeAspect="1"/></xdr:cNvPicPr></xdr:nvPicPr>';
            $xml .= '<xdr:blipFill><a:blip r:embed="' . $rid . '"/><a:stretch><a:fillRect/></a:stretch></xdr:blipFill>';
            $xml .= '<xdr:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $width . '" cy="' . $height . '"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></xdr:spPr>';
            $xml .= '</xdr:pic>';
            $xml .= '<xdr:clientData/>';
            $xml .= '</xdr:oneCellAnchor>';
        }

        $xml .= '</xdr:wsDr>';

        return $xml;
    }

    // Urutan <xf> di cellXfs HARUS sama dengan konstanta S_* di atas
    private static function stylesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
<numFmts count="2"><numFmt numFmtId="164" formatCode="0&quot;%&quot;"/><numFmt numFmtId="165" formatCode="+0&quot;%&quot;;-0&quot;%&quot;;0&quot;%&quot;"/></numFmts>
<fonts count="16">
<font><sz val="10"/><name val="Arial"/></font>
<font><b/><sz val="10"/><name val="Arial"/></font>
<font><b/><sz val="16"/><color rgb="FFFFFFFF"/><name val="Arial"/></font>
<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Arial"/></font>
<font><sz val="9"/><color rgb="FF475569"/><name val="Arial"/></font>
<font><b/><sz val="10"/><color rgb="FFB91C1C"/><name val="Arial"/></font>
<font><b/><sz val="10"/><color rgb="FFB45309"/><name val="Arial"/></font>
<font><b/><sz val="10"/><color rgb="FF15803D"/><name val="Arial"/></font>
<font><b/><sz val="10"/><color rgb="FF047857"/><name val="Arial"/></font>
<font><b/><sz val="10"/><color rgb="FF065F46"/><name val="Arial"/></font>
<font><b/><sz val="10"/><color rgb="FF92400E"/><name val="Arial"/></font>
<font><b/><sz val="10"/><color rgb="FF991B1B"/><name val="Arial"/></font>
<font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Arial"/></font>
<font><b/><sz val="10"/><color rgb="FF14532D"/><name val="Arial"/></font>
<font><b/><sz val="10"/><color rgb="FF1E3A8A"/><name val="Arial"/></font>
<font><b/><sz val="10"/><color rgb="FF78350F"/><name val="Arial"/></font>
</fonts>
<fills count="14">
<fill><patternFill patternType="none"/></fill>
<fill><patternFill patternType="gray125"/></fill>
<fill><patternFill patternType="solid"><fgColor rgb="FF0F2A52"/><bgColor indexed="64"/></patternFill></fill>
<fill><patternFill patternType="solid"><fgColor rgb="FFF1F5F9"/><bgColor indexed="64"/></patternFill></fill>
<fill><patternFill patternType="solid"><fgColor rgb="FFDCFCE7"/><bgColor indexed="64"/></patternFill></fill>
<fill><patternFill patternType="solid"><fgColor rgb="FFDBEAFE"/><bgColor indexed="64"/></patternFill></fill>
<fill><patternFill patternType="solid"><fgColor rgb="FFFEF3C7"/><bgColor indexed="64"/></patternFill></fill>
<fill><patternFill patternType="solid"><fgColor rgb="FFFEE2E2"/><bgColor indexed="64"/></patternFill></fill>
<fill><patternFill patternType="solid"><fgColor rgb="FFF0FDF4"/><bgColor indexed="64"/></patternFill></fill>
<fill><patternFill patternType="solid"><fgColor rgb="FFBBF7D0"/><bgColor indexed="64"/></patternFill></fill>
<fill><patternFill patternType="solid"><fgColor rgb="FFBFDBFE"/><bgColor indexed="64"/></patternFill></fill>
<fill><patternFill patternType="solid"><fgColor rgb="FFFDE68A"/><bgColor indexed="64"/></patternFill></fill>
<fill><patternFill patternType="solid"><fgColor rgb="FF1E3A8A"/><bgColor indexed="64"/></patternFill></fill>
<fill><patternFill patternType="solid"><fgColor rgb="FFD1FAE5"/><bgColor indexed="64"/></patternFill></fill>
</fills>
<borders count="2">
<border><left/><right/><top/><bottom/><diagonal/></border>
<border><left style="thin"><color rgb="FF94A3B8"/></left><right style="thin"><color rgb="FF94A3B8"/></right><top style="thin"><color rgb="FF94A3B8"/></top><bottom style="thin"><color rgb="FF94A3B8"/></bottom><diagonal/></border>
</borders>
<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
<cellXfs count="25">
<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
<xf numFmtId="0" fontId="2" fillId="2" borderId="0" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
<xf numFmtId="0" fontId="3" fillId="2" borderId="0" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
<xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>
<xf numFmtId="0" fontId="1" fillId="3" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
<xf numFmtId="0" fontId="13" fillId="4" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
<xf numFmtId="0" fontId="14" fillId="5" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
<xf numFmtId="0" fontId="15" fillId="6" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1"/></xf>
<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
<xf numFmtId="164" fontId="5" fillId="7" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
<xf numFmtId="164" fontId="6" fillId="6" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
<xf numFmtId="164" fontId="7" fillId="8" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
<xf numFmtId="164" fontId="1" fillId="9" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
<xf numFmtId="164" fontId="1" fillId="10" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
<xf numFmtId="164" fontId="1" fillId="11" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
<xf numFmtId="164" fontId="8" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
<xf numFmtId="165" fontId="7" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
<xf numFmtId="165" fontId="5" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
<xf numFmtId="0" fontId="9" fillId="13" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
<xf numFmtId="0" fontId="12" fillId="12" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
<xf numFmtId="0" fontId="10" fillId="6" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
<xf numFmtId="0" fontId="11" fillId="7" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
<xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
</cellXfs>
<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>
</styleSheet>
XML;
    }
}