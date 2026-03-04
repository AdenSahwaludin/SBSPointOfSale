<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Harian</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.6;
            font-size: 12px;
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
            font-size: 22px;
            font-weight: bold;
            color: #10b981;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 16px;
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
        }
        .stat-card {
            display: inline-block;
            width: 14%;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 10px 5px;
            border-radius: 4px;
            text-align: center;
            margin-right: 0.5%;
            margin-bottom: 10px;
            vertical-align: top;
        }
        .stat-label {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            color: #6c757d;
            margin-bottom: 5px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .stat-value {
            display: block;
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        .highlight-green { color: #10b981; }
        .highlight-red { color: #ef4444; }
        
        .section-title {
            font-size: 13px;
            color: #374151;
            margin-bottom: 10px;
            margin-top: 25px;
            border-left: 4px solid #10b981;
            padding-left: 10px;
            font-weight: bold;
            clear: both;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: 600;
            text-align: left;
            padding: 10px 8px;
            border-bottom: 2px solid #e5e7eb;
            text-transform: uppercase;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-right { text-align: right; }
        
        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-lunas { background-color: #d1fae5; color: #065f46; }
        .badge-menunggu { background-color: #fef3c7; color: #92400e; }
        .badge-batal { background-color: #fee2e2; color: #991b1b; }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #9ca3af;
            text-align: right;
        }
        .payment-summary {
            width: 50%;
            margin-bottom: 20px;
            display: inline-block;
            vertical-align: top;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">Sari Bumi Sakti</div>
        <div class="report-title">Laporan Harian</div>
        <div class="period-info">{{ date('l, d F Y', strtotime($tanggal)) }}</div>
    </div>

    <!-- 6-Col Stats -->
    <div class="stats-container">
        <div class="stat-card">
            <span class="stat-label">Transaksi</span>
            <span class="stat-value">{{ number_format($stats['total_transaksi'], 0, ',', '.') }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Lunas</span>
            <span class="stat-value highlight-green">{{ number_format($stats['total_lunas'], 0, ',', '.') }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Menunggu</span>
            <span class="stat-value">{{ number_format($stats['total_menunggu'], 0, ',', '.') }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Batal</span>
            <span class="stat-value highlight-red">{{ number_format($stats['total_batal'], 0, ',', '.') }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Pendapatan</span>
            <span class="stat-value highlight-green">{{ number_format($stats['total_nilai'], 0, ',', '.') }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Item Terjual</span>
            <span class="stat-value">{{ number_format($stats['total_item'], 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Payment Methods -->
    <div class="payment-summary">
        <div class="section-title">Metode Pembayaran</div>
        <table style="width: 95%;">
            <thead>
                <tr>
                    <th>Metode</th>
                    <th class="text-right">Transaksi</th>
                    <th class="text-right">Nilai</th>
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

    <!-- Transaction List -->
    <div style="clear: both;"></div>
    <div class="section-title">Detail Transaksi</div>

    <table>
        <thead>
            <tr>
                <th width="12%">Jam</th>
                <th width="15%">No. Transaksi</th>
                <th width="18%">Pelanggan</th>
                <th width="15%">Kasir</th>
                <th width="15%">Metode</th>
                <th width="15%" class="text-right">Total</th>
                <th width="10%" class="text-right">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksi as $t)
                <tr>
                    <td>{{ date('H:i', strtotime($t['tanggal'])) }}</td>
                    <td style="font-family: monospace; font-weight: bold;">{{ $t['nomor_transaksi'] }}</td>
                    <td>{{ $t['pelanggan']['nama'] ?? '-' }}</td>
                    <td>{{ $t['kasir']['nama'] ?? '-' }}</td>
                    <td>{{ $t['metode_bayar'] }}</td>
                    <td class="text-right">Rp {{ number_format($t['total'], 0, ',', '.') }}</td>
                    <td class="text-right">
                        <span class="badge badge-{{ strtolower($t['status_pembayaran']) }}">
                            {{ $t['status_pembayaran'] }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">Tidak ada transaksi hari ini</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ date('d F Y H:i') }} | Sari Bumi Sakti POS System
    </div>
</body>
</html>
