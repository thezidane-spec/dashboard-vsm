<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\MaterialArrival;
use App\Models\MaterialItem;
use App\Models\Project;
use App\Models\MaterialRadar;
use App\Models\MaterialRadarItem;
use App\Support\MaterialRadarSync;
use App\Support\MaterialReport;
use App\Support\MaterialXlsx;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Pendataan detail Raw Material & Vendor Raw Material (Divisi Procurement).
 *
 * - Semua user yang login boleh MELIHAT data & laporan.
 * - Hanya role admin, ppic, dan procurement yang boleh MENGUBAH data.
 */
class MaterialController extends Controller
{
    private const EDIT_ROLES = ['admin', 'ppic', 'procurement'];

    // Ambil seluruh data (item, riwayat kedatangan, laporan HTML, ringkasan)
    public function index(Project $project): JsonResponse
    {
        return response()->json($this->payload($project));
    }

    // TAMBAH material baru ke daftar
    public function storeItem(Request $request, Project $project): JsonResponse
    {
        $this->authorizeEdit();
        $data = $this->validateItem($request);

        $item = $project->materialItems()->create($data + ['created_by' => auth()->id()]);

        $snapshot = $this->itemSnapshot($item);
        $this->log(
            $project,
            'ADD_MATERIAL_ITEM',
            'Menambahkan ' . MaterialItem::$categoryLabels[$item->category] . ' "' . $item->name . '" sebanyak '
                . MaterialReport::number($item->quantity) . ' ' . $item->uom,
            null,
            $snapshot
        );

        return $this->respond($project, 'Material "' . $item->name . '" berhasil ditambahkan.', 201);
    }

    // SCAN PDF: baca daftar material dari PDF bertext -> hanya PRATINJAU (belum menulis ke database)
    public function scanPdf(Request $request, Project $project): JsonResponse
    {
        $this->authorizeEdit();
        $request->validate(['pdf' => ['required', 'file', 'mimes:pdf', 'max:10240']], [
            'pdf.required' => 'Pilih file PDF terlebih dulu.',
            'pdf.mimes'    => 'File harus berformat PDF.',
            'pdf.max'      => 'Ukuran PDF maksimal 10 MB.',
            'pdf.file'     => 'Upload PDF gagal.',
        ]);

        $path   = $request->file('pdf')->getRealPath();
        $method = 'teks';

        // 1) PDF bertext -> baca langsung. 2) PDF hasil scan (gambar) / teks tak dikenali -> OCR tabel.
        $text  = \App\Support\PdfMaterialParser::extractText($path);
        $items = trim($text) !== '' ? \App\Support\PdfMaterialParser::parse($text) : [];

        if (empty($items)) {
            if (!\App\Support\PdfTableOcr::available()) {
                $detail = !empty(\App\Support\PdfTableOcr::$trace) ? ' [Detail teknis: ' . implode('; ', \App\Support\PdfTableOcr::$trace) . ']' : '';
                return response()->json(['message' => 'PDF ini berupa gambar hasil scan dan butuh OCR, tetapi server belum punya poppler (pdftoppm), tesseract, dan ekstensi PHP GD yang lengkap.' . $detail], 422);
            }
            $items  = \App\Support\PdfTableOcr::parse($path);
            $method = 'OCR';
        }

        if (empty($items)) {
            $detail = $method === 'OCR' && !empty(\App\Support\PdfTableOcr::$trace) ? ' [Detail teknis: ' . implode('; ', \App\Support\PdfTableOcr::$trace) . ']' : '';
            return response()->json(['message' => 'Tidak ada baris material yang dikenali. Pastikan PDF berisi tabel BOM (kolom No, Material, Spec, Qty, UoM) dengan garis tabel yang jelas dan hasil scan tegak lurus.' . $detail], 422);
        }

        $payload = ['message' => count($items) . ' baris material terbaca' . ($method === 'OCR' ? ' (OCR - periksa teks, angka & spec dengan teliti)' : '') . '. Periksa dulu sebelum diimpor.', 'items' => $items, 'method' => $method];

        // Jika kategori ini sudah punya material -> bandingkan sebagai REVISI BOM (putih/kuning/biru/merah)
        $category = $request->input('category');
        if (in_array($category, array_keys(MaterialItem::$categoryLabels), true)) {
            $existing = $project->materialItems()->with('arrivals')->where('category', $category)->get();
            if ($existing->isNotEmpty()) {
                $cmp = \App\Support\BomRevision::compare($existing, $items);
                $payload['revision'] = $cmp;
                $payload['message']  = count($items) . ' baris terbaca & dibandingkan dengan ' . $existing->count() . ' material yang sudah ada' . ($method === 'OCR' ? ' (OCR - periksa dengan teliti)' : '') . '.';
            }
        }

        return response()->json($payload);
    }

    // TERAPKAN revisi BOM (baris yang dicentang pengguna): tambah / ubah / hapus
    public function applyRevision(Request $request, Project $project): JsonResponse
    {
        $this->authorizeEdit();
        $request->validate([
            'category'          => ['required', Rule::in(array_keys(MaterialItem::$categoryLabels))],
            'rows'              => ['required', 'array', 'min:1', 'max:400'],
            'rows.*.action'     => ['required', Rule::in(['add', 'update', 'delete'])],
            'rows.*.item_id'    => ['nullable', 'integer'],
            'rows.*.name'       => ['nullable', 'string', 'max:150'],
            'rows.*.spec'       => ['nullable', 'string', 'max:150'],
            'rows.*.uom'        => ['nullable', 'string', 'max:20'],
            'rows.*.quantity'   => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'rows.*.weight'     => ['nullable', 'numeric', 'min:0', 'max:99999999'],
        ], $this->messages(), $this->attributes() + ['rows' => 'Daftar revisi']);

        $category = $request->input('category');
        $count    = ['add' => 0, 'update' => 0, 'delete' => 0];

        DB::transaction(function () use ($request, $project, $category, &$count) {
            foreach ($request->input('rows') as $row) {
                $action = $row['action'];

                if ($action === 'add') {
                    if (trim((string) ($row['name'] ?? '')) === '' || trim((string) ($row['uom'] ?? '')) === '' || (float) ($row['quantity'] ?? 0) <= 0) {
                        abort(422, 'Material baru "' . ($row['name'] ?? '') . '" harus punya nama, UOM dan qty lebih dari 0.');
                    }
                    $item = $project->materialItems()->create([
                        'category'   => $category,
                        'name'       => $row['name'],
                        'spec'       => $row['spec'] ?? null,
                        'uom'        => $row['uom'],
                        'quantity'   => $row['quantity'],
                        'weight'     => ($row['weight'] ?? '') === '' ? null : $row['weight'],
                        'created_by' => auth()->id(),
                    ]);
                    $this->log($project, 'REVISION_ADD_MATERIAL', 'Revisi BOM: menambah material "' . $item->name . '"', null, $this->itemSnapshot($item));
                    $count['add']++;
                    continue;
                }

                $item = $project->materialItems()->where('category', $category)->find($row['item_id'] ?? 0);
                if (!$item) { continue; }

                if ($action === 'delete') {
                    $snapshot = $this->itemSnapshot($item);
                    $item->arrivals()->delete();
                    $item->delete();
                    $this->log($project, 'REVISION_DELETE_MATERIAL', 'Revisi BOM: menghapus material "' . $snapshot['nama'] . '"', $snapshot, null);
                    $count['delete']++;
                    continue;
                }

                if ((float) ($row['quantity'] ?? 0) <= 0) {
                    abort(422, 'Qty material "' . $item->name . '" harus lebih dari 0 (gunakan hapus bila dibatalkan).');
                }
                $old = $this->itemSnapshot($item);
                $item->update([
                    'spec'     => $row['spec'] ?? $item->spec,
                    'uom'      => ($row['uom'] ?? '') !== '' ? $row['uom'] : $item->uom,
                    'quantity' => $row['quantity'],
                    'weight'   => array_key_exists('weight', $row) && $row['weight'] !== null && $row['weight'] !== '' ? $row['weight'] : $item->weight,
                ]);
                $this->log($project, 'REVISION_UPDATE_MATERIAL', 'Revisi BOM: mengubah material "' . $item->name . '"', $old, $this->itemSnapshot($item->fresh()));
                $count['update']++;
            }
        });

        return $this->respond($project, 'Revisi BOM diterapkan: ' . $count['add'] . ' ditambah, ' . $count['update'] . ' diubah, ' . $count['delete'] . ' dihapus.');
    }

    // IMPOR hasil scan PDF (setelah diperiksa pengguna): banyak material sekaligus
    public function importItems(Request $request, Project $project): JsonResponse
    {
        $this->authorizeEdit();
        $request->validate([
            'category'          => ['required', Rule::in(array_keys(MaterialItem::$categoryLabels))],
            'purchase_date'     => ['nullable', 'date', 'after_or_equal:2000-01-01', 'before_or_equal:2099-12-31'],
            'items'             => ['required', 'array', 'min:1', 'max:300'],
            'items.*.name'      => ['required', 'string', 'max:150'],
            'items.*.spec'      => ['nullable', 'string', 'max:150'],
            'items.*.uom'       => ['required', 'string', 'max:20'],
            'items.*.quantity'  => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'items.*.weight'    => ['nullable', 'numeric', 'min:0', 'max:99999999'],
        ], $this->messages(), $this->attributes() + ['items' => 'Daftar material', 'items.*.name' => 'Nama material', 'items.*.uom' => 'UOM', 'items.*.quantity' => 'Quantity']);

        $names = [];
        foreach ($request->input('items') as $row) {
            $item = $project->materialItems()->create([
                'category'      => $request->input('category'),
                'name'          => $row['name'],
                'spec'          => $row['spec'] ?? null,
                'uom'           => $row['uom'],
                'quantity'      => $row['quantity'],
                'weight'        => ($row['weight'] ?? '') === '' ? null : $row['weight'],
                'purchase_date' => $request->input('purchase_date') ?: null,
                'created_by'    => auth()->id(),
            ]);
            $names[] = $item->name;
        }

        $this->log($project, 'IMPORT_MATERIAL_PDF', 'Impor ' . count($names) . ' material dari PDF: ' . implode(', ', array_slice($names, 0, 10)) . (count($names) > 10 ? ', ...' : ''));

        return $this->respond($project, count($names) . ' material berhasil diimpor dari PDF.', 201);
    }

    // UBAH data material (mis. revisi qty, spec, tanggal beli)
    public function updateItem(Request $request, Project $project, MaterialItem $item): JsonResponse
    {
        $this->authorizeEdit();
        $this->ensureBelongs($project, $item->project_id);
        $data = $this->validateItem($request);

        $old = $this->itemSnapshot($item);
        $item->update($data);
        $new = $this->itemSnapshot($item);

        $changedOld = [];
        $changedNew = [];
        foreach ($new as $key => $value) {
            if (($old[$key] ?? null) !== $value) {
                $changedOld[$key] = $old[$key] ?? null;
                $changedNew[$key] = $value;
            }
        }

        if (!empty($changedNew)) {
            $this->log($project, 'UPDATE_MATERIAL_ITEM', 'Mengubah data material "' . $item->name . '"', $changedOld, $changedNew);
        }

        return $this->respond($project, 'Material "' . $item->name . '" berhasil diperbarui.');
    }

    // UBAH cepat: Tanggal Diperlukan langsung dari daftar material
    public function updateRequiredDate(Request $request, Project $project, MaterialItem $item): JsonResponse
    {
        $this->authorizeEdit();
        $this->ensureBelongs($project, $item->project_id);
        $data = $request->validate([
            'purchase_date' => ['nullable', 'date', 'after_or_equal:2000-01-01', 'before_or_equal:2099-12-31'],
        ], $this->messages(), $this->attributes());

        $old = $item->purchase_date ? $item->purchase_date->format('Y-m-d') : null;
        $item->update(['purchase_date' => $data['purchase_date'] ?? null]);
        $new = $item->purchase_date ? $item->purchase_date->format('Y-m-d') : null;

        if ($old !== $new) {
            $this->log($project, 'UPDATE_MATERIAL_ITEM', 'Mengubah tanggal diperlukan material "' . $item->name . '"', ['tgl_diperlukan' => $old], ['tgl_diperlukan' => $new]);
        }

        return $this->respond($project, 'Tanggal diperlukan "' . $item->name . '" disimpan.');
    }

    // CENTANG Mill Certificate langsung dari tabel material (sudah / belum menerima mill certificate)
    public function updateMillCert(Request $request, Project $project, MaterialItem $item): JsonResponse
    {
        $this->authorizeEdit();
        $this->ensureBelongs($project, $item->project_id);
        $data = $request->validate(['mill_cert' => ['required', 'boolean']]);

        $old = (bool) $item->mill_cert;
        $item->update(['mill_cert' => (bool) $data['mill_cert']]);

        if ($old !== (bool) $item->mill_cert) {
            $this->log($project, 'UPDATE_MATERIAL_ITEM', 'Mill Certificate material "' . $item->name . '" ' . ($item->mill_cert ? 'diterima' : 'dibatalkan'), ['mill_cert' => $old], ['mill_cert' => (bool) $item->mill_cert]);
        }

        return $this->respond($project, 'Mill Certificate "' . $item->name . '" ' . ($item->mill_cert ? 'ditandai sudah ada.' : 'dibatalkan.'));
    }

    // DAFTAR sumber Material Radar List: dropdown project + baris radarnya (yang sudah diambil ditandai)
    public function radarSources(Request $request, Project $project): JsonResponse
    {
        $this->authorizeEdit();
        $radars = MaterialRadar::orderBy('project_no')->orderBy('project_name')->get();
        $own    = $radars->firstWhere('project_id', $project->id);
        $picked = $radars->firstWhere('id', (int) $request->query('radar_id')) ?? $own;

        $linked = $project->materialItems()->whereNotNull('radar_item_id')->pluck('radar_item_id')->all();

        // material yang sudah ada & belum tertaut -> dicocokkan lewat nama (toleran beda tulisan kecil)
        $free  = $project->materialItems()->whereNull('radar_item_id')->get();
        $taken = [];
        $items = $picked ? $picked->items()->get()->map(function (MaterialRadarItem $r) use ($linked, $free, &$taken) {
            $match = in_array($r->id, $linked, true) ? null : $this->findExisting($free, $r, $taken);
            if ($match) { $taken[$match->id] = true; }
            return [
                'match'         => $match ? ['id' => $match->id, 'name' => $match->name, 'quantity' => (float) $match->quantity, 'uom' => $match->uom, 'category' => $match->category] : null,
                'id'            => $r->id,
                'no'            => $r->no,
                'description'   => $r->description,
                'spec'          => $r->spec,
                'req_qty'       => $r->req_qty,
                'required_date' => $r->required_date ? $r->required_date->format('Y-m-d') : null,
                'arrival_qty'   => $r->arrival_qty,
                'arrival_date'  => $r->arrival_date ? $r->arrival_date->format('Y-m-d') : null,
                'imported'      => in_array($r->id, $linked, true),
            ];
        })->values()->all() : [];

        return response()->json([
            'radars'      => $radars->map(fn($r) => ['id' => $r->id, 'label' => $r->label, 'is_this_project' => (int) $r->project_id === (int) $project->id])->values()->all(),
            'selected_id' => $picked->id ?? null,
            'items'       => $items,
        ]);
    }

    // Cari material yang sudah ada (belum tertaut) dengan nama sama / mirip dengan baris radar
    private function findExisting($free, MaterialRadarItem $r, array $taken): ?MaterialItem
    {
        return MaterialRadarSync::findExisting($free, $r, $taken);
    }

    // AMBIL material dari Material Radar List (termasuk data kedatangannya)
    public function importFromRadar(Request $request, Project $project): JsonResponse
    {
        $this->authorizeEdit();
        $request->validate([
            'category'             => ['required', Rule::in(array_keys(MaterialItem::$categoryLabels))],
            'radar_id'             => ['required', 'integer', 'exists:material_radars,id'],
            'rows'                 => ['required', 'array', 'min:1', 'max:500'],
            'rows.*.radar_item_id' => ['required', 'integer'],
            'rows.*.uom'           => ['nullable', 'string', 'max:20'],
        ], $this->messages() + ['rows.required' => 'Pilih minimal satu material dari Material Radar List.'], $this->attributes() + ['rows.*.uom' => 'UOM']);

        $radar  = MaterialRadar::findOrFail($request->input('radar_id'));
        $linked = $project->materialItems()->whereNotNull('radar_item_id')->pluck('radar_item_id')->all();
        $names  = [];
        $skipped = 0;
        $matched = 0;
        $free    = $project->materialItems()->whereNull('radar_item_id')->get();
        $taken   = [];

        DB::transaction(function () use ($request, $project, $radar, $linked, $free, &$taken, &$names, &$skipped, &$matched) {
            foreach ($request->input('rows') as $row) {
                $r = $radar->items()->find($row['radar_item_id']);
                if (!$r || in_array($r->id, $linked, true) || trim((string) $r->description) === '' || (float) $r->req_qty <= 0) { $skipped++; continue; }

                // Nama sudah ada di Raw Material -> cukup ditautkan: hanya tanggal diperlukan & kedatangan yang ditambahkan
                $existing = $this->findExisting($free, $r, $taken);
                if ($existing) {
                    $taken[$existing->id] = true;
                    $existing->update(['radar_item_id' => $r->id, 'radar_full_sync' => false, 'purchase_date' => $r->required_date ? $r->required_date->format('Y-m-d') : $existing->purchase_date]);
                    MaterialRadarSync::syncArrival($existing->id, $r, auth()->id());
                    $linked[] = $r->id;
                    $names[]  = $existing->name;
                    $matched++;
                    continue;
                }
                if (trim((string) ($row['uom'] ?? '')) === '') { $skipped++; continue; }

                $item = $project->materialItems()->create([
                    'radar_item_id' => $r->id,
                    'category'      => $request->input('category'),
                    'name'          => mb_substr($r->description, 0, 150),
                    'spec'          => $r->spec ? mb_substr($r->spec, 0, 150) : null,
                    'uom'           => $row['uom'],
                    'quantity'      => $r->req_qty,
                    'purchase_date' => $r->required_date ? $r->required_date->format('Y-m-d') : null,
                    'created_by'    => auth()->id(),
                ]);
                MaterialRadarSync::syncArrival($item->id, $r, auth()->id());
                $linked[] = $r->id;
                $names[]  = $item->name;
            }
        });

        if (!$names) {
            return response()->json(['message' => 'Tidak ada material yang diambil (sudah pernah diambil, nama kosong, atau qty 0).'], 422);
        }

        $this->log($project, 'IMPORT_MATERIAL_RADAR', 'Mengambil ' . count($names) . ' material dari Material Radar List "' . $radar->label . '": ' . implode(', ', array_slice($names, 0, 10)) . (count($names) > 10 ? ', ...' : ''));

        return $this->respond($project, count($names) . ' material diambil dari Material Radar List' . ($matched ? " ({$matched} sudah ada: hanya tanggal diperlukan & kedatangan ditambahkan)" : '') . ($skipped ? " ({$skipped} dilewati)." : '.'), 201);
    }

    // HAPUS material (beserta seluruh catatan kedatangannya)
    public function destroyItem(Project $project, MaterialItem $item): JsonResponse
    {
        $this->authorizeEdit();
        $this->ensureBelongs($project, $item->project_id);

        $snapshot = $this->itemSnapshot($item);
        $name     = $item->name;

        $item->arrivals()->delete();
        $item->delete();

        $this->log($project, 'DELETE_MATERIAL_ITEM', 'Menghapus material "' . $name . '" beserta catatan kedatangannya', $snapshot, null);

        return $this->respond($project, 'Material "' . $name . '" berhasil dihapus.');
    }

    // UPDATE: catat kedatangan barang (tanggal sampai + jumlah datang + remarks)
    public function storeArrival(Request $request, Project $project): JsonResponse
    {
        $this->authorizeEdit();

        $data = $request->validate([
            'material_item_id' => [
                'required',
                'integer',
                Rule::exists('material_items', 'id')->where('project_id', $project->id),
            ],
            'arrival_date'     => ['required', 'date', 'after_or_equal:2000-01-01', 'before_or_equal:2099-12-31'],
            'quantity'         => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'remarks'          => ['nullable', 'string', 'max:500'],
        ], $this->messages(), $this->attributes());

        $item = MaterialItem::where('project_id', $project->id)->findOrFail($data['material_item_id']);

        $arrival = $item->arrivals()->create([
            'arrival_date' => $data['arrival_date'],
            'quantity'     => $data['quantity'],
            'remarks'      => $data['remarks'] ?? null,
            'created_by'   => auth()->id(),
        ]);

        $item->load('arrivals');
        $deviation = $item->deviation;

        $this->log(
            $project,
            'ADD_MATERIAL_ARRIVAL',
            'Mencatat kedatangan ' . MaterialReport::number($arrival->quantity) . ' ' . $item->uom . ' "' . $item->name . '" pada '
                . $arrival->arrival_date->format('d M Y'),
            null,
            [
                'material'   => $item->name,
                'tgl_sampai' => $arrival->arrival_date->format('Y-m-d'),
                'jumlah'     => $arrival->quantity,
                'remarks'    => $arrival->remarks,
                'deviasi'    => $deviation,
            ]
        );

        if ($deviation == 0) {
            $status = 'Jumlah sesuai (deviasi 0).';
        } elseif ($deviation > 0) {
            $status = 'Jumlah lebih ' . MaterialReport::number($deviation) . ' ' . $item->uom . ' dari yang diminta.';
        } else {
            $status = 'Masih kurang ' . MaterialReport::number(abs($deviation)) . ' ' . $item->uom . ' dari yang diminta.';
        }

        return $this->respond($project, 'Kedatangan "' . $item->name . '" tersimpan. ' . $status, 201);
    }

    // UBAH satu catatan kedatangan (tanggal sampai, material, jumlah datang, remarks)
    public function updateArrival(Request $request, Project $project, MaterialArrival $arrival): JsonResponse
    {
        $this->authorizeEdit();

        $current = $arrival->item;
        abort_unless($current && (int) $current->project_id === (int) $project->id, 404);

        $data = $request->validate([
            'material_item_id' => [
                'required',
                'integer',
                Rule::exists('material_items', 'id')->where('project_id', $project->id),
            ],
            'arrival_date'     => ['required', 'date', 'after_or_equal:2000-01-01', 'before_or_equal:2099-12-31'],
            'quantity'         => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'remarks'          => ['nullable', 'string', 'max:500'],
        ], $this->messages(), $this->attributes());

        $item = MaterialItem::where('project_id', $project->id)->findOrFail($data['material_item_id']);

        $old = [
            'material'   => $current->name,
            'tgl_sampai' => $arrival->arrival_date->format('Y-m-d'),
            'jumlah'     => $arrival->quantity,
            'remarks'    => $arrival->remarks,
        ];

        $arrival->update([
            'material_item_id' => $item->id,
            'arrival_date'     => $data['arrival_date'],
            'quantity'         => $data['quantity'],
            'remarks'          => $data['remarks'] ?? null,
        ]);
        $arrival->refresh();

        $new = [
            'material'   => $item->name,
            'tgl_sampai' => $arrival->arrival_date->format('Y-m-d'),
            'jumlah'     => $arrival->quantity,
            'remarks'    => $arrival->remarks,
        ];

        $changedOld = [];
        $changedNew = [];
        foreach ($new as $key => $value) {
            if (($old[$key] ?? null) != $value) {
                $changedOld[$key] = $old[$key] ?? null;
                $changedNew[$key] = $value;
            }
        }
        if (!empty($changedNew)) {
            $this->log($project, 'UPDATE_MATERIAL_ARRIVAL', 'Mengubah catatan kedatangan "' . $item->name . '"', $changedOld, $changedNew);
        }

        return $this->respond($project, 'Catatan kedatangan "' . $item->name . '" berhasil diperbarui.');
    }

    // HAPUS satu catatan kedatangan
    public function destroyArrival(Project $project, MaterialArrival $arrival): JsonResponse
    {
        $this->authorizeEdit();

        $item = $arrival->item;
        abort_unless($item && (int) $item->project_id === (int) $project->id, 404);

        $old = [
            'material'   => $item->name,
            'tgl_sampai' => $arrival->arrival_date->format('Y-m-d'),
            'jumlah'     => $arrival->quantity,
            'remarks'    => $arrival->remarks,
        ];

        $arrival->delete();

        $this->log($project, 'DELETE_MATERIAL_ARRIVAL', 'Menghapus catatan kedatangan "' . $item->name . '"', $old, null);

        return $this->respond($project, 'Catatan kedatangan berhasil dihapus.');
    }

    // EXPORT EXCEL (.xlsx)
    public function exportExcel(Project $project)
    {
        if (!class_exists(\ZipArchive::class)) {
            abort(500, 'Ekstensi PHP "zip" belum aktif di server ini, sehingga file Excel tidak dapat dibuat. Aktifkan extension=zip pada php.ini.');
        }

        $report = MaterialReport::build($this->loadItems($project));
        $path   = MaterialXlsx::create($project, $report);

        $base     = Str::slug($project->project_code ?: $project->project_name);
        $filename = 'Laporan-Material-' . ($base !== '' ? $base : $project->id) . '-' . now()->format('Ymd') . '.xlsx';

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // EXPORT PDF: halaman siap cetak (otomatis membuka dialog Print -> pilih "Save as PDF")
    public function print(Project $project)
    {
        $report = MaterialReport::build($this->loadItems($project));

        return view('projects.material-print', compact('project', 'report'));
    }

    // ---------------------------------------------------------------------
    // Helper
    // ---------------------------------------------------------------------

    private function authorizeEdit(): void
    {
        $role = strtolower((string) (auth()->user()->role ?? ''));

        abort_unless(in_array($role, self::EDIT_ROLES, true), 403, 'Anda tidak memiliki hak untuk mengubah data material.');
    }

    private function ensureBelongs(Project $project, $ownerProjectId): void
    {
        abort_unless((int) $ownerProjectId === (int) $project->id, 404);
    }

    private function loadItems(Project $project)
    {
        return $project->materialItems()->with('arrivals')->get();
    }

    private function validateItem(Request $request): array
    {
        return $request->validate([
            'category'      => ['required', Rule::in(array_keys(MaterialItem::$categoryLabels))],
            'name'          => ['required', 'string', 'max:150'],
            'spec'          => ['nullable', 'string', 'max:150'],
            'uom'           => ['required', 'string', 'max:20'],
            'quantity'      => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'weight'        => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'purchase_date' => ['nullable', 'date', 'after_or_equal:2000-01-01', 'before_or_equal:2099-12-31'],
        ], $this->messages(), $this->attributes());
    }

    private function messages(): array
    {
        return [
            'required'        => ':attribute wajib diisi.',
            'numeric'         => ':attribute harus berupa angka.',
            'integer'         => ':attribute tidak valid.',
            'date'            => ':attribute bukan tanggal yang valid.',
            'in'              => ':attribute tidak valid.',
            'exists'          => ':attribute tidak ditemukan pada proyek ini.',
            'min.numeric'     => ':attribute minimal :min.',
            'max.numeric'     => ':attribute terlalu besar.',
            'max.string'      => ':attribute maksimal :max karakter.',
            'after_or_equal'  => ':attribute terlalu lampau.',
            'before_or_equal' => ':attribute terlalu jauh ke depan.',
        ];
    }

    private function attributes(): array
    {
        return [
            'category'         => 'Kategori',
            'name'             => 'Nama material',
            'spec'             => 'Spec',
            'uom'              => 'UOM',
            'quantity'         => 'Quantity',
            'weight'           => 'Weight',
            'purchase_date'    => 'Tanggal diperlukan',
            'material_item_id' => 'Material',
            'arrival_date'     => 'Tanggal sampai',
            'remarks'          => 'Remarks',
        ];
    }

    // Data material dalam bentuk array datar (aman ditampilkan di Audit Trail)
    private function itemSnapshot(MaterialItem $item): array
    {
        return [
            'kategori' => MaterialItem::$categoryLabels[$item->category] ?? $item->category,
            'nama'     => $item->name,
            'spec'     => $item->spec,
            'uom'      => $item->uom,
            'qty'      => $item->quantity,
            'weight'   => $item->weight,
            'tgl_diperlukan' => $item->purchase_date ? $item->purchase_date->format('Y-m-d') : null,
        ];
    }

    private function log(Project $project, string $action, string $description, ?array $old = null, ?array $new = null): void
    {
        $user = auth()->user();

        ActivityLog::create([
            'user_id'     => $user->id,
            'project_id'  => $project->id,
            'action'      => $action,
            'department'  => strtolower((string) ($user->role ?? 'procurement')),
            'description' => $description,
            'old_values'  => $old,
            'new_values'  => $new,
            'ip_address'  => request()->ip(),
        ]);
    }

    private function respond(Project $project, string $message, int $status = 200): JsonResponse
    {
        // Data yang tertaut ke Material Radar List ikut disamakan (qty, tanggal diperlukan, kedatangan)
        foreach ($project->materialItems()->whereNotNull('radar_item_id')->get() as $linked) {
            MaterialRadarSync::push($linked);
        }

        // Persen Raw Material otomatis mengikuti persen barang datang
        $project->syncMainMaterialProgress(auth()->id());

        return response()->json(array_merge(['message' => $message], $this->payload($project)), $status);
    }

    private function payload(Project $project): array
    {
        $items  = $this->loadItems($project);
        $report = MaterialReport::build($items);

        $itemRows = $items->map(function (MaterialItem $item) {
            return [
                'id'             => $item->id,
                'category'       => $item->category,
                'category_label' => MaterialItem::$categoryLabels[$item->category] ?? $item->category,
                'name'           => $item->name,
                'spec'           => $item->spec,
                'uom'            => $item->uom,
                'quantity'       => $item->quantity,
                'weight'         => $item->weight,
                'purchase_date'  => $item->purchase_date ? $item->purchase_date->format('Y-m-d') : null,
                'radar_item_id'  => $item->radar_item_id,
                'actual'         => $item->actual,
                'deviation'      => $item->deviation,
            ];
        })->values()->all();

        $arrivalRows = $items->flatMap(function (MaterialItem $item) {
            return $item->arrivals->map(function (MaterialArrival $arrival) use ($item) {
                return [
                    'id'                 => $arrival->id,
                    'item_id'            => $item->id,
                    'item_name'          => $item->name,
                    'category'           => $item->category,
                    'uom'                => $item->uom,
                    'arrival_date'       => $arrival->arrival_date->format('Y-m-d'),
                    'arrival_date_label' => $arrival->arrival_date->format('d M Y'),
                    'quantity'           => $arrival->quantity,
                    'remarks'            => $arrival->remarks,
                ];
            });
        })->sortByDesc(function (array $row) {
            return $row['arrival_date'] . '-' . str_pad((string) $row['id'], 10, '0', STR_PAD_LEFT);
        })->values()->all();

        return [
            'items'       => $itemRows,
            'arrivals'    => $arrivalRows,
            'report_html' => view('projects.partials.material-report', ['report' => $report, 'interactive' => true])->render(),
            'summary'     => MaterialReport::summary($items),
        ];
    }
}