<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi JemberGo</title>
    <style>
        @page {
            margin: 18mm;
        }

        body {
            margin: 0;
            color: #111;
            background: #fff;
            font-family: "Times New Roman", Times, serif;
            font-size: 11px;
        }

        .document-header {
            padding-bottom: 10px;
            border-bottom: 2px solid #111;
            text-align: center;
        }

        .document-header h1 {
            margin: 0;
            font-size: 20px;
            letter-spacing: .04em;
        }

        .document-header p {
            margin: 4px 0 0;
            font-size: 11px;
        }

        .document-meta {
            width: 100%;
            margin: 12px 0;
            font-size: 10px;
        }

        .document-meta td {
            width: 50%;
            padding: 0 0 4px;
        }

        .document-meta td:last-child {
            text-align: right;
        }

        .summary {
            width: 100%;
            margin: 12px 0;
            padding: 8px 10px;
            border: 1px solid #111;
        }

        .summary td {
            padding: 0;
        }

        .summary td:last-child {
            text-align: right;
        }

        .summary-label {
            font-size: 10px;
        }

        .summary strong {
            font-size: 15px;
        }

        table.report-table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-table th,
        .report-table td {
            padding: 6px 7px;
            border: 1px solid #111;
            text-align: left;
        }

        .report-table th {
            background: #eee;
            font-weight: bold;
        }

        .report-table td:last-child,
        .report-table th:last-child {
            text-align: right;
        }

        .empty {
            padding: 24px;
            text-align: center;
        }

        .document-footer {
            margin-top: 14px;
            font-size: 9px;
            text-align: right;
        }
    </style>
</head>

<body>
    @include('superadmin.partials.report-document')
</body>

</html>
