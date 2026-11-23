{{--
    Weekly Report (Foto Progress) -- kartu di bawah S-Curve.
    Variabel: $project, $canWeekly (admin / PPIC boleh membuat, mengubah, menghapus)
--}}
@php
    $weeklyReports = $project->weeklyReports()->with('photos')->get();
@endphp
<div class="custom-card p-4 mb-4 bg-white rounded-3 border" id="weekly-section">
    <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="d-flex align-items-center gap-2">
            <i class="bi bi-camera text-success fs-5"></i>
            <span>WEEKLY REPORT (FOTO PROGRESS)</span>
        </span>
        @if($canWeekly)
            <button type="button" class="btn btn-sm btn-success fw-bold px-3 no-print" id="btnNewWeekly">
                <i class="bi bi-plus-circle me-1"></i> Buat Weekly Report
            </button>
        @endif
    </div>

    @if(session('weekly_status'))
        <div class="alert alert-success py-2 small no-print">{{ session('weekly_status') }}</div>
    @endif
    @if(session('weekly_error'))
        <div class="alert alert-danger py-2 small no-print">{{ session('weekly_error') }}</div>
    @endif

    @if($weeklyReports->isEmpty())
        <p class="text-muted small mb-0">Belum ada Weekly Report. Klik <strong>Buat Weekly Report</strong> untuk mengisi identitas, waktu, dan foto progress (judul dan jumlah tahap bisa diatur sendiri), lalu ekspor ke Excel.</p>
    @else
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0 table-stack">
                <thead class="table-light">
                    <tr>
                        <th>Periode</th>
                        <th>Customer</th>
                        <th>Foto per tahap</th>
                        <th>Dibuat</th>
                        <th class="text-end no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($weeklyReports as $wr)
                    <tr>
                        <td class="fw-semibold" data-label="Periode">{{ trim($wr->period) !== '' ? trim($wr->period) : '(tanpa periode)' }}</td>
                        <td data-label="Customer">{{ $wr->customer_name ?: '-' }}</td>
                        <td data-label="Foto per tahap">
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($wr->sectionList() as $sec)
                                    @php $n = $wr->photos->where('section', $sec['key'])->count(); @endphp
                                    <span class="badge fw-normal {{ $n ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-light text-muted border' }}">{{ $sec['title'] }}: {{ $n }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="small text-muted" data-label="Dibuat">{{ $wr->created_at?->format('d M Y') }}</td>
                        <td class="text-end text-nowrap no-print stack-actions">
                            <a href="{{ route('projects.weekly.export', [$project->id, $wr->id]) }}" class="btn btn-sm btn-outline-success fw-semibold"><i class="bi bi-file-earmark-excel me-1"></i> Excel</a>
                            @if($canWeekly)
                                <button type="button" class="btn btn-sm btn-outline-primary fw-semibold weekly-edit" data-weekly="{{ $wr->id }}"><i class="bi bi-pencil-square me-1"></i> Edit</button>
                                <button type="button" class="btn btn-sm btn-outline-danger weekly-delete" data-weekly="{{ $wr->id }}" data-label="{{ trim($wr->period) ?: ('#' . $wr->id) }}" title="Hapus"><i class="bi bi-trash3"></i></button>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>