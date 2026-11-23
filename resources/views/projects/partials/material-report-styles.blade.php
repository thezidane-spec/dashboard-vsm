{{-- Gaya tabel laporan material (dipakai popup & halaman cetak PDF) --}}
<style>
    .mr-section { margin-bottom: 26px; }
    .mr-section-title { font-weight: 800; font-size: 0.92rem; color: #0f172a; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; }
    .mr-letter { display: inline-block; min-width: 24px; text-align: center; background: #059669; color: #fff; border-radius: 6px; padding: 1px 7px; }
    .mr-scroll { overflow-x: auto; max-width: 100%; }

    .mr-table { border-collapse: collapse; width: 100%; font-size: 0.74rem; color: #0f172a; background: #fff; }
    .mr-table th, .mr-table td { border: 1px solid #64748b; padding: 4px 7px; text-align: center; vertical-align: middle; white-space: nowrap; }
    .mr-table thead th { background: #e5e7eb; font-weight: 800; }
    .mr-table td.mr-left { text-align: left; white-space: normal; min-width: 150px; }
    .mr-table td.mr-remark { text-align: left; white-space: normal; min-width: 140px; }

    .mr-table td.mr-mill { background: #fef9c3; }
    .mr-table td.mr-mill .form-check-input { margin: 0; cursor: pointer; width: 1.05rem; height: 1.05rem; }
    .mr-table td.mr-actual { background: #d1fae5; color: #047857; font-weight: 700; }
    .mr-table td.dev-zero  { background: #d1fae5; color: #047857; font-weight: 700; }
    .mr-table td.dev-plus  { background: #fef3c7; color: #92400e; font-weight: 700; }
    .mr-table td.dev-minus { background: #fee2e2; color: #991b1b; font-weight: 700; }

    .mr-table tfoot td { background: #f3f4f6; font-weight: 800; }
    .mr-table tfoot td.mr-percent { color: #c2410c; font-size: 0.86rem; letter-spacing: 0.2px; white-space: normal; }
    .mr-table td.mr-empty { color: #64748b; padding: 16px; text-align: center; font-style: italic; }
</style>