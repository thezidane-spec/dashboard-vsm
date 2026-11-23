<?php

namespace App\Support;

use Symfony\Component\Process\Process;

/**
 * OCR tabel BOM dari PDF hasil scan (gambar). Membutuhkan `pdftoppm` (poppler-utils) dan `tesseract`
 * di server, serta ekstensi PHP GD. Alurnya:
 *   1. halaman PDF -> PNG 300 dpi, 2. tesseract -> kata + posisi (TSV),
 *   3. garis tabel (horizontal & vertikal) dideteksi dari piksel gambar -> sel,
 *   4. kolom dipetakan dari teks header (Material, Spec, Qty, UoM, ...), baris = baris bernomor.
 * Hasilnya PRATINJAU; pengguna memeriksa sebelum impor.
 */
class PdfTableOcr
{
    // Catatan langkah OCR terakhir (untuk diagnosa bila hasil kosong)
    public static array $trace = [];

    private static function note(string $msg): void
    {
        self::$trace[] = $msg;
    }

    public static function available(): bool
    {
        self::$trace = [];
        if (!function_exists('imagecreatefrompng')) {
            self::note('ekstensi PHP GD belum aktif');
            return false;
        }
        $ok = true;
        foreach (['pdftoppm' => 'poppler', 'tesseract' => 'tesseract'] as $name => $needle) {
            if (!self::works($name)) {
                $ok = false;
            }
        }
        return $ok;
    }

    private static array $resolved = [];

    // Lokasi program: .env (PDFTOPPM_BIN, TESSERACT_BIN, PDFTOTEXT_BIN) -> PATH sistem -> lokasi umum di Windows
    public static function bin(string $name): string
    {
        if (isset(self::$resolved[$name])) {
            return self::$resolved[$name];
        }

        $candidates = [];
        $env = env(strtoupper($name) . '_BIN');
        if (is_string($env) && trim($env) !== '') {
            $env = trim($env, " \t\"'");
            if (is_file($env)) {
                return self::$resolved[$name] = $env;
            }
            self::note(strtoupper($name) . "_BIN di .env tidak ditemukan: {$env}");
        }

        // PATH sistem
        if (self::runs($name)) {
            return self::$resolved[$name] = $name;
        }

        // Lokasi umum di Windows
        if (DIRECTORY_SEPARATOR === '\\') {
            $profile = str_replace('\\', '/', (string) getenv('USERPROFILE'));
            $local   = str_replace('\\', '/', (string) getenv('LOCALAPPDATA'));
            $dirs = ['C:/poppler*', 'C:/Program Files/poppler*', 'C:/Program Files (x86)/poppler*', 'C:/tools/poppler*', 'C:/laragon/bin/poppler*',
                     $profile . '/poppler*', $profile . '/Downloads/poppler*', $profile . '/Desktop/poppler*', $profile . '/scoop/apps/poppler/current'];
            $patterns = [];
            if ($name === 'tesseract') {
                $patterns = ['C:/Program Files/Tesseract-OCR/tesseract.exe', 'C:/Program Files (x86)/Tesseract-OCR/tesseract.exe', $local . '/Programs/Tesseract-OCR/tesseract.exe'];
            } else {
                foreach ($dirs as $d) {
                    foreach (['', '/*', '/*/*'] as $depth) {
                        foreach (['/Library/bin/', '/bin/'] as $sub) {
                            $patterns[] = $d . $depth . $sub . $name . '.exe';
                        }
                    }
                }
            }
            foreach ($patterns as $pattern) {
                foreach (glob($pattern) ?: [] as $found) {
                    if (is_file($found)) {
                        return self::$resolved[$name] = $found;
                    }
                }
            }
        }

        return $name;
    }

    // Jalankan program dan pastikan benar-benar program yang dimaksud (bukan pesan "path not found" dari OS)
    private static function runs(string $binary): bool
    {
        try {
            $name = strtolower(pathinfo($binary, PATHINFO_FILENAME));
            $p = new Process([$binary, $name === 'tesseract' ? '--version' : '-v']);
            $p->setTimeout(15);
            $p->run();
            $out = strtolower($p->getOutput() . $p->getErrorOutput());
            return strpos($out, $name === 'tesseract' ? 'tesseract' : 'version') !== false && strpos($out, 'cannot find') === false && strpos($out, 'not recognized') === false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private static function works(string $name): bool
    {
        $bin = self::bin($name);
        if ($bin === $name && !self::runs($name)) {
            self::note("program {$name} tidak ditemukan (isi " . strtoupper($name) . "_BIN di .env dengan lokasi {$name}.exe, mis. C:/poppler/Library/bin/pdftoppm.exe)");
            return false;
        }
        return true;
    }

    public static function parse(string $pdfPath): array
    {
        if (!self::available()) {
            return [];
        }
        @set_time_limit(180);
        self::$trace = [];
        @ini_set('memory_limit', '512M');

        // Folder kerja di dalam proyek (storage/app/ocr_tmp) + nama file relatif: aman dari masalah path Windows
        $base = rtrim(function_exists('storage_path') ? storage_path('app') : sys_get_temp_dir(), '\\/') . DIRECTORY_SEPARATOR . 'ocr_tmp';
        $dir  = $base . DIRECTORY_SEPARATOR . bin2hex(random_bytes(6));
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            self::note('gagal membuat folder kerja: ' . $dir);
            return [];
        }
        $items = [];

        try {
            if (!@copy($pdfPath, $dir . DIRECTORY_SEPARATOR . 'in.pdf')) {
                self::note('gagal menyalin PDF ke folder kerja');
                return [];
            }
            $p = new Process([self::bin('pdftoppm'), '-r', '300', '-f', '1', '-l', '5', '-png', 'in.pdf', 'pg'], $dir);
            $p->setTimeout(90);
            $p->run();
            self::note('pdftoppm exit=' . $p->getExitCode() . ($p->getErrorOutput() !== '' ? ' err=' . trim(mb_substr($p->getErrorOutput(), 0, 150)) : ''));

            $pages = glob($dir . DIRECTORY_SEPARATOR . 'pg*.png') ?: [];
            sort($pages);
            self::note('halaman gambar=' . count($pages));
            foreach ($pages as $png) {
                $items = array_merge($items, self::parsePage($png));
            }
        } catch (\Throwable $e) {
            self::note('error: ' . mb_substr($e->getMessage(), 0, 200));
        } finally {
            foreach (glob($dir . DIRECTORY_SEPARATOR . '*') ?: [] as $f) { @unlink($f); }
            @rmdir($dir);
        }

        return array_slice($items, 0, 300);
    }

    private static function words(string $png, int $psm = 6, int $offX = 0, int $offY = 0, bool $digits = false): array
    {
        $cmd = [self::bin('tesseract'), basename($png), 'stdout', '--psm', (string) $psm, '-l', 'eng'];
        if ($digits) { array_push($cmd, '-c', 'tessedit_char_whitelist=0123456789.,'); }
        $cmd[] = 'tsv';
        $p = new Process($cmd, dirname($png));
        $p->setTimeout(120);
        $p->run();
        if (!$p->isSuccessful()) {
            self::note('tesseract exit=' . $p->getExitCode() . ' err=' . trim(mb_substr($p->getErrorOutput(), 0, 150)));
        }
        $words = [];
        foreach (preg_split('/\R/', $p->getOutput()) as $i => $line) {
            if ($i === 0) { continue; }
            $c = explode("\t", $line);
            if (count($c) < 12 || (int) $c[0] !== 5) { continue; }
            $text = trim($c[11]);
            if ($text === '' || (float) $c[10] < 0) { continue; }
            $words[] = ['x' => (int) $c[6] + $offX, 'y' => (int) $c[7] + $offY, 'w' => (int) $c[8], 'h' => (int) $c[9], 'text' => $text];
        }
        return $words;
    }

    // Posisi garis (pusat) di sepanjang satu sumbu: $dark(i) -> true bila piksel gelap
    private static function mergeLines(array $hits, int $gap = 4): array
    {
        $lines = [];
        $start = null; $prev = null;
        foreach ($hits as $v) {
            if ($start === null) { $start = $prev = $v; continue; }
            if ($v - $prev <= $gap) { $prev = $v; continue; }
            $lines[] = (int) round(($start + $prev) / 2);
            $start = $prev = $v;
        }
        if ($start !== null) { $lines[] = (int) round(($start + $prev) / 2); }
        return $lines;
    }

    private static function grid(string $png): ?array
    {
        $im = @imagecreatefrompng($png);
        if (!$im) { self::note('GD gagal membuka PNG (memori/ekstensi png?)'); return null; }
        $w = imagesx($im); $h = imagesy($im);

        // Kecerahan piksel (0-255), koordinat di luar gambar dijepit ke tepi
        $g = function ($x, $y) use ($im, $w, $h) {
            $x = $x < 0 ? 0 : ($x >= $w ? $w - 1 : $x);
            $y = $y < 0 ? 0 : ($y >= $h ? $h - 1 : $y);
            $c = imagecolorat($im, $x, $y);
            return ((($c >> 16) & 255) + (($c >> 8) & 255) + ($c & 255)) / 3;
        };
        // Piksel "garis" = lebih gelap dari salah satu sisi sekitarnya (tahan terhadap garis tipis abu-abu
        // dan sel berwarna kuning/merah/biru, tidak bergantung pada ambang gelap mutlak)
        $hline = function ($x, $y) use ($g) { $v = $g($x, $y); return $v < 215 && ($v + 18 < $g($x, $y - 7) || $v + 18 < $g($x, $y + 7)); };
        $vline = function ($x, $y) use ($g) { $v = $g($x, $y); return $v < 215 && ($v + 18 < $g($x - 7, $y) || $v + 18 < $g($x + 7, $y)); };

        // Garis horizontal: baris piksel yang >50% lebar halaman berupa garis
        $hHits = [];
        for ($y = 0; $y < $h; $y++) {
            $q = 0; $qt = 0;
            for ($x = 0; $x < $w; $x += 40) { $qt++; if ($hline($x, $y)) { $q++; } }
            if ($q / $qt < 0.3) { continue; }
            $n = 0; $tot = 0;
            for ($x = 0; $x < $w; $x += 3) { $tot++; if ($hline($x, $y)) { $n++; } }
            if ($n / $tot > 0.5) { $hHits[] = $y; }
        }
        $rows = self::mergeLines($hHits);
        if (count($rows) < 4) { imagedestroy($im); return null; }

        // Rentang X tabel = sebaran garis pada salah satu garis horizontal
        $yRef = $rows[min(2, count($rows) - 1)];
        $x0 = null; $x1 = null;
        for ($x = 0; $x < $w; $x++) { if ($hline($x, $yRef)) { $x0 = $x0 ?? $x; $x1 = $x; } }
        if ($x0 === null) { imagedestroy($im); return null; }

        // Garis vertikal: diukur pada ~10 baris pertama tabel saja (kolom sama di seluruh tabel)
        $top = $rows[0]; $bottom = $rows[min(10, count($rows) - 1)];
        $vHits = [];
        for ($x = max(0, $x0 - 6); $x <= min($w - 1, $x1 + 6); $x++) {
            $n = 0; $tot = 0;
            for ($y = $top; $y <= $bottom; $y += 3) { $tot++; if ($vline($x, $y)) { $n++; } }
            if ($tot && $n / $tot >= 0.6) { $vHits[] = $x; }
        }
        $cols = self::mergeLines($vHits);
        if (count($cols) < 4) { imagedestroy($im); return null; }

        // Batas bawah tabel: band terakhir yang masih dilalui garis vertikal kolom ke-2 (kotak tanda tangan tidak)
        $probe = $cols[min(2, count($cols) - 1)];
        $last = 0;
        for ($i = 0; $i < count($rows) - 1; $i++) {
            $n = 0; $tot = 0;
            for ($y = $rows[$i] + 6; $y < $rows[$i + 1] - 5; $y += 2) {
                $tot++;
                if ($vline($probe, $y) || $vline($probe - 1, $y) || $vline($probe + 1, $y)) { $n++; }
            }
            if ($tot > 0 && $n / $tot > 0.8) { $last = $i + 1; } else { break; }
        }
        imagedestroy($im);
        if ($last < 2) { return null; }

        return ['rows' => array_slice($rows, 0, $last + 1), 'cols' => $cols];
    }

    // Luruskan halaman scan yang miring: bandingkan posisi garis horizontal di sisi kiri vs kanan tabel
    private static function deskew(string $png): void
    {
        $im = @imagecreatefrompng($png);
        if (!$im) { return; }
        $w = imagesx($im); $h = imagesy($im);
        $prof = function (int $xa, int $xb) use ($im, $h) {
            $p = array_fill(0, $h, 0);
            for ($y = 7; $y < $h - 7; $y++) {
                $n = 0;
                for ($x = $xa; $x < $xb; $x += 3) {
                    $c = imagecolorat($im, $x, $y);
                    $v = ((($c >> 16) & 255) + (($c >> 8) & 255) + ($c & 255)) / 3;
                    if ($v >= 215) { continue; }
                    $c1 = imagecolorat($im, $x, $y - 7); $c2 = imagecolorat($im, $x, $y + 7);
                    $u = ((($c1 >> 16) & 255) + (($c1 >> 8) & 255) + ($c1 & 255)) / 3;
                    $d = ((($c2 >> 16) & 255) + (($c2 >> 8) & 255) + ($c2 & 255)) / 3;
                    if ($v + 18 < $u || $v + 18 < $d) { $n++; }
                }
                $p[$y] = $n;
            }
            return $p;
        };
        $xl = (int) ($w * 0.14); $xr = (int) ($w * 0.72); $span = (int) ($w * 0.16);
        $L = $prof($xl, $xl + $span);
        $R = $prof($xr, $xr + $span);
        $best = 0; $bestScore = -1;
        for ($s = -45; $s <= 45; $s++) {
            $score = 0;
            for ($y = 60; $y < $h - 60; $y++) {
                if ($L[$y] > 8) { $score += $L[$y] * $R[$y + $s]; }
            }
            if ($score > $bestScore) { $bestScore = $score; $best = $s; }
        }
        if (abs($best) >= 6) {
            $angle = rad2deg(atan($best / ($xr - $xl)));
            $white = imagecolorallocate($im, 255, 255, 255);
            $rot = imagerotate($im, $angle, $white);
            if ($rot) {
                imagepng($rot, $png);
                imagedestroy($rot);
                self::note(sprintf('halaman diluruskan %.2f derajat', $angle));
            }
        }
        imagedestroy($im);
    }

    // Warna latar dominan sebuah sel [r,g,b] (null bila hanya teks/kosong)
    private static function cellBg($im, int $x0, int $y0, int $x1, int $y1): ?array
    {
        $bins = [];
        for ($y = $y0 + 8; $y < $y1 - 8; $y += 4) {
            for ($x = $x0 + 8; $x < $x1 - 8; $x += 4) {
                $c = imagecolorat($im, $x, $y);
                $r = ($c >> 16) & 255; $gg = ($c >> 8) & 255; $bb = $c & 255;
                if (($r + $gg + $bb) / 3 < 110) { continue; } // teks
                $k = ($r >> 5) . ($gg >> 5) . ($bb >> 5);
                $bins[$k] = ($bins[$k] ?? ['n' => 0, 'r' => 0, 'g' => 0, 'b' => 0]);
                $bins[$k]['n']++; $bins[$k]['r'] += $r; $bins[$k]['g'] += $gg; $bins[$k]['b'] += $bb;
            }
        }
        if (!$bins) { return null; }
        uasort($bins, fn($a, $b) => $b['n'] <=> $a['n']);
        $m = reset($bins);
        return [$m['r'] / $m['n'], $m['g'] / $m['n'], $m['b'] / $m['n']];
    }

    // Warna latar sebuah sel (penanda revisi BOM): white | yellow | red | blue | other
    private static function cellMark($im, int $x0, int $y0, int $x1, int $y1): string
    {
        $bg = self::cellBg($im, $x0, $y0, $x1, $y1);
        if (!$bg) { return 'white'; }
        [$r, $gg, $bb] = $bg;
        $max = max($r, $gg, $bb); $min = min($r, $gg, $bb);
        if ($max - $min < 38) { return 'white'; }
        $d = $max - $min;
        if ($max == $r)      { $hue = 60 * fmod((($gg - $bb) / $d), 6); }
        elseif ($max == $gg) { $hue = 60 * ((($bb - $r) / $d) + 2); }
        else                 { $hue = 60 * ((($r - $gg) / $d) + 4); }
        if ($hue < 0) { $hue += 360; }
        if ($hue >= 20 && $hue < 75)   { return 'yellow'; }   // kuning / oranye
        if ($hue < 20 || $hue >= 330)  { return 'red'; }      // merah / pink
        if ($hue >= 170 && $hue < 270) { return 'blue'; }
        return 'other';
    }

    // Sel berlatar warna (merah/biru/kuning) dibersihkan: piksel yang mirip warna latar diputihkan supaya teks terbaca OCR
    private static function whitenColoredCells($im, array $rows, array $cols): void
    {
        $white = imagecolorallocate($im, 255, 255, 255);
        for ($r = 0; $r < count($rows) - 1; $r++) {
            for ($c = 0; $c < count($cols) - 1; $c++) {
                $x0 = $cols[$c] + 4; $x1 = $cols[$c + 1] - 4; $y0 = $rows[$r] + 4; $y1 = $rows[$r + 1] - 4;
                if ($x1 - $x0 < 10 || $y1 - $y0 < 10) { continue; }
                $bg = self::cellBg($im, $cols[$c], $rows[$r], $cols[$c + 1], $rows[$r + 1]);
                if (!$bg || max($bg) - min($bg) < 38) { continue; }
                for ($y = $y0; $y < $y1; $y++) {
                    for ($x = $x0; $x < $x1; $x++) {
                        $p = imagecolorat($im, $x, $y);
                        $dr = (($p >> 16) & 255) - $bg[0]; $dg = (($p >> 8) & 255) - $bg[1]; $db = ($p & 255) - $bg[2];
                        if ($dr * $dr + $dg * $dg + $db * $db < 5200) { imagesetpixel($im, $x, $y, $white); }
                    }
                }
            }
        }
    }

    private static function bucket(array $lines, float $v): int
    {
        for ($i = 0; $i < count($lines) - 1; $i++) {
            if ($v >= $lines[$i] && $v < $lines[$i + 1]) { return $i; }
        }
        return -1;
    }

    private static function cellText(array $words): string
    {
        usort($words, fn($a, $b) => [intdiv($a['y'] + $a['h'] / 2, 14), $a['x']] <=> [intdiv($b['y'] + $b['h'] / 2, 14), $b['x']]);
        // urutkan per baris teks (toleransi tinggi huruf) lalu kiri->kanan
        $lines = [];
        foreach ($words as $wd) {
            $cy = $wd['y'] + $wd['h'] / 2;
            $placed = false;
            foreach ($lines as &$ln) {
                if (abs($ln['cy'] - $cy) <= max(10, $wd['h'] * 0.6)) { $ln['w'][] = $wd; $placed = true; break; }
            }
            unset($ln);
            if (!$placed) { $lines[] = ['cy' => $cy, 'w' => [$wd]]; }
        }
        usort($lines, fn($a, $b) => $a['cy'] <=> $b['cy']);
        $out = [];
        foreach ($lines as $ln) {
            usort($ln['w'], fn($a, $b) => $a['x'] <=> $b['x']);
            $out[] = implode(' ', array_column($ln['w'], 'text'));
        }
        return trim(preg_replace('/\s+/', ' ', implode(' ', $out)));
    }

    private static function fieldOf(string $header): ?string
    {
        $h = strtolower($header);
        if (preg_match('/spec/', $h)) { return 'spec'; }
        if (preg_match('/descr|uraian|keterangan penggunaan|used/', $h)) { return 'desc'; }
        if (preg_match('/qty|quantity|jumlah|jml/', $h)) { return 'qty'; }
        if (preg_match('/uom|satuan|unit/', $h)) { return 'uom'; }
        if (preg_match('/weight|berat/', $h)) { return 'weight'; }
        if (preg_match('/remark|ket/', $h)) { return 'remark'; }
        if (preg_match('/material|nama|item|barang/', $h)) { return 'name'; }
        if (preg_match('/^no/', $h)) { return 'no'; }
        return null;
    }

    // OCR per kolom: garis tabel dihapus dari gambar, tiap kolom dipotong jadi satu strip lalu dibaca terpisah
    // (jauh lebih akurat untuk angka satu digit & teks pada sel berwarna daripada membaca satu halaman penuh).
    private static function columnWords(string $png, array $rows, array $cols, ?int $onlyCol = null, bool $digits = false): array
    {
        $im = @imagecreatefrompng($png);
        if (!$im) { return []; }
        self::whitenColoredCells($im, $rows, $cols);
        $white = imagecolorallocate($im, 255, 255, 255);
        $xMin = $cols[0]; $xMax = $cols[count($cols) - 1];
        $yMin = $rows[0]; $yMax = $rows[count($rows) - 1];
        foreach ($rows as $y) { imagefilledrectangle($im, $xMin - 6, $y - 5, $xMax + 6, $y + 5, $white); }
        foreach ($cols as $x) { imagefilledrectangle($im, $x - 5, $yMin - 6, $x + 5, $yMax + 6, $white); }

        $all = [];
        for ($c = 0; $c < count($cols) - 1; $c++) {
            if ($onlyCol !== null && $c !== $onlyCol) { continue; }
            $x = $cols[$c] + 6; $w = $cols[$c + 1] - $cols[$c] - 12;
            if ($w < 20) { continue; }
            $strip = imagecrop($im, ['x' => $x, 'y' => $yMin, 'width' => $w, 'height' => $yMax - $yMin]);
            if (!$strip) { continue; }
            // Sel berwarna (kuning/oranye/pink) dijadikan hitam-putih tegas supaya teks tetap terbaca
            imagefilter($strip, IMG_FILTER_GRAYSCALE);
            imagefilter($strip, IMG_FILTER_CONTRAST, -90);
            // beri bingkai putih supaya tesseract tidak memotong huruf di tepi
            $padded = imagecreatetruecolor($w + 40, $yMax - $yMin + 40);
            imagefill($padded, 0, 0, imagecolorallocate($padded, 255, 255, 255));
            imagecopy($padded, $strip, 20, 20, 0, 0, $w, $yMax - $yMin);
            $file = $png . "_c{$c}.png";
            imagepng($padded, $file);
            imagedestroy($strip); imagedestroy($padded);
            // kolom sempit (No, Qty, UoM) lebih akurat dengan psm 6 + whitelist ringan tidak dipakai; cukup psm 6
            foreach (self::words($file, 6, $x - 20, $yMin - 20, $digits) as $wd) { $all[] = $wd; }
            @unlink($file);
        }
        imagedestroy($im);
        return $all;
    }

    private static function parsePage(string $png): array
    {
        self::deskew($png);
        $grid = self::grid($png);
        if (!$grid) { self::note('garis tabel tidak terdeteksi'); return []; }

        $rows = $grid['rows']; $cols = $grid['cols'];
        self::note('grid baris=' . count($rows) . ' kolom=' . (count($cols) - 1));
        $words = self::columnWords($png, $rows, $cols);
        self::note('kata terbaca=' . count($words));
        if (!$words) { return []; }

        // sel[baris][kolom] = kata-kata
        $cells = [];
        foreach ($words as $wd) {
            $r = self::bucket($rows, $wd['y'] + $wd['h'] / 2);
            $c = self::bucket($cols, $wd['x'] + $wd['w'] / 2);
            if ($r < 0 || $c < 0) { continue; }
            $cells[$r][$c][] = $wd;
        }

        // Baris header = baris yang punya kata header (Material / Qty / UoM / Description)
        $fieldByCol = []; $headerRow = null;
        foreach ($cells as $r => $byCol) {
            $mapped = [];
            foreach ($byCol as $c => $ws) {
                $f = self::fieldOf(self::cellText($ws));
                if ($f) { $mapped[$c] = $f; }
            }
            if (count(array_intersect($mapped, ['name', 'qty', 'uom'])) >= 2) {
                $fieldByCol = $mapped; $headerRow = $r; break;
            }
        }
        if ($headerRow === null) { self::note('header tabel (Material/Qty/UoM) tidak dikenali'); return []; }

        // Header "Qty" sering tidak terbaca: kolom tanpa judul tepat di kiri kolom UoM dianggap Qty
        if (!in_array('qty', $fieldByCol, true)) {
            $uomCol = array_search('uom', $fieldByCol, true);
            if ($uomCol !== false && $uomCol > 0 && !isset($fieldByCol[$uomCol - 1])) {
                $fieldByCol[$uomCol - 1] = 'qty';
            }
        }

        // Kolom Qty dibaca ulang khusus angka (whitelist digit) -- jauh lebih akurat untuk angka satu digit
        $qtyCol = array_search('qty', $fieldByCol, true);
        if ($qtyCol !== false) {
            $digitWords = self::columnWords($png, $rows, $cols, (int) $qtyCol, true);
            if ($digitWords) {
                unset($cells[$headerRow][$qtyCol]);
                foreach ($cells as $rr => &$byCol) { if ($rr > $headerRow) { unset($byCol[$qtyCol]); } }
                unset($byCol);
                foreach ($digitWords as $wd) {
                    $rr = self::bucket($rows, $wd['y'] + $wd['h'] / 2);
                    if ($rr > $headerRow) { $cells[$rr][$qtyCol][] = $wd; }
                }
            }
        }

        $colOf = [];
        foreach ($fieldByCol as $ci => $f) { $colOf[$f] = $ci; } // kolom terakhir untuk field kembar (UoM)
        $im = @imagecreatefrompng($png);
        $markOf = function (int $r, ?int $ci) use ($im, $rows, $cols) {
            if (!$im || $ci === null) { return null; }
            return self::cellMark($im, $cols[$ci], $rows[$r], $cols[$ci + 1], $rows[$r + 1]);
        };

        $items = [];
        for ($r = $headerRow + 1; $r < count($rows) - 1; $r++) {
            $rec = [];
            foreach ($fieldByCol as $c => $f) {
                $rec[$f] = isset($cells[$r][$c]) ? self::cellText($cells[$r][$c]) : '';
            }
            // Baris data valid = nomor urut angka di kolom No (menyaring kotak tanda tangan dll)
            $no = preg_replace('/[^0-9]/', '', $rec['no'] ?? '');
            $hasNoCol = in_array('no', $fieldByCol, true);
            if (($hasNoCol && $no === '') || ($rec['name'] ?? '') === '') { continue; }

            $qty = (float) str_replace(',', '.', preg_replace('/[^0-9.,]/', '', strtr($rec['qty'] ?? '', ['O' => '0', 'o' => '0', 'l' => '1', 'I' => '1'])));
            if (preg_replace('/\s+/', '', (string) ($rec['qty'] ?? '')) === '') { continue; }
            $qty = max(0, $qty); // qty 0 tetap dibaca: pada BOM revisi berarti material dibatalkan

            // buang sisa garis tabel yang terbaca sebagai simbol di awal/akhir teks
            $clean = fn($s) => trim(preg_replace('/^[^\p{L}\p{N}]+|[|~_]+$/u', '', (string) $s));
            $rec['name'] = $clean($rec['name']);
            $rec['spec'] = strtr($clean($rec['spec'] ?? ''), ['$' => 'S']);
            // salah baca umum grade baja: S400 / 3400 / 5S400 / 400 -> SS400
            $rec['spec'] = preg_replace('/^(?:[S53]?S?|)(400|304|316|490|540)$/i', 'SS$1', trim($rec['spec']));
            if ($rec['name'] === '') { continue; }

            $items[] = [
                'name'     => mb_substr($rec['name'], 0, 150),
                'spec'     => mb_substr($rec['spec'], 0, 150),
                'uom'      => PdfMaterialParser::normalizeUom($rec['uom'] ?? ''),
                'quantity' => $qty,
                'weight'   => null,
                'mark'     => ['row' => $markOf($r, $colOf['name'] ?? null), 'qty' => $markOf($r, $colOf['qty'] ?? null), 'spec' => $markOf($r, $colOf['spec'] ?? null)],
                'note'     => trim(($rec['desc'] ?? '') . (($rec['remark'] ?? '') !== '' ? ' | ' . $rec['remark'] : ''), ' |'),
            ];
        }

        if ($im) { imagedestroy($im); }

        return $items;
    }
}