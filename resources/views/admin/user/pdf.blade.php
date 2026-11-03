<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Laporan Data User' }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .header h3 {
            margin: 0;
            font-size: 14pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header h2 {
            margin: 2px 0;
            font-size: 16pt;
            text-transform: uppercase;
            color: #1a3a63;
        }
        .header p {
            margin: 2px 0;
            font-size: 9pt;
            color: #555;
        }
        .title-box {
            text-align: center;
            margin-bottom: 15px;
        }
        .title-box h4 {
            margin: 0;
            font-size: 13pt;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 10pt;
        }
        .meta-table td {
            padding: 2px 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9.5pt;
        }
        .data-table th {
            background-color: #1a3a63;
            color: #ffffff;
            border: 1px solid #1a3a63;
            padding: 6px 4px;
            text-align: center;
        }
        .data-table td {
            border: 1px solid #bbb;
            padding: 5px 6px;
        }
        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8pt;
            font-weight: bold;
            border-radius: 3px;
        }
        .badge-admin {
            background-color: #343a40;
            color: #fff;
        }
        .badge-petugas {
            background-color: #17a2b8;
            color: #fff;
        }
        .badge-aktif {
            background-color: #28a745;
            color: #fff;
        }
        .badge-belum {
            background-color: #dc3545;
            color: #fff;
        }
        .signature-section {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .signature-box {
            float: right;
            width: 240px;
            text-align: center;
            font-size: 10pt;
        }
        .signature-space {
            height: 60px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI -->
    <div class="header">
        <h3>PENGADILAN AGAMA JAKARTA UTARA</h3>
        <h2>SiMonter - Sistem Monitoring Evaluasi Terpadu</h2>
        <p>Jl. Plumpang Semper No. 25, Koja, Jakarta Utara 14260 | Telp: (021) 43931456 | Website: www.pa-jakartautara.go.id</p>
    </div>

    <!-- JUDUL LAPORAN -->
    <div class="title-box">
        <h4>LAPORAN DATA PENGGUNA SISTEM</h4>
    </div>

    <!-- METADATA PERIODE -->
    <table class="meta-table">
        <tr>
            <td width="18%"><strong>Periode Tanggal</strong></td>
            <td width="3%">:</td>
            <td width="40%">{{ $periodeString ?? 'Semua Waktu' }}</td>
            <td width="15%"><strong>Tanggal Cetak</strong></td>
            <td width="3%">:</td>
            <td width="21%">{{ date('d/m/Y H:i') }} WIB</td>
        </tr>
        <tr>
            <td><strong>Filter Jabatan</strong></td>
            <td>:</td>
            <td>{{ $filterJabatan ?? 'Semua Jabatan' }}</td>
            <td><strong>Dicetak Oleh</strong></td>
            <td>:</td>
            <td>{{ Auth::user()->name ?? 'Admin' }}</td>
        </tr>
        <tr>
            <td><strong>Total Data</strong></td>
            <td>:</td>
            <td>{{ count($users) }} Pengguna</td>
            <td><strong>Status Akun</strong></td>
            <td>:</td>
            <td>{{ $filterStatus ?? 'Semua Status' }}</td>
        </tr>
    </table>

    <!-- TABEL DATA -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="22%">Nama Lengkap</th>
                <th width="20%">NIP</th>
                <th width="20%">Email</th>
                <th width="14%">Kontak</th>
                <th width="10%">Jabatan</th>
                <th width="10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $u)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td><strong>{{ $u->name }}</strong></td>
                    <td class="text-center">{{ $u->nip }}</td>
                    <td>{{ $u->email ?? '-' }}</td>
                    <td class="text-center">{{ $u->kontak ?? '-' }}</td>
                    <td class="text-center">
                        <span class="badge {{ $u->jabatan === 'Admin' ? 'badge-admin' : 'badge-petugas' }}">
                            {{ $u->jabatan }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $u->status === 'Sedang Bertugas' ? 'badge-aktif' : 'badge-belum' }}">
                            {{ $u->status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px; color: #888;">Tidak ada data pengguna pada periode yang dipilih.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TANDA TANGAN -->
    <div class="signature-section">
        <div class="signature-box">
            <p style="margin-bottom: 2px;">Jakarta Utara, {{ date('d F Y') }}</p>
            <p style="margin-top: 0;">Mengetahui,<br><strong>Administrator SiMonter</strong></p>
            <div class="signature-space"></div>
            <p class="signature-name">{{ Auth::user()->name ?? 'Reza Fahlevi' }}</p>
            <p style="margin-top: 2px; font-size: 9pt;">NIP. {{ Auth::user()->nip ?? '-' }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>

