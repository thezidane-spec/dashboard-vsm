{{--
    Kartu S-Curve satu proyek: ringkasan + grafik Rencana vs Aktual + tabel target per divisi.
    Variabel: $sc (hasil App\Support\SCurve::build), $canvasId (id unik canvas)
--}}
@php
    $scFmtDate = fn($iso) => $iso ? \Carbon\Carbon::parse($iso)->format('d M Y') : '-';
    $scDev     = $sc['deviation'];
@endphp

<div class="row g-2 mb-3 sc-stats">
    <div class="col-6 col-md-3">
        <div class="border rounded-3 p-2 text-center bg-light">
            <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 0.68rem;">Rencana Hari Ini</small>
            <span class="fw-bold fs-5" style="color: #2563eb;">{{ $sc['plan_today'] }}%</span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="border rounded-3 p-2 text-center bg-light">
            <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 0.68rem;">Aktual Hari Ini</small>
            <span class="fw-bold fs-5 text-success">{{ $sc['actual_today'] }}%</span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="border rounded-3 p-2 text-center bg-light">
            <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 0.68rem;">Deviasi (Aktual - Rencana)</small>
            <span class="fw-bold fs-5 {{ $scDev >= 0 ? 'text-success' : 'text-danger' }}">{{ $scDev >= 0 ? '+' : '' }}{{ $scDev }}%</span>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="border rounded-3 p-2 text-center bg-light">
            <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 0.68rem;">Due Date Proyek</small>
            <span class="fw-bold fs-6">{{ $scFmtDate($sc['due']) }}</span>
        </div>
    </div>
</div>

<div class="alert alert-{{ $sc['status_type'] }} py-2 small fw-semibold">{{ $sc['status_text'] }}</div>

@if(!$sc['has_history'])
    <div class="alert alert-warning py-2 small">
        Riwayat progress harian belum ada, jadi garis aktual hanya menampilkan nilai hari ini.
        Garis ini terisi seiring progress diperbarui, atau jalankan <code>php artisan scurve:backfill</code> untuk mengisinya dari Rekam Jejak.
    </div>
@endif

<div class="d-flex justify-content-end align-items-center gap-2 mb-2">
    <span class="small text-muted fw-semibold">Tampilan waktu</span>
    <div class="dropdown">
        <button class="btn btn-sm btn-outline-success dropdown-toggle fw-semibold" type="button" id="{{ $canvasId }}Btn" data-bs-toggle="dropdown" aria-expanded="false" style="min-width: 130px; text-align: left;">Per Hari</button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="{{ $canvasId }}Btn">
            <li><button type="button" class="dropdown-item" data-sc-mode="day">Per Hari</button></li>
            <li><button type="button" class="dropdown-item" data-sc-mode="week">Per Minggu</button></li>
        </ul>
    </div>
</div>
<div style="position: relative; height: 340px;">
    <canvas id="{{ $canvasId }}"></canvas>
</div>
<div class="small text-muted mt-2">
    Garis rencana disusun dari <strong>Due Date</strong> dan <strong>Planning (%)</strong> tiap divisi (mulai {{ $scFmtDate($sc['start']) }}).
    Garis aktual bisa selesai sebelum due date atau melewatinya jika terlambat, dan tidak pernah turun
    (kalau persen diturunkan, tanggal-tanggal sebelumnya mengikuti nilai tertinggi yang sekarang).
    Segitiga oranye menandai <strong>Remark Problem</strong>.
</div>

@if(count($sc['remarks']) > 0)
    <div class="mt-3">
        <div class="fw-bold small text-uppercase mb-1" style="color: #92400e;"><i class="bi bi-exclamation-triangle-fill me-1"></i> Remark Problem</div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 0.82rem;">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 44px;">No</th>
                        <th class="text-center" style="width: 110px;">Tanggal</th>
                        <th>Remarks Problem</th>
                        <th class="text-center" style="width: 120px;">Planning Total</th>
                        <th class="text-center" style="width: 100px;">Aktual</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sc['remarks'] as $remark)
                        <tr>
                            <td class="text-center fw-bold">{{ $remark['no'] }}</td>
                            <td class="text-center">{{ $scFmtDate($remark['date']) }}</td>
                            <td>{{ $remark['text'] }}</td>
                            <td class="text-center">{{ $remark['plan'] !== null ? $remark['plan'] . '%' : '-' }}</td>
                            <td class="text-center">{{ $remark['actual'] !== null ? $remark['actual'] . '%' : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<details class="mt-3">
    <summary class="text-success fw-bold" style="cursor: pointer;">Target per divisi yang dipakai untuk kurva rencana</summary>
    <div class="table-responsive mt-2">
        <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 0.82rem;">
            <thead class="table-light">
                <tr>
                    <th>Divisi</th>
                    <th class="text-center">Bobot</th>
                    <th class="text-center">Due Date</th>
                    <th class="text-center">Planning (%)</th>
                    <th class="text-center">Rencana Hari Ini (%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sc['divisions'] as $division)
                    <tr>
                        <td>{{ $division['label'] }}</td>
                        <td class="text-center">{{ rtrim(rtrim(number_format($division['weight'], 2), '0'), '.') }}%</td>
                        <td class="text-center">{{ $division['due'] }}</td>
                        <td class="text-center">{{ $division['plan'] }}%</td>
                        <td class="text-center">{{ $division['plan_today'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</details>

@once
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endonce

<script>
    (function () {
        var sc = @json($sc);
        var el = document.getElementById('{{ $canvasId }}');
        if (!el || typeof Chart === 'undefined') { return; }

        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        function fmt(iso) {
            var p = iso.split('-');
            return p[2] + ' ' + months[parseInt(p[1], 10) - 1] + ' ' + p[0].slice(2);
        }

        function isoToDate(iso) { var p = iso.split('-'); return new Date(Date.UTC(+p[0], +p[1] - 1, +p[2])); }
        function dateToIso(d) { return d.toISOString().slice(0, 10); }
        function mondayOf(iso) {
            var d = isoToDate(iso), dow = (d.getUTCDay() + 6) % 7; // Senin = 0
            d.setUTCDate(d.getUTCDate() - dow);
            return dateToIso(d);
        }

        // Sumbu x = urutan hari (0 .. n-1) pada skala linear, jadi setiap titik & penanda berada tepat di tanggalnya.
        // Per hari: satu titik tiap hari. Per minggu: satu titik tiap akhir minggu (Senin-Minggu), dan garis Aktual
        // berakhir tepat di hari ini (bukan di batas minggu).
        function buildView(mode) {
            var n = sc.labels.length, lastIdx = n - 1, i;
            var ticks = [], plan = [], actual = [];
            // garis Rencana (Plan) hanya sampai Due Date proyek
            var dueIdx = sc.due ? sc.labels.indexOf(sc.due) : -1;
            var planMax = dueIdx >= 0 ? dueIdx : lastIdx;
            var firstNonNull = function (arr) { for (var k = 0; k < arr.length; k++) { if (arr[k] !== null && arr[k] !== undefined) { return k; } } return -1; };
            var has = function (arr, k) { return arr[k] !== null && arr[k] !== undefined; };

            if (mode === 'week') {
                var buckets = [], map = {};
                for (i = 0; i < n; i++) {
                    var key = mondayOf(sc.labels[i]);
                    if (map[key] === undefined) { map[key] = buckets.length; buckets.push({ first: i, last: i }); }
                    buckets[map[key]].last = i;
                }
                var addPt = function (arr, x, y) { if (y === null || y === undefined) { return; } if (arr.length && arr[arr.length - 1].x === x) { arr[arr.length - 1].y = y; return; } arr.push({ x: x, y: y }); };
                addPt(plan, 0, sc.plan[0]);
                addPt(actual, 0, sc.actual[0]);
                buckets.forEach(function (b) {
                    ticks.push(b.first);
                    if (b.first <= planMax) { addPt(plan, Math.min(b.last, planMax), sc.plan[Math.min(b.last, planMax)]); }
                    var lastAct = -1;
                    for (var k = b.last; k >= b.first; k--) { if (has(sc.actual, k)) { lastAct = k; break; } }
                    if (lastAct >= 0) { addPt(actual, lastAct, sc.actual[lastAct]); }
                });
                // terlalu banyak minggu -> label sumbu dijarangkan
                var step = Math.ceil(ticks.length / 14);
                if (step > 1) { ticks = ticks.filter(function (t, k) { return k % step === 0; }); }
            } else {
                for (i = 0; i < n; i++) {
                    if (i <= planMax && has(sc.plan, i)) { plan.push({ x: i, y: sc.plan[i] }); }
                    if (has(sc.actual, i)) { actual.push({ x: i, y: sc.actual[i] }); }
                }
            }

            return {
                mode: mode, n: n, ticks: ticks, plan: plan, actual: actual,
                title: function (x) {
                    var i = Math.max(0, Math.min(lastIdx, Math.round(x)));
                    if (mode !== 'week') { return fmt(sc.labels[i]); }
                    var mon = mondayOf(sc.labels[i]), a = i, z = i;
                    while (a > 0 && mondayOf(sc.labels[a - 1]) === mon) { a--; }
                    while (z < lastIdx && mondayOf(sc.labels[z + 1]) === mon) { z++; }
                    return 'Minggu ' + fmt(sc.labels[a]) + ' - ' + fmt(sc.labels[z]) + ' (titik: ' + fmt(sc.labels[i]) + ')';
                },
                posOfIso: function (iso) { var i = iso ? sc.labels.indexOf(iso) : -1; return i; }
            };
        }

        var markers = [
            { iso: sc.due,   color: '#dc2626', label: 'Due Date' },
            { iso: sc.today, color: '#475569', label: 'Hari ini' }
        ];
        var view = null, chart = null;

        var markerPlugin = {
            id: 'scurveMarkers',
            afterDatasetsDraw: function (c) {
                if (!view) { return; }
                var ctx = c.ctx, area = c.chartArea, x = c.scales.x;
                var dark = window.vsmIsDark && window.vsmIsDark();
                var list = [];
                markers.forEach(function (m) {
                    var pos = view.posOfIso(m.iso);
                    if (pos < 0) { return; }
                    list.push({ m: m, px: x.getPixelForValue(pos), text: m.label + ' ' + shortDate(m.iso) });
                });
                list.sort(function (a, b) { return a.px - b.px; });

                // label disusun bertingkat bila berdekatan; rata kanan/kiri bila hampir menyentuh tepi kanvas
                ctx.save();
                ctx.font = 'bold 11px sans-serif';
                var placed = [];
                list.forEach(function (it) {
                    var w = ctx.measureText(it.text).width, row = 0;
                    var side = 'center', left = it.px - w / 2, right = it.px + w / 2;
                    if (right > c.width - 4) { side = 'right'; left = it.px - w; right = it.px; }
                    else if (left < 4) { side = 'left'; left = it.px; right = it.px + w; }
                    placed.forEach(function (p) { if (left < p.right + 6 && right > p.left - 6 && row === p.row) { row++; } });
                    placed.push({ left: left, right: right, row: row });
                    it.side = side; it.row = row; it.w = w;
                });
                list.forEach(function (it) {
                    var mc = (it.m.iso === sc.today && dark) ? '#cbd5e1' : it.m.color;
                    ctx.strokeStyle = mc;
                    ctx.lineWidth = 1.5;
                    ctx.setLineDash([5, 4]);
                    ctx.beginPath();
                    ctx.moveTo(it.px, area.top - it.row * 14 - 2);
                    ctx.lineTo(it.px, area.bottom);
                    ctx.stroke();
                    ctx.setLineDash([]);
                    ctx.fillStyle = mc;
                    ctx.textAlign = it.side;
                    ctx.fillText(it.text, it.px, area.top - it.row * 14 - 5);
                });
                ctx.restore();
            }
        };

        function shortDate(iso) { var p = iso.split('-'); return parseInt(p[2], 10) + ' ' + months[parseInt(p[1], 10) - 1]; }

        function render(mode) {
            view = buildView(mode);

            // Remark Problem: segitiga tepat di tanggal masalah (pada garis aktual, atau garis rencana kalau belum ada aktual)
            var remarkData = [], remarksByIdx = {};
            (sc.remarks || []).forEach(function (r) {
                if (r.index === null || r.index === undefined) { return; }
                var v = (r.actual !== null && r.actual !== undefined) ? r.actual : sc.plan[r.index];
                remarkData.push({ x: r.index, y: v });
                (remarksByIdx[r.index] = remarksByIdx[r.index] || []).push(r);
            });

            var isWeek = mode === 'week';
            if (chart) { chart.destroy(); }
            chart = new Chart(el, {
                type: 'line',
                data: {
                    datasets: [
                        { label: 'Rencana (Plan)', data: view.plan, borderColor: '#2563eb', borderDash: [6, 4], borderWidth: 2, pointRadius: isWeek ? 3 : 0, pointBackgroundColor: '#2563eb', fill: false },
                        { label: 'Aktual', data: view.actual, borderColor: '#16a34a', backgroundColor: 'rgba(22, 163, 74, 0.12)', borderWidth: 3, pointRadius: isWeek ? 3 : 0, pointBackgroundColor: '#16a34a', fill: true },
                        { label: 'Remark Problem', data: remarkData, showLine: false, pointStyle: 'triangle', pointRadius: 8, pointHoverRadius: 10, backgroundColor: '#f59e0b', borderColor: '#b45309', borderWidth: 1.5, fill: false }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'x', intersect: false },
                    layout: { padding: { top: 34, right: 6 } },
                    scales: {
                        y: { min: 0, max: 100, ticks: { callback: function (v) { return v + '%'; } } },
                        x: {
                            type: 'linear',
                            min: -0.5,
                            max: view.n - 0.5,
                            grid: { color: 'rgba(148, 163, 184, 0.25)' },
                            afterBuildTicks: function (axis) {
                                if (isWeek) { axis.ticks = view.ticks.map(function (v) { return { value: v }; }); }
                            },
                            ticks: {
                                maxTicksLimit: isWeek ? 30 : 12,
                                maxRotation: 0,
                                callback: function (v) {
                                    var i = Math.round(v);
                                    return (i >= 0 && i < view.n) ? fmt(sc.labels[i]) : '';
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                title: function (items) { return items.length ? view.title(items[0].parsed.x) : ''; },
                                label: function (c) {
                                    if (c.dataset.label === 'Remark Problem') {
                                        return (remarksByIdx[c.raw.x] || []).map(function (r) {
                                            return 'Remark ' + r.no + ': ' + r.text + (r.plan !== null ? ' (Plan ' + r.plan + '%)' : '');
                                        });
                                    }
                                    return c.dataset.label + ' (' + fmt(sc.labels[Math.round(c.parsed.x)]) + '): ' + (c.parsed.y === null ? '-' : c.parsed.y.toFixed(1) + '%');
                                }
                            }
                        }
                    }
                },
                plugins: [markerPlugin]
            });

            var btn = document.getElementById('{{ $canvasId }}Btn');
            if (btn) { btn.textContent = isWeek ? 'Per Minggu' : 'Per Hari'; }
        }

        // Pilihan waktu diingat (kalau penyimpanan browser tidak tersedia, tetap jalan)
        var KEY = 'vsmScurveMode', mode = 'day';
        try { mode = localStorage.getItem(KEY) === 'week' ? 'week' : 'day'; } catch (e) { mode = 'day'; }
        render(mode);

        document.querySelectorAll('#{{ $canvasId }}Btn + .dropdown-menu [data-sc-mode]').forEach(function (item) {
            item.addEventListener('click', function () {
                var m = this.getAttribute('data-sc-mode') === 'week' ? 'week' : 'day';
                try { localStorage.setItem(KEY, m); } catch (e) {}
                render(m);
            });
        });
    })();
</script>