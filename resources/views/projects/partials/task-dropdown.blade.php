{{-- Dropdown "Jobdesk" per divisi/tahap. Param: $division (ppic|preparation|main_production|finishing|qaqc), $info (task_key => [name,desc]), $canEdit --}}
@php
    $ddBase   = $project->divisionBase($division);
    $ddActive = $project->activeTasks($division);
    $ddTotal  = $project->divisionTotalWeight($division);
    $ddTotalFmt = rtrim(rtrim(number_format($ddTotal, 2), '0'), '.');
@endphp
<div class="dropdown no-print">
    <button class="btn btn-sm btn-outline-success dropdown-toggle fw-semibold" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
        <i class="bi bi-list-check me-1"></i> Jobdesk (<span data-dd-count="{{ $division }}">{{ count($ddActive) }}</span>/<span data-dd-total="{{ $division }}">{{ count($ddBase) }}</span>)
    </button>
    <ul class="dropdown-menu shadow border-0 p-2" style="min-width: 290px;" data-dd-menu="{{ $division }}">
        <li class="px-2 pb-2 small text-muted">Centang jobdesk yang diperlukan proyek ini. Bobot jobdesk yang tersisa dibagi ulang otomatis (total tetap {{ $ddTotalFmt }}%).</li>
        @foreach($info as $ddKey => $ddInfo)
            <li data-dd-item="{{ $ddKey }}">
                <label class="dropdown-item d-flex align-items-center justify-content-between gap-3 rounded-2" style="{{ $canEdit ? 'cursor: pointer;' : 'cursor: not-allowed; opacity: 0.7;' }}">
                    <span>{{ $ddInfo['name'] }}</span>
                    <input type="checkbox" class="form-check-input div-task-toggle m-0" data-division="{{ $division }}" name="tasks_enabled[{{ $division }}][]" value="{{ $ddKey }}" {{ array_key_exists($ddKey, $ddActive) ? 'checked' : '' }} {{ !$canEdit ? 'disabled' : '' }}>
                </label>
            </li>
        @endforeach
    </ul>
    @if($canEdit)
        <input type="hidden" name="tasks_submitted[{{ $division }}]" value="1">
    @endif
</div>