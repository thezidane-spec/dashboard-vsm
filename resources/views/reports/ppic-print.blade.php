<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - {{ $subtitle }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', Arial, sans-serif; margin: 0; padding: 16px; color: #0f172a; background: #fff; }

        .toolbar { display: flex; gap: 8px; margin-bottom: 12px; }
        .toolbar button { background: #047857; color: #fff; border: 0; border-radius: 8px; padding: 8px 16px; font-weight: 700; cursor: pointer; font-family: inherit; }
        .toolbar button.secondary { background: #e2e8f0; color: #0f172a; }

        /* Banner judul + dua logo perusahaan */
        .banner { background: #0f2a52; color: #fff; display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 10px 20px; border-radius: 6px; margin-bottom: 10px; }
        .banner img { width: 62px; height: 62px; object-fit: contain; flex-shrink: 0; }
        .banner .center { text-align: center; flex: 1; }
        .banner h1 { font-size: 1.45rem; margin: 0; font-weight: 800; letter-spacing: 0.4px; }
        .banner .sub { font-size: 0.9rem; font-weight: 700; margin-top: 4px; letter-spacing: 0.3px; }
        .meta { font-size: 0.68rem; color: #475569; margin-bottom: 8px; }

        table.rt { width: 100%; border-collapse: collapse; font-size: 7.5pt; }
        table.rt th, table.rt td { border: 1px solid #94a3b8; padding: 3px 4px; text-align: center; vertical-align: middle; }
        table.rt thead th { background: #f1f5f9; font-size: 6.5pt; text-transform: uppercase; }
        table.rt td.l { text-align: left; }
        table.rt td.name { text-align: left; min-width: 120px; }
        table.rt tr { page-break-inside: avoid; }

        .group-prep  { background: #dcfce7 !important; color: #14532d; }
        .group-prod-prep { background: #e0f2fe; color: #075985; }
    .group-prod-main { background: #bfdbfe; color: #1e3a8a; }
    .group-prod  { background: #dbeafe !important; color: #1e3a8a; }
        .group-qaqc  { background: #ede9fe !important; color: #4c1d95; }
        .group-qc    { background: #fef3c7 !important; color: #78350f; }
        .group-total-prep { background: #bbf7d0 !important; font-weight: 800; }
        .group-total-prod { background: #bfdbfe !important; font-weight: 800; }
        .group-total-qc   { background: #fde68a !important; font-weight: 800; }
        .group-total-qaqc { background: #ddd6fe !important; font-weight: 800; }

        /* Warna persen: 0-50 merah, 51-99 kuning, 100 hijau */
        .pct-cell { text-align: center; font-weight: 700; }
    .pct-pill { display: inline-block; min-width: 34px; padding: 1px 5px; border-radius: 5px; font-weight: 700; border: 1px solid transparent; }
        .pct-red { background-color: #fee2e2 !important; color: #b91c1c !important; }
        .pct-yellow { background-color: #fef3c7 !important; color: #b45309 !important; }
        .pct-green { background-color: #dcfce7 !important; color: #15803d !important; }

        .status-badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-weight: 800; font-size: 6.5pt; border: 1.5px solid transparent; white-space: nowrap; }
        .status-badge-on-track { background: #d1fae5; color: #065f46; border-color: #6ee7b7; }
        .status-badge-leading  { background: #1e3a8a; color: #ffffff; border-color: #1e40af; }
        .status-badge-at-risk  { background: #fef3c7; color: #92400e; border-color: #fde68a; }
        .status-badge-delayed  { background: #fee2e2; color: #991b1b; border-color: #fecaca; }
        .status-badge-closed-late { background: #ffedd5; color: #9a3412; border-color: #fdba74; }
        .status-none           { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }

        .gap-pos { color: #15803d; font-weight: 700; }
        .gap-neg { color: #b91c1c; font-weight: 700; }

        @page { size: A3 landscape; margin: 8mm; }

        @media print {
            body { padding: 0; }
            .toolbar { display: none; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            thead { display: table-header-group; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Cetak / Simpan sebagai PDF</button>
        <button type="button" class="secondary" onclick="window.close()">Tutup</button>
    </div>

    <div class="banner">
        <img src="{{ asset('images/logo-mudalaya-badge.png') }}" alt="Logo PT Mudalaya Energy Indonesia">
        <div class="center">
            <h1>{{ $title }}</h1>
            <div class="sub">{{ $subtitle }}</div>
        </div>
        <img src="{{ asset('images/logo-mutiara-badge.png') }}" alt="Logo PT Mutiara Global Industry">
    </div>

    <div class="meta">Dicetak: {{ now()->format('d M Y H:i') }} WIB &nbsp;|&nbsp; Warna persen: 0-50% merah, 51-99% kuning, 100% hijau</div>

    <table class="rt">
        @php
                    $prepKeys = array_keys(\App\Models\Project::$productionStages['preparation']['tasks']);
                    $prodPrep = array_filter($productionTasks, fn($t, $k) => in_array($k, $prepKeys, true), ARRAY_FILTER_USE_BOTH);
                    $prodMain = array_diff_key($productionTasks, $prodPrep);
                @endphp
                <thead>
            <tr>
                <th rowspan="3">No</th>
                <th rowspan="3">Kode Proyek</th>
                <th rowspan="3">Nama Proyek</th>
                <th rowspan="3">Customer</th>
                <th rowspan="3">Contract<br>Review</th>
                <th rowspan="3">Due Date</th>
                <th rowspan="3">Time Consumption<br>(Day)</th>
                <th rowspan="3">Time Consumption<br>(Hour)</th>
                <th rowspan="3">Qty</th>
                <th rowspan="3">Fabricator</th>
                <th colspan="{{ count($prepDivisions) + 1 }}" class="group-prep">Preparation ({{ array_sum(array_column($prepDivisions, 'weight')) }}%)</th>
                <th colspan="{{ count($productionTasks) + 1 }}" class="group-prod">Production ({{ array_sum(array_column($productionTasks, 'weight')) }}%)</th>
                <th colspan="{{ count($qcTasks) + 1 }}" class="group-qc">Finish Process ({{ array_sum(\App\Models\Project::$qcTasks) }}%)</th>
                        <th colspan="{{ count($qaqcTasks) + 1 }}" class="group-qaqc">QA/QC ({{ \App\Models\Project::QAQC_TOTAL }}%)</th>
                <th rowspan="3">Planning (%)</th>
                <th rowspan="3">Total<br>Percentage</th>
                <th rowspan="3">Gap</th>
                <th rowspan="3">Status</th>
            </tr>
            <tr>
                @foreach($prepDivisions as $div)
                    <th rowspan="2" class="group-prep">{{ $div['label'] }}</th>
                @endforeach
                <th rowspan="2" class="group-total-prep">Total %</th>
                        <th colspan="{{ count($prodPrep) }}" class="group-prod-prep">Preparation ({{ array_sum(array_column($prodPrep, 'weight')) }}%)</th>
                        <th colspan="{{ count($prodMain) }}" class="group-prod-main">Production ({{ array_sum(array_column($prodMain, 'weight')) }}%)</th>
                        <th rowspan="2" class="group-total-prod">Total %</th>

                @foreach($qcTasks as $task)
                    <th rowspan="2" class="group-qc">{{ $task['label'] }}</th>
                @endforeach
                <th rowspan="2" class="group-total-qc">Total %</th>

                        @foreach($qaqcTasks as $task)
                            <th rowspan="2" class="group-qaqc">{{ $task['label'] }}</th>
                        @endforeach
                        <th rowspan="2" class="group-total-qaqc">Total %</th>
                    </tr>
                    <tr>
                        @foreach($prodPrep as $task)
                            <th class="group-prod-prep" title="Bobot +{{ rtrim(rtrim(number_format($task['weight'], 1), '0'), '.') }}%">{{ $task['label'] }}</th>
                        @endforeach
                        @foreach($prodMain as $task)
                            <th class="group-prod-main" title="Bobot +{{ rtrim(rtrim(number_format($task['weight'], 1), '0'), '.') }}%">{{ $task['label'] }}</th>
                        @endforeach
</tr>
        </thead>
        <tbody>
            @forelse($rows as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="l"><strong>{{ $row['code'] }}</strong></td>
                    <td class="name">{{ $row['name'] }}</td>
                    <td>{{ $row['customer'] }}</td>
                    <td>{{ $row['contract'] }}</td>
                    <td>{{ $row['due'] }}</td>
                    <td>{{ $row['time_days'] ?? '-' }}</td>
                    <td>{{ $row['time_hours'] !== null ? rtrim(rtrim(number_format($row['time_hours'], 2), '0'), '.') : '-' }}</td>
                    <td>{{ $row['qty'] }}</td>
                    <td>{{ $row['fabricator'] ?: '-' }}</td>

                    @foreach($prepDivisions as $divKey => $div)
                        <td class="pct-cell {{ \App\Http\Controllers\ReportController::pctClass($row['prep_percents'][$divKey]) }}">{{ $row['prep_percents'][$divKey] }}%</td>
                    @endforeach
                    <td class="group-total-prep">{{ $row['prep_group_pct'] }}%</td>

                    @foreach($productionTasks as $taskKey => $task)
                        @if($row['production_cells'][$taskKey] === null)<td>-</td>@else <td class="pct-cell {{ \App\Http\Controllers\ReportController::pctClass($row['production_cells'][$taskKey]) }}">{{ $row['production_cells'][$taskKey] }}%</td>@endif
                    @endforeach
                    <td class="group-total-prod">{{ $row['production_group_pct'] }}%</td>

                    @foreach($qcTasks as $taskKey => $task)
                        @if($row['qc_cells'][$taskKey] === null)<td>-</td>@else <td class="pct-cell {{ \App\Http\Controllers\ReportController::pctClass($row['qc_cells'][$taskKey]) }}">{{ $row['qc_cells'][$taskKey] }}%</td>@endif
                    @endforeach
                    <td class="group-total-qc">{{ $row['qc_group_pct'] }}%</td>

                    @foreach($qaqcTasks as $taskKey => $task)
                        @if($row['qaqc_cells'][$taskKey] === null)<td>-</td>@else <td class="pct-cell {{ \App\Http\Controllers\ReportController::pctClass($row['qaqc_cells'][$taskKey]) }}">{{ $row['qaqc_cells'][$taskKey] }}%</td>@endif
                    @endforeach
                    <td class="group-total-qaqc">{{ $row['qaqc_group_pct'] }}%</td>

                    <td>{{ $row['plan'] }}%</td>
                    <td><strong>{{ $row['actual'] }}%</strong></td>
                    <td class="{{ $row['gap'] >= 0 ? 'gap-pos' : 'gap-neg' }}">{{ $row['gap'] >= 0 ? '+' : '' }}{{ $row['gap'] }}%</td>
                    <td><span class="status-badge {{ $row['status']['code'] === 'NO_DUE_DATE' ? 'status-none' : $row['status']['class'] }}">{{ $row['status']['label'] }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 13 + count($prepDivisions) + count($productionTasks) + count($qcTasks) + count($qaqcTasks) + 2 }}">Tidak ada proyek pada periode yang dipilih.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 600);
        });
    </script>
</body>
</html>