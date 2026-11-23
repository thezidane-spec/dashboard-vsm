<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Summary Report: rekap semua proyek per divisi/tahap, detail per jobdesk, bisa difilter
 * tahun & periode bulan.
 *
 * Tabel yang sudah aktif (5): Engineering, Production - Tahap Preparation, Production - Tahap
 * Production, Finishing -- keempatnya menampilkan checklist jobdesk detail (persis seperti di
 * halaman edit proyek). PPIC ditampilkan sebagai tabel MASTER (rollup seluruh divisi dalam satu
 * baris per proyek), karena PPIC yang memantau progres keseluruhan proyek.
 *
 * Catatan: referensi Anda punya beberapa kolom yang datanya belum ada di aplikasi (Time
 * Consumption Day/Hour, Project Delay Day) -- kolom itu SENGAJA belum dibuat, lihat pesan chat.
 *
 * Material, S-Curve, dan Remarks BELUM memakai controller ini.
 */
class ReportController extends Controller
{
    public static array $reportKeys = [
        'ppic'            => ['label' => 'PPIC',                           'icon' => 'bi-calendar3',              'mode' => 'master'],
        'engineering'     => ['label' => 'Engineering',                    'icon' => 'bi-gear-wide-connected',    'mode' => 'tasks'],
        'preparation'     => ['label' => 'Production - Tahap Preparation', 'icon' => 'bi-pencil-square',          'mode' => 'tasks'],
        'main_production' => ['label' => 'Production - Tahap Production', 'icon' => 'bi-gear-wide-connected',    'mode' => 'tasks'],
        'qc'              => ['label' => 'Finish Process',                     'icon' => 'bi-shield-check',           'mode' => 'tasks'],
        'qaqc'            => ['label' => 'QA/QC',                          'icon' => 'bi-patch-check-fill',       'mode' => 'tasks'],
        'remarks'         => ['label' => 'Remarks Report',                 'icon' => 'bi-chat-square-text',       'mode' => 'remarks'],
        'scurve'          => ['label' => 'S-Curve',                        'icon' => 'bi-graph-up-arrow',         'mode' => 'scurve'],
    ];

    // Opsi dropdown "Tahap" di kartu PRODUCTION pada halaman index (lihat reports.index)
    public static array $productionStageOptions = [
        'preparation'     => 'Preparation',
        'main_production' => 'Production',
        'qc'              => 'Finish Process',
        'qaqc'            => 'QA/QC',
    ];

    public static array $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    // Label tiap jobdesk Engineering (urutan & bobot ikut Project::$engineeringTasks)
    public static array $engineeringLabels = [
        'task_drawing_ga'        => 'Drawing GA',
        'task_detail_drawing'    => 'Detail Drawing',
        'task_cutting_plan'      => 'Cutting Plan',
        'task_cnc_program'       => 'CNC Program',
        'task_bom'               => 'BOM',
        'task_painting_calc'     => 'Painting Calc',
        'task_design_calc'       => 'Design Calculation',
        'task_design_name_plate' => 'Design Name Plate',
        'task_design_sticker'    => 'Design Sticker',
        'task_packing_drawing'   => 'Packing Drawing',
        'task_welding_map'       => 'Welding Map',
        'task_skema_loadtest'    => 'Skema Loadtest',
    ];

    // Label tiap jobdesk Finishing / dulu QC-QA (urutan & bobot ikut Project::$qcTasks)
    public static array $qcLabels = [
        'task_blasting'     => 'Blasting',
        'task_painting'     => 'Painting',
        'task_galvanizing'  => 'Galvanizing',
        'task_finishing_after_galv' => 'Finishing After Galvanized',
        'task_electro_plating' => 'Electro Plating',
        'task_load_test'    => 'Load Test Preparation',
        'task_leak_test'    => 'Leak Test Preparation',
        'task_packing'      => 'Packing',
    ];

    // Label jobdesk divisi QA/QC (MDR & NDT, dipisah dari Finishing)
    public static array $qaqcLabels = [
        'task_material_inspection' => 'Material Incoming Check',
        'task_qa_load_test'        => 'Material Dimension Check',
        'task_qa_another_test'     => 'Load Test',
        'task_qa_leak_test'        => 'Leak Test',
        'task_mdr' => 'MDR',
        'task_ndt' => 'NDT',
        'task_welding_report'    => 'Welding Check',
        'task_painting_report'   => 'Painting Check',
        'task_galvanized_report' => 'Galvanized Report',
        'task_inspection_plan'   => 'Inspection Test Plan',
        'task_inspection_customer' => 'Inspections by Customer',
    ];

    // ==========================================
    // Struktur tabel MASTER PPIC: 4 kelompok -- Preparation 27%, Production 50%, Finishing 17%, QA/QC 6% (BAPP 2% kini di PPIC).
    // Bobot ini SEKARANG SAMA dengan bobot asli aplikasi (Project::$engineeringTasks dkk),
    // jadi kolom "Total Percentage" di sini sama persis dengan yang tampil di halaman edit
    // proyek / dashboard. Bukan lagi kalkulasi terpisah khusus report.
    // ==========================================
    public static array $masterGroups = [
        'preparation' => [
            'label'  => 'Preparation',
            'weight' => 27,
            // divisi di dalamnya + bobot ASLI-nya (dipakai untuk menghitung Total Percentage
            // kelompok ini secara proporsional, urutan kolom: PPIC, Engineering, Procurement)
            'divisions' => [
                'ppic'        => ['label' => 'PPIC',        'weight' => 7],
                'engineering' => ['label' => 'Engineering', 'weight' => 10],
                'procurement' => ['label' => 'Procurement', 'weight' => 10],
            ],
        ],
        'production' => [
            'label'  => 'Production',
            'weight' => 50,
            // Total Percentage kelompok ini = progress_production (accessor, sudah 0-100%)
        ],
        'qc' => [
            'label'  => 'Finish Process',
            'weight' => 17,
            // Total Percentage kelompok ini = progress_qc (accessor, sudah 0-100%)
        ],
        'qaqc' => [
            'label'  => 'QA/QC',
            'weight' => 6,
            // Total Percentage kelompok ini = progress_qaqc (accessor, sudah 0-100%)
        ],
    ];

    // Divisi kelompok Preparation dengan bobot dari model (selalu sinkron dengan bobot jobdesk)
    public static function prepDivisions(): array
    {
        $divisions = self::$masterGroups['preparation']['divisions'];
        $divisions['ppic']['weight']        = array_sum(Project::$ppicTasks);
        $divisions['engineering']['weight'] = array_sum(Project::$engineeringTasks);
        $divisions['procurement']['weight'] = array_sum(Project::$procurementTasks);

        return $divisions;
    }

    // Halaman utama Summary Report: 6 kartu (PPIC, Remarks, Material, S-Curve, Engineering, Production)
    public function index(): View
    {
        $projects = Project::orderBy('project_name')->get(['id', 'project_code', 'project_name']);

        return view('reports.index', [
            'projects'               => $projects,
            'productionStageOptions' => self::$productionStageOptions,
        ]);
    }

    // Daftar jobdesk (key => ['label'=>.., 'weight'=>..]) untuk report ber-mode 'tasks'
    private function tasksFor(string $key): array
    {
        if ($key === 'engineering') {
            $tasks = [];
            foreach (Project::$engineeringTasks as $taskKey => $weight) {
                $tasks[$taskKey] = ['label' => self::$engineeringLabels[$taskKey], 'weight' => $weight];
            }
            return $tasks;
        }

        if ($key === 'qc') {
            $tasks = [];
            foreach (Project::$qcTasks as $taskKey => $weight) {
                $tasks[$taskKey] = ['label' => self::$qcLabels[$taskKey], 'weight' => $weight];
            }
            return $tasks;
        }

        if ($key === 'qaqc') {
            $tasks = [];
            foreach (Project::$qaqcTasks as $taskKey => $weight) {
                $tasks[$taskKey] = ['label' => self::$qaqcLabels[$taskKey], 'weight' => $weight];
            }
            return $tasks;
        }

        if (in_array($key, ['preparation', 'main_production'], true)) {
            $tasks = [];
            foreach (Project::$productionStages[$key]['tasks'] as $taskKey => $weight) {
                $tasks[$taskKey] = ['label' => Project::$productionTaskInfo[$taskKey]['name'], 'weight' => $weight];
            }
            return $tasks;
        }

        return [];
    }

    public function show(Request $request, string $key): View
    {
        abort_unless(array_key_exists($key, self::$reportKeys), 404);
        $meta = self::$reportKeys[$key];

        // Kolom "Due Date" dipakai untuk filter tahun/bulan: divisi/tahap masing-masing,
        // KECUALI tabel master PPIC yang memakai due date UTAMA proyek (selalu terisi).
        $dueAttr = in_array($meta['mode'], ['master', 'remarks', 'scurve'], true) ? 'due_date' : "due_date_{$key}";

        $availableYears = Project::whereNotNull($dueAttr)
            ->selectRaw("DISTINCT YEAR($dueAttr) as y")
            ->pluck('y')
            ->map(fn ($y) => (int) $y);

        $currentYear = (int) now()->format('Y');
        if (!$availableYears->contains($currentYear)) {
            $availableYears->push($currentYear);
        }
        $availableYears = $availableYears->sortDesc()->values();

        $showAllYears = $request->input('year') === 'all';
        $year         = (!$showAllYears && $request->filled('year')) ? (int) $request->input('year') : $currentYear;
        $month        = $request->filled('month') ? (int) $request->input('month') : null;

        $query = Project::query();
        if (!$showAllYears) {
            $query->whereNotNull($dueAttr)->whereYear($dueAttr, $year);
            if ($month) {
                $query->whereMonth($dueAttr, $month);
            }
        }
        $projects = $query->orderBy($dueAttr)->orderBy('project_name')->get();

        $filters = [
            'availableYears' => $availableYears,
            'selectedYear'   => $showAllYears ? 'all' : $year,
            'selectedMonth'  => $month,
            'monthNames'     => self::$monthNames,
        ];

        if ($meta['mode'] === 'remarks') {
            return $this->renderRemarks($projects, $meta, $filters);
        }

        if ($meta['mode'] === 'scurve') {
            return $this->renderScurve($projects, $meta, $filters);
        }

        if ($meta['mode'] === 'master') {
            return $this->renderMaster($projects, $meta, $filters);
        }

        return $this->renderTasks($key, $projects, $meta, $filters);
    }

    // Tabel detail jobdesk: Engineering, Preparation, Production, Finishing
    private function renderTasks(string $key, $projects, array $meta, array $filters): View
    {
        $tasks        = $this->tasksFor($key);

        // Report Engineering: kolom mengikuti jobdesk yang dicentang di proyek-proyek yang tampil
        // (gabungan semua proyek). Bobot divisi tetap 10% walau sebagian jobdesk dimatikan.
        if (in_array($key, ['engineering', 'preparation', 'main_production', 'qc', 'qaqc'], true)) {
            $totalWeight = array_sum(array_column($tasks, 'weight'));
            $tasks       = array_filter($tasks, function ($task, $taskKey) use ($projects) {
                return $projects->contains(fn(Project $p) => $p->isTaskEnabled($taskKey));
            }, ARRAY_FILTER_USE_BOTH);
        } else {
            $totalWeight = array_sum(array_column($tasks, 'weight'));
        }

        $planAttr     = "plan_{$key}";
        $progressAttr = "progress_{$key}"; // accessor
        $dueAttr      = "due_date_{$key}";

        $rows = $projects->map(function (Project $project) use ($tasks, $planAttr, $progressAttr, $dueAttr, $key) {
            $taskCells = [];
            foreach ($tasks as $taskKey => $task) {
                // Persen progress jobdesk itu (0-100), BUKAN bobotnya.
                // null = jobdesk Engineering ini tidak dipakai di proyek tersebut (ditampilkan "-")
                if (!$project->isTaskEnabled($taskKey)) {
                    $taskCells[$taskKey] = null;
                    continue;
                }
                $taskCells[$taskKey] = max(0, min(100, (int) $project->$taskKey));
            }

            $due    = $project->$dueAttr;
            $plan   = (int) ($project->$planAttr ?? 0);
            $actual = (int) ($project->$progressAttr ?? 0);
            $gap    = $actual - $plan;

            return [
                'id'       => $project->id,
                'code'     => $project->project_code,
                'name'     => $project->project_name,
                'customer' => $project->customer,
                'qty'      => $project->quantity,
                'due'      => $due ? $due->format('d/m/Y') : '-',
                'tasks'    => $taskCells,
                'plan'     => $plan,
                'actual'   => $actual,
                'gap'      => $gap,
                'status'   => Project::resolveStatus($actual, $due, $plan),
            ];
        });

        // Problem divisi (Problem, Root Cause, Action, Due Date, PIC, Evidence) -- dari Remarks Report, difilter per divisi
        $remarkDivision = ['engineering' => 'engineering', 'preparation' => 'production', 'main_production' => 'production', 'qc' => 'finishing', 'qaqc' => 'qaqc'][$key] ?? null;
        $issueRows = [];
        if ($remarkDivision !== null) {
            foreach ($projects as $project) {
                foreach ($project->remarkEntries($remarkDivision) as $entry) {
                    $issueRows[] = $entry + ['project_id' => $project->id, 'code' => $project->project_code, 'name' => $project->project_name];
                }
            }
        }

        return view('reports.show', array_merge($filters, [
            'reportKey'   => $key,
            'meta'        => $meta,
            'tasks'       => $tasks,
            'totalWeight' => $totalWeight,
            'issueRows'   => $issueRows,
            'rows'        => $rows,
            'summary'     => self::summarize($rows),
        ]));
    }

    // Tabel master PPIC: rollup semua divisi per proyek, dikelompokkan Preparation(25%)/Production(50%)/Finishing(25%)
    // S-Curve: grafik rencana vs aktual untuk satu proyek terpilih + tabel ringkasan semua proyek (sesuai filter)
    private function renderScurve($projects, array $meta, array $filters): View
    {
        $projectOptions = Project::orderBy('project_code')->get(['id', 'project_code', 'project_name']);

        $selectedId = (int) request('project');
        $selected   = $selectedId ? Project::find($selectedId) : null;
        $selected   = $selected ?? $projects->first() ?? Project::orderBy('project_code')->first();

        $overview = $projects->map(function (Project $project) {
            $sc = \App\Support\SCurve::build($project);

            return [
                'id'           => $project->id,
                'code'         => $project->project_code,
                'name'         => $project->project_name,
                'due'          => $project->due_date ? $project->due_date->format('d/m/Y') : '-',
                'plan_today'   => $sc['plan_today'],
                'actual_today' => $sc['actual_today'],
                'deviation'    => $sc['deviation'],
                'status_text'  => $sc['status_text'],
                'status_type'  => $sc['status_type'],
            ];
        })->values();

        return view('reports.scurve', array_merge($filters, [
            'reportKey'      => 'scurve',
            'meta'           => $meta,
            'projectOptions' => $projectOptions,
            'selected'       => $selected,
            'sCurve'         => $selected ? \App\Support\SCurve::build($selected) : null,
            'overview'       => $overview,
        ]));
    }

    // Remarks Report seluruh proyek (tabel: info proyek + Remarks Problem + Task + PIC)
    private function renderRemarks($projects, array $meta, array $filters): View
    {
        $rows = $projects->map(fn(Project $project) => $project->remarksRow())->values();

        $summary = [
            'total'    => $rows->count(),
            'withData' => $rows->filter(fn($r) => count($r['problems']) + count($r['tasks']) + count($r['pics']) > 0)->count(),
            'problems' => $rows->sum(fn($r) => count($r['problems'])),
            'tasks'    => $rows->sum(fn($r) => count($r['tasks'])),
        ];

        return view('reports.remarks', array_merge($filters, [
            'reportKey' => 'remarks',
            'meta'      => $meta,
            'rows'      => $rows,
            'summary'   => $summary,
        ]));
    }

    private function renderMaster($projects, array $meta, array $filters): View
    {
        [$rows, $productionTasks, $qcTasks, $qaqcTasks] = $this->masterData($projects);

        return view('reports.ppic', array_merge($filters, [
            'reportKey'        => 'ppic',
            'meta'             => $meta,
            'prepDivisions'    => self::prepDivisions(),
            'productionTasks'  => $productionTasks,
            'qcTasks'          => $qcTasks,
            'qaqcTasks'        => $qaqcTasks,
            'rows'             => $rows,
            'summary'          => self::summarize($rows),
        ]));
    }

    // Data baris tabel master PPIC -- dipakai tampilan, Export Excel, dan Export PDF
    private function masterData($projects): array
    {
        $productionTasks = $this->tasksFor('preparation') + $this->tasksFor('main_production');
        $qcTasks         = $this->tasksFor('qc');
        $qaqcTasks       = $this->tasksFor('qaqc');

        // Kolom hanya untuk jobdesk yang aktif di minimal satu proyek yang tampil
        $anyEnabled      = fn(array $set) => array_filter($set, fn($task, $k) => $projects->contains(fn(Project $p) => $p->isTaskEnabled($k)), ARRAY_FILTER_USE_BOTH);
        $productionTasks = $anyEnabled($productionTasks);
        $qcTasks         = $anyEnabled($qcTasks);
        $qaqcTasks       = $anyEnabled($qaqcTasks);

        $rows = $projects->map(function (Project $project) use ($productionTasks, $qcTasks, $qaqcTasks) {
            // --- Kelompok Preparation (25%): PPIC, Engineering, Procurement -- 3 divisi utuh ---
            $prepDivisions = self::prepDivisions();
            $prepInternalWeight = array_sum(array_column($prepDivisions, 'weight')); // 15+20+10 = 45
            $prepAchieved = 0;
            $prepPercents = [];
            foreach ($prepDivisions as $divKey => $div) {
                $pct = (int) $project->{"progress_{$divKey}"};
                $prepPercents[$divKey] = $pct;
                $prepAchieved += $pct * $div['weight'];
            }
            // Rata-rata tertimbang (sesuai bobot ASLI ketiga divisi itu) -> 1 angka 0-100 untuk kelompok Preparation
            $prepGroupPct = $prepInternalWeight > 0 ? (int) round($prepAchieved / $prepInternalWeight) : 0;

            // --- Kelompok Production (50%): checklist Marking..Finishing (6 job, data sama seperti report Production) ---
            $productionCells = [];
            foreach ($productionTasks as $taskKey => $task) {
                // Persen progress jobdesk itu (0-100), BUKAN bobotnya
                $productionCells[$taskKey] = $project->isTaskEnabled($taskKey) ? max(0, min(100, (int) $project->$taskKey)) : null;
            }
            $productionGroupPct = (int) $project->progress_production;

            // --- Kelompok Finishing (25%): checklist Packing..BAPP (9 job, data sama seperti report Finishing) ---
            $qcCells = [];
            foreach ($qcTasks as $taskKey => $task) {
                $qcCells[$taskKey] = $project->isTaskEnabled($taskKey) ? max(0, min(100, (int) $project->$taskKey)) : null;
            }
            $qcGroupPct = (int) $project->progress_qc;

            // --- Kelompok QA/QC (6%): MDR & NDT ---
            $qaqcCells = [];
            foreach ($qaqcTasks as $taskKey => $task) {
                $qaqcCells[$taskKey] = $project->isTaskEnabled($taskKey) ? max(0, min(100, (int) $project->$taskKey)) : null;
            }
            $qaqcGroupPct = (int) $project->progress_qaqc;

            // --- Total Percentage keseluruhan proyek: sama persis dengan actual_progress di halaman
            //     edit proyek / dashboard (bobot laporan ini sekarang identik dengan bobot asli aplikasi) ---
            $overallActual = (int) $project->actual_progress;
            $plan = (int) $project->plan_progress;
            $gap  = $overallActual - $plan;

            return [
                'id'                 => $project->id,
                'code'               => $project->project_code,
                'name'               => $project->project_name,
                'customer'           => $project->customer,
                'qty'                => $project->quantity,
                'fabricator'         => $project->fabricator,
                'contract'           => $project->contract_review ? $project->contract_review->format('d/m/Y') : '-',
                'due'                => $project->due_date ? $project->due_date->format('d/m/Y') : '-',
                'time_days'          => $project->time_consumption_days,
                'time_hours'         => $project->time_consumption_hours,
                'prep_percents'      => $prepPercents,
                'prep_group_pct'     => $prepGroupPct,
                'production_cells'   => $productionCells,
                'production_group_pct' => $productionGroupPct,
                'qc_cells'           => $qcCells,
                'qc_group_pct'       => $qcGroupPct,
                'qaqc_cells'         => $qaqcCells,
                'qaqc_group_pct'     => $qaqcGroupPct,
                'plan'               => $plan,
                'actual'             => $overallActual,
                'gap'                => $gap,
                'status'             => Project::resolveStatus($overallActual, $project->due_date, $plan, $project->actual_finish_date),
            ];
        });

        return [$rows, $productionTasks, $qcTasks, $qaqcTasks];
    }

    private static function summarize($rows): array
    {
        return [
            'total'      => $rows->count(),
            'avg_plan'   => $rows->count() ? round($rows->avg('plan'), 1) : 0,
            'avg_actual' => $rows->count() ? round($rows->avg('actual'), 1) : 0,
            'on_track'   => $rows->where('status.code', 'ON_TRACK')->count(),
            'leading'    => $rows->where('status.code', 'LEADING')->count(),
            'at_risk'    => $rows->where('status.code', 'AT_RISK')->count(),
            'delayed'    => $rows->where('status.code', 'DELAYED')->count(),
            'closed_late' => $rows->where('status.code', 'CLOSED_LATE')->count(),
        ];
    }

    // ==========================================
    // EXPORT Summary Report - PPIC (Master Schedule): Excel & PDF
    // Filter tahun/bulan sama dengan halaman report (query ?year=&month=).
    // ==========================================

    // Proyek master PPIC sesuai filter + label periode untuk judul export
    private function masterContext(Request $request): array
    {
        $showAllYears = $request->input('year') === 'all';
        $year         = (!$showAllYears && $request->filled('year')) ? (int) $request->input('year') : (int) now()->format('Y');
        $month        = (!$showAllYears && $request->filled('month')) ? (int) $request->input('month') : null;
        if ($month !== null && ($month < 1 || $month > 12)) {
            $month = null;
        }

        $query = Project::query();
        if (!$showAllYears) {
            $query->whereNotNull('due_date')->whereYear('due_date', $year);
            if ($month) {
                $query->whereMonth('due_date', $month);
            }
        }
        $projects = $query->orderBy('due_date')->orderBy('project_name')->get();

        // Semua bulan -> hanya tahun; semua tahun -> "SEMUA TAHUN"
        if ($showAllYears) {
            $period = 'SEMUA TAHUN';
        } elseif ($month) {
            $period = mb_strtoupper(self::$monthNames[$month]) . ' ' . $year;
        } else {
            $period = (string) $year;
        }

        return [$projects, $period];
    }

    // Judul banner export: baris 1 = judul + jumlah proyek, baris 2 = nama kedua PT + periode
    private static function masterHeader(int $count, string $period): array
    {
        return [
            'title'    => 'VISUAL MANAGEMENT DASHBOARD — ' . $count . ' PROJECT ONGOING',
            'subtitle' => 'PT MUDALAYA ENERGY INDONESIA & PT MUTIARA GLOBAL INDUSTRY  |  PERIODE ' . $period,
        ];
    }

    public function exportPpicExcel(Request $request)
    {
        if (!class_exists(\ZipArchive::class)) {
            abort(500, 'Ekstensi PHP "zip" belum aktif di server ini, sehingga file Excel tidak dapat dibuat. Aktifkan extension=zip pada php.ini.');
        }

        [$projects, $period]                 = $this->masterContext($request);
        [$rows, $productionTasks, $qcTasks, $qaqcTasks]  = $this->masterData($projects);
        $head                                = self::masterHeader($rows->count(), $period);

        $path = \App\Support\PpicReportXlsx::create([
            'title'           => $head['title'],
            'subtitle'        => $head['subtitle'],
            'prepDivisions'   => self::prepDivisions(),
            'productionTasks' => $productionTasks,
            'qcTasks'         => $qcTasks,
            'qaqcTasks'       => $qaqcTasks,
            'rows'            => $rows->all(),
            // kiri = PT Mudalaya (logo hijau), kanan = PT Mutiara (logo oranye)
            'logos'           => [public_path('images/logo-mudalaya-badge.png'), public_path('images/logo-mutiara-badge.png')],
        ]);

        $filename = 'Master-Schedule-PPIC-' . \Illuminate\Support\Str::slug($period) . '.xlsx';

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // EXPORT PDF: halaman siap cetak (otomatis membuka dialog Print -> pilih "Save as PDF")
    public function printPpic(Request $request): View
    {
        [$projects, $period]                 = $this->masterContext($request);
        [$rows, $productionTasks, $qcTasks, $qaqcTasks]  = $this->masterData($projects);

        return view('reports.ppic-print', array_merge(self::masterHeader($rows->count(), $period), [
            'prepDivisions'   => self::prepDivisions(),
            'productionTasks' => $productionTasks,
            'qcTasks'         => $qcTasks,
            'qaqcTasks'       => $qaqcTasks,
            'rows'            => $rows,
        ]));
    }

    // Kelas warna persen progress (sama seperti halaman Update Progress): 0-50 merah, 51-99 kuning, 100 hijau
    public static function pctClass(int $value): string
    {
        if ($value >= 100) {
            return 'pct-green';
        }

        return $value >= 51 ? 'pct-yellow' : 'pct-red';
    }
}