<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Mingguan - {{ date('d F Y', strtotime($start_date)) }}</title>
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
            font-size: 16px;
            color: #666;
            font-weight: 600;
            margin-top: 10px;
        }
        .period {
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
            margin-bottom: 20px;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Laporan Mingguan</h1>
        <h2>{{ date('d F Y', strtotime($start_date)) }} s.d. {{ date('d F Y', strtotime($end_date)) }}</h2>
        <p>Sari Bumi Sakti POS System</p>
    </div>

    <div class="period">
        <strong>Periode:</strong> {{ date('d F Y', strtotime($start_date)) }} hingga {{ date('d F Y', strtotime($end_date)) }} ({{ isset($week_display) ? $week_display : 'Minggu ini' }})
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

    @if(isset($dailyData) && count($dailyData) > 0)
    <div class="section-title">📅 Penjualan Per Hari</div>
    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Tanggal</th>
                <th style="width: 20%;">Hari</th>
                <th style="width: 30%; text-align: right;">Total Penjualan</th>
                <th style="width: 30%; text-align: center;">Jumlah Transaksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dailyData as $day)
            <tr>
                <td>{{ date('d F Y', strtotime($day['tanggal'])) }}</td>
                <td>{{ date('l', strtotime($day['tanggal'])) }}</td>
                <td class="text-right">Rp {{ number_format($day['total'], 0, ',', '.') }}</td>
                <td style="text-align: center;">{{ $day['count'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(isset($paymentMethods) && count($paymentMethods) > 0)
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

    @if(isset($topKasir) && count($topKasir) > 0)
    <div class="section-title">👤 Top Kasir</div>
    <table>
        <thead>
            <tr>
                <th style="width: 40%;">Nama Kasir</th>
                <th style="width: 35%; text-align: right;">Total Penjualan</th>
                <th style="width: 25%; text-align: center;">Transaksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topKasir as $kasir)
            <tr>
                <td><strong>{{ $kasir['nama'] }}</strong></td>
                <td class="text-right">Rp {{ number_format($kasir['total'], 0, ',', '.') }}</td>
                <td style="text-align: center;">{{ $kasir['count'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(isset($topPelanggan) && count($topPelanggan) > 0)
    <div class="section-title">🛍️ Top Pelanggan</div>
    <table>
        <thead>
            <tr>
                <th style="width: 40%;">Nama Pelanggan</th>
                <th style="width: 35%; text-align: right;">Total Pembelian</th>
                <th style="width: 25%; text-align: center;">Transaksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topPelanggan as $pelanggan)
            <tr>
                <td><strong>{{ $pelanggan['nama'] }}</strong></td>
                <td class="text-right">Rp {{ number_format($pelanggan['total'], 0, ',', '.') }}</td>
                <td style="text-align: center;">{{ $pelanggan['count'] }}</td>
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
