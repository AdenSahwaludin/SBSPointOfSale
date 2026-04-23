<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Mingguan - Sari Bumi Sakti</title>
    <style>
        @page { margin: 40px 50px; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #2b2b2b;
            line-height: 1.4;
            font-size: 10px;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        /* Typography & Hierarchy */
        h1, h2, h3, h4, p { margin: 0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-muted { color: #6b7280; }

        /* Corporate Header */
        .header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #000000;
        }
        .header table { width: 100%; border: none; margin: 0; }
        .header td { border: none; padding: 0; background: none; }
        .company-name {
            font-size: 28px;
            font-weight: 800;
            color: #000000;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .report-title {
            font-size: 14px;
            font-weight: normal;
            color: #4b5563;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 5px;
        }
        .period-info {
            font-size: 11px;
            color: #000000;
            margin-top: 5px;
        }

        /* Executive Summary Stats */
        .exec-summary {
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 20px;
        }
        .exec-summary table {
            width: 100%;
            border-collapse: collapse;
        }
        .exec-summary td {
            vertical-align: top;
            width: 33.33%;
            padding-right: 20px;
            border-right: 1px solid #e5e7eb;
        }
        .exec-summary td:last-child {
            padding-right: 0;
            border-right: none;
        }
        .stat-group { margin-bottom: 15px; }
        .stat-group:last-child { margin-bottom: 0; }
        .stat-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #000000;
        }
        .stat-value.highlight {
            font-size: 20px;
        }

        /* Sections */
        .section-title {
            font-size: 12px;
            color: #000000;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 25px;
            margin-bottom: 10px;
            padding-bottom: 4px;
            border-bottom: 1px solid #000000;
            font-weight: bold;
            page-break-after: avoid;
        }

        /* Minimalist Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 20px;
        }
        table.data-table th {
            color: #000000;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 4px;
            border-bottom: 1px solid #000000;
            text-align: left;
            font-size: 9px;
        }
        table.data-table td {
            padding: 8px 4px;
            border-bottom: 1px solid #e5e7eb;
            color: #1f2937;
        }
        table.data-table tr:last-child td { border-bottom: 1px solid #000000; }
        table.data-table th.text-right, table.data-table td.text-right { text-align: right; }

        /* Status Text */
        .status-text {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Layout Helpers */
        .page-break { page-break-after: always; }
        .two-column-wrapper { width: 100%; display: table; margin-bottom: 20px; }
        .two-column { display: table-cell; width: 48%; vertical-align: top; }
        .gap { display: table-cell; width: 4%; }
        
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #000000;
            font-size: 8px;
            color: #4b5563;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>

<body>
    <!-- HEADER -->
    <div class="header">
        <table>
            <tr>

                <td>
                    <div class="company-name">Sari Bumi Sakti</div>
                    <div class="report-title">Laporan Mingguan Operasional & Keuangan</div>
                </td>
                <td class="text-right" style="vertical-align: bottom;">
                    <div class="period-info">
                        <strong>PERIODE:</strong> {{ date('d M Y', strtotime($start_date)) }} - {{ date('d M Y', strtotime($end_date)) }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- EXECUTIVE SUMMARY -->
    <div class="exec-summary">
        <table>
            <tr>
                <td>
                    <div class="stat-group">
                        <div class="stat-label">Total Pendapatan (Lunas)</div>
                        <div class="stat-value highlight">Rp {{ number_format($stats['total_nilai'], 0, ',', '.') }}</div>
                    </div>
                </td>
                <td>
                    <div class="stat-group">
                        <div class="stat-label">Total Transaksi / Item</div>
                        <div class="stat-value">{{ number_format($stats['total_transaksi'], 0, ',', '.') }} / {{ number_format($stats['total_item'], 0, ',', '.') }}</div>
                    </div>
                    <div class="stat-group" style="margin-top: 15px;">
                        <div class="stat-label">Rata-rata Nilai Order (AOV)</div>
                        <div class="stat-value">Rp {{ number_format($insights['aov'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                </td>
                <td>
                    <div class="stat-group">
                        <div class="stat-label">Transaksi Berhasil (Lunas)</div>
                        <div class="stat-value">{{ number_format($stats['total_lunas'], 0, ',', '.') }}</div>
                    </div>
                    <div class="stat-group" style="margin-top: 15px;">
                        <div class="stat-label">Tertunda / Dibatalkan</div>
                        <div class="stat-value">{{ number_format($stats['total_menunggu'], 0, ',', '.') }} / {{ number_format($stats['total_batal'], 0, ',', '.') }}</div>
                    </div>
                </td>
            </tr>
        </table>
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

    <!-- Insights & Top Customers -->
    <div style="margin-bottom: 20px; clear: both;">
        <div class="col-2">
            <div class="section-title">Top 5 Pelanggan</div>
            <table>
                <thead>
                    <tr>
                        <th width="50%">Nama Pelanggan</th>
                        <th width="20%" class="text-right">Trx</th>
                        <th width="30%" class="text-right">Total</th>
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
        <div class="col-2">
            <div class="section-title">Insight & Analisa</div>
            <table>
                <tbody>
                    <tr>
                        <td style="font-weight: bold; width: 60%;">Rata-rata Order (AOV)</td>
                        <td class="text-right text-indigo-700 font-bold">Rp {{ number_format($insights['aov'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Pertumbuhan (vs Mgg Lalu)</td>
                        <td class="text-right">
                            <span class="{{ $insights['growth'] >= 0 ? 'highlight-green' : 'highlight-red' }}" style="font-weight: bold;">
                                {{ $insights['growth'] >= 0 ? '+' : '' }}{{ number_format($insights['growth'], 2, ',', '.') }}%
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Metode Dominan</td>
                        <td class="text-right text-orange-700 font-bold">{{ $insights['dominant_method'] }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Hari Terbaik</td>
                        <td class="text-right text-pink-700 font-bold">{{ $insights['best_day'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">Generated on {{ date('d F Y H:i') }} | Sari Bumi Sakti POS System</div>
</body>
</html>
