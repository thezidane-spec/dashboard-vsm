<?php

namespace App\Support;

use App\Models\Project;
use RuntimeException;
use ZipArchive;

/**
 * Pembuat file .xlsx sederhana untuk laporan material (tanpa paket tambahan).
 * Hanya butuh ekstensi PHP "zip" (ZipArchive).
 *
 * Susunan lembar mengikuti laporan referensi:
 *   Tabel A = Vendor Raw Material, Tabel B = Raw Material,
 *   masing-masing dengan kolom tanggal kedatangan dinamis.
 */
class MaterialXlsx
{
    // Nomor style = urutan <xf> pada styles.xml (lihat stylesXml())
    private const S_DEFAULT   = 0;
    private const S_TITLE     = 1;
    private const S_META      = 2;
    private const S_SECTION   = 3;
    private const S_HEAD      = 4;
    private const S_TEXT      = 5;
    private const S_NUM       = 6;
    private const S_ACTUAL    = 7;
    private const S_DEV_ZERO  = 8;
    private const S_DEV_PLUS  = 9;
    private const S_DEV_MINUS = 10;
    private const S_FOOT      = 11;
    private const S_PERCENT   = 12;

    /** @var array<int, array<int, array{v: mixed, s: int}>> */
    private array $cells = [];

    /** @var string[] */
    private array $merges = [];

    /**
     * Buat file .xlsx sementara dan kembalikan path-nya.
     */
    public static function create(Project $project, array $report): string
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP "zip" belum aktif.');
        }

        return (new self())->generate($project, $report);
    }

    private function generate(Project $project, array $report): string
    {
        $sections = $report['sections'];

        // Jumlah kolom tanggal disamakan antar tabel supaya kolom REMARK sejajar
        $dateCols = 1;
        foreach ($sections as $section) {
            $dateCols = max($dateCols, count($section['dates']));
        }
        $remarkCol = 12 + $dateCols;

        $r = 1;
        $this->put($r, 1, 'LAPORAN MATERIAL - ' . $project->project_name, self::S_TITLE);
        $r++;
        $this->put($r, 1, 'Kode Proyek: ' . $project->project_code . '   |   Customer: ' . ($project->customer ?: '-'), self::S_META);
        $r++;
        $this->put($r, 1, 'Dicetak: ' . now()->format('d M Y H:i'), self::S_META);
        $r += 2;

        foreach ($sections as $section) {
            $r = $this->writeSection($r, $section, $dateCols);
            $r += 2;
        }

        // Lebar kolom
        $widths = [1 => 5, 2 => 34, 3 => 18, 4 => 12, 5 => 8, 6 => 7, 7 => 9, 8 => 10, 9 => 12, 10 => 9, 11 => 9];
        for ($c = 12; $c < $remarkCol; $c++) {
            $widths[$c] = 9;
        }
        $widths[$remarkCol] = 30;

        $sheet = $this->sheetXml($widths);

        $path = tempnam(sys_get_temp_dir(), 'mat');
        $zip  = new ZipArchive();

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Gagal membuat file Excel.');
        }

        $zip->addFromString('[Content_Types].xml', self::contentTypesXml());
        $zip->addFromString('_rels/.rels', self::rootRelsXml());
        $zip->addFromString('xl/workbook.xml', self::workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRelsXml());
        $zip->addFromString('xl/styles.xml', self::stylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
        $zip->close();

        return $path;
    }

    private function writeSection(int $r, array $section, int $dateCols): int
    {
        $remarkCol = 12 + $dateCols;

        // Judul tabel
        $this->put($r, 1, $section['letter'], self::S_SECTION);
        $this->put($r, 2, $section['title'], self::S_SECTION);
        $r++;

        // Header 2 baris
        $h1 = $r;
        $h2 = $r + 1;

        $headers = [1 => 'NO', 2 => 'MATERIAL', 3 => 'SPEC', 4 => 'TGL DIPERLUKAN', 9 => 'MILL CERTIFICATE', 10 => 'ACTUAL', 11 => 'DEVIASI', $remarkCol => 'REMARK'];
        foreach ($headers as $col => $label) {
            $this->put($h1, $col, $label, self::S_HEAD);
            $this->merge($h1, $col, $h2, $col, self::S_HEAD);
        }

        $this->put($h1, 5, 'REQ', self::S_HEAD);
        $this->merge($h1, 5, $h1, 8, self::S_HEAD);
        foreach ([5 => 'QTY', 6 => 'EA', 7 => 'WEIGHT', 8 => 'TOTAL'] as $col => $label) {
            $this->put($h2, $col, $label, self::S_HEAD);
        }

        $this->put($h1, 12, $section['incoming_title'], self::S_HEAD);
        $this->merge($h1, 12, $h1, 11 + $dateCols, self::S_HEAD);
        for ($i = 0; $i < $dateCols; $i++) {
            $label = isset($section['dates'][$i]) ? $section['dates'][$i]['label'] : '';
            $this->put($h2, 12 + $i, $label, self::S_HEAD);
        }

        // Baris data
        $r = $h2 + 1;
        foreach ($section['rows'] as $row) {
            $this->put($r, 1, $row['no'], self::S_NUM);
            $this->put($r, 2, (string) $row['name'], self::S_TEXT);
            $this->put($r, 3, (string) $row['spec'], self::S_TEXT);
            $this->put($r, 4, (string) $row['purchase_date'], self::S_NUM);
            $this->put($r, 5, $row['quantity'], self::S_NUM);
            $this->put($r, 6, (string) $row['uom'], self::S_NUM);
            $this->put($r, 7, $row['weight'] === null ? '' : $row['weight'], self::S_NUM);
            $this->put($r, 8, $row['total_weight'] === null ? '' : $row['total_weight'], self::S_NUM);
            $this->put($r, 9, $row['mill_cert'] ? '✓' : '', self::S_NUM);
            $this->put($r, 10, $row['actual'], self::S_ACTUAL);
            $this->put($r, 11, $row['deviation'], $this->deviationStyle($row['deviation']));

            for ($i = 0; $i < $dateCols; $i++) {
                $value = '';
                if (isset($section['dates'][$i])) {
                    $key = $section['dates'][$i]['key'];
                    if (isset($row['by_date'][$key])) {
                        $value = $row['by_date'][$key];
                    }
                }
                $this->put($r, 12 + $i, $value, self::S_NUM);
            }

            $this->put($r, $remarkCol, (string) $row['remarks'], self::S_TEXT);
            $r++;
        }

        if (count($section['rows']) === 0) {
            $this->put($r, 1, 'Belum ada material terdaftar.', self::S_TEXT);
            $this->merge($r, 1, $r, $remarkCol, self::S_TEXT);
            $r++;
        }

        // Baris total
        $t = $section['totals'];
        $this->put($r, 1, $section['summary_label'], self::S_FOOT);
        $this->merge($r, 1, $r, 4, self::S_FOOT);
        $this->put($r, 5, $t['quantity'], self::S_FOOT);
        $this->put($r, 6, '', self::S_FOOT);
        $this->put($r, 7, '', self::S_FOOT);
        $this->put($r, 8, $t['total_weight'], self::S_FOOT);
        $this->put($r, 9, $t['mill_cert'] . '/' . count($section['rows']), self::S_FOOT);
        $this->put($r, 10, $t['actual'], self::S_FOOT);
        $this->put($r, 11, $t['deviation'], self::S_FOOT);
        $this->put($r, 12, $section['percentage_label'], self::S_PERCENT);
        $this->merge($r, 12, $r, 11 + $dateCols, self::S_PERCENT);
        $this->put($r, $remarkCol, MaterialReport::percent($t['percentage']), self::S_PERCENT);

        return $r + 1;
    }

    private function deviationStyle($deviation): int
    {
        $deviation = round((float) $deviation, 2);

        if ($deviation > 0) {
            return self::S_DEV_PLUS;
        }
        if ($deviation < 0) {
            return self::S_DEV_MINUS;
        }

        return self::S_DEV_ZERO;
    }

    // ---------------------------------------------------------------------
    // Pengelola sel
    // ---------------------------------------------------------------------

    private function put(int $row, int $col, $value, int $style = self::S_DEFAULT): void
    {
        $this->cells[$row][$col] = ['v' => $value, 's' => $style];
    }

    // Gabung sel; sel lain di area gabungan diberi style yang sama supaya border tampil
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

    private function sheetXml(array $widths): string
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $xml .= '<sheetPr><pageSetUpPr fitToPage="1"/></sheetPr>';
        $xml .= '<sheetViews><sheetView showGridLines="0" workbookViewId="0"/></sheetViews>';
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
            $xml .= '<row r="' . $rowNumber . '">';
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

        $xml .= '<pageMargins left="0.4" right="0.4" top="0.5" bottom="0.5" header="0.3" footer="0.3"/>';
        $xml .= '<pageSetup paperSize="9" orientation="landscape" fitToWidth="1" fitToHeight="0"/>';
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

    private static function contentTypesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>
XML;
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

    private static function workbookXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
<sheets><sheet name="Laporan Material" sheetId="1" r:id="rId1"/></sheets>
</workbook>
XML;
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

    // Urutan <xf> di cellXfs HARUS sama dengan konstanta S_* di atas
    private static function stylesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
<fonts count="7">
<font><sz val="10"/><name val="Arial"/></font>
<font><b/><sz val="10"/><name val="Arial"/></font>
<font><b/><sz val="14"/><name val="Arial"/></font>
<font><b/><sz val="10"/><color rgb="FF047857"/><name val="Arial"/></font>
<font><b/><sz val="10"/><color rgb="FF92400E"/><name val="Arial"/></font>
<font><b/><sz val="10"/><color rgb="FF991B1B"/><name val="Arial"/></font>
<font><b/><sz val="10"/><color rgb="FFC2410C"/><name val="Arial"/></font>
</fonts>
<fills count="7">
<fill><patternFill patternType="none"/></fill>
<fill><patternFill patternType="gray125"/></fill>
<fill><patternFill patternType="solid"><fgColor rgb="FFE5E7EB"/><bgColor indexed="64"/></patternFill></fill>
<fill><patternFill patternType="solid"><fgColor rgb="FFD1FAE5"/><bgColor indexed="64"/></patternFill></fill>
<fill><patternFill patternType="solid"><fgColor rgb="FFFEF3C7"/><bgColor indexed="64"/></patternFill></fill>
<fill><patternFill patternType="solid"><fgColor rgb="FFFEE2E2"/><bgColor indexed="64"/></patternFill></fill>
<fill><patternFill patternType="solid"><fgColor rgb="FFF3F4F6"/><bgColor indexed="64"/></patternFill></fill>
</fills>
<borders count="2">
<border><left/><right/><top/><bottom/><diagonal/></border>
<border><left style="thin"><color rgb="FF94A3B8"/></left><right style="thin"><color rgb="FF94A3B8"/></right><top style="thin"><color rgb="FF94A3B8"/></top><bottom style="thin"><color rgb="FF94A3B8"/></bottom><diagonal/></border>
</borders>
<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
<cellXfs count="13">
<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>
<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>
<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>
<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1"/></xf>
<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
<xf numFmtId="0" fontId="3" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
<xf numFmtId="0" fontId="3" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
<xf numFmtId="0" fontId="4" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
<xf numFmtId="0" fontId="5" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
<xf numFmtId="0" fontId="1" fillId="6" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
<xf numFmtId="0" fontId="6" fillId="6" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
</cellXfs>
<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>
</styleSheet>
XML;
    }
}