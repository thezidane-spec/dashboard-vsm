{{--
    POPUP DETAIL MATERIAL (Raw Material & Vendor Raw Material) - Divisi Procurement
    Dipanggil dari edit.blade.php (di luar form utama). Semua aksi memakai AJAX (fetch),
    jadi tidak ikut terkirim bersama form update proyek.
--}}
@php
    $mmCanEdit = isset($canEditMaterial)
        ? (bool) $canEditMaterial
        : in_array(strtolower((string) (auth()->user()->role ?? '')), ['admin', 'ppic', 'procurement'], true);

    $mmConfig = [
        'canEdit'     => $mmCanEdit,
        'csrf'        => csrf_token(),
        'projectName' => $project->project_name,
        'urls'        => [
            'index'          => route('projects.materials.index', $project->id),
            'storeItem'      => route('projects.materials.items.store', $project->id),
            'updateItem'     => route('projects.materials.items.update', [$project->id, '__ID__']),
            'destroyItem'    => route('projects.materials.items.destroy', [$project->id, '__ID__']),
            'storeArrival'   => route('projects.materials.arrivals.store', $project->id),
            'updateArrival'  => route('projects.materials.arrivals.update', [$project->id, '__ID__']),
            'destroyArrival' => route('projects.materials.arrivals.destroy', [$project->id, '__ID__']),
            'scanPdf'        => route('projects.materials.scan', $project->id),
            'importItems'    => route('projects.materials.import', $project->id),
            'applyRevision'  => route('projects.materials.revision.apply', $project->id),
            'radarSources'   => route('projects.materials.radar.sources', $project->id),
            'radarImport'    => route('projects.materials.radar.import', $project->id),
            'requiredDate'   => route('projects.materials.items.required-date', [$project->id, '__ID__']),
            'millCert'       => route('projects.materials.items.mill-cert', [$project->id, '__ID__']),
        ],
    ];
@endphp

@include('projects.partials.material-report-styles')

<style>
    #modalMaterial .modal-content { border-radius: 16px; border: 1.5px solid #cbd5e1; }
    #modalMaterial .modal-header { border-bottom: 1.5px solid #e2e8f0; background: #f8fafc; border-radius: 16px 16px 0 0; }
    #modalMaterial .modal-body { background: #fff; }

    .mm-nav-btn {
        width: 36px; height: 36px; border: 1.5px solid #cbd5e1; border-radius: 10px; background: #fff;
        display: inline-flex; align-items: center; justify-content: center; color: #0f172a; padding: 0;
    }
    .mm-nav-btn:hover:not(:disabled) { background: #ecfdf5; border-color: #059669; color: #047857; }
    .mm-nav-btn:disabled { opacity: 0.3; cursor: not-allowed; }

    .mm-panel { border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 16px; background: #fff; }
    .mm-panel-title { font-weight: 800; color: #047857; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 12px; }
    .mm-subpanel { border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 12px; background: #f8fafc; }

    .mm-btn-green {
        background: #047857; border: 0; color: #fff; font-weight: 700; letter-spacing: 0.4px;
        border-radius: 10px; padding: 8px 24px; text-transform: uppercase; font-size: 0.85rem;
    }
    .mm-btn-green:hover { background: #065f46; color: #fff; }
    .mm-btn-green:disabled { opacity: 0.6; }

    .mm-list-table { font-size: 0.78rem; }
    .mm-list-table th { background: #f1f5f9; text-transform: uppercase; font-size: 0.66rem; color: #334155; white-space: nowrap; }
    .mm-list-scroll { max-height: 260px; overflow-y: auto; }
    #modalMaterial fieldset[disabled] .mm-panel { background: #f8fafc; }
</style>

<div class="modal fade no-print" id="modalMaterial" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="mm-nav-btn" id="mmPrev" aria-label="Halaman sebelumnya" title="Input Material"><i class="bi bi-chevron-left"></i></button>
                    <button type="button" class="mm-nav-btn" id="mmNext" aria-label="Halaman berikutnya" title="Laporan Material"><i class="bi bi-chevron-right"></i></button>
                    <h6 class="modal-title fw-bold m-0 ms-1" id="mmTitle">{{ $project->project_name }} - Material</h6>
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <a href="{{ route('projects.materials.export.excel', $project->id) }}" class="btn btn-sm mm-btn-green d-none" id="mmExportExcel">Export Excel</a>
                    <a href="{{ route('projects.materials.print', $project->id) }}" target="_blank" rel="noopener" class="btn btn-sm mm-btn-green d-none" id="mmExportPdf">Export PDF</a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
            </div>

            <div class="modal-body">
                <div id="mmAlert"></div>

                {{-- =============== HALAMAN 1: INPUT MATERIAL =============== --}}
                <div id="mmPage1">
                    @unless($mmCanEdit)
                        <div class="alert alert-secondary py-2 small mb-3">
                            <i class="bi bi-eye me-1"></i> Anda hanya dapat melihat data. Pengisian material dilakukan oleh Procurement, PPIC, atau Admin.
                        </div>
                    @endunless

                    <fieldset {{ $mmCanEdit ? '' : 'disabled' }}>
                        <div class="row g-3">

                            {{-- KIRI: catat kedatangan barang --}}
                            <div class="col-lg-4">
                                <div class="mm-panel h-100 d-flex flex-column">
                                    <div class="mm-panel-title"><i class="bi bi-truck me-1"></i> <span id="mmArrTitle">Catat Kedatangan</span> <span class="badge bg-warning-subtle text-warning-emphasis ms-1 d-none" id="mmArrEditBadge">Mode ubah</span></div>

                                    <label class="form-label-custom" for="mmArrDate">Date <span class="text-muted fw-normal text-lowercase">(tanggal sampai)</span></label>
                                    <input type="date" id="mmArrDate" class="form-control-custom mb-3">

                                    <div class="mm-subpanel mb-3">
                                        <label class="form-label-custom" for="mmArrItem">Material/Vendor Material</label>
                                        <select id="mmArrItem" class="form-select form-select-sm mb-1">
                                            <option value="">— Pilih material —</option>
                                        </select>
                                        <div class="small text-muted mb-2" id="mmArrHint" style="min-height: 18px;"></div>

                                        <label class="form-label-custom" for="mmArrQty">Material Loading <span class="text-muted fw-normal text-lowercase">(jumlah datang)</span></label>
                                        <input type="number" id="mmArrQty" class="form-control-custom" min="0" step="any" placeholder="0">
                                    </div>

                                    <label class="form-label-custom" for="mmArrRemarks">Remarks</label>
                                    <textarea id="mmArrRemarks" class="form-control-custom mb-3" rows="3" maxlength="500"></textarea>

                                    <div class="mt-auto d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-outline-secondary btn-sm d-none" id="mmArrCancel">Batal Ubah</button>
                                        <button type="button" class="btn mm-btn-green" id="mmArrSave">Update</button>
                                    </div>
                                </div>
                            </div>

                            {{-- KANAN: daftarkan material yang diminta --}}
                            <div class="col-lg-8">
                                <div class="mm-panel h-100 d-flex flex-column">
                                    <div class="mm-panel-title"><i class="bi bi-box-seam me-1"></i> Input Material <span id="mmEditBadge" class="badge bg-warning text-dark ms-1 d-none">Mode Ubah</span></div>

                                    {{-- SCAN OTOMATIS DARI PDF --}}
                                    <div class="p-2 mb-3 rounded-3 border bg-light">
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                                            <span class="small fw-bold">Scan dari PDF</span>
                                            <input type="file" id="mmPdfFile" accept="application/pdf,.pdf" class="form-control form-control-sm" style="max-width: 260px;">
                                            <button type="button" class="btn btn-sm btn-outline-success fw-bold" id="mmPdfScan">Scan</button>
                                            <span class="small text-muted">PDF bertext atau hasil scan (BOM berbentuk tabel), maks 10 MB; hasil scan dibaca dengan OCR dan butuh waktu +- 15-30 detik. Hasil diperiksa dulu sebelum diimpor (kategori &amp; tanggal diperlukan mengikuti form di bawah).</span>
                                        </div>
                                        <div id="mmScanBox" class="d-none mt-2">
                                            <div class="table-responsive" style="max-height: 260px; overflow-y: auto;">
                                                <table class="table table-sm table-bordered align-middle mb-2" style="font-size: .78rem;">
                                                    <thead class="table-light"><tr><th style="width:30px"><input type="checkbox" id="mmScanAll" checked></th><th>Nama</th><th>Spec</th><th style="width:80px">UOM</th><th style="width:90px">Qty</th><th style="width:90px">Weight</th></tr></thead>
                                                    <tbody id="mmScanBody"></tbody>
                                                </table>
                                            </div>
                                            <div class="d-flex justify-content-end gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="mmScanCancel">Tutup</button>
                                                <button type="button" class="btn btn-sm mm-btn-green" id="mmScanImport">Impor yang dicentang</button>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- AMBIL DARI MATERIAL RADAR LIST (data yang sama dengan halaman Material Radar List) --}}
                                    <div class="p-2 mb-3 rounded-3 border" style="background:#eef6ff">
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <i class="bi bi-table text-primary"></i>
                                            <span class="small fw-bold">Ambil dari Material Radar List</span>
                                            <button type="button" class="btn btn-sm btn-outline-primary fw-bold" id="mmRadarOpen">Pilih dari Radar</button>
                                            <span class="small text-muted">Nama, spec, qty, tanggal diperlukan dan kedatangan diambil dari Material Radar List; setelahnya kedua halaman saling tersinkron.</span>
                                        </div>
                                        <div id="mmRadarBox" class="d-none mt-2">
                                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                                <label class="small fw-bold mb-0" for="mmRadarSelect">Project</label>
                                                <select id="mmRadarSelect" class="form-select form-select-sm" style="max-width: 360px;"></select>
                                                <span class="small text-muted">Kategori: mengikuti pilihan Kategori di form bawah.</span>
                                            </div>
                                            <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                                                <table class="table table-sm table-bordered align-middle mb-2 bg-white" style="font-size: .78rem;">
                                                    <thead class="table-light"><tr><th style="width:30px"><input type="checkbox" id="mmRadarAll"></th><th>Material</th><th>Spec</th><th class="text-end">Qty</th><th style="width:80px">UOM</th><th>Diperlukan</th><th class="text-end">Datang</th></tr></thead>
                                                    <tbody id="mmRadarBody"></tbody>
                                                </table>
                                            </div>
                                            <div class="d-flex justify-content-end gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="mmRadarClose">Tutup</button>
                                                <button type="button" class="btn btn-sm mm-btn-green" id="mmRadarImport">Ambil yang dicentang</button>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- REVISI BOM: perbandingan hasil scan dengan material yang sudah ada --}}
                                    <div id="mmRevBox" class="d-none mb-3 p-2 rounded-3 border">
                                        <div class="fw-bold small mb-1"><i class="bi bi-arrow-repeat me-1"></i> Revisi BOM &mdash; perbandingan dengan material yang sudah ada</div>
                                        <div class="d-flex flex-wrap gap-2 mb-2" style="font-size:.72rem;">
                                            <span class="badge border text-dark" style="background:#fff">Putih: sama <b id="mmRevCw">0</b></span>
                                            <span class="badge text-dark" style="background:#fff3cd;border:1px solid #ffe69c">Kuning: material baru / qty bertambah <b id="mmRevCy">0</b></span>
                                            <span class="badge text-dark" style="background:#cfe2ff;border:1px solid #9ec5fe">Biru: qty berubah <b id="mmRevCb">0</b></span>
                                            <span class="badge text-dark" style="background:#f8d7da;border:1px solid #f1aeb5">Merah: dikurangi / kriteria berubah <b id="mmRevCr">0</b></span>
                                        </div>
                                        <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                                            <table class="table table-sm table-bordered align-middle mb-2" style="font-size: .78rem;">
                                                <thead class="table-light"><tr><th style="width:30px"></th><th style="width:170px">Status</th><th>Nama</th><th>Spec</th><th style="width:70px">UOM</th><th style="width:90px">Qty baru</th></tr></thead>
                                                <tbody id="mmRevBody"></tbody>
                                            </table>
                                        </div>
                                        <div class="small text-muted mb-2">Baris putih tidak diubah. Baris merah &ldquo;tidak ada di BOM baru&rdquo; tidak dicentang otomatis (centang bila ingin dihapus). Menghapus material ikut menghapus catatan kedatangannya. Periksa hasil OCR sebelum menerapkan.</div>
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="mmRevCancel">Tutup</button>
                                            <button type="button" class="btn btn-sm mm-btn-green" id="mmRevApply">Terapkan yang dicentang</button>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label-custom" for="mmItemCategory">Kategori</label>
                                            <select id="mmItemCategory" class="form-select form-select-sm mb-3">
                                                <option value="raw_material">Raw Material</option>
                                                <option value="vendor_material">Vendor Raw Material</option>
                                            </select>

                                            <label class="form-label-custom" for="mmItemName">Nama Material</label>
                                            <input type="text" id="mmItemName" class="form-control-custom" maxlength="150" placeholder="cth: Flange Manhole">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label-custom" for="mmItemUom">UOM</label>
                                            <input type="text" id="mmItemUom" class="form-control-custom" list="mmUomList" maxlength="20" placeholder="Pcs / Btg / Lbr / Kg">
                                            <datalist id="mmUomList">
                                                <option value="Pcs"></option>
                                                <option value="Btg"></option>
                                                <option value="Lbr"></option>
                                                <option value="Set"></option>
                                                <option value="Kg"></option>
                                                <option value="M"></option>
                                                <option value="Ltr"></option>
                                            </datalist>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label-custom" for="mmItemSpec">Spec</label>
                                            <input type="text" id="mmItemSpec" class="form-control-custom" maxlength="150" placeholder="cth: SS 400">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label-custom" for="mmItemWeight">Weight <span class="text-muted fw-normal text-lowercase">(per UOM)</span></label>
                                            <input type="number" id="mmItemWeight" class="form-control-custom" min="0" step="any" placeholder="0">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label-custom" for="mmItemQty">Quantity</label>
                                            <input type="number" id="mmItemQty" class="form-control-custom" min="0" step="any" placeholder="0">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label-custom" for="mmItemPurchase">Tanggal Diperlukan</label>
                                            <input type="date" id="mmItemPurchase" class="form-control-custom">
                                        </div>
                                    </div>

                                    <div class="mt-auto pt-3 d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-outline-secondary btn-sm d-none" id="mmItemCancel">Batal Ubah</button>
                                        <button type="button" class="btn mm-btn-green" id="mmItemSave">Tambah</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    {{-- DAFTAR DATA TERCATAT --}}
                    <div class="row g-3 mt-1">
                        <div class="col-lg-7">
                            <div class="mm-panel">
                                <div class="mm-panel-title"><i class="bi bi-list-check me-1"></i> Daftar Material</div>
                                <div class="mm-list-scroll table-responsive">
                                    <table class="table table-sm table-bordered align-middle mb-0 mm-list-table">
                                        <thead>
                                            <tr>
                                                <th>Kategori</th>
                                                <th>Material</th>
                                                <th>Spec</th>
                                                <th style="width:128px">Tgl Diperlukan</th>
                                                <th class="text-end">Qty</th>
                                                <th class="text-end">Actual</th>
                                                <th class="text-end">Deviasi</th>
                                                <th style="width: 76px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="mmItemsBody">
                                            <tr><td colspan="8" class="text-center text-muted py-3">Memuat data...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="mm-panel">
                                <div class="mm-panel-title"><i class="bi bi-clock-history me-1"></i> Riwayat Kedatangan</div>
                                <div class="mm-list-scroll table-responsive">
                                    <table class="table table-sm table-bordered align-middle mb-0 mm-list-table">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Material</th>
                                                <th class="text-end">Jumlah</th>
                                                <th>Remarks</th>
                                                <th style="width: 76px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="mmArrivalsBody">
                                            <tr><td colspan="5" class="text-center text-muted py-3">Memuat data...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- =============== HALAMAN 2: LAPORAN MATERIAL =============== --}}
                <div id="mmPage2" class="d-none">
                    <div id="mmReport"></div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    (function () {
        'use strict';

        const cfg = @json($mmConfig);

        const modalEl = document.getElementById('modalMaterial');
        if (!modalEl) { return; }

        const $ = function (id) { return document.getElementById(id); };

        const state = { items: [], arrivals: [] };
        let editingId = null;
        let editingArrivalId = null;

        // ---------- Util ----------
        function esc(value) {
            return String(value === null || value === undefined ? '' : value).replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }

        function round2(n) {
            const v = Math.round(Number(n) * 100) / 100;
            return v === 0 ? 0 : v; // hindari -0
        }

        function fmt(n) {
            return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(round2(n));
        }

        function todayStr() {
            const d = new Date();
            const p = function (x) { return String(x).padStart(2, '0'); };
            return d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate());
        }

        function flash(type, message) {
            $('mmAlert').innerHTML =
                '<div class="alert alert-' + type + ' alert-dismissible py-2 small mb-3" role="alert">'
                + esc(message)
                + '<button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Tutup"></button></div>';
        }

        async function api(url, method, body) {
            const options = {
                method: method,
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': cfg.csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };

            if (body !== undefined) {
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(body);
            }

            const res = await fetch(url, options);
            let data = null;
            try { data = await res.json(); } catch (e) { data = null; }

            if (!res.ok) {
                let message = (data && data.message) ? data.message : 'Terjadi kesalahan (kode ' + res.status + ').';
                if (data && data.errors) {
                    const first = Object.values(data.errors)[0];
                    if (first && first.length) { message = first[0]; }
                }
                throw new Error(message);
            }

            return data;
        }

        // ---------- Render ----------
        function render(data) {
            state.items = data.items || [];
            state.arrivals = data.arrivals || [];

            renderSelect();
            renderItems();
            renderArrivals();
            $('mmReport').innerHTML = data.report_html || '';
            // Checkbox Mill Certificate (hanya bisa diubah oleh yang punya hak edit Procurement)
            $('mmReport').querySelectorAll('.mr-mill-check').forEach(function (box) {
                if (!cfg.canEdit) { box.disabled = true; return; }
                box.addEventListener('change', async function () {
                    box.disabled = true;
                    try {
                        render(await api(cfg.urls.millCert.replace('__ID__', box.getAttribute('data-item-id')), 'PATCH', { mill_cert: box.checked }));
                    } catch (e) {
                        box.checked = !box.checked;
                        box.disabled = false;
                        flash('danger', e.message);
                    }
                });
            });

            const badge = document.getElementById('material-summary-badge');
            if (badge && data.summary) { badge.textContent = data.summary.text; }

            // Persen Raw Material di form ikut otomatis (dari persen barang datang)
            const pctInput = document.getElementById('task_main_material');
            if (pctInput && data.summary && typeof data.summary.progress !== 'undefined') {
                pctInput.value = data.summary.progress;
            }
        }

        function renderSelect() {
            const select = $('mmArrItem');
            const current = select.value;
            let html = '<option value="">— Pilih material —</option>';

            [['raw_material', 'Raw Material'], ['vendor_material', 'Vendor Raw Material']].forEach(function (group) {
                const list = state.items.filter(function (i) { return i.category === group[0]; });
                if (!list.length) { return; }

                html += '<optgroup label="' + esc(group[1]) + '">';
                list.forEach(function (i) {
                    html += '<option value="' + i.id + '">' + esc(i.name + (i.spec ? ' — ' + i.spec : '')) + '</option>';
                });
                html += '</optgroup>';
            });

            select.innerHTML = html;
            if (current && state.items.some(function (i) { return String(i.id) === current; })) {
                select.value = current;
            }
            updateHint();
        }

        function updateHint() {
            const id = $('mmArrItem').value;
            const item = state.items.find(function (i) { return String(i.id) === id; });
            const el = $('mmArrHint');

            if (!item) { el.textContent = ''; return; }

            const remaining = round2(item.quantity - item.actual);
            let tail;
            if (remaining > 0) {
                tail = 'Sisa <b>' + fmt(remaining) + '</b>';
            } else if (remaining < 0) {
                tail = 'Lebih <b>' + fmt(-remaining) + '</b>';
            } else {
                tail = '<b class="text-success">Lengkap</b>';
            }

            el.innerHTML = 'Diminta <b>' + fmt(item.quantity) + '</b> ' + esc(item.uom)
                + ' · Sudah datang <b>' + fmt(item.actual) + '</b> · ' + tail;
        }

        function renderItems() {
            const body = $('mmItemsBody');

            if (!state.items.length) {
                body.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">Belum ada material terdaftar.</td></tr>';
                return;
            }

            body.innerHTML = state.items.map(function (i) {
                const dev = round2(i.deviation);
                const devClass = dev === 0 ? 'text-success' : (dev > 0 ? 'text-warning-emphasis' : 'text-danger');
                const catClass = i.category === 'vendor_material' ? 'bg-warning-subtle text-warning-emphasis' : 'bg-success-subtle text-success';

                const actions = cfg.canEdit
                    ? '<button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 me-1" data-mm-edit="' + i.id + '" title="Ubah"><i class="bi bi-pencil"></i></button>'
                      + '<button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" data-mm-del-item="' + i.id + '" title="Hapus"><i class="bi bi-trash"></i></button>'
                    : '';

                return '<tr>'
                    + '<td><span class="badge ' + catClass + '">' + esc(i.category_label) + '</span></td>'
                    + '<td class="fw-semibold">' + esc(i.name) + (i.radar_item_id ? ' <i class="bi bi-link-45deg text-primary" title="Tertaut dengan Material Radar List"></i>' : '') + '</td>'
                    + '<td>' + esc(i.spec || '-') + '</td>'
                    + '<td>' + (cfg.canEdit
                        ? '<input type="date" class="form-control form-control-sm py-0 px-1" style="font-size:.75rem" data-mm-req-date="' + i.id + '" value="' + esc(i.purchase_date || '') + '" title="Tanggal diperlukan (bisa langsung diganti)">'
                        : esc(i.purchase_date || '-')) + '</td>'
                    + '<td class="text-end text-nowrap">' + fmt(i.quantity) + ' ' + esc(i.uom) + '</td>'
                    + '<td class="text-end">' + fmt(i.actual) + '</td>'
                    + '<td class="text-end fw-bold ' + devClass + '">' + fmt(dev) + '</td>'
                    + '<td class="text-center text-nowrap">' + actions + '</td>'
                    + '</tr>';
            }).join('');
        }

        function renderArrivals() {
            const body = $('mmArrivalsBody');

            if (!state.arrivals.length) {
                body.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Belum ada barang yang tercatat sampai.</td></tr>';
                return;
            }

            body.innerHTML = state.arrivals.map(function (a) {
                const action = cfg.canEdit
                    ? '<button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 me-1" data-mm-edit-arrival="' + a.id + '" title="Ubah"><i class="bi bi-pencil"></i></button>'
                      + '<button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" data-mm-del-arrival="' + a.id + '" title="Hapus"><i class="bi bi-trash"></i></button>'
                    : '';

                return '<tr>'
                    + '<td class="text-nowrap">' + esc(a.arrival_date_label) + '</td>'
                    + '<td>' + esc(a.item_name) + '</td>'
                    + '<td class="text-end text-nowrap">' + fmt(a.quantity) + ' ' + esc(a.uom) + '</td>'
                    + '<td>' + esc(a.remarks || '') + '</td>'
                    + '<td class="text-center">' + action + '</td>'
                    + '</tr>';
            }).join('');
        }

        // ---------- Halaman (‹ ›) ----------
        function setPage(n) {
            $('mmAlert').innerHTML = '';
            $('mmPage1').classList.toggle('d-none', n !== 1);
            $('mmPage2').classList.toggle('d-none', n !== 2);
            $('mmTitle').textContent = cfg.projectName + (n === 1 ? ' - Material' : ' - Main Material');
            $('mmExportExcel').classList.toggle('d-none', n !== 2);
            $('mmExportPdf').classList.toggle('d-none', n !== 2);
            $('mmPrev').disabled = (n === 1);
            $('mmNext').disabled = (n === 2);
        }

        // ---------- Form: material ----------
        function resetItemForm(keepCategory) {
            const category = $('mmItemCategory').value;

            editingId = null;
            ['mmItemName', 'mmItemSpec', 'mmItemUom', 'mmItemWeight', 'mmItemQty', 'mmItemPurchase'].forEach(function (id) {
                $(id).value = '';
            });
            $('mmItemCategory').value = keepCategory ? category : 'raw_material';

            $('mmItemSave').textContent = 'Tambah';
            $('mmItemCancel').classList.add('d-none');
            $('mmEditBadge').classList.add('d-none');
        }

        function startEditItem(id) {
            const item = state.items.find(function (i) { return String(i.id) === String(id); });
            if (!item) { return; }

            editingId = item.id;
            $('mmItemCategory').value = item.category;
            $('mmItemName').value = item.name || '';
            $('mmItemSpec').value = item.spec || '';
            $('mmItemUom').value = item.uom || '';
            $('mmItemWeight').value = (item.weight === null || item.weight === undefined) ? '' : item.weight;
            $('mmItemQty').value = item.quantity;
            $('mmItemPurchase').value = item.purchase_date || '';

            $('mmItemSave').textContent = 'Simpan Perubahan';
            $('mmItemCancel').classList.remove('d-none');
            $('mmEditBadge').classList.remove('d-none');
            $('mmItemName').focus();
        }

        async function saveItem() {
            const payload = {
                category: $('mmItemCategory').value,
                name: $('mmItemName').value.trim(),
                spec: $('mmItemSpec').value.trim(),
                uom: $('mmItemUom').value.trim(),
                weight: $('mmItemWeight').value,
                quantity: $('mmItemQty').value,
                purchase_date: $('mmItemPurchase').value
            };

            const button = $('mmItemSave');
            button.disabled = true;

            try {
                const data = editingId
                    ? await api(cfg.urls.updateItem.replace('__ID__', editingId), 'PUT', payload)
                    : await api(cfg.urls.storeItem, 'POST', payload);

                render(data);
                flash('success', data.message || 'Data material tersimpan.');
                resetItemForm(true);
            } catch (err) {
                flash('danger', err.message);
            } finally {
                button.disabled = false;
            }
        }

        // ---------- Form: kedatangan ----------
        function resetArrivalForm() {
            editingArrivalId = null;
            $('mmArrItem').value = '';
            $('mmArrQty').value = '';
            $('mmArrRemarks').value = '';
            $('mmArrSave').textContent = 'Update';
            $('mmArrCancel').classList.add('d-none');
            $('mmArrEditBadge').classList.add('d-none');
            $('mmArrTitle').textContent = 'Catat Kedatangan';
            updateHint();
        }

        function startEditArrival(id) {
            const a = state.arrivals.find(function (x) { return String(x.id) === String(id); });
            if (!a) { return; }

            editingArrivalId = a.id;
            $('mmArrDate').value = a.arrival_date || '';
            $('mmArrItem').value = String(a.item_id);
            $('mmArrQty').value = a.quantity;
            $('mmArrRemarks').value = a.remarks || '';
            $('mmArrSave').textContent = 'Simpan Perubahan';
            $('mmArrCancel').classList.remove('d-none');
            $('mmArrEditBadge').classList.remove('d-none');
            $('mmArrTitle').textContent = 'Ubah Kedatangan';
            updateHint();
            $('mmArrQty').focus();
        }

        async function saveArrival() {
            const payload = {
                material_item_id: $('mmArrItem').value,
                arrival_date: $('mmArrDate').value,
                quantity: $('mmArrQty').value,
                remarks: $('mmArrRemarks').value.trim()
            };

            const button = $('mmArrSave');
            button.disabled = true;

            try {
                const data = editingArrivalId
                    ? await api(cfg.urls.updateArrival.replace('__ID__', editingArrivalId), 'PUT', payload)
                    : await api(cfg.urls.storeArrival, 'POST', payload);

                render(data);
                flash('success', data.message || 'Kedatangan tersimpan.');

                // Tanggal dipertahankan (biasanya beberapa barang datang di hari yang sama)
                resetArrivalForm();
            } catch (err) {
                flash('danger', err.message);
            } finally {
                button.disabled = false;
            }
        }

        // ---------- Scan PDF ----------
        function scanRowHtml(item) {
            return '<tr><td><input type="checkbox" class="sc-on" checked></td>'
                + '<td><input type="text" class="form-control form-control-sm sc-name" maxlength="150" value="' + esc(item.name) + '">'
                + (item.note ? '<div class="text-muted" style="font-size:.68rem" title="Hanya info dari PDF, tidak disimpan">' + esc(item.note) + '</div>' : '') + '</td>'
                + '<td><input type="text" class="form-control form-control-sm sc-spec" maxlength="150" value="' + esc(item.spec) + '"></td>'
                + '<td><input type="text" class="form-control form-control-sm sc-uom" maxlength="20" value="' + esc(item.uom) + '"></td>'
                + '<td><input type="number" step="any" min="0" class="form-control form-control-sm sc-qty" value="' + esc(item.quantity) + '"></td>'
                + '<td><input type="number" step="any" min="0" class="form-control form-control-sm sc-weight" value="' + esc(item.weight) + '"></td></tr>';
        }

        async function scanPdf() {
            const file = $('mmPdfFile').files[0];
            if (!file) { flash('danger', 'Pilih file PDF terlebih dulu.'); return; }
            const button = $('mmPdfScan');
            button.disabled = true;
            button.textContent = 'Membaca (OCR bisa 30 dtk)...';
            try {
                const form = new FormData();
                form.append('pdf', file);
                form.append('category', $('mmItemCategory').value);
                const res = await fetch(cfg.urls.scanPdf, { method: 'POST', body: form, headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf, 'X-Requested-With': 'XMLHttpRequest' } });
                let data = null;
                try { data = await res.json(); } catch (e) { data = null; }
                if (!res.ok) {
                    let message = (data && data.message) ? data.message : 'Scan gagal (kode ' + res.status + ').';
                    if (data && data.errors) { const first = Object.values(data.errors)[0]; if (first && first.length) { message = first[0]; } }
                    throw new Error(message);
                }
                if (data.revision) {
                    showRevision(data.revision);
                    $('mmScanBox').classList.add('d-none');
                } else {
                    $('mmRevBox').classList.add('d-none');
                    $('mmScanBody').innerHTML = data.items.map(scanRowHtml).join('');
                    $('mmScanAll').checked = true;
                    $('mmScanBox').classList.remove('d-none');
                }
                flash('success', data.message);
            } catch (err) {
                flash('danger', err.message);
            } finally {
                button.disabled = false;
                button.textContent = 'Scan';
            }
        }

        // ---------- Ambil dari Material Radar List ----------
        function fmtDate(v) { return v ? v.split('-').reverse().join('/') : '-'; }

        function radarRowHtml(r) {
            const off = r.imported || !r.description || !(Number(r.req_qty) > 0);
            const why = r.imported ? 'Sudah diambil' : (!r.description || !(Number(r.req_qty) > 0) ? 'Nama / qty kosong' : '');
            const m = r.match;
            const matchNote = (!off && m) ? ' <span class="badge bg-info-subtle text-info-emphasis" title="Sudah ada di Raw Material: material tidak dobel, hanya tanggal diperlukan & kedatangan yang ditambahkan">Sudah ada: ' + esc(m.name) + (Number(m.quantity) !== Number(r.req_qty) ? ' (qty ' + fmt(m.quantity) + ' vs radar ' + fmt(r.req_qty) + ')' : '') + '</span>' : '';
            return '<tr' + (off ? ' class="text-muted"' : '') + ' data-rid="' + r.id + '">'
                + '<td><input type="checkbox" class="rd-on"' + (off ? ' disabled' : (m ? ' checked' : '')) + '></td>'
                + '<td>' + esc(r.description || '-') + (why ? ' <span class="badge bg-secondary-subtle text-secondary">' + why + '</span>' : '') + matchNote + '</td>'
                + '<td>' + esc(r.spec || '-') + '</td>'
                + '<td class="text-end">' + (r.req_qty === null ? '-' : fmt(r.req_qty)) + '</td>'
                + '<td><input type="text" class="form-control form-control-sm rd-uom" maxlength="20" value="' + (m ? esc(m.uom) : 'Pcs') + '"' + (off || m ? ' disabled' : '') + ' list="mmUomList"></td>'
                + '<td>' + fmtDate(r.required_date) + '</td>'
                + '<td class="text-end">' + (r.arrival_qty === null ? '-' : fmt(r.arrival_qty) + ' <span class="text-muted">(' + fmtDate(r.arrival_date) + ')</span>') + '</td></tr>';
        }

        async function loadRadar(radarId) {
            const data = await api(cfg.urls.radarSources + (radarId ? '?radar_id=' + encodeURIComponent(radarId) : ''), 'GET');
            const sel = $('mmRadarSelect');
            if (!data.radars.length) {
                sel.innerHTML = '<option value="">Belum ada data Material Radar List</option>';
                $('mmRadarBody').innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">Impor dulu di halaman Material Radar List.</td></tr>';
                return;
            }
            sel.innerHTML = data.radars.map(function (r) {
                return '<option value="' + r.id + '"' + (String(r.id) === String(data.selected_id) ? ' selected' : '') + '>' + esc(r.label) + (r.is_this_project ? ' (project ini)' : '') + '</option>';
            }).join('');
            $('mmRadarBody').innerHTML = data.items.length ? data.items.map(radarRowHtml).join('') : '<tr><td colspan="7" class="text-center text-muted py-3">Project ini belum punya baris material di radar.</td></tr>';
            $('mmRadarAll').checked = false;
        }

        async function openRadar() {
            $('mmRadarBox').classList.remove('d-none');
            try { await loadRadar(null); } catch (err) { flash('danger', err.message); }
        }

        async function importRadar() {
            const rows = [];
            $('mmRadarBody').querySelectorAll('tr[data-rid]').forEach(function (tr) {
                const on = tr.querySelector('.rd-on');
                if (on && on.checked && !on.disabled) { rows.push({ radar_item_id: tr.dataset.rid, uom: tr.querySelector('.rd-uom').value.trim() }); }
            });
            if (!rows.length) { flash('danger', 'Centang minimal satu material dari Material Radar List.'); return; }
            const button = $('mmRadarImport');
            button.disabled = true;
            try {
                const data = await api(cfg.urls.radarImport, 'POST', { category: $('mmItemCategory').value, radar_id: $('mmRadarSelect').value, rows: rows });
                render(data);
                flash('success', data.message);
                await loadRadar($('mmRadarSelect').value);
            } catch (err) {
                flash('danger', err.message);
            } finally {
                button.disabled = false;
            }
        }

        // ---------- Revisi BOM ----------
        const REV_BG = { white: '#ffffff', yellow: '#fff3cd', blue: '#cfe2ff', red: '#f8d7da' };

        function revRowHtml(r) {
            const disabled = r.status === 'skip' || r.status === 'same';
            const readonlyName = r.item_id ? ' readonly' : '';
            let info = '<div class="fw-bold">' + esc(r.label) + '</div>';
            if (r.changes && r.changes.length) { info += '<div class="text-muted" style="font-size:.68rem">' + r.changes.map(esc).join('<br>') + '</div>'; }
            if (r.warning) { info += '<div class="text-danger" style="font-size:.68rem">' + esc(r.warning) + '</div>'; }
            const deleting = r.action === 'delete';
            return '<tr style="background:' + REV_BG[r.color] + '" data-action="' + esc(r.action) + '" data-item="' + esc(r.item_id || '') + '" data-color="' + esc(r.color) + '">'
                + '<td style="background:inherit"><input type="checkbox" class="rv-on"' + (r.checked ? ' checked' : '') + (disabled ? ' disabled' : '') + '></td>'
                + '<td style="background:inherit">' + info + '</td>'
                + '<td style="background:inherit"><input type="text" class="form-control form-control-sm rv-name" maxlength="150" value="' + esc(r.name) + '"' + readonlyName + '></td>'
                + '<td style="background:inherit"><input type="text" class="form-control form-control-sm rv-spec" maxlength="150" value="' + esc(r.spec) + '"' + (deleting ? ' readonly' : '') + '></td>'
                + '<td style="background:inherit"><input type="text" class="form-control form-control-sm rv-uom" maxlength="20" value="' + esc(r.uom) + '"' + (deleting ? ' readonly' : '') + '></td>'
                + '<td style="background:inherit"><input type="number" step="any" min="0" class="form-control form-control-sm rv-qty" value="' + esc(r.quantity) + '"' + (deleting ? ' readonly' : '') + '>'
                + (r.old ? '<div class="text-muted" style="font-size:.66rem">lama: ' + esc(r.old.quantity) + '</div>' : '') + '</td></tr>';
        }

        function showRevision(rev) {
            $('mmRevBody').innerHTML = rev.rows.map(revRowHtml).join('');
            $('mmRevCw').textContent = rev.summary.white;
            $('mmRevCy').textContent = rev.summary.yellow;
            $('mmRevCb').textContent = rev.summary.blue;
            $('mmRevCr').textContent = rev.summary.red;
            $('mmRevBox').classList.remove('d-none');
        }

        async function applyRevision() {
            const rows = [];
            $('mmRevBody').querySelectorAll('tr').forEach(function (tr) {
                const on = tr.querySelector('.rv-on');
                if (!on.checked || on.disabled) { return; }
                const itemId = tr.dataset.item || null;
                let action = tr.dataset.action;
                if (action === 'none') { action = 'update'; }
                if (!itemId && action !== 'add') { return; }
                rows.push({
                    action: action,
                    item_id: itemId,
                    name: tr.querySelector('.rv-name').value.trim(),
                    spec: tr.querySelector('.rv-spec').value.trim(),
                    uom: tr.querySelector('.rv-uom').value.trim(),
                    quantity: tr.querySelector('.rv-qty').value
                });
            });
            if (!rows.length) { flash('danger', 'Tidak ada baris yang dicentang untuk diterapkan.'); return; }
            const deletes = rows.filter(function (r) { return r.action === 'delete'; }).length;
            if (deletes && !window.confirm(deletes + ' material akan DIHAPUS beserta catatan kedatangannya. Lanjutkan?')) { return; }
            const button = $('mmRevApply');
            button.disabled = true;
            try {
                const data = await api(cfg.urls.applyRevision, 'POST', { category: $('mmItemCategory').value, rows: rows });
                render(data);
                $('mmRevBox').classList.add('d-none');
                $('mmRevBody').innerHTML = '';
                $('mmPdfFile').value = '';
                flash('success', data.message || 'Revisi diterapkan.');
            } catch (err) {
                flash('danger', err.message);
            } finally {
                button.disabled = false;
            }
        }

        async function importScan() {
            const items = [];
            $('mmScanBody').querySelectorAll('tr').forEach(function (tr) {
                if (!tr.querySelector('.sc-on').checked) { return; }
                items.push({
                    name: tr.querySelector('.sc-name').value.trim(),
                    spec: tr.querySelector('.sc-spec').value.trim(),
                    uom: tr.querySelector('.sc-uom').value.trim(),
                    quantity: tr.querySelector('.sc-qty').value,
                    weight: tr.querySelector('.sc-weight').value
                });
            });
            if (!items.length) { flash('danger', 'Centang minimal satu baris untuk diimpor.'); return; }
            const button = $('mmScanImport');
            button.disabled = true;
            try {
                const data = await api(cfg.urls.importItems, 'POST', {
                    category: $('mmItemCategory').value,
                    purchase_date: $('mmItemPurchase').value || null,
                    items: items
                });
                render(data);
                $('mmScanBox').classList.add('d-none');
                $('mmScanBody').innerHTML = '';
                $('mmPdfFile').value = '';
                flash('success', data.message || 'Material diimpor.');
            } catch (err) {
                flash('danger', err.message);
            } finally {
                button.disabled = false;
            }
        }

        // ---------- Event ----------
        $('mmPdfScan').addEventListener('click', scanPdf);
        $('mmScanImport').addEventListener('click', importScan);
        $('mmRevApply').addEventListener('click', applyRevision);
        $('mmRadarOpen').addEventListener('click', openRadar);
        $('mmRadarClose').addEventListener('click', function () { $('mmRadarBox').classList.add('d-none'); });
        $('mmRadarImport').addEventListener('click', importRadar);
        $('mmRadarSelect').addEventListener('change', function () { loadRadar(this.value).catch(function (err) { flash('danger', err.message); }); });
        $('mmRadarAll').addEventListener('change', function () {
            const on = this.checked;
            $('mmRadarBody').querySelectorAll('.rd-on:not(:disabled)').forEach(function (cb) { cb.checked = on; });
        });
        // Tanggal diperlukan: ganti langsung dari daftar material
        modalEl.addEventListener('change', async function (e) {
            const inp = e.target.closest('[data-mm-req-date]');
            if (!inp) { return; }
            try {
                const data = await api(cfg.urls.requiredDate.replace('__ID__', inp.getAttribute('data-mm-req-date')), 'PATCH', { purchase_date: inp.value || null });
                render(data);
                flash('success', data.message);
            } catch (err) {
                flash('danger', err.message);
                renderItems();
            }
        });
        $('mmRevCancel').addEventListener('click', function () { $('mmRevBox').classList.add('d-none'); });
        $('mmScanCancel').addEventListener('click', function () { $('mmScanBox').classList.add('d-none'); });
        $('mmScanAll').addEventListener('change', function () {
            const on = this.checked;
            $('mmScanBody').querySelectorAll('.sc-on').forEach(function (cb) { cb.checked = on; });
        });
        $('mmPrev').addEventListener('click', function () { setPage(1); });
        $('mmNext').addEventListener('click', function () { setPage(2); });
        $('mmArrItem').addEventListener('change', updateHint);
        $('mmItemSave').addEventListener('click', saveItem);
        $('mmItemCancel').addEventListener('click', function () { resetItemForm(true); });
        $('mmArrSave').addEventListener('click', saveArrival);
        $('mmArrCancel').addEventListener('click', resetArrivalForm);

        modalEl.addEventListener('click', async function (e) {
            const editBtn = e.target.closest('[data-mm-edit]');
            const delItemBtn = e.target.closest('[data-mm-del-item]');
            const delArrivalBtn = e.target.closest('[data-mm-del-arrival]');
            const editArrivalBtn = e.target.closest('[data-mm-edit-arrival]');

            if (editArrivalBtn) {
                startEditArrival(editArrivalBtn.getAttribute('data-mm-edit-arrival'));
                return;
            }

            if (editBtn) {
                startEditItem(editBtn.getAttribute('data-mm-edit'));
                return;
            }

            if (delItemBtn) {
                const id = delItemBtn.getAttribute('data-mm-del-item');
                const item = state.items.find(function (i) { return String(i.id) === String(id); });
                if (!item) { return; }
                if (!window.confirm('Hapus material "' + item.name + '" beserta seluruh catatan kedatangannya?')) { return; }

                try {
                    const data = await api(cfg.urls.destroyItem.replace('__ID__', id), 'DELETE');
                    if (String(editingId) === String(id)) { resetItemForm(true); }
                    render(data);
                    flash('success', data.message || 'Material dihapus.');
                } catch (err) {
                    flash('danger', err.message);
                }
                return;
            }

            if (delArrivalBtn) {
                const id = delArrivalBtn.getAttribute('data-mm-del-arrival');
                if (!window.confirm('Hapus catatan kedatangan ini?')) { return; }

                try {
                    const data = await api(cfg.urls.destroyArrival.replace('__ID__', id), 'DELETE');
                if (String(editingArrivalId) === String(id)) { resetArrivalForm(); }
                    render(data);
                    flash('success', data.message || 'Catatan kedatangan dihapus.');
                } catch (err) {
                    flash('danger', err.message);
                }
            }
        });

        // Setiap popup dibuka: mulai dari halaman Input, lalu muat data terbaru
        modalEl.addEventListener('show.bs.modal', async function () {
            setPage(1);
            $('mmAlert').innerHTML = '';
            if (!$('mmArrDate').value) { $('mmArrDate').value = todayStr(); }

            try {
                render(await api(cfg.urls.index, 'GET'));
            } catch (err) {
                flash('danger', err.message);
            }
        });

        setPage(1);
    })();
</script>