@extends('layouts.app')

@section('title', 'PDCA Control Center - PT Mudalaya Energy Indonesia')
@section('page_title', 'PDCA Control Center')

@push('styles')
<style>
    .pdca-board { background: #0b3d2e; border-radius: 16px; padding: 18px; color: #ecfdf5; }
    .pdca-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 14px; }
    .pdca-head h2 { font-weight: 800; letter-spacing: 2px; font-size: 1.5rem; margin: 0; }
    .pdca-head img { height: 44px; background: #fff; border-radius: 8px; padding: 3px; }
    .pdca-legend span { margin-left: 12px; font-size: .8rem; font-weight: 700; }
    .dot { display: inline-block; width: 11px; height: 11px; border-radius: 50%; margin-right: 4px; vertical-align: -1px; }
    .dot-ok { background: #22c55e; } .dot-risk { background: #facc15; } .dot-crit { background: #ef4444; }
    .pdca-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    @media (max-width: 992px) { .pdca-grid { grid-template-columns: 1fr; } }
    .pdca-panel { background: #f8fafc; color: #0f172a; border-radius: 12px; overflow: visible; position: relative; display: flex; flex-direction: column; min-height: 250px; }
    .pdca-panel:has(.dropdown-menu.show) { z-index: 60; }
    .pdca-panel-title { border-radius: 12px 12px 0 0; background: #14532d; color: #fff; font-weight: 800; font-size: .82rem; letter-spacing: .5px; padding: 8px 12px; text-transform: uppercase; }
    .pdca-panel-title .num { display: inline-block; background: #facc15; color: #14532d; border-radius: 50%; width: 22px; height: 22px; text-align: center; line-height: 22px; margin-right: 8px; }
    .pdca-body { padding: 12px; flex: 1; font-size: .82rem; }
    .pdca-ph { display: flex; align-items: center; justify-content: center; text-align: center; height: 100%; min-height: 150px; color: #64748b; border: 2px dashed #cbd5e1; border-radius: 10px; padding: 10px; }
    .tag-ph { font-size: .65rem; background: #fde68a; color: #78350f; border-radius: 999px; padding: 1px 8px; margin-left: 6px; font-weight: 700; }
    .stat-row { display: flex; gap: 8px; flex-wrap: wrap; }
    .stat-box { flex: 1 1 70px; border-radius: 10px; padding: 8px; text-align: center; color: #fff; }
    .stat-box b { display: block; font-size: 1.6rem; line-height: 1.1; }
    .donuts { display: flex; justify-content: space-around; gap: 6px; }
    .donut { text-align: center; font-weight: 700; font-size: .75rem; }
    .bar-row { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
    .bar-row .lbl { width: 88px; font-weight: 700; }
    .bar-track { flex: 1; height: 12px; background: #e2e8f0; border-radius: 6px; position: relative; }
    .bar-fill { height: 100%; border-radius: 6px; }
    .bar-plan { position: absolute; top: -3px; bottom: -3px; width: 3px; background: #0f172a; }
    .weekbars { display: flex; align-items: flex-end; gap: 10px; height: 90px; margin-bottom: 8px; }
    .weekbars .wk { flex: 1; text-align: center; font-weight: 700; }
    .weekbars .wk div { background: #16a34a; border-radius: 6px 6px 0 0; min-height: 4px; }
    .pdca-list { list-style: none; margin: 0; padding: 0; }
    .pdca-list li { padding: 4px 0; border-bottom: 1px dashed #cbd5e1; }
    /* Panel 1: 4 kotak status dalam grid 2x2 yang mengisi ruang panel */
    .pdca-body.col-fill { display: flex; flex-direction: column; }
    .stat-grid { display: grid; grid-template-columns: 1fr 1fr; grid-auto-rows: 1fr; gap: 10px; flex: 1; min-height: 190px; }
    .stat-grid .stat-box { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 14px 8px; font-size: .95rem; font-weight: 600; }
    .stat-grid .stat-box b { font-size: 2.6rem; }
    /* Panel 2: Planning di tengah atas, Actual & Gap di bawahnya */
    #donutsPlan { display: grid; grid-template-columns: 1fr 1fr; row-gap: 10px; justify-items: center; }
    #donutsPlan .donut:first-child { grid-column: 1 / -1; }
    #donutsPlan .donut svg { width: 118px; height: 118px; }
    #donutsPlan .donut:first-child svg { width: 138px; height: 138px; }
    .pdca-dd { margin-top: 10px; }
    .pdca-dd-btn { border: 1.5px solid #14532d; background: #fff; color: #0f172a; font-weight: 700; font-size: .78rem; border-radius: 8px; padding: 6px 10px; display: flex; justify-content: space-between; align-items: center; gap: 8px; }
    .pdca-dd-btn .lbl { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pdca-dd .dropdown-menu { max-height: 230px; overflow-y: auto; font-size: .78rem; }
    .pdca-dd .dropdown-item { white-space: normal; padding: 5px 12px; }
    .pdca-dd .dropdown-item.active, .pdca-dd .dropdown-item:active { background: #14532d; color: #fff; }
    .pdca-mat-info { text-align: center; font-size: .75rem; color: #475569; margin-top: 6px; }
    .pdca-sub { font-weight: 800; font-size: .76rem; margin: 10px 0 4px; }
    .pdca-sub.late { color: #dc2626; }
    .pdca-foot { text-align: center; font-weight: 800; letter-spacing: 3px; margin-top: 14px; color: #facc15; }

    /* ===== ANIMASI MASUK ===== */
    @keyframes boardIn   { from { opacity: 0; transform: scale(.985); } to { opacity: 1; transform: none; } }
    @keyframes panelIn   { from { opacity: 0; transform: translateY(26px) scale(.96); } to { opacity: 1; transform: none; } }
    @keyframes titleIn   { from { opacity: 0; letter-spacing: 14px; } to { opacity: 1; letter-spacing: 2px; } }
    @keyframes slideIn   { from { opacity: 0; transform: translateX(-16px); } to { opacity: 1; transform: none; } }
    @keyframes popIn     { 0% { opacity: 0; transform: scale(.4); } 70% { transform: scale(1.1); } 100% { opacity: 1; transform: scale(1); } }
    @keyframes growX     { from { transform: scaleX(0); } to { transform: scaleX(1); } }
    @keyframes growY     { from { transform: scaleY(0); } to { transform: scaleY(1); } }
    @keyframes drawArc   { from { stroke-dasharray: 0 var(--circ); } to { stroke-dasharray: var(--dash) var(--circ); } }
    @keyframes planIn    { from { opacity: 0; transform: scaleY(0); } to { opacity: 1; transform: scaleY(1); } }
    @keyframes alertGlow { 0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,.55); } 50% { box-shadow: 0 0 0 9px rgba(239,68,68,0); } }
    @keyframes footGlow  { 0%, 100% { text-shadow: 0 0 0 rgba(250,204,21,0); } 50% { text-shadow: 0 0 14px rgba(250,204,21,.9); } }
    @keyframes shine     { from { background-position: -200% 0; } to { background-position: 200% 0; } }

    .pdca-board { animation: boardIn .6s ease-out both; }
    .pdca-head h2 { animation: titleIn 1.1s cubic-bezier(.2,.8,.2,1) both; }
    .pdca-head img { animation: popIn .7s .2s cubic-bezier(.2,.8,.2,1) both; }
    .pdca-panel { animation: panelIn .65s cubic-bezier(.2,.8,.2,1) backwards; transition: transform .25s ease, box-shadow .25s ease; }
    .pdca-panel:hover { transform: translateY(-4px); box-shadow: 0 10px 24px rgba(0,0,0,.35); }
    .pdca-grid .pdca-panel:nth-child(1) { animation-delay: .15s; } .pdca-grid .pdca-panel:nth-child(2) { animation-delay: .25s; }
    .pdca-grid .pdca-panel:nth-child(3) { animation-delay: .35s; } .pdca-grid .pdca-panel:nth-child(4) { animation-delay: .45s; }
    .pdca-grid .pdca-panel:nth-child(5) { animation-delay: .55s; } .pdca-grid .pdca-panel:nth-child(6) { animation-delay: .65s; }
    .pdca-grid .pdca-panel:nth-child(7) { animation-delay: .75s; } .pdca-grid .pdca-panel:nth-child(8) { animation-delay: .85s; }
    .pdca-grid .pdca-panel:nth-child(9) { animation-delay: .95s; }
    .pdca-panel-title { background-image: linear-gradient(110deg, transparent 35%, rgba(255,255,255,.22) 50%, transparent 65%); background-size: 200% 100%; background-color: #14532d; animation: shine 2.2s 1s ease-out 1 both; }
    .pdca-panel-title .num { animation: popIn .5s .6s both; }

    .donut-arc { animation: drawArc 1.4s .8s cubic-bezier(.3,.7,.2,1) both; }
    .bar-fill { transform-origin: left; animation: growX 1.1s .9s cubic-bezier(.3,.7,.2,1) both; }
    .bar-plan { animation: planIn .5s 1.8s both; }
    .weekbars .wk div { transform-origin: bottom; animation: growY .9s 1s cubic-bezier(.3,.7,.2,1) both; }
    .stat-box { animation: popIn .6s both; }
    .pdca-list li { animation: slideIn .5s both; }
    .stat-box.alert-pulse { animation: popIn .6s both, alertGlow 1.8s 1.6s infinite; }
    .pdca-foot { animation: footGlow 2.6s 1.5s infinite; }

</style>
@endpush

@php
    $pctColor = fn($v) => $v >= 100 ? '#16a34a' : ($v > 50 ? '#facc15' : '#ef4444');
    $donut = function ($pct, $color, $label) {
        $pct = max(0, min(100, $pct)); $r = 40; $circ = 2 * M_PI * $r; $dash = $circ * $pct / 100;
        return '<div class="donut"><svg width="96" height="96" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="#e2e8f0" stroke-width="14"/>'
            . '<circle class="donut-arc" cx="50" cy="50" r="40" fill="none" stroke="' . $color . '" stroke-width="14" stroke-linecap="' . ($pct > 0 ? 'round' : 'butt') . '" style="--dash:' . round($dash, 2) . ';--circ:' . round($circ, 2) . '" stroke-dasharray="' . round($dash, 2) . ' ' . round($circ, 2) . '" transform="rotate(-90 50 50)"/>'
            . '<text x="50" y="56" text-anchor="middle" font-size="20" font-weight="800" fill="#0f172a"><tspan data-count="' . $pct . '" data-suffix="%">' . $pct . '%</tspan></text></svg><div>' . $label . '</div></div>';
    };
    $gap = $avgActual - $avgPlan;
    $maxWeek = max(1, max($weeks));
@endphp

@section('content')
<div class="pdca-board">
    <div class="pdca-head">
        <img src="{{ asset('images/logo-mudalaya-badge.png') }}" alt="Mudalaya">
        <h2>PDCA CONTROL CENTER</h2>
        <div class="d-flex align-items-center gap-3">
            <div class="pdca-legend">
                <span><i class="dot dot-ok"></i>Normal</span><span><i class="dot dot-risk"></i>Risk</span><span><i class="dot dot-crit"></i>Critical</span>
            </div>
            <img src="{{ asset('images/logo-mutiara-badge.png') }}" alt="Mutiara">
        </div>
    </div>

    <div class="pdca-grid">
        {{-- 1 --}}
        <div class="pdca-panel">
            <div class="pdca-panel-title"><span class="num">1</span>Project Status</div>
            <div class="pdca-body col-fill">
                <div class="mb-2 fw-bold">Total proyek: {{ $total }}</div>
                <div class="stat-grid">
                    <div class="stat-box" style="background:#059669"><b data-count="{{ $statusCounts['ON_TRACK'] }}">{{ $statusCounts['ON_TRACK'] }}</b>On Track</div>
                    <div class="stat-box" style="background:#1e3a8a"><b data-count="{{ $statusCounts['LEADING'] }}">{{ $statusCounts['LEADING'] }}</b>Leading</div>
                    <div class="stat-box" style="background:#d97706"><b data-count="{{ $statusCounts['AT_RISK'] }}">{{ $statusCounts['AT_RISK'] }}</b>At Risk</div>
                    <div class="stat-box {{ $statusCounts['DELAYED'] > 0 ? 'alert-pulse' : '' }}" style="background:#dc2626"><b data-count="{{ $statusCounts['DELAYED'] }}">{{ $statusCounts['DELAYED'] }}</b>Delayed</div>
                    <div class="stat-box" style="background:#ea580c"><b data-count="{{ $statusCounts['CLOSED_LATE'] }}">{{ $statusCounts['CLOSED_LATE'] }}</b>Close Terlambat</div>
                </div>
            </div>
        </div>

        {{-- 2 --}}
        <div class="pdca-panel">
            <div class="pdca-panel-title"><span class="num">2</span>Plan vs Actual (Overall)</div>
            <div class="pdca-body">
                <div class="donuts" id="donutsPlan">
                    {!! $donut($avgPlan, '#16a34a', 'Planning') !!}
                    {!! $donut($avgActual, $pctColor($avgActual), 'Actual') !!}
                    {!! $donut(abs($gap), $gap >= 0 ? '#16a34a' : '#ef4444', 'Gap ' . ($gap >= 0 ? '+' : '') . $gap . '%') !!}
                </div>
                <div class="dropdown pdca-dd" data-pdca-dd="plan">
                    <button type="button" class="pdca-dd-btn w-100 dropdown-toggle text-start" data-bs-toggle="dropdown" aria-expanded="false"><span class="lbl">Semua Proyek (Overall)</span></button>
                    <ul class="dropdown-menu w-100 shadow border-0">
                        <li><button type="button" class="dropdown-item active" data-id="">Semua Proyek (Overall)</button></li>
                        @foreach($perProject as $pp)
                            <li><button type="button" class="dropdown-item" data-id="{{ $pp['id'] }}">{{ $pp['label'] }}</button></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        {{-- 3 --}}
        <div class="pdca-panel">
            <div class="pdca-panel-title"><span class="num">3</span>Due Date - Next 4 Weeks</div>
            <div class="pdca-body">
                <div class="weekbars">
                    @foreach($weeks as $w => $n)
                        <div class="wk"><span data-count="{{ $n }}">{{ $n }}</span><div style="height: {{ round($n / $maxWeek * 60) }}px"></div>W{{ $w }}</div>
                    @endforeach
                </div>
                <div class="pdca-sub late"><i class="bi bi-exclamation-octagon-fill"></i> Sudah melewati due date: {{ $pastDueTotal }} project</div>
                @if($pastDueTotal)
                    <ul class="pdca-list">
                        @foreach($pastDue as $d)
                            <li><b>{{ $d['code'] }}</b> {{ \Illuminate\Support\Str::limit($d['name'], 22) }}<br><span class="text-muted">due {{ $d['due'] }} · <span class="text-danger fw-bold">+{{ $d['late'] }} hari</span> · progress {{ $d['actual'] }}%</span></li>
                        @endforeach
                    </ul>
                    @if($pastDueTotal > count($pastDue))<div class="text-muted" style="font-size:.7rem">+{{ $pastDueTotal - count($pastDue) }} project lainnya (terlambat terlama dulu)</div>@endif
                @else
                    <div class="text-success" style="font-size:.78rem">Tidak ada project yang melewati due date.</div>
                @endif
                <div class="pdca-sub">Due date 4 minggu ke depan</div>
                <ul class="pdca-list">
                    @forelse($dueList as $d)
                        <li><b>{{ $d['code'] }}</b> {{ \Illuminate\Support\Str::limit($d['name'], 22) }} <span class="float-end">{{ $d['due'] }} ({{ $d['days'] }}h) · {{ $d['actual'] }}%</span></li>
                    @empty
                        <li class="text-muted">Tidak ada due date dalam 4 minggu ke depan.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- 4 --}}
        <div class="pdca-panel">
            <div class="pdca-panel-title"><span class="num">4</span>Material Readiness</div>
            <div class="pdca-body">
                @php $matPct = $materialOverall['pct']; $matGap = 100 - $matPct; @endphp
                <div class="donuts" id="donutsMat">
                    {!! $donut($matPct, $pctColor($matPct), 'Barang Datang') !!}
                    {!! $donut($matGap, $matGap > 0 ? '#ef4444' : '#16a34a', 'Gap ' . ($matGap > 0 ? '-' : '') . $matGap . '%') !!}
                </div>
                <div class="pdca-mat-info" id="matInfo">
                    @if($materialOverall['items'] > 0){{ $materialOverall['full'] }} dari {{ $materialOverall['items'] }} item sudah lengkap (semua proyek)@else Belum ada data material @endif
                </div>
                <div class="dropdown pdca-dd" data-pdca-dd="mat">
                    <button type="button" class="pdca-dd-btn w-100 dropdown-toggle text-start" data-bs-toggle="dropdown" aria-expanded="false"><span class="lbl">Semua Proyek (Menyeluruh)</span></button>
                    <ul class="dropdown-menu w-100 shadow border-0">
                        <li><button type="button" class="dropdown-item active" data-id="">Semua Proyek (Menyeluruh)</button></li>
                        @foreach($materialByProject as $mp)
                            <li><button type="button" class="dropdown-item" data-id="{{ $mp['id'] }}">{{ $mp['label'] }}</button></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        {{-- 5 --}}
        <div class="pdca-panel">
            <div class="pdca-panel-title"><span class="num">5</span>Production Progress</div>
            <div class="pdca-body">
              <div id="stageBars">
                @foreach($stages as $s)
                    <div class="bar-row">
                        <div class="lbl">{{ $s['label'] }}</div>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: {{ $s['actual'] }}%; background: {{ $s['actual'] >= $s['plan'] ? '#16a34a' : ($s['plan'] - $s['actual'] > 5 ? '#ef4444' : '#facc15') }}"></div>
                            <div class="bar-plan" style="left: {{ $s['plan'] }}%" title="Plan {{ $s['plan'] }}%"></div>
                        </div>
                        <div style="width:44px;text-align:right;font-weight:700">{{ $s['actual'] }}%</div>
                    </div>
                @endforeach
              </div>
                <div class="text-muted" style="font-size:.7rem">Garis hitam = planning rata-rata tahap. Preparation = gabungan PPIC, Engineering, Procurement &amp; Preparation produksi.</div>
                <div class="dropdown pdca-dd" data-pdca-dd="stage">
                    <button type="button" class="pdca-dd-btn w-100 dropdown-toggle text-start" data-bs-toggle="dropdown" aria-expanded="false"><span class="lbl">Semua Proyek (Rata-rata)</span></button>
                    <ul class="dropdown-menu w-100 shadow border-0">
                        <li><button type="button" class="dropdown-item active" data-id="">Semua Proyek (Rata-rata)</button></li>
                        @foreach($stageByProject as $sp)
                            <li><button type="button" class="dropdown-item" data-id="{{ $sp['id'] }}">{{ $sp['label'] }}</button></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        {{-- 6 --}}
        <div class="pdca-panel">
            <div class="pdca-panel-title"><span class="num">6</span>Quality / NCR</div>
            <div class="pdca-body"><div class="pdca-ph">Data NCR / kualitas belum tersedia <span class="tag-ph">PLACEHOLDER</span></div></div>
        </div>

        {{-- 7 --}}
        <div class="pdca-panel">
            <div class="pdca-panel-title"><span class="num">7</span>Delivery</div>
            <div class="pdca-body">
                <div class="stat-row">
                    <div class="stat-box" style="background:#16a34a"><b data-count="{{ $delivery['done'] }}">{{ $delivery['done'] }}</b>Terkirim</div>
                    <div class="stat-box" style="background:#facc15;color:#422006"><b data-count="{{ $delivery['next'] }}">{{ $delivery['next'] }}</b>4 Minggu ke depan</div>
                    <div class="stat-box {{ $delivery['late'] > 0 ? 'alert-pulse' : '' }}" style="background:#ef4444"><b data-count="{{ $delivery['late'] }}">{{ $delivery['late'] }}</b>Terlambat</div>
                </div>
            </div>
        </div>

        {{-- 8 --}}
        <div class="pdca-panel">
            <div class="pdca-panel-title"><span class="num">8</span>Top 5 Problems</div>
            <div class="pdca-body">
                <ol class="pdca-list ps-3">
                    @forelse($problems as $pr)
                        <li><b>{{ $pr['code'] }}</b> · {{ $pr['division'] }}<br>{{ \Illuminate\Support\Str::limit($pr['problem'], 70) }}</li>
                    @empty
                        <li class="text-muted" style="list-style:none">Belum ada problem tercatat.</li>
                    @endforelse
                </ol>
                @if($problemTotal > 5)<div class="text-muted" style="font-size:.7rem">dari {{ $problemTotal }} problem (terlama dulu)</div>@endif
            </div>
        </div>

        {{-- 9 --}}
        <div class="pdca-panel">
            <div class="pdca-panel-title"><span class="num">9</span>Overdue Action</div>
            <div class="pdca-body">
                <div class="fw-bold mb-1 {{ $overdueTotal ? 'text-danger' : 'text-success' }}">{{ $overdueTotal }} action melewati due date</div>
                <ul class="pdca-list">
                    @foreach($overdue as $o)
                        <li><b>{{ $o['code'] }}</b> · {{ \Illuminate\Support\Str::limit($o['action'], 45) }}<br><span class="text-muted">PIC {{ $o['pic'] ?: '-' }} · due {{ $o['due'] }} · <span class="text-danger fw-bold">+{{ $o['late'] }} hari</span></span></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <div class="pdca-foot">ABNORMALITY MUST BE VISIBLE</div>
</div>

<script>
    (function () {
        // ===== Dropdown pilih proyek (panel 2 & 4) =====
        var PLAN = @json($perProject), PLAN_ALL = { plan: {{ $avgPlan }}, actual: {{ $avgActual }} };
        var MAT = @json($materialByProject), MAT_ALL = @json($materialOverall);
        var STAGE = @json($stageByProject), STAGE_ALL = @json($stages);
        function color(v) { return v >= 100 ? '#16a34a' : (v > 50 ? '#facc15' : '#ef4444'); }
        function donut(pct, col, label) {
            pct = Math.max(0, Math.min(100, pct));
            var circ = 2 * Math.PI * 40, dash = circ * pct / 100;
            return '<div class="donut"><svg width="96" height="96" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="#e2e8f0" stroke-width="14"/>' +
                '<circle class="donut-arc" style="animation-delay:0s;--dash:' + dash.toFixed(2) + ';--circ:' + circ.toFixed(2) + '" cx="50" cy="50" r="40" fill="none" stroke="' + col + '" stroke-width="14" stroke-linecap="' + (pct > 0 ? 'round' : 'butt') + '"' +
                ' stroke-dasharray="' + dash.toFixed(2) + ' ' + circ.toFixed(2) + '" transform="rotate(-90 50 50)"/>' +
                '<text x="50" y="56" text-anchor="middle" font-size="20" font-weight="800" fill="#0f172a">' + pct + '%</text></svg><div>' + label + '</div></div>';
        }
        function find(list, id) { for (var i = 0; i < list.length; i++) { if (String(list[i].id) === String(id)) { return list[i]; } } return null; }
        function drawPlan(id) {
            var d = id === '' ? PLAN_ALL : find(PLAN, id); if (!d) { return; }
            var gap = d.actual - d.plan;
            document.getElementById('donutsPlan').innerHTML = donut(d.plan, '#16a34a', 'Planning') + donut(d.actual, color(d.actual), 'Actual') +
                donut(Math.abs(gap), gap >= 0 ? '#16a34a' : '#ef4444', 'Gap ' + (gap >= 0 ? '+' : '') + gap + '%');
        }
        function drawMat(id) {
            var d = id === '' ? MAT_ALL : find(MAT, id); if (!d) { return; }
            var gap = 100 - d.pct, info = document.getElementById('matInfo');
            document.getElementById('donutsMat').innerHTML = donut(d.pct, color(d.pct), 'Barang Datang') +
                donut(gap, gap > 0 ? '#ef4444' : '#16a34a', 'Gap ' + (gap > 0 ? '-' : '') + gap + '%');
            info.textContent = d.items > 0 ? (d.full + ' dari ' + d.items + ' item sudah lengkap' + (id === '' ? ' (semua proyek)' : '')) : 'Belum ada data material untuk ' + (id === '' ? 'proyek mana pun' : 'proyek ini');
        }
        function drawStages(id) {
            var list = id === '' ? STAGE_ALL : (find(STAGE, id) || {}).stages; if (!list) { return; }
            document.getElementById('stageBars').innerHTML = list.map(function (s) {
                var c = s.actual >= s.plan ? '#16a34a' : (s.plan - s.actual > 5 ? '#ef4444' : '#facc15');
                return '<div class="bar-row"><div class="lbl">' + s.label + '</div><div class="bar-track">' +
                    '<div class="bar-fill" style="width:' + s.actual + '%;background:' + c + ';animation-delay:0s"></div>' +
                    '<div class="bar-plan" style="left:' + s.plan + '%;animation-delay:.3s" title="Plan ' + s.plan + '%"></div></div>' +
                    '<div style="width:44px;text-align:right;font-weight:700">' + s.actual + '%</div></div>';
            }).join('');
        }
        document.querySelectorAll('[data-pdca-dd]').forEach(function (dd) {
            var kind = dd.getAttribute('data-pdca-dd'), btn = dd.querySelector('.pdca-dd-btn');
            dd.querySelectorAll('.dropdown-item').forEach(function (it) {
                it.addEventListener('click', function () {
                    dd.querySelectorAll('.dropdown-item').forEach(function (x) { x.classList.remove('active'); });
                    it.classList.add('active');
                    btn.querySelector('.lbl').textContent = it.textContent.trim();
                    var id = it.getAttribute('data-id');
                    if (kind === 'plan') { drawPlan(id); } else if (kind === 'stage') { drawStages(id); } else { drawMat(id); }
                });
            });
        });

        // Jeda animasi bertingkat untuk kotak statistik & daftar
        document.querySelectorAll('.stat-row, .stat-grid').forEach(function (row) {
            Array.prototype.forEach.call(row.children, function (el, i) { el.style.animationDelay = (0.9 + i * 0.12) + 's'; });
        });
        document.querySelectorAll('.pdca-list').forEach(function (list) {
            Array.prototype.forEach.call(list.children, function (el, i) { el.style.animationDelay = (1.0 + i * 0.1) + 's'; });
        });
        document.querySelectorAll('.bar-row').forEach(function (row, i) {
            var fill = row.querySelector('.bar-fill'), plan = row.querySelector('.bar-plan');
            if (fill) { fill.style.animationDelay = (0.9 + i * 0.15) + 's'; }
            if (plan) { plan.style.animationDelay = (1.8 + i * 0.15) + 's'; }
        });
        document.querySelectorAll('.weekbars .wk div').forEach(function (el, i) { el.style.animationDelay = (1.0 + i * 0.12) + 's'; });

        // Angka menghitung naik dari 0
        document.querySelectorAll('[data-count]').forEach(function (el) {
            var target = parseFloat(el.getAttribute('data-count')) || 0;
            var suffix = el.getAttribute('data-suffix') || '';
            var delay = 900, duration = 1300, start = null;
            el.textContent = '0' + suffix;
            function step(ts) {
                if (start === null) { start = ts; }
                var p = Math.min(1, Math.max(0, (ts - start) / duration));
                var eased = 1 - Math.pow(1 - p, 3);
                el.textContent = Math.round(target * eased) + suffix;
                if (p < 1) { requestAnimationFrame(step); } else { el.textContent = target + suffix; }
            }
            setTimeout(function () { requestAnimationFrame(step); }, delay);
        });
    })();
</script>
@endsection