<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Material - {{ $project->project_name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    @include('projects.partials.material-report-styles')

    <style>
        body { font-family: 'Plus Jakarta Sans', Arial, sans-serif; margin: 0; padding: 20px; color: #0f172a; background: #fff; }
        .toolbar { display: flex; gap: 8px; margin-bottom: 16px; }
        .toolbar button { background: #047857; color: #fff; border: 0; border-radius: 8px; padding: 8px 16px; font-weight: 700; cursor: pointer; font-family: inherit; }
        .toolbar button.secondary { background: #e2e8f0; color: #0f172a; }
        .print-head { border-bottom: 2px solid #047857; padding-bottom: 8px; margin-bottom: 16px; }
        .print-head h1 { font-size: 1.15rem; margin: 0 0 4px 0; }
        .print-head .meta { font-size: 0.8rem; color: #475569; }

        @page { size: A4 landscape; margin: 10mm; }

        @media print {
            body { padding: 0; }
            .toolbar { display: none; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .mr-scroll { overflow: visible; }
            .mr-table { font-size: 7pt; }
            .mr-table th, .mr-table td { white-space: normal; padding: 2px 4px; }
            .mr-section { page-break-inside: auto; }
            .mr-table tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Cetak / Simpan sebagai PDF</button>
        <button type="button" class="secondary" onclick="window.close()">Tutup</button>
    </div>

    <div class="print-head">
        <h1>{{ $project->project_name }} - Main Material</h1>
        <div class="meta">
            Kode Proyek: {{ $project->project_code }}
            &nbsp;|&nbsp; Customer: {{ $project->customer ?: '-' }}
            &nbsp;|&nbsp; Dicetak: {{ now()->format('d M Y H:i') }}
        </div>
    </div>

    @include('projects.partials.material-report', ['report' => $report])

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 500);
        });
    </script>
</body>
</html>