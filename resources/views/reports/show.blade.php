@extends('layouts.app')

@section('title', 'Report ' . $meta['label'] . ' - PT Mudalaya Energy Indonesia')
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

    .report-table { font-size: 0.8rem; }
    .report-table thead th { background: #f1f5f9; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.3px; white-space: nowrap; text-align: center; vertical-align: middle; }
    .report-table td, .report-table th { vertical-align: middle; }
    .report-table td.text-center-num { text-align: center; }
    .report-table td.report-name { min-width: 170px; white-space: normal; }
    .report-table thead th.group-head { background: #dcfce7; color: #14532d; }

    .task-pill { display: inline-block; min-width: 40px; padding: 2px 6px; border-radius: 6px; font-weight: 700; font-size: 0.72rem; }
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
        <form method="GET" action="{{ route('reports.show', $reportKey) }}" class="row g-2 align-items-center">
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
                <button type="button" class="btn btn-outline-secondary" disabled title="Segera hadir"><i class="bi bi-file-earmark-excel me-1"></i> Export Excel</button>
                <button type="button" class="btn btn-outline-secondary" disabled title="Segera hadir"><i class="bi bi-file-earmark-pdf me-1"></i> Export PDF</button>
            </div>
        </form>
    </div>

    {{-- TABEL DETAIL JOBDESK --}}
    <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
        <div class="report-scroll">
            <table class="table table-bordered table-hover report-table mb-0 rpt-cards">
                <thead>
                    <tr>
                        <th rowspan="2">No</th>
                        <th rowspan="2">Kode Proyek</th>
                        <th rowspan="2" class="report-name">Nama Proyek</th>
                        <th rowspan="2">Customer</th>
                        <th rowspan="2">Qty</th>
                        <th rowspan="2">Due Date</th>
                        <th colspan="{{ count($tasks) }}" class="group-head">{{ $meta['label'] }} (Bobot {{ rtrim(rtrim(number_format($totalWeight, 1), '0'), '.') }}%)</th>
                        <th rowspan="2">Planning (%)</th>
                        <th rowspan="2">Total Percentage</th>
                        <th rowspan="2">Gap</th>
                        <th rowspan="2">Status</th>
                    </tr>
                    <tr>
                        @foreach($tasks as $task)
                            <th class="group-head" title="Bobot +{{ rtrim(rtrim(number_format($task['weight'], 1), '0'), '.') }}%{{ $reportKey === 'engineering' ? ' (bobot dasar; naik otomatis bila jobdesk lain dimatikan)' : '' }}">{{ $task['label'] }}</th>
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
                            <td data-k="customer">{{ $row['customer'] }}</td>
                            <td class="text-center-num" data-k="extra">{{ $row['qty'] }}</td>
                            <td class="text-center-num" data-k="due" data-label="Due Date">{{ $row['due'] }}</td>
                            @foreach($tasks as $taskKey => $task)
                                @if($row['tasks'][$taskKey] === null)
                                    <td class="text-center-num rpt-job" data-label="{{ $task['label'] }}"><span class="task-pill task-pill-pending" title="Jobdesk ini tidak dipakai di proyek ini">-</span></td>
                                @else
                                    <td class="text-center-num pct-cell rpt-job {{ \App\Http\Controllers\ReportController::pctClass($row['tasks'][$taskKey]) }}" data-label="{{ $task['label'] }}">{{ $row['tasks'][$taskKey] }}%</td>
                                @endif
                            @endforeach
                            <td class="text-center-num" data-k="plan" data-label="Plan">{{ $row['plan'] }}%</td>
                            <td class="text-center-num fw-bold" data-k="actual" data-label="Actual" style="color: var(--mei-green-brand);">{{ $row['actual'] }}%</td>
                            <td class="text-center-num fw-bold {{ $row['gap'] >= 0 ? 'text-success' : 'text-danger' }}" data-k="gap" data-label="Gap">{{ $row['gap'] >= 0 ? '+' : '' }}{{ $row['gap'] }}%</td>
                            <td class="text-center-num" data-k="status"><span class="badge rounded-pill px-3 py-2 {{ $row['status']['class'] }}">{{ $row['status']['label'] }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 10 + count($tasks) }}" class="text-center text-muted py-4">Tidak ada proyek pada periode yang dipilih.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PROBLEM DIVISI (dari Remarks Report): Problem, Root Cause, Action, Due Date, PIC, Evidence --}}
    @php
        $fmtIssueDate = function ($d) { try { return $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '-'; } catch (\Throwable $e) { return '-'; } };
    @endphp
    <div class="card border-0 shadow-sm rounded-3 p-3 bg-white mt-3">
        <div class="fw-bold mb-2 text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> PROBLEM DIVISI {{ strtoupper($meta['label']) }}</div>
        <div class="report-scroll">
            <table class="table table-bordered table-hover report-table mb-0 table-stack">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Proyek</th>
                        <th class="report-name">Nama Proyek</th>
                        <th>Problem</th>
                        <th>Root Cause</th>
                        <th>Action</th>
                        <th>Due Date</th>
                        <th>PIC</th>
                        <th>Evidence</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($issueRows as $i => $issue)
                        <tr>
                            <td class="text-center-num stack-hide">{{ $i + 1 }}</td>
                            <td class="fw-semibold" data-label="Kode Proyek">{{ $issue['code'] }}</td>
                            <td class="report-name"><a href="{{ route('projects.edit', $issue['project_id']) }}" class="text-decoration-none text-dark fw-semibold">{{ $issue['name'] }}</a></td>
                            <td style="min-width:200px;" data-label="Problem">{{ $issue['problem'] !== '' ? $issue['problem'] : '-' }} @if($issue['problem'] !== '')<span class="badge bg-warning-subtle text-warning-emphasis">{{ $fmtIssueDate($issue['date']) }}</span>@endif @if(!empty($issue['jobdesk_label']))<span class="badge bg-primary-subtle text-primary-emphasis">{{ $issue['jobdesk_label'] }}</span>@endif</td>
                            <td style="min-width:160px;" data-label="Root Cause">{{ $issue['root_cause'] !== '' ? $issue['root_cause'] : '-' }}</td>
                            <td style="min-width:160px;" data-label="Action">{{ $issue['action'] !== '' ? $issue['action'] : '-' }}</td>
                            <td class="text-center-num" data-label="Due Date">{{ $fmtIssueDate($issue['due_date']) }}</td>
                            <td data-label="PIC">{{ $issue['pic'] !== '' ? $issue['pic'] : '-' }}</td>
                            <td class="text-center-num" data-label="Evidence">
                                @if($issue['evidence'])
                                    <a href="{{ route('projects.evidence', ['project' => $issue['project_id'], 'path' => $issue['evidence']['path']]) }}" target="_blank"><i class="bi bi-paperclip"></i> {{ \Illuminate\Support\Str::limit($issue['evidence']['name'] ?? 'Evidence', 18) }}</a>
                                @endif
                                @if(($issue['evidence_text'] ?? '') !== '')
                                    <div class="small text-start" style="white-space: pre-line;">{{ $issue['evidence_text'] }}</div>
                                @endif
                                @if(!$issue['evidence'] && ($issue['evidence_text'] ?? '') === '') - @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-3">Belum ada problem tercatat untuk divisi ini.</td></tr>
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
    });
</script>
@endsection