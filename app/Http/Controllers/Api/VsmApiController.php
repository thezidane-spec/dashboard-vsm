<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaterialRadar;
use App\Models\MaterialRadarItem;
use App\Models\Project;
use Illuminate\Http\Request;

/**
 * REST API VSM (read-only), format respons seragam:
 *   {"success": true, "message": "...", "data": {...}}
 * Endpoint: /api/vsm/summary (publik), /api/vsm/projects, /api/vsm/projects/{kode}, /api/vsm/material
 */
class VsmApiController extends Controller
{
    private function ok(string $message, $data, array $extra = [], int $code = 200)
    {
        return response()->json(array_merge(['success' => true, 'message' => $message, 'data' => $data], $extra), $code);
    }

    private function fail(string $message, int $code)
    {
        return response()->json(['success' => false, 'message' => $message], $code);
    }

    private function d($date): ?string
    {
        return $date ? $date->format('Y-m-d') : null;
    }

    private function row(Project $p): array
    {
        $plan = (int) $p->plan_progress;
        $act  = (int) $p->actual_progress;
        $st   = $p->status;

        return [
            'id'                   => $p->id,
            'project_code'         => $p->project_code,
            'project_name'         => $p->project_name,
            'customer'             => $p->customer,
            'quantity'             => $p->quantity,
            'due_date'             => $this->d($p->due_date),
            'delivery_date'        => $this->d($p->delivery_date),
            'actual_delivery_date' => $this->d($p->actual_delivery_date),
            'actual_finish_date'   => $this->d($p->actual_finish_date),
            'plan_progress'        => $plan,
            'actual_progress'      => $act,
            'gap'                  => $act - $plan,
            'status_code'          => $st['code'] ?? null,
            'status'               => $st['label'] ?? null,
        ];
    }

    /** GET /api/vsm/summary */
    public function summary()
    {
        $projects = Project::all();
        $by = fn(string $code) => $projects->filter(fn($p) => ($p->status['code'] ?? null) === $code)->count();

        $recent = $projects->sortByDesc('updated_at')->take(5)->map(fn($p) => $this->row($p))->values();

        return $this->ok('Ringkasan data VSM berhasil diambil.', [
            'projects' => [
                'total'        => $projects->count(),
                'on_track'     => $by('ON_TRACK'),
                'leading'      => $by('LEADING'),
                'at_risk'      => $by('AT_RISK'),
                'delayed'      => $by('DELAYED'),
                'closed_late'  => $by('CLOSED_LATE'),
                'avg_progress' => round($projects->avg('actual_progress') ?? 0, 1),
            ],
            'material' => [
                'total_radars'    => MaterialRadar::count(),
                'total_items'     => MaterialRadarItem::count(),
                'items_with_bom'  => MaterialRadarItem::whereNotNull('bom_no')->where('bom_no', '!=', '')->count(),
                'items_with_po'   => MaterialRadarItem::whereNotNull('po_no')->where('po_no', '!=', '')->count(),
            ],
            'recent_projects' => $recent,
        ]);
    }

    /** GET /api/vsm/projects?status=&search=&customer=&per_page= */
    public function projects(Request $request)
    {
        $request->validate(['per_page' => 'nullable|integer|min:1|max:200', 'page' => 'nullable|integer|min:1']);

        $q = Project::query()->orderBy('project_code');
        if ($s = trim((string) $request->query('search'))) {
            $q->where(fn($w) => $w->where('project_code', 'like', "%$s%")->orWhere('project_name', 'like', "%$s%"));
        }
        if ($c = trim((string) $request->query('customer'))) {
            $q->where('customer', 'like', "%$c%");
        }

        $all = $q->get();
        if ($status = strtoupper((string) $request->query('status'))) {
            $all = $all->filter(fn($p) => ($p->status['code'] ?? null) === $status)->values();
        }

        $per   = (int) $request->query('per_page', 50);
        $page  = max(1, (int) $request->query('page', 1));
        $slice = $all->slice(($page - 1) * $per, $per)->map(fn($p) => $this->row($p))->values();

        return $this->ok('Daftar project berhasil diambil.', $slice, [
            'meta' => ['current_page' => $page, 'per_page' => $per, 'total' => $all->count(), 'last_page' => max(1, (int) ceil($all->count() / $per))],
        ]);
    }

    /** GET /api/vsm/projects/{kode} */
    public function project(string $code)
    {
        $p = Project::where('project_code', $code)->first();
        if (!$p) {
            return $this->fail('Project tidak ditemukan.', 404);
        }

        $radar = MaterialRadar::where('project_id', $p->id)->orWhere('project_no', $code)->first();
        $mat   = $radar ? $radar->items()->orderBy('sort')->get(['no', 'material_code', 'description', 'req_qty', 'bom_no', 'po_no', 'po_qty', 'arrival_qty', 'required_date', 'arrival_date']) : collect();

        return $this->ok('Detail project berhasil diambil.', $this->row($p) + [
            'division_progress' => [
                'engineering' => (int) $p->progress_engineering,
                'procurement' => (int) $p->progress_procurement,
                'ppic'        => (int) $p->progress_ppic,
                'preparation' => (int) $p->progress_preparation,
                'production'  => (int) $p->progress_main_production,
                'finishing'   => (int) $p->progress_qc,
                'qaqc'        => (int) $p->progress_qaqc,
            ],
            'unfinished_jobdesks' => $p->unfinishedJobdesks(),
            'bom_numbers'         => $mat->pluck('bom_no')->filter()->unique()->values(),
            'po_numbers'          => $mat->pluck('po_no')->filter()->unique()->values(),
            'material_items'      => $mat->values(),
        ]);
    }

    /** GET /api/vsm/material?project=&search=&per_page=  — baris BOM/PO per material */
    public function material(Request $request)
    {
        $request->validate(['per_page' => 'nullable|integer|min:1|max:200']);

        $q = MaterialRadarItem::with('radar:id,project_no,project_name,customer')->orderByDesc('id');
        if ($pr = trim((string) $request->query('project'))) {
            $q->whereHas('radar', fn($r) => $r->where('project_no', $pr));
        }
        if ($s = trim((string) $request->query('search'))) {
            $q->where(fn($w) => $w->where('bom_no', 'like', "%$s%")->orWhere('po_no', 'like', "%$s%")->orWhere('description', 'like', "%$s%"));
        }
        $page = $q->paginate((int) $request->query('per_page', 50))->withQueryString();

        $data = $page->getCollection()->map(fn($i) => [
            'project_code' => $i->radar?->project_no,
            'project_name' => $i->radar?->project_name,
            'customer'     => $i->radar?->customer,
            'no'           => $i->no,
            'description'  => $i->description,
            'req_qty'      => $i->req_qty,
            'bom_no'       => $i->bom_no,
            'po_no'        => $i->po_no,
            'po_qty'       => $i->po_qty,
            'arrival_qty'  => $i->arrival_qty,
            'required_date'=> $i->required_date?->format('Y-m-d'),
            'arrival_date' => $i->arrival_date?->format('Y-m-d'),
        ])->values();

        return $this->ok('Data material (BOM/PO) berhasil diambil.', $data, [
            'meta' => ['current_page' => $page->currentPage(), 'per_page' => $page->perPage(), 'total' => $page->total(), 'last_page' => $page->lastPage()],
        ]);
    }
}