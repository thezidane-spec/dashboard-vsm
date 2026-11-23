<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Membandingkan hasil scan BOM (revisi baru) dengan material yang sudah tercatat di proyek.
 *
 * Warna (mengikuti penanda di BOM):
 *  - white  : material sama persis -> tidak ada perubahan
 *  - yellow : material BARU, atau qty BERTAMBAH           -> tambahkan / naikkan qty
 *  - blue   : perubahan qty saja (berkurang / ditandai biru di BOM) -> ubah qty
 *  - red    : material dikurangi/dibatalkan (qty 0), atau kriteria berubah (spec / UOM / ditandai merah),
 *             atau material lama yang tidak ada lagi di BOM baru
 */
class BomRevision
{
    public static function norm(string $s): string
    {
        $s = strtolower(strtr($s, ['°' => '', '”' => '', '“' => '', '’' => '', '‘' => '', '×' => 'x']));
        return preg_replace('/[^a-z0-9]/', '', $s);
    }

    // Nama dianggap sama bila identik setelah dinormalkan, atau beda tipis akibat salah baca OCR
    public static function nameSame(string $a, string $b): bool
    {
        $a = self::norm($a); $b = self::norm($b);
        if ($a === $b) { return true; }
        // Ukuran / angka harus persis sama: "Pipe 2\" SCH 40" tidak boleh dianggap sama dengan "Pipe 6\" SCH 40"
        if (preg_replace('/\D/', '', $a) !== preg_replace('/\D/', '', $b)) { return false; }
        $len = max(strlen($a), strlen($b));
        if ($len < 8 || abs(strlen($a) - strlen($b)) > 2) { return false; }
        return levenshtein($a, $b) <= max(1, (int) floor($len * 0.08));
    }

    public static function specSame(?string $a, ?string $b): bool
    {
        $a = self::specNorm((string) $a); $b = self::specNorm((string) $b);
        return $a === $b;
    }

    // "S400" / "3400" / "$S400" hasil OCR -> "ss400"
    public static function specNorm(string $s): string
    {
        $n = self::norm($s);
        if (preg_match('/^(?:s|5|3|\$)?s?(400|304|316|490|540)$/', $n, $m)) { return 'ss' . $m[1]; }
        return $n;
    }

    public static function uomSame(?string $a, ?string $b): bool
    {
        return strtolower(PdfMaterialParser::normalizeUom((string) $a)) === strtolower(PdfMaterialParser::normalizeUom((string) $b));
    }

    /**
     * @param Collection $existing  MaterialItem (dengan arrivals) pada kategori yang dipilih
     * @param array      $scanned   hasil PdfTableOcr (name, spec, uom, quantity, weight, note, mark)
     * @return array{rows: array<int,array>, summary: array<string,int>}
     */
    public static function compare(Collection $existing, array $scanned): array
    {
        $taken = [];
        $rows  = [];
        $mk    = fn($row, $key) => $row['mark'][$key] ?? null;

        foreach ($scanned as $i => $s) {
            // cari material lama yang cocok (nama sama / mirip), utamakan yang spec-nya juga sama
            $match = null;
            foreach ($existing as $e) {
                if (isset($taken[$e->id]) || !self::nameSame($s['name'], $e->name)) { continue; }
                if ($match === null || (self::norm($e->name) === self::norm($s['name']) && self::specSame($s['spec'] ?? '', $e->spec))) {
                    $match = $e;
                }
            }
            if ($match) { $taken[$match->id] = true; }

            $qty   = (float) $s['quantity'];
            $row   = [
                'index'   => $i,
                'name'    => $s['name'],
                'spec'    => $s['spec'] ?? '',
                'uom'     => $s['uom'] ?? '',
                'quantity' => $qty,
                'weight'  => $s['weight'] ?? null,
                'note'    => $s['note'] ?? '',
                'item_id' => $match->id ?? null,
                'old'     => $match ? ['name' => $match->name, 'spec' => $match->spec, 'uom' => $match->uom, 'quantity' => (float) $match->quantity, 'actual' => $match->actual, 'arrivals' => $match->arrivals->count()] : null,
                'changes' => [],
                'warning' => null,
            ];

            if (!$match) {
                if ($qty <= 0) {
                    $row += ['status' => 'skip', 'color' => 'red', 'label' => 'Qty 0, tidak ada di sistem', 'action' => 'none', 'checked' => false];
                } else {
                    $row += ['status' => 'add', 'color' => 'yellow', 'label' => 'Material baru', 'action' => 'add', 'checked' => true];
                }
                $rows[] = $row;
                continue;
            }

            $row['name'] = $match->name; // nama lama dipertahankan (hindari menimpa dengan salah baca OCR)
            $oldQty = (float) $match->quantity;

            if ($qty <= 0) {
                $row += ['status' => 'removed', 'color' => 'red', 'label' => 'Dikurangi / dibatalkan (qty 0)', 'action' => 'delete', 'checked' => true];
                if ($match->arrivals->count() > 0) {
                    $row['warning'] = 'Sudah ada ' . $match->arrivals->count() . ' catatan kedatangan (total ' . MaterialReport::number($match->actual) . '); ikut terhapus.';
                }
                $rows[] = $row;
                continue;
            }

            $specDiff = !self::specSame($s['spec'] ?? '', $match->spec);
            $uomDiff  = ($s['uom'] ?? '') !== '' && !self::uomSame($s['uom'], $match->uom);
            $redMark  = $mk($s, 'row') === 'red' || $mk($s, 'spec') === 'red';
            $qtyDiff  = abs($qty - $oldQty) > 0.0001;

            if ($specDiff || $uomDiff || $redMark) {
                if ($specDiff) { $row['changes'][] = 'spec: ' . ($match->spec ?: '-') . ' → ' . ($s['spec'] ?: '-'); }
                if ($uomDiff)  { $row['changes'][] = 'UOM: ' . $match->uom . ' → ' . $s['uom']; }
                if ($qtyDiff)  { $row['changes'][] = 'qty: ' . MaterialReport::number($oldQty) . ' → ' . MaterialReport::number($qty); }
                $real = $specDiff || $uomDiff;
                $row += [
                    'status'  => 'changed', 'color' => 'red',
                    'label'   => $real ? 'Kriteria berubah' : 'Ditandai merah di BOM (periksa)',
                    'action'  => ($real || $qtyDiff) ? 'update' : 'none',
                    'checked' => $real,
                ];
                $rows[] = $row;
                continue;
            }

            if ($qtyDiff) {
                $row['changes'][] = 'qty: ' . MaterialReport::number($oldQty) . ' → ' . MaterialReport::number($qty);
                $m = [$mk($s, 'qty'), $mk($s, 'row')];
                if (in_array('blue', $m, true))        { $color = 'blue'; }
                elseif (in_array('yellow', $m, true))  { $color = 'yellow'; }
                else                                   { $color = $qty > $oldQty ? 'yellow' : 'blue'; }
                $row += [
                    'status' => 'qty', 'color' => $color,
                    'label'  => $color === 'yellow' ? 'Qty bertambah' : 'Qty berubah',
                    'action' => 'update', 'checked' => true,
                ];
                $rows[] = $row;
                continue;
            }

            $row += ['status' => 'same', 'color' => 'white', 'label' => 'Sama', 'action' => 'none', 'checked' => false];
            $rows[] = $row;
        }

        // material lama yang tidak muncul lagi di BOM baru
        foreach ($existing as $e) {
            if (isset($taken[$e->id])) { continue; }
            $rows[] = [
                'index' => null, 'name' => $e->name, 'spec' => $e->spec, 'uom' => $e->uom, 'quantity' => (float) $e->quantity,
                'weight' => $e->weight, 'note' => '', 'item_id' => $e->id,
                'old' => ['name' => $e->name, 'spec' => $e->spec, 'uom' => $e->uom, 'quantity' => (float) $e->quantity, 'actual' => $e->actual, 'arrivals' => $e->arrivals->count()],
                'changes' => [], 'status' => 'missing', 'color' => 'red', 'label' => 'Tidak ada di BOM baru',
                'action' => 'delete', 'checked' => false,
                'warning' => $e->arrivals->count() > 0 ? 'Sudah ada ' . $e->arrivals->count() . ' catatan kedatangan; ikut terhapus.' : null,
            ];
        }

        $summary = ['white' => 0, 'yellow' => 0, 'blue' => 0, 'red' => 0];
        foreach ($rows as $r) { $summary[$r['color']]++; }

        return ['rows' => $rows, 'summary' => $summary];
    }
}