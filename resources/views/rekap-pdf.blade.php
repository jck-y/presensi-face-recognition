<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Presensi - PT Gloria Sumber Damai</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 12px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #999;
            padding: 6px 8px;
            text-align: left;
            font-size: 11px;
        }
        th {
            background-color: #e8f0fe;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 11px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Rekapitulasi Presensi</h2>
        <p>PT Gloria Sumber Damai</p>
        <p>Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Karyawan</th>
                <th>NIP</th>
                <th>Divisi</th>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Jenis</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekap as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->karyawan->nama_karyawan }}</td>
                    <td>{{ $row->karyawan->nip }}</td>
                    <td>{{ $row->karyawan->divisi->nama_divisi }}</td>
                    <td>{{ $row->tanggal }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->waktu)->format('H:i') }}</td>
                    <td>{{ ucfirst($row->jenis_absensi) }}</td>
                    <td>{{ $row->status_absensi }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">Tidak ada data presensi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Total Data: {{ $rekap->count() }} records
    </div>
</body>
</html>
