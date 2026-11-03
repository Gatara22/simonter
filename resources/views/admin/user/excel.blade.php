<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
        }
        th {
            background-color: #1a3a63;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            border: 1px solid #000000;
            padding: 8px;
        }
        td {
            border: 1px solid #cccccc;
            padding: 6px;
            vertical-align: middle;
        }
        .text-center {
            text-align: center;
        }
        .kop-title {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
        }
        .kop-sub {
            font-size: 11pt;
            text-align: center;
            color: #555555;
        }
        .text-format {
            mso-number-format: "\@";
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="7" class="kop-title">PENGADILAN AGAMA JAKARTA UTARA</td>
        </tr>
        <tr>
            <td colspan="7" class="kop-sub">SiMonter - Sistem Monitoring Evaluasi Terpadu</td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: center; font-weight: bold; font-size: 12pt;">LAPORAN DATA PENGGUNA SISTEM</td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: center; color: #555;">Periode: {{ $periodeString ?? 'Semua Waktu' }} | Tanggal Cetak: {{ date('d/m/Y H:i') }} WIB</td>
        </tr>
        <tr>
            <td colspan="7"></td>
        </tr>
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th style="width: 220px;">Nama Lengkap</th>
                <th style="width: 180px;">NIP</th>
                <th style="width: 200px;">Email</th>
                <th style="width: 140px;">Kontak</th>
                <th style="width: 110px;">Jabatan</th>
                <th style="width: 140px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $u)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $u->name }}</td>
                    <td class="text-center text-format">{{ $u->nip }}</td>
                    <td>{{ $u->email ?? '-' }}</td>
                    <td class="text-center text-format">{{ $u->kontak ?? '-' }}</td>
                    <td class="text-center">{{ $u->jabatan }}</td>
                    <td class="text-center">{{ $u->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">Tidak ada data pengguna pada periode yang dipilih.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

