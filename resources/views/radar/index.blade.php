@extends('layouts.app')

@section('title', 'Material Radar List - PT Mudalaya Energy Indonesia')
@section('page_title', 'Material Radar List')

@push('styles')
<style>
    /* Warna PIC -- ubah di sini bila ingin menyamakan dengan warna di spreadsheet */
    .radar { --c-eng: #BDD7EE; --c-ppic: #FFE699; --c-wh: #C6E0B4; --c-proc: #F8CBAD; --c-head: #D9D9D9; }
    .radar .pic-engineering { background: var(--c-eng) !important; }
    .radar .pic-ppic        { background: var(--c-ppic) !important; }
    .radar .pic-warehouse   { background: var(--c-wh) !important; }
    .radar .pic-procurement { background: var(--c-proc) !important; }
    .radar-wrap { overflow: auto; max-height: 72vh; background: #fff; border: 1px solid #94a3b8; border-radius: 6px; }
    .radar-table { border-collapse: collapse; font-size: .8rem; color: #111827; min-width: 1500px; width: 100%; }
    .radar-table th, .radar-table td { border: 1px solid #6b7280; padding: 4px 6px; vertical-align: middle; color: #111827; }
    .radar-table thead th { text-align: center; font-weight: 800; position: sticky; top: 0; z-index: 2; }
    .radar-table thead tr:nth-child(2) th { top: 27px; }
    .radar-table td.num { text-align: right; }
    .radar-table td.ctr { text-align: center; }
    .radar-table .e { display: none; width: 100%; min-width: 80px; font-size: .78rem; padding: 2px 4px; border: 1px solid #475569; border-radius: 3px; background: #fff; color: #111827; }
    .radar-table textarea.e { min-width: 220px; min-height: 52px; }
    .radar-table.editing .can-edit .v { display: none; }
    .radar-table.editing .can-edit .e { display: block; }
    .radar-table.editing .can-edit { outline: 2px solid #16a34a; outline-offset: -2px; }
    .radar-info { border-collapse: collapse; font-size: .85rem; color: #111827; }
    .radar-info td { border: 1px solid #6b7280; padding: 4px 10px; min-width: 150px; background: #fff; }
    .radar-info td.lbl { font-weight: 700; background: var(--c-head); min-width: 120px; }
    .legend-chip { display: inline-flex; align-items: center; gap: 6px; padding: 3px 12px; border: 1px solid #6b7280; font-size: .78rem; font-weight: 700; color: #111827; }
    .col-del { width: 34px; }
</style>
@endpush

@section('content')
@php
    $pics = ['engineering' => 'Engineering', 'ppic' => 'PPIC', 'warehouse' => 'Warehouse', 'procurement' => 'Procurement'];
    $editable = !empty($myPics) && $selected;
    $can = fn($field) => in_array($fieldPic[$field] ?? '', $myPics, true);
    $fmtD = fn($d) => $d ? $d->format('d M Y') : '';
    $fmtN = function ($n) { return $n === null ? '' : rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.'); };
    $startEdit = request()->boolean('edit');
@endphp
<div class="container-fluid p-0 radar">

    @if(session('success')) <div class="alert alert-success py-2 small">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger py-2 small">{{ session('error') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div> @endif

    {{-- BAR ATAS: pilih proyek, impor, edit --}}
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>

        <div class="dropdown">
            <button class="btn btn-sm btn-outline-success dropdown-toggle fw-semibold" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="min-width: 260px; text-align:left;">
                <i class="bi bi-folder2-open me-1"></i> {{ $selected ? $selected->label : 'Pilih proyek' }}
            </button>
            <ul class="dropdown-menu shadow border-0" style="max-height: 360px; overflow:auto; min-width: 320px;">
                @forelse($radars as $r)
                    <li><a class="dropdown-item {{ $selected && $selected->id === $r->id ? 'active' : '' }}" href="{{ route('radar.index', ['radar' => $r->id]) }}">{{ $r->label }}</a></li>
                @empty
                    <li><span class="dropdown-item-text text-muted small">Belum ada proyek. Tambahkan lewat tombol Impor.</span></li>
                @endforelse
            </ul>
        </div>

        @if($canManage)
            <button class="btn btn-sm btn-success" type="button" data-bs-toggle="modal" data-bs-target="#radarImportModal"><i class="bi bi-file-earmark-arrow-down me-1"></i> Impor dari Spreadsheet</button>
            @if($radars->isNotEmpty())
                <form method="POST" action="{{ route('radar.destroy-all') }}" class="m-0"
                      data-confirm-title="Hapus Semua Material Radar List?"
                      data-confirm-lead="Anda akan menghapus seluruh data Material Radar List:"
                      data-confirm-box="{{ $radars->count() }} proyek &middot; {{ \App\Models\MaterialRadarItem::count() }} baris material"
                      data-confirm-note="Semua proyek dan barisnya terhapus permanen dan &lt;strong&gt;tidak dapat dikembalikan&lt;/strong&gt;. Material di Raw Material tetap ada; hanya tautannya ke radar yang dilepas."
                      data-confirm-button="Ya, Hapus Semua">@csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger fw-semibold"><i class="bi bi-trash3 me-1"></i> Hapus Semua</button>
                </form>
            @endif
        @endif

        @if($editable)
            <button type="button" id="btnEdit" class="btn btn-sm btn-warning fw-semibold"><i class="bi bi-pencil-square me-1"></i> Edit</button>
            <button type="submit" form="radarForm" id="btnSave" class="btn btn-sm btn-success fw-semibold d-none"><i class="bi bi-check2-circle me-1"></i> Simpan Perubahan</button>
            <a href="{{ route('radar.index', ['radar' => $selected->id]) }}" id="btnCancel" class="btn btn-sm btn-outline-secondary d-none">Batal</a>
            @if(in_array('engineering', $myPics, true))
                <form method="POST" action="{{ route('radar.items.add', $selected) }}" class="m-0 d-none" id="addRowForm">@csrf
                    <button class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-lg"></i> Tambah Baris</button>
                </form>
            @endif
        @endif
        @if($canManage && $selected)
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#radarMetaModal"><i class="bi bi-person-lines-fill me-1"></i> Info &amp; PIC</button>
            <form method="POST" action="{{ route('radar.destroy', $selected) }}" class="m-0"
                  data-confirm-title="Hapus Proyek dari Radar?"
                  data-confirm-lead="Anda akan menghapus proyek berikut dari Material Radar List:"
                  data-confirm-box="{{ $selected->label }}"
                  data-confirm-note="Seluruh baris proyek ini terhapus permanen dan &lt;strong&gt;tidak dapat dikembalikan&lt;/strong&gt;. Material di Raw Material tetap ada; hanya tautannya yang dilepas."
                  data-confirm-button="Ya, Hapus Proyek">@csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
        @endif
    </div>

    {{-- LEGENDA PIC + INFO PROYEK --}}
    <div class="d-flex flex-wrap gap-4 align-items-start mb-3">
        <div class="scroll-x"><table class="radar-info">
            <tr><td class="lbl">Project No</td><td>{{ $selected->project_no ?? '' }}</td><td class="lbl pic-engineering">PIC Engineering</td><td class="pic-engineering">{{ $selected->pic_engineering ?? '' }}</td><td class="lbl pic-warehouse">PIC Warehouse</td><td class="pic-warehouse">{{ $selected->pic_warehouse ?? '' }}</td></tr>
            <tr><td class="lbl">Project Name</td><td>{{ $selected->project_name ?? '' }}</td><td class="lbl pic-ppic">PIC PPIC</td><td class="pic-ppic">{{ $selected->pic_ppic ?? '' }}</td><td class="lbl pic-procurement">PIC Procurement</td><td class="pic-procurement">{{ $selected->pic_procurement ?? '' }}</td></tr>
            <tr><td class="lbl">Customer</td><td>{{ $selected->customer ?? '' }}</td><td colspan="4" style="border:0;background:transparent;"></td></tr>
        </table></div>
        <div class="d-flex flex-wrap gap-1 align-items-center">
            <span class="small fw-bold text-muted me-1">Keterangan warna:</span>
            @foreach($pics as $k => $label)<span class="legend-chip pic-{{ $k }}">{{ $label }}</span>@endforeach
        </div>
    </div>

    @if($editable)
        <p class="small text-muted mb-2">Anda dapat mengedit kolom berwarna: <strong>{{ implode(', ', array_map(fn($p) => $pics[$p], $myPics)) }}</strong>. Klik <strong>Edit</strong>, ubah isi kolom yang disorot hijau, lalu <strong>Simpan Perubahan</strong>.</p>
    @endif

    <form method="POST" id="radarForm" action="{{ $selected ? route('radar.items.update', $selected) : '#' }}">
        @csrf @method('PUT')
        <div class="radar-wrap">
            <table class="radar-table {{ $startEdit && $editable ? 'editing' : '' }}" id="radarTable">
                <thead>
                    <tr>
                        <th rowspan="2" class="pic-engineering" style="width:42px;">No</th>
                        <th rowspan="2" class="pic-engineering" style="min-width:130px;">Material Code</th>
                        <th rowspan="2" class="pic-engineering" style="min-width:230px;">Material Description</th>
                        <th rowspan="2" class="pic-engineering" style="min-width:160px;">Material Spec</th>
                        <th rowspan="2" class="pic-engineering">Required QTY</th>
                        <th rowspan="2" class="pic-warehouse">Avail QTY</th>
                        <th colspan="2" class="pic-engineering">BOM</th>
                        <th colspan="3" class="pic-ppic">PR</th>
                        <th colspan="3" class="pic-procurement">PO</th>
                        <th rowspan="2" class="pic-ppic">Required Date</th>
                        <th rowspan="2" class="pic-warehouse">Actual Arrival Date</th>
                        <th rowspan="2" class="pic-warehouse">QTY of Arrival</th>
                        <th rowspan="2" class="pic-ppic" style="min-width:220px;">Remarks / Action Plan</th>
                        <th rowspan="2" class="col-del d-none" id="thDel"></th>
                    </tr>
                    <tr>
                        <th class="pic-engineering">BOM No</th><th class="pic-engineering">Release Date</th>
                        <th class="pic-ppic">PR No</th><th class="pic-ppic">QTY of PR</th><th class="pic-ppic">Release Date</th>
                        <th class="pic-procurement">PO No</th><th class="pic-procurement">QTY of PO</th><th class="pic-procurement">Release Date</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $cols = [
                            ['no','ctr','t'], ['material_code','','t'], ['description','','t'], ['spec','','t'], ['req_qty','num','n'], ['avail_qty','num','n'],
                            ['bom_no','','t'], ['bom_date','ctr','d'],
                            ['pr_no','','t'], ['pr_qty','num','n'], ['pr_date','ctr','d'],
                            ['po_no','','t'], ['po_qty','num','n'], ['po_date','ctr','d'],
                            ['required_date','ctr','d'], ['arrival_date','ctr','d'], ['arrival_qty','num','n'], ['remarks','','x'],
                        ];
                    @endphp
                    @forelse($items as $it)
                        <tr>
                            @foreach($cols as [$f, $align, $type])
                                @php
                                    $raw = $it->$f;
                                    $show = $type === 'd' ? $fmtD($raw) : ($type === 'n' ? $fmtN($raw) : $raw);
                                    $val  = $type === 'd' ? ($raw ? $raw->format('Y-m-d') : '') : ($type === 'n' ? $fmtN($raw) : $raw);
                                    $ok = $editable && $can($f);
                                @endphp
                                <td class="pic-{{ $fieldPic[$f] }} {{ $align }} {{ $ok ? 'can-edit' : '' }}">
                                    <span class="v">{{ $show }}</span>
                                    @if($ok)
                                        @if($type === 'x' || $f === 'description')
                                            <textarea class="e" name="items[{{ $it->id }}][{{ $f }}]">{{ $val }}</textarea>
                                        @elseif($type === 'd')
                                            <input class="e" type="date" name="items[{{ $it->id }}][{{ $f }}]" value="{{ $val }}">
                                        @elseif($type === 'n')
                                            <input class="e" type="number" step="any" name="items[{{ $it->id }}][{{ $f }}]" value="{{ $val }}">
                                        @else
                                            <input class="e" type="text" name="items[{{ $it->id }}][{{ $f }}]" value="{{ $val }}">
                                        @endif
                                    @endif
                                </td>
                            @endforeach
                            <td class="col-del d-none del-cell ctr">
                                @if($editable && in_array('engineering', $myPics, true))
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0" form="delForm{{ $it->id }}" title="Hapus baris"><i class="bi bi-x-lg"></i></button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        {{-- tabel kosong: hanya judul kolom, belum ada data --}}
                        <tr><td colspan="18" style="height: 120px; background:#fff;"></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>
    @if($editable && in_array('engineering', $myPics, true))
        @foreach($items as $it)
            <form id="delForm{{ $it->id }}" method="POST" action="{{ route('radar.items.delete', [$selected, $it]) }}" class="d-none"
                  data-confirm-title="Hapus Baris?"
                  data-confirm-lead="Anda akan menghapus baris material:"
                  data-confirm-box="{{ \Illuminate\Support\Str::limit($it->description ?: 'Baris tanpa nama', 90) }}"
                  data-confirm-note="Baris ini terhapus dari Material Radar List dan &lt;strong&gt;tidak dapat dikembalikan&lt;/strong&gt;."
                  data-confirm-button="Ya, Hapus Baris">@csrf @method('DELETE')</form>
        @endforeach
    @endif

    @if(!$selected)
        <p class="text-muted small mt-2">Belum ada data. @if($canManage) Klik <strong>Impor dari Spreadsheet</strong> untuk menambahkan satu atau beberapa sheet proyek sekaligus. @endif</p>
    @endif
</div>

{{-- MODAL IMPOR --}}
@if($canManage)
<div class="modal fade" id="radarImportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="{{ route('radar.import') }}" enctype="multipart/form-data">@csrf
            <div class="modal-header"><h6 class="modal-title fw-bold">Impor Material Radar List</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <p class="small text-muted">Semua sheet proyek di dalam spreadsheet akan ditambahkan sekaligus (sheet "Template" dilewati). Satu sheet = satu proyek.</p>
                <label class="form-label small fw-bold">Link Google Spreadsheet</label>
                <input type="url" name="sheet_url" class="form-control mb-1" placeholder="https://docs.google.com/spreadsheets/d/...">
                <div class="form-text mb-3">Akses spreadsheet harus "Siapa saja yang memiliki link" (Viewer).</div>
                <label class="form-label small fw-bold">atau unggah file .xlsx</label>
                <input type="file" name="sheet_file" class="form-control mb-3" accept=".xlsx">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="overwrite" value="1" id="ovr">
                    <label class="form-check-label small" for="ovr">Timpa proyek yang sudah ada (No Project sama). Perubahan manual pada proyek itu akan hilang.</label>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button><button class="btn btn-success btn-sm">Impor</button></div>
        </form>
    </div></div>
</div>
@if($selected)
<div class="modal fade" id="radarMetaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="{{ route('radar.meta', $selected) }}">@csrf @method('PUT')
            <div class="modal-header"><h6 class="modal-title fw-bold">Info Proyek &amp; PIC</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body row g-2">
                @foreach(['project_no' => 'Project No', 'project_name' => 'Project Name', 'customer' => 'Customer', 'pic_engineering' => 'PIC Engineering', 'pic_ppic' => 'PIC PPIC', 'pic_warehouse' => 'PIC Warehouse', 'pic_procurement' => 'PIC Procurement'] as $k => $lbl)
                    <div class="col-6"><label class="form-label small fw-bold mb-1">{{ $lbl }}</label><input type="text" name="{{ $k }}" class="form-control form-control-sm" value="{{ $selected->$k }}"></div>
                @endforeach
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button><button class="btn btn-success btn-sm">Simpan</button></div>
        </form>
    </div></div>
</div>
@endif
@endif

{{-- POPUP KONFIRMASI HAPUS (satu modal dipakai semua tombol hapus di halaman ini; gaya sama dengan popup hapus proyek) --}}
<div class="modal fade no-print" id="radarConfirmModal" tabindex="-1" aria-labelledby="radarConfirmTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger" id="radarConfirmTitle"><i class="bi bi-exclamation-triangle-fill me-2"></i><span data-rc="title">Hapus?</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2" data-rc="lead"></p>
                <div class="p-3 rounded-3 bg-light border mb-3 fw-bold" data-rc="box"></div>
                <p class="mb-0 text-danger small" data-rc="note"></p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary fw-bold px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger fw-bold px-4" id="radarConfirmOk"><i class="bi bi-trash3-fill me-1"></i> <span data-rc="button">Ya, Hapus</span></button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // Semua form ber-atribut data-confirm-* meminta konfirmasi lewat popup di atas (bukan dialog bawaan browser)
    var modalEl = document.getElementById('radarConfirmModal');
    if (!modalEl || typeof bootstrap === 'undefined') { return; }
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl), pending = null;
    function set(key, html, asHtml) { var el = modalEl.querySelector('[data-rc="' + key + '"]'); if (el) { if (asHtml) { el.innerHTML = html; } else { el.textContent = html; } } }
    document.addEventListener('submit', function (e) {
        var f = e.target;
        if (!f || !f.hasAttribute || !f.hasAttribute('data-confirm-title')) { return; }
        if (f.dataset.confirmed === '1') { return; }
        e.preventDefault();
        pending = f;
        set('title', f.getAttribute('data-confirm-title'));
        set('lead', f.getAttribute('data-confirm-lead') || '');
        set('box', f.getAttribute('data-confirm-box') || '');
        set('note', f.getAttribute('data-confirm-note') || '', true);
        set('button', f.getAttribute('data-confirm-button') || 'Ya, Hapus');
        modal.show();
    }, true);
    document.getElementById('radarConfirmOk').addEventListener('click', function () {
        if (!pending) { return; }
        this.disabled = true;
        pending.dataset.confirmed = '1';
        pending.submit();
    });
    modalEl.addEventListener('hidden.bs.modal', function () { pending = null; document.getElementById('radarConfirmOk').disabled = false; });
})();
</script>
<script>
(function () {
    var table = document.getElementById('radarTable'), btnEdit = document.getElementById('btnEdit');
    if (!table || !btnEdit) { return; }
    function setEdit(on) {
        table.classList.toggle('editing', on);
        btnEdit.classList.toggle('d-none', on);
        ['btnSave', 'btnCancel', 'addRowForm'].forEach(function (id) { var el = document.getElementById(id); if (el) el.classList.toggle('d-none', !on); });
        document.querySelectorAll('.col-del').forEach(function (el) { el.classList.toggle('d-none', !on); });
    }
    btnEdit.addEventListener('click', function () { setEdit(true); });
    if (table.classList.contains('editing')) { setEdit(true); }
})();
</script>
@endpush