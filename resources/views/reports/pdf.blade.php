<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Tracking Tamu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .info-box {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .info-box table {
            width: 100%;
        }
        .info-box td {
            padding: 3px 0;
        }
        .stats {
            margin-bottom: 20px;
        }
        .stats table {
            width: 100%;
            border-collapse: collapse;
        }
        .stats td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            background-color: #f8f9fa;
        }
        .stats .value {
            font-size: 24px;
            font-weight: bold;
            color: #0d6efd;
        }
        .stats .label {
            font-size: 11px;
            color: #666;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th {
            background-color: #0d6efd;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        table.data-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
            font-size: 10px;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #198754;
            color: white;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN TRACKING TAMU</h1>
        <p>Visitor Management System - UNHAN</p>
        <p>Periode: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</p>
        @if($location)
            <p>Lokasi: {{ $location->name }}</p>
        @else
            <p>Lokasi: Semua Lokasi</p>
        @endif
    </div>

    <div class="info-box">
        <table>
            <tr>
                <td width="150"><strong>Tanggal Cetak:</strong></td>
                <td>{{ now()->format('d/m/Y H:i:s') }}</td>
            </tr>
            <tr>
                <td><strong>Total Records:</strong></td>
                <td>{{ $logs->count() }} data</td>
            </tr>
        </table>
    </div>

    <div class="stats">
        <table>
            <tr>
                <td width="50%">
                    <div class="value">{{ $stats['total_entries'] }}</div>
                    <div class="label">Total Masuk</div>
                </td>
                <td width="50%">
                    <div class="value">{{ $stats['total_exits'] }}</div>
                    <div class="label">Total Keluar</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="15%">Waktu</th>
                <th width="20%">Nama Tamu</th>
                <th width="20%">Institusi</th>
                <th width="18%">Lokasi</th>
                <th width="10%">Aksi</th>
                <th width="10%">Status</th>
                <th width="17%">RFID UID</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $log->user->name }}</td>
                <td>{{ $log->user->institution ?? '-' }}</td>
                <td>{{ $log->location->name }}</td>
                <td>
                    @if($log->action_type === 'entry')
                        <span class="badge badge-success">Masuk</span>
                    @else
                        <span class="badge badge-danger">Keluar</span>
                    @endif
                </td>
                <td>{{ $log->status === 'accepted' ? 'Diterima' : ucfirst($log->status) }}</td>
                <td><code>{{ $log->rfid_uid }}</code></td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px; color: #999;">
                    Tidak ada data untuk periode yang dipilih
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis oleh VMS UNHAN pada {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
