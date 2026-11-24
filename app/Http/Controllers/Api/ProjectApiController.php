<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaterialRadar;
use App\Support\VsmWebhook;
use Illuminate\Http\Request;

/**
 * REST API read-only: kode project, nama project, customer, nomor BOM, nomor PO.
 * Bentuk data sama dengan webhook (lihat VsmWebhook::payload).
 */
class ProjectApiController extends Controller
{
    private function shape(MaterialRadar $r): array
    {
        $p = VsmWebhook::payload($r);
        return [
            'id'          => $r->id,
            'project'     => $p['project'],
            'bom_numbers' => $p['bom_numbers'],
            'po_numbers'  => $p['po_numbers'],
            'references'  => $p['references'],
            'updated_at'  => optional($r->updated_at)->toIso8601String(),
        ];
    }

    /** GET /api/v1/projects?search=&customer=&updated_since=&per_page= */
    public function index(Request $request)
    {
        $request->validate([
            'per_page'      => 'nullable|integer|min:1|max:200',
            'updated_since' => 'nullable|date',
        ]);

        $q = MaterialRadar::with('project')->orderBy('id');

        if ($s = trim((string) $request->query('search'))) {
            $q->where(function ($w) use ($s) {
                $w->where('project_no', 'like', "%$s%")
                  ->orWhere('project_name', 'like', "%$s%")
                  ->orWhereHas('project', fn($p) => $p->where('project_code', 'like', "%$s%")->orWhere('project_name', 'like', "%$s%"));
            });
        }
        if ($c = trim((string) $request->query('customer'))) {
            $q->where('customer', 'like', "%$c%");
        }
        if ($since = $request->query('updated_since')) {
            $q->where(function ($w) use ($since) {
                $w->where('updated_at', '>=', $since)
                  ->orWhereHas('items', fn($i) => $i->where('updated_at', '>=', $since));
            });
        }

        $page = $q->paginate((int) $request->query('per_page', 50))->withQueryString();

        return response()->json([
            'data' => $page->getCollection()->map(fn($r) => $this->shape($r))->values(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page'    => $page->lastPage(),
                'per_page'     => $page->perPage(),
                'total'        => $page->total(),
            ],
        ]);
    }

    /** GET /api/v1/projects/{code}  (kode project, mis. VSM-77) */
    public function show(string $code)
    {
        $r = MaterialRadar::with('project')
            ->where('project_no', $code)
            ->orWhereHas('project', fn($p) => $p->where('project_code', $code))
            ->first();

        if (!$r) {
            return response()->json(['message' => 'Project tidak ditemukan.'], 404);
        }
        return response()->json(['data' => $this->shape($r)]);
    }
}