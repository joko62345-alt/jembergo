<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Transaksi JemberGo</title>
    <style>
        @page { size: A4 landscape; margin: 18mm; }
        :root { color: #111; background: #fff; font-family: "Times New Roman", Times, serif; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #111; background: #fff; font-size: 12px; }
        .print-toolbar { display: flex; justify-content: flex-end; gap: 8px; margin-bottom: 20px; font-family: Arial, sans-serif; }
        .print-toolbar button { padding: 8px 14px; border: 1px solid #111; background: #fff; color: #111; cursor: pointer; font-size: 12px; }
        .print-toolbar button.primary { background: #111; color: #fff; }
        .document-header { padding-bottom: 12px; border-bottom: 2px solid #111; text-align: center; }
        .document-header h1 { margin: 0; font-size: 21px; letter-spacing: .04em; }
        .document-header p { margin: 4px 0 0; font-size: 12px; }
        .document-meta { display: flex; justify-content: space-between; margin: 14px 0; font-size: 11px; }
        .summary { display: flex; justify-content: space-between; align-items: end; margin: 14px 0; padding: 10px 12px; border: 1px solid #111; }
        .summary-label { font-size: 11px; }
        .summary strong { font-size: 17px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 7px 8px; border: 1px solid #111; text-align: left; }
        th { background: #eee; font-weight: bold; }
        td:last-child, th:last-child { text-align: right; }
        .empty { padding: 28px; text-align: center; }
        .document-footer { margin-top: 18px; font-size: 10px; text-align: right; }
        @media print { .print-toolbar { display: none; } }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <button type="button" onclick="window.print()"><i>Print</i> / Cetak</button>
        <button class="primary" type="button" onclick="window.close()">Tutup preview</button>
    </div>
    @include('superadmin.partials.report-document')
</body>
</html>