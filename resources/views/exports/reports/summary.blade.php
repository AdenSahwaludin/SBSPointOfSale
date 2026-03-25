<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Ringkasan Laporan</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.6;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #10b981;
            padding-bottom: 10px;
        }

        .company-name {
            font-size: 24px;
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
            font-size: 10px;
            color: #777;
        }

        .stats-container {
            width: 100%;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .stat-card {
            display: inline-block;
            width: 30%;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
            margin-right: 2%;
            margin-bottom: 10px;
            vertical-align: top;
            font-size: 9px;
        }

        .stat-card:nth-child(3n) {
            margin-right: 0;
        }

        .stat-label {
            display: block;
            font-size: 9px;
            text-transform: uppercase;
            color: #6c757d;
            margin-bottom: 3px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .stat-value {
            display: block;
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }

        .text-green {
            color: #10b981;
        }

        .text-orange {
            color: #f59e0b;
        }

        .text-red {
            color: #ef4444;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
            font-size: 9px;
            page-break-inside: avoid;
        }

        th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: 600;
            text-align: left;
            padding: 8px 6px;
            border-bottom: 1.5px solid #e5e7eb;
            text-transform: uppercase;
        }

        td {
            padding: 7px 6px;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
        }

        tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .badge-lunas {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-menunggu {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-batal {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 8px;
            color: #9ca3af;
            text-align: right;
        }

        .text-right {
            text-align: right;
        }

        .font-mono {
            font-family: monospace;
        }

        .section-title {
            font-size: 12px;
            color: #374151;
            margin-top: 15px;
            margin-bottom: 8px;
            border-left: 3px solid #10b981;
            padding-left: 8px;
            font-weight: bold;
            page-break-after: avoid;
        }

        .page-break {
            page-break-after: always;
        }

        .two-column {
            display: inline-block;
            width: 48%;
            vertical-align: top;
            margin-right: 2%;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="company-name">Sari Bumi Sakti</div>
        <div class="report-title">Ringkasan Laporan</div>
        @if ($startDate && $endDate)
            <div class="period-info">
                Periode: {{ date('d/m/Y', strtotime($startDate)) }} - {{ date('d/m/Y', strtotime($endDate)) }}
                @if ($status && $status !== 'all')
                    | Status: <span style="text-transform: uppercase;">{{ $status }}</span>
                @endif
            </div>
        @endif
    </div>

    <!-- Stats Grid -->
    <div class="stats-container">
        <div class="stat-card">
            <span class="stat-label">Total Transaksi</span>
            <span class="stat-value">{{ number_format($stats['total_transaksi'], 0, ',', '.') }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Total Pendapatan</span>
            <span class="stat-value text-green">Rp {{ number_format($stats['total_pendapatan'], 0, ',', '.') }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Rata-rata Nilai</span>
            <span class="stat-value">Rp
                {{ $stats['total_transaksi'] > 0 ? number_format($stats['total_pendapatan'] / $stats['total_transaksi'], 0, ',', '.') : 0 }}</span>
        </div>

        <div class="stat-card">
            <span class="stat-label">Lunas</span>
            <span class="stat-value text-green">{{ number_format($stats['total_lunas'], 0, ',', '.') }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Menunggu</span>
            <span class="stat-value text-orange">{{ number_format($stats['total_menunggu'], 0, ',', '.') }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Batal</span>
            <span class="stat-value text-red">{{ number_format($stats['total_batal'], 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Trend Penjualan -->
    <div class="section-title">Trend Penjualan Harian</div>
    <table>
        <thead>
            <tr>
                <th width="30%">Tanggal</th>
                <th width="30%" class="text-right">Jumlah Transaksi</th>
                <th width="40%" class="text-right">Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($salesTrend as $trend)
                <tr>
                    <td>{{ date('d/m/Y', strtotime($trend->date)) }}</td>
                    <td class="text-right">{{ $trend->count }}</td>
                    <td class="text-right">Rp {{ number_format($trend->revenue, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">Tidak ada data penjualan</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Metode Pembayaran -->
    <div class="section-title">Metode Pembayaran</div>
    <table>
        <thead>
            <tr>
                <th width="35%">Metode</th>
                <th width="30%" class="text-right">Jumlah Transaksi</th>
                <th width="35%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($paymentMethods as $method)
                <tr>
                    <td>{{ $method->method }}</td>
                    <td class="text-right">{{ $method->count }}</td>
                    <td class="text-right">Rp {{ number_format($method->total, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">Tidak ada data metode pembayaran</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Distribusi Status -->
    <div class="section-title">Distribusi Status Transaksi</div>
    <table>
        <thead>
            <tr>
                <th width="50%">Status</th>
                <th width="50%" class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($statusDistribution as $dist)
                <tr>
                    <td>
                        <span class="badge badge-{{ strtolower($dist->status) }}">
                            {{ $dist->status }}
                        </span>
                    </td>
                    <td class="text-right">{{ $dist->count }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" style="text-align: center;">Tidak ada data distribusi status</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Top Produk -->
    <div class="section-title">Top 5 Produk Terlaris</div>
    <table>
        <thead>
            <tr>
                <th width="40%">Produk</th>
                <th width="30%" class="text-right">Jumlah Terjual</th>
                <th width="30%" class="text-right">Total Penjualan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topProducts as $product)
                <tr>
                    <td>{{ $product->nama }}</td>
                    <td class="text-right">{{ $product->total_qty }}</td>
                    <td class="text-right">Rp {{ number_format($product->total_revenue, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">Tidak ada data produk terjual</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Page Break -->
    <div class="page-break"></div>

    <!-- Detail Transaksi -->
    <div class="section-title">Detail Transaksi</div>
    <table>
        <thead>
            <tr>
                <th width="12%">No. Transaksi</th>
                <th width="12%">Tanggal</th>
                <th width="18%">Pelanggan</th>
                <th width="15%">Kasir</th>
                <th width="18%" class="text-right">Total</th>
                <th width="12%" class="text-right">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksi as $t)
                <tr>
                    <td class="font-mono"><strong>{{ $t->nomor_transaksi }}</strong></td>
                    <td>{{ date('d/m/y H:i', strtotime($t->tanggal)) }}</td>
                    <td>{{ $t->pelanggan?->nama ?? '-' }}</td>
                    <td>{{ $t->kasir?->nama ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($t->total, 0, ',', '.') }}</td>
                    <td class="text-right">
                        <span class="badge badge-{{ strtolower($t->status_pembayaran) }}">
                            {{ $t->status_pembayaran }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 15px;">Tidak ada data transaksi</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ date('d F Y H:i') }} | Sari Bumi Sakti POS System
    </div>
</body>

</html>
