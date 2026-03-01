<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Harian - {{ date('d F Y', strtotime($tanggal)) }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #10b981;
            padding-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            color: #10b981;
            margin-bottom: 5px;
        }
        .header h2 {
            font-size: 18px;
            color: #666;
            font-weight: 600;
            margin-top: 10px;
        }
        .date-info {
            background-color: #f0fdf4;
            padding: 10px 15px;
            border-left: 4px solid #10b981;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .stats-grid {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        .stat-card {
            flex: 1;
            min-width: 130px;
            border: 1px solid #e5e7eb;
            padding: 12px;
            border-radius: 8px;
            background-color: #f9fafb;
            font-size: 11px;
        }
        .stat-label {
            font-size: 10px;
            color: #999;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #10b981;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #10b981;
            margin-top: 25px;
            margin-bottom: 12px;
            border-left: 4px solid #10b981;
            padding-left: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        thead {
            background-color: #10b981;
            color: white;
        }
        th {
            padding: 10px;
            text-align: left;
            font-weight: 600;
            border: 1px solid #ddd;
        }
        td {
            padding: 8px 10px;
            border: 1px solid #ddd;
        }
        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .status {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 600;
        }
        .status.lunas {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status.menunggu {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status.batal {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .footer {
            margin-top: 40px;
            text-align: right;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        .text-right {
            text-align: right;
        }
        .two-column {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
        }
        .col {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📅 Laporan Harian</h1>
        <h2>{{ date('l, d F Y', strtotime($tanggal)) }}</h2>
        <p>Sari Bumi Sakti POS System</p>
    </div>

    <div class="date-info">
        <strong>Tanggal:</strong> {{ date('d F Y', strtotime($tanggal)) }} | <strong>Hari:</strong> {{ date('l', strtotime($tanggal)) }}
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Transaksi</div>
            <div class="stat-value">{{ $stats['total_transaksi'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Lunas</div>
            <div class="stat-value" style="color: #059669;">{{ $stats['total_lunas'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Menunggu</div>
            <div class="stat-value" style="color: #d97706;">{{ $stats['total_menunggu'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Batal</div>
            <div class="stat-value" style="color: #dc2626;">{{ $stats['total_batal'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Nilai</div>
            <div class="stat-value">Rp {{ number_format($stats['total_nilai'], 0, ',', '.') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Item</div>
            <div class="stat-value">{{ $stats['total_item'] }}</div>
        </div>
    </div>

    @if($paymentMethods && count($paymentMethods) > 0)
    <div class="section-title">💳 Metode Pembayaran</div>
    <table>
        <thead>
            <tr>
                <th style="width: 40%;">Metode</th>
                <th style="width: 35%; text-align: right;">Total</th>
                <th style="width: 25%; text-align: center;">Jumlah Transaksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($paymentMethods as $method)
            <tr>
                <td><strong>{{ $method['metode'] }}</strong></td>
                <td class="text-right">Rp {{ number_format($method['total'], 0, ',', '.') }}</td>
                <td style="text-align: center;">{{ $method['count'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($transaksi && count($transaksi) > 0)
    <div class="section-title">📝 Detail Transaksi</div>
    <table>
        <thead>
            <tr>
                <th style="width: 12%;">No. Transaksi</th>
                <th style="width: 10%;">Jam</th>
                <th style="width: 15%;">Pelanggan</th>
                <th style="width: 15%;">Kasir</th>
                <th style="width: 12%;">Metode</th>
                <th style="width: 20%; text-align: right;">Total</th>
                <th style="width: 12%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaksi as $t)
            <tr>
                <td><strong>{{ $t['nomor_transaksi'] }}</strong></td>
                <td>{{ date('H:i', strtotime($t['tanggal'])) }}</td>
                <td>{{ $t['pelanggan']['nama'] ?? '-' }}</td>
                <td>{{ $t['kasir']['nama'] ?? '-' }}</td>
                <td>{{ $t['metode_bayar'] }}</td>
                <td class="text-right">Rp {{ number_format($t['total'], 0, ',', '.') }}</td>
                <td>
                    <span class="status {{ strtolower($t['status_pembayaran']) }}">
                        {{ $t['status_pembayaran'] }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        <p>Laporan ini dihasilkan pada: {{ date('d F Y H:i:s') }}</p>
    </div>
</body>
</html>
