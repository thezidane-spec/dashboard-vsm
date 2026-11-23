<?php

namespace App\Support;

use ZipArchive;

/**
 * Pembaca .xlsx ringan tanpa library tambahan (ZipArchive + SimpleXML).
 * Mengembalikan tiap sheet sebagai [nomorBaris => [indeksKolom(0..) => nilai]].
 * Sel bertanggal otomatis diubah ke 'Y-m-d'.
 */
class XlsxReader
{
    private ZipArchive $zip;
    private array $shared = [];
    private array $dateStyle = [];

    public function __construct(string $path)
    {
        $this->zip = new ZipArchive();
        if ($this->zip->open($path) !== true) {
            throw new \RuntimeException('File bukan .xlsx yang valid.');
        }
        $this->loadShared();
        $this->loadStyles();
    }

    private function xml(string $name): ?\SimpleXMLElement
    {
        $raw = $this->zip->getFromName($name);
        if ($raw === false) { return null; }
        $x = @simplexml_load_string($raw);
        return $x ?: null;
    }

    private function loadShared(): void
    {
        $x = $this->xml('xl/sharedStrings.xml');
        if (!$x) { return; }
        foreach ($x->si as $si) {
            $text = '';
            if (isset($si->t)) { $text .= (string) $si->t; }
            foreach ($si->r as $r) { $text .= (string) $r->t; }
            $this->shared[] = $text;
        }
    }

    private function loadStyles(): void
    {
        $x = $this->xml('xl/styles.xml');
        if (!$x) { return; }
        $custom = [];
        if (isset($x->numFmts)) {
            foreach ($x->numFmts->numFmt as $nf) {
                $custom[(int) $nf['numFmtId']] = (string) $nf['formatCode'];
            }
        }
        $i = 0;
        if (isset($x->cellXfs)) {
            foreach ($x->cellXfs->xf as $xf) {
                $id = (int) $xf['numFmtId'];
                $isDate = in_array($id, [14, 15, 16, 17, 18, 19, 20, 21, 22, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 45, 46, 47, 50, 51, 52, 53, 54, 55, 56, 57, 58], true);
                if (!$isDate && isset($custom[$id])) {
                    $code = preg_replace('/"[^"]*"|\[[^\]]*\]|\\\\./', '', $custom[$id]);
                    $isDate = (bool) preg_match('/[dmy]/i', $code) && !preg_match('/[#0]/', $code);
                }
                $this->dateStyle[$i++] = $isDate;
            }
        }
    }

    /** @return array<int, array{name:string, path:string}> */
    public function sheets(): array
    {
        $wb = $this->xml('xl/workbook.xml');
        $rels = $this->xml('xl/_rels/workbook.xml.rels');
        if (!$wb || !$rels) { return []; }
        $map = [];
        foreach ($rels->Relationship as $rel) {
            $t = (string) $rel['Target'];
            $map[(string) $rel['Id']] = ltrim(str_starts_with($t, '/') ? $t : 'xl/' . $t, '/');
        }
        $out = [];
        foreach ($wb->sheets->sheet as $sh) {
            $rid = (string) $sh->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
            if (isset($map[$rid])) {
                $out[] = ['name' => (string) $sh['name'], 'path' => $map[$rid]];
            }
        }
        return $out;
    }

    private static function colIndex(string $ref): int
    {
        preg_match('/^[A-Z]+/', $ref, $m);
        $n = 0;
        foreach (str_split($m[0] ?? 'A') as $ch) { $n = $n * 26 + (ord($ch) - 64); }
        return $n - 1;
    }

    public function rows(string $sheetPath): array
    {
        $x = $this->xml($sheetPath);
        $rows = [];
        if (!$x || !isset($x->sheetData)) { return $rows; }
        foreach ($x->sheetData->row as $row) {
            $r = (int) $row['r'];
            foreach ($row->c as $c) {
                $col  = self::colIndex((string) $c['r']);
                $type = (string) $c['t'];
                $val  = isset($c->v) ? (string) $c->v : null;
                if ($type === 's') {
                    $val = $this->shared[(int) $val] ?? '';
                } elseif ($type === 'inlineStr') {
                    $val = (string) ($c->is->t ?? '');
                } elseif ($val !== null && $type !== 'str' && $type !== 'b' && $type !== 'e' && is_numeric($val)) {
                    if (!empty($this->dateStyle[(int) $c['s']]) && (float) $val > 0) {
                        $val = (new \DateTime('1899-12-30'))->modify('+' . (int) floor((float) $val) . ' days')->format('Y-m-d');
                    }
                }
                if ($val !== null && trim($val) !== '') {
                    $rows[$r][$col] = trim($val);
                }
            }
        }
        ksort($rows);
        return $rows;
    }

    public function close(): void { $this->zip->close(); }
}