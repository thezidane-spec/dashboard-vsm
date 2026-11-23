@extends('layouts.app')

@section('title', 'Summary Report - PT Mudalaya Energy Indonesia')
@section('page_title', 'Summary Report')

@push('styles')
<style>
    .report-card {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px;
        height: 100%;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .report-card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 800;
        font-size: 1.05rem;
        color: #0f172a;
    }
    .report-card-title i {
        color: var(--mei-green-brand);
        font-size: 1.2rem;
    }
    .report-card-desc {
        color: #64748b;
        font-size: 0.82rem;
        margin: 0;
        flex-grow: 1;
    }
    .report-card-soon {
        display: inline-block;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #92400e;
        background: #fef3c7;
        border: 1px solid #fde68a;
        border-radius: 999px;
        padding: 2px 10px;
    }
    .report-card .btn-report {
        background-color: var(--mei-green-brand);
        color: #fff;
        font-weight: 700;
        border: 0;
    }
    .report-card .btn-report:hover {
        background-color: var(--mei-green-dark);
        color: #fff;
    }
</style>
@endpush

@section('content')
<div class="container-fluid p-0">

    <p class="text-muted mb-4" style="max-width: 720px;">
        Rekap progres semua proyek per divisi/tahap. Pilih salah satu kartu di bawah untuk melihat tabel lengkapnya,
        lengkap dengan filter tahun dan periode bulan.
    </p>

    <div class="row g-3">

        {{-- 1. PPIC --}}
        <div class="col-md-6">
            <div class="report-card">
                <div class="report-card-title"><i class="bi bi-calendar3"></i> PPIC</div>
                <p class="report-card-desc">Rekap due date, planning, dan actual progress Divisi PPIC di semua proyek.</p>
                <a href="{{ route('reports.show', 'ppic') }}" class="btn btn-report">Lihat Report</a>
            </div>
        </div>

        {{-- 2. REMARKS REPORT --}}
        <div class="col-md-6">
            <div class="report-card">
                <div class="report-card-title"><i class="bi bi-chat-square-text"></i> Remarks Report</div>
                <p class="report-card-desc">Rekap Remarks Problem, Task revisi, dan PIC dari semua proyek, lengkap dengan deviasi progress.</p>
                <a href="{{ route('reports.show', 'remarks') }}" class="btn btn-report">Lihat Report</a>
            </div>
        </div>

        {{-- 3. MATERIAL (per proyek, pakai fitur Material yang sudah ada) --}}
        <div class="col-md-6">
            <div class="report-card">
                <div class="report-card-title"><i class="bi bi-box-seam"></i> Material</div>
                <p class="report-card-desc">Tabel Raw Material & Vendor Raw Material per proyek (data yang sama seperti di halaman Procurement).</p>
                <div class="d-flex gap-2">
                    <select id="materialProjectSelect" class="form-select">
                        @forelse($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->project_code }} - {{ $project->project_name }}</option>
                        @empty
                            <option value="">Belum ada proyek</option>
                        @endforelse
                    </select>
                    <button type="button" class="btn btn-report text-nowrap" onclick="openMaterialReport()" {{ $projects->isEmpty() ? 'disabled' : '' }}>Lihat Report</button>
                </div>
            </div>
        </div>

        {{-- 4. S-CURVE (per proyek) --}}
        <div class="col-md-6">
            <div class="report-card">
                <div class="report-card-title"><i class="bi bi-graph-up-arrow"></i> S-Curve</div>
                <p class="report-card-desc">Kurva-S rencana (dari Due Date dan Planning % tiap divisi) vs actual progress per proyek.</p>
                <form method="GET" action="{{ route('reports.show', 'scurve') }}" class="d-flex gap-2">
                    <select name="project" class="form-select">
                        @forelse($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->project_code }} - {{ $project->project_name }}</option>
                        @empty
                            <option value="">Belum ada proyek</option>
                        @endforelse
                    </select>
                    <button type="submit" class="btn btn-report text-nowrap" {{ $projects->isEmpty() ? 'disabled' : '' }}>Lihat Report</button>
                </form>
            </div>
        </div>

        {{-- 5. ENGINEERING --}}
        <div class="col-md-6">
            <div class="report-card">
                <div class="report-card-title"><i class="bi bi-gear-wide-connected"></i> Engineering</div>
                <p class="report-card-desc">Rekap due date, planning, dan actual progress Divisi Engineering di semua proyek.</p>
                <a href="{{ route('reports.show', 'engineering') }}" class="btn btn-report">Lihat Report</a>
            </div>
        </div>

        {{-- 6. PRODUCTION (dropdown tahap: Preparation / Production / Finishing / QA/QC) --}}
        <div class="col-md-6">
            <div class="report-card">
                <div class="report-card-title"><i class="bi bi-tools"></i> Production</div>
                <p class="report-card-desc">Pilih tahap yang ingin dilihat: Preparation, Production, Finishing, atau QA/QC.</p>
                <div class="d-flex gap-2">
                    <select id="productionStageSelect" class="form-select">
                        @foreach($productionStageOptions as $stageKey => $stageLabel)
                            <option value="{{ $stageKey }}">{{ $stageLabel }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-report text-nowrap" onclick="openProductionReport()">Lihat Report</button>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    function openProductionReport() {
        var key = document.getElementById('productionStageSelect').value;
        window.location.href = '{{ url("/reports") }}/' + key;
    }

    function openMaterialReport() {
        var projectId = document.getElementById('materialProjectSelect').value;
        if (!projectId) { return; }
        window.open('{{ url("/projects") }}/' + projectId + '/materials/print', '_blank');
    }
</script>
@endsection