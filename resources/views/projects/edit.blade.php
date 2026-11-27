<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Progress Proyek - PT Mudalaya Energy Indonesia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --mei-bg-light: #f8fafc;
            --mei-card-bg: #ffffff;
            --mei-border-color: #cbd5e1;
            --mei-green-brand: #059669;
            --mei-green-dark: #047857;
            --mei-green-subtle: #ecfdf5;
            --mei-green-border: #a7f3d0;
            --mei-text-dark: #0f172a;
        }

        body { 
            background-color: var(--mei-bg-light); 
            color: var(--mei-text-dark); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
        }

        .custom-card {
            background: var(--mei-card-bg);
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
        }

        .section-header {
            background: linear-gradient(90deg, #ecfdf5 0%, #f0fdf4 100%);
            border-left: 5px solid var(--mei-green-brand);
            border-top: 1px solid var(--mei-green-border);
            border-right: 1px solid var(--mei-green-border);
            border-bottom: 1px solid var(--mei-green-border);
            padding: 12px 18px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 1.05rem;
            color: var(--mei-green-dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .form-label-custom {
            font-weight: 700;
            font-size: 0.82rem;
            color: #334155;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .form-control-custom {
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 0.95rem;
            font-weight: 600;
            color: #0f172a;
            background-color: #ffffff;
            width: 100%;
        }

        .form-control-readonly {
            background-color: #f1f5f9 !important;
            border: 1.5px solid #cbd5e1 !important;
            color: #334155 !important;
            font-weight: 700;
            cursor: not-allowed;
        }

        .division-header-box {
            background: #f8fafc;
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            padding: 16px;
            margin-top: 24px;
            margin-bottom: 16px;
        }

        .division-title-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1.5px solid #e2e8f0;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        .task-box {
            border: 1.5px solid #cbd5e1;
            background-color: #ffffff;
            border-radius: 12px;
            padding: 14px 16px;
            cursor: pointer;
            transition: all 0.2s ease;
            height: 100%;
        }

        .task-box.disabled-box {
            background-color: #f8fafc;
            opacity: 0.7;
            cursor: not-allowed;
        }

        .task-box.checked-active {
            border-color: var(--mei-green-brand);
            background-color: #f0fdf4;
        }

        .task-box.in-progress-active {
            border-color: #d97706;
            background-color: #fffbeb;
        }

        /* 0-50% = merah, 51-99% = kuning (in-progress-active di atas), 100% = hijau (checked-active di atas) */
        .task-box.not-started-active {
            border-color: #ef4444;
            background-color: #fef2f2;
        }

        .progress-input-group {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 2px 10px;
        }
        .progress-input-group .progress-input {
            flex: 1;
            border: 0;
            padding: 6px 0;
            font-weight: 800;
            font-size: 0.95rem;
            text-align: center;
            background: transparent;
        }
        .progress-input-group .progress-input:focus {
            outline: none;
            box-shadow: none;
        }
        .progress-input-group .progress-input:disabled {
            background: transparent;
            color: #64748b;
        }
        .task-late-badge { margin-bottom: 8px; display: inline-flex; align-items: center; gap: 4px; font-size: .7rem; font-weight: 700; padding: 2px 8px; border-radius: 999px; white-space: nowrap; border: 1px solid transparent; }
        .task-late-danger { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
        .task-late-warning { background: #fef3c7; color: #b45309; border-color: #fde68a; }
        .task-late-success { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
        .itp-status-btn { border: 1px solid #cbd5e1; background: #fff; color: #334155; text-align: left; }
        .itp-status-btn.itp-open  { background: #fef3c7; border-color: #fcd34d; color: #92400e; }
        .itp-status-btn.itp-waiting { background: #dbeafe; border-color: #93c5fd; color: #1e40af; }
        .itp-status-btn.itp-close { background: #d1fae5; border-color: #6ee7b7; color: #065f46; }
        .itp-status-btn.itp-na    { background: #f1f5f9; border-color: #cbd5e1; color: #64748b; }
        .task-late-secondary { background: #f1f5f9; color: #64748b; border-color: #e2e8f0; }
        .late-summary { border: 1px solid #fecaca; background: #fef2f2; border-radius: 10px; padding: 6px 12px; }
        .late-summary > summary { cursor: pointer; font-weight: 700; font-size: .82rem; color: #b91c1c; list-style: none; }
        .late-summary > summary::-webkit-details-marker { display: none; }
        .late-summary[open] > summary { margin-bottom: 6px; }
        .late-summary-list { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 4px 16px; font-size: .82rem; }
        .late-summary-list .task-late-badge { margin-bottom: 0; }
        .late-summary-list li { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        .progress-input-suffix {
            font-weight: 700;
            color: #94a3b8;
        }

        /* Remarks Report: daftar isian bernomor (Remark Problem / Task / PIC) */
        .remark-list { border: 1px solid #cbd5e1; border-radius: 10px; padding: 8px 10px; min-height: 130px; max-height: 250px; overflow-y: auto; background: #fff; }
        .remark-row { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
        .remark-row .remark-num { min-width: 22px; font-weight: 700; color: #475569; }
        .remark-row .remark-text { flex: 1 1 auto; min-width: 0; }
        .remark-row .remark-extra { flex: 0 0 150px; }
        .remark-row { flex-wrap: wrap; }
        .remark-row .remark-sub { flex: 1 0 100%; padding-left: 30px; }
        .remark-row .remark-file-note { font-size: 0.72rem; color: #475569; }
        .remark-row .remark-remove { border: 0; background: transparent; color: #94a3b8; padding: 0 4px; line-height: 1; }
        .remark-row .remark-remove:hover { color: #dc2626; }

        /* Centang jobdesk Engineering (dropdown "Jobdesk") */
        .eng-task-toggle { width: 1.35rem; height: 1.35rem; border-radius: 50%; cursor: pointer; }
        .eng-task-toggle:checked { background-color: #16a34a; border-color: #16a34a; }
        .eng-task-toggle:disabled { cursor: not-allowed; }

        .badge-weight-custom {
            background-color: #ecfdf5;
            color: #047857;
            font-weight: 800;
            font-size: 0.82rem;
            padding: 5px 10px;
            border-radius: 20px;
            border: 1px solid #a7f3d0;
        }

        .status-badge-on-track { background: #d1fae5; color: #065f46; border: 1.5px solid #a7f3d0; }
        .status-badge-leading { background: #1e3a8a; color: #ffffff; border: 1.5px solid #1e40af; }
        .bg-leading { background-color: #1e3a8a !important; }
        .status-badge-at-risk  { background: #fef3c7; color: #92400e; border: 1.5px solid #fde68a; }
        .status-badge-delayed  { background: #fee2e2; color: #991b1b; border: 1.5px solid #fca5a5; }

        /* Kotak TAHAP Divisi Production (Tahap Preparation / Tahap Production) */
        .prod-stage-block {
            border: 1.5px dashed #cbd5e1;
            border-radius: 12px;
            padding: 14px;
            background: #fcfefd;
        }
        .prod-stage-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 800;
            color: #047857;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 6px;
        }

        /* HEADER DOKUMEN CETAK (HANYA MUNCUL SAAT PRINT) */
        .print-only-header {
            display: none;
        }

        /* CSS KHUSUS MODE CETAK / PRINT PDF */
        @media print {
            #auditTrailCollapse { display: block !important; height: auto !important; visibility: visible !important; }

            @page {
                size: A4 portrait;
                margin: 10mm;
            }

            body {
                background: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
                font-size: 9pt !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            #sidebar, 
            .topbar, 
            .no-print, 
            .btn, 
            .header-page-bar,
            .custom-card:not(.print-area),
            #form-update-project > div:not(.print-area) {
                display: none !important;
            }

            .print-only-header {
                display: block !important;
                text-align: center;
                margin-bottom: 15px;
                border-bottom: 2px solid #000;
                padding-bottom: 8px;
            }

            .print-area {
                display: block !important;
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .table {
                width: 100% !important;
                border-collapse: collapse !important;
                margin-top: 10px !important;
            }

            .table th, .table td {
                border: 1px solid #000000 !important;
                padding: 6px 8px !important;
                font-size: 8.5pt !important;
            }

            .table thead th {
                background-color: #f1f5f9 !important;
                color: #000000 !important;
                text-align: center !important;
            }
        }
    </style>
    @include('partials.theme')
    @include('partials.responsive')
</head>
<body class="p-3 p-md-4">
    <button type="button" class="theme-toggle floating no-print" aria-label="Ganti tema" title="Tema gelap"><i class="bi bi-moon-stars-fill"></i></button>

    @php
        $role = auth()->user()->role;
        $canEditGeneral     = in_array($role, ['admin', 'ppic']);
        $canEditProcurement = in_array($role, ['admin', 'ppic', 'procurement']);
        $canEditPPIC        = in_array($role, ['admin', 'ppic']);
        $canEditEngineering = in_array($role, ['admin', 'ppic', 'engineering']);
        $canEditProduction  = in_array($role, ['admin', 'ppic', 'production']);
        $canEditDelivery    = in_array($role, ['admin', 'ppic', 'production']);
        $canEditQC          = in_array($role, ['admin', 'ppic', 'qc_qa', 'qc']);
        $canEditFinishing   = in_array($role, ['admin', 'ppic', 'qc_qa', 'qc', 'production']); // Finish Process: production juga boleh
        
        $canSubmitAny = $canEditGeneral || $canEditProcurement || $canEditPPIC || $canEditEngineering || $canEditProduction || $canEditQC || $canEditFinishing;

        // Detail Material (Raw Material & Vendor Raw Material) - popup di kotak Raw Material
        $canEditMaterial = $canEditProcurement;
        $materialSummary = \App\Support\MaterialReport::summary($project->materialItems()->with('arrivals')->get());
    @endphp

    <div class="container" style="max-width: 1050px;">
        
        <!-- HEADER KOP SURAT UNTUK MODE CETAK -->
        <div class="print-only-header">
            <h3 class="fw-bold m-0 text-uppercase">PT MUDALAYA ENERGY INDONESIA</h3>
            <h5 class="m-0 text-secondary fw-bold">LAPORAN REKAPITULASI DAILY REPORT PROYEK</h5>
            <hr class="my-2" style="border-top: 2px solid #000;">
            
            <div class="row text-start small mb-2">
                <div class="col-6">
                    <div>Kode Proyek: <strong>{{ $project->project_code }}</strong></div>
                    <div>Nama Proyek: <strong>{{ $project->project_name }}</strong></div>
                    <div>Customer: <strong>{{ $project->customer }}</strong></div>
                </div>
                <div class="col-6 text-end">
                    <div>Actual Progress: <strong>{{ $project->actual_progress }}%</strong></div>
                    <div>Status VSM: <strong>{{ $project->status['label'] }}</strong></div>
                    <div>Dicetak Pada: <strong>{{ date('d M Y H:i') }} WIB</strong></div>
                </div>
            </div>
        </div>

        <!-- Header Page Bar -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom gap-3 header-page-bar">
            <div class="d-flex align-items-center gap-3 header-page-title">
                <div class="p-3 rounded-3 text-white shadow-sm header-page-icon" style="background-color: var(--mei-green-brand);">
                    <i class="bi bi-pencil-square fs-3"></i>
                </div>
                <div>
                    <h3 class="fw-extrabold m-0" style="color: var(--mei-green-brand);">UPDATE PROGRESS PROYEK</h3>
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">VSM Proyek</span>
                        <span class="text-secondary fw-semibold">{{ $project->project_code }} — <strong class="text-dark">{{ $project->project_name }}</strong></span>
                        <span class="badge bg-success-subtle text-success border border-success ms-2">Role Login: {{ strtoupper($role) }}</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary fw-bold px-3 py-2 rounded-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
        </div>

        <!-- FORM UTAMA EDIT PROYEK -->
        <form action="{{ route('projects.update', $project->id) }}" method="POST" id="form-update-project" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- LIVE PREVIEW CALCULATION BANNER -->
            <div class="p-4 rounded-3 text-white mb-4" style="background-color: var(--mei-green-dark, #047857);">
                <div class="row align-items-center g-3">
                    <div class="col-md-7">
                        <span class="badge bg-white text-dark fw-bold mb-2">Kalkulasi Otomatis</span>
                        <small class="text-light d-inline-block ms-2">Bobot Milestone Terverifikasi</small>
                        <h2 class="fw-bold m-0 mt-1" style="color: #fef08a;">Live Actual Progress: <span id="display-actual">{{ $project->actual_progress }}%</span></h2>
                        <p class="m-0 mt-1 text-light small">Rencana Target: <strong id="display-plan">{{ $project->plan_progress }}%</strong> | Selisih (Gap): <strong id="display-gap">{{ $project->gap }}%</strong></p>
                    </div>
                    <div class="col-md-5 text-end">
                        <div class="p-3 rounded-3 text-center" style="background: rgba(0, 0, 0, 0.2);">
                            <small class="text-uppercase tracking-wider d-block mb-2 font-monospace" style="font-size: 0.75rem;">STATUS PROYEK SAAT INI</small>
                            <span id="display-status-badge" class="badge rounded-pill px-3 py-2 fs-6 fw-bold {{ $project->status['color_class'] }}">{{ $project->status['label'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="progress mt-3 bg-black bg-opacity-25" style="height: 10px; border-radius: 8px;">
                    <div id="display-progress-bar" class="progress-bar bg-warning progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $project->actual_progress }}%;"></div>
                </div>
            </div>

            <!-- SECTION 1: INFORMASI UTAMA & VSM DETAILS -->
            <div class="custom-card p-4 mb-4 bg-white rounded-3 border">
                <div class="section-header">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle-fill text-success fs-5"></i>
                        <span>1. INFORMASI UTAMA & VSM DETAILS</span>
                    </span>
                    <span class="badge bg-white text-success border border-success-subtle fw-semibold">Langkah 1</span>
                </div>

                <div class="row g-3 form-2col">
                    <div class="col-md-4">
                        <label class="form-label-custom">Kode Proyek</label>
                        @if(!$canEditGeneral) <input type="hidden" name="project_code" value="{{ $project->project_code }}"> @endif
                        <input type="text" name="project_code" class="form-control-custom {{ !$canEditGeneral ? 'form-control-readonly' : '' }}" value="{{ old('project_code', $project->project_code) }}" {{ !$canEditGeneral ? 'readonly' : 'required' }}>
                        @error('project_code') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        @if($canEditGeneral)<div class="form-text" style="font-size:.7rem">Bisa dikoreksi bila salah ketik. Kode harus unik.</div>@endif
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-custom">Nama Proyek</label>
                        @if(!$canEditGeneral) <input type="hidden" name="project_name" value="{{ $project->project_name }}"> @endif
                        <input type="text" name="project_name" class="form-control-custom {{ !$canEditGeneral ? 'form-control-readonly' : '' }}" value="{{ $project->project_name }}" {{ !$canEditGeneral ? 'disabled' : 'required' }}>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-custom">Customer / Klien</label>
                        @if(!$canEditGeneral) <input type="hidden" name="customer" value="{{ $project->customer }}"> @endif
                        <input type="text" name="customer" class="form-control-custom {{ !$canEditGeneral ? 'form-control-readonly' : '' }}" value="{{ $project->customer }}" {{ !$canEditGeneral ? 'disabled' : 'required' }}>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label-custom">Quantity (QTY)</label>
                        @if(!$canEditGeneral) <input type="hidden" name="quantity" value="{{ $project->quantity }}"> @endif
                        <input type="number" name="quantity" class="form-control-custom {{ !$canEditGeneral ? 'form-control-readonly' : '' }}" value="{{ $project->quantity ?? 1 }}" min="1" {{ !$canEditGeneral ? 'disabled' : 'required' }}>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label-custom">Fabricator</label>
                        @if(!$canEditGeneral) <input type="hidden" name="fabricator" value="{{ $project->fabricator }}"> @endif
                        <input type="text" name="fabricator" class="form-control-custom {{ !$canEditGeneral ? 'form-control-readonly' : '' }}" value="{{ $project->fabricator }}" placeholder="Workshop / Subcon..." {{ !$canEditGeneral ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label-custom">Plan Total Overall (%)</label>
                        <input type="text" id="input_plan_progress" class="form-control-custom form-control-readonly" value="{{ $project->plan_progress }}" readonly title="Otomatis dari jarak hari Contract Review sampai Due Date, bertambah tiap hari">
                        <small class="text-muted d-block mt-1" style="font-size: 0.68rem;">{{ ($project->contract_review && $project->due_date) ? 'Otomatis: jarak hari Contract Review sampai Due Date' : 'Isi Contract Review & Due Date agar otomatis' }}</small>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label-custom">Contract Review</label>
                        @if(!$canEditGeneral) <input type="hidden" name="contract_review" value="{{ $project->contract_review ? $project->contract_review->format('Y-m-d') : '' }}"> @endif
                        <input type="date" name="contract_review" class="form-control-custom {{ !$canEditGeneral ? 'form-control-readonly' : '' }}" value="{{ $project->contract_review ? $project->contract_review->format('Y-m-d') : '' }}" {{ !$canEditGeneral ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label-custom">Start Production</label>
                        @if(!$canEditGeneral) <input type="hidden" name="start_production" value="{{ $project->start_production ? $project->start_production->format('Y-m-d') : '' }}"> @endif
                        <input type="date" name="start_production" class="form-control-custom {{ !$canEditGeneral ? 'form-control-readonly' : '' }}" value="{{ $project->start_production ? $project->start_production->format('Y-m-d') : '' }}" {{ !$canEditGeneral ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label-custom">Due Date (Target Utama)</label>
                        @if(!$canEditGeneral) <input type="hidden" name="due_date" value="{{ $project->due_date ? $project->due_date->format('Y-m-d') : '' }}"> @endif
                        <input type="date" name="due_date" class="form-control-custom {{ !$canEditGeneral ? 'form-control-readonly' : '' }}" value="{{ $project->due_date ? $project->due_date->format('Y-m-d') : '' }}" {{ !$canEditGeneral ? 'disabled' : 'required' }}>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label-custom">Delivery Date <span class="text-muted fw-normal">(Rencana)</span></label>
                        @if(!$canEditGeneral) <input type="hidden" name="delivery_date" value="{{ $project->delivery_date ? $project->delivery_date->format('Y-m-d') : '' }}"> @endif
                        <input type="date" name="delivery_date" class="form-control-custom {{ !$canEditGeneral ? 'form-control-readonly' : '' }}" value="{{ $project->delivery_date ? $project->delivery_date->format('Y-m-d') : '' }}" {{ !$canEditGeneral ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label-custom">Note / Catatan</label>
                        @if(!$canEditGeneral) <input type="hidden" name="note" value="{{ $project->note }}"> @endif
                        <input type="text" name="note" class="form-control-custom {{ !$canEditGeneral ? 'form-control-readonly' : '' }}" value="{{ $project->note }}" placeholder="Catatan proyek..." {{ !$canEditGeneral ? 'disabled' : '' }}>
                    </div>
                </div>
            </div>

            <!-- DIAGRAM BULAT: PLANNING, ACTUAL, GAP (ikut berubah live saat persen jobdesk diubah) -->
            @php
                $donutPlan   = max(0, min(100, (int) $project->plan_progress));
                $donutActual = max(0, min(100, (int) $project->actual_progress));
                $donutGap    = $donutActual - $donutPlan;
                $donutC      = 2 * M_PI * 52;
                $donutActualColor = $donutActual >= 100 ? '#16a34a' : ($donutActual >= 51 ? '#d97706' : '#dc2626');
                $donutGapColor    = $donutGap >= 0 ? '#16a34a' : '#dc2626';
            @endphp
            <div class="custom-card p-4 mb-4 bg-white rounded-3 border" id="donut-section">
                <div class="section-header">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-pie-chart-fill text-success fs-5"></i>
                        <span>PLAN vs ACTUAL (OVERALL)</span>
                    </span>
                </div>
                <div class="row text-center g-3 justify-content-center">
                    @foreach([
                        ['id' => 'plan',   'label' => 'PLAN',   'pct' => $donutPlan,   'color' => '#16a34a', 'text' => $donutPlan . '%'],
                        ['id' => 'actual', 'label' => 'ACTUAL', 'pct' => $donutActual, 'color' => $donutActualColor, 'text' => $donutActual . '%'],
                        ['id' => 'gap',    'label' => 'GAP',    'pct' => min(100, abs($donutGap)), 'color' => $donutGapColor, 'text' => ($donutGap > 0 ? '+' : '') . $donutGap . '%'],
                    ] as $d)
                        <div class="col-4 col-md-3">
                            <svg viewBox="0 0 120 120" width="150" height="150" role="img" aria-label="{{ $d['label'] }} {{ $d['text'] }}" style="max-width: 100%;">
                                <circle cx="60" cy="60" r="52" fill="none" stroke="#e5e7eb" stroke-width="12"></circle>
                                <circle id="donut-{{ $d['id'] }}-arc" cx="60" cy="60" r="52" fill="none" stroke="{{ $d['color'] }}" stroke-width="12" stroke-linecap="round"
                                        stroke-dasharray="{{ round($donutC * $d['pct'] / 100, 2) }} {{ round($donutC, 2) }}" transform="rotate(-90 60 60)"></circle>
                                <text id="donut-{{ $d['id'] }}-text" x="60" y="68" text-anchor="middle" font-size="24" font-weight="800" fill="{{ $d['color'] }}">{{ $d['text'] }}</text>
                            </svg>
                            <div class="fw-bold mt-1" style="letter-spacing: 0.5px;">{{ $d['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- SECTION 2: CHECKLIST & PARAMETER GRANULAR DIVISI -->
            <div class="custom-card p-4 mb-4 bg-white rounded-3 border">
                <div class="section-header">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-check2-square text-success fs-5"></i>
                        <span>2. PROGRESS & TARGET PARAMETER PER DEPARTEMEN</span>
                    </span>
                    <span class="badge bg-white text-success border border-success-subtle fw-semibold">Langkah 2</span>
                </div>

                <!-- 3. DIVISI ENGINEERING -->
                <div class="division-header-box">
                    @php
                        $engActive       = $project->activeEngineeringTasks();
                        $engTotalCount   = count(\App\Models\Project::$engineeringTasks);
                    @endphp
                    <div class="division-title-bar">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <h6 class="fw-bold m-0 text-success"><i class="bi bi-gear-wide-connected me-1"></i> DIVISI ENGINEERING</h6>

                            {{-- Pilih jobdesk Engineering yang diperlukan proyek ini. Default semua tercentang. --}}
                            <div class="dropdown no-print">
                                <button class="btn btn-sm btn-outline-success dropdown-toggle fw-semibold" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                    <i class="bi bi-list-check me-1"></i> Jobdesk (<span id="engTaskCount">{{ count($engActive) }}</span>/{{ $engTotalCount }})
                                </button>
                                <ul class="dropdown-menu shadow border-0 p-2" style="min-width: 290px;">
                                    <li class="px-2 pb-2 small text-muted">Centang jobdesk yang diperlukan proyek ini. Bobot jobdesk yang tersisa dibagi ulang otomatis (total tetap 10%).</li>
                                    @foreach(\App\Models\Project::$engineeringTaskInfo as $engKey => $engInfo)
                                        <li>
                                            <label class="dropdown-item d-flex align-items-center justify-content-between gap-3 rounded-2" style="{{ $canEditEngineering ? 'cursor: pointer;' : 'cursor: not-allowed; opacity: 0.7;' }}">
                                                <span>{{ $engInfo['name'] }}</span>
                                                <input type="checkbox" class="form-check-input eng-task-toggle m-0" name="engineering_tasks_enabled[]" value="{{ $engKey }}" {{ $project->isEngineeringTaskEnabled($engKey) ? 'checked' : '' }} {{ !$canEditEngineering ? 'disabled' : '' }}>
                                            </label>
                                        </li>
                                    @endforeach
                                </ul>
                                @if($canEditEngineering)
                                    <input type="hidden" name="engineering_tasks_submitted" value="1">
                                @endif
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success fw-bold">Bobot Divisi: 10%</span>
                    </div>
                    <div class="row align-items-center g-3 mb-3 form-2col">
                        <div class="col-md-3">
                            <label class="form-label-custom">Due Date</label>
                            @if(!$canEditEngineering) <input type="hidden" name="due_date_engineering" value="{{ $project->due_date_engineering ? $project->due_date_engineering->format('Y-m-d') : '' }}"> @endif
                            <input type="date" name="due_date_engineering" class="form-control-custom {{ !$canEditEngineering ? 'form-control-readonly' : '' }}" value="{{ $project->due_date_engineering ? $project->due_date_engineering->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Planning (%)</label>
                            @if(!$canEditEngineering) <input type="hidden" name="plan_engineering" value="{{ $project->plan_engineering }}"> @endif
                            <input type="number" name="plan_engineering" class="form-control-custom {{ !$canEditEngineering ? 'form-control-readonly' : '' }}" value="{{ $project->plan_engineering }}" min="0" max="100" {{ !$canEditEngineering ? 'disabled' : '' }}>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Actual Progress Divisi</label>
                            <input type="text" id="display-progress-engineering" class="form-control-custom form-control-readonly text-success fw-bold" value="{{ $project->progress_engineering }}%" readonly>
                        </div>
                        <div class="col-md-3 text-end">
                            @php $deadlineInfo = $project->getDepartmentDeadlineInfo('engineering'); @endphp
                            <small class="{{ $deadlineInfo['text_class'] }} fw-bold d-block"><i class="bi {{ $deadlineInfo['icon'] }} me-1"></i> {{ $deadlineInfo['label'] }}</small>
                        </div>
                    </div>
                    @include('projects.partials.late-summary', ['division' => 'engineering', 'info' => \App\Models\Project::$engineeringTaskInfo])
                    <div class="row g-3 task-row-2">
                        <div class="col-md-4 {{ $project->isEngineeringTaskEnabled('task_drawing_ga') ? '' : 'd-none' }}" data-eng-task="task_drawing_ga">
                            <label class="task-box d-block {{ !$canEditEngineering ? 'disabled-box' : '' }}" for="task_drawing_ga">
                                @php
                                    $pct_task_drawing_ga = (int) round($project->task_drawing_ga);
                                @endphp
                                @if(!$canEditEngineering)
                                    <input type="hidden" name="progress_task_drawing_ga" value="{{ $pct_task_drawing_ga }}">
                                    <input type="hidden" name="due_date_task_drawing_ga" value="{{ $project->due_date_task_drawing_ga ? $project->due_date_task_drawing_ga->format('Y-m-d') : '' }}">
                                    <input type="hidden" name="last_update_task_drawing_ga" value="{{ $project->last_update_task_drawing_ga ? $project->last_update_task_drawing_ga->format('Y-m-d') : '' }}">
                                @endif
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <div><div class="fw-bold">Drawing GA</div><p class="small text-muted m-0">General Arrangement Drawing approved.</p></div>
                                    <span class="badge-weight-custom flex-shrink-0 eng-weight-badge" data-base-weight="{{ \App\Models\Project::$engineeringTasks['task_drawing_ga'] }}">+{{ rtrim(rtrim(number_format($engActive['task_drawing_ga'] ?? \App\Models\Project::$engineeringTasks['task_drawing_ga'], 2), '0'), '.') }}%</span>
                                </div>
                                <div class="progress-input-group mb-2">
                                    <input type="number" min="0" max="100" step="1" inputmode="numeric" class="form-control form-control-sm progress-input" name="progress_task_drawing_ga" id="task_drawing_ga" data-weight="{{ round($engActive['task_drawing_ga'] ?? 0, 4) }}" value="{{ $pct_task_drawing_ga }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    <span class="progress-input-suffix">%</span>
                                </div>
                                @include('projects.partials.task-late', ['taskKey' => 'task_drawing_ga'])
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label-custom">Start Date</label>
                                        <input type="date" name="start_date_task_drawing_ga" class="form-control-custom form-control-sm" value="{{ $project->start_date_task_drawing_ga ? $project->start_date_task_drawing_ga->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Due Date</label>
                                        <input type="date" name="due_date_task_drawing_ga" class="form-control-custom form-control-sm" value="{{ $project->due_date_task_drawing_ga ? $project->due_date_task_drawing_ga->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Tanggal Update</label>
                                        <input type="date" name="last_update_task_drawing_ga" class="form-control-custom form-control-sm" value="{{ $project->last_update_task_drawing_ga ? $project->last_update_task_drawing_ga->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-4 {{ $project->isEngineeringTaskEnabled('task_detail_drawing') ? '' : 'd-none' }}" data-eng-task="task_detail_drawing">
                            <label class="task-box d-block {{ !$canEditEngineering ? 'disabled-box' : '' }}" for="task_detail_drawing">
                                @php
                                    $pct_task_detail_drawing = (int) round($project->task_detail_drawing);
                                @endphp
                                @if(!$canEditEngineering)
                                    <input type="hidden" name="progress_task_detail_drawing" value="{{ $pct_task_detail_drawing }}">
                                    <input type="hidden" name="due_date_task_detail_drawing" value="{{ $project->due_date_task_detail_drawing ? $project->due_date_task_detail_drawing->format('Y-m-d') : '' }}">
                                    <input type="hidden" name="last_update_task_detail_drawing" value="{{ $project->last_update_task_detail_drawing ? $project->last_update_task_detail_drawing->format('Y-m-d') : '' }}">
                                @endif
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <div><div class="fw-bold">Detail Drawing</div><p class="small text-muted m-0">Gambar manufaktur & fabrikasi detail.</p></div>
                                    <span class="badge-weight-custom flex-shrink-0 eng-weight-badge" data-base-weight="{{ \App\Models\Project::$engineeringTasks['task_detail_drawing'] }}">+{{ rtrim(rtrim(number_format($engActive['task_detail_drawing'] ?? \App\Models\Project::$engineeringTasks['task_detail_drawing'], 2), '0'), '.') }}%</span>
                                </div>
                                <div class="progress-input-group mb-2">
                                    <input type="number" min="0" max="100" step="1" inputmode="numeric" class="form-control form-control-sm progress-input" name="progress_task_detail_drawing" id="task_detail_drawing" data-weight="{{ round($engActive['task_detail_drawing'] ?? 0, 4) }}" value="{{ $pct_task_detail_drawing }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    <span class="progress-input-suffix">%</span>
                                </div>
                                @include('projects.partials.task-late', ['taskKey' => 'task_detail_drawing'])
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label-custom">Start Date</label>
                                        <input type="date" name="start_date_task_detail_drawing" class="form-control-custom form-control-sm" value="{{ $project->start_date_task_detail_drawing ? $project->start_date_task_detail_drawing->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Due Date</label>
                                        <input type="date" name="due_date_task_detail_drawing" class="form-control-custom form-control-sm" value="{{ $project->due_date_task_detail_drawing ? $project->due_date_task_detail_drawing->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Tanggal Update</label>
                                        <input type="date" name="last_update_task_detail_drawing" class="form-control-custom form-control-sm" value="{{ $project->last_update_task_detail_drawing ? $project->last_update_task_detail_drawing->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-4 {{ $project->isEngineeringTaskEnabled('task_cutting_plan') ? '' : 'd-none' }}" data-eng-task="task_cutting_plan">
                            <label class="task-box d-block {{ !$canEditEngineering ? 'disabled-box' : '' }}" for="task_cutting_plan">
                                @php
                                    $pct_task_cutting_plan = (int) round($project->task_cutting_plan);
                                @endphp
                                @if(!$canEditEngineering)
                                    <input type="hidden" name="progress_task_cutting_plan" value="{{ $pct_task_cutting_plan }}">
                                    <input type="hidden" name="due_date_task_cutting_plan" value="{{ $project->due_date_task_cutting_plan ? $project->due_date_task_cutting_plan->format('Y-m-d') : '' }}">
                                    <input type="hidden" name="last_update_task_cutting_plan" value="{{ $project->last_update_task_cutting_plan ? $project->last_update_task_cutting_plan->format('Y-m-d') : '' }}">
                                @endif
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <div><div class="fw-bold">Cutting Plan</div><p class="small text-muted m-0">Rencana pemotongan material disetujui.</p></div>
                                    <span class="badge-weight-custom flex-shrink-0 eng-weight-badge" data-base-weight="{{ \App\Models\Project::$engineeringTasks['task_cutting_plan'] }}">+{{ rtrim(rtrim(number_format($engActive['task_cutting_plan'] ?? \App\Models\Project::$engineeringTasks['task_cutting_plan'], 2), '0'), '.') }}%</span>
                                </div>
                                <div class="progress-input-group mb-2">
                                    <input type="number" min="0" max="100" step="1" inputmode="numeric" class="form-control form-control-sm progress-input" name="progress_task_cutting_plan" id="task_cutting_plan" data-weight="{{ round($engActive['task_cutting_plan'] ?? 0, 4) }}" value="{{ $pct_task_cutting_plan }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    <span class="progress-input-suffix">%</span>
                                </div>
                                @include('projects.partials.task-late', ['taskKey' => 'task_cutting_plan'])
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label-custom">Start Date</label>
                                        <input type="date" name="start_date_task_cutting_plan" class="form-control-custom form-control-sm" value="{{ $project->start_date_task_cutting_plan ? $project->start_date_task_cutting_plan->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Due Date</label>
                                        <input type="date" name="due_date_task_cutting_plan" class="form-control-custom form-control-sm" value="{{ $project->due_date_task_cutting_plan ? $project->due_date_task_cutting_plan->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Tanggal Update</label>
                                        <input type="date" name="last_update_task_cutting_plan" class="form-control-custom form-control-sm" value="{{ $project->last_update_task_cutting_plan ? $project->last_update_task_cutting_plan->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-4 {{ $project->isEngineeringTaskEnabled('task_cnc_program') ? '' : 'd-none' }}" data-eng-task="task_cnc_program">
                            <label class="task-box d-block {{ !$canEditEngineering ? 'disabled-box' : '' }}" for="task_cnc_program">
                                @php
                                    $pct_task_cnc_program = (int) round($project->task_cnc_program);
                                @endphp
                                @if(!$canEditEngineering)
                                    <input type="hidden" name="progress_task_cnc_program" value="{{ $pct_task_cnc_program }}">
                                    <input type="hidden" name="due_date_task_cnc_program" value="{{ $project->due_date_task_cnc_program ? $project->due_date_task_cnc_program->format('Y-m-d') : '' }}">
                                    <input type="hidden" name="last_update_task_cnc_program" value="{{ $project->last_update_task_cnc_program ? $project->last_update_task_cnc_program->format('Y-m-d') : '' }}">
                                @endif
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <div><div class="fw-bold">CNC Program</div><p class="small text-muted m-0">Program CNC untuk mesin potong/bentuk.</p></div>
                                    <span class="badge-weight-custom flex-shrink-0 eng-weight-badge" data-base-weight="{{ \App\Models\Project::$engineeringTasks['task_cnc_program'] }}">+{{ rtrim(rtrim(number_format($engActive['task_cnc_program'] ?? \App\Models\Project::$engineeringTasks['task_cnc_program'], 2), '0'), '.') }}%</span>
                                </div>
                                <div class="progress-input-group mb-2">
                                    <input type="number" min="0" max="100" step="1" inputmode="numeric" class="form-control form-control-sm progress-input" name="progress_task_cnc_program" id="task_cnc_program" data-weight="{{ round($engActive['task_cnc_program'] ?? 0, 4) }}" value="{{ $pct_task_cnc_program }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    <span class="progress-input-suffix">%</span>
                                </div>
                                @include('projects.partials.task-late', ['taskKey' => 'task_cnc_program'])
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label-custom">Start Date</label>
                                        <input type="date" name="start_date_task_cnc_program" class="form-control-custom form-control-sm" value="{{ $project->start_date_task_cnc_program ? $project->start_date_task_cnc_program->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Due Date</label>
                                        <input type="date" name="due_date_task_cnc_program" class="form-control-custom form-control-sm" value="{{ $project->due_date_task_cnc_program ? $project->due_date_task_cnc_program->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Tanggal Update</label>
                                        <input type="date" name="last_update_task_cnc_program" class="form-control-custom form-control-sm" value="{{ $project->last_update_task_cnc_program ? $project->last_update_task_cnc_program->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-4 {{ $project->isEngineeringTaskEnabled('task_bom') ? '' : 'd-none' }}" data-eng-task="task_bom">
                            <label class="task-box d-block {{ !$canEditEngineering ? 'disabled-box' : '' }}" for="task_bom">
                                @php
                                    $pct_task_bom = (int) round($project->task_bom);
                                @endphp
                                @if(!$canEditEngineering)
                                    <input type="hidden" name="progress_task_bom" value="{{ $pct_task_bom }}">
                                    <input type="hidden" name="due_date_task_bom" value="{{ $project->due_date_task_bom ? $project->due_date_task_bom->format('Y-m-d') : '' }}">
                                    <input type="hidden" name="last_update_task_bom" value="{{ $project->last_update_task_bom ? $project->last_update_task_bom->format('Y-m-d') : '' }}">
                                @endif
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <div><div class="fw-bold">BOM</div><p class="small text-muted m-0">Bill of Materials terverifikasi.</p></div>
                                    <span class="badge-weight-custom flex-shrink-0 eng-weight-badge" data-base-weight="{{ \App\Models\Project::$engineeringTasks['task_bom'] }}">+{{ rtrim(rtrim(number_format($engActive['task_bom'] ?? \App\Models\Project::$engineeringTasks['task_bom'], 2), '0'), '.') }}%</span>
                                </div>
                                <div class="progress-input-group mb-2">
                                    <input type="number" min="0" max="100" step="1" inputmode="numeric" class="form-control form-control-sm progress-input" name="progress_task_bom" id="task_bom" data-weight="{{ round($engActive['task_bom'] ?? 0, 4) }}" value="{{ $pct_task_bom }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    <span class="progress-input-suffix">%</span>
                                </div>
                                @include('projects.partials.task-late', ['taskKey' => 'task_bom'])
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label-custom">Start Date</label>
                                        <input type="date" name="start_date_task_bom" class="form-control-custom form-control-sm" value="{{ $project->start_date_task_bom ? $project->start_date_task_bom->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Due Date</label>
                                        <input type="date" name="due_date_task_bom" class="form-control-custom form-control-sm" value="{{ $project->due_date_task_bom ? $project->due_date_task_bom->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Tanggal Update</label>
                                        <input type="date" name="last_update_task_bom" class="form-control-custom form-control-sm" value="{{ $project->last_update_task_bom ? $project->last_update_task_bom->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-4 {{ $project->isEngineeringTaskEnabled('task_painting_calc') ? '' : 'd-none' }}" data-eng-task="task_painting_calc">
                            <label class="task-box d-block {{ !$canEditEngineering ? 'disabled-box' : '' }}" for="task_painting_calc">
                                @php
                                    $pct_task_painting_calc = (int) round($project->task_painting_calc);
                                @endphp
                                @if(!$canEditEngineering)
                                    <input type="hidden" name="progress_task_painting_calc" value="{{ $pct_task_painting_calc }}">
                                    <input type="hidden" name="due_date_task_painting_calc" value="{{ $project->due_date_task_painting_calc ? $project->due_date_task_painting_calc->format('Y-m-d') : '' }}">
                                    <input type="hidden" name="last_update_task_painting_calc" value="{{ $project->last_update_task_painting_calc ? $project->last_update_task_painting_calc->format('Y-m-d') : '' }}">
                                @endif
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <div><div class="fw-bold">Painting Calc</div><p class="small text-muted m-0">Perhitungan spesifikasi pengecatan.</p></div>
                                    <span class="badge-weight-custom flex-shrink-0 eng-weight-badge" data-base-weight="{{ \App\Models\Project::$engineeringTasks['task_painting_calc'] }}">+{{ rtrim(rtrim(number_format($engActive['task_painting_calc'] ?? \App\Models\Project::$engineeringTasks['task_painting_calc'], 2), '0'), '.') }}%</span>
                                </div>
                                <div class="progress-input-group mb-2">
                                    <input type="number" min="0" max="100" step="1" inputmode="numeric" class="form-control form-control-sm progress-input" name="progress_task_painting_calc" id="task_painting_calc" data-weight="{{ round($engActive['task_painting_calc'] ?? 0, 4) }}" value="{{ $pct_task_painting_calc }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    <span class="progress-input-suffix">%</span>
                                </div>
                                @include('projects.partials.task-late', ['taskKey' => 'task_painting_calc'])
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label-custom">Start Date</label>
                                        <input type="date" name="start_date_task_painting_calc" class="form-control-custom form-control-sm" value="{{ $project->start_date_task_painting_calc ? $project->start_date_task_painting_calc->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Due Date</label>
                                        <input type="date" name="due_date_task_painting_calc" class="form-control-custom form-control-sm" value="{{ $project->due_date_task_painting_calc ? $project->due_date_task_painting_calc->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Tanggal Update</label>
                                        <input type="date" name="last_update_task_painting_calc" class="form-control-custom form-control-sm" value="{{ $project->last_update_task_painting_calc ? $project->last_update_task_painting_calc->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-4 {{ $project->isEngineeringTaskEnabled('task_design_calc') ? '' : 'd-none' }}" data-eng-task="task_design_calc">
                            <label class="task-box d-block {{ !$canEditEngineering ? 'disabled-box' : '' }}" for="task_design_calc">
                                @php
                                    $pct_task_design_calc = (int) round($project->task_design_calc);
                                @endphp
                                @if(!$canEditEngineering)
                                    <input type="hidden" name="progress_task_design_calc" value="{{ $pct_task_design_calc }}">
                                    <input type="hidden" name="due_date_task_design_calc" value="{{ $project->due_date_task_design_calc ? $project->due_date_task_design_calc->format('Y-m-d') : '' }}">
                                    <input type="hidden" name="last_update_task_design_calc" value="{{ $project->last_update_task_design_calc ? $project->last_update_task_design_calc->format('Y-m-d') : '' }}">
                                @endif
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <div><div class="fw-bold">Design Calculation</div><p class="small text-muted m-0">Kalkulasi desain & kekuatan struktur.</p></div>
                                    <span class="badge-weight-custom flex-shrink-0 eng-weight-badge" data-base-weight="{{ \App\Models\Project::$engineeringTasks['task_design_calc'] }}">+{{ rtrim(rtrim(number_format($engActive['task_design_calc'] ?? \App\Models\Project::$engineeringTasks['task_design_calc'], 2), '0'), '.') }}%</span>
                                </div>
                                <div class="progress-input-group mb-2">
                                    <input type="number" min="0" max="100" step="1" inputmode="numeric" class="form-control form-control-sm progress-input" name="progress_task_design_calc" id="task_design_calc" data-weight="{{ round($engActive['task_design_calc'] ?? 0, 4) }}" value="{{ $pct_task_design_calc }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    <span class="progress-input-suffix">%</span>
                                </div>
                                @include('projects.partials.task-late', ['taskKey' => 'task_design_calc'])
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label-custom">Start Date</label>
                                        <input type="date" name="start_date_task_design_calc" class="form-control-custom form-control-sm" value="{{ $project->start_date_task_design_calc ? $project->start_date_task_design_calc->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Due Date</label>
                                        <input type="date" name="due_date_task_design_calc" class="form-control-custom form-control-sm" value="{{ $project->due_date_task_design_calc ? $project->due_date_task_design_calc->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Tanggal Update</label>
                                        <input type="date" name="last_update_task_design_calc" class="form-control-custom form-control-sm" value="{{ $project->last_update_task_design_calc ? $project->last_update_task_design_calc->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-4 {{ $project->isEngineeringTaskEnabled('task_design_name_plate') ? '' : 'd-none' }}" data-eng-task="task_design_name_plate">
                            <label class="task-box d-block {{ !$canEditEngineering ? 'disabled-box' : '' }}" for="task_design_name_plate">
                                @php
                                    $pct_task_design_name_plate = (int) round($project->task_design_name_plate);
                                @endphp
                                @if(!$canEditEngineering)
                                    <input type="hidden" name="progress_task_design_name_plate" value="{{ $pct_task_design_name_plate }}">
                                    <input type="hidden" name="due_date_task_design_name_plate" value="{{ $project->due_date_task_design_name_plate ? $project->due_date_task_design_name_plate->format('Y-m-d') : '' }}">
                                    <input type="hidden" name="last_update_task_design_name_plate" value="{{ $project->last_update_task_design_name_plate ? $project->last_update_task_design_name_plate->format('Y-m-d') : '' }}">
                                @endif
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <div><div class="fw-bold">Design Name Plate</div><p class="small text-muted m-0">Desain name plate produk disetujui.</p></div>
                                    <span class="badge-weight-custom flex-shrink-0 eng-weight-badge" data-base-weight="{{ \App\Models\Project::$engineeringTasks['task_design_name_plate'] }}">+{{ rtrim(rtrim(number_format($engActive['task_design_name_plate'] ?? \App\Models\Project::$engineeringTasks['task_design_name_plate'], 2), '0'), '.') }}%</span>
                                </div>
                                <div class="progress-input-group mb-2">
                                    <input type="number" min="0" max="100" step="1" inputmode="numeric" class="form-control form-control-sm progress-input" name="progress_task_design_name_plate" id="task_design_name_plate" data-weight="{{ round($engActive['task_design_name_plate'] ?? 0, 4) }}" value="{{ $pct_task_design_name_plate }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    <span class="progress-input-suffix">%</span>
                                </div>
                                @include('projects.partials.task-late', ['taskKey' => 'task_design_name_plate'])
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label-custom">Start Date</label>
                                        <input type="date" name="start_date_task_design_name_plate" class="form-control-custom form-control-sm" value="{{ $project->start_date_task_design_name_plate ? $project->start_date_task_design_name_plate->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Due Date</label>
                                        <input type="date" name="due_date_task_design_name_plate" class="form-control-custom form-control-sm" value="{{ $project->due_date_task_design_name_plate ? $project->due_date_task_design_name_plate->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Tanggal Update</label>
                                        <input type="date" name="last_update_task_design_name_plate" class="form-control-custom form-control-sm" value="{{ $project->last_update_task_design_name_plate ? $project->last_update_task_design_name_plate->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-4 {{ $project->isEngineeringTaskEnabled('task_design_sticker') ? '' : 'd-none' }}" data-eng-task="task_design_sticker">
                            <label class="task-box d-block {{ !$canEditEngineering ? 'disabled-box' : '' }}" for="task_design_sticker">
                                @php
                                    $pct_task_design_sticker = (int) round($project->task_design_sticker);
                                @endphp
                                @if(!$canEditEngineering)
                                    <input type="hidden" name="progress_task_design_sticker" value="{{ $pct_task_design_sticker }}">
                                    <input type="hidden" name="due_date_task_design_sticker" value="{{ $project->due_date_task_design_sticker ? $project->due_date_task_design_sticker->format('Y-m-d') : '' }}">
                                    <input type="hidden" name="last_update_task_design_sticker" value="{{ $project->last_update_task_design_sticker ? $project->last_update_task_design_sticker->format('Y-m-d') : '' }}">
                                @endif
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <div><div class="fw-bold">Design Sticker</div><p class="small text-muted m-0">Desain sticker/label produk disetujui.</p></div>
                                    <span class="badge-weight-custom flex-shrink-0 eng-weight-badge" data-base-weight="{{ \App\Models\Project::$engineeringTasks['task_design_sticker'] }}">+{{ rtrim(rtrim(number_format($engActive['task_design_sticker'] ?? \App\Models\Project::$engineeringTasks['task_design_sticker'], 2), '0'), '.') }}%</span>
                                </div>
                                <div class="progress-input-group mb-2">
                                    <input type="number" min="0" max="100" step="1" inputmode="numeric" class="form-control form-control-sm progress-input" name="progress_task_design_sticker" id="task_design_sticker" data-weight="{{ round($engActive['task_design_sticker'] ?? 0, 4) }}" value="{{ $pct_task_design_sticker }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    <span class="progress-input-suffix">%</span>
                                </div>
                                @include('projects.partials.task-late', ['taskKey' => 'task_design_sticker'])
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label-custom">Start Date</label>
                                        <input type="date" name="start_date_task_design_sticker" class="form-control-custom form-control-sm" value="{{ $project->start_date_task_design_sticker ? $project->start_date_task_design_sticker->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Due Date</label>
                                        <input type="date" name="due_date_task_design_sticker" class="form-control-custom form-control-sm" value="{{ $project->due_date_task_design_sticker ? $project->due_date_task_design_sticker->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Tanggal Update</label>
                                        <input type="date" name="last_update_task_design_sticker" class="form-control-custom form-control-sm" value="{{ $project->last_update_task_design_sticker ? $project->last_update_task_design_sticker->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-4 {{ $project->isEngineeringTaskEnabled('task_packing_drawing') ? '' : 'd-none' }}" data-eng-task="task_packing_drawing">
                            <label class="task-box d-block {{ !$canEditEngineering ? 'disabled-box' : '' }}" for="task_packing_drawing">
                                @php
                                    $pct_task_packing_drawing = (int) round($project->task_packing_drawing);
                                @endphp
                                @if(!$canEditEngineering)
                                    <input type="hidden" name="progress_task_packing_drawing" value="{{ $pct_task_packing_drawing }}">
                                    <input type="hidden" name="due_date_task_packing_drawing" value="{{ $project->due_date_task_packing_drawing ? $project->due_date_task_packing_drawing->format('Y-m-d') : '' }}">
                                    <input type="hidden" name="last_update_task_packing_drawing" value="{{ $project->last_update_task_packing_drawing ? $project->last_update_task_packing_drawing->format('Y-m-d') : '' }}">
                                @endif
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <div><div class="fw-bold">Packing Drawing</div><p class="small text-muted m-0">Gambar rencana pengepakan barang.</p></div>
                                    <span class="badge-weight-custom flex-shrink-0 eng-weight-badge" data-base-weight="{{ \App\Models\Project::$engineeringTasks['task_packing_drawing'] }}">+{{ rtrim(rtrim(number_format($engActive['task_packing_drawing'] ?? \App\Models\Project::$engineeringTasks['task_packing_drawing'], 2), '0'), '.') }}%</span>
                                </div>
                                <div class="progress-input-group mb-2">
                                    <input type="number" min="0" max="100" step="1" inputmode="numeric" class="form-control form-control-sm progress-input" name="progress_task_packing_drawing" id="task_packing_drawing" data-weight="{{ round($engActive['task_packing_drawing'] ?? 0, 4) }}" value="{{ $pct_task_packing_drawing }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    <span class="progress-input-suffix">%</span>
                                </div>
                                @include('projects.partials.task-late', ['taskKey' => 'task_packing_drawing'])
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label-custom">Start Date</label>
                                        <input type="date" name="start_date_task_packing_drawing" class="form-control-custom form-control-sm" value="{{ $project->start_date_task_packing_drawing ? $project->start_date_task_packing_drawing->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Due Date</label>
                                        <input type="date" name="due_date_task_packing_drawing" class="form-control-custom form-control-sm" value="{{ $project->due_date_task_packing_drawing ? $project->due_date_task_packing_drawing->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Tanggal Update</label>
                                        <input type="date" name="last_update_task_packing_drawing" class="form-control-custom form-control-sm" value="{{ $project->last_update_task_packing_drawing ? $project->last_update_task_packing_drawing->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-4 {{ $project->isEngineeringTaskEnabled('task_welding_map') ? '' : 'd-none' }}" data-eng-task="task_welding_map">
                            <label class="task-box d-block {{ !$canEditEngineering ? 'disabled-box' : '' }}" for="task_welding_map">
                                @php
                                    $pct_task_welding_map = (int) round($project->task_welding_map);
                                @endphp
                                @if(!$canEditEngineering)
                                    <input type="hidden" name="progress_task_welding_map" value="{{ $pct_task_welding_map }}">
                                    <input type="hidden" name="due_date_task_welding_map" value="{{ $project->due_date_task_welding_map ? $project->due_date_task_welding_map->format('Y-m-d') : '' }}">
                                    <input type="hidden" name="last_update_task_welding_map" value="{{ $project->last_update_task_welding_map ? $project->last_update_task_welding_map->format('Y-m-d') : '' }}">
                                @endif
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <div><div class="fw-bold">Welding Map</div><p class="small text-muted m-0">Peta jalur & urutan pengelasan.</p></div>
                                    <span class="badge-weight-custom flex-shrink-0 eng-weight-badge" data-base-weight="{{ \App\Models\Project::$engineeringTasks['task_welding_map'] }}">+{{ rtrim(rtrim(number_format($engActive['task_welding_map'] ?? \App\Models\Project::$engineeringTasks['task_welding_map'], 2), '0'), '.') }}%</span>
                                </div>
                                <div class="progress-input-group mb-2">
                                    <input type="number" min="0" max="100" step="1" inputmode="numeric" class="form-control form-control-sm progress-input" name="progress_task_welding_map" id="task_welding_map" data-weight="{{ round($engActive['task_welding_map'] ?? 0, 4) }}" value="{{ $pct_task_welding_map }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    <span class="progress-input-suffix">%</span>
                                </div>
                                @include('projects.partials.task-late', ['taskKey' => 'task_welding_map'])
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label-custom">Start Date</label>
                                        <input type="date" name="start_date_task_welding_map" class="form-control-custom form-control-sm" value="{{ $project->start_date_task_welding_map ? $project->start_date_task_welding_map->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Due Date</label>
                                        <input type="date" name="due_date_task_welding_map" class="form-control-custom form-control-sm" value="{{ $project->due_date_task_welding_map ? $project->due_date_task_welding_map->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Tanggal Update</label>
                                        <input type="date" name="last_update_task_welding_map" class="form-control-custom form-control-sm" value="{{ $project->last_update_task_welding_map ? $project->last_update_task_welding_map->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-4 {{ $project->isEngineeringTaskEnabled('task_skema_loadtest') ? '' : 'd-none' }}" data-eng-task="task_skema_loadtest">
                            <label class="task-box d-block {{ !$canEditEngineering ? 'disabled-box' : '' }}" for="task_skema_loadtest">
                                @php
                                    $pct_task_skema_loadtest = (int) round($project->task_skema_loadtest);
                                @endphp
                                @if(!$canEditEngineering)
                                    <input type="hidden" name="progress_task_skema_loadtest" value="{{ $pct_task_skema_loadtest }}">
                                    <input type="hidden" name="due_date_task_skema_loadtest" value="{{ $project->due_date_task_skema_loadtest ? $project->due_date_task_skema_loadtest->format('Y-m-d') : '' }}">
                                    <input type="hidden" name="last_update_task_skema_loadtest" value="{{ $project->last_update_task_skema_loadtest ? $project->last_update_task_skema_loadtest->format('Y-m-d') : '' }}">
                                @endif
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <div><div class="fw-bold">Skema Loadtest</div><p class="small text-muted m-0">Skema pengujian beban disetujui.</p></div>
                                    <span class="badge-weight-custom flex-shrink-0 eng-weight-badge" data-base-weight="{{ \App\Models\Project::$engineeringTasks['task_skema_loadtest'] }}">+{{ rtrim(rtrim(number_format($engActive['task_skema_loadtest'] ?? \App\Models\Project::$engineeringTasks['task_skema_loadtest'], 2), '0'), '.') }}%</span>
                                </div>
                                <div class="progress-input-group mb-2">
                                    <input type="number" min="0" max="100" step="1" inputmode="numeric" class="form-control form-control-sm progress-input" name="progress_task_skema_loadtest" id="task_skema_loadtest" data-weight="{{ round($engActive['task_skema_loadtest'] ?? 0, 4) }}" value="{{ $pct_task_skema_loadtest }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    <span class="progress-input-suffix">%</span>
                                </div>
                                @include('projects.partials.task-late', ['taskKey' => 'task_skema_loadtest'])
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label-custom">Start Date</label>
                                        <input type="date" name="start_date_task_skema_loadtest" class="form-control-custom form-control-sm" value="{{ $project->start_date_task_skema_loadtest ? $project->start_date_task_skema_loadtest->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Due Date</label>
                                        <input type="date" name="due_date_task_skema_loadtest" class="form-control-custom form-control-sm" value="{{ $project->due_date_task_skema_loadtest ? $project->due_date_task_skema_loadtest->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom">Tanggal Update</label>
                                        <input type="date" name="last_update_task_skema_loadtest" class="form-control-custom form-control-sm" value="{{ $project->last_update_task_skema_loadtest ? $project->last_update_task_skema_loadtest->format('Y-m-d') : '' }}" {{ !$canEditEngineering ? 'disabled' : '' }}>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                @include('projects.partials.division-problem', ['division' => 'engineering', 'canInput' => $canEditEngineering])
                </div>

                <!-- 1. DIVISI PROCUREMENT -->
                <div class="division-header-box">
                    <div class="division-title-bar">
                        <h6 class="fw-bold m-0 text-success"><i class="bi bi-cart-check-fill me-1"></i> DIVISI PROCUREMENT</h6>
                        <span class="badge bg-success-subtle text-success border border-success fw-bold">Bobot Divisi: 10%</span>
                    </div>
                    <div class="row align-items-center g-3 mb-3 form-2col">
                        <div class="col-md-3">
                            <label class="form-label-custom">Due Date <span class="text-muted fw-normal">(Target Material Datang)</span></label>
                            @if(!$canEditProcurement) <input type="hidden" name="due_date_procurement" value="{{ $project->due_date_procurement ? $project->due_date_procurement->format('Y-m-d') : '' }}"> @endif
                            <input type="date" name="due_date_procurement" class="form-control-custom {{ !$canEditProcurement ? 'form-control-readonly' : '' }}" value="{{ $project->due_date_procurement ? $project->due_date_procurement->format('Y-m-d') : '' }}" {{ !$canEditProcurement ? 'disabled' : '' }}>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Actual Material Arrival Date</label>
                            @if(!$canEditProcurement) <input type="hidden" name="actual_material_arrival_date" value="{{ $project->actual_material_arrival_date ? $project->actual_material_arrival_date->format('Y-m-d') : '' }}"> @endif
                            <input type="date" name="actual_material_arrival_date" class="form-control-custom {{ !$canEditProcurement ? 'form-control-readonly' : '' }}" value="{{ $project->actual_material_arrival_date ? $project->actual_material_arrival_date->format('Y-m-d') : '' }}" {{ !$canEditProcurement ? 'disabled' : '' }}>
                            @php $arrivalComparison = $project->material_arrival_comparison; @endphp
                            <span class="badge {{ $arrivalComparison['badge_class'] }} mt-2">{{ $arrivalComparison['label'] }}</span>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Planning (%)</label>
                            @if(!$canEditProcurement) <input type="hidden" name="plan_procurement" value="{{ $project->plan_procurement }}"> @endif
                            <input type="number" name="plan_procurement" class="form-control-custom {{ !$canEditProcurement ? 'form-control-readonly' : '' }}" value="{{ $project->plan_procurement }}" min="0" max="100" {{ !$canEditProcurement ? 'disabled' : '' }}>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Actual Progress Divisi</label>
                            <input type="text" class="form-control-custom form-control-readonly text-success fw-bold" value="{{ $project->progress_procurement }}%" readonly>
                            @php $deadlineInfo = $project->getDepartmentDeadlineInfo('procurement'); @endphp
                            <small class="{{ $deadlineInfo['text_class'] }} fw-bold d-block mt-2"><i class="bi {{ $deadlineInfo['icon'] }} me-1"></i> {{ $deadlineInfo['label'] }}</small>
                        </div>
                    </div>
                    @include('projects.partials.late-summary', ['division' => 'procurement', 'info' => \App\Models\Project::$procurementTaskInfo])
                    <div class="row g-3 task-row-2">
                        <div class="col-md-4">
                            <label class="task-box d-block {{ !$canEditProcurement ? 'disabled-box' : '' }}" for="task_main_material">
                                {{-- Otomatis dari persentase barang datang (Detail Material); tidak diisi manual --}}
                                @php $pct_task_main_material = $materialSummary['progress']; @endphp
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <div><div class="fw-bold">Raw Material</div><p class="small text-muted m-0">Pengadaan material utama sesuai kebutuhan proyek.</p></div>
                                    <span class="badge-weight-custom flex-shrink-0">+{{ rtrim(rtrim(number_format(\App\Models\Project::$procurementTasks['task_main_material'], 2), '0'), '.') }}%</span>
                                </div>
                                <div class="progress-input-group mb-2">
                                    <input type="number" class="form-control form-control-sm progress-input" id="task_main_material" data-weight="{{ \App\Models\Project::$procurementTasks['task_main_material'] }}" value="{{ $pct_task_main_material }}" readonly title="Otomatis dari persentase barang datang (lihat Detail Material)" style="background-color:#f1f5f9;cursor:not-allowed;">
                                    <span class="progress-input-suffix">%</span>
                                </div>

                                <button type="button" class="btn btn-sm btn-outline-success fw-bold w-100 mb-2 no-print" data-bs-toggle="modal" data-bs-target="#modalMaterial">
                                    <i class="bi bi-box-seam me-1"></i> Detail Material
                                    <span class="badge bg-success ms-1" id="material-summary-badge">{{ $materialSummary['text'] }}</span>
                                </button>

                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label-custom" style="font-size: 0.7rem;">Start Date</label>
                                        <input type="date" name="start_date_task_main_material" class="form-control-custom form-control-sm" value="{{ $project->start_date_task_main_material ? $project->start_date_task_main_material->format('Y-m-d') : '' }}" {{ !$canEditProcurement ? 'disabled' : '' }}>
                                    </div>
                                </div>

                                <div class="row g-2 pt-2 border-top">
                                    <div class="col-6">
                                        <label class="form-label-custom" style="font-size: 0.7rem;">Status Pengiriman</label>
                                        @if(!$canEditProcurement) <input type="hidden" name="task_main_material_shipping_status" value="{{ $project->task_main_material_shipping_status }}"> @endif
                                        <select name="task_main_material_shipping_status" class="form-select form-select-sm {{ !$canEditProcurement ? 'form-control-readonly' : '' }}" {{ !$canEditProcurement ? 'disabled' : '' }}>
                                            @foreach(\App\Models\Project::$shippingStatusOptions as $optKey => $optLabel)
                                            <option value="{{ $optKey }}" {{ $project->task_main_material_shipping_status === $optKey ? 'selected' : '' }}>{{ $optLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom" style="font-size: 0.7rem;">Kesesuaian Barang</label>
                                        @if(!$canEditProcurement) <input type="hidden" name="task_main_material_match_status" value="{{ $project->task_main_material_match_status }}"> @endif
                                        <select name="task_main_material_match_status" class="form-select form-select-sm {{ !$canEditProcurement ? 'form-control-readonly' : '' }}" {{ !$canEditProcurement ? 'disabled' : '' }}>
                                            @foreach(\App\Models\Project::$matchStatusOptions as $optKey => $optLabel)
                                            <option value="{{ $optKey }}" {{ $project->task_main_material_match_status === $optKey ? 'selected' : '' }}>{{ $optLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label class="task-box d-block {{ !$canEditProcurement ? 'disabled-box' : '' }}" for="task_secondary_material">
                                @php $pct_task_secondary_material = (int) round($project->task_secondary_material); @endphp
                                @if(!$canEditProcurement)
                                    <input type="hidden" name="progress_task_secondary_material" value="{{ $pct_task_secondary_material }}">
                                @endif
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <div><div class="fw-bold">Accessories</div><p class="small text-muted m-0">Pengadaan accessories & part pendukung proyek.</p></div>
                                    <span class="badge-weight-custom flex-shrink-0">+{{ rtrim(rtrim(number_format(\App\Models\Project::$procurementTasks['task_secondary_material'], 2), '0'), '.') }}%</span>
                                </div>
                                <div class="progress-input-group mb-2">
                                    <input type="number" min="0" max="100" step="1" inputmode="numeric" class="form-control form-control-sm progress-input" name="progress_task_secondary_material" id="task_secondary_material" data-weight="{{ \App\Models\Project::$procurementTasks['task_secondary_material'] }}" value="{{ $pct_task_secondary_material }}" {{ !$canEditProcurement ? 'disabled' : '' }}>
                                    <span class="progress-input-suffix">%</span>
                                </div>
                                @include('projects.partials.task-late', ['taskKey' => 'task_secondary_material'])

                                @if(!$canEditProcurement)
                                    <input type="hidden" name="due_date_task_secondary_material" value="{{ $project->due_date_task_secondary_material ? $project->due_date_task_secondary_material->format('Y-m-d') : '' }}">
                                    <input type="hidden" name="last_update_task_secondary_material" value="{{ $project->last_update_task_secondary_material ? $project->last_update_task_secondary_material->format('Y-m-d') : '' }}">
                                @endif
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label-custom" style="font-size: 0.7rem;">Start Date</label>
                                        <input type="date" name="start_date_task_secondary_material" class="form-control-custom form-control-sm" value="{{ $project->start_date_task_secondary_material ? $project->start_date_task_secondary_material->format('Y-m-d') : '' }}" {{ !$canEditProcurement ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom" style="font-size: 0.7rem;">Due Date</label>
                                        <input type="date" name="due_date_task_secondary_material" class="form-control-custom form-control-sm" value="{{ $project->due_date_task_secondary_material ? $project->due_date_task_secondary_material->format('Y-m-d') : '' }}" {{ !$canEditProcurement ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom" style="font-size: 0.7rem;">Tanggal Update</label>
                                        <input type="date" name="last_update_task_secondary_material" class="form-control-custom form-control-sm" value="{{ $project->last_update_task_secondary_material ? $project->last_update_task_secondary_material->format('Y-m-d') : '' }}" {{ !$canEditProcurement ? 'disabled' : '' }}>
                                    </div>
                                </div>

                                <div class="row g-2 pt-2 border-top">
                                    <div class="col-6">
                                        <label class="form-label-custom" style="font-size: 0.7rem;">Status Pengiriman</label>
                                        @if(!$canEditProcurement) <input type="hidden" name="task_secondary_material_shipping_status" value="{{ $project->task_secondary_material_shipping_status }}"> @endif
                                        <select name="task_secondary_material_shipping_status" class="form-select form-select-sm {{ !$canEditProcurement ? 'form-control-readonly' : '' }}" {{ !$canEditProcurement ? 'disabled' : '' }}>
                                            @foreach(\App\Models\Project::$shippingStatusOptions as $optKey => $optLabel)
                                            <option value="{{ $optKey }}" {{ $project->task_secondary_material_shipping_status === $optKey ? 'selected' : '' }}>{{ $optLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom" style="font-size: 0.7rem;">Kesesuaian Barang</label>
                                        @if(!$canEditProcurement) <input type="hidden" name="task_secondary_material_match_status" value="{{ $project->task_secondary_material_match_status }}"> @endif
                                        <select name="task_secondary_material_match_status" class="form-select form-select-sm {{ !$canEditProcurement ? 'form-control-readonly' : '' }}" {{ !$canEditProcurement ? 'disabled' : '' }}>
                                            @foreach(\App\Models\Project::$matchStatusOptions as $optKey => $optLabel)
                                            <option value="{{ $optKey }}" {{ $project->task_secondary_material_match_status === $optKey ? 'selected' : '' }}>{{ $optLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <label class="task-box d-block {{ !$canEditProcurement ? 'disabled-box' : '' }}" for="task_painting_material">
                                @php $pct_task_painting_material = (int) round($project->task_painting_material); @endphp
                                @if(!$canEditProcurement)
                                    <input type="hidden" name="progress_task_painting_material" value="{{ $pct_task_painting_material }}">
                                @endif
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <div><div class="fw-bold">Painting</div><p class="small text-muted m-0">Pengadaan cat & material pengecatan.</p></div>
                                    <span class="badge-weight-custom flex-shrink-0">+{{ rtrim(rtrim(number_format(\App\Models\Project::$procurementTasks['task_painting_material'], 2), '0'), '.') }}%</span>
                                </div>
                                <div class="progress-input-group mb-2">
                                    <input type="number" min="0" max="100" step="1" inputmode="numeric" class="form-control form-control-sm progress-input" name="progress_task_painting_material" id="task_painting_material" data-weight="{{ \App\Models\Project::$procurementTasks['task_painting_material'] }}" value="{{ $pct_task_painting_material }}" {{ !$canEditProcurement ? 'disabled' : '' }}>
                                    <span class="progress-input-suffix">%</span>
                                </div>
                                @include('projects.partials.task-late', ['taskKey' => 'task_painting_material'])

                                @if(!$canEditProcurement)
                                    <input type="hidden" name="due_date_task_painting_material" value="{{ $project->due_date_task_painting_material ? $project->due_date_task_painting_material->format('Y-m-d') : '' }}">
                                    <input type="hidden" name="last_update_task_painting_material" value="{{ $project->last_update_task_painting_material ? $project->last_update_task_painting_material->format('Y-m-d') : '' }}">
                                @endif
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label-custom" style="font-size: 0.7rem;">Start Date</label>
                                        <input type="date" name="start_date_task_painting_material" class="form-control-custom form-control-sm" value="{{ $project->start_date_task_painting_material ? $project->start_date_task_painting_material->format('Y-m-d') : '' }}" {{ !$canEditProcurement ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom" style="font-size: 0.7rem;">Due Date</label>
                                        <input type="date" name="due_date_task_painting_material" class="form-control-custom form-control-sm" value="{{ $project->due_date_task_painting_material ? $project->due_date_task_painting_material->format('Y-m-d') : '' }}" {{ !$canEditProcurement ? 'disabled' : '' }}>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom" style="font-size: 0.7rem;">Tanggal Update</label>
                                        <input type="date" name="last_update_task_painting_material" class="form-control-custom form-control-sm" value="{{ $project->last_update_task_painting_material ? $project->last_update_task_painting_material->format('Y-m-d') : '' }}" {{ !$canEditProcurement ? 'disabled' : '' }}>
                                    </div>
                                </div>

                                <div class="row g-2 pt-2 border-top">
                                    <div class="col-6">
                                        <label class="form-label-custom" style="font-size: 0.7rem;">Status Pengiriman</label>
                                        @if(!$canEditProcurement) <input type="hidden" name="task_painting_material_shipping_status" value="{{ $project->task_painting_material_shipping_status }}"> @endif
                                        <select name="task_painting_material_shipping_status" class="form-select form-select-sm {{ !$canEditProcurement ? 'form-control-readonly' : '' }}" {{ !$canEditProcurement ? 'disabled' : '' }}>
                                            @foreach(\App\Models\Project::$shippingStatusOptions as $optKey => $optLabel)
                                            <option value="{{ $optKey }}" {{ $project->task_painting_material_shipping_status === $optKey ? 'selected' : '' }}>{{ $optLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label-custom" style="font-size: 0.7rem;">Kesesuaian Barang</label>
                                        @if(!$canEditProcurement) <input type="hidden" name="task_painting_material_match_status" value="{{ $project->task_painting_material_match_status }}"> @endif
                                        <select name="task_painting_material_match_status" class="form-select form-select-sm {{ !$canEditProcurement ? 'form-control-readonly' : '' }}" {{ !$canEditProcurement ? 'disabled' : '' }}>
                                            @foreach(\App\Models\Project::$matchStatusOptions as $optKey => $optLabel)
                                            <option value="{{ $optKey }}" {{ $project->task_painting_material_match_status === $optKey ? 'selected' : '' }}>{{ $optLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                    @include('projects.partials.weight-card', [
                        'anchor' => 'procurement-weight',
                        'title' => 'Tonase Total Material',
                        'value' => $project->total_material_weight_kg,
                        'canEdit' => $canEditProcurement,
                        'modalId' => 'modalMaterialWeight',
                    ])
                @include('projects.partials.division-problem', ['division' => 'procurement', 'canInput' => $canEditProcurement])
                </div>

                <!-- 2. DIVISI PPIC -->
                <div class="division-header-box">
                    <div class="division-title-bar">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <h6 class="fw-bold m-0 text-success"><i class="bi bi-calendar3 me-1"></i> DIVISI PPIC</h6>
                            @include('projects.partials.task-dropdown', ['division' => 'ppic', 'info' => \App\Models\Project::$ppicTaskInfo, 'canEdit' => $canEditPPIC])
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success fw-bold">Bobot Divisi: {{ array_sum(\App\Models\Project::$ppicTasks) }}%</span>
                    </div>
                    <div class="row align-items-center g-3 mb-3 form-2col">
                        <div class="col-md-3">
                            <label class="form-label-custom">Due Date</label>
                            @if(!$canEditPPIC) <input type="hidden" name="due_date_ppic" value="{{ $project->due_date_ppic ? $project->due_date_ppic->format('Y-m-d') : '' }}"> @endif
                            <input type="date" name="due_date_ppic" class="form-control-custom {{ !$canEditPPIC ? 'form-control-readonly' : '' }}" value="{{ $project->due_date_ppic ? $project->due_date_ppic->format('Y-m-d') : '' }}" {{ !$canEditPPIC ? 'disabled' : '' }}>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Planning (%)</label>
                            @if(!$canEditPPIC) <input type="hidden" name="plan_ppic" value="{{ $project->plan_ppic }}"> @endif
                            <input type="number" name="plan_ppic" class="form-control-custom {{ !$canEditPPIC ? 'form-control-readonly' : '' }}" value="{{ $project->plan_ppic }}" min="0" max="100" {{ !$canEditPPIC ? 'disabled' : '' }}>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Actual Progress Divisi</label>
                            <input type="text" id="display-progress-ppic" class="form-control-custom form-control-readonly text-success fw-bold" value="{{ $project->progress_ppic }}%" readonly>
                        </div>
                        <div class="col-md-3 text-end">
                            @php $deadlineInfo = $project->getDepartmentDeadlineInfo('ppic'); @endphp
                            <small class="{{ $deadlineInfo['text_class'] }} fw-bold d-block"><i class="bi {{ $deadlineInfo['icon'] }} me-1"></i> {{ $deadlineInfo['label'] }}</small>
                        </div>
                    </div>
                    @include('projects.partials.late-summary', ['division' => 'ppic', 'info' => \App\Models\Project::$ppicTaskInfo])
                    @include('projects.partials.task-grid', ['division' => 'ppic', 'info' => \App\Models\Project::$ppicTaskInfo, 'canEdit' => $canEditPPIC])
                @include('projects.partials.division-problem', ['division' => 'ppic', 'canInput' => $canEditPPIC])
                </div>

                <!-- 4. DIVISI PRODUCTION -->
                <div class="division-header-box">
                    <div class="division-title-bar">
                        <h6 class="fw-bold m-0 text-success"><i class="bi bi-tools me-1"></i> DIVISI PRODUCTION</h6>
                        <span class="badge bg-success-subtle text-success border border-success fw-bold">Bobot Divisi: 50%</span>
                    </div>
                    <div class="row align-items-center g-3 mb-3 form-2col">
                        <div class="col-md-3">
                            <label class="form-label-custom">Due Date</label>
                            @if(!$canEditProduction) <input type="hidden" name="due_date_production" value="{{ $project->due_date_production ? $project->due_date_production->format('Y-m-d') : '' }}"> @endif
                            <input type="date" name="due_date_production" class="form-control-custom {{ !$canEditProduction ? 'form-control-readonly' : '' }}" value="{{ $project->due_date_production ? $project->due_date_production->format('Y-m-d') : '' }}" {{ !$canEditProduction ? 'disabled' : '' }}>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Planning (%)</label>
                            @if(!$canEditProduction) <input type="hidden" name="plan_production" value="{{ $project->plan_production }}"> @endif
                            <input type="number" name="plan_production" class="form-control-custom {{ !$canEditProduction ? 'form-control-readonly' : '' }}" value="{{ $project->plan_production }}" min="0" max="100" {{ !$canEditProduction ? 'disabled' : '' }}>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Actual Progress Divisi</label>
                            <input type="text" id="display-progress-production" class="form-control-custom form-control-readonly text-success fw-bold" value="{{ $project->progress_production }}%" readonly>
                        </div>
                        <div class="col-md-3 text-end">
                            @php $deadlineInfo = $project->getDepartmentDeadlineInfo('production'); @endphp
                            <small class="{{ $deadlineInfo['text_class'] }} fw-bold d-block"><i class="bi {{ $deadlineInfo['icon'] }} me-1"></i> {{ $deadlineInfo['label'] }}</small>
                        </div>
                    </div>

                    {{-- 2 TAHAP: Preparation (Marking, Cutting) & Production (Drilling, Fit Up, Welding, Finishing).
                         Tiap TAHAP punya Due Date / Planning (%) / Actual (%) sendiri (seperti "mini divisi"),
                         sedangkan tiap job di dalamnya tetap checklist Belum/Sedang/Selesai seperti divisi lain. --}}
                    @foreach(\App\Models\Project::$productionStages as $stageKey => $stage)
                        @php
                            $stageFieldKey = $stage['field_key'];
                            $stageDueAttr  = "due_date_{$stageFieldKey}";
                            $stagePlanAttr = "plan_{$stageFieldKey}";
                            $stageWeight   = array_sum($stage['tasks']);
                            $stageDeadline = $project->getDepartmentDeadlineInfo($stageFieldKey);
                            $stageActual   = $project->getProductionStageProgress($stageKey);
                        @endphp
                        <div class="prod-stage-block {{ !$loop->first ? 'mt-3' : '' }}">
                            @php
                                $stageInfo = [];
                                foreach (array_keys($stage['tasks']) as $sk) { $stageInfo[$sk] = \App\Models\Project::$productionTaskInfo[$sk]; }
                            @endphp
                            <div class="prod-stage-title">
                                <span class="d-flex align-items-center gap-3 flex-wrap">
                                    <span><i class="bi {{ $stage['icon'] }} me-1"></i> {{ $stage['label'] }}</span>
                                    @include('projects.partials.task-dropdown', ['division' => $stageKey, 'info' => $stageInfo, 'canEdit' => $canEditProduction])
                                </span>
                                <span class="badge bg-success-subtle text-success border border-success">Bobot Tahap: {{ $stageWeight }}%</span>
                            </div>

                            <div class="row align-items-center g-3 mb-3 form-2col">
                                <div class="col-md-3">
                                    <label class="form-label-custom">Due Date</label>
                                    @if(!$canEditProduction) <input type="hidden" name="{{ $stageDueAttr }}" value="{{ $project->$stageDueAttr ? $project->$stageDueAttr->format('Y-m-d') : '' }}"> @endif
                                    <input type="date" name="{{ $stageDueAttr }}" class="form-control-custom {{ !$canEditProduction ? 'form-control-readonly' : '' }}" value="{{ $project->$stageDueAttr ? $project->$stageDueAttr->format('Y-m-d') : '' }}" {{ !$canEditProduction ? 'disabled' : '' }}>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label-custom">Planning (%)</label>
                                    @if(!$canEditProduction) <input type="hidden" name="{{ $stagePlanAttr }}" value="{{ $project->$stagePlanAttr }}"> @endif
                                    <input type="number" name="{{ $stagePlanAttr }}" class="form-control-custom {{ !$canEditProduction ? 'form-control-readonly' : '' }}" value="{{ $project->$stagePlanAttr }}" min="0" max="100" {{ !$canEditProduction ? 'disabled' : '' }}>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label-custom">Actual (%) Tahap</label>
                                    <input type="text" id="display-progress-stage-{{ $stageKey }}" class="form-control-custom form-control-readonly text-success fw-bold" value="{{ $stageActual }}%" readonly>
                                </div>
                                <div class="col-md-3 text-end">
                                    <small class="{{ $stageDeadline['text_class'] }} fw-bold d-block"><i class="bi {{ $stageDeadline['icon'] }} me-1"></i> {{ $stageDeadline['label'] }}</small>
                                </div>
                            </div>

                            @include('projects.partials.late-summary', ['division' => $stageKey, 'info' => $stageInfo])
                    @include('projects.partials.task-grid', ['division' => $stageKey, 'info' => $stageInfo, 'canEdit' => $canEditProduction])
                        </div>
                    @endforeach
                @include('projects.partials.division-problem', ['division' => 'production', 'canInput' => $canEditProduction])
                </div>

                <!-- 5. DIVISI FINISH PROCESS (dulu Finishing; tanpa MDR, NDT & BAPP) -->
                <div class="division-header-box">
                    <div class="division-title-bar">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <h6 class="fw-bold m-0 text-success"><i class="bi bi-shield-check me-1"></i> DIVISI FINISH PROCESS</h6>
                            @include('projects.partials.task-dropdown', ['division' => 'finishing', 'info' => \App\Models\Project::$qcTaskInfo, 'canEdit' => $canEditFinishing])
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success fw-bold">Bobot Divisi: {{ array_sum(\App\Models\Project::$qcTasks) }}%</span>
                    </div>
                    <div class="row align-items-center g-3 mb-3 form-2col">
                        <div class="col-md-3">
                            <label class="form-label-custom">Due Date</label>
                            @if(!$canEditFinishing) <input type="hidden" name="due_date_qc" value="{{ $project->due_date_qc ? $project->due_date_qc->format('Y-m-d') : '' }}"> @endif
                            <input type="date" name="due_date_qc" class="form-control-custom {{ !$canEditFinishing ? 'form-control-readonly' : '' }}" value="{{ $project->due_date_qc ? $project->due_date_qc->format('Y-m-d') : '' }}" {{ !$canEditFinishing ? 'disabled' : '' }}>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Planning (%)</label>
                            @if(!$canEditFinishing) <input type="hidden" name="plan_qc" value="{{ $project->plan_qc }}"> @endif
                            <input type="number" name="plan_qc" class="form-control-custom {{ !$canEditFinishing ? 'form-control-readonly' : '' }}" value="{{ $project->plan_qc }}" min="0" max="100" {{ !$canEditFinishing ? 'disabled' : '' }}>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Actual Progress Divisi</label>
                            <input type="text" id="display-progress-qc" class="form-control-custom form-control-readonly text-success fw-bold" value="{{ $project->progress_qc }}%" readonly>
                        </div>
                        <div class="col-md-3 text-end">
                            @php $deadlineInfo = $project->getDepartmentDeadlineInfo('qc'); @endphp
                            <small class="{{ $deadlineInfo['text_class'] }} fw-bold d-block"><i class="bi {{ $deadlineInfo['icon'] }} me-1"></i> {{ $deadlineInfo['label'] }}</small>
                        </div>
                    </div>
                    @include('projects.partials.late-summary', ['division' => 'finishing', 'info' => \App\Models\Project::$qcTaskInfo])
                    @include('projects.partials.task-grid', ['division' => 'finishing', 'info' => \App\Models\Project::$qcTaskInfo, 'canEdit' => $canEditFinishing])
                @include('projects.partials.division-problem', ['division' => 'finishing', 'canInput' => $canEditFinishing])
                </div>

                <!-- 6. DIVISI QA/QC (MDR & NDT, dipisah dari Finishing) -->
                <div class="division-header-box">
                    <div class="division-title-bar">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <h6 class="fw-bold m-0 text-success"><i class="bi bi-patch-check-fill me-1"></i> DIVISI QA/QC</h6>
                            @php $qaqcInfo = $project->qaqcInfoWithCustom(); @endphp
                            @include('projects.partials.task-dropdown', ['division' => 'qaqc', 'info' => $qaqcInfo, 'canEdit' => $canEditQC])
                            @if($canEditQC)
                                <input type="hidden" name="custom_tasks_submitted" value="1">
                                <button type="button" class="btn btn-sm btn-success fw-semibold no-print" id="btnAddCustomTask" data-bs-toggle="modal" data-bs-target="#modalAddCustomTask">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah Jobdesk
                                </button>
                            @endif
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success fw-bold">Bobot Divisi: {{ rtrim(rtrim(number_format(\App\Models\Project::QAQC_TOTAL, 2), '0'), '.') }}%</span>
                    </div>
                    <div class="row align-items-center g-3 mb-3 form-2col">
                        <div class="col-md-3">
                            <label class="form-label-custom">Due Date</label>
                            @if(!$canEditQC) <input type="hidden" name="due_date_qaqc" value="{{ $project->due_date_qaqc ? $project->due_date_qaqc->format('Y-m-d') : '' }}"> @endif
                            <input type="date" name="due_date_qaqc" class="form-control-custom {{ !$canEditQC ? 'form-control-readonly' : '' }}" value="{{ $project->due_date_qaqc ? $project->due_date_qaqc->format('Y-m-d') : '' }}" {{ !$canEditQC ? 'disabled' : '' }}>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Planning (%)</label>
                            @if(!$canEditQC) <input type="hidden" name="plan_qaqc" value="{{ $project->plan_qaqc }}"> @endif
                            <input type="number" name="plan_qaqc" class="form-control-custom {{ !$canEditQC ? 'form-control-readonly' : '' }}" value="{{ $project->plan_qaqc }}" min="0" max="100" {{ !$canEditQC ? 'disabled' : '' }}>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Actual Progress Divisi</label>
                            <input type="text" id="display-progress-qaqc" class="form-control-custom form-control-readonly text-success fw-bold" value="{{ $project->progress_qaqc }}%" readonly>
                        </div>
                        <div class="col-md-3 text-end">
                            @php $deadlineInfo = $project->getDepartmentDeadlineInfo('qaqc'); @endphp
                            <small class="{{ $deadlineInfo['text_class'] }} fw-bold d-block"><i class="bi {{ $deadlineInfo['icon'] }} me-1"></i> {{ $deadlineInfo['label'] }}</small>
                        </div>
                    </div>
                    @include('projects.partials.late-summary', ['division' => 'qaqc', 'info' => $qaqcInfo])
                    @include('projects.partials.task-grid', ['division' => 'qaqc', 'info' => $qaqcInfo, 'canEdit' => $canEditQC])
                    @include('projects.partials.weight-card', [
                        'anchor' => 'finished-weight',
                        'title' => 'Tonase Total Proyek Selesai',
                        'value' => $project->finished_project_weight_kg,
                        'canEdit' => $canEditQC,
                        'modalId' => 'modalFinishedWeight',
                    ])
                @include('projects.partials.division-problem', ['division' => 'qaqc', 'canInput' => $canEditQC])
                </div>
            </div>

            <!-- SECTION: REMARKS REPORT -->
            @php
                $remarksRow   = $project->remarksRow();
                $remarksCount = count($remarksRow['problems']) + count($remarksRow['tasks']) + count($remarksRow['pics']);
            @endphp
            <div class="custom-card p-4 mb-4 bg-white rounded-3 border" id="remarks-section">
                <div class="section-header d-flex justify-content-between align-items-center">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-chat-square-text-fill text-warning fs-5"></i>
                        <span>REMARKS REPORT</span>
                    </span>
                    <span class="d-flex gap-2 no-print">
                        @if($remarksCount > 0)
                            <button type="button" class="btn btn-sm btn-outline-danger fw-bold px-3"
                                    data-remark-delete="all" data-label="seluruh isi Remarks Report"
                                    data-action="{{ route('projects.remarks.clear', $project->id) }}">
                                <i class="bi bi-trash3 me-1"></i> Hapus Semua
                            </button>
                        @endif
                        <button type="button" class="btn btn-sm btn-warning fw-bold px-3" data-bs-toggle="modal" data-bs-target="#modalRemarks">
                            <i class="bi bi-pencil-square me-1"></i> Update Remarks
                        </button>
                    </span>
                </div>

                @if(session('remarks_status'))
                    <div class="alert alert-success py-2 small no-print">{{ session('remarks_status') }}</div>
                @endif
                @if(session('remarks_error'))
                    <div class="alert alert-danger py-2 small no-print">{{ session('remarks_error') }}</div>
                @endif

                @include('reports.partials.remarks-table', ['remarkRows' => [$remarksRow], 'emptyText' => 'Belum ada Remarks Report.', 'remarkProjectId' => $project->id])

                @if($remarksRow['updated_at'])
                    <small class="text-muted d-block mt-2">Terakhir diperbarui: {{ $remarksRow['updated_at']->format('d M Y H:i') }}</small>
                @endif
            </div>

            <!-- SECTION: S-CURVE PROGRESS (rencana dari Due Date + Planning % divisi vs aktual harian) -->
            @php $canCorrectHistory = in_array(strtolower((string) (auth()->user()->role ?? '')), ['admin', 'ppic'], true); @endphp
            <div class="custom-card p-4 mb-4 bg-white rounded-3 border" id="scurve-section">
                <div class="section-header d-flex justify-content-between align-items-center">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-graph-up-arrow text-success fs-5"></i>
                        <span>S-CURVE PROGRESS</span>
                    </span>
                    @if($canCorrectHistory)
                        <button type="button" class="btn btn-sm btn-outline-warning fw-bold px-3 no-print" data-bs-toggle="modal" data-bs-target="#modalProgressHistory">
                            <i class="bi bi-clock-history me-1"></i> Koreksi Riwayat
                        </button>
                    @endif
                </div>

                @if(session('scurve_status'))
                    <div class="alert alert-success py-2 small no-print">{{ session('scurve_status') }}</div>
                @endif
                @if(session('scurve_error'))
                    <div class="alert alert-danger py-2 small no-print">{{ session('scurve_error') }}</div>
                @endif

                @include('projects.partials.scurve-card', ['sc' => $sCurve, 'canvasId' => 'scurveChartEdit'])
            </div>

            <!-- SECTION: WEEKLY REPORT (FOTO PROGRESS) -- di bawah S-Curve; popup input ada di luar form utama -->
            @include('projects.partials.weekly-report', ['project' => $project, 'canWeekly' => $canCorrectHistory])

            <!-- SECTION: DELIVERY & PENYELESAIAN PROYEK -- di bawah Weekly Report (Foto Progress) -->
            <div class="custom-card p-4 mb-4 bg-white rounded-3 border" id="finish-section">
                <div class="section-header">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-truck text-success fs-5"></i>
                        <span>DELIVERY & PENYELESAIAN PROYEK</span>
                    </span>
                </div>
                <div class="row g-3 form-2col">
                    <div class="col-md-6">
                        <label class="form-label-custom">Actual Delivery Date <span class="text-muted fw-normal">(Realisasi)</span></label>
                        @if(!$canEditDelivery) <input type="hidden" name="actual_delivery_date" value="{{ $project->actual_delivery_date ? $project->actual_delivery_date->format('Y-m-d') : '' }}"> @endif
                        <input type="date" name="actual_delivery_date" class="form-control-custom {{ !$canEditDelivery ? 'form-control-readonly' : '' }}" value="{{ $project->actual_delivery_date ? $project->actual_delivery_date->format('Y-m-d') : '' }}" {{ !$canEditDelivery ? 'disabled' : '' }}>
                        @php $deliveryComparison = $project->delivery_comparison; @endphp
                        <span class="badge {{ $deliveryComparison['badge_class'] }} mt-2">{{ $deliveryComparison['label'] }}</span>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">Tanggal Aktual Selesai Project</label>
                        @if(!$canEditDelivery) <input type="hidden" name="actual_finish_date" value="{{ $project->actual_finish_date ? $project->actual_finish_date->format('Y-m-d') : '' }}"> @endif
                        <input type="date" name="actual_finish_date" id="input_actual_finish_date" class="form-control-custom {{ !$canEditDelivery ? 'form-control-readonly' : '' }}" value="{{ old('actual_finish_date', $project->actual_finish_date ? $project->actual_finish_date->format('Y-m-d') : '') }}" {{ !$canEditDelivery ? 'disabled' : '' }}>
                        <div class="form-text" style="font-size:.7rem">Isi saat proyek benar-benar selesai. Hanya bisa tersimpan jika <strong>semua jobdesk sudah 100%</strong>. Setelah tersimpan, status proyek menjadi <strong>CLOSE</strong> (atau <strong>CLOSE TERLAMBAT</strong> bila lewat Due Date).</div>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: INPUT DAILY REPORT DEPARTEMEN -->
            @if($canSubmitAny)
            <div class="custom-card p-4 mb-4 bg-white rounded-3 border">
                <div class="section-header">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-journal-plus text-success fs-5"></i>
                        <span>3. LAPORAN HARIAN DEPARTEMEN (DAILY REPORT - {{ strtoupper($role) }})</span>
                    </span>
                    <span class="badge bg-white text-success border border-success-subtle fw-semibold">Langkah 3</span>
                </div>

                <div class="row g-3 report-input-row">
                    <div class="col-md-3">
                        <label class="form-label-custom">Tanggal Laporan</label>
                        <input type="date" name="report_date" class="form-control-custom" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-custom">Nama PIC / Pelapor</label>
                        <input type="text" name="pic_name" class="form-control-custom" placeholder="Contoh: Rangga / Sekar">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">Catatan Perkembangan Harian Tim {{ strtoupper($role) }}</label>
                        <input type="text" name="daily_note" class="form-control-custom" placeholder="Contoh: Master schedule sudah dibuat...">
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-info d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="bi bi-info-circle-fill fs-5"></i>
                <div>
                    Anda sedang memantau halaman proyek ini dalam mode <strong>Read-Only (Hanya Melihat)</strong>. Pengisian Laporan Harian hanya diizinkan untuk PIC Departemen yang berwenang.
                </div>
            </div>
            @endif

            <!-- SECTION 4: HISTORI DAILY REPORT DEPARTEMEN (PRINT AREA) -->
            <div class="custom-card p-4 mb-4 bg-white rounded-3 border print-area">
                <div class="section-header d-flex justify-content-between align-items-center no-print">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-clock-history text-success fs-5"></i>
                        <span>RIWAYAT HISTORI DAILY REPORT (ALL DEPARTMENTS)</span>
                    </span>
                    <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-success fw-bold px-3 py-1 rounded-2">
                        <i class="bi bi-printer me-1"></i> Cetak / Export PDF
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" style="font-size: 0.88rem;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 15%;">TANGGAL</th>
                                <th style="width: 15%;">DEPARTEMEN</th>
                                <th style="width: 20%;">PELAPOR (PIC DEPARTEMEN)</th>
                                <th style="width: 12%; text-align: center;">PROGRESS</th>
                                <th>CATATAN KEGIATAN HARIAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($project->dailyReports as $report)
                            @php
                                $noteText = $report->notes;
                                $picDisplay = $report->user->name ?? 'System User';

                                if (preg_match('/^\[PIC:\s*([^\]]+)\]\s*(.*)$/i', $noteText, $matches)) {
                                    $picDisplay = trim($matches[1]);
                                    $noteText   = trim($matches[2]);
                                }
                            @endphp
                            <tr>
                                <td class="fw-bold text-secondary">{{ \Carbon\Carbon::parse($report->report_date)->format('d M Y') }}</td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success fw-bold text-uppercase">
                                        {{ strtoupper($report->department) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark text-capitalize">
                                        {{ $picDisplay }}
                                    </span>
                                </td>
                                <td class="text-center fw-bold text-success">{{ $report->progress_percentage }}%</td>
                                <td>{{ $noteText }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    Belum ada riwayat laporan harian untuk proyek ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SECTION 5: AUDIT TRAIL & ACTIVITY LOG (REKAM JEJAK SISTEM) -->
            <div class="custom-card p-4 mb-4 bg-white rounded-3 border print-area">
                <div class="section-header d-flex justify-content-between align-items-center no-print">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-success fs-5"></i>
                        <span>REKAM JEJAK AKTIVITAS (AUDIT TRAIL LOGS)</span>
                    </span>
                    <span class="d-flex align-items-center gap-2">
                        <span class="badge bg-white text-success border border-success-subtle fw-semibold">{{ ($project->activityLogs ?? collect())->count() }} catatan</span>
                        <button type="button" class="btn btn-sm btn-outline-success fw-semibold" id="auditTrailToggle"
                                data-bs-toggle="collapse" data-bs-target="#auditTrailCollapse"
                                aria-expanded="false" aria-controls="auditTrailCollapse">
                            <i class="bi bi-chevron-down" id="auditTrailIcon"></i> <span id="auditTrailLabel">Tampilkan</span>
                        </button>
                    </span>
                </div>

                <div class="collapse" id="auditTrailCollapse">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 15%;">WAKTU</th>
                                <th style="width: 18%;">USER & DIVISI</th>
                                <th style="width: 15%;">AKSI</th>
                                <th>DESKRIPSI / PERUBAHAN DATA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($project->activityLogs ?? [] as $log)
                            <tr>
                                <td class="fw-bold text-secondary">{{ $log->created_at->format('d M Y H:i') }} WIB</td>
                                <td>
                                    <strong class="d-block text-dark">{{ $log->user->name ?? 'System' }}</strong>
                                    <span class="badge bg-secondary text-uppercase" style="font-size: 0.65rem;">{{ $log->department }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark fw-bold" style="font-size: 0.72rem;">{{ $log->action }}</span>
                                </td>
                                <td>
                                    <span class="d-block text-dark mb-1">{{ $log->description }}</span>
                                    
                                    <!-- DETAIL PERUBAHAN KOLOM NILAI LAMA VS BARU -->
                                    @if(!empty($log->new_values))
                                        <details class="mt-1">
                                            <summary class="text-success cursor-pointer fw-bold no-print" style="font-size: 0.78rem; cursor: pointer;">
                                                <i class="bi bi-search me-1"></i> Lihat Rincian Kolom Diubah
                                            </summary>
                                            <div class="mt-2 p-2 bg-light rounded border">
                                                @php
                                                    // Nilai log bisa berupa array (mis. daftar jobdesk Engineering yang dimatikan) -- ubah jadi teks yang bisa ditampilkan
                                                    $fmtLogValue = function ($value, $field) {
                                                        if ($field === 'engineering_tasks_disabled') {
                                                            $names = collect((array) $value)
                                                                ->map(fn($k) => \App\Models\Project::$engineeringTaskInfo[$k]['name'] ?? $k)
                                                                ->implode(', ');
                                                            return $names !== '' ? 'Dimatikan: ' . $names : 'Semua jobdesk aktif';
                                                        }
                                                        if ($field === 'progress_history') {
                                                            return count((array) $value) ? implode(' | ', (array) $value) : '-';
                                                        }
                                                        if (in_array($field, ['remarks_problems', 'remarks_tasks', 'remarks_pics'], true)) {
                                                            $items = collect((array) $value)->map(function ($item) use ($field) {
                                                                $item = (array) $item;
                                                                if ($field === 'remarks_pics') {
                                                                    $deptKey = $item['department'] ?? '';
                                                                    return ($item['name'] ?? '') . ' (' . (\App\Models\Project::$remarkDepartments[$deptKey] ?? ($deptKey ?: '-')) . ')';
                                                                }
                                                                $date = $item['date'] ?? ($item['due_date'] ?? null);
                                                                return ($item['text'] ?? '') . ($date ? ' [' . \Carbon\Carbon::parse($date)->format('d/m/Y') . ']' : '');
                                                            });
                                                            return $items->isEmpty() ? '-' : $items->implode(' | ');
                                                        }
                                                        if (is_bool($value)) {
                                                            return $value ? 'TRUE' : 'FALSE';
                                                        }
                                                        if (is_array($value)) {
                                                            return json_encode($value, JSON_UNESCAPED_UNICODE);
                                                        }
                                                        return $value ?? 'null';
                                                    };
                                                @endphp
                                                <ul class="m-0 ps-3 small text-monospace">
                                                    @foreach($log->new_values as $key => $val)
                                                        <li>
                                                            <strong class="text-dark">{{ $key }}:</strong> 
                                                            <span class="text-danger text-decoration-line-through">{{ $fmtLogValue($log->old_values[$key] ?? null, $key) }}</span> 
                                                            &rarr; 
                                                            <span class="text-success fw-bold">{{ $fmtLogValue($val, $key) }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </details>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">
                                    <i class="bi bi-journal-text d-block fs-4 mb-1"></i>
                                    Belum ada catatan aktivitas perubahan data untuk proyek ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                </div>
            </div>

            <!-- BUTTON ACTIONS -->
            <div class="d-flex justify-content-between align-items-center mb-5 p-3 custom-card no-print form-actions-bar">
                <div class="d-flex align-items-center gap-2 form-actions-left">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary px-4 py-2 fw-bold rounded-3">Batal</a>
                    @if($canEditGeneral)
                        {{-- Hapus proyek: hanya admin & PPIC ($canEditGeneral); dikonfirmasi lewat popup di bawah --}}
                        <button type="button" class="btn btn-outline-danger px-4 py-2 fw-bold rounded-3" data-bs-toggle="modal" data-bs-target="#deleteProjectModal">
                            <i class="bi bi-trash3-fill me-1"></i> Hapus Proyek
                        </button>
                    @endif
                </div>
                
                @if($canSubmitAny)
                    <button type="submit" class="btn btn-success px-4 py-2 fw-bold rounded-3 shadow-sm form-actions-main" style="background-color: var(--mei-green-brand); border-color: var(--mei-green-brand);">
                        <i class="bi bi-check2-circle me-1"></i> Simpan & Perbarui Progress Proyek
                    </button>
                @else
                    <button type="button" class="btn btn-secondary px-4 py-2 fw-bold rounded-3 form-actions-main" disabled title="Anda hanya memiliki hak akses membaca (Read-Only)">
                        <i class="bi bi-lock-fill me-1"></i> Mode Pantau (Read-Only)
                    </button>
                @endif
            </div>
        </form> <!-- AKHIR FORM UTAMA PROYEK DITUTUP DI SINI -->

        @include('projects.partials.weight-modal', [
            'modalId' => 'modalMaterialWeight',
            'title' => 'Tonase Total Material',
            'field' => 'total_material_weight_kg',
            'value' => $project->total_material_weight_kg,
            'updateRoute' => route('projects.weights.material.update', $project->id),
            'deleteRoute' => route('projects.weights.material.delete', $project->id),
        ])
        @include('projects.partials.weight-modal', [
            'modalId' => 'modalFinishedWeight',
            'title' => 'Tonase Total Proyek Selesai',
            'field' => 'finished_project_weight_kg',
            'value' => $project->finished_project_weight_kg,
            'updateRoute' => route('projects.weights.finished.update', $project->id),
            'deleteRoute' => route('projects.weights.finished.delete', $project->id),
        ])

        @if($canEditGeneral)
        <!-- POPUP KONFIRMASI HAPUS PROYEK (admin & PPIC). Form-nya terpisah dari form utama. -->
        <div class="modal fade no-print" id="deleteProjectModal" tabindex="-1" aria-labelledby="deleteProjectModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold text-danger" id="deleteProjectModalLabel">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>Hapus Proyek?
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">Anda akan menghapus proyek:</p>
                        <div class="p-3 rounded-3 bg-light border mb-3">
                            <div class="fw-bold">{{ $project->project_code }}</div>
                            <div>{{ $project->project_name }}</div>
                        </div>
                        <p class="mb-0 text-danger small">
                            Seluruh data proyek ini (progress, kendala, catatan material, dan rekam jejak) akan terhapus permanen dan <strong>tidak dapat dikembalikan</strong>.
                        </p>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary fw-bold px-4" data-bs-dismiss="modal">Batal</button>
                        <form action="{{ route('projects.destroy', $project) }}" method="POST" class="m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger fw-bold px-4">
                                <i class="bi bi-trash3-fill me-1"></i> Ya, Hapus Proyek
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>


    {{-- POPUP: proyek belum bisa diselesaikan karena masih ada jobdesk < 100% --}}
    @if(session('finish_blocked'))
    <div class="modal fade" id="modalFinishBlocked" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning-subtle">
                    <h6 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> Project Belum Bisa Diselesaikan</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="small mb-2">Selesaikan dulu progress jobdesk berikut sampai <strong>100%</strong>, lalu tekan Update Progress lagi. Perubahan sebelumnya belum tersimpan.</p>
                    <ul class="list-group list-group-flush small">
                        @foreach(session('finish_blocked') as $row)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><span class="text-muted">{{ $row['division'] }}:</span> <strong>{{ $row['name'] }}</strong></span>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ $row['progress'] }}%</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success fw-bold px-4" data-bs-dismiss="modal">Mengerti</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('modalFinishBlocked');
            if (el && window.bootstrap) { new bootstrap.Modal(el).show(); }
        });
    </script>
    @endif

    <!-- MODAL FORM POPUP REMARKS REPORT (BERADA DI LUAR FORM UTAMA) -->
    <div class="modal fade" id="modalRemarks" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <form action="{{ route('projects.remarks.update', $project->id) }}" method="POST" id="formRemarks" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-warning text-dark">
                        <h6 class="modal-title fw-bold"><i class="bi bi-chat-square-text-fill me-1"></i> Remarks Report - {{ $project->project_code }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-lg-7">
                                <div class="mb-3">
                                    <label class="form-label-custom">Remark Problem &amp; Root Cause</label>
                                    <div class="remark-list" data-remark-list="problems"></div>
                                    <div class="form-text">Ketik masalahnya; tanggal masalah dan kolom Root Cause muncul di bawahnya. Nomor baris sejajar dengan Action dan PIC.</div>
                                </div>
                                <div>
                                    <label class="form-label-custom">Action</label>
                                    <div class="remark-list" data-remark-list="tasks"></div>
                                    <div class="form-text">Ketik action-nya, lalu isi tanggal due date yang muncul di sebelahnya.</div>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <label class="form-label-custom">PIC &amp; Evidence</label>
                                <div class="remark-list" data-remark-list="pics"></div>
                                <div class="form-text">Ketik nama PIC, pilih departemennya, lalu (opsional) isi evidence berupa file (PDF / gambar / Excel / Word maks 10 MB), teks ketikan, atau keduanya.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning btn-sm fw-bold px-4">UPDATE</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL KONFIRMASI HAPUS REMARKS (satu item atau semua) -->
    <div class="modal fade" id="modalRemarkDelete" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <form method="POST" id="formRemarkDelete">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="expected" id="remarkDeleteExpected" value="">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Hapus Remarks?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">Anda akan menghapus:</p>
                        <div class="p-3 rounded-3 bg-light border mb-3 fw-semibold" id="remarkDeleteLabel"></div>
                        <p class="mb-0 text-danger small">Data yang dihapus <strong>tidak dapat dikembalikan</strong>.</p>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary fw-bold px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger fw-bold px-4"><i class="bi bi-trash3-fill me-1"></i> Ya, Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($canCorrectHistory)
        @include('projects.partials.weekly-report-modal', ['project' => $project])
    @endif

    @if($canCorrectHistory)
    <!-- MODAL KOREKSI RIWAYAT PROGRESS (bahan garis aktual S-Curve) - admin & PPIC -->
    <div class="modal fade no-print" id="modalProgressHistory" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h6 class="modal-title fw-bold"><i class="bi bi-clock-history me-1"></i> Koreksi Riwayat Progress - {{ $project->project_code }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        Isi titik riwayat untuk melengkapi garis aktual: pilih jobdesk, tanggal progress itu tercapai, dan persennya.
                        Kalau pada tanggal itu sudah ada catatan untuk jobdesk yang sama, nilainya diganti. Garis aktual meneruskan nilai terakhir sampai ada titik berikutnya.
                    </p>

                    <form id="formProgressHistory" action="{{ route('projects.progress-history.store', $project->id) }}" method="POST">
                        @csrf
                        <div id="historyRows"></div>
                        <div class="d-flex justify-content-between mt-2">
                            <button type="button" id="btnAddHistoryRow" class="btn btn-sm btn-outline-success"><i class="bi bi-plus-lg me-1"></i> Tambah baris</button>
                            <button type="submit" class="btn btn-warning btn-sm fw-bold px-4">Simpan Riwayat</button>
                        </div>
                    </form>

                    <hr class="my-4">
                    <h6 class="fw-bold mb-2">Riwayat tercatat ({{ $progressHistory->count() }}{{ $progressHistory->count() >= 400 ? '+, 400 terbaru' : '' }})</h6>
                    <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                        <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 0.82rem;">
                            <thead class="table-light" style="position: sticky; top: 0;">
                                <tr><th>Tanggal</th><th>Jobdesk</th><th class="text-center">Progress</th><th class="text-center" style="width: 60px;">Hapus</th></tr>
                            </thead>
                            <tbody>
                                @forelse($progressHistory as $history)
                                    <tr>
                                        <td>{{ $history->log_date->format('d/m/Y') }}</td>
                                        <td>{{ $taskLabels[$history->task_key] ?? $history->task_key }}</td>
                                        <td class="text-center fw-bold">{{ $history->progress }}%</td>
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('projects.progress-history.destroy', [$project->id, $history->id]) }}" class="m-0" onsubmit="return confirm('Hapus titik riwayat ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Hapus"><i class="bi bi-trash3"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">Belum ada riwayat tercatat.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Rekam Jejak (Audit Trail): default tersembunyi, bisa ditampilkan/disembunyikan
        (function () {
            var panel = document.getElementById('auditTrailCollapse');
            if (!panel) { return; }
            var icon  = document.getElementById('auditTrailIcon');
            var label = document.getElementById('auditTrailLabel');
            panel.addEventListener('show.bs.collapse', function () {
                icon.className = 'bi bi-chevron-up';
                label.textContent = 'Sembunyikan';
            });
            panel.addEventListener('hide.bs.collapse', function () {
                icon.className = 'bi bi-chevron-down';
                label.textContent = 'Tampilkan';
            });
        })();
    </script>

    <script>
        // Dropdown "Jobdesk" Engineering: jobdesk yang tidak dicentang disembunyikan & tidak dihitung;
        // bobot divisi (10%) dibagi ulang proporsional ke jobdesk yang tersisa (sama dengan Project::activeEngineeringTasks).
        document.addEventListener('DOMContentLoaded', function () {
            const toggles = document.querySelectorAll('.eng-task-toggle');
            if (!toggles.length) { return; }

            const countEl = document.getElementById('engTaskCount');
            const divEl   = document.getElementById('display-progress-engineering');
            const base    = {};
            let totalBase = 0;

            toggles.forEach(function (t) {
                const badge = document.querySelector('[data-eng-task="' + t.value + '"] .eng-weight-badge');
                base[t.value] = badge ? parseFloat(badge.getAttribute('data-base-weight')) : 0;
                totalBase += base[t.value];
            });

            function fmt(n) { return (Math.round(n * 100) / 100).toString(); }

            function updateDivisionProgress() {
                let sum = 0;
                toggles.forEach(function (t) {
                    if (!t.checked) { return; }
                    const input = document.getElementById(t.value);
                    const val = Math.max(0, Math.min(100, parseInt(input.value, 10) || 0));
                    sum += parseFloat(input.getAttribute('data-weight') || 0) * val / 100;
                });
                if (divEl) { divEl.value = Math.round(sum / totalBase * 100) + '%'; }
            }

            function recalc() {
                let enabledSum = 0;
                let count = 0;
                toggles.forEach(function (t) { if (t.checked) { enabledSum += base[t.value]; count++; } });
                const scale = enabledSum > 0 ? totalBase / enabledSum : 0;

                toggles.forEach(function (t) {
                    const col   = document.querySelector('[data-eng-task="' + t.value + '"]');
                    const input = document.getElementById(t.value);
                    const badge = col.querySelector('.eng-weight-badge');
                    if (t.checked) {
                        const w = base[t.value] * scale;
                        col.classList.remove('d-none');
                        input.setAttribute('data-weight', w);
                        badge.textContent = '+' + fmt(w) + '%';
                    } else {
                        col.classList.add('d-none');
                        input.setAttribute('data-weight', 0);
                        badge.textContent = '+' + fmt(base[t.value]) + '%';
                    }
                });

                if (countEl) { countEl.textContent = count; }
                updateDivisionProgress();

                // Picu hitung ulang Actual Progress total proyek (SCRIPT 1 mendengarkan event 'input')
                const first = document.getElementById(toggles[0].value);
                if (first) { first.dispatchEvent(new Event('input', { bubbles: true })); }
            }

            toggles.forEach(function (t) {
                t.addEventListener('change', function () {
                    if (!t.checked && document.querySelectorAll('.eng-task-toggle:checked').length === 0) {
                        t.checked = true;
                        alert('Minimal satu jobdesk Engineering harus dipilih.');
                        return;
                    }
                    recalc();
                });
            });

            document.querySelectorAll('[data-eng-task] .progress-input').forEach(function (input) {
                input.addEventListener('input', updateDivisionProgress);
            });
        });
    </script>

    <script>
        // REMARKS REPORT: daftar isian bernomor. Baris baru muncul otomatis saat baris terakhir diisi.
        //  - Remark Problem : begitu mulai mengetik, tanggal masalah muncul (otomatis hari ini, bisa diubah)
        //  - Task           : begitu mulai mengetik, tanggal due date task revisi muncul
        //  - PIC            : begitu mulai mengetik, dropdown departemen muncul (semua departemen)
        document.addEventListener('DOMContentLoaded', function () {
            const DEPARTMENTS = @json(\App\Models\Project::$remarkDepartments);
            const JOBDESKS = @json(\App\Models\Project::remarkJobdeskOptions());
            const INITIAL = @json(['problems' => $remarksRow['problems'], 'tasks' => $remarksRow['tasks'], 'pics' => $remarksRow['pics']]);

            const CONFIG = {
                problems: { field: 'remarks_problems', textKey: 'text', extraKey: 'date',       placeholder: 'Contoh kalimat masalah...', max: 500 },
                tasks:    { field: 'remarks_tasks',    textKey: 'text', extraKey: 'due_date',   placeholder: 'Contoh kalimat action / tindakan...', max: 500 },
                pics:     { field: 'remarks_pics',     textKey: 'name', extraKey: 'department', placeholder: 'Nama PIC...', max: 100 }
            };

            function todayLocal() {
                const d = new Date();
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return d.getFullYear() + '-' + m + '-' + day;
            }

            // Dropdown button jobdesk (hanya untuk Remarks Problem): pilihan mengikuti divisi problem
            function buildJobdesk(data) {
                const wrap = document.createElement('div');
                wrap.className = 'dropdown remark-sub remark-jobdesk d-none';
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.className = 'remark-jobdesk-value';
                hidden.value = data ? (data.jobdesk || '') : '';
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-sm btn-outline-secondary dropdown-toggle w-100 text-start';
                btn.setAttribute('data-bs-toggle', 'dropdown');
                btn.setAttribute('data-bs-boundary', 'viewport');
                const menu = document.createElement('ul');
                menu.className = 'dropdown-menu shadow border-0 w-100';
                menu.style.maxHeight = '240px';
                menu.style.overflowY = 'auto';
                const divInput = function () { return wrap.parentNode ? wrap.parentNode.querySelector('.remark-division').value : ''; };

                function labelOf(key) {
                    const divs = divInput() ? [divInput()] : Object.keys(JOBDESKS);
                    for (let i = 0; i < divs.length; i++) {
                        if (JOBDESKS[divs[i]] && JOBDESKS[divs[i]][key]) { return JOBDESKS[divs[i]][key]; }
                    }
                    return '';
                }
                function refreshLabel() {
                    const l = labelOf(hidden.value);
                    btn.innerHTML = '<i class="bi bi-diagram-3 me-1"></i>Jobdesk: ' + (l ? '' : '<span class="text-muted">pilih jobdesk...</span>');
                    if (l) { btn.appendChild(document.createTextNode(l)); }
                }
                function pick(key) { hidden.value = key; refreshLabel(); }
                function fillMenu() {
                    menu.innerHTML = '';
                    const d = divInput();
                    const groups = d ? { [d]: JOBDESKS[d] || {} } : JOBDESKS;
                    const none = document.createElement('li');
                    const nb = document.createElement('button');
                    nb.type = 'button'; nb.className = 'dropdown-item small text-muted'; nb.textContent = '- Tidak ada / umum -';
                    nb.addEventListener('click', function () { pick(''); });
                    none.appendChild(nb); menu.appendChild(none);
                    Object.keys(groups).forEach(function (g) {
                        if (!d) {
                            const h = document.createElement('li');
                            h.innerHTML = '<h6 class="dropdown-header"></h6>';
                            h.firstChild.textContent = DEPARTMENTS[g] || g;
                            menu.appendChild(h);
                        }
                        Object.keys(groups[g]).forEach(function (k) {
                            const li = document.createElement('li');
                            const b = document.createElement('button');
                            b.type = 'button'; b.className = 'dropdown-item small'; b.textContent = groups[g][k];
                            b.addEventListener('click', function () { pick(k); });
                            li.appendChild(b); menu.appendChild(li);
                        });
                    });
                }
                btn.addEventListener('show.bs.dropdown', fillMenu);
                wrap.append(hidden, btn, menu);
                wrap._refresh = refreshLabel;
                setTimeout(refreshLabel, 0);
                return wrap;
            }

            function createRow(type, data) {
                const cfg = CONFIG[type];
                const row = document.createElement('div');
                row.className = 'remark-row';

                const num = document.createElement('span');
                num.className = 'remark-num';

                const text = document.createElement('input');
                text.type = 'text';
                text.className = 'form-control form-control-sm remark-text';
                text.placeholder = cfg.placeholder;
                text.maxLength = cfg.max;
                text.value = data ? (data[cfg.textKey] || '') : '';

                let extra;
                if (type === 'pics') {
                    extra = document.createElement('select');
                    extra.className = 'form-select form-select-sm remark-extra d-none';
                    const ph = document.createElement('option');
                    ph.value = '';
                    ph.textContent = 'Departemen...';
                    extra.appendChild(ph);
                    Object.keys(DEPARTMENTS).forEach(function (key) {
                        const opt = document.createElement('option');
                        opt.value = key;
                        opt.textContent = DEPARTMENTS[key];
                        extra.appendChild(opt);
                    });
                } else {
                    extra = document.createElement('input');
                    extra.type = 'date';
                    extra.className = 'form-control form-control-sm remark-extra d-none';
                }
                extra.value = data ? (data[cfg.extraKey] || '') : '';

                const del = document.createElement('button');
                del.type = 'button';
                del.className = 'remark-remove d-none';
                del.title = 'Hapus baris';
                del.innerHTML = '<i class="bi bi-x-lg"></i>';

                // Hidden: divisi asal item (dipertahankan saat disimpan ulang)
                const division = document.createElement('input');
                division.type = 'hidden';
                division.className = 'remark-division';
                division.value = data ? (data.division || '') : '';

                row.append(num, text, extra, del, division);

                // Baris kedua: Root Cause (problem) / Evidence (PIC)
                let sub = null, evPath = null, evName = null;
                if (type === 'problems') {
                    sub = document.createElement('input');
                    sub.type = 'text';
                    sub.className = 'form-control form-control-sm remark-sub remark-rootcause d-none';
                    sub.placeholder = 'Root cause (akar penyebab)...';
                    sub.maxLength = 500;
                    sub.value = data ? (data.root_cause || '') : '';
                    row.append(sub);
                    row.append(buildJobdesk(data));
                } else if (type === 'pics') {
                    sub = document.createElement('div');
                    sub.className = 'remark-sub remark-evidence d-none';
                    const file = document.createElement('input');
                    file.type = 'file';
                    file.className = 'form-control form-control-sm remark-file';
                    file.accept = '.pdf,.jpg,.jpeg,.png,.webp,.xlsx,.docx';
                    evPath = document.createElement('input'); evPath.type = 'hidden'; evPath.className = 'remark-ev-path';
                    evName = document.createElement('input'); evName.type = 'hidden'; evName.className = 'remark-ev-name';
                    const note = document.createElement('div');
                    note.className = 'remark-file-note';
                    if (data && data.evidence && data.evidence.path) {
                        evPath.value = data.evidence.path;
                        evName.value = data.evidence.name || '';
                        note.textContent = 'Evidence saat ini: ' + (data.evidence.name || 'file') + ' (pilih file baru untuk mengganti)';
                    } else {
                        note.textContent = 'Evidence (opsional)';
                    }
                    const evText = document.createElement('textarea');
                    evText.className = 'form-control form-control-sm remark-ev-text mt-1';
                    evText.rows = 2; evText.maxLength = 1000;
                    evText.placeholder = 'Atau ketik evidence berupa teks / keterangan / link...';
                    evText.value = data ? (data.evidence_text || '') : '';
                    sub.append(note, file, evPath, evName, evText);
                    row.append(sub);
                }
                return { row: row, text: text, extra: extra, del: del, sub: sub };
            }

            function initList(list) {
                const type = list.getAttribute('data-remark-list');
                const cfg = CONFIG[type];

                function rows() { return Array.prototype.slice.call(list.querySelectorAll('.remark-row')); }

                function reindex() {
                    rows().forEach(function (row, i) {
                        row.querySelector('.remark-num').textContent = (i + 1) + '.';
                        row.querySelector('.remark-text').name  = cfg.field + '[' + i + '][' + cfg.textKey + ']';
                        row.querySelector('.remark-extra').name = cfg.field + '[' + i + '][' + cfg.extraKey + ']';
                        row.querySelector('.remark-division').name = cfg.field + '[' + i + '][division]';
                        const jd = row.querySelector('.remark-jobdesk-value');
                        if (jd) { jd.name = cfg.field + '[' + i + '][jobdesk]'; }
                        const rc = row.querySelector('.remark-rootcause');
                        if (rc) { rc.name = cfg.field + '[' + i + '][root_cause]'; }
                        const fl = row.querySelector('.remark-file');
                        if (fl) {
                            fl.name = cfg.field + '[' + i + '][evidence]';
                            row.querySelector('.remark-ev-path').name = cfg.field + '[' + i + '][evidence_path]';
                            row.querySelector('.remark-ev-name').name = cfg.field + '[' + i + '][evidence_name]';
                            row.querySelector('.remark-ev-text').name = cfg.field + '[' + i + '][evidence_text]';
                        }
                    });
                }

                function isFilled(row) { return row.querySelector('.remark-text').value.trim() !== ''; }

                // Selalu tepat satu baris kosong di paling bawah
                function ensureTrailingBlank() {
                    let all = rows();
                    if (all.length === 0 || isFilled(all[all.length - 1])) {
                        addRow(null);
                    } else {
                        while (all.length > 1 && !isFilled(all[all.length - 1]) && !isFilled(all[all.length - 2])) {
                            list.removeChild(all[all.length - 1]);
                            all = rows();
                        }
                    }
                    reindex();
                }

                function syncRow(parts) {
                    const has = parts.text.value.trim() !== '';
                    parts.extra.classList.toggle('d-none', !has);
                    parts.del.classList.toggle('d-none', !has);
                    if (parts.sub) { parts.sub.classList.toggle('d-none', !has); }
                    const jdw = parts.row.querySelector('.remark-jobdesk');
                    if (jdw) { jdw.classList.toggle('d-none', !has); }
                    if (type === 'pics') { parts.extra.required = has; }
                    if (has && type === 'problems' && !parts.extra.value) { parts.extra.value = todayLocal(); }
                }

                function addRow(data) {
                    const parts = createRow(type, data);
                    list.appendChild(parts.row);
                    syncRow(parts);

                    parts.text.addEventListener('input', function () {
                        syncRow(parts);
                        ensureTrailingBlank();
                    });
                    parts.del.addEventListener('click', function () {
                        list.removeChild(parts.row);
                        ensureTrailingBlank();
                    });
                }

                (INITIAL[type] || []).forEach(function (item) { addRow(item); });
                ensureTrailingBlank();
            }

            document.querySelectorAll('[data-remark-list]').forEach(initList);

            // ---- Edit / hapus langsung dari tabel ----
            const modalEl    = document.getElementById('modalRemarks');
            const delModalEl = document.getElementById('modalRemarkDelete');
            let focusTarget  = null;

            // Edit: buka popup lalu fokus ke baris item yang dipilih
            document.querySelectorAll('[data-remark-edit]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    focusTarget = { type: btn.getAttribute('data-remark-edit'), index: parseInt(btn.getAttribute('data-index'), 10) };
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                });
            });
            modalEl.addEventListener('shown.bs.modal', function () {
                if (!focusTarget) { return; }
                const list = document.querySelector('[data-remark-list="' + focusTarget.type + '"]');
                const row  = list ? list.querySelectorAll('.remark-row')[focusTarget.index] : null;
                if (row) {
                    const input = row.querySelector('.remark-text');
                    row.scrollIntoView({ block: 'nearest' });
                    input.focus();
                    input.select();
                }
                focusTarget = null;
            });

            // Hapus: tampilkan popup konfirmasi (satu item, atau "Hapus Semua")
            document.querySelectorAll('[data-remark-delete]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const isAll = btn.getAttribute('data-remark-delete') === 'all';
                    document.getElementById('formRemarkDelete').action = btn.getAttribute('data-action');
                    document.getElementById('remarkDeleteExpected').value = isAll ? '' : btn.getAttribute('data-label');
                    document.getElementById('remarkDeleteLabel').textContent = btn.getAttribute('data-label');
                    bootstrap.Modal.getOrCreateInstance(delModalEl).show();
                });
            });
        });
    </script>

    @if($canCorrectHistory)
    <script>
        // Form Koreksi Riwayat Progress: baris dinamis (jobdesk, tanggal, persen)
        document.addEventListener('DOMContentLoaded', function () {
            const TASKS = @json($taskLabels);
            const container = document.getElementById('historyRows');
            const addBtn = document.getElementById('btnAddHistoryRow');
            if (!container || !addBtn) { return; }

            function todayLocal() {
                const d = new Date();
                return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
            }

            function reindex() {
                Array.prototype.slice.call(container.querySelectorAll('.history-row')).forEach(function (row, i) {
                    row.querySelector('.h-task').name = 'history[' + i + '][task_key]';
                    row.querySelector('.h-date').name = 'history[' + i + '][date]';
                    row.querySelector('.h-progress').name = 'history[' + i + '][progress]';
                });
            }

            function addRow() {
                const row = document.createElement('div');
                row.className = 'history-row d-flex gap-2 align-items-center mb-2';

                const select = document.createElement('select');
                select.className = 'form-select form-select-sm h-task';
                const ph = document.createElement('option');
                ph.value = '';
                ph.textContent = 'Pilih jobdesk...';
                select.appendChild(ph);
                Object.keys(TASKS).forEach(function (key) {
                    const opt = document.createElement('option');
                    opt.value = key;
                    opt.textContent = TASKS[key];
                    select.appendChild(opt);
                });

                const date = document.createElement('input');
                date.type = 'date';
                date.className = 'form-control form-control-sm h-date';
                date.max = todayLocal();
                date.style.maxWidth = '170px';

                const pct = document.createElement('input');
                pct.type = 'number';
                pct.min = 0;
                pct.max = 100;
                pct.step = 1;
                pct.placeholder = '%';
                pct.className = 'form-control form-control-sm h-progress';
                pct.style.maxWidth = '90px';

                const del = document.createElement('button');
                del.type = 'button';
                del.className = 'btn btn-sm btn-link text-secondary p-0';
                del.innerHTML = '<i class="bi bi-x-lg"></i>';
                del.addEventListener('click', function () {
                    container.removeChild(row);
                    if (container.children.length === 0) { addRow(); }
                    reindex();
                });

                row.append(select, date, pct, del);
                container.appendChild(row);
                reindex();
            }

            addBtn.addEventListener('click', addRow);
            for (let i = 0; i < 3; i++) { addRow(); }
        });
    </script>
    @endif

    <!-- POPUP DETAIL MATERIAL (Raw Material & Vendor Raw Material) -->
    @include('projects.partials.material-modal')

    @if($canEditQC)
    {{-- Modal "Tambah Jobdesk" QA/QC: judul & deskripsi diinput manual. Jobdesk baru ikut tersimpan saat tombol Simpan Progress ditekan. --}}
    <div class="modal fade" id="modalAddCustomTask" tabindex="-1" aria-labelledby="modalAddCustomTaskLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold" id="modalAddCustomTaskLabel"><i class="bi bi-plus-circle me-1 text-success"></i> Tambah Jobdesk QA/QC</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-custom" for="customTaskName">Judul Jobdesk</label>
                        <input type="text" id="customTaskName" class="form-control-custom" maxlength="120" placeholder="Contoh: Dimensional Check Akhir" autocomplete="off">
                        <div class="small text-danger mt-1 d-none" id="customTaskNameError">Judul jobdesk wajib diisi.</div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label-custom" for="customTaskDesc">Deskripsi</label>
                        <textarea id="customTaskDesc" class="form-control-custom" rows="3" maxlength="300" placeholder="Penjelasan singkat jobdesk ini"></textarea>
                    </div>
                    <div class="small text-muted">Jobdesk baru punya Start Date, Due Date, Tanggal Update, dan persen progress seperti jobdesk lain. Bobot divisi QA/QC tetap 6% (dibagi rata).</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success btn-sm fw-semibold" id="btnConfirmAddCustomTask"><i class="bi bi-plus-lg me-1"></i> Tambahkan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalRemoveCustomTask" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-body text-center p-4">
                    <i class="bi bi-trash3 text-danger fs-2"></i>
                    <h6 class="fw-bold mt-2">Hapus jobdesk ini?</h6>
                    <p class="small text-muted mb-3" id="removeCustomTaskName"></p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-danger btn-sm fw-semibold" id="btnConfirmRemoveCustomTask">Hapus</button>
                    </div>
                    <div class="small text-muted mt-2">Terhapus permanen setelah Simpan Progress ditekan.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Template kartu jobdesk custom baru (diisi JS) --}}
    <template id="customTaskTemplate">
        @include('projects.partials.task-card-custom', ['cId' => '__ID__', 'cKey' => '__KEY__', 'cName' => '', 'cDesc' => '', 'cEnabled' => true, 'cWeight' => \App\Models\Project::CUSTOM_BASE_WEIGHT, 'cTemplate' => true, 'canEdit' => true])
    </template>
    @endif

    <script>
        // Jobdesk custom QA/QC (tambah / hapus) & status Inspection Test Plan (Open / Waiting for Customer Approval / Close / N/A)
        document.addEventListener('DOMContentLoaded', function () {
            const grid = document.querySelector('[data-division-grid="qaqc"]');
            const menu = document.querySelector('[data-dd-menu="qaqc"]');
            const notify = function () { if (window.vsmDivTasks) { window.vsmDivTasks.recalc('qaqc'); } };
            const bump = function (delta) {
                const el = document.querySelector('[data-dd-total="qaqc"]');
                if (el) { el.textContent = Math.max(0, parseInt(el.textContent, 10) + delta); }
            };

            // ----- Status Inspection Test Plan -----
            const itpInput = document.getElementById('itpStatusInput');
            if (itpInput) {
                const col = document.querySelector('[data-div-task="task_inspection_plan"]');
                const pct = document.getElementById('task_inspection_plan');
                const btn = document.getElementById('itpStatusBtn');
                const label = document.getElementById('itpStatusLabel');
                const names = { open: 'Open', waiting: 'Waiting for Customer Approval', close: 'Close', na: 'N/A' };
                document.querySelectorAll('.itp-status-opt').forEach(function (opt) {
                    opt.addEventListener('click', function () {
                        const status = opt.getAttribute('data-status');
                        itpInput.value = status;
                        label.textContent = names[status];
                        btn.className = 'btn btn-sm w-100 dropdown-toggle fw-semibold itp-status-btn itp-' + status;
                        document.querySelectorAll('.itp-status-opt').forEach(function (o) { o.classList.toggle('active', o === opt); });
                        pct.value = status === 'close' ? 100 : 0;
                        if (status === 'na') { col.setAttribute('data-na', '1'); } else { col.removeAttribute('data-na'); }
                        notify();
                    });
                });
            }

            if (!grid || !menu) { return; }

            // ----- Tambah jobdesk custom -----
            const modalEl = document.getElementById('modalAddCustomTask');
            if (modalEl) {
                const nameEl = document.getElementById('customTaskName');
                const descEl = document.getElementById('customTaskDesc');
                const errEl  = document.getElementById('customTaskNameError');
                let seq = 0;

                modalEl.addEventListener('shown.bs.modal', function () { nameEl.focus(); });
                modalEl.addEventListener('hidden.bs.modal', function () { nameEl.value = ''; descEl.value = ''; errEl.classList.add('d-none'); });

                const add = function () {
                    const name = nameEl.value.trim();
                    if (!name) { errEl.classList.remove('d-none'); nameEl.focus(); return; }
                    seq++;
                    const id = 'new_' + seq;
                    const key = 'task_custom_' + id;
                    const html = document.getElementById('customTaskTemplate').innerHTML.replace(/__ID__/g, id).replace(/__KEY__/g, key);
                    const holder = document.createElement('div');
                    holder.innerHTML = html.trim();
                    const col = holder.firstElementChild;
                    col.setAttribute('data-div-task', key);
                    col.querySelector('[data-cf="name"]').value = name;
                    col.querySelector('[data-cf="desc"]').value = descEl.value.trim();
                    col.querySelector('[data-cf-text="name"]').textContent = name;
                    col.querySelector('[data-cf-text="desc"]').textContent = descEl.value.trim();
                    grid.appendChild(col);

                    // Item di dropdown "Jobdesk" (dicentang = aktif)
                    const li = document.createElement('li');
                    li.setAttribute('data-dd-item', key);
                    const label = document.createElement('label');
                    label.className = 'dropdown-item d-flex align-items-center justify-content-between gap-3 rounded-2';
                    label.style.cursor = 'pointer';
                    const span = document.createElement('span');
                    span.textContent = name;
                    const box = document.createElement('input');
                    box.type = 'checkbox';
                    box.className = 'form-check-input div-task-toggle m-0';
                    box.setAttribute('data-division', 'qaqc');
                    box.name = 'tasks_enabled[qaqc][]';
                    box.value = key;
                    box.checked = true;
                    label.append(span, box);
                    li.appendChild(label);
                    menu.appendChild(li);

                    window.vsmDivTasks.bindToggle(box);
                    window.vsmDivTasks.bindInput(col.querySelector('.progress-input'));
                    bump(1);
                    notify();
                    bootstrap.Modal.getInstance(modalEl).hide();
                    col.scrollIntoView({ behavior: 'smooth', block: 'center' });
                };
                document.getElementById('btnConfirmAddCustomTask').addEventListener('click', add);
                nameEl.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); add(); } });
            }

            // ----- Hapus jobdesk custom -----
            const removeModalEl = document.getElementById('modalRemoveCustomTask');
            if (removeModalEl) {
                let target = null;
                grid.addEventListener('click', function (e) {
                    const btn = e.target.closest('.custom-task-remove');
                    if (!btn) { return; }
                    target = btn.closest('[data-custom-task]');
                    document.getElementById('removeCustomTaskName').textContent = target.querySelector('[data-cf="name"]').value;
                    bootstrap.Modal.getOrCreateInstance(removeModalEl).show();
                });
                document.getElementById('btnConfirmRemoveCustomTask').addEventListener('click', function () {
                    if (!target) { return; }
                    const key = target.getAttribute('data-div-task');
                    const li = menu.querySelector('[data-dd-item="' + key + '"]');
                    const wasChecked = li && li.querySelector('input').checked;
                    if (li) { li.remove(); }
                    target.remove();
                    target = null;
                    bump(-1);
                    // Pengaman: minimal satu jobdesk aktif
                    if (!document.querySelector('.div-task-toggle[data-division="qaqc"]:checked')) {
                        const first = document.querySelector('.div-task-toggle[data-division="qaqc"]');
                        if (first) { first.checked = true; }
                    }
                    notify();
                    bootstrap.Modal.getInstance(removeModalEl).hide();
                });
            }
        });
    </script>


    <!-- SCRIPT 1: LOGIKA KALKULASI INTERAKTIF PROGRESS & WARNA (Merah 0-50% / Kuning 51-99% / Hijau 100%) -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const progressInputs = document.querySelectorAll('.progress-input');
            const displayActual = document.getElementById('display-actual');
            const displayGap = document.getElementById('display-gap');
            const displayStatusBadge = document.getElementById('display-status-badge');
            const displayProgressBar = document.getElementById('display-progress-bar');
            const inputPlan = document.getElementById('input_plan_progress');

            function clamp(value) {
                let val = parseInt(value, 10);
                if (isNaN(val)) val = 0;
                return Math.max(0, Math.min(100, val));
            }

            function applyColor(input, val) {
                const box = input.closest('.task-box');
                if (!box) return;
                box.classList.remove('not-started-active', 'in-progress-active', 'checked-active');
                if (val >= 100) {
                    box.classList.add('checked-active');       // Hijau: 100%
                } else if (val >= 51) {
                    box.classList.add('in-progress-active');    // Kuning: 51-99%
                } else {
                    box.classList.add('not-started-active');    // Merah: 0-50%
                }
            }

            function calculateProgress() {
                let totalActual = 0;

                progressInputs.forEach(inp => {
                    const val = clamp(inp.value);
                    const weight = parseFloat(inp.getAttribute('data-weight') || 0);
                    totalActual += weight * (val / 100);
                    applyColor(inp, val);
                });

                totalActual = Math.round(totalActual);
                const planVal = parseInt(inputPlan ? inputPlan.value : 0) || 0;
                const gap = totalActual - planVal;

                if (displayActual) displayActual.textContent = totalActual + '%';
                if (displayGap) displayGap.textContent = (gap >= 0 ? '+' : '') + gap + '%';

                // Diagram bulat Plan / Actual / Gap ikut diperbarui
                (function () {
                    const C = 2 * Math.PI * 52;
                    function setDonut(id, pct, color, text) {
                        const arc = document.getElementById('donut-' + id + '-arc');
                        const txt = document.getElementById('donut-' + id + '-text');
                        if (!arc || !txt) return;
                        const p = Math.max(0, Math.min(100, pct));
                        arc.setAttribute('stroke-dasharray', (C * p / 100).toFixed(2) + ' ' + C.toFixed(2));
                        arc.setAttribute('stroke', color);
                        txt.setAttribute('fill', color);
                        txt.textContent = text;
                    }
                    const planPct = Math.max(0, Math.min(100, planVal));
                    const actPct = Math.max(0, Math.min(100, totalActual));
                    setDonut('plan', planPct, '#16a34a', planPct + '%');
                    setDonut('actual', actPct, actPct >= 100 ? '#16a34a' : (actPct >= 51 ? '#d97706' : '#dc2626'), actPct + '%');
                    setDonut('gap', Math.min(100, Math.abs(gap)), gap >= 0 ? '#16a34a' : '#dc2626', (gap > 0 ? '+' : '') + gap + '%');
                })();

                if (displayProgressBar) {
                    displayProgressBar.style.width = totalActual + '%';
                    displayProgressBar.setAttribute('aria-valuenow', totalActual);
                }

                // Status (sama dengan Project::resolveStatus di server):
                // CLOSE / CLOSE TERLAMBAT = ada Tanggal Aktual Selesai | ON TRACK = 100% | DELAYED = lewat due date ATAU tertinggal >= 5% dari planning
                // AT RISK = tertinggal 1-4% dari planning | LEADING = sisanya
                const dueEl = document.querySelector('input[name="due_date"]');
                const finEl = document.getElementById('input_actual_finish_date');
                let daysLeft = null;
                let finishedLate = false;
                const parseD = (v) => { const dp = v.split('-'); return new Date(parseInt(dp[0], 10), parseInt(dp[1], 10) - 1, parseInt(dp[2], 10)); };
                if (dueEl && dueEl.value) {
                    const dueDay = parseD(dueEl.value);
                    const today = new Date(); today.setHours(0, 0, 0, 0);
                    daysLeft = Math.round((dueDay - today) / 86400000);
                    if (finEl && finEl.value) { finishedLate = parseD(finEl.value) > dueDay; }
                }
                const hasFinish = !!(finEl && finEl.value);
                const gapBehind = planVal - totalActual;

                let statusLabel, badgeClass, progressClass;

                if (hasFinish) {
                    statusLabel = finishedLate ? 'CLOSE TERLAMBAT' : 'CLOSE';
                    badgeClass = finishedLate ? 'status-badge-closed-late' : 'status-badge-on-track';
                    progressClass = finishedLate ? 'bg-warning' : 'bg-success';
                } else if (totalActual >= 100) {
                    statusLabel = 'ON TRACK';
                    badgeClass = 'status-badge-on-track';
                    progressClass = 'bg-success';
                } else if (daysLeft === null) {
                    statusLabel = 'Belum Ada Tenggat';
                    badgeClass = 'bg-secondary-subtle text-secondary border border-secondary';
                    progressClass = 'bg-secondary';
                } else if (daysLeft < 0 || gapBehind >= 5) {
                    statusLabel = 'DELAYED';
                    badgeClass = 'status-badge-delayed';
                    progressClass = 'bg-danger';
                } else if (gapBehind >= 1) {
                    statusLabel = 'AT RISK';
                    badgeClass = 'status-badge-at-risk';
                    progressClass = 'bg-warning';
                } else {
                    statusLabel = 'LEADING';
                    badgeClass = 'status-badge-leading';
                    progressClass = 'bg-leading';
                }

                if (displayProgressBar) displayProgressBar.className = `progress-bar ${progressClass} progress-bar-striped progress-bar-animated`;
                if (displayStatusBadge) {
                    displayStatusBadge.className = `badge rounded-pill px-3 py-2 fs-6 fw-bold ${badgeClass}`;
                    displayStatusBadge.textContent = statusLabel;
                }
            }

            progressInputs.forEach(inp => {
                // Update warna & total secara live saat mengetik
                inp.addEventListener('input', calculateProgress);
                // Klem ke rentang 0-100 begitu selesai mengisi (blur / pindah field)
                inp.addEventListener('change', function () {
                    inp.value = clamp(inp.value);
                    calculateProgress();
                });
            });
            if (inputPlan) inputPlan.addEventListener('input', calculateProgress);
            const dueDateInput = document.querySelector('input[name="due_date"]');
            if (dueDateInput) dueDateInput.addEventListener('change', calculateProgress);
            const finishDateInput = document.getElementById('input_actual_finish_date');
            if (finishDateInput) finishDateInput.addEventListener('change', calculateProgress);

            calculateProgress();
        });
    </script>

    <script>
        // Dropdown "Jobdesk" divisi lain (PPIC, Preparation, Production, Finish Process, QA/QC): sama seperti Engineering --
        // jobdesk yang tidak dicentang disembunyikan & tidak dihitung, bobot divisi/tahap dibagi ulang proporsional (total tetap).
        document.addEventListener('DOMContentLoaded', function () {
            const grids = document.querySelectorAll('[data-division-grid]');
            if (!grids.length) { return; }
            const displayId = { ppic: 'display-progress-ppic', finishing: 'display-progress-qc', qaqc: 'display-progress-qaqc',
                                preparation: 'display-progress-stage-preparation', main_production: 'display-progress-stage-main_production' };
            const totals = {};
            function fmt(n) { return (Math.round(n * 100) / 100).toString(); }

            function achieved(division) {
                let sum = 0;
                document.querySelectorAll('[data-div-task][data-division="' + division + '"]').forEach(function (col) {
                    if (col.classList.contains('d-none')) { return; }
                    const input = col.querySelector('.progress-input');
                    const val = Math.max(0, Math.min(100, parseInt(input.value, 10) || 0));
                    sum += parseFloat(input.getAttribute('data-weight') || 0) * val / 100;
                });
                return sum;
            }

            function refreshDisplays() {
                Object.keys(displayId).forEach(function (division) {
                    const el = document.getElementById(displayId[division]);
                    const total = totals[division];
                    if (el && total) { el.value = Math.round(achieved(division) / total * 100) + '%'; }
                });
                const prod = document.getElementById('display-progress-production');
                if (prod && totals.preparation && totals.main_production) {
                    prod.value = Math.round((achieved('preparation') + achieved('main_production')) / (totals.preparation + totals.main_production) * 100) + '%';
                }
            }

            grids.forEach(function (g) {
                const division = g.getAttribute('data-division-grid');
                let total = 0;
                g.querySelectorAll('.div-weight-badge').forEach(function (b) { total += parseFloat(b.getAttribute('data-base-weight')); });
                // Total bobot divisi bisa tetap (QA/QC selalu 6% walau ada jobdesk custom)
                const fixed = parseFloat(g.getAttribute('data-total-weight'));
                totals[division] = fixed > 0 ? fixed : total;
            });

            // Inspection Test Plan berstatus N/A tidak dihitung (tapi kartunya tetap tampil supaya statusnya bisa diubah lagi)
            function isNa(col) { return col && col.getAttribute('data-na') === '1'; }

            function recalc(division) {
                const toggles = document.querySelectorAll('.div-task-toggle[data-division="' + division + '"]');
                let enabledSum = 0, count = 0;
                const base = {};
                toggles.forEach(function (t) {
                    const col0 = document.querySelector('[data-div-task="' + t.value + '"]');
                    const badge = col0.querySelector('.div-weight-badge');
                    base[t.value] = parseFloat(badge.getAttribute('data-base-weight'));
                    if (t.checked) { count++; if (!isNa(col0)) { enabledSum += base[t.value]; } }
                });
                const scale = enabledSum > 0 ? totals[division] / enabledSum : 0;
                toggles.forEach(function (t) {
                    const col = document.querySelector('[data-div-task="' + t.value + '"]');
                    const input = col.querySelector('.progress-input');
                    const badge = col.querySelector('.div-weight-badge');
                    if (t.checked && isNa(col)) {
                        col.classList.remove('d-none');
                        input.setAttribute('data-weight', 0);
                        badge.textContent = 'N/A';
                    } else if (t.checked) {
                        const w = base[t.value] * scale;
                        col.classList.remove('d-none');
                        input.setAttribute('data-weight', w);
                        badge.textContent = '+' + fmt(w) + '%';
                    } else {
                        col.classList.add('d-none');
                        input.setAttribute('data-weight', 0);
                        badge.textContent = '+' + fmt(base[t.value]) + '%';
                    }
                });
                const countEl = document.querySelector('[data-dd-count="' + division + '"]');
                if (countEl) { countEl.textContent = count; }
                refreshDisplays();
                // Picu hitung ulang Actual Progress total proyek (SCRIPT 1 mendengarkan event 'input')
                const any = document.querySelector('.progress-input');
                if (any) { any.dispatchEvent(new Event('input', { bubbles: true })); }
            }

            function bindToggle(t) {
                t.addEventListener('change', function () {
                    const division = t.getAttribute('data-division');
                    if (!t.checked && document.querySelectorAll('.div-task-toggle[data-division="' + division + '"]:checked').length === 0) {
                        t.checked = true;
                        alert('Minimal satu jobdesk harus dipilih.');
                        return;
                    }
                    recalc(division);
                });
            }
            function bindInput(i) {
                i.addEventListener('input', refreshDisplays);
                i.addEventListener('change', refreshDisplays);
            }
            document.querySelectorAll('.div-task-toggle').forEach(bindToggle);
            document.querySelectorAll('.div-task-grid .progress-input').forEach(bindInput);

            // Dipakai skrip jobdesk custom & Inspection Test Plan di bawah
            window.vsmDivTasks = { recalc: recalc, bindToggle: bindToggle, bindInput: bindInput };
            recalc('qaqc');
        });
    </script>
@include('partials.motion', ['motionPage' => 'edit'])
</body>
</html>