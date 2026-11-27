<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectIssue;
use App\Models\ActivityLog;
use App\Models\DailyReport;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Ambil input pencarian, customer, status, dan department dari query string
        $search     = $request->get('search');
        $customer   = $request->get('customer');
        $status     = $request->get('status');
        $department = $request->get('department');

        // Ambil daftar Customer unik untuk opsi dropdown filter
        $customersList = Project::select('customer')->whereNotNull('customer')->distinct()->pluck('customer');

        // Query dasar
        $query = Project::with('taskProgress.startedBy');

        // Filter Pencarian (Kode Proyek / Nama Proyek / Customer)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('project_code', 'LIKE', "%{$search}%")
                  ->orWhere('project_name', 'LIKE', "%{$search}%")
                  ->orWhere('customer', 'LIKE', "%{$search}%");
            });
        }

        // Filter Customer
        if ($customer) {
            $query->where('customer', $customer);
        }

        // Ambil data dari database
        $projects = $query->latest()->get();

        // Filter berdasarkan Status VSM (ON_TRACK, LEADING, AT_RISK, DELAYED, CLOSED_LATE)
        if ($status) {
            $projects = $projects->filter(function($p) use ($status) {
                return isset($p->status['code']) && $p->status['code'] === $status;
            });
        }

        // Hitung KPI dinamis sesuai 3 kategori VSM baru + Rata-rata Actual Progress
        $kpi = [
            'total'        => $projects->count(),
            'on_track'     => $projects->filter(fn($p) => isset($p->status['code']) && $p->status['code'] === 'ON_TRACK')->count(),
            'leading'      => $projects->filter(fn($p) => isset($p->status['code']) && $p->status['code'] === 'LEADING')->count(),
            'at_risk'      => $projects->filter(fn($p) => isset($p->status['code']) && $p->status['code'] === 'AT_RISK')->count(),
            'delayed'      => $projects->filter(fn($p) => isset($p->status['code']) && $p->status['code'] === 'DELAYED')->count(),
            'closed_late'  => $projects->filter(fn($p) => isset($p->status['code']) && $p->status['code'] === 'CLOSED_LATE')->count(),
            'avg_progress' => round($projects->avg('actual_progress') ?? 0, 1),
        ];

        // Notifikasi keterlambatan divisi: dicek dari SEMUA proyek (tidak terpengaruh filter/search yang aktif)
        // supaya notifikasi selalu tampil lengkap berapa pun kondisi filter saat ini.
        $lateAlerts = [];
        foreach (Project::all() as $p) {
            $lateDepts = $p->getLateDepartments();
            if (count($lateDepts) > 0) {
                $lateAlerts[] = [
                    'project_id'   => $p->id,
                    'project_code' => $p->project_code,
                    'project_name' => $p->project_name,
                    'departments'  => $lateDepts,
                ];
            }
        }

        return view('dashboard', compact(
            'projects', 
            'kpi', 
            'customersList', 
            'search', 
            'customer', 
            'status', 
            'department',
            'lateAlerts'
        ));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_code'     => 'required|unique:projects',
            'project_name'     => 'required',
            'customer'         => 'required',
            'due_date'         => 'required|date',
            'contract_review'  => 'nullable|date',
            'start_production' => 'nullable|date',
            'delivery_date'    => 'nullable|date',
            'quantity'         => 'required|integer|min:1',
            'fabricator'       => 'nullable|string',
            'note'             => 'nullable|string',
        ]);

        Project::create(array_merge($request->all(), $validated));

        return redirect()->route('dashboard')->with('success', 'Proyek baru berhasil ditambahkan!');
    }

    public function edit(Project $project)
    {
        // Load relasi dailyReports, activityLogs, issues, taskCompletions, dan taskProgress (+ siapa yang mengerjakan)
        $project->load(['dailyReports', 'activityLogs', 'issues', 'taskCompletions', 'taskProgress.startedBy']);

        // Ambil data laporan harian diurutkan berdasarkan tanggal
        $reports = $project->dailyReports()->orderBy('report_date', 'asc')->get();

        // Format data untuk Chart.js
        $chartLabels = [];
        $chartActual = [];
        $chartPlan   = [];

        foreach ($reports as $report) {
            $chartLabels[] = \Carbon\Carbon::parse($report->report_date)->format('d M');
            $chartActual[] = $report->progress_percentage;
            $chartPlan[]   = $project->plan_progress; 
        }

        // Susun tanggal selesai tiap task checklist: task_key => "15 Sep 2026"
        $taskCompletionDates = $project->taskCompletions->mapWithKeys(function ($tc) {
            return [$tc->task_key => $tc->completed_at->format('d M Y')];
        });

        // Daftar task_key yang statusnya sedang "Sedang Dikerjakan"
        $inProgressTaskKeys = $project->taskProgress->pluck('task_key')->toArray();

        // Info siapa & sejak kapan tiap task sedang dikerjakan: task_key => ['name' => ..., 'since' => ...]
        $taskProgressInfo = $project->taskProgress->mapWithKeys(function ($tp) {
            return [$tp->task_key => [
                'name'  => $tp->startedBy->name ?? 'Tidak diketahui',
                'since' => $tp->started_at->format('d M Y'),
            ]];
        });

        // S-Curve rencana vs aktual proyek ini
        $sCurve = \App\Support\SCurve::build($project);
        $progressHistory = \App\Models\TaskDailyProgress::where('project_id', $project->id)->orderByDesc('log_date')->orderBy('task_key')->limit(400)->get();
        $taskLabels      = \App\Support\SCurve::taskLabels();

        return view('projects.edit', compact('project', 'chartLabels', 'chartActual', 'chartPlan', 'taskCompletionDates', 'inProgressTaskKeys', 'taskProgressInfo', 'sCurve', 'progressHistory', 'taskLabels'));
    }

    public function update(Request $request, Project $project)
    {
        $user = auth()->user();
        $role = strtolower($user->role); // engineering, procurement, ppic, production, qc_qa, admin

        // Validasi Wewenang
        $allowedRoles = ['admin', 'ppic', 'engineering', 'procurement', 'production', 'qc_qa', 'qc'];
        
        if (!in_array($role, $allowedRoles)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk mengubah data departemen ini.');
        }

        $rules = [
            'daily_note'            => 'nullable|string',
            'report_date'           => 'nullable|date',
            'due_date_engineering'  => 'nullable|date',
            'plan_engineering'      => 'nullable|integer|min:0|max:100',
            'due_date_procurement'  => 'nullable|date',
            'plan_procurement'      => 'nullable|integer|min:0|max:100',
            'due_date_ppic'         => 'nullable|date',
            'plan_ppic'             => 'nullable|integer|min:0|max:100',
            'due_date_production'  => 'nullable|date',
            'plan_production'       => 'nullable|integer|min:0|max:100',
            'due_date_preparation' => 'nullable|date',
            'plan_preparation'     => 'nullable|integer|min:0|max:100',
            'due_date_main_production'=> 'nullable|date',
            'plan_main_production' => 'nullable|integer|min:0|max:100',
            'due_date_qc'           => 'nullable|date',
            'plan_qc'               => 'nullable|integer|min:0|max:100',
            'due_date_qaqc'         => 'nullable|date',
            'plan_qaqc'             => 'nullable|integer|min:0|max:100',
        ];

        // Progress tiap jobdesk sekarang angka 0-100% (dulu dropdown Belum/Sedang/Selesai)
        foreach ([
            'task_main_material', 'task_secondary_material', 'task_painting_material',
            'task_master_schedule', 'task_breakdown_schedule', 'task_pr', 'task_vendor',
            'task_surat_jalan', 'task_progress_report', 'task_berita_acara',
            'task_drawing_ga', 'task_detail_drawing', 'task_cutting_plan', 'task_cnc_program', 'task_bom',
            'task_painting_calc', 'task_design_calc', 'task_design_name_plate', 'task_design_sticker',
            'task_packing_drawing', 'task_welding_map', 'task_skema_loadtest',
            'task_marking', 'task_cutting', 'task_drilling', 'task_fit_up', 'task_welding', 'task_finishing',
            'task_packing', 'task_blasting', 'task_painting', 'task_load_test',
            'task_leak_test', 'task_mdr', 'task_ndt', 'task_bapp',
        ] as $taskKey) {
            $rules["progress_{$taskKey}"] = 'nullable|integer|min:0|max:100';
        }

        // Due Date & Tanggal Update PER JOB, khusus Divisi Engineering (supaya update progress lebih
        // fleksibel per item, bukan cuma satu Due Date untuk seluruh divisi)
        $engineeringTaskKeys = [
            'task_drawing_ga', 'task_detail_drawing', 'task_cutting_plan', 'task_cnc_program', 'task_bom',
            'task_painting_calc', 'task_design_calc', 'task_design_name_plate', 'task_design_sticker',
            'task_packing_drawing', 'task_welding_map', 'task_skema_loadtest',
        ];
        foreach ($engineeringTaskKeys as $taskKey) {
            $rules["due_date_{$taskKey}"]    = 'nullable|date';
            $rules["last_update_{$taskKey}"] = 'nullable|date';
            $rules["start_date_{$taskKey}"]  = 'nullable|date';
        }

        // Due Date & Tanggal Update PER JOB, khusus Tahap Preparation (Marking, Cutting)
        // di Divisi Production -- pola sama seperti Engineering di atas.
        $preparationTaskKeys = ['task_marking', 'task_cutting', 'task_prep_drilling', 'task_rolling', 'task_bending', 'task_machining', 'task_prep_finishing'];
        foreach ($preparationTaskKeys as $taskKey) {
            $rules["due_date_{$taskKey}"]    = 'nullable|date';
            $rules["last_update_{$taskKey}"] = 'nullable|date';
            $rules["start_date_{$taskKey}"]  = 'nullable|date';
        }

        // Due Date & Tanggal Update PER JOB, khusus Tahap Production (Drilling, Fit Up, Welding, Finishing)
        // di Divisi Production -- pola sama seperti Tahap Preparation di atas.
        $mainProductionTaskKeys = ['task_drilling', 'task_fit_up', 'task_welding', 'task_finishing'];
        foreach ($mainProductionTaskKeys as $taskKey) {
            $rules["due_date_{$taskKey}"]    = 'nullable|date';
            $rules["last_update_{$taskKey}"] = 'nullable|date';
            $rules["start_date_{$taskKey}"]  = 'nullable|date';
        }

        // Due Date & Tanggal Update PER JOB, khusus Divisi Finishing (Packing s/d BAPP, tanpa MDR & NDT)
        $finishingTaskKeys = [
            'task_packing', 'task_blasting', 'task_painting', 'task_load_test',
            'task_leak_test', 'task_galvanizing', 'task_finishing_after_galv', 'task_electro_plating',
        ];
        // Divisi Procurement (Accessories & Painting; Raw Material otomatis dari barang datang)
        $procurementTaskKeys = ['task_secondary_material', 'task_painting_material'];
        foreach ($procurementTaskKeys as $taskKey) {
            $rules["due_date_{$taskKey}"]    = 'nullable|date';
            $rules["last_update_{$taskKey}"] = 'nullable|date';
            $rules["start_date_{$taskKey}"]  = 'nullable|date';
        }

        // Start Date Raw Material (jobdesk ini progress-nya otomatis, tapi tanggal mulainya diisi manual)
        $rules['start_date_task_main_material'] = 'nullable|date';

        // Divisi QA/QC (MDR & NDT)
        $qaqcTaskKeys = ['task_mdr', 'task_ndt', 'task_material_inspection', 'task_qa_load_test', 'task_qa_another_test', 'task_qa_leak_test', 'task_welding_report', 'task_painting_report', 'task_galvanized_report', 'task_inspection_plan', 'task_inspection_customer'];
        foreach (array_merge($finishingTaskKeys, $qaqcTaskKeys) as $taskKey) {
            $rules["due_date_{$taskKey}"]    = 'nullable|date';
            $rules["last_update_{$taskKey}"] = 'nullable|date';
            $rules["start_date_{$taskKey}"]  = 'nullable|date';
        }

        // Due Date & Tanggal Update PER JOB, khusus Divisi PPIC (Master Schedule s/d Berita Acara)
        $ppicTaskKeys = [
            'task_master_schedule',
            'task_breakdown_schedule',
            'task_pr',
            'task_vendor',
            'task_surat_jalan', 'task_progress_report', 'task_berita_acara', 'task_bapp',
        ];
        foreach ($ppicTaskKeys as $taskKey) {
            $rules["due_date_{$taskKey}"]    = 'nullable|date';
            $rules["last_update_{$taskKey}"] = 'nullable|date';
            $rules["start_date_{$taskKey}"]  = 'nullable|date';
        }

        if (in_array($role, ['admin', 'ppic'])) {
            $rules = array_merge($rules, [
                // Kode Proyek boleh dikoreksi (mis. salah ketik) selama tidak kembar dengan proyek lain
                'project_code'     => ['sometimes', 'required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('projects', 'project_code')->ignore($project->id)],
                'project_name'     => 'required',
                'customer'         => 'required',
                'due_date'         => 'required|date',
                'contract_review'  => 'nullable|date',
                'start_production' => 'nullable|date',
                'delivery_date'    => 'nullable|date',
                'quantity'         => 'required|integer|min:1',
                'fabricator'       => 'nullable|string',
                'note'             => 'nullable|string',
            ]);
        }

        // Actual Delivery Date: hak edit terpisah (admin, ppic, production)
        if (in_array($role, ['admin', 'ppic', 'production'])) {
            $rules['actual_delivery_date'] = 'nullable|date';
            // Tanggal Aktual Selesai proyek (menutup proyek; hanya bisa jika semua jobdesk 100%)
            $rules['actual_finish_date'] = 'nullable|date';
        }

        // Actual Material Arrival Date: hak edit sesuai izin Procurement
        if (in_array($role, ['admin', 'ppic', 'procurement'])) {
            $rules['actual_material_arrival_date'] = 'nullable|date';

            // Detail per barang: tanggal beli, tanggal sampai, status kirim, status kesesuaian
            foreach (['main_material', 'secondary_material', 'painting_material'] as $item) {
                $rules["task_{$item}_purchase_date"]   = 'nullable|date';
                $rules["task_{$item}_arrival_date"]    = 'nullable|date';
                $rules["task_{$item}_shipping_status"] = 'nullable|in:' . implode(',', array_keys(\App\Models\Project::$shippingStatusOptions));
                $rules["task_{$item}_match_status"]    = 'nullable|in:' . implode(',', array_keys(\App\Models\Project::$matchStatusOptions));
            }
        }

        // Status Inspection Test Plan (Open / Waiting for Customer Approval / Close / N/A) -- menggantikan input persen
        $rules['status_task_inspection_plan'] = 'nullable|in:' . implode(',', array_keys(\App\Models\Project::$inspectionStatusOptions));

        $validated = $request->validate($rules);

        // Hak edit QA/QC: Inspection Test Plan & jobdesk custom
        $canEditQaqc = in_array($role, ['admin', 'ppic', 'qc_qa']);
        if (!$canEditQaqc) {
            unset($validated['status_task_inspection_plan']);
        }

        // Jobdesk custom QA/QC (nama & deskripsi manual) -- diproses sebelum snapshot progress
        [$customList, $customIdMap, $customOld, $customNew, $customRemoved] = $this->parseCustomTasks($request, $project, $canEditQaqc);

        // Inspection Test Plan: status menentukan persen (Close = 100, Open/Waiting = 0; N/A tidak dihitung sama sekali)
        if ($canEditQaqc && !empty($validated['status_task_inspection_plan'])) {
            $request->merge(['progress_task_inspection_plan' => $validated['status_task_inspection_plan'] === 'close' ? 100 : 0]);
        }

        // Peta task_key -> departemen (dipakai untuk validasi hak akses & pencatatan riwayat)
        $taskDepartmentMap = [
            'task_main_material'      => 'procurement',
            'task_secondary_material' => 'procurement',
            'task_painting_material'  => 'procurement',
            'task_master_schedule'    => 'ppic',
            'task_breakdown_schedule' => 'ppic',
            'task_pr'                 => 'ppic',
            'task_vendor'             => 'ppic',
            'task_surat_jalan'        => 'ppic',
            'task_progress_report'    => 'ppic',
            'task_berita_acara'       => 'ppic',
            'task_drawing_ga'         => 'engineering',
            'task_detail_drawing'     => 'engineering',
            'task_cutting_plan'       => 'engineering',
            'task_cnc_program'        => 'engineering',
            'task_bom'                => 'engineering',
            'task_painting_calc'      => 'engineering',
            'task_design_calc'        => 'engineering',
            'task_design_name_plate'  => 'engineering',
            'task_design_sticker'     => 'engineering',
            'task_packing_drawing'    => 'engineering',
            'task_welding_map'        => 'engineering',
            'task_skema_loadtest'     => 'engineering',
            'task_marking'            => 'production',
            'task_cutting'            => 'production',
            'task_drilling'           => 'production',
            'task_fit_up'             => 'production',
            'task_welding'            => 'production',
            'task_finishing'          => 'production',
            'task_packing'            => 'qc_qa',
            'task_blasting'           => 'qc_qa',
            'task_painting'           => 'qc_qa',
            'task_load_test'          => 'qc_qa',
            'task_leak_test'          => 'qc_qa',
            'task_mdr'                => 'qc_qa',
            'task_ndt'                => 'qc_qa',
            'task_bapp'               => 'ppic',
            'task_prep_drilling'      => 'production',
            'task_rolling'            => 'production',
            'task_bending'            => 'production',
            'task_machining'          => 'production',
            'task_prep_finishing'     => 'production',
            'task_galvanizing'        => 'qc_qa',
            'task_finishing_after_galv' => 'qc_qa',
            'task_electro_plating'    => 'qc_qa',
            'task_material_inspection' => 'qc_qa',
            'task_qa_load_test'       => 'qc_qa',
            'task_qa_another_test'    => 'qc_qa',
            'task_qa_leak_test'       => 'qc_qa',
            'task_inspection_plan'    => 'qc_qa',
            'task_welding_report'     => 'qc_qa',
            'task_painting_report'    => 'qc_qa',
            'task_galvanized_report'  => 'qc_qa',
            'task_inspection_customer' => 'qc_qa',
        ];

        // Jobdesk Finish Process punya hak akses sendiri ('finishing'): QC/QA, PPIC, Admin DAN Production.
        foreach ($finishingTaskKeys as $finishingKey) {
            $taskDepartmentMap[$finishingKey] = 'finishing';
        }

        // Snapshot progress % lama tiap job (dipakai deteksi perubahan & kalkulasi progress)
        $oldTasks = [];
        foreach ($taskDepartmentMap as $taskKey => $dept) {
            $oldTasks[$taskKey] = (int) $project->$taskKey;
        }

        // Hak akses per departemen (siapa yang boleh mengubah progress % job-nya)
        $canEdit = [
            'procurement' => in_array($role, ['admin', 'ppic', 'procurement']),
            'ppic'        => in_array($role, ['admin', 'ppic']),
            'engineering' => in_array($role, ['admin', 'ppic', 'engineering']),
            'production'  => in_array($role, ['admin', 'ppic', 'production']),
            'qc_qa'       => in_array($role, ['admin', 'ppic', 'qc_qa']),
            'finishing'   => in_array($role, ['admin', 'ppic', 'qc_qa', 'production']),
        ];

        // Problem per divisi (diinput di halaman update progress; otomatis masuk ke Remarks Report).
        // Divisi mana yang boleh diisi mengikuti hak akses edit divisi itu.
        $qcAllowed = !empty($canEdit['qc_qa']) || $role === 'qc';
        $divisionAllowed = [
            'engineering' => !empty($canEdit['engineering']),
            'procurement' => !empty($canEdit['procurement']),
            'ppic'        => !empty($canEdit['ppic']),
            'production'  => !empty($canEdit['production']),
            'finishing'   => !empty($canEdit['finishing']) || $role === 'qc',
            'qaqc'        => $qcAllowed,
        ];
        foreach ((array) $request->input('div_problem', []) as $divKey => $divRow) {
            if (empty($divisionAllowed[$divKey]) || trim((string) ($divRow['text'] ?? '')) === '') {
                continue;
            }
            $divFile = $request->file("div_problem.{$divKey}.evidence");
            if ($divFile && ($fileError = $this->evidenceFileError($divFile))) {
                return redirect()->back()->withInput()->with('error', $fileError . ' Perubahan belum disimpan.');
            }
        }

        // Due date & planning (%) level TAHAP (Preparation / Production) diperlakukan sama seperti
        // due_date_production/plan_production: kalau role yang login tidak berhak mengubah Production,
        // buang field ini dari $validated supaya tidak ikut menimpa data yang sudah tersimpan.
        if (empty($canEdit['production'])) {
            unset(
                $validated['due_date_preparation'], $validated['plan_preparation'],
                $validated['due_date_main_production'], $validated['plan_main_production']
            );
        }

        // Due Date & Tanggal Update per job Engineering: sama seperti due date/plan Production di atas --
        // kalau role yang login tidak berhak mengubah Engineering, buang dari $validated supaya tidak
        // ikut menimpa data yang sudah tersimpan.
        if (empty($canEdit['engineering'])) {
            foreach ($engineeringTaskKeys as $taskKey) {
                unset($validated["due_date_{$taskKey}"], $validated["last_update_{$taskKey}"], $validated["start_date_{$taskKey}"]);
            }
        }

        // Due Date & Tanggal Update per job Tahap Preparation: sama seperti Engineering di atas --
        // kalau role yang login tidak berhak mengubah Production, buang dari $validated.
        if (empty($canEdit['production'])) {
            foreach ($preparationTaskKeys as $taskKey) {
                unset($validated["due_date_{$taskKey}"], $validated["last_update_{$taskKey}"], $validated["start_date_{$taskKey}"]);
            }
        }

        // Due Date & Tanggal Update per job Tahap Production: sama seperti Tahap Preparation di atas.
        if (empty($canEdit['production'])) {
            foreach ($mainProductionTaskKeys as $taskKey) {
                unset($validated["due_date_{$taskKey}"], $validated["last_update_{$taskKey}"], $validated["start_date_{$taskKey}"]);
            }
        }

        // Due Date & Tanggal Update per job Divisi Finishing & QA/QC: sama seperti divisi lain di atas.
        if (empty($canEdit['qc_qa'])) {
            unset($validated['due_date_qaqc'], $validated['plan_qaqc']);
            foreach ($qaqcTaskKeys as $taskKey) {
                unset($validated["due_date_{$taskKey}"], $validated["last_update_{$taskKey}"], $validated["start_date_{$taskKey}"]);
            }
        }
        if (empty($canEdit['finishing'])) {
            foreach ($finishingTaskKeys as $taskKey) {
                unset($validated["due_date_{$taskKey}"], $validated["last_update_{$taskKey}"], $validated["start_date_{$taskKey}"]);
            }
        }

        // Due Date & Tanggal Update per job Divisi Procurement: sama seperti divisi lain di atas.
        if (empty($canEdit['procurement'])) {
            unset($validated['start_date_task_main_material']);
            foreach ($procurementTaskKeys as $taskKey) {
                unset($validated["due_date_{$taskKey}"], $validated["last_update_{$taskKey}"], $validated["start_date_{$taskKey}"]);
            }
        }

        // Due Date & Tanggal Update per job Divisi PPIC: sama seperti divisi lain di atas.
        if (empty($canEdit['ppic'])) {
            foreach ($ppicTaskKeys as $taskKey) {
                unset($validated["due_date_{$taskKey}"], $validated["last_update_{$taskKey}"], $validated["start_date_{$taskKey}"]);
            }
        }

        // Progress (%) tiap job: 0-100, diisi manual. Kalau role tidak berhak atas departemen job itu,
        // atau field-nya memang tidak dikirim (misal form divisi lain), nilai lama dipertahankan.
        $tasks = [];
        foreach ($taskDepartmentMap as $taskKey => $dept) {
            // Raw Material (task_main_material) otomatis dari barang datang -- lihat Project::syncMainMaterialProgress
            if ($taskKey !== 'task_main_material' && !empty($canEdit[$dept]) && $request->filled("progress_{$taskKey}")) {
                $tasks[$taskKey] = max(0, min(100, (int) $request->input("progress_{$taskKey}")));
            } else {
                $tasks[$taskKey] = $oldTasks[$taskKey];
            }
        }

        // 'progress_task_xxx' hanyalah nama field di form -- sudah dipetakan ke kolom 'task_xxx' lewat $tasks di atas,
        // jadi TIDAK boleh ikut ke UPDATE (kalau ikut, MySQL error "Unknown column 'progress_task_xxx'").
        $dataToUpdate = array_filter($validated, function($key) {
            return !in_array($key, ['daily_note', 'report_date', 'pic_name'])
                && strpos($key, 'progress_task_') !== 0;
        }, ARRAY_FILTER_USE_KEY);

        // Jobdesk Engineering yang dipakai di proyek ini (dropdown "Jobdesk"). Hanya Engineering / PPIC / Admin.
        // Yang disimpan adalah daftar jobdesk yang DIMATIKAN; kosong (null) = semua aktif.
        $engineeringExtra = [];
        if (!empty($canEdit['engineering']) && $request->boolean('engineering_tasks_submitted')) {
            $allEngineeringKeys = array_keys(Project::$engineeringTasks);
            $enabledKeys        = array_values(array_intersect($allEngineeringKeys, (array) $request->input('engineering_tasks_enabled', [])));

            if (count($enabledKeys) === 0) {
                return redirect()->back()->withInput()->with('error', 'Minimal satu jobdesk Engineering harus dipilih.');
            }

            $disabledKeys = array_values(array_diff($allEngineeringKeys, $enabledKeys));
            $engineeringExtra['engineering_tasks_disabled'] = count($disabledKeys) > 0 ? $disabledKeys : null;
        }

        // Dropdown "Jobdesk" divisi lain (PPIC, Preparation, Production, Finish Process, QA/QC) -- pola sama dengan Engineering.
        // Disimpan di kolom JSON tasks_disabled: {"ppic":[...], "preparation":[...], ...}; divisi tanpa jobdesk mati tidak disimpan.
        $disabledExtra = [];
        $divisionRights = ['ppic' => 'ppic', 'preparation' => 'production', 'main_production' => 'production', 'finishing' => 'finishing', 'qaqc' => 'qc_qa'];
        $divisionNames  = ['ppic' => 'PPIC', 'preparation' => 'Preparation', 'main_production' => 'Production', 'finishing' => 'Finish Process', 'qaqc' => 'QA/QC'];
        $disabledAll    = (array) ($project->tasks_disabled ?? []);
        $disabledTouched = false;
        foreach ($divisionRights as $divKey => $rightKey) {
            if (empty($canEdit[$rightKey]) || empty($request->input("tasks_submitted.{$divKey}"))) {
                continue;
            }
            $allKeys     = array_keys(Project::taskSets()[$divKey]);
            $postedKeys  = (array) $request->input("tasks_enabled.{$divKey}", []);
            if ($divKey === 'qaqc') {
                // jobdesk custom: kunci jobdesk baru (new_N) dipetakan ke kunci permanennya
                $allKeys    = array_merge($allKeys, array_map(fn($row) => 'task_custom_' . $row['id'], $customList ?? $project->customTaskList()));
                $postedKeys = array_map(fn($key) => $customIdMap[$key] ?? $key, $postedKeys);
            }
            $enabledKeys = array_values(array_intersect($allKeys, $postedKeys));
            if (count($enabledKeys) === 0) {
                return redirect()->back()->withInput()->with('error', 'Minimal satu jobdesk ' . $divisionNames[$divKey] . ' harus dipilih.');
            }
            $off = array_values(array_diff($allKeys, $enabledKeys));
            if ($off) { $disabledAll[$divKey] = $off; } else { unset($disabledAll[$divKey]); }
            $disabledTouched = true;
        }
        if ($disabledTouched) {
            $disabledExtra['tasks_disabled'] = count($disabledAll) > 0 ? $disabledAll : null;
        }

        $customExtra = $customList === null ? [] : ['custom_tasks' => count($customList) ? $customList : null];

        $payload = array_merge($dataToUpdate, $tasks, $engineeringExtra, $disabledExtra, $customExtra);

        // Penutupan proyek: Tanggal Aktual Selesai hanya boleh diisi bila SEMUA jobdesk aktif sudah 100%.
        // Kalau belum, seluruh perubahan ditolak dan halaman menampilkan popup daftar jobdesk yang belum selesai.
        if (!empty($payload['actual_finish_date'])) {
            $probe = $project->replicate();
            $probe->forceFill($payload);
            $unfinished = $probe->unfinishedJobdesks();
            if (count($unfinished) > 0) {
                return redirect()->back()->withInput()
                    ->with('finish_blocked', $unfinished)
                    ->with('error', 'Project belum bisa diselesaikan: masih ada jobdesk yang belum 100%. Perubahan belum disimpan.');
            }
        }

        // SNAPSHOT DATA LAMA
        $oldValues = $project->only(array_keys($payload));

        // Eksekusi Update
        $project->update($payload);

        foreach ($customRemoved as $removedKey) {
            \App\Models\TaskCompletion::where('project_id', $project->id)->where('task_key', $removedKey)->delete();
            \App\Models\TaskProgress::where('project_id', $project->id)->where('task_key', $removedKey)->delete();
            \App\Models\TaskDailyProgress::where('project_id', $project->id)->where('task_key', $removedKey)->delete();
        }

        // Simpan problem per divisi -> otomatis jadi satu baris Remarks Report (Problem, Root Cause, Action, Due Date, PIC, Evidence)
        $addedProblems = [];
        foreach ((array) $request->input('div_problem', []) as $divKey => $divRow) {
            $text = trim((string) ($divRow['text'] ?? ''));
            if (empty($divisionAllowed[$divKey]) || $text === '') {
                continue;
            }

            $validDate = function ($value) {
                $value = trim((string) $value);
                return (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1])) ? $value : null;
            };

            $action = trim((string) ($divRow['action'] ?? ''));
            $picName = trim((string) ($divRow['pic'] ?? ''));
            $evidence = null;
            if ($divFile = $request->file("div_problem.{$divKey}.evidence")) {
                $evidence = $this->storeEvidence($project, $divFile);
            }

            $project->appendRemarkEntry(
                [
                    'text'       => mb_substr($text, 0, 500),
                    'date'       => $validDate($divRow['date'] ?? '') ?? \App\Support\SCurve::today()->toDateString(),
                    'root_cause' => mb_substr(trim((string) ($divRow['root_cause'] ?? '')), 0, 500),
                    'division'   => $divKey,
                    'jobdesk'    => Project::cleanRemarkJobdesk($divRow['jobdesk'] ?? '', $divKey),
                ],
                ['text' => mb_substr($action, 0, 500), 'due_date' => $action === '' ? null : $validDate($divRow['action_due'] ?? ''), 'division' => $divKey],
                ['name' => mb_substr($picName !== '' ? $picName : $user->name, 0, 100), 'department' => $divKey, 'division' => $divKey, 'evidence' => $evidence,
                    'evidence_text' => mb_substr(trim((string) ($divRow['evidence_text'] ?? '')), 0, 1000)]
            );
            $addedProblems[] = Project::$remarkDepartments[$divKey] . ': ' . mb_substr($text, 0, 80);
        }
        if (!empty($addedProblems)) {
            $project->save();
            ActivityLog::create([
                'user_id'     => $user->id,
                'project_id'  => $project->id,
                'action'      => 'ADD_DIVISION_PROBLEM',
                'department'  => $role,
                'description' => "User {$user->name} ({$role}) menambah problem divisi ke Remarks Report: " . implode(' | ', $addedProblems),
                'ip_address'  => $request->ip(),
            ]);
        }

        // ==========================================
        // CATAT / HAPUS TANGGAL SELESAI TASK (task_completions) -- HANYA saat progress TEPAT 100%
        // ==========================================
        $oldTasks = $oldTasks + $customOld;
        $tasks    = $tasks + $customNew;
        foreach ($tasks as $taskKey => $newValue) {
            $oldValue = $oldTasks[$taskKey] ?? 0;

            // Baru saja mencapai 100%: catat tanggal selesainya
            if ($newValue === 100 && $oldValue !== 100) {
                \App\Models\TaskCompletion::updateOrCreate(
                    ['project_id' => $project->id, 'task_key' => $taskKey],
                    [
                        'user_id'      => $user->id,
                        'department'   => $taskDepartmentMap[$taskKey] ?? $role,
                        'completed_at' => now(),
                    ]
                );
            }

            // Turun lagi dari 100% (mundur ke sedang dikerjakan / dibatalkan total): hapus catatan selesainya
            if ($oldValue === 100 && $newValue !== 100) {
                \App\Models\TaskCompletion::where('project_id', $project->id)
                    ->where('task_key', $taskKey)
                    ->delete();
            }
        }

        // ==========================================
        // CATAT / HAPUS STATUS "SEDANG DIKERJAKAN" (task_progress) -- berdasarkan AMBANG PERSEN:
        // 0% = belum dikerjakan, 1-99% = sedang dikerjakan, 100% = selesai
        // ==========================================
        foreach ($tasks as $taskKey => $newValue) {
            $oldValue = $oldTasks[$taskKey] ?? 0;

            $wasInProgress = $oldValue > 0 && $oldValue < 100;
            $isInProgress  = $newValue > 0 && $newValue < 100;

            // Baru saja mulai dikerjakan (sebelumnya 0% atau 100%, sekarang 1-99%): catat siapa & kapan
            if ($isInProgress && !$wasInProgress) {
                \App\Models\TaskProgress::updateOrCreate(
                    ['project_id' => $project->id, 'task_key' => $taskKey],
                    [
                        'started_by' => $user->id,
                        'department' => $taskDepartmentMap[$taskKey] ?? $role,
                        'started_at' => now(),
                    ]
                );
            }

            // Keluar dari status "sedang dikerjakan" (baik jadi selesai 100%, atau dibatalkan jadi 0%)
            if (!$isInProgress && $wasInProgress) {
                \App\Models\TaskProgress::where('project_id', $project->id)
                    ->where('task_key', $taskKey)
                    ->delete();
            }
        }

        // ==========================================
        // CATAT RIWAYAT PROGRESS HARIAN (task_daily_progress) -- bahan data S-Curve nanti.
        // Hanya dicatat kalau nilainya benar-benar berubah, supaya tidak menumpuk log kosong.
        // ==========================================
        foreach ($tasks as $taskKey => $newValue) {
            $oldValue = $oldTasks[$taskKey] ?? 0;
            if ($newValue !== $oldValue) {
                \App\Models\TaskDailyProgress::logOn($project->id, $taskKey, $newValue, $this->progressLogDate($request, $taskKey, $oldValues), $user->id);
            }
        }

        // SNAPSHOT DATA BARU & SIMPAN KE AUDIT LOG
        $newValues = $project->only(array_keys($payload));

        $changedOld = [];
        $changedNew = [];

        foreach ($newValues as $key => $newValue) {
            $oldValue = $oldValues[$key] ?? null;
            if ($oldValue != $newValue) {
                $changedOld[$key] = $oldValue;
                $changedNew[$key] = $newValue;
            }
        }

        if (!empty($changedNew) || $request->filled('daily_note')) {
            ActivityLog::create([
                'user_id'     => $user->id,
                'project_id'  => $project->id,
                'action'      => 'UPDATE_PROJECT',
                'department'  => $role,
                'description' => "User {$user->name} ({$role}) memperbarui data proyek {$project->project_code}",
                'old_values'  => $changedOld,
                'new_values'  => $changedNew,
                'ip_address'  => $request->ip(),
            ]);
        }

        // Simpan Daily Report
        if ($request->filled('daily_note')) {
            $pic = $request->filled('pic_name') ? $request->pic_name : $user->name;
            $combinedNote = '[PIC: ' . $pic . '] ' . $request->input('daily_note');

            DailyReport::create([
                'project_id'          => $project->id,
                'user_id'             => $user->id,
                'department'          => $role,
                'report_date'         => $request->input('report_date', now()->format('Y-m-d')),
                'progress_percentage' => $project->actual_progress,
                'notes'               => $combinedNote,
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Progress & target departemen berhasil diperbarui!');
    }

    public function updateMaterialWeight(Request $request, Project $project)
    {
        return $this->saveProjectWeight(
            $request,
            $project,
            'total_material_weight_kg',
            ['admin', 'ppic', 'procurement'],
            'Tonase total material',
            'procurement-weight'
        );
    }

    public function deleteMaterialWeight(Request $request, Project $project)
    {
        return $this->clearProjectWeight(
            $request,
            $project,
            'total_material_weight_kg',
            ['admin', 'ppic', 'procurement'],
            'Tonase total material',
            'procurement-weight'
        );
    }

    public function updateFinishedWeight(Request $request, Project $project)
    {
        return $this->saveProjectWeight(
            $request,
            $project,
            'finished_project_weight_kg',
            ['admin', 'ppic', 'qc_qa', 'qc'],
            'Tonase total proyek selesai',
            'finished-weight'
        );
    }

    public function deleteFinishedWeight(Request $request, Project $project)
    {
        return $this->clearProjectWeight(
            $request,
            $project,
            'finished_project_weight_kg',
            ['admin', 'ppic', 'qc_qa', 'qc'],
            'Tonase total proyek selesai',
            'finished-weight'
        );
    }

    private function saveProjectWeight(Request $request, Project $project, string $field, array $roles, string $label, string $anchor)
    {
        abort_unless(in_array(strtolower((string) $request->user()->role), $roles, true), 403);

        $validated = $request->validate([
            $field => 'required|numeric|min:0|max:999999999999.999',
        ]);

        $project->update([$field => $validated[$field]]);

        return redirect()->to(route('projects.edit', $project->id) . '#' . $anchor)
            ->with('success', "{$label} berhasil disimpan.");
    }

    private function clearProjectWeight(Request $request, Project $project, string $field, array $roles, string $label, string $anchor)
    {
        abort_unless(in_array(strtolower((string) $request->user()->role), $roles, true), 403);

        $project->update([$field => null]);

        return redirect()->to(route('projects.edit', $project->id) . '#' . $anchor)
            ->with('success', "{$label} berhasil dihapus.");
    }

    /**
     * Baca jobdesk custom QA/QC dari form (custom_tasks[ID][name|desc|progress|start|due|update|delete]).
     * Return [daftar baru | null bila tidak dikirim, peta new_N => task_custom_ID, progress lama, progress baru].
     */
    private function parseCustomTasks(Request $request, Project $project, bool $canEdit): array
    {
        if (!$canEdit || !$request->boolean('custom_tasks_submitted')) {
            return [null, [], [], [], []];
        }

        $validDate = function ($value) {
            $value = trim((string) $value);
            return (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1])) ? $value : null;
        };

        $existing = [];
        foreach ($project->customTaskList() as $row) {
            $existing[$row['id']] = $row;
        }

        $list = [];
        $idMap = [];
        $old = [];
        $new = [];
        foreach ((array) $request->input('custom_tasks', []) as $postedId => $row) {
            $postedId = (string) $postedId;
            $row = (array) $row;
            if (!empty($row['delete'])) {
                continue;
            }
            $name = mb_substr(trim((string) ($row['name'] ?? '')), 0, 120);
            if ($name === '') {
                continue;
            }

            if (isset($existing[$postedId])) {
                $id = $postedId;
            } elseif (preg_match('/^new_\d+$/', $postedId)) {
                $id = substr(bin2hex(random_bytes(5)), 0, 8);
                $idMap[$postedId] = 'task_custom_' . $id;
            } else {
                continue;
            }

            $key = 'task_custom_' . $id;
            $progress = max(0, min(100, (int) ($row['progress'] ?? 0)));
            $old[$key] = isset($existing[$id]) ? (int) ($existing[$id]['progress'] ?? 0) : 0;
            $new[$key] = $progress;

            $list[] = [
                'id'          => $id,
                'name'        => $name,
                'desc'        => mb_substr(trim((string) ($row['desc'] ?? '')), 0, 300),
                'progress'    => $progress,
                'start_date'  => $validDate($row['start'] ?? ''),
                'due_date'    => $validDate($row['due'] ?? ''),
                'last_update' => $validDate($row['update'] ?? ''),
            ];
        }

        // Jobdesk custom yang dihapus: riwayatnya dibersihkan setelah proyek berhasil disimpan
        $removed = [];
        foreach (array_keys($existing) as $id) {
            if (!in_array($id, array_column($list, 'id'), true)) {
                $removed[] = 'task_custom_' . $id;
            }
        }

        return [$list, $idMap, $old, $new, $removed];
    }

    public function destroy(Request $request, Project $project)
    {
        $user = auth()->user();
        $role = strtolower((string) $user->role);

        // Hanya admin & PPIC yang boleh menghapus proyek (dicek di server, bukan sekadar menyembunyikan tombol)
        abort_unless(in_array($role, ['admin', 'ppic']), 403, 'Hanya admin dan PPIC yang dapat menghapus proyek.');

        $label = trim("{$project->project_code} - {$project->project_name}");

        // Catat ke Activity Log. project_id dikosongkan karena log milik proyek ikut terhapus bersama proyeknya.
        ActivityLog::create([
            'user_id'     => $user->id,
            'project_id'  => null,
            'action'      => 'DELETE_PROJECT',
            'department'  => $role,
            'description' => "User {$user->name} ({$role}) menghapus proyek: {$label}",
            'old_values'  => $project->only(['id', 'project_code', 'project_name', 'customer', 'due_date']),
            'ip_address'  => $request->ip(),
        ]);

        $project->delete();

        return redirect()->route('dashboard')->with('success', "Proyek {$label} berhasil dihapus!");
    }

    // =========================================================================
    // MODUL MANAJEMEN KENDALA & BLOCKER LOG (ISSUE TRACKING)
    // =========================================================================

  // 1. SIMPAN ISSUES / KENDALA BARU (FIXED & SAFE)
    public function storeIssue(Request $request, Project $project)
    {
        $user = auth()->user();
        
        // Pastikan fallback nilai department aman & tidak null
        $dept = $user->role ? strtolower($user->role) : 'general';

        $request->validate([
            'category'    => 'required|string',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'action_plan' => 'nullable|string',
            'priority'    => 'required|in:LOW,MEDIUM,CRITICAL',
        ]);

        $issue = \App\Models\ProjectIssue::create([
            'project_id'  => $project->id,
            'user_id'     => $user->id,
            'department'  => $dept,
            'category'    => $request->category,
            'title'       => $request->title,
            'description' => $request->description,
            'action_plan' => $request->action_plan,
            'priority'    => $request->priority,
            'status'      => 'OPEN',
        ]);

        // Catat ke Activity Log
        \App\Models\ActivityLog::create([
            'user_id'     => $user->id,
            'project_id'  => $project->id,
            'action'      => 'REPORT_BLOCKER',
            'department'  => $dept,
            'description' => "User {$user->name} ({$dept}) melaporkan kendala baru [{$request->priority}]: {$request->title}",
            'new_values'  => $issue->toArray(),
            'ip_address'  => $request->ip(),
        ]);

        return redirect()->route('projects.edit', $project->id)->with('success', 'Kendala / Blocker proyek berhasil dilaporkan!');
    }

    // Tanggal catatan riwayat progress (bahan kurva aktual S-Curve).
    // Memakai isi kolom "Tanggal Update" jobdesk kalau Anda MENGUBAHNYA (mendukung input terlambat / tanggal mundur).
    // Kalau kosong, tidak diubah dari sebelumnya, atau di masa depan -> memakai hari ini.
    private function progressLogDate(Request $request, string $taskKey, array $oldValues): string
    {
        $today   = \App\Support\SCurve::today()->toDateString();
        $entered = trim((string) $request->input("last_update_{$taskKey}", ''));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $entered)) {
            return $today;
        }

        $previous    = $oldValues["last_update_{$taskKey}"] ?? null;
        $previousStr = $previous ? \Carbon\Carbon::parse($previous)->toDateString() : null;

        if ($entered === $previousStr || $entered > $today) {
            return $today;
        }

        return $entered;
    }

    // KOREKSI RIWAYAT S-CURVE: tambah / ganti titik riwayat progress jobdesk pada tanggal tertentu (admin & PPIC).
    public function storeProgressHistory(Request $request, Project $project)
    {
        $user = auth()->user();
        $role = strtolower((string) $user->role);
        abort_unless(in_array($role, ['admin', 'ppic']), 403, 'Hanya admin dan PPIC yang dapat mengoreksi riwayat progress.');

        $back   = route('projects.edit', $project->id) . '#scurve-section';
        $labels = \App\Support\SCurve::taskLabels();
        $today  = \App\Support\SCurve::today()->toDateString();

        $saved   = [];
        $summary = [];
        foreach (array_slice((array) $request->input('history', []), 0, 100) as $row) {
            $taskKey  = (string) ($row['task_key'] ?? '');
            $date     = trim((string) ($row['date'] ?? ''));
            $progress = $row['progress'] ?? '';

            // Baris kosong dilewati
            if ($taskKey === '' && $date === '' && $progress === '') {
                continue;
            }

            if (!isset($labels[$taskKey]) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $m) || !checkdate((int) $m[2], (int) $m[3], (int) $m[1])
                || !is_numeric($progress) || $progress < 0 || $progress > 100 || $date > $today) {
                return redirect()->to($back)->with('scurve_error', 'Ada baris riwayat yang tidak valid (jobdesk, tanggal sampai hari ini, dan progress 0-100 wajib diisi). Tidak ada yang disimpan.');
            }

            $saved[]   = [$taskKey, $date, (int) round((float) $progress)];
            $summary[] = $labels[$taskKey] . ' (' . \Carbon\Carbon::parse($date)->format('d/m/Y') . '): ' . (int) round((float) $progress) . '%';
        }

        if (count($saved) === 0) {
            return redirect()->to($back)->with('scurve_error', 'Tidak ada baris riwayat yang diisi.');
        }

        foreach ($saved as [$taskKey, $date, $progress]) {
            \App\Models\TaskDailyProgress::logOn($project->id, $taskKey, $progress, $date, $user->id);
        }

        ActivityLog::create([
            'user_id'     => $user->id,
            'project_id'  => $project->id,
            'action'      => 'CORRECT_PROGRESS_HISTORY',
            'department'  => $role,
            'description' => "User {$user->name} ({$role}) mengoreksi riwayat progress S-Curve: " . count($saved) . ' titik',
            'new_values'  => ['progress_history' => $summary],
            'ip_address'  => $request->ip(),
        ]);

        return redirect()->to($back)->with('scurve_status', count($saved) . ' titik riwayat progress berhasil disimpan.');
    }

    public function destroyProgressHistory(Request $request, Project $project, int $history)
    {
        $user = auth()->user();
        $role = strtolower((string) $user->role);
        abort_unless(in_array($role, ['admin', 'ppic']), 403, 'Hanya admin dan PPIC yang dapat mengoreksi riwayat progress.');

        $row  = \App\Models\TaskDailyProgress::where('project_id', $project->id)->where('id', $history)->first();
        $back = route('projects.edit', $project->id) . '#scurve-section';

        if (!$row) {
            return redirect()->to($back)->with('scurve_error', 'Titik riwayat tidak ditemukan.');
        }

        $labels = \App\Support\SCurve::taskLabels();
        $text   = ($labels[$row->task_key] ?? $row->task_key) . ' (' . $row->log_date->format('d/m/Y') . '): ' . $row->progress . '%';
        $row->delete();

        ActivityLog::create([
            'user_id'     => $user->id,
            'project_id'  => $project->id,
            'action'      => 'DELETE_PROGRESS_HISTORY',
            'department'  => $role,
            'description' => "User {$user->name} ({$role}) menghapus titik riwayat progress: {$text}",
            'old_values'  => ['progress_history' => [$text]],
            'ip_address'  => $request->ip(),
        ]);

        return redirect()->to($back)->with('scurve_status', 'Titik riwayat progress dihapus.');
    }

    // REMARKS REPORT: simpan daftar Remarks Problem (+tanggal), Task (+due date), dan PIC (+departemen) proyek.
    // Menggantikan tampilan Issue Tracking; semua role yang login boleh mengisi (sama seperti pelaporan kendala sebelumnya).
    public function updateRemarks(Request $request, Project $project)
    {
        $user = auth()->user();
        $dept = $user->role ? strtolower($user->role) : 'general';

        $cleanDate = function ($value) {
            $value = trim((string) $value);
            if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m) || !checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
                return null;
            }
            return $value;
        };

        $cleanDivision = function ($value) {
            $value = (string) $value;
            return array_key_exists($value, Project::$remarkDepartments) ? $value : null;
        };

        // Rapikan daftar: baris kosong di TENGAH dipertahankan (supaya nomor Problem/Action/PIC tetap sejajar), baris kosong di ujung dibuang
        $trim = function (array $items, callable $isBlank) {
            while (!empty($items) && $isBlank(end($items))) {
                array_pop($items);
            }
            return array_values($items);
        };

        $problems = [];
        foreach (array_slice((array) $request->input('remarks_problems', []), 0, 50, true) as $row) {
            $text = trim((string) ($row['text'] ?? ''));
            $problems[] = [
                'text'       => mb_substr($text, 0, 500),
                // Tanggal masalah: kalau kosong, pakai hari ini
                'date'       => $text === '' ? null : ($cleanDate($row['date'] ?? null) ?? \App\Support\SCurve::today()->toDateString()),
                'root_cause' => $text === '' ? '' : mb_substr(trim((string) ($row['root_cause'] ?? '')), 0, 500),
                'division'   => $cleanDivision($row['division'] ?? ''),
                'jobdesk'    => $text === '' ? null : Project::cleanRemarkJobdesk($row['jobdesk'] ?? '', $cleanDivision($row['division'] ?? '')),
            ];
        }
        $problems = $trim($problems, fn($i) => $i['text'] === '');

        $tasks = [];
        foreach (array_slice((array) $request->input('remarks_tasks', []), 0, 50, true) as $row) {
            $text = trim((string) ($row['text'] ?? ''));
            $tasks[] = [
                'text'     => mb_substr($text, 0, 500),
                'due_date' => $text === '' ? null : $cleanDate($row['due_date'] ?? null),
                'division' => $cleanDivision($row['division'] ?? ''),
            ];
        }
        $tasks = $trim($tasks, fn($i) => $i['text'] === '');

        // PIC + evidence (upload baru menggantikan file lama; tanpa upload file lama dipertahankan)
        $oldEvidencePaths = [];
        foreach ((array) ($project->remarks_pics ?? []) as $oldPic) {
            if (!empty($oldPic['evidence']['path'])) {
                $oldEvidencePaths[] = $oldPic['evidence']['path'];
            }
        }

        $backError = function ($message) use ($project) {
            return redirect()->to(route('projects.edit', $project->id) . '#remarks-section')->with('remarks_error', $message);
        };

        $pics = [];
        $newFiles = [];
        foreach (array_slice((array) $request->input('remarks_pics', []), 0, 50, true) as $i => $row) {
            $name = trim((string) ($row['name'] ?? ''));
            $picDept = (string) ($row['department'] ?? '');
            if ($name !== '' && !array_key_exists($picDept, Project::$remarkDepartments)) {
                return $backError("Pilih departemen untuk PIC \"{$name}\". Remarks Report belum disimpan.");
            }

            $evidence = null;
            if ($name !== '') {
                $keepPath = (string) ($row['evidence_path'] ?? '');
                if ($keepPath !== '' && in_array($keepPath, $oldEvidencePaths, true)) {
                    $evidence = ['path' => $keepPath, 'name' => mb_substr((string) ($row['evidence_name'] ?? basename($keepPath)), 0, 150)];
                }
                $file = $request->file("remarks_pics.{$i}.evidence");
                if ($file) {
                    if ($error = $this->evidenceFileError($file)) {
                        return $backError($error);
                    }
                    $newFiles[$i] = $file;
                }
            }

            $pics[$i] = [
                'name'       => mb_substr($name, 0, 100),
                'department' => $name === '' ? '' : $picDept,
                'division'   => $cleanDivision($row['division'] ?? ''),
                'evidence'   => $evidence,
                // Evidence berupa ketikan teks (bisa dipakai sendiri atau bersama file)
                'evidence_text' => $name === '' ? '' : mb_substr(trim((string) ($row['evidence_text'] ?? '')), 0, 1000),
            ];
        }
        // Simpan file baru hanya setelah semua validasi lolos
        foreach ($newFiles as $i => $file) {
            $pics[$i]['evidence'] = $this->storeEvidence($project, $file);
        }
        $pics = $trim(array_values($pics), fn($i) => $i['name'] === '');

        // Hapus file evidence lama yang tidak dipakai lagi
        $keptPaths = [];
        foreach ($pics as $pic) {
            if (!empty($pic['evidence']['path'])) {
                $keptPaths[] = $pic['evidence']['path'];
            }
        }
        foreach (array_diff($oldEvidencePaths, $keptPaths) as $orphan) {
            $this->deleteEvidenceFile($project, $orphan);
        }

        $oldValues = $project->only(['remarks_problems', 'remarks_tasks', 'remarks_pics']);
        $newValues = ['remarks_problems' => $problems, 'remarks_tasks' => $tasks, 'remarks_pics' => $pics];

        $project->update($newValues + ['remarks_updated_at' => now()]);

        // Catat ke Activity Log hanya kalau isinya benar-benar berubah
        if (json_encode(array_map(fn($v) => $v ?? [], $oldValues)) !== json_encode($newValues)) {
            ActivityLog::create([
                'user_id'     => $user->id,
                'project_id'  => $project->id,
                'action'      => 'UPDATE_REMARKS',
                'department'  => $dept,
                'description' => "User {$user->name} ({$dept}) memperbarui Remarks Report: " . count($problems) . ' problem, ' . count($tasks) . ' task, ' . count($pics) . ' PIC',
                'old_values'  => $oldValues,
                'new_values'  => $newValues,
                'ip_address'  => $request->ip(),
            ]);
        }

        return redirect()->to(route('projects.edit', $project->id) . '#remarks-section')->with('remarks_status', 'Remarks Report berhasil diperbarui.');
    }

    // ---- EVIDENCE (lampiran PIC di Remarks Report) ----
    // Disimpan di disk 'local' (storage/app/evidence/{project_id}/...), disajikan lewat route terautentikasi.
    private function evidenceFileError($file): ?string
    {
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'xlsx', 'docx'];
        if (!$file->isValid()) {
            return 'Upload evidence gagal. Coba lagi.';
        }
        if (!in_array(strtolower($file->getClientOriginalExtension()), $allowed, true)) {
            return 'Evidence harus berupa file PDF, JPG, PNG, WEBP, XLSX atau DOCX.';
        }
        if ($file->getSize() > 10 * 1024 * 1024) {
            return 'Ukuran file evidence maksimal 10 MB.';
        }
        return null;
    }

    private function storeEvidence(Project $project, $file): array
    {
        $ext  = strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs("evidence/{$project->id}", \Illuminate\Support\Str::random(24) . '.' . $ext, 'local');

        return ['path' => $path, 'name' => mb_substr($file->getClientOriginalName(), 0, 150)];
    }

    private function evidencePathOk(Project $project, string $path): bool
    {
        return strpos($path, "evidence/{$project->id}/") === 0 && strpos($path, '..') === false;
    }

    private function deleteEvidenceFile(Project $project, string $path): void
    {
        if ($this->evidencePathOk($project, $path)) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($path);
        }
    }

    // Buka / unduh file evidence (hanya file yang benar-benar tercatat di remarks proyek ini)
    public function showEvidence(Request $request, Project $project)
    {
        $path = (string) $request->query('path', '');
        $name = null;
        foreach ((array) ($project->remarks_pics ?? []) as $pic) {
            if (!empty($pic['evidence']['path']) && $pic['evidence']['path'] === $path) {
                $name = $pic['evidence']['name'] ?? basename($path);
            }
        }
        abort_unless($name !== null && $this->evidencePathOk($project, $path) && \Illuminate\Support\Facades\Storage::disk('local')->exists($path), 404);

        return \Illuminate\Support\Facades\Storage::disk('local')->response($path, $name);
    }

    // REMARKS REPORT: hapus SATU item (Remarks Problem / Task / PIC) dari tabel.
    // "expected" = teks item saat halaman dimuat; kalau sudah berubah (diedit pengguna lain) penghapusan dibatalkan.
    public function destroyRemarkItem(Request $request, Project $project, string $type, int $index)
    {
        $columns = ['problems' => 'remarks_problems', 'tasks' => 'remarks_tasks', 'pics' => 'remarks_pics'];
        $labels  = ['problems' => 'Remarks Problem', 'tasks' => 'Task', 'pics' => 'PIC'];
        abort_unless(isset($columns[$type]), 404);

        $column   = $columns[$type];
        $back     = route('projects.edit', $project->id) . '#remarks-section';
        $oldItems = array_values((array) ($project->$column ?? []));

        if (!isset($oldItems[$index])) {
            return redirect()->to($back)->with('remarks_error', 'Item remarks sudah tidak ada. Muat ulang halaman lalu coba lagi.');
        }

        $item  = (array) $oldItems[$index];
        $label = (string) ($item['text'] ?? ($item['name'] ?? ''));

        $expected = (string) $request->input('expected', '');
        if ($expected !== '' && $expected !== $label) {
            return redirect()->to($back)->with('remarks_error', 'Remarks sudah diubah oleh pengguna lain. Muat ulang halaman lalu coba lagi.');
        }

        // Item dikosongkan di tempat (bukan digeser) supaya nomor Problem / Action / PIC tetap sejajar; baris kosong di ujung dibuang
        $blank = [
            'problems' => ['text' => '', 'date' => null, 'root_cause' => '', 'division' => $item['division'] ?? null],
            'tasks'    => ['text' => '', 'due_date' => null, 'division' => $item['division'] ?? null],
            'pics'     => ['name' => '', 'department' => '', 'division' => $item['division'] ?? null, 'evidence' => null],
        ][$type];
        $newItems = $oldItems;
        $newItems[$index] = $blank;
        $isBlank = fn($i) => trim((string) ($i['text'] ?? ($i['name'] ?? ''))) === '';
        while (!empty($newItems) && $isBlank(end($newItems))) {
            array_pop($newItems);
        }
        $newItems = array_values($newItems);

        if ($type === 'pics' && !empty($item['evidence']['path'])) {
            $this->deleteEvidenceFile($project, $item['evidence']['path']);
        }

        $project->update([$column => $newItems, 'remarks_updated_at' => now()]);

        $user = auth()->user();
        $dept = $user->role ? strtolower($user->role) : 'general';
        ActivityLog::create([
            'user_id'     => $user->id,
            'project_id'  => $project->id,
            'action'      => 'DELETE_REMARK_ITEM',
            'department'  => $dept,
            'description' => "User {$user->name} ({$dept}) menghapus {$labels[$type]} dari Remarks Report: {$label}",
            'old_values'  => [$column => $oldItems],
            'new_values'  => [$column => $newItems],
            'ip_address'  => $request->ip(),
        ]);

        return redirect()->to($back)->with('remarks_status', "{$labels[$type]} berhasil dihapus.");
    }

    // REMARKS REPORT: kosongkan SEMUA remarks proyek (problem, task, dan PIC).
    public function clearRemarks(Request $request, Project $project)
    {
        $oldValues = $project->only(['remarks_problems', 'remarks_tasks', 'remarks_pics']);
        $newValues = ['remarks_problems' => [], 'remarks_tasks' => [], 'remarks_pics' => []];

        foreach ((array) ($project->remarks_pics ?? []) as $oldPic) {
            if (!empty($oldPic['evidence']['path'])) {
                $this->deleteEvidenceFile($project, $oldPic['evidence']['path']);
            }
        }

        $project->update($newValues + ['remarks_updated_at' => now()]);

        $user = auth()->user();
        $dept = $user->role ? strtolower($user->role) : 'general';
        ActivityLog::create([
            'user_id'     => $user->id,
            'project_id'  => $project->id,
            'action'      => 'CLEAR_REMARKS',
            'department'  => $dept,
            'description' => "User {$user->name} ({$dept}) menghapus seluruh isi Remarks Report",
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
            'ip_address'  => $request->ip(),
        ]);

        return redirect()->to(route('projects.edit', $project->id) . '#remarks-section')->with('remarks_status', 'Seluruh Remarks Report berhasil dikosongkan.');
    }

    // 2. UPDATE STATUS ISSUES (MENJADI RESOLVED / SOLVED)
    public function updateIssueStatus(Request $request, ProjectIssue $issue)
    {
        $user = auth()->user();
        $status = $request->input('status', 'RESOLVED');

        $issue->update([
            'status'      => $status,
            'resolved_at' => $status === 'RESOLVED' ? now() : null,
        ]);

        // Catat Otomatis ke Activity Log
        ActivityLog::create([
            'user_id'     => $user->id,
            'project_id'  => $issue->project_id,
            'action'      => 'RESOLVE_BLOCKER',
            'department'  => $user->role,
            'description' => "User {$user->name} mengubah status kendala '{$issue->title}' menjadi {$status}",
            'ip_address'  => $request->ip(),
        ]);

        return redirect()->back()->with('success', 'Status kendala berhasil diperbarui!');
    }
}