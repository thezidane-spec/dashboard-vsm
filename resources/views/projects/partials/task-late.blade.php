{{-- Keterangan tenggat PER JOBDESK (badge kecil). Param: $taskKey --}}
@php $tl = $project->getTaskDeadlineInfo($taskKey); @endphp
@if(true)
    <span class="task-late-badge task-late-{{ $tl['class'] }}" title="{{ $tl['label'] }}{{ !empty($tl['inherited']) ? ' (mengikuti tenggat divisi)' : '' }}" data-task-late="{{ $taskKey }}">
        <i class="bi {{ in_array($tl['status'], ['late','done_late']) ? 'bi-exclamation-triangle-fill' : ($tl['status'] === 'done' ? 'bi-check-circle-fill' : 'bi-clock-history') }}"></i>
        {{ $tl['short'] }}
    </span>
@endif