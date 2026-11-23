<?php

namespace App\Support;

use App\Models\MaterialArrival;
use App\Models\MaterialItem;
use App\Models\MaterialRadarItem;
use App\Models\Project;

/**
 * Menyamakan data Raw Material dengan Material Radar List (baris yang tertaut lewat material_items.radar_item_id).
 *
 *  Raw Material            Material Radar List
 *  name                <-> description
 *  spec                <-> spec
 *  quantity            <-> req_qty
 *  purchase_date       <-> required_date   ("Tanggal Diperlukan")
 *  total kedatangan    <-> arrival_qty     (arrival_date = tanggal kedatangan terakhir)
 *
 * Kedatangan di radar hanya satu angka; di Raw Material disimpan sebagai satu catatan ber-source "radar"
 * sebesar (arrival_qty radar - kedatangan manual), sehingga total kedua sisi selalu sama.
 * Semua penulisan memakai query langsung (tanpa event) supaya tidak saling memanggil berulang.
 */
class MaterialRadarSync
{
    /** Cari material Raw Material yang sudah ada (belum tertaut) dengan nama sama/mirip dengan baris radar. */
    public static function findExisting($free, MaterialRadarItem $r, array $taken): ?MaterialItem
    {
        $best = null;
        foreach ($free as $m) {
            if (isset($taken[$m->id]) || !BomRevision::nameSame((string) $r->description, $m->name)) { continue; }
            if ($best === null || BomRevision::specSame($r->spec, $m->spec)) { $best = $m; }
        }
        return $best;
    }

    /** Tebakan UOM dari nama (radar tidak punya kolom UOM): plat -> Lbr, batang panjang -> Btg, selain itu Pcs. Bisa diubah manual. */
    public static function guessUom(string $name): string
    {
        $n = strtolower($name);
        if (preg_match('/\b(plate|plat|grating|sheet|lembar)\b/', $n)) { return 'Lbr'; }
        if (preg_match('/\b(pipe|pipa|rhs|shs|unp|wf|h-?beam|i-?beam|siku|angle|channel|flat\s*bar|round\s*bar|hollow)\b/', $n) || preg_match('/\b(6000|12000|6m|12m)\b/', $n)) { return 'Btg'; }
        return 'Pcs';
    }

    /**
     * Baris BARU di radar (hasil Update List) otomatis masuk ke Raw Material proyek yang tertaut:
     *  - nama sudah ada di Raw Material -> hanya ditautkan (qty/nama/spec dipertahankan; tanggal diperlukan & kedatangan ditambahkan)
     *  - nama belum ada -> dibuat sebagai material baru (kategori Raw Material, UOM ditebak dari nama)
     * @param  \Illuminate\Support\Collection $newRows MaterialRadarItem yang baru muncul
     * @return array{added:int, linked:int, skipped:int}
     */
    public static function addNewRows(\App\Models\MaterialRadar $radar, $newRows, ?int $userId = null): array
    {
        $out = ['added' => 0, 'linked' => 0, 'skipped' => 0];
        $project = $radar->project_id ? Project::find($radar->project_id) : null;
        if (!$project) { $out['skipped'] = $newRows->count(); return $out; }

        $free  = $project->materialItems()->whereNull('radar_item_id')->get();
        $taken = [];
        $names = [];

        foreach ($newRows as $r) {
            if (trim((string) $r->description) === '' || (float) $r->req_qty <= 0) { $out['skipped']++; continue; }

            $existing = self::findExisting($free, $r, $taken);
            if ($existing) {
                $taken[$existing->id] = true;
                $existing->update(['radar_item_id' => $r->id, 'radar_full_sync' => false, 'purchase_date' => $r->required_date ? $r->required_date->format('Y-m-d') : $existing->purchase_date]);
                self::syncArrival($existing->id, $r, $userId);
                $out['linked']++;
                continue;
            }

            $item = $project->materialItems()->create([
                'radar_item_id' => $r->id,
                'category'      => MaterialItem::CATEGORY_RAW,
                'name'          => mb_substr($r->description, 0, 150),
                'spec'          => $r->spec ? mb_substr($r->spec, 0, 150) : null,
                'uom'           => self::guessUom($r->description),
                'quantity'      => $r->req_qty,
                'purchase_date' => $r->required_date ? $r->required_date->format('Y-m-d') : null,
                'created_by'    => $userId,
            ]);
            self::syncArrival($item->id, $r, $userId);
            $names[] = $item->name;
            $out['added']++;
        }

        if (($out['added'] || $out['linked']) && auth()->check()) {
            \App\Models\ActivityLog::create([
                'user_id'     => auth()->id(),
                'project_id'  => $project->id,
                'action'      => 'RADAR_UPDATE_ADD_MATERIAL',
                'department'  => strtolower((string) (auth()->user()->role ?? 'ppic')),
                'description' => 'Update Material Radar List: ' . $out['added'] . ' material baru masuk ke Raw Material (' . implode(', ', array_slice($names, 0, 8)) . (count($names) > 8 ? ', ...' : '') . '), ' . $out['linked'] . ' material lama ditautkan',
                'ip_address'  => request()->ip(),
            ]);
        }
        if ($out['added'] || $out['linked']) { $project->syncMainMaterialProgress($userId); }

        return $out;
    }

    /** Raw Material -> Radar (dipanggil setelah material / kedatangannya berubah) */
    public static function push(MaterialItem $item): void
    {
        if (!$item->radar_item_id) { return; }
        $radarItem = MaterialRadarItem::find($item->radar_item_id);
        if (!$radarItem) { return; }

        $item->load('arrivals');
        $last = $item->arrivals->sortBy('arrival_date')->last();

        $data = [
            'required_date' => $item->purchase_date ? $item->purchase_date->format('Y-m-d') : null,
            'arrival_qty'   => $item->arrivals->isEmpty() ? null : $item->actual,
            'arrival_date'  => $last ? $last->arrival_date->format('Y-m-d') : null,
            'updated_at'    => now(),
        ];
        // Nama / spec / qty ikut hanya untuk material yang dibuat dari radar (bukan yang dicocokkan ke data lama)
        if ($item->radar_full_sync) {
            $data += ['description' => $item->name, 'spec' => $item->spec, 'req_qty' => (float) $item->quantity];
        }
        MaterialRadarItem::whereKey($radarItem->id)->update($data);
    }

    /** Radar -> Raw Material (dipanggil setelah baris radar disimpan) */
    public static function pull(MaterialRadarItem $radarItem): int
    {
        $count = 0;
        foreach (MaterialItem::where('radar_item_id', $radarItem->id)->get() as $item) {
            $qty  = (float) ($radarItem->req_qty ?? 0);
            $data = ['purchase_date' => $radarItem->required_date ? $radarItem->required_date->format('Y-m-d') : null, 'updated_at' => now()];
            if ($item->radar_full_sync) {
                $data += [
                    'name'     => trim((string) $radarItem->description) !== '' ? mb_substr($radarItem->description, 0, 150) : $item->name,
                    'spec'     => $radarItem->spec,
                    'quantity' => $qty > 0 ? $qty : $item->quantity,
                ];
            }
            MaterialItem::whereKey($item->id)->update($data);
            self::syncArrival($item->id, $radarItem);
            $count++;
        }

        return $count;
    }

    /** Catatan kedatangan ber-source "radar" mengikuti kolom Arrival di radar */
    public static function syncArrival(int $itemId, MaterialRadarItem $radarItem, ?int $userId = null): void
    {
        $manual = (float) MaterialArrival::where('material_item_id', $itemId)->whereNull('source')->sum('quantity');
        $want   = round((float) ($radarItem->arrival_qty ?? 0) - $manual, 2);
        $row    = MaterialArrival::where('material_item_id', $itemId)->where('source', 'radar')->first();

        if ($want > 0 && $radarItem->arrival_date) {
            $data = ['arrival_date' => $radarItem->arrival_date->format('Y-m-d'), 'quantity' => $want, 'remarks' => 'Dari Material Radar List'];
            if ($row) {
                MaterialArrival::whereKey($row->id)->update($data + ['updated_at' => now()]);
            } else {
                MaterialArrival::create($data + ['material_item_id' => $itemId, 'source' => 'radar', 'created_by' => $userId ?? auth()->id()]);
            }
        } elseif ($row) {
            $row->delete();
        }
    }
}