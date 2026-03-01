<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ringkasan Laporan{{ $startDate ? ' - ' . date('d/m/Y', strtotime($startDate)) . ' hingga ' . date('d/m/Y', strtotime($endDate)) : '' }}</title>
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
        .header p {
            font-size: 14px;
            color: #666;
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
            gap: 20px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        .stat-card {
            flex: 1;
            min-width: 150px;
            border: 1px solid #e5e7eb;
            padding: 15px;
            border-radius: 8px;
            background-color: #f9fafb;
        }
        .stat-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .stat-value {
            font-size: 22px;
            font-weight: bold;
            color: #10b981;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 12px;
        }
        thead {
            background-color: #10b981;
            color: white;
        }
        th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border: 1px solid #ddd;
        }
        td {
            padding: 10px 12px;
            border: 1px solid #ddd;
        }
        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        tbody tr:hover {
            background-color: #f3f4f6;
        }
        .status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
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
            font-size: 11px;
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
        <h1>📊 Ringkasan Laporan</h1>
        <p>Sari Bumi Sakti POS System</p>
    </div>

    @if($startDate && $endDate)
    <div class="period">
        <strong>Periode:</strong> {{ date('d F Y', strtotime($startDate)) }} s.d. {{ date('d F Y', strtotime($endDate)) }}
        @if($status && $status !== 'all')
        | <strong>Status:</strong> {{ $status }}
        @endif
    </div>
    @endif

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Transaksi</div>
            <div class="stat-value">{{ $stats['total_transaksi'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Pendapatan</div>
            <div class="stat-value">Rp {{ number_format($stats['total_pendapatan'], 0, ',', '.') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Status Lunas</div>
            <div class="stat-value" style="color: #059669;">{{ $stats['total_lunas'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Status Menunggu</div>
            <div class="stat-value" style="color: #d97706;">{{ $stats['total_menunggu'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Status Batal</div>
            <div class="stat-value" style="color: #dc2626;">{{ $stats['total_batal'] }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">No. Transaksi</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 15%;">Pelanggan</th>
                <th style="width: 12%;">Kasir</th>
                <th style="width: 15%;">Total</th>
                <th style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksi as $t)
            <tr>
                <td><strong>{{ $t['nomor_transaksi'] }}</strong></td>
                <td>{{ date('d/m/Y H:i', strtotime($t['tanggal'])) }}</td>
                <td>{{ $t['pelanggan']['nama'] ?? '-' }}</td>
                <td>{{ $t['kasir']['nama'] ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($t['total'], 0, ',', '.') }}</td>
                <td>
                    <span class="status {{ strtolower($t['status_pembayaran']) }}">
                        {{ $t['status_pembayaran'] }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; color: #999;">Tidak ada data transaksi</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini dihasilkan pada: {{ date('d F Y H:i:s') }}</p>
    </div>
</body>
</html>
