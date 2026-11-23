@extends('layouts.app')

@section('title', 'S-Curve - PT Mudalaya Energy Indonesia')
@section('page_title', 'Summary Report')

@section('content')
<div class="container-fluid p-0">

    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary no-print"><i class="bi bi-arrow-left"></i> Kembali</a>
        <h5 class="fw-bold m-0"><i class="bi {{ $meta['icon'] }} me-1 text-success"></i> {{ $meta['label'] }}</h5>
    </div>
    <p class="text-muted small mb-3" style="max-width: 860px;">
        Kurva-S proyek: garis <strong>rencana</strong> disusun dari Due Date dan Planning (%) tiap divisi, garis <strong>aktual</strong> dari riwayat progress harian.
        Garis aktual bisa selesai sebelum due date atau terus berjalan melewatinya kalau terlambat.
        Filter Tahun dan Bulan (berdasarkan due date proyek) memengaruhi tabel ringkasan di bawah grafik.
    </p>

    {{-- FILTER + PILIH PROYEK --}}
    <div class="card border-0 shadow-sm rounded-3 mb-3 p-3 bg-white no-print">
        <form method="GET" action="{{ route('reports.show', 'scurve') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label-custom small text-muted mb-1 d-block">Proyek (grafik)</label>
                <select name="project" class="form-select bg-light">
                    @foreach($projectOptions as $option)
                        <option value="{{ $option->id }}" {{ $selected && $selected->id === $option->id ? 'selected' : '' }}>{{ $option->project_code }} - {{ $option->project_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label-custom small text-muted mb-1 d-block">Tahun (tabel)</label>
                <select name="year" class="form-select bg-light">
                    <option value="all" {{ $selectedYear === 'all' ? 'selected' : '' }}>-- Semua Tahun --</option>
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ (string) $selectedYear === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label-custom small text-muted mb-1 d-block">Periode Bulan (tabel)</label>
                <select name="month" class="form-select bg-light" {{ $selectedYear === 'all' ? 'disabled' : '' }}>
                    <option value="">-- Semua Bulan --</option>
                    @foreach($monthNames as $num => $label)
                        <option value="{{ $num }}" {{ (string) $selectedMonth === (string) $num ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success w-100 fw-bold" style="background-color: var(--mei-green-brand);">
                    <i class="bi bi-funnel-fill me-1"></i> Tampilkan
                </button>
            </div>
        </form>
    </div>

    {{-- GRAFIK PROYEK TERPILIH --}}
    @if($selected && $sCurve)
        <div class="card border-0 shadow-sm rounded-3 p-3 mb-3 bg-white">
            <h6 class="fw-bold mb-3">
                <a href="{{ route('projects.edit', $selected->id) }}" class="text-success text-decoration-none">{{ $selected->project_code }}</a>
                - {{ $selected->project_name }}
            </h6>
            @include('projects.partials.scurve-card', ['sc' => $sCurve, 'canvasId' => 'scurveChartReport'])
        </div>
    @else
        <div class="alert alert-info">Belum ada proyek untuk ditampilkan.</div>
    @endif

    {{-- RINGKASAN SEMUA PROYEK (sesuai filter) --}}
    <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
        <h6 class="fw-bold mb-3">Ringkasan Rencana vs Aktual Hari Ini</h6>
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0 rpt-cards" style="font-size: 0.85rem;">
                <thead class="table-light">
                    <tr class="text-center text-uppercase" style="font-size: 0.72rem;">
                        <th>No</th>
                        <th>Kode Proyek</th>
                        <th class="text-start">Nama Proyek</th>
                        <th>Due Date</th>
                        <th>Rencana</th>
                        <th>Aktual</th>
                        <th>Deviasi</th>
                        <th class="text-start">Status</th>
                        <th class="no-print">Grafik</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($overview as $i => $row)
                        <tr>
                            <td class="text-center" data-k="no">{{ $i + 1 }}</td>
                            <td class="text-center fw-bold" data-k="code">{{ $row['code'] }}</td>
                            <td data-k="name">{{ $row['name'] }}</td>
                            <td class="text-center" data-k="due" data-label="Due Date">{{ $row['due'] }}</td>
                            <td class="text-center" data-k="plan" data-label="Rencana">{{ $row['plan_today'] }}%</td>
                            <td class="text-center fw-bold" data-k="actual" data-label="Aktual">{{ $row['actual_today'] }}%</td>
                            <td class="text-center fw-bold {{ $row['deviation'] >= 0 ? 'text-success' : 'text-danger' }}" data-k="gap" data-label="Deviasi">{{ $row['deviation'] >= 0 ? '+' : '' }}{{ $row['deviation'] }}%</td>
                            <td data-k="status"><span class="badge bg-{{ $row['status_type'] }}-subtle text-{{ $row['status_type'] }} border border-{{ $row['status_type'] }}">{{ $row['status_text'] }}</span></td>
                            <td class="text-center no-print" data-k="aksi">
                                <a href="{{ route('reports.show', 'scurve') }}?{{ http_build_query(array_filter(['project' => $row['id'], 'year' => $selectedYear, 'month' => $selectedMonth])) }}" class="btn btn-sm btn-outline-success">Lihat</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">Tidak ada proyek pada periode yang dipilih.</td></tr>
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