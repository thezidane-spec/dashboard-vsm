{{--
    RESPONSIF LAYAR SMARTPHONE / TABLET.
    - Di bawah 992px sidebar menjadi menu geser (dibuka dengan tombol hamburger di topbar) dan konten memenuhi lebar layar.
    - Tabel lebar bisa digeser ke samping di dalam kartunya (halaman tidak ikut melebar).
    - Pasang di <head> setelah semua <style> (sama seperti partials.theme).
--}}
<style>
    /* Cegah apa pun membuat seluruh halaman bergeser ke samping */
    html, body { max-width: 100%; }
    #main-wrapper { min-width: 0; }
    img, canvas, svg, video { max-width: 100%; }
    .mobile-menu-btn, .sidebar-backdrop { display: none; }
    .division-title-bar { flex-wrap: wrap; gap: 8px; }
    /* tabel info yang lebar: geser di dalam wadahnya */
    .scroll-x { overflow-x: auto; max-width: 100%; -webkit-overflow-scrolling: touch; }

    /* ===== Tablet & HP (< 992px): sidebar jadi menu geser ===== */
    @media (max-width: 991.98px) {
        #sidebar { transform: translateX(-102%); width: min(82vw, 290px); height: 100vh; height: 100dvh; box-shadow: none; z-index: 1045; }
        body.sidebar-open #sidebar { transform: translateX(0); box-shadow: 0 0 40px rgba(0, 0, 0, .45); }
        #main-wrapper { margin-left: 0 !important; }
        .mobile-menu-btn {
            display: inline-flex; align-items: center; justify-content: center; flex: 0 0 auto;
            width: 40px; height: 40px; border-radius: 10px; border: 1.5px solid #cbd5e1; background: #fff; color: #0f172a; font-size: 1.35rem; padding: 0;
        }
        [data-bs-theme="dark"] .mobile-menu-btn { background: #162235; border-color: #2b3a52; color: #e2e8f0; }
        .sidebar-backdrop { position: fixed; inset: 0; background: rgba(2, 6, 23, .55); z-index: 1040; opacity: 0; pointer-events: none; transition: opacity .25s ease; display: block; }
        body.sidebar-open .sidebar-backdrop { opacity: 1; pointer-events: auto; }
        body.sidebar-open { overflow: hidden; }
        .topbar { padding: 0 16px; height: 62px; gap: 10px; }
        .topbar > div:first-child { min-width: 0; flex: 1 1 auto; }
        .topbar h5 { font-size: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; min-width: 0; }
        .content-body { padding: 16px; }
    }

    /* ===== HP (< 576px) ===== */
    @media screen and (max-width: 575.98px) {
        .topbar { padding: 0 12px; height: 58px; }
        .topbar .badge { font-size: 0; padding: 0 !important; width: 12px; height: 12px; border-radius: 50% !important; background: #059669 !important; border: 0 !important; }
        .topbar .badge i { display: none; }
        .topbar > div:last-child { gap: 10px !important; }
        .content-body { padding: 12px; }
        h1, .h1 { font-size: 1.5rem; } h2, .h2 { font-size: 1.35rem; } h3, .h3 { font-size: 1.2rem; }
        .modal-dialog:not(.modal-fullscreen) { margin: .5rem; }
        .modal-body { padding: 12px; }
        .modal-header, .modal-footer { padding: 10px 12px; }
        .btn-group-responsive, .page-actions { flex-wrap: wrap; }
        .dropdown-menu { max-width: calc(100vw - 24px); min-width: 0 !important; }
        /* tombol dropdown dengan teks panjang (mis. pilih proyek) menyesuaikan lebar layar, teks dipotong dengan "..." */
        .dropdown { max-width: 100%; min-width: 0; }
        .dropdown > .dropdown-toggle { max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .dropdown > .dropdown-toggle[style*="min-width"] { min-width: 0 !important; width: 100%; }
        /* header halaman Update Progress Proyek */
        .header-page-bar { gap: 12px !important; }
        .header-page-title { min-width: 0; width: 100%; gap: 10px !important; }
        .header-page-title > div:last-child { min-width: 0; flex: 1 1 auto; }
        .header-page-title h3 { font-size: 1.1rem; line-height: 1.25; word-break: break-word; }
        .header-page-icon { padding: 10px !important; }
        .header-page-icon i { font-size: 1.25rem !important; }
        .header-page-bar > .btn { width: 100%; }
        /* kartu dan kotak isian lebih rapat */
        .custom-card, .card { border-radius: 12px; }
        .table-responsive, .rm-scroll { -webkit-overflow-scrolling: touch; }
        /* tombol aksi tidak terlalu kecil untuk jari */
        .btn-sm { min-height: 34px; }
        input.form-control, select.form-select, .form-control-custom { font-size: 16px; } /* cegah iOS zoom otomatis saat fokus */

        /* tombol mode gelap melayang: lebih kecil, dan disembunyikan saat popup terbuka (tidak menutupi tombol Simpan) */
        .theme-toggle.floating { width: 36px; height: 36px; right: 12px; bottom: 12px; opacity: .92; }
        body.modal-open .theme-toggle.floating { display: none; }
        /* baris riwayat progress: jobdesk satu baris penuh, tanggal + persen di bawahnya */
        .history-row { flex-wrap: wrap; }
        .history-row .h-task { flex: 0 0 100%; width: 100%; }
        .history-row .h-date { flex: 1 1 auto; max-width: none !important; min-width: 0; }
        .history-row .h-progress { flex: 0 0 84px; max-width: 84px !important; }

        /* tabel daftar sederhana (class "table-stack") tampil sebagai kartu per baris di HP */
        .table-stack thead { display: none; }
        .table-stack, .table-stack tbody, .table-stack tr, .table-stack td { display: block; width: 100%; }
        .table-stack tr { border: 1px solid var(--bs-border-color, #e2e8f0); border-radius: 10px; padding: 8px 12px; margin-bottom: 10px; }
        .table-stack td { border: 0 !important; padding: 4px 0 !important; text-align: left !important; background: transparent !important; }
        .table-stack td[data-label]::before { content: attr(data-label); display: block; font-size: .66rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #64748b; }
        .table-stack td.stack-actions { display: flex; flex-wrap: wrap; gap: 6px; padding-top: 8px !important; white-space: normal !important; }
        /* daftar proyek di dashboard: kartu mungil, 3 proyek per baris (kode + status, nama, customer, plan/actual/gap, due date, tombol) */
        .dash-cards thead { display: none; }
        .dash-cards, .dash-cards tbody { display: block; width: 100%; }
        .dash-cards tbody { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 6px; }
        .dash-cards tr { display: flex; flex-direction: column; gap: 3px; padding: 7px; margin: 0; border: 1px solid var(--bs-border-color, #e2e8f0); border-radius: 10px; font-size: .62rem; min-width: 0; }
        .dash-cards tr:has(td[colspan]) { grid-column: 1 / -1; }
        .dash-cards td { display: block; border: 0 !important; padding: 0 !important; background: transparent !important; text-align: left !important; min-width: 0; word-break: break-word; line-height: 1.25; }
        .dash-cards td[colspan] { text-align: center !important; padding: 14px 0 !important; font-size: .8rem; }
        .dash-cards td[data-label]::before { content: attr(data-label); font-size: .5rem; font-weight: 700; text-transform: uppercase; letter-spacing: .02em; color: #64748b; }
        .dash-cards [data-k="no"], .dash-cards [data-k="qty"], .dash-cards [data-k="delivery"], .dash-cards [data-k="fab"], .dash-cards [data-k="note"] { display: none !important; }
        .dash-cards [data-k="code"] { order: 1; font-size: .64rem; font-weight: 800; }
        .dash-cards [data-k="status"] { order: 2; }
        .dash-cards [data-k="status"] .badge { font-size: .48rem; padding: 2px 6px !important; border-width: 1px !important; }
        .dash-cards [data-k="name"] { order: 3; font-size: .7rem; line-height: 1.2; display: -webkit-box !important; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        .dash-cards [data-k="customer"] { order: 4; }
        .dash-cards [data-k="customer"] .badge { font-size: .5rem; white-space: normal; text-align: left; padding: 2px 5px; }
        .dash-cards [data-k="plan"], .dash-cards [data-k="actual"], .dash-cards [data-k="gap"] { order: 5; display: flex !important; justify-content: space-between; align-items: baseline; font-size: .62rem; border-top: 1px dashed rgba(148, 163, 184, .45) !important; padding-top: 2px !important; }
        .dash-cards [data-k="due"] { order: 6; font-size: .58rem; }
        .dash-cards [data-k="due"]::before { display: block; }
        .dash-cards [data-k="aksi"] { order: 9; text-align: center !important; padding-top: 2px !important; }
        .dash-cards [data-k="aksi"] .btn { width: 100%; font-size: .56rem; padding: 4px 2px !important; line-height: 1.15; }
        .dash-cards [data-k="aksi"] .bi { display: none; }

        /* baris tombol di bawah form (Batal / Hapus / Simpan): Simpan memenuhi lebar, tombol lain berdampingan di bawahnya */
        .form-actions-bar { flex-direction: column-reverse; align-items: stretch !important; gap: 10px; }
        .form-actions-main { width: 100%; }
        .form-actions-left { width: 100%; }
        .form-actions-left > * { flex: 1 1 0; }

        /* ---- halaman laporan (Summary Report) ---- */
        /* kotak angka ringkasan (Total Proyek, Rata-rata, On Track, dst.): 3 per baris dan mungil */
        .row > [class*="col-"]:has(> .report-kpi) { flex: 0 0 auto; width: 33.3333%; }
        .row:has(> [class*="col-"] > .report-kpi) { --bs-gutter-x: .5rem; --bs-gutter-y: .5rem; }
        .report-kpi { padding: 7px 6px !important; border-radius: 10px !important; height: 100%; display: flex; flex-direction: column; justify-content: center; }
        .report-kpi small { font-size: .52rem !important; line-height: 1.2; display: block; }
        .report-kpi h3 { font-size: 1.05rem; margin: 1px 0 0 !important; }
        .sc-stats > [class*="col-"] { flex: 0 0 auto; width: 33.3333%; }
        .sc-stats > [class*="col-"] > div { padding: 6px 4px !important; }
        .sc-stats small { font-size: .5rem !important; line-height: 1.2; }
        .sc-stats .fs-5, .sc-stats .fs-6 { font-size: .85rem !important; }
        /* pilihan laporan di halaman indeks: kartu mungil 3 per baris */
        .row > [class*="col-"]:has(> .report-card) { flex: 0 0 auto; width: 33.3333%; }
        .report-card { padding: 8px 7px !important; border-radius: 10px !important; height: 100%; }
        .report-card-title { font-size: .72rem !important; line-height: 1.2; gap: 4px; margin-bottom: 6px !important; flex-direction: column; align-items: flex-start; }
        .report-card-title i { font-size: 1rem !important; }
        .report-card-desc { display: none; }
        .report-card .d-flex, .report-card form.d-flex { flex-direction: column; gap: 5px !important; }
        .report-card .form-select { font-size: .62rem !important; padding: 4px 16px 4px 6px !important; min-height: 0; background-size: 10px !important; background-position: right 4px center !important; }
        .report-card .btn-report { font-size: .6rem !important; padding: 5px 4px !important; width: 100%; white-space: normal !important; }
        /* tabel proyek di laporan: kartu mungil 3 per baris; ketuk kartu untuk melihat rincian jobdesk */
        .rpt-cards thead { display: none; }
        .rpt-cards, .rpt-cards tbody { display: block; width: 100%; }
        .rpt-cards tbody { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 6px; }
        .rpt-cards tr { display: flex; flex-direction: column; gap: 3px; padding: 7px; margin: 0; border: 1px solid var(--bs-border-color, #e2e8f0); border-radius: 10px; font-size: .62rem; min-width: 0; cursor: pointer; }
        .rpt-cards tr:has(td[colspan]) { grid-column: 1 / -1; cursor: default; }
        .rpt-cards td:not(.rpt-job) { background: transparent !important; }
        .rpt-cards td { display: block; border: 0 !important; padding: 0 !important; text-align: left !important; min-width: 0 !important; max-width: 100% !important; width: auto !important; word-break: break-word; line-height: 1.25; white-space: normal !important; }
        .rpt-cards td[colspan] { text-align: center !important; padding: 14px 0 !important; font-size: .8rem; }
        .rpt-cards td[data-label]::before { content: attr(data-label); font-size: .5rem; font-weight: 700; text-transform: uppercase; letter-spacing: .02em; color: #64748b; }
        .rpt-cards [data-k="no"], .rpt-cards [data-k="extra"], .rpt-cards .rpt-job { display: none !important; }
        .rpt-cards [data-k="code"] { order: 1; font-size: .64rem; font-weight: 800; }
        .rpt-cards [data-k="status"] { order: 2; }
        .rpt-cards [data-k="status"] .badge { font-size: .48rem; padding: 2px 6px !important; white-space: normal; text-align: left; }
        .rpt-cards [data-k="name"] { order: 3; font-size: .7rem; line-height: 1.2; display: -webkit-box !important; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        .rpt-cards [data-k="name"] a { font-size: inherit; }
        .rpt-cards [data-k="customer"] { order: 4; font-size: .55rem; color: #64748b; }
        .rpt-cards [data-k="plan"], .rpt-cards [data-k="actual"], .rpt-cards [data-k="gap"] { order: 5; display: flex !important; justify-content: space-between; align-items: baseline; font-size: .62rem; border-top: 1px dashed rgba(148, 163, 184, .45) !important; padding-top: 2px !important; }
        .rpt-cards [data-k="due"] { order: 6; font-size: .58rem; }
        .rpt-cards [data-k="due"]::before { display: block; }
        .rpt-cards [data-k="aksi"] { order: 9; text-align: center !important; }
        .rpt-cards [data-k="aksi"] .btn { width: 100%; font-size: .56rem; padding: 4px 2px !important; }
        .rpt-cards tr:has(.rpt-job):not(:has(td[colspan]))::after { content: "Rincian \25BE"; order: 30; font-size: .52rem; color: #64748b; text-align: center; }
        .rpt-cards tr.rpt-open { grid-column: 1 / -1; }
        .rpt-cards tr.rpt-open::after { content: "Tutup \25B4"; }
        .rpt-cards tr.rpt-open .rpt-job { display: flex !important; justify-content: space-between; gap: 8px; order: 20; padding: 2px 5px !important; border-radius: 5px; font-size: .62rem; }
        .rpt-cards tr.rpt-open .rpt-job::before { font-size: .56rem; text-transform: none; letter-spacing: 0; }
        /* tabel teks panjang (Problem Divisi dst.): satu kartu per baris, huruf kecil */
        .table-stack tr { font-size: .72rem; }
        .table-stack .stack-hide { display: none !important; }

        /* ---- halaman Update Progress Proyek: kurangi lapisan padding agar kartu jobdesk tidak sempit ---- */
        .container { padding-left: 0; padding-right: 0; }   /* .content-body sudah memberi jarak tepi */
        .custom-card.p-4 { padding: 10px !important; }
        .division-header-box { padding: 8px !important; }
        /* kartu jobdesk: 3 per baris dan berukuran mungil */
        :is(.div-task-grid, .task-row-2) { --bs-gutter-x: .5rem; --bs-gutter-y: .5rem; }
        .div-task-grid > [data-div-task], .task-row-2 > [class*="col-"] { flex: 0 0 auto; width: 50%; max-width: 50%; }
        :is(.div-task-grid, .task-row-2) .task-box { padding: 9px; border-width: 1px; border-radius: 10px; }
        :is(.div-task-grid, .task-row-2) .task-box > div:first-of-type { flex-direction: column; gap: 2px !important; margin-bottom: 4px !important; }
        :is(.div-task-grid, .task-row-2) .task-box .fw-bold { font-size: .76rem; line-height: 1.15; word-break: break-word; }
        :is(.div-task-grid, .task-row-2) .task-box p.small { display: none; }
        :is(.div-task-grid, .task-row-2) .div-weight-badge { font-size: .6rem; padding: 1px 7px; align-self: flex-start; }
        :is(.div-task-grid, .task-row-2) .custom-task-remove { font-size: .7rem; }
        :is(.div-task-grid, .task-row-2) .progress-input-group { padding: 0 4px; gap: 2px; margin-bottom: 4px !important; }
        :is(.div-task-grid, .task-row-2) .progress-input-group .progress-input { font-size: .95rem; padding: 4px 0; min-width: 0; width: 100%; }
        :is(.div-task-grid, .task-row-2) .progress-input-suffix { font-size: .62rem; }
        :is(.div-task-grid, .task-row-2) .task-late-badge { font-size: .56rem; padding: 1px 5px; margin-bottom: 4px; white-space: normal; line-height: 1.2; }
        :is(.div-task-grid, .task-row-2) .form-label-custom { font-size: .58rem; margin: 0 0 1px; letter-spacing: 0; }
        :is(.div-task-grid, .task-row-2) .row.g-2 { --bs-gutter-x: 0; --bs-gutter-y: .25rem; }
        :is(.div-task-grid, .task-row-2) input[type="date"] { font-size: 11.5px !important; padding: 3px 3px !important; height: 30px; min-width: 0; }
        :is(.div-task-grid, .task-row-2) .itp-status-btn { font-size: .66rem; padding: 3px 4px; white-space: normal; line-height: 1.15; }
        .task-row-2 .badge-weight-custom { font-size: .6rem; padding: 1px 7px; align-self: flex-start; }
        .task-row-2 .form-select { font-size: .66rem; padding: 4px 18px 4px 6px; min-height: 0; background-size: 10px; background-position: right 5px center; }
        .task-row-2 .btn { font-size: .68rem; padding: 5px 4px; white-space: normal; line-height: 1.15; }
        .task-row-2 .btn .badge { font-size: .55rem; }
        .task-row-2 .btn i { display: none; }
        /* isian laporan (Problem Divisi + Daily Report): 2 kolom seragam */
        .report-input-row { --bs-gutter-x: .5rem; --bs-gutter-y: .5rem; }
        #form-update-project .report-input-row > [class*="col-md-"] { flex: 0 0 auto; width: 50%; max-width: 50%; }
        #form-update-project .report-input-row > .col-12, #form-update-project .report-input-row > .col-md-6:last-child:nth-child(odd) { width: 100%; max-width: 100%; }
        .report-input-row > * { min-width: 0; }
        .report-input-row .form-label-custom { font-size: .62rem; margin-bottom: 2px; letter-spacing: 0; }
        .report-input-row .form-control-custom, .report-input-row .form-control { font-size: .8rem; padding: 7px 8px; min-width: 0; }
        .report-input-row input[type="date"] { font-size: 11.5px !important; padding: 6px 4px !important; }
        .report-input-row .jd-label { font-size: .75rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: block; }
        .div-problem-box { padding: 10px !important; }
        /* informasi utama proyek + halaman tambah proyek: 2 kolom seragam */
        .form-2col { --bs-gutter-x: .5rem; --bs-gutter-y: .6rem; align-items: flex-start !important; }
        .form-2col > [class*="col-md-"], .form-2col > [class*="col-lg-"] { flex: 0 0 auto; width: 50%; max-width: 50%; min-width: 0; }
        .form-2col .form-label, .form-2col .form-label-custom { font-size: .66rem; margin-bottom: 2px; letter-spacing: 0; line-height: 1.2; }
        .form-2col .form-control, .form-2col .form-control-custom { font-size: .82rem; padding: 7px 8px; min-width: 0; }
        .form-2col input[type="date"] { font-size: 12px !important; padding: 6px 4px !important; }
        .form-2col .form-text, .form-2col small.text-muted { font-size: .58rem !important; line-height: 1.25; }
        .form-2col .badge { font-size: .6rem; white-space: normal; }
        /* Input tanggal: ketuk di mana saja pada kolom langsung membuka pemilih tanggal (kalender) bawaan HP */
        input[type="date"] {
            position: relative;
            padding-right: 26px !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='%2364748b' viewBox='0 0 16 16'%3E%3Cpath d='M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 14px;
        }
        input[type="date"]::-webkit-calendar-picker-indicator {
            position: absolute; inset: 0; width: 100%; height: 100%;
            opacity: 0; cursor: pointer; margin: 0; padding: 0;
        }
        /* isian berpasangan di bagian lain (mis. Procurement) dibuat satu kolom supaya tidak terpotong */
        #form-update-project .row.g-2 > .col-6 { flex: 0 0 100%; max-width: 100%; }
        /* judul bagian + tombol aksinya boleh turun ke baris berikutnya */
        .section-header { flex-wrap: wrap; gap: 8px; }
        .section-header > span { min-width: 0; }

        /* ===== Department Workflow: kartu divisi 3 per baris ===== */
        .card-body:has(> .dept-row) { padding: .6rem !important; }
        .dept-row { --bs-gutter-x: .45rem; --bs-gutter-y: .45rem; }
        .dept-row > .dept-col { flex: 0 0 auto; width: 33.3333%; }
        .dept-card { padding: 7px 6px !important; border-radius: 10px !important; }
        .dept-card .d-flex.justify-content-between.align-items-start { flex-direction: column; gap: 2px; margin-bottom: 4px !important; }
        .dept-card .badge { font-size: .52rem; padding: 2px 4px; }
        .dept-card h6 { font-size: .62rem; line-height: 1.2; overflow-wrap: anywhere; }
        .dept-card small.text-muted.d-block { display: none !important; }
        .dept-col, .dept-card, .dept-card > div, .dept-card .d-flex > div { min-width: 0; }
        .dept-card .fs-4 { font-size: .95rem !important; line-height: 1; }
        .dept-card .progress { height: 6px !important; margin-bottom: 6px !important; }
        .dept-card .row-cols-2 > * { flex: 0 0 100%; width: 100%; max-width: 100%; }
        .dept-card .row-cols-2 small { font-size: .56rem !important; line-height: 1.25; align-items: flex-start !important; }
        .dept-card .row-cols-2 small i { font-size: .6rem; margin-right: 3px !important; margin-top: 1px; }
        .dept-card .border-top { flex-direction: column; align-items: flex-start !important; gap: 0; font-size: .55rem; padding-top: 4px !important; margin-top: 4px !important; }
        .dept-card .border-top i { display: none; }
        .dept-card .btn { font-size: .55rem; padding: 4px 2px; white-space: normal; line-height: 1.15; }
        .dept-card .btn i { display: none; }
        .dept-card .mt-3.pt-2 { margin-top: 6px !important; padding-top: 0 !important; }

        /* ===== PDCA Control Center: 3 panel per baris ===== */
        .pdca-board { padding: 8px; border-radius: 12px; }
        .pdca-head { margin-bottom: 8px; gap: 6px; }
        .pdca-head h2 { font-size: .85rem; letter-spacing: 1px; }
        .pdca-head img { height: 26px; }
        .pdca-legend span { margin-left: 6px; font-size: .55rem; }
        .pdca-grid { grid-template-columns: repeat(3, minmax(0, 1fr)) !important; gap: 6px; }
        .pdca-panel { min-height: 0; border-radius: 8px; }
        .pdca-panel:hover { transform: none; }
        .pdca-panel-title { font-size: .54rem; letter-spacing: 0; padding: 5px 5px; border-radius: 8px 8px 0 0; line-height: 1.2; }
        .pdca-panel-title .num { width: 14px; height: 14px; line-height: 14px; font-size: .5rem; margin-right: 3px; }
        .pdca-body { padding: 6px 5px; font-size: .56rem; overflow-wrap: anywhere; }
        .pdca-ph { min-height: 70px; font-size: .55rem; padding: 4px; }
        .tag-ph { font-size: .45rem; padding: 1px 4px; margin-left: 0; }
        .stat-row { gap: 4px; }
        .stat-box { flex: 1 1 100%; padding: 4px; font-size: .5rem; border-radius: 6px; }
        .stat-box b { font-size: .95rem; }
        .stat-grid { gap: 4px; min-height: 0; }
        .stat-grid .stat-box { padding: 6px 2px; font-size: .52rem; }
        .stat-grid .stat-box b { font-size: 1.1rem; }
        .donuts { flex-wrap: wrap; gap: 2px; }
        .donut { font-size: .5rem; }
        .donut svg, #donutsPlan .donut svg, #donutsPlan .donut:first-child svg { width: 46px !important; height: 46px !important; }
        #donutsPlan { row-gap: 2px; }
        .bar-row { gap: 3px; margin-bottom: 5px; flex-wrap: wrap; }
        .bar-row .lbl { width: 100%; font-size: .5rem; }
        .bar-row > div:last-child { width: 28px !important; font-size: .5rem; }
        .bar-track { height: 7px; }
        .weekbars { gap: 3px; height: 60px; font-size: .5rem; }
        .pdca-sub { font-size: .52rem; margin: 6px 0 3px; }
        .pdca-list li { padding: 2px 0; font-size: .5rem; }
        .pdca-list .float-end { float: none !important; display: block; }
        .pdca-mat-info { font-size: .5rem; }
        .pdca-body [style*="font-size:.7rem"], .pdca-body [style*="font-size:.78rem"] { font-size: .48rem !important; }
        .pdca-dd { margin-top: 5px; }
        .pdca-dd-btn { font-size: .5rem; padding: 3px 4px; border-radius: 6px; gap: 2px; }
        .pdca-dd-btn.dropdown-toggle::after { margin-left: 0; border-width: .25em .25em 0; }
        .pdca-dd .dropdown-menu { position: fixed !important; left: 8px !important; right: 8px !important; top: auto !important; bottom: 8px !important; transform: none !important; width: auto !important; max-height: 55vh; font-size: .8rem; z-index: 2000; }
        .pdca-foot { font-size: .55rem; letter-spacing: 1px; margin-top: 8px; }
    }
</style>
<script>
    // Menu geser di HP: buka lewat tombol hamburger, tutup lewat latar gelap / pilih menu / tombol Esc
    document.addEventListener('DOMContentLoaded', function () {
        var body = document.body;
        document.querySelectorAll('[data-sidebar-toggle]').forEach(function (b) {
            b.addEventListener('click', function () { body.classList.toggle('sidebar-open'); });
        });
        document.querySelectorAll('.sidebar-backdrop, #sidebar .nav-link-custom').forEach(function (el) {
            el.addEventListener('click', function () { body.classList.remove('sidebar-open'); });
        });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { body.classList.remove('sidebar-open'); } });
        window.addEventListener('resize', function () { if (window.innerWidth >= 992) { body.classList.remove('sidebar-open'); } });
    });

    // Kartu proyek di halaman laporan (HP): ketuk untuk membuka / menutup rincian jobdesk
    document.addEventListener('click', function (e) {
        if (!window.matchMedia('(max-width: 575.98px)').matches) { return; }
        var tr = e.target.closest('.rpt-cards tbody tr');
        if (!tr || e.target.closest('a, button') || tr.querySelector('td[colspan]')) { return; }
        tr.classList.toggle('rpt-open');
    });
</script>