{{-- Kartu jobdesk satu divisi/tahap. Param: $division, $info (task_key => [name,desc]), $canEdit --}}
@php
    $gBase   = $project->divisionBase($division);
    $gActive = $project->activeTasks($division);
    $fmtW    = fn($w) => rtrim(rtrim(number_format($w, 2), '0'), '.');
@endphp
<div class="row g-3 div-task-grid" data-division-grid="{{ $division }}" data-total-weight="{{ $project->divisionTotalWeight($division) }}">
    @foreach($info as $taskKey => $taskInfo)
        @if(!empty($taskInfo['custom']))
            @include('projects.partials.task-card-custom', [
                'cId' => substr($taskKey, strlen('task_custom_')), 'cKey' => $taskKey, 'cName' => $taskInfo['name'], 'cDesc' => $taskInfo['desc'],
                'cEnabled' => array_key_exists($taskKey, $gActive), 'cWeight' => $gActive[$taskKey] ?? $gBase[$taskKey],
            ])
            @continue
        @endif
        @php
            $enabled = array_key_exists($taskKey, $gActive);
            $isItp   = $taskKey === 'task_inspection_plan';
            $itpStatus = $isItp ? $project->inspectionPlanStatus() : null;
            // Inspection Test Plan berstatus N/A tetap tampil (supaya bisa diubah lagi) selama jobdesknya tidak di-uncheck
            $visible = $isItp ? !in_array($taskKey, (array) (((array) ($project->tasks_disabled ?? []))[$division] ?? []), true) : $enabled;
            $pct     = (int) round($project->$taskKey);
            $dueA    = "due_date_{$taskKey}";
            $updA    = "last_update_{$taskKey}";
            $startA  = "start_date_{$taskKey}";
        @endphp
        <div class="col-md-4 {{ $visible ? '' : 'd-none' }}" data-div-task="{{ $taskKey }}" data-division="{{ $division }}" @if($isItp && $itpStatus === 'na') data-na="1" @endif>
            <label class="task-box d-block {{ !$canEdit ? 'disabled-box' : '' }}" for="{{ $taskKey }}">
                @if(!$canEdit)
                    @unless($isItp)<input type="hidden" name="progress_{{ $taskKey }}" value="{{ $pct }}">@endunless
                    <input type="hidden" name="{{ $dueA }}" value="{{ $project->$dueA ? $project->$dueA->format('Y-m-d') : '' }}">
                    <input type="hidden" name="{{ $updA }}" value="{{ $project->$updA ? $project->$updA->format('Y-m-d') : '' }}">
                @endif
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <div>
                        <div class="fw-bold">{{ $taskInfo['name'] }}</div>
                        <p class="small text-muted m-0">{{ $taskInfo['desc'] }}</p>
                    </div>
                    <span class="badge-weight-custom flex-shrink-0 div-weight-badge" data-base-weight="{{ $gBase[$taskKey] }}">+{{ $fmtW($gActive[$taskKey] ?? $gBase[$taskKey]) }}%</span>
                </div>
                @if($isItp)
                    {{-- Inspection Test Plan: status Open / Close / N/A (tanpa input persen) --}}
                    <input type="hidden" class="progress-input" id="{{ $taskKey }}" name="progress_{{ $taskKey }}" data-weight="{{ round($gActive[$taskKey] ?? 0, 4) }}" data-division="{{ $division }}" value="{{ $itpStatus === 'close' ? 100 : 0 }}">
                    <input type="hidden" name="status_task_inspection_plan" id="itpStatusInput" value="{{ $itpStatus }}">
                    <div class="dropdown mb-2 itp-status">
                        <button class="btn btn-sm w-100 dropdown-toggle fw-semibold itp-status-btn itp-{{ $itpStatus }}" type="button" id="itpStatusBtn" data-bs-toggle="dropdown" aria-expanded="false" {{ !$canEdit ? 'disabled' : '' }}>
                            Status: <span id="itpStatusLabel">{{ \App\Models\Project::$inspectionStatusOptions[$itpStatus] }}</span>
                        </button>
                        <ul class="dropdown-menu shadow border-0 w-100">
                            @foreach(\App\Models\Project::$inspectionStatusOptions as $stKey => $stLabel)
                                <li><button type="button" style="white-space: normal;" class="dropdown-item itp-status-opt {{ $itpStatus === $stKey ? 'active' : '' }}" data-status="{{ $stKey }}">
                                    <span class="d-block fw-semibold">{{ $stLabel }}</span><span class="d-block small opacity-75">{{ ['open' => '(belum selesai)', 'waiting' => '(menunggu persetujuan customer)', 'close' => '(selesai 100%)', 'na' => '(tidak perlu dikerjakan)'][$stKey] }}</span>
                                </button></li>
                            @endforeach
                        </ul>
                    </div>
                @else
                <div class="progress-input-group mb-2">
                    <input type="number" min="0" max="100" step="1" inputmode="numeric" class="form-control form-control-sm progress-input" name="progress_{{ $taskKey }}" id="{{ $taskKey }}" data-weight="{{ round($gActive[$taskKey] ?? 0, 4) }}" data-division="{{ $division }}" value="{{ $pct }}" {{ !$canEdit ? 'disabled' : '' }}>
                    <span class="progress-input-suffix">%</span>
                </div>
                @endif
                @include('projects.partials.task-late', ['taskKey' => $taskKey])
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label-custom">Start Date</label>
                        <input type="date" name="{{ $startA }}" class="form-control-custom form-control-sm" value="{{ $project->$startA ? $project->$startA->format('Y-m-d') : '' }}" {{ !$canEdit ? 'disabled' : '' }}>
                    </div>
                    <div class="col-6">
                        <label class="form-label-custom">Due Date</label>
                        <input type="date" name="{{ $dueA }}" class="form-control-custom form-control-sm" value="{{ $project->$dueA ? $project->$dueA->format('Y-m-d') : '' }}" {{ !$canEdit ? 'disabled' : '' }}>
                    </div>
                    <div class="col-6">
                        <label class="form-label-custom">Tanggal Update</label>
                        <input type="date" name="{{ $updA }}" class="form-control-custom form-control-sm" value="{{ $project->$updA ? $project->$updA->format('Y-m-d') : '' }}" {{ !$canEdit ? 'disabled' : '' }}>
                    </div>
                </div>
            </label>
        </div>
    @endforeach
</div>