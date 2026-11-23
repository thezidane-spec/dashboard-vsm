<?php

namespace App\Support;

use App\Models\MaterialRadar;
use App\Models\MaterialRadarItem;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/** Mengubah Google Spreadsheet / file .xlsx "Material Radar List" (banyak sheet) menjadi data di database. */
class MaterialRadarImporter
{
    private const LABELS = [
        'project_no'      => '/^project\s*no/i',
        'project_name'    => '/^project\s*name/i',
        'customer'        => '/^customer/i',
        'pic_engineering' => '/^pic\s*engineering/i',
        'pic_ppic'        => '/^pic\s*ppic/i',
        'pic_warehouse'   => '/^pic\s*warehouse/i',
        'pic_procurement' => '/^pic\s*procurement/i',
    ];

    /** Unduh file .xlsx dari link Google Spreadsheet (harus bisa diakses "siapa saja yang memiliki link"). */
    public static function download(string $url): string
    {
        if (!preg_match('~/spreadsheets/d/([a-zA-Z0-9_-]+)~', $url, $m)) {
            throw new \RuntimeException('Link bukan link Google Spreadsheet.');
        }
        $resp = Http::timeout(90)->withOptions(['allow_redirects' => true])
            ->get("https://docs.google.com/spreadsheets/d/{$m[1]}/export", ['format' => 'xlsx']);
        $body = $resp->body();
        if (!$resp->successful() || substr($body, 0, 2) !== 'PK') {
            throw new \RuntimeException('Spreadsheet tidak bisa diunduh. Pastikan akses dibagikan "Siapa saja yang memiliki link" (Viewer), atau unggah file .xlsx-nya.');
        }
        $dir = storage_path('app/radar_tmp');
        if (!is_dir($dir)) { mkdir($dir, 0775, true); }
        $path = $dir . '/' . uniqid('radar_', true) . '.xlsx';
        file_put_contents($path, $body);
        return $path;
    }

    /**
     * @return array{created:int, updated:int, skipped:array<int,string>, items:int}
     */
    public static function import(string $xlsxPath, ?string $sourceUrl, bool $overwrite, bool $autoAdd = false): array
    {
        $reader = new XlsxReader($xlsxPath);
        $result = ['created' => 0, 'updated' => 0, 'skipped' => [], 'items' => 0, 'warnings' => [], 'changes' => []];

        foreach ($reader->sheets() as $sheet) {
            if (strcasecmp(trim($sheet['name']), 'Template') === 0) { continue; }
            $parsed = self::parseSheet($reader->rows($sheet['path']));
            if (!$parsed || (empty($parsed['meta']['project_no']) && empty($parsed['meta']['project_name']))) {
                $result['skipped'][] = $sheet['name'] . ' (bukan format Material Radar List)';
                continue;
            }
            $meta = $parsed['meta'] + ['sheet_name' => $sheet['name'], 'source_url' => $sourceUrl];
            if (empty($parsed['columns']['required_date'])) {
                $result['warnings'][] = $sheet['name'] . ': kolom "Required Date" tidak ditemukan di judul kolom';
            }
            foreach ($parsed['warnings'] as $field => $vals) {
                if ($field === 'description') {
                    $result['warnings'][] = $sheet['name'] . ': ' . count($vals) . ' baris terisi tetapi kolom Material Description kosong (' . implode(', ', array_slice($vals, 0, 5)) . ')';
                    continue;
                }
                $result['warnings'][] = $sheet['name'] . ': ' . count($vals) . ' nilai kolom ' . $field . ' bukan tanggal yang dikenali (mis. "' . implode('", "', array_slice(array_unique($vals), 0, 3)) . '")';
            }

            $existing = MaterialRadar::query()
                ->when(!empty($meta['project_no']), fn($q) => $q->where('project_no', $meta['project_no']), fn($q) => $q->where('sheet_name', $sheet['name']))
                ->first();

            if ($existing && !$overwrite) {
                $result['skipped'][] = $sheet['name'] . ' (sudah ada)';
                continue;
            }

            DB::transaction(function () use ($existing, $meta, $parsed, $autoAdd, &$result) {
                $meta['project_id'] = !empty($meta['project_no'])
                    ? Project::where('project_code', $meta['project_no'])->value('id') : null;
                $relink = [];
                $oldNames = $existing ? $existing->items()->pluck('description')->all() : [];
                // Material Code yang sudah terisi di aplikasi tidak hilang saat update: dipakai lagi bila sheet baru kosong (dicocokkan lewat nama + spec)
                $oldCodes = $existing ? $existing->items()->whereNotNull('material_code')->get(['description', 'spec', 'material_code'])->all() : [];
                $oldCount = $existing ? $existing->items()->count() : 0;
                if ($existing) {
                    // simpan tautan Raw Material -> baris radar lama, supaya bisa dipasang lagi ke baris baru (dicocokkan lewat nama)
                    foreach (\App\Models\MaterialItem::whereIn('radar_item_id', $existing->items()->pluck('id'))->get() as $m) {
                        $relink[] = ['material' => $m, 'old' => $existing->items()->find($m->radar_item_id)];
                    }
                    $existing->update($meta);
                    $existing->items()->delete();
                    $radar = $existing;
                    $result['updated']++;
                } else {
                    $radar = MaterialRadar::create($meta);
                    $result['created']++;
                }
                foreach ($parsed['items'] as $i => $item) {
                    if (empty($item['material_code'])) {
                        foreach ($oldCodes as $oc) {
                            if (BomRevision::nameSame((string) $item['description'], (string) $oc->description) && BomRevision::specSame($item['spec'], $oc->spec)) { $item['material_code'] = $oc->material_code; break; }
                        }
                    }
                    $radar->items()->create($item + ['sort' => $i + 1]);
                }
                $newItems = $radar->items()->get();
                $used = [];
                foreach ($relink as $rl) {
                    $m = $rl['material']; $old = $rl['old'];
                    $match = $old ? $newItems->first(fn($n) => !isset($used[$n->id]) && BomRevision::nameSame((string) $n->description, (string) $old->description) && BomRevision::specSame($n->spec, $old->spec))
                        ?? $newItems->first(fn($n) => !isset($used[$n->id]) && BomRevision::nameSame((string) $n->description, (string) $old->description)) : null;
                    if ($match) {
                        $used[$match->id] = true;
                        \App\Models\MaterialItem::whereKey($m->id)->update(['radar_item_id' => $match->id]);
                        MaterialRadarSync::pull($match);   // tanggal diperlukan & kedatangan dari sheet terbaru
                        $result['relinked'] = ($result['relinked'] ?? 0) + 1;
                    } else {
                        \App\Models\MaterialItem::whereKey($m->id)->update(['radar_item_id' => null, 'radar_full_sync' => true]);
                        $result['unlinked'] = ($result['unlinked'] ?? 0) + 1;
                    }
                }
                // Update List: baris yang baru muncul di sheet otomatis masuk ke Raw Material proyek yang tertaut
                if ($autoAdd && $existing) {
                    $fresh = $newItems->filter(function ($n) use ($oldNames) {
                        foreach ($oldNames as $o) { if (BomRevision::nameSame((string) $n->description, (string) $o)) { return false; } }
                        return true;
                    })->values();
                    if ($fresh->isNotEmpty()) {
                        $sync = MaterialRadarSync::addNewRows($radar, $fresh, auth()->id());
                        $result['auto_added']   = ($result['auto_added'] ?? 0) + $sync['added'];
                        $result['auto_linked']  = ($result['auto_linked'] ?? 0) + $sync['linked'];
                        $result['auto_skipped'] = ($result['auto_skipped'] ?? 0) + $sync['skipped'];
                        $result['new_rows']     = ($result['new_rows'] ?? 0) + $fresh->count();
                    }
                }
                $result['items'] += count($parsed['items']);
                $result['changes'][] = ($existing ? 'diperbarui' : 'baru') . ' - ' . ($meta['project_no'] ?: $meta['sheet_name']) . ': ' . $oldCount . ' → ' . count($parsed['items']) . ' baris';
            });
        }
        $reader->close();

        return $result;
    }

    public static function parseSheet(array $rows): ?array
    {
        // ---- blok header proyek (baris atas) ----
        $meta = array_fill_keys(array_keys(self::LABELS), null);
        $isLabel = function ($text) {
            foreach (self::LABELS as $re) { if (preg_match($re, (string) $text)) { return true; } }
            return false;
        };
        foreach ($rows as $rn => $cells) {
            if ($rn > 10) { break; }
            ksort($cells);
            foreach ($cells as $ci => $text) {
                foreach (self::LABELS as $field => $re) {
                    if ($meta[$field] !== null || !preg_match($re, $text)) { continue; }
                    $rest = trim(preg_replace('/^[^:]*:?/', '', $text, 1));
                    if ($rest === '' || $rest === $text) {
                        $rest = '';
                        foreach ($cells as $cj => $other) {
                            if ($cj <= $ci || trim($other, " :") === '') { continue; }
                            if ($isLabel($other)) { break; }
                            $rest = trim($other, " :");
                            break;
                        }
                    }
                    $meta[$field] = $rest !== '' ? $rest : null;
                }
            }
        }

        // ---- baris judul kolom ----
        $headRow = null;
        foreach ($rows as $rn => $cells) {
            $low = array_map(fn($v) => strtolower(trim($v)), $cells);
            if (in_array('no', $low, true) && preg_grep('/material\s*desc/', $low)) { $headRow = $rn; break; }
        }
        if ($headRow === null) { return null; }

        $h1 = $rows[$headRow];
        $h2 = $rows[$headRow + 1] ?? [];
        $find = function (string $re) use ($h1, $h2) {
            foreach ([$h1, $h2] as $h) {
                foreach ($h as $ci => $t) { if (preg_match($re, $t)) { return $ci; } }
            }
            return null;
        };
        $col = [
            'no' => $find('/^no\.?$/i') ?? 0,
            'material_code' => $find('/material\s*code|kode\s*material/i'),   // opsional; kosong bila sheet belum punya kolomnya
            'description' => $find('/material\s*desc/i'),
            'spec' => $find('/material\s*spec/i'),
            'req_qty' => $find('/^required\s*qty/i'),
            'avail_qty' => $find('/^avail/i'),
            'required_date' => $find('/^required\s*date|^req\.?\s*date|^need(ed)?\s*date|diperlukan/i'),
            'arrival_date' => $find('/actual\s*arrival/i'),
            'arrival_qty' => $find('/qty\s*of\s*arrival|arrival\s*qty/i'),
            'remarks' => $find('/remarks|action\s*plan/i'),
        ];

        // grup BOM / PR / PO (judul gabungan: hanya sel pertama berisi)
        $groups = [];
        foreach ($h1 as $ci => $t) {
            $k = strtolower(trim($t));
            if (in_array($k, ['bom', 'pr', 'po'], true)) { $groups[$k] = $ci; }
        }
        $starts = $groups;
        asort($starts);
        $endBoundary = $col['required_date'] ?? ($col['arrival_date'] ?? (max(array_keys($h1)) + 1));
        $spanOf = [];
        $names = array_keys($starts);
        foreach ($names as $i => $g) {
            $spanOf[$g] = [$starts[$g], (isset($names[$i + 1]) ? $starts[$names[$i + 1]] : $endBoundary) - 1];
        }
        $sub = function (string $g, string $re, int $fallbackOffset) use ($spanOf, $h2, $groups) {
            if (!isset($spanOf[$g])) { return null; }
            [$a, $b] = $spanOf[$g];
            for ($c = $a; $c <= $b; $c++) {
                if (isset($h2[$c]) && preg_match($re, $h2[$c])) { return $c; }
            }
            return min($a + $fallbackOffset, $b);
        };
        $col['bom_no']   = $sub('bom', '/\bno\b/i', 0);
        $col['bom_date'] = $sub('bom', '/release|date/i', 1);
        $col['pr_no']    = $sub('pr', '/\bno\b/i', 0);
        $col['pr_qty']   = $sub('pr', '/qty/i', 1);
        $col['pr_date']  = $sub('pr', '/release|date/i', 2);
        $col['po_no']    = $sub('po', '/\bno\b/i', 0);
        $col['po_qty']   = $sub('po', '/qty/i', 1);
        $col['po_date']  = $sub('po', '/release|date/i', 2);

        if ($col['description'] === null) { return null; }

        // ---- baris data ----
        $items = [];
        $warnings = [];
        $noName = [];
        $seq = 0;
        foreach ($rows as $rn => $cells) {
            if ($rn <= $headRow + 1) { continue; }
            $get = fn($f) => isset($col[$f]) && $col[$f] !== null ? ($cells[$col[$f]] ?? null) : null;
            $desc = $get('description');
            if ($desc === null || trim($desc) === '') {
                // baris tanpa nama material tetapi ada data lain (spec, qty, BOM/PR/PO, tanggal, remarks) tetap dibawa & dilaporkan
                $hasData = false;
                foreach (['spec', 'req_qty', 'avail_qty', 'bom_no', 'pr_no', 'pr_qty', 'po_no', 'po_qty', 'required_date', 'arrival_date', 'arrival_qty', 'remarks'] as $f) {
                    if (trim((string) $get($f)) !== '') { $hasData = true; break; }
                }
                if (!$hasData) { continue; }
                $noName[] = $rn;
                $desc = '';
            }
            $seq++;
            $row = ['no' => $get('no') ?? (string) $seq];
            foreach (['material_code', 'description', 'spec', 'bom_no', 'pr_no', 'po_no', 'remarks'] as $f) {
                $row[$f] = $get($f);
                if ($f === 'material_code' && $row[$f] !== null) { $row[$f] = trim((string) $row[$f]) === '' ? null : trim((string) $row[$f]); }
            }
            foreach (MaterialRadarItem::$numberFields as $f) { $row[$f] = MaterialRadarItem::parseNumber($get($f)); }
            foreach (MaterialRadarItem::$dateFields as $f) {
                $row[$f] = MaterialRadarItem::parseDate($get($f));
                // sel terisi tetapi bukan tanggal yang dikenali -> dilaporkan, bukan diam-diam dikosongkan
                if ($row[$f] === null && $get($f) !== null && trim((string) $get($f)) !== '') { $warnings[$f][] = trim((string) $get($f)); }
            }
            $items[] = $row;
        }

        if ($noName) { $warnings['description'] = array_map(fn($n) => 'baris ' . $n, $noName); }

        return ['meta' => $meta, 'items' => $items, 'warnings' => $warnings, 'columns' => array_map(fn($c) => $c !== null, $col)];
    }
}