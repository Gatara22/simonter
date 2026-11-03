<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Laporan Data Tugas' }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm 12mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        .header h3 {
            margin: 0;
            font-size: 13pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header h2 {
            margin: 2px 0;
            font-size: 15pt;
            text-transform: uppercase;
            color: #1a3a63;
        }
        .header p {
            margin: 2px 0;
            font-size: 8.5pt;
            color: #555;
        }
        .title-box {
            text-align: center;
            margin-bottom: 10px;
        }
        .title-box h4 {
            margin: 0;
            font-size: 12pt;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 10px;
            font-size: 9pt;
        }
        .meta-table td {
            padding: 2px 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 8.5pt;
        }
        .data-table th {
            background-color: #1a3a63;
            color: #ffffff;
            border: 1px solid #1a3a63;
            padding: 6px 3px;
            text-align: center;
        }
        .data-table td {
            border: 1px solid #bbb;
            padding: 4px 5px;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 7.5pt;
            font-weight: bold;
            border-radius: 3px;
        }
        .badge-didelegasikan { background-color: #17a2b8; color: #fff; }
        .badge-proses { background-color: #007bff; color: #fff; }
        .badge-menunggu { background-color: #ffc107; color: #212529; }
        .badge-selesai { background-color: #28a745; color: #fff; }
        .badge-revisi { background-color: #dc3545; color: #fff; }
        .signature-section {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .signature-box {
            float: right;
            width: 240px;
            text-align: center;
            font-size: 9.5pt;
        }
        .signature-space {
            height: 50px;
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
        <h4>LAPORAN DATA DELEGASI & TINDAK LANJUT TUGAS</h4>
    </div>

    <!-- METADATA PERIODE -->
    <table class="meta-table">
        <tr>
            <td width="15%"><strong>Periode Tugas</strong></td>
            <td width="2%">:</td>
            <td width="38%">{{ $periodeString ?? 'Semua Periode' }}</td>
            <td width="15%"><strong>Tanggal Cetak</strong></td>
            <td width="2%">:</td>
            <td width="28%">{{ date('d/m/Y H:i') }} WIB</td>
        </tr>
        <tr>
            <td><strong>Filter Status</strong></td>
            <td>:</td>
            <td>{{ $filterStatus ?? 'Semua Status' }}</td>
            <td><strong>Dicetak Oleh</strong></td>
            <td>:</td>
            <td>{{ Auth::user()->name ?? 'Admin' }}</td>
        </tr>
        <tr>
            <td><strong>Total Tugas</strong></td>
            <td>:</td>
            <td>{{ count($tugas) }} Data Tugas</td>
            <td><strong>Petugas Pelaksana</strong></td>
            <td>:</td>
            <td>{{ $filterPetugas ?? 'Semua Petugas' }}</td>
        </tr>
    </table>

    <!-- TABEL DATA TUGAS -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="20%">Judul & Instruksi Tugas</th>
                <th width="14%">Petugas Pelaksana</th>
                <th width="13%">Pemberi Tugas</th>
                <th width="10%">Tanggal Mulai</th>
                <th width="10%">Batas Selesai</th>
                <th width="10%">Status</th>
                <th width="20%">Hasil Tindak Lanjut & Bukti</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tugas as $t)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $t->judul }}</strong>
                        <div style="color: #555; font-size: 8pt; margin-top: 2px;">{{ Str::limit($t->deskripsi, 90) }}</div>
                    </td>
                    <td>
                        <strong>{{ $t->petugas->name ?? '-' }}</strong><br>
                        <span style="color: #666; font-size: 8pt;">NIP: {{ $t->petugas->nip ?? '-' }}</span>
                    </td>
                    <td>{{ $t->admin->name ?? 'Admin' }}</td>
                    <td class="text-center">{{ $t->tanggal_mulai ? $t->tanggal_mulai->format('d/m/Y') : '-' }}</td>
                    <td class="text-center">{{ $t->tanggal_selesai ? $t->tanggal_selesai->format('d/m/Y') : '-' }}</td>
                    <td class="text-center">
                        @if($t->status === 'Didelegasikan')
                            <span class="badge badge-didelegasikan">Didelegasikan</span>
                        @elseif($t->status === 'Sedang Dikerjakan')
                            <span class="badge badge-proses">Dikerjakan</span>
                        @elseif($t->status === 'Menunggu Konfirmasi')
                            <span class="badge badge-menunggu">Menunggu Konfirmasi</span>
                        @elseif($t->status === 'Selesai')
                            <span class="badge badge-selesai">Selesai</span>
                        @elseif($t->status === 'Perlu Revisi')
                            <span class="badge badge-revisi">Perlu Revisi</span>
                        @else
                            <span class="badge">{{ $t->status }}</span>
                        @endif
                    </td>
                    <td>
                        @if($t->catatan_petugas)
                            <div><strong>Laporan:</strong> {{ Str::limit($t->catatan_petugas, 70) }}</div>
                        @endif
                        @if($t->file_evidence)
                            <div style="font-size: 8pt; color: #28a745;"><strong>[File]:</strong> {{ $t->file_evidence_name ?? 'Ada File' }}</div>
                        @endif
                        @if($t->link_evidence)
                            <div style="font-size: 8pt; color: #17a2b8;"><strong>[Link]:</strong> {{ Str::limit($t->link_evidence, 35) }}</div>
                        @endif
                        @if(!$t->catatan_petugas && !$t->file_evidence && !$t->link_evidence)
                            <span style="color: #999; font-style: italic;">Belum ada tindak lanjut</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 15px; color: #888;">Tidak ada data tugas pada periode yang dipilih.</td>
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
            <p style="margin-top: 2px; font-size: 8.5pt;">NIP. {{ Auth::user()->nip ?? '-' }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>

