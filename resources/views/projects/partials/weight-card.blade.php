@php
    $formattedWeight = $value === null
        ? null
        : rtrim(rtrim(number_format((float) $value, 3, ',', '.'), '0'), ',');
@endphp
<div class="custom-card p-3 mt-3" id="{{ $anchor }}">
    <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <div>
            <div class="fw-bold"><i class="bi bi-speedometer2 text-success me-1"></i>{{ $title }}</div>
            <div class="text-muted small mt-1">
                {{ $formattedWeight !== null ? $formattedWeight . ' kg' : 'Belum diinput' }}
            </div>
        </div>
        @if($canEdit)
            <button type="button" class="btn btn-sm btn-outline-success fw-semibold no-print"
                    data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                <i class="bi bi-pencil-square me-1"></i>{{ $value !== null ? 'Ubah Tonase' : 'Input Tonase' }}
            </button>
        @endif
    </div>
</div>
