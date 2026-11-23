{{--
    TEMA GELAP / TERANG.
    - Pilihan disimpan di localStorage ('vsm-theme'); default terang.
    - Memakai data-bs-theme Bootstrap 5.3 pada <html>, ditambah penyesuaian untuk class buatan sendiri di bawah.
    - Pasang di <head> (setelah semua <style>) pada setiap halaman, dan letakkan tombol dengan class "theme-toggle" di mana saja.
--}}
<script>
    (function () {
        try {
            var t = localStorage.getItem('vsm-theme');
            if (t === 'dark' || t === 'light') { document.documentElement.setAttribute('data-bs-theme', t); }
        } catch (e) { /* localStorage tidak tersedia: tetap tema terang */ }
    })();
</script>
<style>
    /* ===== Tombol pengalih tema ===== */
    .theme-toggle { width: 40px; height: 40px; border-radius: 50%; border: 1.5px solid #cbd5e1; background: #ffffff; color: #0f172a; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; cursor: pointer; padding: 0; transition: transform .25s ease, background-color .3s ease, color .3s ease, border-color .3s ease, box-shadow .25s ease; }
    .theme-toggle:hover { transform: rotate(18deg) scale(1.08); box-shadow: 0 4px 12px rgba(15,23,42,.18); }
    .theme-toggle:active { transform: scale(.92); }
    .theme-toggle i { transition: transform .5s cubic-bezier(.3,.7,.2,1), opacity .3s ease; display: inline-block; }
    .theme-toggle.spin i { transform: rotate(360deg) scale(.6); opacity: .2; }
    .theme-toggle.floating { position: fixed; right: 18px; bottom: 18px; z-index: 1080; box-shadow: 0 6px 18px rgba(15,23,42,.22); }
    @media print { .theme-toggle { display: none !important; } }

    /* Transisi halus sesaat ketika tema berganti */
    html.theme-anim, html.theme-anim *, html.theme-anim *::before, html.theme-anim *::after {
        transition: background-color .4s ease, color .4s ease, border-color .4s ease, box-shadow .4s ease, fill .4s ease, stroke .4s ease !important;
    }

    /* ===== TEMA GELAP ===== */
    [data-bs-theme="dark"] {
        --mei-bg-light: #0b1220;
        --mei-card-bg: #111a2b;
        --mei-border-color: #2b3a52;
        --mei-green-subtle: #0f2a22;
        --mei-green-border: #14532d;
        --mei-text-dark: #e2e8f0;
        --mei-sidebar-bg: #04261c;
        --bs-body-bg: #0b1220;
        --bs-body-color: #e2e8f0;
        --bs-border-color: #2b3a52;
        --bs-secondary-color: #94a3b8;
        color-scheme: dark;
    }
    [data-bs-theme="dark"] body { background-color: #0b1220; color: #e2e8f0; }
    [data-bs-theme="dark"] .theme-toggle { background: #162235; color: #fcd34d; border-color: #2b3a52; }

    /* utilitas Bootstrap yang warnanya "terkunci" putih/gelap */
    [data-bs-theme="dark"] .bg-white { background-color: #111a2b !important; }
    [data-bs-theme="dark"] .bg-light { background-color: #162235 !important; }
    [data-bs-theme="dark"] .text-dark { color: #e2e8f0 !important; }
    [data-bs-theme="dark"] .border, [data-bs-theme="dark"] .border-top, [data-bs-theme="dark"] .border-bottom { border-color: #2b3a52 !important; }
    [data-bs-theme="dark"] .text-muted { color: #94a3b8 !important; }
    [data-bs-theme="dark"] .btn-light { background: #162235; border-color: #2b3a52; color: #e2e8f0; }
    [data-bs-theme="dark"] .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
    [data-bs-theme="dark"] hr { border-color: #2b3a52; opacity: 1; }

    /* layout utama */
    [data-bs-theme="dark"] .topbar { background: #0f1828; border-bottom-color: #2b3a52; }
    [data-bs-theme="dark"] .content-body, [data-bs-theme="dark"] #main-wrapper { background-color: #0b1220; }
    [data-bs-theme="dark"] .card { background-color: #111a2b; border-color: #2b3a52; color: #e2e8f0; }
    [data-bs-theme="dark"] .card-header.bg-white { background-color: #111a2b !important; border-color: #2b3a52 !important; }
    [data-bs-theme="dark"] .card-body.bg-light { background-color: #0d1626 !important; }

    /* tabel */
    [data-bs-theme="dark"] .table { --bs-table-bg: transparent; --bs-table-color: #e2e8f0; --bs-table-border-color: #2b3a52; --bs-table-striped-bg: #131e31; --bs-table-hover-bg: #162842; --bs-table-hover-color: #ffffff; color: #e2e8f0; }
    [data-bs-theme="dark"] .table thead th, [data-bs-theme="dark"] .table-light, [data-bs-theme="dark"] .table > :not(caption) > * > .table-light { background-color: #162235 !important; color: #cbd5e1 !important; border-color: #2b3a52; }
    [data-bs-theme="dark"] .report-table thead th, [data-bs-theme="dark"] .remarks-table thead th { background: #162235 !important; color: #cbd5e1; }
    [data-bs-theme="dark"] .report-table thead th.group-head { background: #123524 !important; color: #86efac; }
    [data-bs-theme="dark"] .report-kpi { background: #111a2b; border-color: #2b3a52; }
    [data-bs-theme="dark"] .task-pill-done { background: rgba(34,197,94,.22); color: #86efac; }
    [data-bs-theme="dark"] .task-pill-pending { background: #1b2940; color: #94a3b8; }
    [data-bs-theme="dark"] .pct-red { background-color: rgba(239,68,68,.22) !important; color: #fca5a5 !important; }
    [data-bs-theme="dark"] .pct-yellow { background-color: rgba(245,158,11,.22) !important; color: #fcd34d !important; }
    [data-bs-theme="dark"] .pct-green { background-color: rgba(34,197,94,.22) !important; color: #86efac !important; }
    [data-bs-theme="dark"] .report-card { background: #111a2b; border-color: #2b3a52; }
    [data-bs-theme="dark"] .report-card-title { color: #e2e8f0; }
    [data-bs-theme="dark"] .report-card-soon { background: rgba(245,158,11,.15); border-color: rgba(245,158,11,.4); color: #fcd34d; }
    [data-bs-theme="dark"] .remarks-table .rm-date { background: rgba(245,158,11,.18); color: #fcd34d; }
    [data-bs-theme="dark"] .remarks-table .rm-plan { background: rgba(59,130,246,.2); color: #93c5fd; }
    [data-bs-theme="dark"] .remarks-table .rm-dept { background: rgba(34,197,94,.18); color: #86efac; }

    /* badge status */
    [data-bs-theme="dark"] .status-badge-on-track { background: rgba(34,197,94,.18) !important; color: #86efac !important; border-color: rgba(34,197,94,.5) !important; }
    [data-bs-theme="dark"] .status-badge-at-risk { background: rgba(245,158,11,.18) !important; color: #fcd34d !important; border-color: rgba(245,158,11,.5) !important; }
    .status-badge-closed-late { background: #ffedd5; color: #9a3412; border: 1.5px solid #fdba74; }
    [data-bs-theme="dark"] .status-badge-closed-late { background: rgba(234,88,12,.2) !important; color: #fdba74 !important; border-color: rgba(234,88,12,.55) !important; }
    [data-bs-theme="dark"] .status-badge-delayed { background: rgba(239,68,68,.18) !important; color: #fca5a5 !important; border-color: rgba(239,68,68,.5) !important; }
    [data-bs-theme="dark"] .badge-weight-custom { background-color: #123524; color: #86efac; }

    /* halaman Update Progress */
    [data-bs-theme="dark"] .custom-card { background: #111a2b; border-color: #2b3a52; box-shadow: 0 4px 14px rgba(0,0,0,.35); }
    [data-bs-theme="dark"] .section-header { background: linear-gradient(90deg, #0f2a22 0%, #0d2019 100%); border-color: #14532d; color: #6ee7b7; }
    [data-bs-theme="dark"] .form-label-custom { color: #cbd5e1; }
    [data-bs-theme="dark"] .form-control-custom { background-color: #0d1626; border-color: #2b3a52; color: #e2e8f0; }
    [data-bs-theme="dark"] .form-control-custom:focus { border-color: #34d399; box-shadow: 0 0 0 3px rgba(52,211,153,.2); outline: none; }
    [data-bs-theme="dark"] .form-control-readonly, [data-bs-theme="dark"] .form-control-custom:disabled { background-color: #162235 !important; border-color: #2b3a52 !important; color: #94a3b8 !important; }
    [data-bs-theme="dark"] .division-header-box { background: #0f1828; border-color: #2b3a52; }
    [data-bs-theme="dark"] .division-title-bar { border-bottom-color: #2b3a52; }
    [data-bs-theme="dark"] .task-box { background-color: #0d1626; border-color: #2b3a52; color: #e2e8f0; }
    [data-bs-theme="dark"] .task-box.disabled-box { background-color: #0a111e; }
    [data-bs-theme="dark"] .task-box.checked-active { background-color: #0f2a1d; border-color: #16a34a; }
    [data-bs-theme="dark"] .task-box.in-progress-active { background-color: #2a2110; border-color: #d97706; }
    [data-bs-theme="dark"] .task-box.not-started-active { background-color: #2a1414; border-color: #ef4444; }
    [data-bs-theme="dark"] .progress-input-group { background: #0d1626; border-color: #2b3a52; }
    [data-bs-theme="dark"] .progress-input-group .progress-input { background: transparent; color: #e2e8f0; }
    [data-bs-theme="dark"] .progress-input-suffix { color: #94a3b8; }
    [data-bs-theme="dark"] .prod-stage-block { background: #0d1626; border-color: #2b3a52; }
    [data-bs-theme="dark"] .remark-list { background: #0d1626; border-color: #2b3a52; }
    [data-bs-theme="dark"] .remark-row .remark-num { color: #94a3b8; }
    [data-bs-theme="dark"] .form-control, [data-bs-theme="dark"] .form-select { background-color: #0d1626; border-color: #2b3a52; color: #e2e8f0; }
    [data-bs-theme="dark"] .form-control::placeholder { color: #64748b; }
    [data-bs-theme="dark"] .form-control:disabled, [data-bs-theme="dark"] .form-select:disabled { background-color: #162235; color: #94a3b8; }

    /* modal (Material, Remarks, hapus, dll.) */
    [data-bs-theme="dark"] .modal-content { background-color: #111a2b; border-color: #2b3a52; color: #e2e8f0; }
    [data-bs-theme="dark"] .modal-header, [data-bs-theme="dark"] .modal-footer { border-color: #2b3a52; }
    [data-bs-theme="dark"] #modalMaterial .modal-header { background: #0f1828; border-bottom-color: #2b3a52; }
    [data-bs-theme="dark"] #modalMaterial .modal-body { background: #0b1220; }
    [data-bs-theme="dark"] .mm-nav-btn { background: #162235; border-color: #2b3a52; color: #e2e8f0; }
    [data-bs-theme="dark"] .mm-nav-btn:hover:not(:disabled) { background: #123524; }
    [data-bs-theme="dark"] .mm-panel { background: #111a2b; border-color: #2b3a52; }
    [data-bs-theme="dark"] .mm-subpanel, [data-bs-theme="dark"] #modalMaterial fieldset[disabled] .mm-panel { background: #0f1828; border-color: #2b3a52; }
    [data-bs-theme="dark"] .mm-list-table th { background: #162235; color: #cbd5e1; }

    /* Laporan material (lembar laporan) */
    [data-bs-theme="dark"] .mr-table { background: #111a2b; color: #e2e8f0; }
    [data-bs-theme="dark"] .mr-table thead th { background: #1b2940; color: #e2e8f0; }
    [data-bs-theme="dark"] .mr-table td.mr-actual, [data-bs-theme="dark"] .mr-table td.dev-zero { background: rgba(34,197,94,.22); color: #86efac; }
    [data-bs-theme="dark"] .mr-table td.dev-plus { background: rgba(245,158,11,.22); color: #fcd34d; }
    [data-bs-theme="dark"] .mr-table td.dev-minus { background: rgba(239,68,68,.22); color: #fca5a5; }
    [data-bs-theme="dark"] .mr-table tfoot td { background: #162235; color: #e2e8f0; }

    /* PDCA: panel jadi gelap */
    [data-bs-theme="dark"] .pdca-board { background: #06251b; }
    [data-bs-theme="dark"] .pdca-panel { background: #111a2b; color: #e2e8f0; }
    [data-bs-theme="dark"] .pdca-ph { border-color: #2b3a52; color: #94a3b8; }
    [data-bs-theme="dark"] .bar-track { background: #1b2940; }
    [data-bs-theme="dark"] .bar-plan { background: #e2e8f0; }
    [data-bs-theme="dark"] .donut, [data-bs-theme="dark"] .donut text { color: #e2e8f0; fill: #e2e8f0; }
    [data-bs-theme="dark"] .donut circle:first-child { stroke: #1f2e47; }
    [data-bs-theme="dark"] .pdca-list li { border-bottom-color: #2b3a52; }

    /* S-Curve & kartu ringkas */
    [data-bs-theme="dark"] .alert { border-color: #2b3a52; }
    [data-bs-theme="dark"] code { color: #fca5a5; }

    /* Halaman login */
    [data-bs-theme="dark"] .login-card { background: #111a2b !important; color: #e2e8f0; }
    [data-bs-theme="dark"] .login-card .form-control { background-color: #0d1626 !important; border-color: #2b3a52 !important; color: #e2e8f0 !important; }
    [data-bs-theme="dark"] .login-card .form-control:focus { background-color: #0d1626 !important; }
    [data-bs-theme="dark"] .login-card label, [data-bs-theme="dark"] .login-card h4, [data-bs-theme="dark"] .login-card h5 { color: #ffffff !important; }
    [data-bs-theme="dark"] body:has(.login-card) { background: linear-gradient(135deg, #020617 0%, #0b1220 50%, #0f1d33 100%); }
    [data-bs-theme="dark"] .login-card { border-color: #2b3a52; box-shadow: 0 20px 40px rgba(0, 0, 0, .55); }
    [data-bs-theme="dark"] .login-card .login-header { background-color: #0d1626; border-bottom-color: #2b3a52; }
    [data-bs-theme="dark"] .login-card .login-header h5,
    [data-bs-theme="dark"] .login-card .login-header small,
    [data-bs-theme="dark"] .login-card .text-dark,
    [data-bs-theme="dark"] .login-card .text-secondary,
    [data-bs-theme="dark"] .login-card .text-muted,
    [data-bs-theme="dark"] .login-card .form-check-label,
    [data-bs-theme="dark"] .login-card .form-control,
    [data-bs-theme="dark"] .login-card .btn-mei-primary { color: #ffffff !important; }
    [data-bs-theme="dark"] .login-card .form-control::placeholder { color: #94a3b8; }
    [data-bs-theme="dark"] .login-card .input-group-text-custom { color: #cbd5e1; }
    [data-bs-theme="dark"] .login-card .bg-light { background-color: #0d1626 !important; border-top-color: #2b3a52 !important; }
    [data-bs-theme="dark"] .login-card .form-check-input:not(:checked) { background-color: #0d1626; border-color: #475569; }
</style>
<script>
(function () {
    'use strict';
    var KEY = 'vsm-theme';
    var root = document.documentElement;

    function current() { return root.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light'; }

    // Chart.js: warna teks & garis grid mengikuti tema, grafik yang sudah ada ikut diperbarui
    function themeCharts() {
        if (typeof Chart === 'undefined') { return; }
        var dark = current() === 'dark';
        Chart.defaults.color = dark ? '#cbd5e1' : '#475569';
        Chart.defaults.borderColor = dark ? 'rgba(148,163,184,.18)' : 'rgba(0,0,0,.1)';
        try {
            Object.keys(Chart.instances || {}).forEach(function (id) { Chart.instances[id].update(); });
        } catch (e) { /* abaikan */ }
    }

    function paint() {
        var dark = current() === 'dark';
        document.querySelectorAll('.theme-toggle').forEach(function (btn) {
            btn.innerHTML = '<i class="bi ' + (dark ? 'bi-sun-fill' : 'bi-moon-stars-fill') + '"></i>';
            btn.setAttribute('aria-label', dark ? 'Ganti ke tema terang' : 'Ganti ke tema gelap');
            btn.setAttribute('title', dark ? 'Tema terang' : 'Tema gelap');
        });
        themeCharts();
    }

    function setTheme(next, animate) {
        if (animate) {
            root.classList.add('theme-anim');
            setTimeout(function () { root.classList.remove('theme-anim'); }, 500);
        }
        root.setAttribute('data-bs-theme', next);
        try { localStorage.setItem(KEY, next); } catch (e) { /* abaikan */ }
        paint();
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest ? e.target.closest('.theme-toggle') : null;
        if (!btn) { return; }
        btn.classList.add('spin');
        setTimeout(function () { btn.classList.remove('spin'); }, 450);
        setTheme(current() === 'dark' ? 'light' : 'dark', true);
    });

    // Cetak selalu memakai tema terang
    var before = null;
    window.addEventListener('beforeprint', function () { before = current(); root.setAttribute('data-bs-theme', 'light'); });
    window.addEventListener('afterprint', function () { if (before) { root.setAttribute('data-bs-theme', before); before = null; } });

    document.addEventListener('DOMContentLoaded', paint);
    window.addEventListener('load', themeCharts);
    window.vsmIsDark = function () { return current() === 'dark'; };
})();
</script>