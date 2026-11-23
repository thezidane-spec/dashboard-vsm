@extends('layouts.app')

@section('title', 'Report PPIC - PT Mudalaya Energy Indonesia')
@section('page_title', 'Summary Report')

@push('styles')
<style>
    .status-badge-on-track { background: #d1fae5; color: #065f46; border: 1.5px solid #a7f3d0; }
    .status-badge-leading { background: #1e3a8a; color: #ffffff; border: 1.5px solid #1e40af; }
    .status-badge-at-risk  { background: #fef3c7; color: #92400e; border: 1.5px solid #fde68a; }
    .status-badge-delayed  { background: #fee2e2; color: #991b1b; border: 1.5px solid #fca5a5; }

    .report-kpi { border: 0; border-radius: 12px; padding: 14px; text-align: center; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
    .report-kpi small { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }
    .report-kpi h3 { margin: 2px 0 0; font-weight: 800; }

    .report-table { font-size: 0.78rem; }
    .report-table thead th { background: #f1f5f9; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.3px; white-space: nowrap; text-align: center; vertical-align: middle; }
    .report-table td, .report-table th { vertical-align: middle; }
    .report-table td.text-center-num { text-align: center; white-space: nowrap; }
    .report-table td.report-name { min-width: 160px; white-space: normal; }

    .group-prep  { background: #dcfce7; color: #14532d; }
    .group-prod-prep { background: #e0f2fe; color: #075985; }
    .group-prod-main { background: #bfdbfe; color: #1e3a8a; }
    .group-prod  { background: #dbeafe; color: #1e3a8a; }
    .group-qaqc  { background: #ede9fe; color: #4c1d95; }
    .group-qc    { background: #fef3c7; color: #78350f; }
    .group-total-prep  { background: #bbf7d0 !important; font-weight: 800; }
    .group-total-prod  { background: #bfdbfe !important; font-weight: 800; }
    .group-total-qc    { background: #fde68a !important; font-weight: 800; }
    .group-total-qaqc  { background: #ddd6fe !important; font-weight: 800; }

    .task-pill { display: inline-block; min-width: 36px; padding: 2px 6px; border-radius: 6px; font-weight: 700; font-size: 0.68rem; }
    .task-pill-done { background: #d1fae5; color: #047857; }
    .task-pill-pending { background: #f1f5f9; color: #94a3b8; }

    /* Warna persen progress jobdesk/divisi -- sama seperti halaman Update Progress: 0-50 merah, 51-99 kuning, 100 hijau */
    .pct-cell { text-align: center; font-weight: 700; }
    .pct-pill { display: inline-block; min-width: 46px; padding: 2px 8px; border-radius: 6px; font-weight: 700; border: 1px solid transparent; }
    .pct-red { background-color: #fee2e2 !important; color: #b91c1c !important; }
    .pct-yellow { background-color: #fef3c7 !important; color: #b45309 !important; }
    .pct-green { background-color: #dcfce7 !important; color: #15803d !important; }

    .report-scroll { overflow-x: auto; }
</style>
@endpush

@section('content')
<div class="container-fluid p-0">

    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary no-print"><i class="bi bi-arrow-left"></i> Kembali</a>
        <h5 class="fw-bold m-0"><i class="bi {{ $meta['icon'] }} me-1 text-success"></i> Report - {{ $meta['label'] }}</h5>
    </div>
    <p class="text-muted small mb-3" style="max-width: 820px;">
        Tabel khusus PPIC: progres semua proyek dirangkum jadi 3 kelompok besar --
        <strong>Preparation ({{ array_sum(array_column($prepDivisions, 'weight')) }}%)</strong> gabungan Engineering 10%, Procurement 10% &amp; PPIC 7%,
        <strong>Production ({{ array_sum(array_column($productionTasks, 'weight')) }}%)</strong> checklist Marking s/d Finishing (termasuk Drilling),
        <strong>Finish Process ({{ array_sum(\App\Models\Project::$qcTasks) }}%)</strong> checklist Packing s/d Leak Test, dan
        <strong>QA/QC (6%)</strong> checklist MDR &amp; NDT.
        Bobot ini sama persis dengan bobot resmi aplikasi -- kolom Total Percentage di sini identik
        dengan Actual Progress yang tampil di halaman edit proyek &amp; dashboard.
    </p>

    {{-- RINGKASAN --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-md">
            <div class="report-kpi border-start border-4 border-secondary">
                <small class="text-secondary">Total Proyek</small>
                <h3>{{ $summary['total'] }}</h3>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="report-kpi border-start border-4 border-info">
                <small class="text-info">Rata-Rata Plan</small>
                <h3>{{ $summary['avg_plan'] }}%</h3>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="report-kpi border-start border-4" style="border-color: var(--mei-green-brand) !important;">
                <small style="color: var(--mei-green-brand);">Rata-Rata Actual</small>
                <h3 style="color: var(--mei-green-brand);">{{ $summary['avg_actual'] }}%</h3>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="report-kpi border-start border-4 border-success">
                <small class="text-success">On Track</small>
                <h3>{{ $summary['on_track'] }}</h3>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="report-kpi border-start border-4" style="border-color: #1e3a8a !important;">
                <small style="color: #1e3a8a;">Leading</small>
                <h3>{{ $summary['leading'] }}</h3>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="report-kpi border-start border-4 border-warning">
                <small class="text-warning">At Risk</small>
                <h3>{{ $summary['at_risk'] }}</h3>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="report-kpi border-start border-4 border-danger">
                <small class="text-danger">Delayed</small>
                <h3>{{ $summary['delayed'] }}</h3>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="report-kpi border-start border-4" style="border-color:#ea580c !important;">
                <small style="color:#ea580c;">Close Terlambat</small>
                <h3>{{ $summary['closed_late'] }}</h3>
            </div>
        </div>
    </div>

    {{-- FILTER TAHUN & PERIODE BULAN --}}
    <div class="card border-0 shadow-sm rounded-3 mb-3 p-3 bg-white no-print">
        <form method="GET" action="{{ route('reports.show', 'ppic') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <label class="form-label-custom small text-muted mb-1 d-block">Tahun</label>
                <select name="year" class="form-select bg-light">
                    <option value="all" {{ $selectedYear === 'all' ? 'selected' : '' }}>-- Semua Tahun --</option>
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ (string) $selectedYear === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label-custom small text-muted mb-1 d-block">Periode Bulan</label>
                <select name="month" class="form-select bg-light" {{ $selectedYear === 'all' ? 'disabled' : '' }}>
                    <option value="">-- Semua Bulan --</option>
                    @foreach($monthNames as $num => $label)
                        <option value="{{ $num }}" {{ (string) $selectedMonth === (string) $num ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success w-100 fw-bold mt-4" style="background-color: var(--mei-green-brand);">
                    <i class="bi bi-funnel-fill me-1"></i> Filter
                </button>
            </div>
            <div class="col-md-4 text-md-end mt-4">
                {{-- Export mengikuti filter Tahun/Bulan yang sedang dipilih (href diperbarui oleh script di bawah saat diklik) --}}
                <a href="{{ route('reports.ppic.excel', array_filter(['year' => $selectedYear, 'month' => $selectedMonth])) }}"
                   data-report-export data-base="{{ route('reports.ppic.excel') }}"
                   class="btn btn-outline-success fw-semibold"><i class="bi bi-file-earmark-excel me-1"></i> Export Excel</a>
                <a href="{{ route('reports.ppic.print', array_filter(['year' => $selectedYear, 'month' => $selectedMonth])) }}"
                   data-report-export data-base="{{ route('reports.ppic.print') }}" target="_blank" rel="noopener"
                   class="btn btn-outline-danger fw-semibold"><i class="bi bi-file-earmark-pdf me-1"></i> Export PDF</a>
            </div>
        </form>
    </div>

    {{-- TABEL MASTER PPIC --}}
    <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
        <div class="report-scroll">
            <table class="table table-bordered table-hover report-table mb-0 rpt-cards">
                @php
                    $prepKeys = array_keys(\App\Models\Project::$productionStages['preparation']['tasks']);
                    $prodPrep = array_filter($productionTasks, fn($t, $k) => in_array($k, $prepKeys, true), ARRAY_FILTER_USE_BOTH);
                    $prodMain = array_diff_key($productionTasks, $prodPrep);
                @endphp
                <thead>
                    <tr>
                        <th rowspan="3">No</th>
                        <th rowspan="3">Kode Proyek</th>
                        <th rowspan="3" class="report-name">Nama Proyek</th>
                        <th rowspan="3">Customer</th>
                        <th rowspan="3">Contract<br>Review</th>
                        <th rowspan="3">Due Date</th>
                        <th rowspan="3">Time Consumption<br>(Day)</th>
                        <th rowspan="3">Time Consumption<br>(Hour)</th>
                        <th rowspan="3">Qty</th>
                        <th rowspan="3">Fabricator</th>
                        <th colspan="{{ count($prepDivisions) + 1 }}" class="group-prep">Preparation ({{ array_sum(array_column($prepDivisions, 'weight')) }}%)</th>
                        <th colspan="{{ count($productionTasks) + 1 }}" class="group-prod">Production ({{ array_sum(array_column($productionTasks, 'weight')) }}%)</th>
                        <th colspan="{{ count($qcTasks) + 1 }}" class="group-qc">Finish Process ({{ array_sum(\App\Models\Project::$qcTasks) }}%)</th>
                        <th colspan="{{ count($qaqcTasks) + 1 }}" class="group-qaqc">QA/QC ({{ \App\Models\Project::QAQC_TOTAL }}%)</th>
                        <th rowspan="3">Planning (%)</th>
                        <th rowspan="3">Total<br>Percentage</th>
                        <th rowspan="3">Gap</th>
                        <th rowspan="3">Status</th>
                    </tr>
                    <tr>
                        @foreach($prepDivisions as $div)
                            <th rowspan="2" class="group-prep">{{ $div['label'] }}</th>
                        @endforeach
                        <th rowspan="2" class="group-total-prep">Total %</th>
                        <th colspan="{{ count($prodPrep) }}" class="group-prod-prep">Preparation ({{ array_sum(array_column($prodPrep, 'weight')) }}%)</th>
                        <th colspan="{{ count($prodMain) }}" class="group-prod-main">Production ({{ array_sum(array_column($prodMain, 'weight')) }}%)</th>
                        <th rowspan="2" class="group-total-prod">Total %</th>

                        @foreach($qcTasks as $task)
                            <th rowspan="2" class="group-qc" title="Bobot +{{ rtrim(rtrim(number_format($task['weight'], 1), '0'), '.') }}%">{{ $task['label'] }}</th>
                        @endforeach
                        <th rowspan="2" class="group-total-qc">Total %</th>

                        @foreach($qaqcTasks as $task)
                            <th rowspan="2" class="group-qaqc">{{ $task['label'] }}</th>
                        @endforeach
                        <th rowspan="2" class="group-total-qaqc">Total %</th>
                    </tr>
                    <tr>
                        @foreach($prodPrep as $task)
                            <th class="group-prod-prep" title="Bobot +{{ rtrim(rtrim(number_format($task['weight'], 1), '0'), '.') }}%">{{ $task['label'] }}</th>
                        @endforeach
                        @foreach($prodMain as $task)
                            <th class="group-prod-main" title="Bobot +{{ rtrim(rtrim(number_format($task['weight'], 1), '0'), '.') }}%">{{ $task['label'] }}</th>
                        @endforeach
</tr>
                </thead>
                <tbody>
                    @forelse($rows as $index => $row)
                        <tr>
                            <td class="text-center-num" data-k="no">{{ $index + 1 }}</td>
                            <td class="fw-semibold" data-k="code">{{ $row['code'] }}</td>
                            <td class="report-name" data-k="name">
                                <a href="{{ route('projects.edit', $row['id']) }}" class="text-decoration-none text-dark fw-semibold">{{ $row['name'] }}</a>
                            </td>
                            <td class="text-center-num" data-k="customer">{{ $row['customer'] }}</td>
                            <td class="text-center-num" data-k="extra">{{ $row['contract'] }}</td>
                            <td class="text-center-num" data-k="due" data-label="Due Date">{{ $row['due'] }}</td>
                            <td class="text-center-num" data-k="extra">{{ $row['time_days'] ?? '-' }}</td>
                            <td class="text-center-num" data-k="extra">{{ $row['time_hours'] !== null ? rtrim(rtrim(number_format($row['time_hours'], 2), '0'), '.') : '-' }}</td>
                            <td class="text-center-num" data-k="extra">{{ $row['qty'] }}</td>
                            <td class="text-center-num" data-k="extra">{{ $row['fabricator'] ?: '-' }}</td>

                            {{-- Preparation: PPIC, Engineering, Procurement --}}
                            @foreach($prepDivisions as $divKey => $div)
                                <td class="rpt-job text-center-num pct-cell {{ \App\Http\Controllers\ReportController::pctClass($row['prep_percents'][$divKey]) }}" data-label="{{ $div['label'] }}">{{ $row['prep_percents'][$divKey] }}%</td>
                            @endforeach
                            <td class="rpt-job text-center-num group-total-prep" data-label="Total Preparation">{{ $row['prep_group_pct'] }}%</td>

                            {{-- Production: Marking..Finishing --}}
                            @foreach($productionTasks as $taskKey => $task)
                                @if($row['production_cells'][$taskKey] === null)<td class="rpt-job text-center-num text-muted" data-label="{{ $task['label'] }}">-</td>@else <td class="rpt-job text-center-num pct-cell {{ \App\Http\Controllers\ReportController::pctClass($row['production_cells'][$taskKey]) }}" data-label="{{ $task['label'] }}">{{ $row['production_cells'][$taskKey] }}%</td>@endif
                            @endforeach
                            <td class="rpt-job text-center-num group-total-prod" data-label="Total Production">{{ $row['production_group_pct'] }}%</td>

                            {{-- Finishing: Packing..BAPP --}}
                            @foreach($qcTasks as $taskKey => $task)
                                @if($row['qc_cells'][$taskKey] === null)<td class="rpt-job text-center-num text-muted" data-label="{{ $task['label'] }}">-</td>@else <td class="rpt-job text-center-num pct-cell {{ \App\Http\Controllers\ReportController::pctClass($row['qc_cells'][$taskKey]) }}" data-label="{{ $task['label'] }}">{{ $row['qc_cells'][$taskKey] }}%</td>@endif
                            @endforeach
                            <td class="rpt-job text-center-num group-total-qc" data-label="Total Finish Process">{{ $row['qc_group_pct'] }}%</td>

                            {{-- QA/QC: MDR & NDT --}}
                            @foreach($qaqcTasks as $taskKey => $task)
                                @if($row['qaqc_cells'][$taskKey] === null)<td class="rpt-job text-center-num text-muted" data-label="{{ $task['label'] }}">-</td>@else <td class="rpt-job text-center-num pct-cell {{ \App\Http\Controllers\ReportController::pctClass($row['qaqc_cells'][$taskKey]) }}" data-label="{{ $task['label'] }}">{{ $row['qaqc_cells'][$taskKey] }}%</td>@endif
                            @endforeach
                            <td class="rpt-job text-center-num group-total-qaqc" data-label="Total Finish Process">{{ $row['qaqc_group_pct'] }}%</td>

                            <td class="text-center-num" data-k="plan" data-label="Plan">{{ $row['plan'] }}%</td>
                            <td class="text-center-num fw-bold" data-k="actual" data-label="Actual" style="color: var(--mei-green-brand);">{{ $row['actual'] }}%</td>
                            <td class="text-center-num fw-bold {{ $row['gap'] >= 0 ? 'text-success' : 'text-danger' }}" data-k="gap" data-label="Gap">{{ $row['gap'] >= 0 ? '+' : '' }}{{ $row['gap'] }}%</td>
                            <td class="text-center-num" data-k="status"><span class="badge rounded-pill px-3 py-2 {{ $row['status']['class'] }}">{{ $row['status']['label'] }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 13 + count($prepDivisions) + count($productionTasks) + count($qcTasks) + count($qaqcTasks) + 2 }}" class="text-center text-muted py-4">Tidak ada proyek pada periode yang dipilih.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var yearSelect = document.querySelector('select[name="year"]');
        var monthSelect = document.querySelector('select[name="month"]');
        if (!yearSelect || !monthSelect) { return; }

        yearSelect.addEventListener('change', function () {
            var isAll = yearSelect.value === 'all';
            monthSelect.disabled = isAll;
            if (isAll) { monthSelect.value = ''; }
        });

        // Export Excel / PDF memakai nilai filter yang sedang tampil di dropdown
        document.querySelectorAll('[data-report-export]').forEach(function (link) {
            link.addEventListener('click', function () {
                var params = new URLSearchParams();
                params.set('year', yearSelect.value);
                if (yearSelect.value !== 'all' && monthSelect.value) { params.set('month', monthSelect.value); }
                link.href = link.dataset.base + '?' + params.toString();
            });
        });
    });
</script>
@endsection