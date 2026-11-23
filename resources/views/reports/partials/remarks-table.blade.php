{{--
    Tabel Remarks Report (dipakai di halaman edit proyek dan Summary Report - Remarks Report).
    Variabel:
      $remarkRows   : array/collection hasil Project::remarksRow()
      $linkProjects : (opsional) true -> kode proyek jadi link ke halaman edit
      $emptyText    : (opsional) teks kalau belum ada proyek
      $remarkProjectId : (opsional) id proyek -> tombol edit/hapus per item ikut tampil (halaman edit proyek)

    Setiap proyek = beberapa baris sub-tabel (satu per entri remarks: Problem, Root Cause, Action + Due Date, PIC, Evidence);
    kolom info proyek di-rowspan. Entri disejajarkan per nomor lewat Project::remarkEntries().
--}}
@php
    $fmtRemarkDate = function ($date) {
        if (!$date) { return '-'; }
        try { return \Carbon\Carbon::parse($date)->format('d/m/Y'); } catch (\Throwable $e) { return '-'; }
    };
    $remarkDeptLabels = \App\Models\Project::$remarkDepartments;
    $linkProjects = $linkProjects ?? false;
    $emptyText    = $emptyText ?? 'Belum ada proyek.';
    $remarkProjectId = $remarkProjectId ?? null;
@endphp

<style>
    .remarks-table { font-size: 0.82rem; }
    .remarks-table thead th { background: #f1f5f9; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.3px; text-align: center; vertical-align: middle; white-space: nowrap; }
    .remarks-table td { vertical-align: middle; }
    .remarks-table td.rm-center { text-align: center; }
    .remarks-table td.rm-text { min-width: 220px; white-space: normal; }
    .remarks-table .rm-num { font-weight: 700; color: #475569; margin-right: 4px; }
    .remarks-table .rm-date { display: inline-block; margin-left: 6px; padding: 1px 8px; border-radius: 999px; background: #fef3c7; color: #92400e; font-weight: 700; font-size: 0.7rem; white-space: nowrap; }
    .remarks-table .rm-plan { display: inline-block; margin-left: 4px; padding: 1px 8px; border-radius: 999px; background: #dbeafe; color: #1e3a8a; font-weight: 700; font-size: 0.7rem; white-space: nowrap; }
    .remarks-table .rm-dept { display: inline-block; margin-left: 6px; padding: 1px 8px; border-radius: 999px; background: #dcfce7; color: #14532d; font-weight: 700; font-size: 0.7rem; }
    .remarks-table .rm-actions { white-space: nowrap; margin-left: 6px; }
    .remarks-table .rm-btn { border: 0; background: transparent; color: #64748b; padding: 0 3px; font-size: 0.85rem; line-height: 1; }
    .remarks-table .rm-btn:hover { color: #16a34a; }
    .remarks-table .rm-btn-del:hover { color: #dc2626; }
    .remarks-table .rm-gap-pos { color: #15803d; font-weight: 800; }
    .remarks-table .rm-gap-neg { color: #b91c1c; font-weight: 800; }
</style>

<div class="table-responsive">
    <table class="table table-bordered align-middle mb-0 remarks-table">
        <thead>
            <tr>
                <th>NO</th>
                <th>NO Project</th>
                <th>Project</th>
                <th>Customer</th>
                <th>Due Date</th>
                <th>Deviation</th>
                <th>Planning Progress</th>
                <th>Actual Progress</th>
                <th>Remarks Problem</th>
                <th>Root Cause</th>
                <th>Action</th>
                <th>Due Date</th>
                <th>PIC</th>
                <th>Evidence</th>
            </tr>
        </thead>
        <tbody>
            @forelse($remarkRows as $index => $row)
                @php
                    $entries  = $row['entries'] ?? [];
                    $rowCount = max(1, count($entries));
                @endphp
                @for($k = 0; $k < $rowCount; $k++)
                    @php
                        $e       = $entries[$k] ?? null;
                        $ix      = $e['index'] ?? $k;
                        $problem = ($e && $e['problem'] !== '') ? ['text' => $e['problem'], 'date' => $e['date'], 'plan_at' => $e['plan_at'] ?? null, 'jobdesk_label' => $e['jobdesk_label'] ?? null] : null;
                        $rootc   = $e['root_cause'] ?? '';
                        $task    = ($e && $e['action'] !== '') ? ['text' => $e['action'], 'due_date' => $e['due_date']] : null;
                        $pic     = ($e && $e['pic'] !== '') ? ['name' => $e['pic'], 'department' => $e['pic_dept']] : null;
                        $evid    = $e['evidence'] ?? null;
                    @endphp
                    <tr>
                        @if($k === 0)
                            <td rowspan="{{ $rowCount }}" class="rm-center fw-bold">{{ $index + 1 }}</td>
                            <td rowspan="{{ $rowCount }}" class="rm-center fw-bold">
                                @if($linkProjects)
                                    <a href="{{ route('projects.edit', $row['id']) }}" class="text-success text-decoration-none">{{ $row['code'] }}</a>
                                @else
                                    {{ $row['code'] }}
                                @endif
                            </td>
                            <td rowspan="{{ $rowCount }}" style="min-width: 160px;">{{ $row['name'] }}</td>
                            <td rowspan="{{ $rowCount }}" class="rm-center">{{ $row['customer'] }}</td>
                            <td rowspan="{{ $rowCount }}" class="rm-center">{{ $row['due'] }}</td>
                            <td rowspan="{{ $rowCount }}" class="rm-center {{ $row['gap'] >= 0 ? 'rm-gap-pos' : 'rm-gap-neg' }}">{{ $row['gap'] >= 0 ? '+' : '' }}{{ $row['gap'] }}%</td>
                            <td rowspan="{{ $rowCount }}" class="rm-center">{{ $row['plan'] }}%</td>
                            <td rowspan="{{ $rowCount }}" class="rm-center fw-bold">{{ $row['actual'] }}%</td>
                        @endif

                        {{-- Remarks Problem + tanggal masalah --}}
                        <td class="rm-text">
                            @if($problem)
                                <span class="rm-num">{{ $k + 1 }}.</span>{{ $problem['text'] ?? '' }}
                                <span class="rm-date">{{ $fmtRemarkDate($problem['date'] ?? null) }}</span>
                                @if(!empty($problem['jobdesk_label']))
                                    <span class="rm-plan" style="background:#e0e7ff;color:#3730a3;" title="Jobdesk terkait problem">{{ $problem['jobdesk_label'] }}</span>
                                @endif
                                @if(isset($problem['plan_at']) && $problem['plan_at'] !== null)
                                    <span class="rm-plan" title="Planning total proyek pada tanggal masalah">Plan {{ $problem['plan_at'] }}%</span>
                                @endif
                                @include('reports.partials.remark-actions', ['type' => 'problems', 'index' => $ix, 'label' => $problem['text'] ?? '', 'projectId' => $remarkProjectId])
                            @endif
                            @if(($e['evidence_text'] ?? '') !== '')
                                <div class="small text-start" style="white-space: pre-line;">{{ $e['evidence_text'] }}</div>
                            @endif
                            @if(!$evid && ($e['evidence_text'] ?? '') === '' && $k === 0)
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- Root Cause --}}
                        <td class="rm-text">
                            @if($rootc !== '')
                                {{ $rootc }}
                            @elseif($k === 0)
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- Action + due date --}}
                        <td class="rm-text">
                            @if($task)
                                <span class="rm-num">{{ $k + 1 }}.</span>{{ $task['text'] ?? '' }}
                                @include('reports.partials.remark-actions', ['type' => 'tasks', 'index' => $ix, 'label' => $task['text'] ?? '', 'projectId' => $remarkProjectId])
                            @elseif($k === 0)
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="rm-center">
                            @if($task)
                                {{ $fmtRemarkDate($task['due_date'] ?? null) }}
                            @elseif($k === 0)
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- PIC + departemen --}}
                        <td style="min-width: 150px;">
                            @if($pic)
                                <span class="rm-num">{{ $k + 1 }}.</span>{{ $pic['name'] ?? '' }}
                                <span class="rm-dept">{{ $remarkDeptLabels[$pic['department'] ?? ''] ?? ($pic['department'] ?? '-') }}</span>
                                @include('reports.partials.remark-actions', ['type' => 'pics', 'index' => $ix, 'label' => $pic['name'] ?? '', 'projectId' => $remarkProjectId])
                            @elseif($k === 0)
                                <span class="text-muted">-</span>
                            @endif
                        </td>
 
                        {{-- Evidence (lampiran PIC) --}}
                        <td class="rm-center" style="min-width: 110px;">
                            @if($evid)
                                <a href="{{ route('projects.evidence', ['project' => $row['id'], 'path' => $evid['path']]) }}" target="_blank" class="small text-decoration-none" title="{{ $evid['name'] ?? 'Evidence' }}">
                                    <i class="bi bi-paperclip"></i> {{ \Illuminate\Support\Str::limit($evid['name'] ?? 'Evidence', 18) }}
                                </a>
                            @elseif($k === 0)
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @endfor
            @empty
                <tr>
                    <td colspan="14" class="text-center text-muted py-4">{{ $emptyText }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>