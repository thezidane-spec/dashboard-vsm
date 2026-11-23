@extends('layouts.app')

@section('title', 'Remarks Report - PT Mudalaya Energy Indonesia')
@section('page_title', 'Summary Report')

@push('styles')
<style>
    .report-kpi { border: 0; border-radius: 12px; padding: 14px; text-align: center; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
    .report-kpi small { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }
    .report-kpi h3 { margin: 2px 0 0; font-weight: 800; }
</style>
@endpush

@section('content')
<div class="container-fluid p-0">

    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary no-print"><i class="bi bi-arrow-left"></i> Kembali</a>
        <h5 class="fw-bold m-0"><i class="bi {{ $meta['icon'] }} me-1 text-success"></i> {{ $meta['label'] }}</h5>
    </div>
    <p class="text-muted small mb-3" style="max-width: 820px;">
        Rekap Remarks Report semua proyek: masalah (Remarks Problem) beserta tanggalnya, Task revisi beserta due date-nya, dan PIC beserta departemennya.
        Diisi lewat tombol <strong>Update Remarks</strong> di halaman edit masing-masing proyek.
        Deviation = Actual Progress - Planning Progress.
    </p>

    {{-- RINGKASAN --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="report-kpi border-start border-4 border-secondary">
                <small class="text-secondary">Total Proyek</small>
                <h3>{{ $summary['total'] }}</h3>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="report-kpi border-start border-4 border-success">
                <small class="text-success">Proyek Ber-Remarks</small>
                <h3>{{ $summary['withData'] }}</h3>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="report-kpi border-start border-4 border-warning">
                <small class="text-warning">Remarks Problem</small>
                <h3>{{ $summary['problems'] }}</h3>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="report-kpi border-start border-4 border-info">
                <small class="text-info">Task Revisi</small>
                <h3>{{ $summary['tasks'] }}</h3>
            </div>
        </div>
    </div>

    {{-- FILTER TAHUN & PERIODE BULAN (berdasarkan Due Date proyek) --}}
    <div class="card border-0 shadow-sm rounded-3 mb-3 p-3 bg-white no-print">
        <form method="GET" action="{{ route('reports.show', 'remarks') }}" class="row g-2 align-items-center">
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
        </form>
    </div>

    <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
        @include('reports.partials.remarks-table', [
            'remarkRows'   => $rows,
            'linkProjects' => true,
            'emptyText'    => 'Tidak ada proyek pada periode yang dipilih.',
        ])
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