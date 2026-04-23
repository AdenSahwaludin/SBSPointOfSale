<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Bulanan - Sari Bumi Sakti</title>
    <style>
        @page {
            margin: 40px 50px;
        }

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
        h1,
        h2,
        h3,
        h4,
        p {
            margin: 0;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .text-muted {
            color: #6b7280;
        }

        /* Corporate Header */
        .header {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000000;
        }

        .header table {
            width: 100%;
            border: none;
            margin: 0;
        }

        .header td {
            border: none;
            padding: 0;
            background: none;
        }

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

        .exec-summary {
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
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

        .stat-group {
            margin-bottom: 15px;
        }

        .stat-group:last-child {
            margin-bottom: 0;
        }

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

        table.data-table tr:last-child td {
            border-bottom: 1px solid #000000;
        }

        table.data-table th.text-right,
        table.data-table td.text-right {
            text-align: right;
        }

        /* Status Text */
        .status-text {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Layout Helpers */
        .page-break {
            page-break-after: always;
        }

        .two-column-wrapper {
            width: 100%;
            display: table;
            margin-bottom: 20px;
        }

        .two-column {
            display: table-cell;
            width: 48%;
            vertical-align: top;
        }

        .gap {
            display: table-cell;
            width: 4%;
        }

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
                @if(file_exists(public_path('assets/images/Logo_Cap_Daun_Kayu_Putih.png')))
                    <td width="60" style="vertical-align: top;">
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('assets/images/Logo_Cap_Daun_Kayu_Putih.png'))) }}"
                            width="50">
                    </td>
                @endif
                <td>
                    <div class="company-name">Sari Bumi Sakti</div>
                    <div class="report-title">Laporan Bulanan Operasional & Keuangan</div>
                </td>
                <td class="text-right" style="vertical-align: bottom;">
                    <div class="period-info">
                        <strong>PERIODE BULAN:</strong> <span
                            style="text-transform: uppercase;">{{ $bulan_display }}</span>
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
                        <div class="stat-value highlight">Rp {{ number_format($stats['total_nilai'], 0, ',', '.') }}
                        </div>
                    </div>
                </td>
                <td>
                    <div class="stat-group">
                        <div class="stat-label">Total Transaksi / Item</div>
                        <div class="stat-value">{{ number_format($stats['total_transaksi'], 0, ',', '.') }} /
                            {{ number_format($stats['total_item'], 0, ',', '.') }}
                        </div>
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
                        <div class="stat-value">{{ number_format($stats['total_menunggu'], 0, ',', '.') }} /
                            {{ number_format($stats['total_batal'], 0, ',', '.') }}
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div class="two-column-wrapper">
        <div class="two-column">
            <div class="section-title">Kinerja Harian</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="40%">Tanggal</th>
                        <th width="20%" class="text-right">Trx</th>
                        <th width="40%" class="text-right">Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dailyData as $day)
                        <tr>
                            <td class="font-bold">{{ $day['hari'] }}</td>
                            <td class="text-right">{{ $day['count'] }}</td>
                            <td class="text-right">Rp {{ number_format($day['total'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">Tidak ada data tersedia</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="gap"></div>
        <div class="two-column">
            <div class="section-title">Metode Pembayaran (Lunas)</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="40%">Metode</th>
                        <th width="20%" class="text-right">Trx</th>
                        <th width="40%" class="text-right">Volume</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paymentMethods as $method)
                        <tr>
                            <td class="font-bold">{{ $method['metode'] }}</td>
                            <td class="text-right">{{ $method['count'] }}</td>
                            <td class="text-right font-bold">Rp {{ number_format($method['total'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">Tidak ada data tersedia</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="two-column-wrapper">
        <div class="two-column">
            <div class="section-title">Top 5 Pelanggan</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="50%">Pelanggan</th>
                        <th width="15%" class="text-right">Trx</th>
                        <th width="35%" class="text-right">Total Belanja</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topPelanggan as $plg)
                        <tr>
                            <td class="font-bold">{{ $plg['nama'] }}</td>
                            <td class="text-right">{{ $plg['transaksi'] }}</td>
                            <td class="text-right">Rp {{ number_format($plg['total_belanja'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">Tidak ada data pelanggan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="gap"></div>
        <div class="two-column">
            <div class="section-title">Top 5 Kasir</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="50%">Kasir</th>
                        <th width="15%" class="text-right">Trx</th>
                        <th width="35%" class="text-right">Total Penjualan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topKasir as $ksr)
                        <tr>
                            <td class="font-bold">{{ $ksr['nama'] }}</td>
                            <td class="text-right">{{ $ksr['transaksi'] }}</td>
                            <td class="text-right">Rp {{ number_format($ksr['total_penjualan'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">Tidak ada data kasir</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="two-column-wrapper" style="margin-bottom: 0;">
        <div class="two-column">
            <div class="section-title">Insight & Analisa (vs Bulan Lalu)</div>
            <table class="data-table">
                <tbody>
                    <tr>
                        <td width="60%" class="font-bold">Pertumbuhan Pendapatan</td>
                        <td width="40%" class="text-right">
                            @if (($insights['growth'] ?? 0) > 0)
                                +{{ $insights['growth'] }}%
                            @elseif (($insights['growth'] ?? 0) < 0)
                                {{ $insights['growth'] }}%
                            @else
                                0%
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="font-bold">Pendapatan Bulan Lalu</td>
                        <td class="text-right">Rp {{ number_format($insights['last_period_value'] ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>



    <!-- DETAILED RECORDS -->
    <div class="section-title">Rincian Transaksi</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="15%">No. Transaksi</th>
                <th width="15%">Tanggal/Waktu</th>
                <th width="22%">Pelanggan</th>
                <th width="18%">Kasir</th>
                <th width="18%" class="text-right">Total</th>
                <th width="12%" class="text-right">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksi as $t)
                <tr>
                    <td style="font-family: monospace;">{{ $t['nomor_transaksi'] }}</td>
                    <td>{{ date('d/m H:i', strtotime($t['tanggal'])) }}</td>
                    <td>{{ $t['pelanggan']['nama'] ?? 'Umum' }}</td>
                    <td>{{ $t['kasir']['nama'] ?? '-' }}</td>
                    <td class="text-right font-bold">Rp {{ number_format($t['total'], 0, ',', '.') }}</td>
                    <td class="text-right status-text">{{ $t['status_pembayaran'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted" style="padding: 20px;">Tidak ada riwayat transaksi pada
                        periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        RAHASIA &bull; SARI BUMI SAKTI &bull; DICETAK PADA {{ date('d M Y, H:i') }}
    </div>
</body>

</html>