{{-- Kartu jobdesk CUSTOM QA/QC (nama & deskripsi diinput manual). Param: $cId, $cKey, $cName, $cDesc, $cEnabled, $cWeight, $canEdit, $project; $cTemplate = true untuk template JS --}}
@php
    $cTemplate = $cTemplate ?? false;
    $cPct   = $cTemplate ? 0 : (int) $project->$cKey;
    $cStart = $cTemplate ? null : $project->{'start_date_' . $cKey};
    $cDue   = $cTemplate ? null : $project->{'due_date_' . $cKey};
    $cUpd   = $cTemplate ? null : $project->{'last_update_' . $cKey};
    $cFmtW  = rtrim(rtrim(number_format((float) $cWeight, 2), '0'), '.');
    $cName_ = "custom_tasks[{$cId}]";
@endphp
<div class="col-md-4 {{ $cEnabled ? '' : 'd-none' }}" data-div-task="{{ $cKey }}" data-division="qaqc" data-custom-task="{{ $cId }}">
    <div class="task-box d-block {{ !$canEdit ? 'disabled-box' : '' }}">
        <input type="hidden" name="{{ $cName_ }}[name]" value="{{ $cName }}" data-cf="name">
        <input type="hidden" name="{{ $cName_ }}[desc]" value="{{ $cDesc }}" data-cf="desc">
        <input type="hidden" name="{{ $cName_ }}[delete]" value="" data-cf="delete" disabled>
        @if(!$canEdit)
            <input type="hidden" name="{{ $cName_ }}[progress]" value="{{ $cPct }}">
        @endif
        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
            <div>
                <div class="fw-bold"><span data-cf-text="name">{{ $cName }}</span> <span class="badge bg-info-subtle text-info border border-info-subtle align-middle" style="font-size:.62rem;">CUSTOM</span></div>
                <p class="small text-muted m-0" data-cf-text="desc">{{ $cDesc }}</p>
            </div>
            <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
                <span class="badge-weight-custom div-weight-badge" data-base-weight="{{ \App\Models\Project::CUSTOM_BASE_WEIGHT }}">+{{ $cFmtW }}%</span>
                @if($canEdit)
                    <button type="button" class="btn btn-sm btn-link text-danger p-0 custom-task-remove" title="Hapus jobdesk ini"><i class="bi bi-trash3"></i></button>
                @endif
            </div>
        </div>
        <div class="progress-input-group mb-2">
            <input type="number" min="0" max="100" step="1" inputmode="numeric" class="form-control form-control-sm progress-input" name="{{ $cName_ }}[progress]" id="{{ $cKey }}" data-weight="{{ round((float) $cWeight, 4) }}" data-division="qaqc" value="{{ $cPct }}" {{ !$canEdit ? 'disabled' : '' }}>
            <span class="progress-input-suffix">%</span>
        </div>
        @unless($cTemplate)
            @include('projects.partials.task-late', ['taskKey' => $cKey])
        @endunless
        <div class="row g-2">
            <div class="col-6">
                <label class="form-label-custom">Start Date</label>
                <input type="date" name="{{ $cName_ }}[start]" class="form-control-custom form-control-sm" value="{{ $cStart ? $cStart->format('Y-m-d') : '' }}" {{ !$canEdit ? 'disabled' : '' }}>
            </div>
            <div class="col-6">
                <label class="form-label-custom">Due Date</label>
                <input type="date" name="{{ $cName_ }}[due]" class="form-control-custom form-control-sm" value="{{ $cDue ? $cDue->format('Y-m-d') : '' }}" {{ !$canEdit ? 'disabled' : '' }}>
            </div>
            <div class="col-6">
                <label class="form-label-custom">Tanggal Update</label>
                <input type="date" name="{{ $cName_ }}[update]" class="form-control-custom form-control-sm" value="{{ $cUpd ? $cUpd->format('Y-m-d') : '' }}" {{ !$canEdit ? 'disabled' : '' }}>
            </div>
        </div>
    </div>
</div>