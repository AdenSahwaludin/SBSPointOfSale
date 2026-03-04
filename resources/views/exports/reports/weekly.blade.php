<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Mingguan</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.5;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #10b981;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #10b981;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 14px;
            font-weight: 600;
            color: #555;
            margin-bottom: 5px;
        }
        .period-info {
            font-size: 12px;
            color: #777;
        }
        
        .stats-container {
            width: 100%;
            margin-bottom: 20px;
            display: table;
            border-spacing: 5px;
        }
        .stat-card {
            display: table-cell;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
            width: 16%;
        }
        .stat-label {
            display: block;
            font-size: 9px;
            text-transform: uppercase;
            color: #6c757d;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .stat-value {
            display: block;
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }
        .highlight-green { color: #10b981; }
        .highlight-red { color: #ef4444; }
        .highlight-orange { color: #f59e0b; }
        
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 13px;
            color: #374151;
            margin-bottom: 10px;
            border-left: 4px solid #10b981;
            padding-left: 10px;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: 600;
            text-align: left;
            padding: 8px;
            border-bottom: 2px solid #e5e7eb;
            text-transform: uppercase;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
        }
        tr:nth-child(even) { background-color: #f9fafb; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .col-2 { width: 48%; display: inline-block; vertical-align: top; margin-right: 2%; }
        .col-2:last-child { margin-right: 0; }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            font-size: 9px;
            color: #9ca3af;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">Sari Bumi Sakti</div>
        <div class="report-title">Laporan Mingguan</div>
        <div class="period-info">{{ date('d M Y', strtotime($start_date)) }} - {{ date('d M Y', strtotime($end_date)) }}</div>
    </div>

    <!-- Stats -->
    <div class="stats-container">
        <div class="stat-card">
            <span class="stat-label">Transaksi</span>
            <span class="stat-value">{{ number_format($stats['total_transaksi']) }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Lunas</span>
            <span class="stat-value highlight-green">{{ number_format($stats['total_lunas']) }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Menunggu</span>
            <span class="stat-value highlight-orange">{{ number_format($stats['total_menunggu']) }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Batal</span>
            <span class="stat-value highlight-red">{{ number_format($stats['total_batal']) }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Total Nilai</span>
            <span class="stat-value highlight-green">{{ number_format($stats['total_nilai']) }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Total Item</span>
            <span class="stat-value">{{ number_format($stats['total_item']) }}</span>
        </div>
    </div>

    <!-- Daily Breakdown -->
    <div class="section">
        <div class="section-title">Rincian Per Hari</div>
        <table>
            <thead>
                <tr>
                    <th width="20%">Tanggal</th>
                    <th width="15%">Hari</th>
                    <th width="15%" class="text-right">Transaksi</th>
                    <th width="25%" class="text-right">Pendapatan</th>
                    <th width="25%" class="text-right">Rata-rata</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dailyData as $day)
                <tr>
                    <td>{{ date('d/m/Y', strtotime($day['tanggal'])) }}</td>
                    <td>{{ date('l', strtotime($day['tanggal'])) }}</td>
                    <td class="text-right">{{ $day['count'] }}</td>
                    <td class="text-right">Rp {{ number_format($day['total'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ $day['count'] > 0 ? number_format($day['total'] / $day['count'], 0, ',', '.') : 0 }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Top Performers & Methods -->
    <div style="margin-bottom: 20px;">
        <div class="col-2">
            <div class="section-title">Top 5 Kasir</div>
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th class="text-right">Trx</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topKasir as $k)
                    <tr>
                        <td>{{ $k['nama'] }}</td>
                        <td class="text-right">{{ $k['count'] }}</td>
                        <td class="text-right">Rp {{ number_format($k['total'], 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center">No Data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="col-2">
            <div class="section-title">Metode Pembayaran</div>
            <table>
                <thead>
                    <tr>
                        <th>Metode</th>
                        <th class="text-right">Trx</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paymentMethods as $pm)
                    <tr>
                        <td>{{ $pm['metode'] }}</td>
                        <td class="text-right">{{ $pm['count'] }}</td>
                        <td class="text-right">Rp {{ number_format($pm['total'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Customers -->
    <div class="section" style="clear: both;">
        <div class="section-title">Top 5 Pelanggan</div>
        <table>
            <thead>
                <tr>
                    <th width="40%">Nama Pelanggan</th>
                    <th width="20%" class="text-right">Jumlah Transaksi</th>
                    <th width="40%" class="text-right">Total Belanja</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topPelanggan as $p)
                <tr>
                    <td>{{ $p['nama'] }}</td>
                    <td class="text-right">{{ $p['count'] }}</td>
                    <td class="text-right">Rp {{ number_format($p['total'], 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center">Tidak ada data pelanggan</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">Generated on {{ date('d F Y H:i') }} | Sari Bumi Sakti POS System</div>
</body>
</html>
