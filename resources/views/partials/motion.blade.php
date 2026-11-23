{{--
    Animasi interaktif (dipakai di Dashboard utama dan halaman Update Progress).
    $motionPage: 'dashboard' | 'edit'
    Semua efek memakai class/selector yang sudah ada -- tidak mengubah isi data apa pun.
--}}
@php $motionPage = $motionPage ?? 'dashboard'; @endphp
<style>
    @keyframes mvUp     { from { opacity: 0; transform: translateY(24px) scale(.97); } to { opacity: 1; transform: none; } }
    @keyframes mvPop    { 0% { opacity: 0; transform: scale(.5); } 70% { transform: scale(1.08); } 100% { opacity: 1; transform: none; } }
    @keyframes mvBump   { 0% { transform: scale(1); } 35% { transform: scale(1.18); } 100% { transform: scale(1); } }
    @keyframes mvPulseR { 0%, 100% { box-shadow: 0 0 0 0 rgba(220,38,38,.55); } 50% { box-shadow: 0 0 0 8px rgba(220,38,38,0); } }
    @keyframes mvPulseY { 0%, 100% { box-shadow: 0 0 0 0 rgba(217,119,6,.5); } 50% { box-shadow: 0 0 0 7px rgba(217,119,6,0); } }
    @keyframes mvFlash  { 0% { background-color: rgba(34,197,94,.45); } 100% { background-color: transparent; } }
    @keyframes mvTarget { 0% { box-shadow: 0 0 0 0 rgba(34,197,94,.7); } 100% { box-shadow: 0 0 0 18px rgba(34,197,94,0); } }
    @keyframes mvRipple { from { transform: scale(0); opacity: .45; } to { transform: scale(4); opacity: 0; } }
    @keyframes mvSpin   { to { transform: rotate(360deg); } }

    html { scroll-behavior: smooth; }
    .mv-hide { opacity: 0; }
    .mv-in   { animation: mvUp .6s cubic-bezier(.2,.8,.2,1) backwards; }
    .mv-pop  { animation: mvPop .55s cubic-bezier(.2,.8,.2,1) backwards; }
    .mv-bump { animation: mvBump .45s ease; display: inline-block; }
    .mv-flash { animation: mvFlash 1s ease-out; }
    :target.mv-target, .mv-target-hit { animation: mvTarget 1.2s ease-out 2; }

    /* tombol: efek tekan + riak klik */
    .btn, button.nav-link-custom { position: relative; overflow: hidden; transition: transform .15s ease, box-shadow .2s ease, filter .2s ease; }
    .btn:active { transform: scale(.95); }
    .btn:not(:disabled):hover { filter: brightness(1.06); }
    .mv-ripple { position: absolute; border-radius: 50%; width: 40px; height: 40px; margin: -20px 0 0 -20px; background: rgba(255,255,255,.7); pointer-events: none; animation: mvRipple .6s ease-out forwards; }
    .btn-spinner { display: inline-block; width: 1em; height: 1em; border: 2px solid currentColor; border-right-color: transparent; border-radius: 50%; animation: mvSpin .7s linear infinite; vertical-align: -2px; margin-right: 6px; }

    /* sidebar */
    .nav-link-custom { transition: background-color .2s ease, color .2s ease; }

@if($motionPage === 'dashboard')
    /* kartu KPI & kartu proyek interaktif */
    .mv-lift { position: relative; transition: transform .25s cubic-bezier(.2,.8,.2,1), box-shadow .25s ease; }
    /* "jembatan" tak terlihat di bawah kartu: saat kartu terangkat, kursor tetap dianggap di atasnya (mencegah kedip-kedip) */
    .mv-lift::after { content: ''; position: absolute; left: 0; right: 0; bottom: -10px; height: 10px; }
    .mv-lift:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(15,23,42,.15) !important; }
    .progress-bar.mv-grow { transition: width 1.3s cubic-bezier(.3,.7,.2,1); }
    /* baris tabel: hanya sorotan warna (tanpa transform, supaya tabel tidak melebar / scrollbar tidak bergerak) */
    .table tbody tr { transition: background-color .2s ease, box-shadow .2s ease; }
    .table tbody tr:hover { box-shadow: inset 4px 0 0 #16a34a; }
    .status-badge-delayed { animation: mvPulseR 1.8s infinite; }
    .status-badge-at-risk { animation: mvPulseY 2.2s infinite; }
@endif

@if($motionPage === 'edit')
    .progress-bar { transition: width .6s cubic-bezier(.3,.7,.2,1); }
    #donut-section circle[id$="-arc"] { transition: stroke-dasharray .8s cubic-bezier(.3,.7,.2,1), stroke .4s ease; }
    .progress-input-group { transition: box-shadow .25s ease, transform .2s ease; }
    .progress-input-group:focus-within { transform: scale(1.04); }
    .progress-input-group.mv-bump { display: flex; }
    .mv-done-burst { animation: mvFlash 1.2s ease-out; border-radius: 10px; }
    .mv-saving { pointer-events: none; opacity: .85; }
@endif

    @media print {
        .mv-hide { opacity: 1 !important; }
        *, *::before, *::after { animation: none !important; transition: none !important; }
    }
</style>

<script>
(function () {
    'use strict';
    var PAGE = @json($motionPage);
    var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };

    // ---------- Angka menghitung naik ----------
    function countUp(el, delay, duration) {
        var txt = el.textContent.trim();
        var m = txt.match(/^(\D*?)(\d+(?:[.,]\d+)?)(.*)$/);
        if (!m) { return; }
        var target = parseFloat(m[2].replace(',', '.'));
        if (!isFinite(target)) { return; }
        var decimals = (m[2].split(/[.,]/)[1] || '').length;
        var start = null;
        el.textContent = m[1] + (0).toFixed(decimals) + m[3];
        function step(ts) {
            if (start === null) { start = ts; }
            var p = Math.min(1, (ts - start) / (duration || 1100));
            var v = target * (1 - Math.pow(1 - p, 3));
            el.textContent = m[1] + v.toFixed(decimals) + m[3];
            if (p < 1) { requestAnimationFrame(step); } else { el.textContent = txt; }
        }
        setTimeout(function () { requestAnimationFrame(step); }, delay || 0);
    }

    // ---------- Muncul saat di-scroll (berurutan) ----------
    function reveal(selector, opts) {
        opts = opts || {};
        var els = $$(selector).filter(function (el) { return !el.classList.contains('mv-seen'); });
        if (!('IntersectionObserver' in window)) { return; }
        els.forEach(function (el) { el.classList.add('mv-hide', 'mv-seen'); });
        var queue = 0;
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) { return; }
                var el = entry.target;
                io.unobserve(el);
                var delay = Math.min(queue++ * 70, 560);
                setTimeout(function () {
                    el.classList.remove('mv-hide');
                    el.classList.add(opts.pop ? 'mv-pop' : 'mv-in');
                    if (opts.onShow) { opts.onShow(el); }
                    queue = Math.max(0, queue - 1);
                }, delay);
            });
        }, { threshold: 0, rootMargin: '0px 0px -2px 0px' });
        els.forEach(function (el) { io.observe(el); });
    }

    // ---------- Riak klik pada tombol ----------
    document.addEventListener('click', function (e) {
        var btn = e.target.closest ? e.target.closest('.btn') : null;
        if (!btn || btn.disabled) { return; }
        var r = btn.getBoundingClientRect();
        var dot = document.createElement('span');
        dot.className = 'mv-ripple';
        dot.style.left = (e.clientX - r.left) + 'px';
        dot.style.top = (e.clientY - r.top) + 'px';
        btn.appendChild(dot);
        setTimeout(function () { if (dot.parentNode) { dot.parentNode.removeChild(dot); } }, 650);
    });

    function bump(el) {
        el.classList.remove('mv-bump');
        void el.offsetWidth;
        el.classList.add('mv-bump');
    }

    document.addEventListener('DOMContentLoaded', function () {

        if (PAGE === 'dashboard') {
            // KPI: muncul membal + angka naik + efek angkat saat disentuh
            $$('.row.g-3.mb-4 > .col > .card').forEach(function (card, i) {
                card.classList.add('mv-lift', 'mv-pop');
                card.style.animationDelay = (i * 90) + 'ms';
                var num = card.querySelector('h2');
                if (num) { countUp(num, 250 + i * 90, 1200); }
            });

            // Bar filter & kartu section
            reveal('.filter-card-bar, .card.mb-4');

            // Kartu proyek per divisi: muncul berurutan, persen naik, bar terisi
            $$('.col-md-6.col-lg-4 > .border.rounded-3').forEach(function (card) { card.classList.add('mv-lift'); });
            $$('.progress-bar').forEach(function (bar) {
                var target = bar.style.width;
                if (!target) { return; }
                bar.classList.add('mv-grow');
                bar.style.width = '0%';
                bar.setAttribute('data-target', target);
            });
            reveal('.col-md-6.col-lg-4 > .border.rounded-3', {
                onShow: function (card) {
                    var bar = card.querySelector('.progress-bar[data-target]');
                    if (bar) { setTimeout(function () { bar.style.width = bar.getAttribute('data-target'); }, 120); }
                    var pct = card.querySelector('.fs-4.fw-extrabold');
                    if (pct) { countUp(pct, 100, 1000); }
                }
            });
            // Jaga-jaga: bar yang tidak masuk kartu terobservasi tetap terisi
            setTimeout(function () {
                $$('.progress-bar[data-target]').forEach(function (bar) {
                    if (bar.style.width === '0%' && !bar.closest('.mv-hide')) { bar.style.width = bar.getAttribute('data-target'); }
                });
            }, 1500);

            // Baris tabel berurutan
            reveal('table.table tbody tr');
        }

        if (PAGE === 'edit') {
            // Bagian form muncul berurutan saat di-scroll
            reveal('.custom-card, .division-header-box');

            // Header live: angka naik di awal, lalu "membal" tiap berubah
            ['display-actual', 'display-plan', 'display-gap'].forEach(function (id, i) {
                var el = document.getElementById(id);
                if (!el) { return; }
                setTimeout(function () { countUp(el, 0, 900); }, 350 + i * 100);
                setTimeout(function () {
                    new MutationObserver(function () { bump(el); }).observe(el, { childList: true, characterData: true, subtree: true });
                }, 1800);
            });
            var bar = document.getElementById('display-progress-bar');
            if (bar) {
                var w = bar.style.width;
                bar.style.width = '0%';
                setTimeout(function () { bar.style.width = w; }, 400);
            }

            // Diagram bulat Planning / Actual / Gap: tergambar dari 0 saat halaman dibuka
            $$('#donut-section circle[id$="-arc"]').forEach(function (arc, i) {
                var target = arc.getAttribute('stroke-dasharray');
                if (!target) { return; }
                var parts = target.split(/[ ,]+/);
                arc.style.transition = 'none';
                arc.setAttribute('stroke-dasharray', '0 ' + (parts[1] || 400));
                setTimeout(function () {
                    arc.style.transition = '';
                    arc.setAttribute('stroke-dasharray', target);
                }, 500 + i * 150);
            });
            $$('#donut-section text[id$="-text"]').forEach(function (tx, i) { countUp(tx, 500 + i * 150, 1000); });

            // Input progress jobdesk: bergetar/membal saat diubah; kilau hijau saat mencapai 100%
            $$('.progress-input').forEach(function (input) {
                var last = input.value;
                input.addEventListener('input', function () {
                    var group = input.closest('.progress-input-group');
                    if (group) { bump(group); }
                    var v = parseInt(input.value, 10);
                    if (v === 100 && last !== '100') {
                        var holder = input.closest('label') || input.closest('.task-item') || group;
                        if (holder) {
                            holder.classList.remove('mv-done-burst');
                            void holder.offsetWidth;
                            holder.classList.add('mv-done-burst');
                        }
                    }
                    last = input.value;
                });
            });

            // Tombol simpan: indikator "Menyimpan..." supaya jelas sedang diproses
            var form = document.getElementById('form-update-project');
            if (form) {
                form.addEventListener('submit', function (e) {
                    var btn = e.submitter || form.querySelector('button[type="submit"]');
                    if (!btn || btn.classList.contains('mv-saving')) { return; }
                    btn.classList.add('mv-saving');
                    var span = document.createElement('span');
                    span.className = 'btn-spinner';
                    btn.insertBefore(span, btn.firstChild);
                });
            }

            // Lompat ke bagian lewat #anchor (mis. #remarks-section): beri sorotan
            if (location.hash) {
                var target = document.querySelector(location.hash);
                if (target) {
                    setTimeout(function () { target.classList.add('mv-target-hit'); }, 700);
                }
            }
        }
    });
})();
</script>