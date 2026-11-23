<?php

namespace App\Support;

use App\Models\WeeklyReport;
use RuntimeException;
use ZipArchive;

/**
 * Ekspor Weekly Report (Foto Progress) ke .xlsx, mengikuti tata letak sheet "Lembar1" pada file contoh:
 * kotak header (logo + "FOTO PROGRESS" + periode), tabel identitas, lalu kotak per tahap
 * berisi foto. Judul dan jumlah tahap mengikuti isian laporan (bawaan: Material, Fabrication, Galvanized, Packing, Delivery). Font: Calibri / Calibri Light seperti contoh.
 * File dibuat langsung sebagai XML (tanpa library tambahan).
 */
class WeeklyReportXlsx
{
    private const COLS = 9;                       // A..I
    private const EMU  = 9525;                    // 1 piksel = 9525 EMU
    private const MAX_ROW_PT = 409;               // batas tinggi baris Excel
    private const PAD = 10;                       // jarak foto dari tepi kotak (px)
    private const GAP = 10;                       // jarak antar foto (px)
    private const CAP_LINE_PX = 19;               // tinggi satu baris keterangan foto (Calibri 11)
    private const CAP_PAD = 4;                    // jarak keterangan dari foto (px)
    private const CAP_CHAR_PX = 6.6;              // perkiraan lebar rata-rata satu huruf (px)

    // Lebar kolom (karakter) sama dengan Lembar1; piksel = lebar*7+5
    private const WIDTHS = [1 => 9, 2 => 35, 3 => 9, 4 => 9, 5 => 9, 6 => 9, 7 => 9, 8 => 9, 9 => 18.22];

    private array $cells = [];        // [row][col] => ['v' => nilai, 'o' => opsi style, 'b' => border sisi]
    private array $merges = [];
    private array $rowPt = [];        // tinggi baris (pt)
    private array $fonts = [];
    private array $borders = [];
    private array $xfs = [];
    private array $blocks = [];      // [baris awal, baris akhir] tiap tahap (untuk pemisah halaman)
    private array $pics = [];         // ['path','w','h','x','y','row' (nomor baris awal)]

    public static function create(WeeklyReport $report, string $photoDisk = 'local'): string
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP "zip" belum aktif.');
        }

        return (new self())->generate($report, $photoDisk);
    }

    // ------------------------------------------------------------------ ukuran
    private static function colPx(int $col): float
    {
        return floor(self::WIDTHS[$col] * 7 + 5);
    }

    private static function totalPx(): float
    {
        $t = 0;
        for ($c = 1; $c <= self::COLS; $c++) { $t += self::colPx($c); }
        return $t;
    }

    private function rowPx(int $row): float
    {
        return ($this->rowPt[$row] ?? 15) * 96 / 72;
    }

    // ------------------------------------------------------------------ sel
    private function put(int $r, int $c, $value, array $o = []): void
    {
        $this->cells[$r][$c] = ['v' => $value, 'o' => $o, 'b' => $this->cells[$r][$c]['b'] ?? []];
    }

    private function border(int $r, int $c, string $side, string $style): void
    {
        if (!isset($this->cells[$r][$c])) { $this->cells[$r][$c] = ['v' => '', 'o' => [], 'b' => []]; }
        $this->cells[$r][$c]['b'][$side] = $style;
    }

    // Garis tepi sebuah area (tiap sel di tepi mendapat sisi yang sesuai)
    private function outline(int $r1, int $c1, int $r2, int $c2, string $style): void
    {
        for ($c = $c1; $c <= $c2; $c++) { $this->border($r1, $c, 'top', $style); $this->border($r2, $c, 'bottom', $style); }
        for ($r = $r1; $r <= $r2; $r++) { $this->border($r, $c1, 'left', $style); $this->border($r, $c2, 'right', $style); }
    }

    private function merge(int $r1, int $c1, int $r2, int $c2): void
    {
        $this->merges[] = self::ref($r1, $c1) . ':' . self::ref($r2, $c2);
        for ($r = $r1; $r <= $r2; $r++) {
            for ($c = $c1; $c <= $c2; $c++) {
                if (!isset($this->cells[$r][$c])) { $this->cells[$r][$c] = ['v' => '', 'o' => [], 'b' => []]; }
            }
        }
    }

    private static function colName(int $col): string
    {
        $n = '';
        while ($col > 0) { $m = ($col - 1) % 26; $n = chr(65 + $m) . $n; $col = intdiv($col - 1, 26); }
        return $n;
    }

    private static function ref(int $r, int $c): string { return self::colName($c) . $r; }

    private static function esc(string $t): string
    {
        $t = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $t);
        return htmlspecialchars($t, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    // ------------------------------------------------------------------ style
    private function fontId(string $name, float $size, bool $bold): int
    {
        $key = "$name|$size|" . ($bold ? 1 : 0);
        if (!isset($this->fonts[$key])) { $this->fonts[$key] = count($this->fonts); }
        return $this->fonts[$key];
    }

    private function borderId(array $b): int
    {
        $key = implode('|', [$b['left'] ?? '', $b['right'] ?? '', $b['top'] ?? '', $b['bottom'] ?? '']);
        if (!isset($this->borders[$key])) { $this->borders[$key] = ['b' => $b, 'id' => count($this->borders) + 1]; }
        return $this->borders[$key]['id'];
    }

    private function xfId(array $o, array $b): int
    {
        $font = $this->fontId($o['font'] ?? 'Calibri', (float) ($o['size'] ?? 11), !empty($o['bold']));
        $bid  = $this->borderId($b);
        $fill = !empty($o['white']) ? 1 : 0;
        $key  = implode('|', [$font, $bid, $fill, $o['h'] ?? '', $o['v'] ?? '', !empty($o['wrap']) ? 1 : 0]);
        if (!isset($this->xfs[$key])) {
            $this->xfs[$key] = ['font' => $font, 'border' => $bid, 'fill' => $fill, 'h' => $o['h'] ?? null, 'v' => $o['v'] ?? null, 'wrap' => !empty($o['wrap']), 'id' => count($this->xfs) + 1];
        }
        return $this->xfs[$key]['id'];
    }

    // ------------------------------------------------------------------ penyusunan
    private function generate(WeeklyReport $report, string $disk): string
    {
        $report->loadMissing('photos', 'project');
        $totalW = self::totalPx();

        // Baris 1-4: kotak header (logo | FOTO PROGRESS / periode)
        $this->rowPt = [1 => 25.95, 2 => 14.55, 3 => 34.05, 4 => 16.95];
        $this->merge(1, 1, 4, 2);
        $this->put(1, 3, 'FOTO PROGRESS ', ['size' => 16, 'h' => 'center', 'v' => 'center', 'wrap' => true]);
        $this->merge(1, 3, 2, 9);
        $this->put(3, 3, (string) ($report->period ?? ''), ['size' => 16, 'h' => 'center', 'v' => 'center', 'wrap' => true]);
        $this->merge(3, 3, 4, 9);
        $this->outline(1, 1, 4, 9, 'medium');
        $this->outline(1, 1, 4, 2, 'thin');
        $this->outline(1, 3, 2, 9, 'thin');
        $this->outline(3, 3, 4, 9, 'thin');
        $this->border(1, 1, 'left', 'medium'); $this->border(1, 1, 'top', 'medium');
        for ($c = 1; $c <= 9; $c++) { $this->border(4, $c, 'bottom', $c <= 2 ? 'thin' : 'thin'); }
        // sisi luar medium diterapkan ulang agar tidak tertimpa garis tipis
        for ($c = 1; $c <= 9; $c++) { $this->border(1, $c, 'top', 'medium'); }
        for ($r = 1; $r <= 4; $r++) { $this->border($r, 1, 'left', 'medium'); $this->border($r, 9, 'right', 'medium'); }

        // Baris 5-9: tabel identitas (label A:B, isi C:I)
        $info = [
            ['Name of Costumer ', $report->customer_name],
            ['Name of corporate Group', $report->corporate_group],
            ['Address', $report->address],
            ['Postcode / Town / Country', $report->postcode],
            ['Language', $report->language],
        ];
        $r = 5;
        foreach ($info as [$label, $value]) {
            $value = trim((string) $value);
            $this->put($r, 1, $label, ['font' => 'Calibri Light', 'bold' => true, 'h' => 'left', 'v' => 'center', 'white' => true]);
            $this->merge($r, 1, $r, 2);
            $this->put($r, 3, $value === '' ? ':' : ': ' . $value, ['h' => 'left', 'v' => 'center', 'wrap' => true, 'white' => true]);
            $this->merge($r, 3, $r, 9);
            // tinggi baris menyesuaikan isi panjang (mis. alamat)
            $lines = max(1, (int) ceil(mb_strlen($value) / 78));
            if ($lines > 1) { $this->rowPt[$r] = 15 * $lines; }
            for ($c = 1; $c <= 9; $c++) {
                foreach (['top', 'bottom'] as $s) { $this->border($r, $c, $s, 'thin'); }
            }
            $this->border($r, 1, 'left', 'medium');
            $this->border($r, 2, 'right', 'thin');
            $this->border($r, 3, 'left', 'thin');
            $this->border($r, 9, 'right', 'thin');
            $r++;
        }
        for ($c = 1; $c <= 9; $c++) { $this->border($r - 1, $c, 'bottom', 'medium'); }

        // Tahap-tahap foto
        $r++;                                   // baris kosong
        $this->rowPt[$r - 1] = 15.15;
        foreach ($report->sectionList() as $sec) {
            $key = $sec['key'];
            $blockStart = $r;
            $this->rowPt[$r] = 15.15;
            $this->put($r, 1, mb_strtoupper($sec['title']), ['bold' => true, 'h' => 'left', 'v' => 'center']);
            $this->merge($r, 1, $r, 3);
            $this->put($r, 4, $sec['time'], ['h' => 'center', 'v' => 'center']);
            $this->merge($r, 4, $r, 9);
            $this->outline($r, 1, $r, 3, 'medium');
            $this->outline($r, 4, $r, 9, 'medium');

            $photoRow = $r + 1;
            $this->merge($photoRow, 1, $photoRow, 9);
            $this->outline($photoRow, 1, $photoRow, 9, 'medium');
            $files = [];
            foreach ($report->photos->where('section', $key) as $photo) {
                $abs = \Illuminate\Support\Facades\Storage::disk($disk)->path($photo->path);
                $size = is_file($abs) ? @getimagesize($abs) : false;
                if ($size && $size[0] > 0 && $size[1] > 0) { $files[] = ['path' => $abs, 'w' => $size[0], 'h' => $size[1], 'cap' => trim((string) $photo->caption)]; }
            }
            $this->rowPt[$photoRow] = $files ? $this->layoutPhotos($files, $photoRow, $totalW) : 15.15;
            $r = $photoRow + 2;                  // satu baris kosong antar tahap
            $this->blocks[] = [$blockStart, $photoRow + 1];
            $this->rowPt[$r - 1] = 15.15;
        }
        $lastRow = $r - 2;                       // baris foto tahap terakhir

        // Logo di kotak header
        $logoKey = array_key_exists($report->logo, WeeklyReport::LOGOS) ? $report->logo : 'mutiara';
        $logoPath = public_path(WeeklyReport::LOGOS[$logoKey]['file']);
        if (is_file($logoPath) && ($ls = @getimagesize($logoPath))) {
            $boxW = self::colPx(1) + self::colPx(2);
            $boxH = $this->rowPx(1) + $this->rowPx(2) + $this->rowPx(3) + $this->rowPx(4);
            $scale = min(($boxW - 16) / $ls[0], ($boxH - 12) / $ls[1]);
            $w = $ls[0] * $scale; $h = $ls[1] * $scale;
            $this->pics[] = ['path' => $logoPath, 'w' => $w, 'h' => $h, 'x' => ($boxW - $w) / 2, 'y' => ($boxH - $h) / 2, 'top' => 0, 'name' => 'Logo'];
        }

        return $this->write($lastRow);
    }

    // Tinggi kotak keterangan foto (px) menurut panjang teks dan lebar foto
    private function capHeight(string $cap, float $w): float
    {
        if ($cap === '') { return 0; }
        $perLine = max(8, (int) floor(($w - 6) / self::CAP_CHAR_PX));
        $lines = 0;
        foreach (preg_split('/\R/u', $cap) as $part) { $lines += max(1, (int) ceil(mb_strlen($part) / $perLine)); }

        return $lines * self::CAP_LINE_PX + self::CAP_PAD;
    }

    /**
     * Susun foto (dan keterangannya, tepat di bawah tiap foto) di dalam kotak tahap; kembalikan tinggi baris (pt).
     * Posisi disimpan relatif terhadap baris foto.
     */
    private function layoutPhotos(array $files, int $row, float $totalW): float
    {
        $n = count($files);
        $perRow = $n === 1 ? 1 : ($n <= 4 ? 2 : 3);
        $boxW = ($totalW - 2 * self::PAD - ($perRow - 1) * self::GAP) / $perRow;
        $maxH = $perRow === 1 ? 420 : ($perRow === 2 ? 300 : 220);

        $lines = [];
        foreach (array_chunk($files, $perRow) as $chunk) {
            $items = [];
            foreach ($chunk as $f) {
                $s = min($boxW / $f['w'], $maxH / $f['h'], 1.5);
                $items[] = ['f' => $f, 'w' => $f['w'] * $s, 'h' => $f['h'] * $s];
            }
            $lines[] = $items;
        }

        // Tinggi satu baris foto = foto tertinggi + keterangannya
        $lineHeight = function (array $items, float $k): float {
            return max(array_map(fn($x) => $x['h'] * $k + $this->capHeight($x['f']['cap'], $x['w'] * $k), $items));
        };
        $totalFor = function (float $k) use ($lines, $lineHeight): float {
            $t = 2 * self::PAD + (count($lines) - 1) * self::GAP;
            foreach ($lines as $items) { $t += $lineHeight($items, $k); }
            return $t;
        };

        // Batas tinggi baris Excel (409 pt): perkecil semua foto bila terlalu tinggi (keterangan tetap ukuran normal)
        $limitPx = (self::MAX_ROW_PT - 2) * 96 / 72;
        $k = 1.0;
        for ($i = 0; $i < 60 && $totalFor($k) > $limitPx; $i++) { $k *= 0.97; }

        $y = self::PAD;
        foreach ($lines as $items) {
            $lineH = $lineHeight($items, $k);
            $hasCap = false;
            foreach ($items as $it) { if ($it['f']['cap'] !== '') { $hasCap = true; } }
            $lineW = array_sum(array_column($items, 'w')) * $k + (count($items) - 1) * self::GAP;
            $x = ($totalW - $lineW) / 2;
            foreach ($items as $it) {
                $w = $it['w'] * $k; $h = $it['h'] * $k;
                $capH = $this->capHeight($it['f']['cap'], $w);
                // bila ada keterangan, foto rata atas agar keterangan menempel di bawah foto
                $py = $hasCap ? $y : $y + ($lineH - $h) / 2;
                $this->pics[] = ['path' => $it['f']['path'], 'w' => $w, 'h' => $h, 'x' => $x, 'y' => $py, 'row' => $row, 'name' => 'Foto'];
                if ($capH > 0) {
                    $this->pics[] = ['type' => 'text', 'text' => $it['f']['cap'], 'w' => $w, 'h' => $capH, 'x' => $x, 'y' => $py + $h, 'row' => $row, 'name' => 'Keterangan'];
                }
                $x += $w + self::GAP;
            }
            $y += $lineH + self::GAP;
        }
        $heightPx = $y - self::GAP + self::PAD;

        return min(self::MAX_ROW_PT, round($heightPx * 72 / 96 + 1, 2));
    }

    // ------------------------------------------------------------------ penulisan file
    private function write(int $lastRow): string
    {
        // posisi atas tiap baris (px) untuk menghitung anchor gambar
        $tops = [];
        $acc = 0;
        for ($r = 1; $r <= $lastRow + 1; $r++) { $tops[$r] = $acc; $acc += $this->rowPx($r); }

        $media = [];
        $drawing = '';
        foreach ($this->pics as $i => $pic) {
            $isText = ($pic['type'] ?? 'pic') === 'text';
            $mi = 0;
            if (!$isText) {
                $ext = strtolower(pathinfo($pic['path'], PATHINFO_EXTENSION));
                $ext = in_array($ext, ['jpg', 'jpeg'], true) ? 'jpeg' : 'png';
                $mi = count($media) + 1;
                $media[] = ['idx' => $mi, 'ext' => $ext, 'path' => $pic['path']];
            }

            $absY = ($tops[$pic['row'] ?? 1] ?? 0) + $pic['y'];
            $absX = $pic['x'];
            // x -> (kolom, offset)
            $col = 0; $left = 0;
            for ($c = 1; $c <= self::COLS; $c++) {
                $w = self::colPx($c);
                if ($absX < $left + $w || $c === self::COLS) { $col = $c - 1; break; }
                $left += $w;
            }
            $colOff = max(0, $absX - $left);
            // y -> (baris, offset)
            $rowIdx = 1;
            for ($r = 1; $r <= $lastRow; $r++) {
                if ($absY < $tops[$r] + $this->rowPx($r) || $r === $lastRow) { $rowIdx = $r; break; }
            }
            $rowOff = max(0, $absY - $tops[$rowIdx]);

            $cx = (int) round($pic['w'] * self::EMU);
            $cy = (int) round($pic['h'] * self::EMU);
            $from = '<xdr:from><xdr:col>' . $col . '</xdr:col><xdr:colOff>' . (int) round($colOff * self::EMU)
                . '</xdr:colOff><xdr:row>' . ($rowIdx - 1) . '</xdr:row><xdr:rowOff>' . (int) round($rowOff * self::EMU) . '</xdr:rowOff></xdr:from>';
            if ($isText) {
                // kotak teks tanpa garis / isi: keterangan foto, rata tengah, Calibri 11
                $paras = '';
                foreach (preg_split('/\R/u', (string) $pic['text']) as $part) {
                    $paras .= '<a:p><a:pPr algn="ctr"/><a:r><a:rPr lang="id-ID" sz="1100"><a:solidFill><a:srgbClr val="000000"/></a:solidFill><a:latin typeface="Calibri"/></a:rPr><a:t>' . self::esc($part) . '</a:t></a:r></a:p>';
                }
                $drawing .= '<xdr:oneCellAnchor>' . $from . '<xdr:ext cx="' . $cx . '" cy="' . $cy . '"/>'
                    . '<xdr:sp macro="" textlink=""><xdr:nvSpPr><xdr:cNvPr id="' . ($i + 2) . '" name="Keterangan ' . ($i + 1) . '"/><xdr:cNvSpPr txBox="1"/></xdr:nvSpPr>'
                    . '<xdr:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $cx . '" cy="' . $cy . '"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom><a:noFill/><a:ln><a:noFill/></a:ln></xdr:spPr>'
                    . '<xdr:txBody><a:bodyPr vertOverflow="overflow" horzOverflow="overflow" wrap="square" lIns="0" tIns="' . (self::CAP_PAD * self::EMU) . '" rIns="0" bIns="0" rtlCol="0" anchor="t"/><a:lstStyle/>' . $paras . '</xdr:txBody></xdr:sp><xdr:clientData/></xdr:oneCellAnchor>';
                continue;
            }
            $drawing .= '<xdr:oneCellAnchor>' . $from
                . '<xdr:ext cx="' . $cx . '" cy="' . $cy . '"/>'
                . '<xdr:pic><xdr:nvPicPr><xdr:cNvPr id="' . ($i + 2) . '" name="' . $pic['name'] . ' ' . ($i + 1) . '"/><xdr:cNvPicPr><a:picLocks noChangeAspect="1"/></xdr:cNvPicPr></xdr:nvPicPr>'
                . '<xdr:blipFill><a:blip r:embed="rId' . $mi . '"/><a:stretch><a:fillRect/></a:stretch></xdr:blipFill>'
                . '<xdr:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $cx . '" cy="' . $cy . '"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></xdr:spPr></xdr:pic><xdr:clientData/></xdr:oneCellAnchor>';
        }

        // Sampai 5 tahap: satu halaman penuh (sama dengan contoh). Lebih dari itu: lanjut ke halaman berikutnya,
        // dengan pemisah halaman di antara tahap supaya kotak foto tidak terpotong.
        $fitH = 1;
        $breaksXml = '';
        if (count($this->blocks) > 5) {
            $fitH = 0;
            $pageH = (978 / (657 / self::totalPx())) * 0.95;      // tinggi halaman A4 (px lembar) dengan cadangan 5%
            $used = 0;
            for ($r = 1; $r < $this->blocks[0][0]; $r++) { $used += $this->rowPx($r); }
            $breaks = [];
            foreach ($this->blocks as [$a, $b]) {
                $h = 0;
                for ($r = $a; $r <= min($b, $lastRow); $r++) { $h += $this->rowPx($r); }
                if ($used > 0 && $used + $h > $pageH) { $breaks[] = $a - 1; $used = 0; }
                $used += $h;
            }
            if ($breaks) {
                $breaksXml = '<rowBreaks count="' . count($breaks) . '" manualBreakCount="' . count($breaks) . '">'
                    . implode('', array_map(fn($b) => '<brk id="' . $b . '" max="16383" man="1"/>', $breaks)) . '</rowBreaks>';
            }
        }

        // sheet
        ksort($this->cells);
        $rowsXml = '';
        for ($r = 1; $r <= $lastRow; $r++) {
            $cols = $this->cells[$r] ?? [];
            ksort($cols);
            $ht = isset($this->rowPt[$r]) ? ' ht="' . $this->rowPt[$r] . '" customHeight="1"' : '';
            $rowsXml .= '<row r="' . $r . '"' . $ht . '>';
            foreach ($cols as $c => $cell) {
                $ref = self::ref($r, $c);
                $xf  = ($cell['o'] || $cell['b']) ? $this->xfId($cell['o'], $cell['b']) : 0;
                $v   = $cell['v'];
                $rowsXml .= ($v === '' || $v === null)
                    ? '<c r="' . $ref . '" s="' . $xf . '"/>'
                    : '<c r="' . $ref . '" s="' . $xf . '" t="inlineStr"><is><t xml:space="preserve">' . self::esc((string) $v) . '</t></is></c>';
            }
            $rowsXml .= '</row>';
        }

        $cols = '';
        for ($c = 1; $c <= self::COLS; $c++) { $cols .= '<col min="' . $c . '" max="' . $c . '" width="' . self::WIDTHS[$c] . '" customWidth="1"/>'; }

        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheetPr><pageSetUpPr fitToPage="1"/></sheetPr>'
            . '<sheetViews><sheetView workbookViewId="0"/></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="15"/>'
            . '<cols>' . $cols . '</cols><sheetData>' . $rowsXml . '</sheetData>'
            . '<mergeCells count="' . count($this->merges) . '">' . implode('', array_map(fn($m) => '<mergeCell ref="' . $m . '"/>', $this->merges)) . '</mergeCells>'
            . '<printOptions horizontalCentered="1"/>'
            . '<pageMargins left="0.708661417322835" right="0.708661417322835" top="0.748031496062992" bottom="0.748031496062992" header="0.31496062992126" footer="0.31496062992126"/>'
            . '<pageSetup paperSize="9" orientation="portrait" fitToWidth="1" fitToHeight="' . $fitH . '"/>'
            . $breaksXml
            . ($this->pics ? '<drawing r:id="rId1"/>' : '')
            . '</worksheet>';

        $path = tempnam(sys_get_temp_dir(), 'wkr');
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Gagal membuat file Excel.');
        }

        $ct = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/>'
            . '<Default Extension="png" ContentType="image/png"/><Default Extension="jpeg" ContentType="image/jpeg"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . ($this->pics ? '<Override PartName="/xl/drawings/drawing1.xml" ContentType="application/vnd.openxmlformats-officedocument.drawing+xml"/>' : '')
            . '</Types>';
        $zip->addFromString('[Content_Types].xml', $ct);
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Lembar1" sheetId="1" r:id="rId1"/></sheets>'
            . '<definedNames><definedName name="_xlnm.Print_Area" localSheetId="0">Lembar1!$A$1:$I$' . $lastRow . '</definedName></definedNames></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
        $zip->addFromString('xl/styles.xml', $this->stylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
        if ($this->pics) {
            $zip->addFromString('xl/worksheets/_rels/sheet1.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing" Target="../drawings/drawing1.xml"/></Relationships>');
            $zip->addFromString('xl/drawings/drawing1.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><xdr:wsDr xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' . $drawing . '</xdr:wsDr>');
            $rels = '';
            foreach ($media as $m) {
                $rels .= '<Relationship Id="rId' . $m['idx'] . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/image' . $m['idx'] . '.' . $m['ext'] . '"/>';
                $zip->addFromString('xl/media/image' . $m['idx'] . '.' . $m['ext'], (string) file_get_contents($m['path']));
            }
            $zip->addFromString('xl/drawings/_rels/drawing1.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . $rels . '</Relationships>');
        }
        $zip->close();

        return $path;
    }

    private function stylesXml(): string
    {
        // pastikan font default (Calibri 11) ada di indeks 0
        $fonts = [];
        $fontXml = function (string $key): string {
            [$name, $size, $bold] = explode('|', $key);
            return '<font>' . ($bold === '1' ? '<b/>' : '') . '<sz val="' . $size . '"/><name val="' . $name . '"/><family val="2"/></font>';
        };
        // font urut sesuai id
        $byId = [];
        foreach ($this->fonts as $key => $id) { $byId[$id] = $key; }
        ksort($byId);
        $fontsXml = '<font><sz val="11"/><name val="Calibri"/><family val="2"/></font>';
        $fontOffset = 1;
        foreach ($byId as $id => $key) { $fontsXml .= $fontXml($key); }

        $bordersXml = '<border><left/><right/><top/><bottom/><diagonal/></border>';
        foreach ($this->borders as $entry) {
            $b = $entry['b'];
            $side = fn($tag, $st) => $st ? '<' . $tag . ' style="' . $st . '"><color auto="1"/></' . $tag . '>' : '<' . $tag . '/>';
            $bordersXml .= '<border>' . $side('left', $b['left'] ?? '') . $side('right', $b['right'] ?? '') . $side('top', $b['top'] ?? '') . $side('bottom', $b['bottom'] ?? '') . '<diagonal/></border>';
        }

        $xfXml = '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>';
        foreach ($this->xfs as $x) {
            $align = '';
            if ($x['h'] || $x['v'] || $x['wrap']) {
                $align = '<alignment' . ($x['h'] ? ' horizontal="' . $x['h'] . '"' : '') . ($x['v'] ? ' vertical="' . $x['v'] . '"' : '') . ($x['wrap'] ? ' wrapText="1"' : '') . '/>';
            }
            $xfXml .= '<xf numFmtId="0" fontId="' . ($x['font'] + $fontOffset) . '" fillId="' . ($x['fill'] ? 2 : 0) . '" borderId="' . $x['border'] . '" xfId="0" applyFont="1" applyBorder="1"' . ($x['fill'] ? ' applyFill="1"' : '') . ($align ? ' applyAlignment="1">' . $align . '</xf>' : '/>');
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="' . (count($this->fonts) + 1) . '">' . $fontsXml . '</fonts>'
            . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="' . (count($this->borders) + 1) . '">' . $bordersXml . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="' . (count($this->xfs) + 1) . '">' . $xfXml . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }
}