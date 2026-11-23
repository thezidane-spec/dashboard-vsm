<?php

namespace App\Http\Controllers;

use App\Models\MaterialRadar;
use App\Models\MaterialRadarItem;
use App\Support\MaterialRadarImporter;
use App\Support\MaterialRadarSync;
use Illuminate\Http\Request;

class MaterialRadarController extends Controller
{
    private function role(): string
    {
        return strtolower((string) (auth()->user()->role ?? ''));
    }

    private function canManage(): bool
    {
        return in_array($this->role(), ['admin', 'ppic'], true);
    }

    public function index(Request $request)
    {
        $radars   = MaterialRadar::orderBy('project_no')->orderBy('project_name')->get();
        $selected = $radars->firstWhere('id', (int) $request->query('radar')) ?? $radars->first();
        $items    = $selected ? $selected->items()->get() : collect();

        return view('radar.index', [
            'radars'    => $radars,
            'selected'  => $selected,
            'items'     => $items,
            'myPics'    => MaterialRadarItem::picsForRole($this->role()),
            'canManage' => $this->canManage(),
            'fieldPic'  => MaterialRadarItem::$fieldPic,
            'lastUrl'   => MaterialRadar::whereNotNull('source_url')->latest('updated_at')->value('source_url'),
        ]);
    }

    // Impor dari link Google Spreadsheet dan/atau file .xlsx (semua sheet proyek sekaligus)
    public function import(Request $request)
    {
        abort_unless($this->canManage(), 403);
        $request->validate([
            'sheet_url'  => 'nullable|string|max:500',
            'sheet_file' => 'nullable|file|max:20480',
        ]);
        if ($request->hasFile('sheet_file') && strtolower($request->file('sheet_file')->getClientOriginalExtension()) !== 'xlsx') {
            return back()->with('error', 'File harus berformat .xlsx (di Google Sheets: File > Download > Microsoft Excel).');
        }
        if (!$request->filled('sheet_url') && !$request->hasFile('sheet_file')) {
            return back()->with('error', 'Isi link Google Spreadsheet atau pilih file .xlsx.');
        }

        $tmp = null;
        try {
            if ($request->hasFile('sheet_file')) {
                $tmp = $request->file('sheet_file')->getRealPath();
                $url = null;
            } else {
                $url = trim($request->input('sheet_url'));
                $tmp = MaterialRadarImporter::download($url);
            }
            // Mode update: proyek yang sudah ada diganti isinya dengan list terbaru (otomatis timpa)
            $isUpdate = $request->input('mode') === 'update';
            $res = MaterialRadarImporter::import($tmp, $url, $isUpdate || $request->boolean('overwrite'), $isUpdate);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal impor: ' . $e->getMessage());
        } finally {
            if ($tmp && !$request->hasFile('sheet_file') && is_file($tmp)) { @unlink($tmp); }
        }

        $msg = !empty($isUpdate)
            ? "List diperbarui: {$res['updated']} proyek diganti dengan list terbaru, {$res['created']} proyek baru ditambahkan, total {$res['items']} baris material."
            : "Impor selesai: {$res['created']} proyek baru, {$res['updated']} diperbarui, {$res['items']} baris material.";
        if ($res['skipped']) {
            $msg .= ' Dilewati: ' . implode('; ', $res['skipped']) . '.';
        }
        if (!empty($isUpdate) && !empty($res['changes'])) {
            $msg .= ' Rincian: ' . implode('; ', array_slice($res['changes'], 0, 8)) . (count($res['changes']) > 8 ? '; ...' : '') . '.';
        }
        if (!empty($isUpdate) && !empty($res['new_rows'])) {
            $msg .= ' Baris baru di radar: ' . $res['new_rows'] . ' -> ' . ($res['auto_added'] ?? 0) . ' material baru masuk ke Raw Material (UOM ditebak dari nama, silakan periksa)'
                . (!empty($res['auto_linked']) ? ', ' . $res['auto_linked'] . ' cocok dengan material yang sudah ada (ditautkan, tidak dobel)' : '')
                . (!empty($res['auto_skipped']) ? ', ' . $res['auto_skipped'] . ' tidak masuk (proyek radar belum tertaut ke proyek dashboard, atau nama/qty kosong)' : '') . '.';
        }
        if (!empty($res['relinked']) || !empty($res['unlinked'])) {
            $msg .= ' Material Raw Material tertaut: ' . ($res['relinked'] ?? 0) . ' tersambung ulang' . (!empty($res['unlinked']) ? ', ' . $res['unlinked'] . ' terlepas (barisnya tidak ada lagi di sheet)' : '') . '.';
        }
        if (!empty($res['warnings'])) {
            $msg .= ' Perhatian: ' . implode('; ', $res['warnings']) . '.';
        }
        // Mode update: tetap di proyek yang sedang dibuka (kalau masih ada); impor biasa: ke proyek terbaru
        $stay  = !empty($isUpdate) && $request->filled('current') ? MaterialRadar::whereKey((int) $request->input('current'))->first() : null;
        $first = $stay ?: MaterialRadar::latest('id')->first();

        return redirect()->route('radar.index', $first ? ['radar' => $first->id] : [])->with('success', $msg);
    }

    // Simpan perubahan isi tabel -- hanya kolom milik PIC sesuai role (admin: semua)
    public function updateItems(Request $request, MaterialRadar $radar)
    {
        $pics = MaterialRadarItem::picsForRole($this->role());
        abort_if(empty($pics), 403);

        $allowed = array_keys(array_filter(MaterialRadarItem::$fieldPic, fn($pic) => in_array($pic, $pics, true)));
        $items   = $radar->items()->get()->keyBy('id');
        $changed = 0;

        foreach ((array) $request->input('items', []) as $id => $row) {
            $item = $items->get((int) $id);
            if (!$item) { continue; }
            $data = [];
            foreach ($allowed as $field) {
                if (!array_key_exists($field, (array) $row)) { continue; }
                $val = is_string($row[$field]) ? trim($row[$field]) : null;
                if (in_array($field, MaterialRadarItem::$dateFields, true)) {
                    $val = MaterialRadarItem::parseDate($val);
                } elseif (in_array($field, MaterialRadarItem::$numberFields, true)) {
                    $val = MaterialRadarItem::parseNumber($val);
                } else {
                    $val = $val === '' ? null : mb_substr($val, 0, $field === 'description' || $field === 'remarks' ? 2000 : 255);
                }
                $data[$field] = $val;
            }
            if ($data) {
                $item->fill($data);
                if ($item->isDirty()) { $item->save(); MaterialRadarSync::pull($item); $changed++; }
            }
        }

        return redirect()->route('radar.index', ['radar' => $radar->id])->with('success', "Perubahan disimpan ({$changed} baris diperbarui).");
    }

    // Tambah baris kosong (hak PIC Engineering / admin)
    public function addItem(MaterialRadar $radar)
    {
        abort_unless(in_array('engineering', MaterialRadarItem::picsForRole($this->role()), true), 403);
        $radar->items()->create(['sort' => (int) $radar->items()->max('sort') + 1, 'no' => (string) ($radar->items()->count() + 1)]);

        return redirect()->route('radar.index', ['radar' => $radar->id, 'edit' => 1])->with('success', 'Baris baru ditambahkan.');
    }

    public function deleteItem(MaterialRadar $radar, MaterialRadarItem $item)
    {
        abort_unless(in_array('engineering', MaterialRadarItem::picsForRole($this->role()), true), 403);
        abort_unless($item->material_radar_id === $radar->id, 404);
        $item->delete();

        return redirect()->route('radar.index', ['radar' => $radar->id, 'edit' => 1])->with('success', 'Baris dihapus.');
    }

    // Ubah info proyek & nama PIC (admin / PPIC)
    public function updateMeta(Request $request, MaterialRadar $radar)
    {
        abort_unless($this->canManage(), 403);
        $data = $request->validate([
            'project_no' => 'nullable|string|max:255', 'project_name' => 'nullable|string|max:255', 'customer' => 'nullable|string|max:255',
            'pic_engineering' => 'nullable|string|max:255', 'pic_ppic' => 'nullable|string|max:255',
            'pic_warehouse' => 'nullable|string|max:255', 'pic_procurement' => 'nullable|string|max:255',
        ]);
        $radar->update($data);

        return redirect()->route('radar.index', ['radar' => $radar->id])->with('success', 'Info proyek disimpan.');
    }

    // HAPUS SEMUA data Material Radar List (semua proyek & barisnya). Material di Raw Material tetap ada, hanya tautannya dilepas.
    public function destroyAll()
    {
        abort_unless($this->canManage(), 403);
        $projects = MaterialRadar::count();
        $rows     = MaterialRadarItem::count();

        \Illuminate\Support\Facades\DB::transaction(function () {
            \App\Models\MaterialItem::whereNotNull('radar_item_id')->update(['radar_item_id' => null, 'radar_full_sync' => true]);
            \App\Models\MaterialArrival::where('source', 'radar')->update(['source' => null]);
            MaterialRadarItem::query()->delete();
            MaterialRadar::query()->delete();
        });

        return redirect()->route('radar.index')->with('success', "Semua data Material Radar List dihapus ({$projects} proyek, {$rows} baris).");
    }

    public function destroy(MaterialRadar $radar)
    {
        abort_unless($this->canManage(), 403);
        \App\Models\MaterialItem::whereIn('radar_item_id', $radar->items()->pluck('id'))->update(['radar_item_id' => null, 'radar_full_sync' => true]);
        $radar->delete();

        return redirect()->route('radar.index')->with('success', 'Proyek Material Radar List dihapus.');
    }
}