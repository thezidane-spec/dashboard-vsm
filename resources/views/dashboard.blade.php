@extends('layouts.app')

@section('title', 'VSM Dashboard - PT Mudalaya Energy Indonesia')
@section('page_title', 'Control Monitoring Proyek Ongoing')

@push('styles')
<style>
    /* CSS KHUSUS MEDIA PRINT / EXPORT PDF */
    @media print {
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        body {
            background: #ffffff !important;
            color: #000000 !important;
            padding: 0 !important;
            font-size: 8pt !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        #sidebar, 
        .topbar, 
        .no-print, 
        .btn, 
        .card-header button,
        .row.g-3.mb-4, 
        .filter-card-bar, 
        .col-aksi, 
        th:last-child, 
        td:last-child { 
            display: none !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
            background: transparent !important;
        }

        .table-responsive {
            overflow: visible !important;
        }

        .table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-top: 10px !important;
        }

        .table th, .table td {
            border: 1px solid #000000 !important;
            padding: 4px 6px !important;
            font-size: 7.5pt !important;
            color: #000000 !important;
        }

        .table thead th {
            background-color: #f1f5f9 !important;
            color: #000000 !important;
            text-align: center !important;
            font-weight: bold !important;
        }

        .badge {
            border: 1px solid #000 !important;
            color: #000 !important;
            background: transparent !important;
            font-size: 7pt !important;
            padding: 2px 4px !important;
        }
    }

    .status-badge-on-track { background: #d1fae5; color: #065f46; border: 1.5px solid #a7f3d0; }
    .status-badge-leading { background: #1e3a8a; color: #ffffff; border: 1.5px solid #1e40af; }
    .border-leading { border-color: #1e3a8a !important; }
    .text-leading { color: #1e3a8a !important; }
    .status-badge-at-risk { background: #fef3c7; color: #92400e; border: 1.5px solid #fde68a; }
    .status-badge-delayed { background: #fee2e2; color: #991b1b; border: 1.5px solid #fca5a5; }
    .print-only-header { display: none; }

    @media print {
        .print-only-header {
            display: block;
            margin: 4px 0 0;
            color: #000000;
            font-size: 8pt;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid p-0">

    <!-- HEADER RESMI PT MUDALAYA ENERGY INDONESIA -->
    <div class="text-center mb-4 pb-2 border-bottom">
        <h3 class="fw-extrabold m-0 text-uppercase" style="letter-spacing: 1px; color: #0f172a;">PT MUDALAYA ENERGY INDONESIA</h3>
        <h5 class="text-secondary fw-bold m-0 mt-1">LAPORAN CONTROL MONITORING PROYEK ONGOING</h5>
        <p class="print-only-header">
            Dicetak pada: <strong>{{ date('d F Y H:i') }} WIB</strong> | Oleh: <strong>{{ auth()->user()->name }}</strong>
        </p>
    </div>

    <!-- NOTIFIKASI KETERLAMBATAN DIVISI -->
    @if(count($lateAlerts) > 0)
    <div class="alert alert-danger border-danger-subtle shadow-sm mb-4 no-print" role="alert">
        <div class="d-flex align-items-center justify-content-between" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#lateAlertsBody" aria-expanded="true">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <strong>{{ count($lateAlerts) }} Proyek Memiliki Divisi yang Terlambat</strong>
            </div>
            <i class="bi bi-chevron-down"></i>
        </div>
        <div class="collapse show mt-3" id="lateAlertsBody">
            <ul class="list-unstyled m-0" style="max-height: 260px; overflow-y: auto;">
                @foreach($lateAlerts as $alert)
                <li class="mb-2 pb-2 {{ !$loop->last ? 'border-bottom border-danger-subtle' : '' }}">
                    <a href="{{ route('projects.edit', $alert['project_id']) }}" class="fw-bold text-danger text-decoration-none">
                        {{ $alert['project_code'] }} — {{ $alert['project_name'] }}
                    </a>
                    <div class="d-flex flex-wrap gap-2 mt-1">
                        @foreach($alert['departments'] as $dept)
                        <span class="badge bg-danger-subtle text-danger border border-danger">
                            {{ $dept['department_label'] }} terlambat {{ $dept['days_late'] }} hari (progress {{ $dept['progress'] }}%)
                        </span>
                        @endforeach
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <!-- KPI RINGKASAN VSM (HANYA MUNCUL DI LAYAR MONITOR) -->
    <div class="row g-3 mb-4 no-print">
        <div class="col">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white border-start border-4 border-dark">
                <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.75rem;">TOTAL PROJECT</small>
                <h2 class="fw-extrabold m-0 text-dark">{{ $kpi['total'] }}</h2>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white border-start border-4 border-success">
                <small class="text-success fw-bold text-uppercase d-block mb-1" style="font-size: 0.75rem;">ON TRACK</small>
                <h2 class="fw-extrabold m-0 text-success">{{ $kpi['on_track'] }}</h2>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white border-start border-4 border-leading">
                <small class="text-leading fw-bold text-uppercase d-block mb-1" style="font-size: 0.75rem;">LEADING</small>
                <h2 class="fw-extrabold m-0 text-leading">{{ $kpi['leading'] }}</h2>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white border-start border-4 border-warning">
                <small class="text-warning fw-bold text-uppercase d-block mb-1" style="font-size: 0.75rem;">AT RISK</small>
                <h2 class="fw-extrabold m-0 text-warning">{{ $kpi['at_risk'] }}</h2>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white border-start border-4 border-danger">
                <small class="text-danger fw-bold text-uppercase d-block mb-1" style="font-size: 0.75rem;">DELAYED</small>
                <h2 class="fw-extrabold m-0 text-danger">{{ $kpi['delayed'] }}</h2>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white border-start border-4" style="border-color:#ea580c !important;">
                <small class="fw-bold text-uppercase d-block mb-1" style="font-size: 0.75rem; color:#ea580c;">CLOSE TERLAMBAT</small>
                <h2 class="fw-extrabold m-0" style="color:#ea580c;">{{ $kpi['closed_late'] }}</h2>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-white border-start border-4 border-info">
                <small class="text-info fw-bold text-uppercase d-block mb-1" style="font-size: 0.75rem;">RATA-RATA ACTUAL</small>
                <h2 class="fw-extrabold m-0 text-info">{{ $kpi['avg_progress'] }}%</h2>
            </div>
        </div>
    </div>

    <!-- FILTER BAR (HANYA MUNCUL DI LAYAR MONITOR) -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 p-3 bg-white no-print filter-card-bar">
        <form method="GET" action="{{ route('dashboard') }}" class="row g-2 align-items-center">
            @if(request('department'))
                <input type="hidden" name="department" value="{{ request('department') }}">
            @endif
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Ketik kode/nama proyek..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="customer" class="form-select bg-light">
                    <option value="">-- Semua Customer --</option>
                    @foreach($customersList as $cust)
                        <option value="{{ $cust }}" {{ request('customer') == $cust ? 'selected' : '' }}>{{ $cust }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select bg-light">
                    <option value="">-- Semua Status --</option>
                    <option value="ON_TRACK" {{ request('status') == 'ON_TRACK' ? 'selected' : '' }}>ON TRACK</option>
                    <option value="LEADING" {{ request('status') == 'LEADING' ? 'selected' : '' }}>LEADING</option>
                    <option value="AT_RISK" {{ request('status') == 'AT_RISK' ? 'selected' : '' }}>AT RISK</option>
                    <option value="DELAYED" {{ request('status') == 'DELAYED' ? 'selected' : '' }}>DELAYED</option>
                    <option value="CLOSED_LATE" {{ request('status') == 'CLOSED_LATE' ? 'selected' : '' }}>CLOSE TERLAMBAT</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success w-100 fw-bold" style="background-color: var(--mei-green-brand);">
                    <i class="bi bi-funnel-fill me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>

    @php
        $userRole = strtolower(auth()->user()->role);
        $currentDept = strtolower(request('department') ?? '');
        
        // Cek Hak Akses Edit per Divisi
        $canEditDept = in_array($userRole, ['admin', 'ppic']) || 
                       ($currentDept && $userRole === $currentDept) || 
                       (in_array($currentDept, ['qc_qa', 'qaqc']) && in_array($userRole, ['qc', 'qc_qa']));
    @endphp

    <!-- TAMPILAN MODE 1: KARTU FOKUS PROGRESS DEPARTEMEN (JIKA MENU DEPARTEMENT WORKFLOW DIKLIK) -->
    @if(request('department'))
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-success text-white py-3 d-flex justify-content-between align-items-center" style="background-color: var(--mei-green-dark) !important;">
                <h6 class="fw-bold m-0 text-uppercase">
                    <i class="bi bi-diagram-3 me-2"></i> MONITORING PROGRESS DIVISI: {{ request('department') === 'qaqc' ? 'QA/QC' : strtoupper(str_replace('_', '/', request('department'))) }}
                </h6>
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light fw-bold text-success">
                    <i class="bi bi-x-circle me-1"></i> Lihat Semua Proyek
                </a>
            </div>
            <div class="card-body p-4 bg-light">
                <div class="row g-4 dept-row">
                    @forelse($projects as $project)
                    @php
                        $deptKey = strtolower(request('department'));
                        $deptProgress = match($deptKey) {
                            'engineering' => $project->progress_engineering ?? 0,
                            'procurement' => $project->progress_procurement ?? 0,
                            'ppic'        => $project->progress_ppic ?? 0,
                            'production'  => $project->progress_production ?? 0,
                            'qc_qa', 'qc' => $project->progress_qc ?? 0,
                            'qaqc'        => $project->progress_qaqc ?? 0,
                            default       => 0
                        };
                        $dueDate = match($deptKey) {
                            'engineering' => $project->due_date_engineering,
                            'procurement' => $project->due_date_procurement,
                            'ppic'        => $project->due_date_ppic,
                            'production'  => $project->due_date_production,
                            'qc_qa', 'qc' => $project->due_date_qc,
                            'qaqc'        => $project->due_date_qaqc,
                            default       => null
                        };

                        // Daftar checklist task per departemen (label => nama kolom task_*)
                        $deptTaskList = match($deptKey) {
                            'engineering' => [
                                'Drawing GA'         => 'task_drawing_ga',
                                'Detail Drawing'     => 'task_detail_drawing',
                                'Cutting Plan'       => 'task_cutting_plan',
                                'CNC Program'        => 'task_cnc_program',
                                'BOM'                => 'task_bom',
                                'Painting Calc'      => 'task_painting_calc',
                                'Design Calc'        => 'task_design_calc',
                                'Design Name Plate'  => 'task_design_name_plate',
                                'Design Sticker'     => 'task_design_sticker',
                                'Packing Drawing'    => 'task_packing_drawing',
                                'Welding Map'        => 'task_welding_map',
                                'Skema Loadtest'     => 'task_skema_loadtest',
                            ],
                            'procurement' => [
                                'Raw Material'       => 'task_main_material',
                                'Accessories'        => 'task_secondary_material',
                                'Painting'           => 'task_painting_material',
                            ],
                            'ppic' => [
                                'Master Schedule'    => 'task_master_schedule',
                                'Break Schedule'     => 'task_breakdown_schedule',
                                'PR Raw Material'     => 'task_pr',
                                'PR Purchase Part'    => 'task_vendor',
                                'Surat Jalan'        => 'task_surat_jalan',
                                'PR Painting'         => 'task_progress_report',
                                'Berita Acara'       => 'task_berita_acara',
                                'BAPP'               => 'task_bapp',
                            ],
                            'production' => [
                                'Marking'                => 'task_marking',
                                'Cutting'                => 'task_cutting',
                                'Drilling (Preparation)' => 'task_prep_drilling',
                                'Rolling'                => 'task_rolling',
                                'Bending'                => 'task_bending',
                                'Machining'              => 'task_machining',
                                'Finishing (Preparation)' => 'task_prep_finishing',
                                'Drilling (Production)'  => 'task_drilling',
                                'Fit Up'                 => 'task_fit_up',
                                'Welding'                => 'task_welding',
                                'Finishing (Production)' => 'task_finishing',
                            ],
                            'qc_qa', 'qc' => [
                                'Blasting'                  => 'task_blasting',
                                'Painting'                  => 'task_painting',
                                'Galvanizing'               => 'task_galvanizing',
                                'Finishing After Galvanized' => 'task_finishing_after_galv',
                                'Electro Plating'           => 'task_electro_plating',
                                'Load Test Preparation'     => 'task_load_test',
                                'Leak Test Preparation'     => 'task_leak_test',
                                'Packing'                   => 'task_packing',
                            ],
                            'qaqc' => [
                                'Material Incoming Check'  => 'task_material_inspection',
                                'Material Dimension Check' => 'task_qa_load_test',
                                'Load Test'           => 'task_qa_another_test',
                                'Leak Test'           => 'task_qa_leak_test',
                                'MDR'                 => 'task_mdr',
                                'NDT'                 => 'task_ndt',
                                'Welding Check'       => 'task_welding_report',
                                'Painting Check'      => 'task_painting_report',
                                'Galvanized Report'   => 'task_galvanized_report',
                                'Inspection Test Plan' => 'task_inspection_plan',
                                'Inspections by Customer' => 'task_inspection_customer',
                            ],
                            default => [],
                        };

                        // Engineering: hanya jobdesk yang dicentang (dropdown "Jobdesk") di proyek ini
                        $deptTaskList = array_filter($deptTaskList, fn($taskKey) => $project->isTaskEnabled($taskKey));
                        // Jobdesk custom QA/QC yang ditambahkan di proyek ini
                        if ($deptKey === 'qaqc') {
                            foreach ($project->customTaskInfo() as $customKey => $customInfo) {
                                if ($project->isTaskEnabled($customKey)) { $deptTaskList[$customInfo['name']] = $customKey; }
                            }
                        }
                    @endphp
                    <div class="col-md-6 col-lg-4 dept-col">
                        <div class="border rounded-3 p-3 bg-white h-100 shadow-sm d-flex flex-column justify-content-between dept-card">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge bg-secondary mb-1">{{ $project->project_code }}</span>
                                        <h6 class="fw-bold m-0 text-dark">{{ $project->project_name }}</h6>
                                        <small class="text-muted d-block">{{ $project->customer }}</small>
                                    </div>
                                    <span class="fs-4 fw-extrabold text-success">{{ $deptProgress }}%</span>
                                </div>

                                <!-- Progress Bar Divisi -->
                                <div class="progress mb-3" style="height: 10px;">
                                    <div class="progress-bar bg-success progress-bar-striped" role="progressbar" style="width: {{ $deptProgress }}%;"></div>
                                </div>

                                <!-- Checklist Ringkas Task Divisi -->
                                @if(count($deptTaskList) > 0)
                                @php $inProgressItemsForProject = $project->taskProgress->keyBy('task_key'); @endphp
                                <div class="row row-cols-2 g-1 mb-2">
                                    @foreach($deptTaskList as $taskLabel => $taskKey)
                                    @php
                                        $isDone = (bool) ($project->{$taskKey} ?? false);
                                        $progressItem = $inProgressItemsForProject->get($taskKey);
                                        $isInProgress = !$isDone && $progressItem;
                                        $iconClass = $isDone ? 'bi-check-circle-fill' : ($isInProgress ? 'bi-clock-history' : 'bi-circle');
                                        $textClass = $isDone ? 'text-success' : ($isInProgress ? 'text-warning' : 'text-muted');
                                        $tooltip = $isInProgress ? 'Dikerjakan oleh ' . ($progressItem->startedBy->name ?? 'Tidak diketahui') . ' sejak ' . $progressItem->started_at->format('d M Y') : '';
                                    @endphp
                                    <div class="col">
                                        <small class="d-flex align-items-center {{ $textClass }}" style="font-size: 0.78rem;" @if($tooltip) title="{{ $tooltip }}" data-bs-toggle="tooltip" @endif>
                                            <i class="bi {{ $iconClass }} me-1"></i>
                                            <span>{{ $taskLabel }}</span>
                                        </small>
                                    </div>
                                    @endforeach
                                </div>
                                @endif

                                <div class="d-flex justify-content-between align-items-center small text-muted border-top pt-2 mt-2">
                                    <span><i class="bi bi-calendar-event me-1"></i> Target Divisi:</span>
                                    <strong class="text-dark">{{ $dueDate ? \Carbon\Carbon::parse($dueDate)->format('d M Y') : 'TBD' }}</strong>
                                </div>
                            </div>

                            <div class="mt-3 pt-2">
                                @if($canEditDept)
                                    <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-sm btn-outline-success fw-bold w-100">
                                        <i class="bi bi-pencil-square me-1"></i> Update Progress Divisi
                                    </a>
                                @else
                                    <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-sm btn-outline-secondary fw-bold w-100">
                                        <i class="bi bi-eye-fill me-1"></i> Pantau Progress (Read-Only)
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        Tidak ada data proyek ditemukan untuk divisi ini.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    <!-- TAMPILAN MODE 2: TABEL MONITORING UTAMA UTUH (DEFAULT ALL PROJECTS) -->
    @else
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between no-print">
                <h6 class="fw-bold m-0 text-dark">
                    <i class="bi bg-success text-white p-1 rounded me-2 bi-table"></i> CONTROL MONITORING PROYEK ONGOING
                </h6>
                <button onclick="window.print()" class="btn btn-sm btn-outline-success fw-bold">
                    <i class="bi bi-printer me-1"></i> Cetak / Export PDF
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 dash-cards" style="font-size: 0.88rem;">
                    <thead class="table-light text-uppercase fw-bold" style="font-size: 0.75rem;">
                        <tr>
                            <th style="width: 40px;">NO</th>
                            <th>PROJECT CODE</th>
                            <th>PROJECT NAME</th>
                            <th>CUSTOMER</th>
                            <th class="text-center">QTY</th>
                            <th>FABRICATOR</th>
                            <th>DUE DATE</th>
                            <th>DELIVERY</th>
                            <th class="text-center">PLAN</th>
                            <th class="text-center">ACTUAL PROGRESS</th>
                            <th class="text-center">GAP</th>
                            <th class="text-center">STATUS</th>
                            <th>NOTE</th>
                            <th class="text-center col-aksi">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $index => $project)
                        <tr>
                            <td class="text-center" data-k="no">{{ $index + 1 }}</td>
                            <td data-k="code"><strong class="text-success">{{ $project->project_code }}</strong></td>
                            <td class="fw-bold" data-k="name">{{ $project->project_name }}</td>
                            <td data-k="customer"><span class="badge bg-light text-dark border">{{ $project->customer }}</span></td>
                            <td class="fw-bold text-center" data-k="qty" data-label="Qty">{{ $project->quantity ?? 1 }}</td>
                            <td data-k="fab" data-label="Fabricator">{{ $project->fabricator ?? '-' }}</td>
                            <td data-k="due" data-label="Due Date">{{ $project->due_date ? $project->due_date->format('d M Y') : '-' }}</td>
                            <td data-k="delivery" data-label="Delivery">{{ $project->delivery_date ? $project->delivery_date->format('d M Y') : '-' }}</td>
                            <td class="fw-bold text-center" data-k="plan" data-label="Plan">{{ $project->plan_progress }}%</td>
                            <td class="text-center fw-bold text-success" data-k="actual" data-label="Actual">{{ $project->actual_progress }}%</td>
                            <td class="text-center fw-bold {{ $project->gap >= 0 ? 'text-success' : 'text-danger' }}" data-k="gap" data-label="Gap">
                                {{ ($project->gap >= 0 ? '+' : '') . $project->gap }}%
                            </td>
                            <td class="text-center" data-k="status">
                                <span class="badge rounded-pill px-2 py-1 {{ $project->status['color_class'] ?? 'bg-secondary' }}">
                                    {{ $project->status['label'] ?? 'ON TRACK' }}
                                </span>
                            </td>
                            <td class="text-muted small" data-k="note" data-label="Note">{{ $project->note ?? '-' }}</td>
                            <td class="text-center col-aksi" data-k="aksi">
                                <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-sm btn-success fw-bold px-3" style="background-color: var(--mei-green-brand);">
                                    <i class="bi bi-pencil-square me-1"></i> Detail / Update
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="14" class="text-center text-muted py-4">
                                <i class="bi bi-inbox d-block fs-3 mb-2"></i>
                                Belum ada data proyek ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>

@push('scripts')
@include('partials.motion', ['motionPage' => 'dashboard'])
<script>
    // Aktifkan tooltip Bootstrap untuk info "Dikerjakan oleh..." di checklist kartu departemen
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipTriggerList.forEach(function (el) {
            new bootstrap.Tooltip(el);
        });
    });
</script>
@endpush
@endsection