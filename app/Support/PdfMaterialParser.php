<?php

namespace App\Support;

use Symfony\Component\Process\Process;

/**
 * Membaca daftar material (nama, spec, UOM, qty, weight) dari PDF BERTEKS (bukan hasil scan gambar).
 * Teks diambil dengan smalot/pdfparser (jika terpasang) atau perintah `pdftotext`, lalu tiap baris
 * dianalisis dengan heuristik: baris yang punya "angka + UOM" (mis. 12 Pcs) dianggap satu material.
 * Hasilnya hanya PRATINJAU -- pengguna memeriksa/mengubahnya sebelum diimpor.
 */
class PdfMaterialParser
{
    private const UOM = [
        'pcs' => 'Pcs', 'pc' => 'Pcs', 'btg' => 'Btg', 'lbr' => 'Lbr', 'kg' => 'Kg', 'ton' => 'Ton',
        'm' => 'M', 'mtr' => 'M', 'set' => 'Set', 'unit' => 'Unit', 'lot' => 'Lot', 'ea' => 'Ea',
        'roll' => 'Roll', 'ltr' => 'Ltr', 'sht' => 'Sht',
        'lmbr' => 'Lbr', 'lembar' => 'Lbr', 'batang' => 'Btg', 'bh' => 'Pcs', 'buah' => 'Pcs',
    ];

    // Samakan penulisan UOM (termasuk salah baca OCR yang umum) ke bentuk baku
    public static function normalizeUom(string $raw): string
    {
        $k = strtolower(preg_replace('/[^A-Za-z]/', '', $raw));
        $k = strtr($k, ['0' => 'o']);
        if (isset(self::UOM[$k])) {
            return self::UOM[$k];
        }
        // salah baca ringan: cari UOM baku dengan jarak edit <= 1 (mis. "Pes", "Btq")
        foreach (self::UOM as $key => $label) {
            if (strlen($key) >= 3 && strlen($k) >= 3 && levenshtein($k, $key) <= 1) {
                return $label;
            }
        }
        return $raw === '' ? '' : ucfirst(strtolower(trim($raw)));
    }

    public static function extractText(string $path): string
    {
        if (class_exists(\Smalot\PdfParser\Parser::class)) {
            try {
                $text = (new \Smalot\PdfParser\Parser())->parseFile($path)->getText();
                if (trim($text) !== '') {
                    return $text;
                }
            } catch (\Throwable $e) {
                // lanjut ke pdftotext
            }
        }

        try {
            $process = new Process([PdfTableOcr::bin('pdftotext'), '-layout', '-enc', 'UTF-8', $path, '-']);
            $process->setTimeout(30);
            $process->run();
            if ($process->isSuccessful()) {
                return $process->getOutput();
            }
        } catch (\Throwable $e) {
            // tidak ada pdftotext
        }

        return '';
    }

    public static function parse(string $text): array
    {
        $uomPattern = implode('|', array_map('preg_quote', array_keys(self::UOM)));
        $items = [];

        foreach (preg_split('/\R/u', $text) as $line) {
            $line = trim(preg_replace('/\t+/', '  ', $line));
            if ($line === '' || mb_strlen($line) < 4) {
                continue;
            }

            // Buang nomor urut di depan: "1", "1.", "1)"
            $line = preg_replace('/^\d{1,3}[.)]?\s+(?=\D)/u', '', $line);

            $cells = preg_split('/\s{2,}/u', $line);

            $qty = null; $uom = null; $weight = null; $spec = null; $nameParts = [];

            foreach ($cells as $cell) {
                $cell = trim($cell);
                if ($cell === '') { continue; }

                // "12 Pcs", "12,5 kg"
                if (preg_match('/^(\d+(?:[.,]\d+)?)\s*(' . $uomPattern . ')\.?$/iu', $cell, $m)) {
                    $value = (float) str_replace(',', '.', $m[1]);
                    $u = self::UOM[strtolower($m[2])];
                    if ($qty === null) { $qty = $value; $uom = $u; }
                    elseif ($u === 'Kg' && $weight === null) { $weight = $value; }
                    continue;
                }
                // angka saja lalu UOM di sel berikutnya
                if ($qty === null && preg_match('/^\d+(?:[.,]\d+)?$/', $cell) && $uom === null) {
                    $pendingQty = (float) str_replace(',', '.', $cell);
                    $qty = $pendingQty; $uom = '';
                    continue;
                }
                if ($uom === '' && preg_match('/^(' . $uomPattern . ')\.?$/iu', $cell, $m)) {
                    $uom = self::UOM[strtolower($m[1])];
                    continue;
                }
                if ($spec === null && self::looksLikeSpec($cell)) {
                    $spec = $cell;
                    continue;
                }
                if (preg_match('/[A-Za-z]{3,}/', $cell)) {
                    $nameParts[] = $cell;
                }
            }

            // Satu sel panjang tanpa pemisah lebar: coba ambil "qty uom" di akhir baris
            if (($qty === null || $uom === '' || $uom === null) && count($cells) === 1
                && preg_match('/^(.*?)\s+(\d+(?:[.,]\d+)?)\s*(' . $uomPattern . ')\.?\b/iu', $line, $m)) {
                $nameParts = [trim($m[1])];
                $qty = (float) str_replace(',', '.', $m[2]);
                $uom = self::UOM[strtolower($m[3])];
            }

            if ($qty === null || $qty <= 0 || $uom === null || $uom === '' || empty($nameParts)) {
                continue;
            }

            $name = trim(preg_replace('/\s+/', ' ', $nameParts[0]));
            // Spec yang menempel di nama: "Plate SS400 12 mm" -> biarkan di nama bila sel spec tidak ada
            if ($spec === null && isset($nameParts[1])) {
                $spec = trim($nameParts[1]);
            }

            $items[] = [
                'name'     => mb_substr($name, 0, 150),
                'spec'     => $spec !== null ? mb_substr($spec, 0, 150) : '',
                'uom'      => $uom,
                'quantity' => $qty,
                'weight'   => $weight,
            ];
        }

        return array_slice($items, 0, 300);
    }

    private static function looksLikeSpec(string $cell): bool
    {
        return (bool) preg_match('/\b(SS\s?\d{3}|SUS\s?\d+|A\d{2,3}|ASTM|JIS|SNI|API|SA-?\d+|AISI|\d+(?:[.,]\d+)?\s?[xX×]\s?\d+)/u', $cell);
    }
}