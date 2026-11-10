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
            vertical-align: top;
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
            <td colspan="11" class="kop-title">PENGADILAN AGAMA JAKARTA UTARA</td>
        </tr>
        <tr>
            <td colspan="11" class="kop-sub">SiMonter - Sistem Monitoring Evaluasi Terpadu</td>
        </tr>
        <tr>
            <td colspan="11" style="text-align: center; font-weight: bold; font-size: 12pt;">LAPORAN DATA DELEGASI & TINDAK LANJUT TUGAS</td>
        </tr>
        <tr>
            <td colspan="11" style="text-align: center; color: #555;">Periode: {{ $periodeString ?? 'Semua Periode' }} | Klasifikasi: {{ $filterKlasifikasi ?? 'Semua Klasifikasi' }} | Tanggal Cetak: {{ date('d/m/Y H:i') }} WIB</td>
        </tr>
        <tr>
            <td colspan="11"></td>
        </tr>
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th style="width: 220px;">Judul Tugas</th>
                <th style="width: 140px;">Klasifikasi</th>
                <th style="width: 160px;">Pemberi Tugas</th>
                <th style="width: 160px;">Petugas Pelaksana</th>
                <th style="width: 160px;">NIP Petugas</th>
                <th style="width: 110px;">Tanggal Mulai</th>
                <th style="width: 110px;">Batas Selesai</th>
                <th style="width: 130px;">Status</th>
                <th style="width: 250px;">Laporan Tindak Lanjut</th>
                <th style="width: 200px;">Bukti Evidence</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tugas as $t)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $t->judul }}</strong>
                        <div style="font-size: 8.5pt; color: #555;">{{ $t->deskripsi }}</div>
                    </td>
                    <td class="text-center">{{ $t->klasifikasi ?? 'Kesekretariatan' }}</td>
                    <td>{{ $t->admin->name ?? 'Admin' }}</td>
                    <td>{{ $t->petugas->name ?? '-' }}</td>
                    <td class="text-center text-format">{{ $t->petugas->nip ?? '-' }}</td>
                    <td class="text-center">{{ $t->tanggal_mulai ? $t->tanggal_mulai->format('d/m/Y') : '-' }}</td>
                    <td class="text-center">{{ $t->tanggal_selesai ? $t->tanggal_selesai->format('d/m/Y') : '-' }}</td>
                    <td class="text-center">{{ $t->status }}</td>
                    <td>{{ $t->catatan_petugas ?? '-' }}</td>
                    <td>
                        @if($t->file_evidence)
                            <div>[File]: {{ $t->file_evidence_name ?? 'Ada Berkas' }}</div>
                        @endif
                        @if($t->link_evidence)
                            <div>[Link]: {{ $t->link_evidence }}</div>
                        @endif
                        @if(!$t->file_evidence && !$t->link_evidence)
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 20px;">Tidak ada data tugas pada periode yang dipilih.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

