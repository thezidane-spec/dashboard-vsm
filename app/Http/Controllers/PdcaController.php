<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Support\SCurve;
use Carbon\Carbon;
use Illuminate\View\View;

/**
 * PDCA Control Center: papan 9 panel (status proyek, plan vs actual, due date 4 minggu, material,
 * produksi, kualitas, delivery, top problem, overdue action).
 * Panel yang datanya belum tersedia (Material Readiness, Quality/NCR, bar bulanan) berupa placeholder.
 */
class PdcaController extends Controller
{
    public function index(): View
    {
        $today    = SCurve::today();
        $projects = Project::all();

        // 1. Project status
        $statusCounts = ['ON_TRACK' => 0, 'LEADING' => 0, 'AT_RISK' => 0, 'DELAYED' => 0, 'CLOSED_LATE' => 0];
        foreach ($projects as $p) {
            $code = $p->status['code'] ?? '';
            if (isset($statusCounts[$code])) {
                $statusCounts[$code]++;
            }
        }

        // 2. Plan vs Actual (rata-rata semua proyek)
        $avgPlan   = $projects->count() ? (int) round($projects->avg(fn($p) => (int) $p->plan_progress)) : 0;
        $avgActual = $projects->count() ? (int) round($projects->avg(fn($p) => (int) $p->actual_progress)) : 0;

        // 2b. Plan vs Actual per proyek (untuk dropdown pilih proyek di panel 2)
        $perProject = $projects->sortBy('project_code')->values()->map(fn($p) => [
            'id'     => $p->id,
            'label'  => trim($p->project_code . ' - ' . $p->project_name),
            'plan'   => (int) $p->plan_progress,
            'actual' => (int) $p->actual_progress,
        ])->all();

        // 4. Material readiness: % barang yang sudah datang (qty terpenuhi / qty diminta; per item dibatasi maksimal qty diminta)
        $materialByProject = [];
        $matReq = 0.0; $matGot = 0.0; $matItems = 0;
        $matItemsAll = \App\Models\MaterialItem::with('arrivals')->get()->groupBy('project_id');
        foreach ($projects->sortBy('project_code') as $p) {
            $its = $matItemsAll->get($p->id, collect());
            $req = (float) $its->sum('quantity');
            $got = (float) $its->sum(fn($i) => min($i->actual, $i->quantity));
            $matReq += $req; $matGot += $got; $matItems += $its->count();
            $materialByProject[] = [
                'id'    => $p->id,
                'label' => trim($p->project_code . ' - ' . $p->project_name),
                'items' => $its->count(),
                'full'  => $its->filter(fn($i) => $i->actual >= $i->quantity)->count(),
                'pct'   => $req > 0 ? (int) round(min(100, $got / $req * 100)) : 0,
            ];
        }
        $materialOverall = [
            'items' => $matItems,
            'full'  => array_sum(array_column($materialByProject, 'full')),
            'pct'   => $matReq > 0 ? (int) round(min(100, $matGot / $matReq * 100)) : 0,
        ];

        // 3. Due date 4 minggu ke depan (per minggu + daftar)
        $weeks = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
        $dueList = [];
        foreach ($projects as $p) {
            if (!$p->due_date) { continue; }
            $days = (int) round(($p->due_date->copy()->startOfDay()->getTimestamp() - $today->getTimestamp()) / 86400);
            if ($days >= 0 && $days < 28) {
                $weeks[(int) floor($days / 7) + 1]++;
                $dueList[] = ['code' => $p->project_code, 'name' => $p->project_name, 'due' => $p->due_date->format('d/m'), 'days' => $days, 'actual' => (int) $p->actual_progress];
            }
        }
        usort($dueList, fn($a, $b) => $a['days'] <=> $b['days']);

        // 3b. Proyek yang SUDAH melewati due date dan belum selesai (actual < 100%), terlambat terlama dulu
        $pastDue = [];
        foreach ($projects as $p) {
            if (!$p->due_date || (int) $p->actual_progress >= 100) { continue; }
            $late = (int) round(($today->getTimestamp() - $p->due_date->copy()->startOfDay()->getTimestamp()) / 86400);
            if ($late > 0) {
                $pastDue[] = ['code' => $p->project_code, 'name' => $p->project_name, 'due' => $p->due_date->format('d/m/Y'), 'late' => $late, 'actual' => (int) $p->actual_progress];
            }
        }
        usort($pastDue, fn($a, $b) => $b['late'] <=> $a['late']);

        // 5. Production progress per tahap (actual vs planning). Baris "Preparation" = gabungan PPIC + Engineering + Procurement
        //    + Tahap Preparation produksi, dihitung tertimbang dengan bobot aslinya masing-masing.
        $prepParts = [
            ['progress_ppic', 'plan_ppic', array_sum(Project::$ppicTasks)],
            ['progress_engineering', 'plan_engineering', array_sum(Project::$engineeringTasks)],
            ['progress_procurement', 'plan_procurement', array_sum(Project::$procurementTasks)],
            ['progress_preparation', 'plan_preparation', array_sum(Project::$productionStages['preparation']['tasks'])],
        ];
        $stageDefs = ['preparation' => 'Preparation', 'main_production' => 'Production', 'qc' => 'Finish Process', 'qaqc' => 'QA/QC'];
        $stagesOf = function ($p) use ($prepParts, $stageDefs) {
            $out = [];
            foreach ($stageDefs as $key => $label) {
                if ($key === 'preparation') {
                    $w = 0; $act = 0; $pl = 0;
                    foreach ($prepParts as [$progAttr, $planAttr, $weight]) {
                        $w += $weight; $act += (float) $p->$progAttr * $weight; $pl += (float) ($p->$planAttr ?? 0) * $weight;
                    }
                    $out[] = ['label' => $label, 'actual' => $w > 0 ? (int) round($act / $w) : 0, 'plan' => $w > 0 ? (int) round($pl / $w) : 0];
                } else {
                    $out[] = ['label' => $label, 'actual' => (int) $p->{"progress_{$key}"}, 'plan' => (int) ($p->{"plan_{$key}"} ?? 0)];
                }
            }
            return $out;
        };
        $stageByProject = [];
        foreach ($projects->sortBy('project_code') as $p) {
            $stageByProject[] = ['id' => $p->id, 'label' => trim($p->project_code . ' - ' . $p->project_name), 'stages' => $stagesOf($p)];
        }
        $stages = [];
        foreach (array_values($stageDefs) as $i => $label) {
            $stages[] = [
                'label'  => $label,
                'actual' => $projects->count() ? (int) round($projects->avg(fn($p) => $stagesOf($p)[$i]['actual'])) : 0,
                'plan'   => $projects->count() ? (int) round($projects->avg(fn($p) => $stagesOf($p)[$i]['plan'])) : 0,
            ];
        }

        // 7. Delivery
        $deliveryDone = $projects->filter(fn($p) => !empty($p->actual_delivery_date))->count();
        $deliveryLate = $projects->filter(fn($p) => empty($p->actual_delivery_date) && $p->delivery_date && $p->delivery_date->copy()->startOfDay()->lt($today))->count();
        $deliveryNext = $projects->filter(fn($p) => empty($p->actual_delivery_date) && $p->delivery_date && $p->delivery_date->copy()->startOfDay()->betweenIncluded($today, $today->copy()->addDays(28)))->count();

        // 8 & 9. Dari Remarks Report
        $problems = [];
        $overdue  = [];
        foreach ($projects as $p) {
            foreach ($p->remarkEntries() as $e) {
                $divLabel = Project::$remarkDepartments[$e['division'] ?? ''] ?? '-';
                if ($e['problem'] !== '') {
                    $problems[] = ['code' => $p->project_code, 'problem' => $e['problem'], 'division' => $divLabel, 'date' => $e['date']];
                }
                if ($e['action'] !== '' && $e['due_date'] && Carbon::parse($e['due_date'])->startOfDay()->lt($today)) {
                    $overdue[] = [
                        'code' => $p->project_code, 'action' => $e['action'], 'pic' => $e['pic'], 'division' => $divLabel,
                        'due' => Carbon::parse($e['due_date'])->format('d/m/Y'),
                        'late' => (int) round(($today->getTimestamp() - Carbon::parse($e['due_date'])->startOfDay()->getTimestamp()) / 86400),
                    ];
                }
            }
        }
        // Problem terlama dulu (paling lama terbuka)
        usort($problems, fn($a, $b) => strcmp((string) $a['date'], (string) $b['date']));
        usort($overdue, fn($a, $b) => $b['late'] <=> $a['late']);

        return view('pdca.index', [
            'today'        => $today,
            'total'        => $projects->count(),
            'statusCounts' => $statusCounts,
            'avgPlan'      => $avgPlan,
            'avgActual'    => $avgActual,
            'perProject'   => $perProject,
            'materialByProject' => $materialByProject,
            'materialOverall'   => $materialOverall,
            'pastDue'      => array_slice($pastDue, 0, 6),
            'pastDueTotal' => count($pastDue),
            'weeks'        => $weeks,
            'dueList'      => array_slice($dueList, 0, 6),
            'stages'       => $stages,
            'stageByProject' => $stageByProject,
            'delivery'     => ['done' => $deliveryDone, 'late' => $deliveryLate, 'next' => $deliveryNext],
            'problems'     => array_slice($problems, 0, 5),
            'problemTotal' => count($problems),
            'overdue'      => array_slice($overdue, 0, 6),
            'overdueTotal' => count($overdue),
        ]);
    }
}