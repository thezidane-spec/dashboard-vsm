{{-- Popup input Weekly Report (di luar form utama proyek). Variabel: $project --}}
@php
    $weeklyDefaults = collect(\App\Models\WeeklyReport::SECTIONS)->map(fn($label, $key) => ['key' => $key, 'title' => $label, 'time' => ''])->values();
    $weeklyData = $project->weeklyReports()->with('photos')->get()->mapWithKeys(fn($wr) => [$wr->id => [
        'id'              => $wr->id,
        'period'          => (string) $wr->period,
        'customer_name'   => (string) $wr->customer_name,
        'corporate_group' => (string) $wr->corporate_group,
        'address'         => (string) $wr->address,
        'postcode'        => (string) $wr->postcode,
        'language'        => (string) $wr->language,
        'logo'            => $wr->logo,
        'sections'        => $wr->sectionList(),
        'photos'          => $wr->photos->map(fn($p) => [
            'id' => $p->id, 'section' => $p->section, 'name' => $p->original_name, 'caption' => (string) $p->caption,
            'url' => route('projects.weekly.photo', [$project->id, $wr->id, $p->id]),
        ])->values(),
    ]]);
@endphp
<div class="modal fade no-print" id="modalWeekly" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <form id="formWeekly" class="d-flex flex-column" style="min-height: 0; max-height: calc(100vh - 3.5rem);" method="POST" action="{{ route('projects.weekly.store', $project->id) }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" value="POST" id="weeklyMethod">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold"><i class="bi bi-camera text-success me-1"></i> <span id="weeklyTitle">Buat Weekly Report</span> - {{ $project->project_code }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body" style="overflow-y: auto; min-height: 0;">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label-custom">Name of Customer</label>
                            <input type="text" name="customer_name" id="wkCustomer" class="form-control-custom" maxlength="255" value="{{ $project->customer }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Name of Corporate Group</label>
                            <input type="text" name="corporate_group" id="wkGroup" class="form-control-custom" maxlength="255">
                        </div>
                        <div class="col-12">
                            <label class="form-label-custom">Address</label>
                            <textarea name="address" id="wkAddress" class="form-control-custom" rows="2" maxlength="1000"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Postcode / Town / Country</label>
                            <input type="text" name="postcode" id="wkPostcode" class="form-control-custom" maxlength="255" placeholder="Contoh: 16820/JAWA BARAT/INDONESIA">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Language</label>
                            <input type="text" name="language" id="wkLanguage" class="form-control-custom" maxlength="100" value="INDONESIA">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Logo Header</label>
                            <select name="logo" id="wkLogo" class="form-control-custom">
                                @foreach(\App\Models\WeeklyReport::LOGOS as $logoKey => $logo)
                                    <option value="{{ $logoKey }}">{{ $logo['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label-custom">Periode (judul di header laporan)</label>
                            <input type="text" name="period" id="wkPeriod" class="form-control-custom" maxlength="150" placeholder="Contoh: MEI - SEPTEMBER 2026">
                            <div class="form-text">Waktu tiap tahap di bawah otomatis ikut terisi dari periode ini selama belum Anda ubah sendiri.</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <span class="fw-bold small text-uppercase">Tahap foto progress</span>
                        <button type="button" class="btn btn-sm btn-outline-success fw-semibold" id="wkAddSection"><i class="bi bi-plus-circle me-1"></i> Tambah Tahap</button>
                    </div>
                    <div class="row g-3" id="wkSections"></div>
                    <div class="form-text">Judul tiap tahap bisa diubah. Tambah atau hapus tahap sesuai kebutuhan proyek (maks. {{ \App\Models\WeeklyReport::MAX_SECTIONS }} tahap). Hasil Excel mengikuti jumlah tahap ini.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold" id="btnSaveWeekly"><i class="bi bi-save me-1"></i> Simpan Weekly Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Konfirmasi hapus Weekly Report --}}
<div class="modal fade no-print" id="modalWeeklyDelete" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <form id="formWeeklyDelete" method="POST" action="#">
                @csrf @method('DELETE')
                <div class="modal-body text-center p-4">
                    <i class="bi bi-trash3 text-danger fs-2"></i>
                    <h6 class="fw-bold mt-2">Hapus Weekly Report?</h6>
                    <p class="small text-muted mb-1" id="weeklyDeleteLabel"></p>
                    <p class="small text-muted mb-3">Semua foto di laporan ini ikut terhapus dan <strong>tidak dapat dikembalikan</strong>.</p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger btn-sm fw-semibold">Hapus</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const reports = @json($weeklyData);
        const storeUrl = @json(route('projects.weekly.store', $project->id));
        const updateUrl = @json(route('projects.weekly.update', [$project->id, '__ID__']));
        const deleteUrl = @json(route('projects.weekly.destroy', [$project->id, '__ID__']));
        const modalEl = document.getElementById('modalWeekly');
        if (!modalEl) { return; }
        const form = document.getElementById('formWeekly');
        const field = function (id) { return document.getElementById(id); };
        const MAX_SECTIONS = @json(\App\Models\WeeklyReport::MAX_SECTIONS);
        const MAX_PHOTOS = @json(\App\Models\WeeklyReport::MAX_PHOTOS_PER_SECTION);
        const defaults = @json($weeklyDefaults);
        const box = field('wkSections');
        let editing = null;
        let seq = 0;
        const touched = {};   // waktu tahap yang sudah diubah sendiri (per key tahap)

        const clearRemovals = function () { form.querySelectorAll('input[name="remove_photos[]"]').forEach(function (i) { i.remove(); }); };
        const newKey = function () { return 's' + Math.random().toString(36).slice(2, 10); };

        // satu baris foto: gambar kecil + kolom keterangan + tombol hapus
        function photoRow(src, name) {
            const row = document.createElement('div');
            row.className = 'd-flex align-items-start gap-2 border rounded-2 p-2 bg-white';
            const img = document.createElement('img');
            img.src = src; img.alt = name || 'Foto'; img.loading = 'lazy';
            img.style.cssText = 'width:84px;height:63px;object-fit:cover;flex:none;border-radius:4px;background:#f1f5f9;';
            const mid = document.createElement('div');
            mid.className = 'flex-grow-1 min-w-0';
            const cap = document.createElement('input');
            cap.type = 'text'; cap.className = 'form-control-custom'; cap.maxLength = 200;
            cap.placeholder = 'Keterangan foto (tampil di bawah foto)';
            const fn = document.createElement('div');
            fn.className = 'small text-muted text-truncate mt-1';
            fn.textContent = name || '';
            mid.append(cap, fn);
            const del = document.createElement('button');
            del.type = 'button';
            del.className = 'btn btn-outline-danger btn-sm flex-none';
            del.title = 'Hapus foto ini';
            del.textContent = '×';
            row.append(img, mid, del);
            return { row: row, cap: cap, del: del };
        }

        // foto yang sudah tersimpan (keterangan bisa diedit; × menandai foto untuk dihapus saat simpan)
        function renderThumbs(thumbBox, key, report) {
            thumbBox.innerHTML = '';
            if (!report) { return; }
            report.photos.filter(function (p) { return p.section === key; }).forEach(function (p) {
                const r = photoRow(p.url, p.name);
                r.cap.name = 'existing_captions[' + p.id + ']';
                r.cap.value = p.caption || '';
                r.del.title = 'Hapus foto ini saat disimpan';
                r.del.addEventListener('click', function () {
                    const flag = r.row.dataset.removed !== '1';
                    r.row.dataset.removed = flag ? '1' : '0';
                    r.row.style.opacity = flag ? '.4' : '1';
                    r.cap.disabled = flag;
                    r.del.textContent = flag ? '↺' : '×';
                    form.querySelectorAll('input[name="remove_photos[]"][value="' + p.id + '"]').forEach(function (i) { i.remove(); });
                    if (flag) {
                        const hid = document.createElement('input');
                        hid.type = 'hidden'; hid.name = 'remove_photos[]'; hid.value = p.id;
                        form.appendChild(hid);
                    }
                });
                thumbBox.appendChild(r.row);
            });
        }

        // foto baru yang dipilih: bisa ditambah berkali-kali, tiap foto punya keterangan & tombol hapus
        function bindNewFiles(col, key) {
            const input = col.querySelector('.wk-files');
            const list = col.querySelector('.wk-newlist');
            const saved = col.querySelector('.wk-thumbs');
            let items = [];
            const keptSaved = function () { return saved.querySelectorAll(':scope > div:not([data-removed="1"])').length; };
            const sync = function () {
                const dt = new DataTransfer();
                items.forEach(function (it) { dt.items.add(it.file); });
                input.files = dt.files;
                list.innerHTML = '';
                items.forEach(function (it) {
                    if (!it.url) { it.url = URL.createObjectURL(it.file); }
                    const r = photoRow(it.url, it.file.name);
                    r.cap.name = 'captions[' + key + '][]';
                    r.cap.value = it.caption;
                    r.cap.addEventListener('input', function () { it.caption = r.cap.value; });
                    r.del.addEventListener('click', function () {
                        URL.revokeObjectURL(it.url);
                        items = items.filter(function (x) { return x !== it; });
                        sync();
                    });
                    list.appendChild(r.row);
                });
            };
            input.addEventListener('change', function () {
                const picked = Array.from(input.files);
                let skipped = 0;
                picked.forEach(function (f) {
                    if (keptSaved() + items.length >= MAX_PHOTOS) { skipped++; return; }
                    items.push({ file: f, caption: '', url: null });
                });
                if (skipped) { alert('Maksimal ' + MAX_PHOTOS + ' foto per tahap. ' + skipped + ' foto tidak ditambahkan.'); }
                sync();
            });
        }

        function updateControls() {
            const count = box.querySelectorAll('.wk-section').length;
            box.querySelectorAll('.wk-del').forEach(function (b) { b.disabled = count <= 1; });
            field('wkAddSection').classList.toggle('d-none', count >= MAX_SECTIONS);
        }

        // satu tahap = judul + waktu + foto; judul, jumlah, dan waktu bebas diatur per laporan
        function addSection(sec, report) {
            const i = seq++;
            const col = document.createElement('div');
            col.className = 'col-12 wk-section';
            col.dataset.key = sec.key;
            col.innerHTML =
                '<div class="border rounded-3 p-3"><div class="row g-2 align-items-end">' +
                '<div class="col-md-4"><label class="form-label-custom">Judul tahap</label>' +
                '<input type="text" class="form-control-custom wk-title fw-bold" name="sections[' + i + '][title]" maxlength="100" required placeholder="Contoh: Material"></div>' +
                '<div class="col-md-7"><label class="form-label-custom">Waktu</label>' +
                '<input type="text" class="form-control-custom wk-time" name="sections[' + i + '][time]" maxlength="150" placeholder="Contoh: MEI - SEPTEMBER 2026"></div>' +
                '<div class="col-md-1 text-end"><button type="button" class="btn btn-outline-danger btn-sm wk-del" title="Hapus tahap ini"><i class="bi bi-trash3"></i></button></div>' +
                '<input type="hidden" name="sections[' + i + '][key]">' +
                '<div class="col-12"><label class="form-label-custom">Foto <span class="wk-title-lbl"></span> <span class="text-muted fw-normal">(JPG / PNG / WEBP, maks. ' + MAX_PHOTOS + ' foto)</span></label>' +
                '<input type="file" class="form-control form-control-sm wk-files" accept="image/jpeg,image/png,image/webp" multiple>' +
                '<div class="form-text">Pilih satu atau beberapa foto (boleh menambah berkali-kali). Keterangan tiap foto tampil tepat di bawah fotonya pada Excel.</div>' +
                '<div class="d-flex flex-column gap-2 mt-2 wk-thumbs"></div>' +
                '<div class="d-flex flex-column gap-2 mt-2 wk-newlist"></div></div>' +
                '</div></div>';

            const title = col.querySelector('.wk-title');
            const time = col.querySelector('.wk-time');
            const lbl = col.querySelector('.wk-title-lbl');
            col.querySelector('input[type="hidden"]').value = sec.key;
            col.querySelector('.wk-files').name = 'photos[' + sec.key + '][]';
            title.value = sec.title || '';
            time.value = sec.time || '';
            lbl.textContent = title.value;
            // waktu baru mengikuti periode sampai diubah sendiri
            touched[sec.key] = report ? (time.value !== '' && time.value !== report.period) : false;
            if (!report && time.value === '') { time.value = field('wkPeriod').value; }

            title.addEventListener('input', function () { lbl.textContent = title.value; });
            time.addEventListener('input', function () { touched[sec.key] = true; });
            col.querySelector('.wk-del').addEventListener('click', function () {
                const saved = col.querySelectorAll('.wk-thumbs > div').length + col.querySelectorAll('.wk-newlist > div').length;
                if (saved > 0 && !confirm('Tahap "' + title.value + '" punya ' + saved + ' foto. Hapus tahap ini beserta fotonya?')) { return; }
                col.remove();
                updateControls();
            });
            renderThumbs(col.querySelector('.wk-thumbs'), sec.key, report);
            bindNewFiles(col, sec.key);
            box.appendChild(col);
            updateControls();
            return col;
        }

        function openForm(report) {
            editing = report || null;
            clearRemovals();
            form.reset();
            Object.keys(touched).forEach(function (k) { delete touched[k]; });
            box.innerHTML = '';
            seq = 0;
            document.getElementById('weeklyTitle').textContent = report ? 'Edit Weekly Report' : 'Buat Weekly Report';
            document.getElementById('weeklyMethod').value = report ? 'PUT' : 'POST';
            form.action = report ? updateUrl.replace('__ID__', report.id) : storeUrl;
            if (report) {
                field('wkCustomer').value = report.customer_name;
                field('wkGroup').value = report.corporate_group;
                field('wkAddress').value = report.address;
                field('wkPostcode').value = report.postcode;
                field('wkLanguage').value = report.language;
                field('wkLogo').value = report.logo;
                field('wkPeriod').value = report.period;
            }
            (report ? report.sections : defaults).forEach(function (sec) { addSection(sec, report); });
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }

        field('wkAddSection').addEventListener('click', function () {
            if (box.querySelectorAll('.wk-section').length >= MAX_SECTIONS) { return; }
            const col = addSection({ key: newKey(), title: '', time: '' }, null);
            col.querySelector('.wk-title').focus();
            col.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        });

        // waktu tiap tahap mengikuti periode sampai diubah sendiri
        field('wkPeriod').addEventListener('input', function () {
            box.querySelectorAll('.wk-section').forEach(function (col) {
                if (!touched[col.dataset.key]) { col.querySelector('.wk-time').value = field('wkPeriod').value; }
            });
        });

        const btnNew = document.getElementById('btnNewWeekly');
        if (btnNew) { btnNew.addEventListener('click', function () { openForm(null); }); }
        document.querySelectorAll('.weekly-edit').forEach(function (b) {
            b.addEventListener('click', function () { openForm(reports[b.dataset.weekly]); });
        });
        document.querySelectorAll('.weekly-delete').forEach(function (b) {
            b.addEventListener('click', function () {
                document.getElementById('formWeeklyDelete').action = deleteUrl.replace('__ID__', b.dataset.weekly);
                document.getElementById('weeklyDeleteLabel').textContent = b.dataset.label;
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalWeeklyDelete')).show();
            });
        });

        // cegah kirim ganda saat unggah foto
        form.addEventListener('submit', function () {
            const btn = document.getElementById('btnSaveWeekly');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
        });
    });
</script>