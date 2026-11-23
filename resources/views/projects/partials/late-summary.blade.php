{{-- Ringkasan jobdesk terlambat di bawah header divisi/tahap (dilipat supaya rapi). Param: $division, $info --}}
@php $lateList = $project->lateTasksOf($division, $info); @endphp
@if(count($lateList) > 0)
    <details class="late-summary mb-3">
        <summary><i class="bi bi-exclamation-triangle-fill me-1"></i> {{ count($lateList) }} jobdesk terlambat</summary>
        <ul class="late-summary-list">
            @foreach($lateList as $lt)
                <li><span class="fw-semibold">{{ $lt['name'] }}</span> <span class="task-late-badge task-late-danger">{{ $lt['info']['label'] }}</span></li>
            @endforeach
        </ul>
    </details>
@endif