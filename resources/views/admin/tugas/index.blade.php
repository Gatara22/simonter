@extends('layouts.app')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tasks mr-2"></i>
            {{ $title }}
        </h1>
        @if($canDelegate ?? $isAdmin)
            <a href="{{ route('tugas.create') }}" class="btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Delegasikan Tugas Baru
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Mini Statistics Cards -->
    <div class="row">
        <!-- Total Tasks -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Tugas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Didelegasikan / Proses -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Didelegasikan / Berjalan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['didelegasikan'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-spinner fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menunggu Konfirmasi -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Menunggu Konfirmasi</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['menunggu'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-reply-all fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selesai -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Tugas Selesai</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['selesai'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Klasifikasi Data Tugas: 2 Bagian (Kesekretariatan & Kepaniteraan) -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body py-3 px-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                <div>
                    <h6 class="font-weight-bold text-gray-800 mb-0">
                        <i class="fas fa-sitemap text-primary mr-1"></i> Klasifikasi Data Tugas (Delegasi)
                    </h6>
                    <small class="text-muted">
                        Data tugas diklasifikasikan menjadi 2 bagian: <strong>Kesekretariatan</strong> (Delegasi Kepala Sub Bagian) dan <strong>Kepaniteraan</strong> (Delegasi Panitera Muda).
                    </small>
                </div>
                @if(!empty($activeKlasifikasi))
                    <a href="{{ route('tugas.index', array_filter(['status' => request('status')])) }}" class="btn btn-xs btn-outline-secondary py-1 px-2 small">
                        <i class="fas fa-sync-alt mr-1"></i> Tampilkan Semua Klasifikasi
                    </a>
                @endif
            </div>

            <div class="row mt-2">
                <!-- Semua Klasifikasi -->
                <div class="col-md-4 mb-2 mb-md-0">
                    <a href="{{ route('tugas.index', array_filter(['status' => request('status')])) }}"
                       class="card h-100 text-decoration-none {{ empty($activeKlasifikasi) ? 'border-dark bg-light shadow-sm' : 'border' }}"
                       style="transition: all 0.15s ease;">
                        <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-xs font-weight-bold text-uppercase {{ empty($activeKlasifikasi) ? 'text-dark' : 'text-muted' }}">
                                    Semua Klasifikasi
                                </div>
                                <div class="small text-muted">Seluruh Data Delegasi Tugas</div>
                            </div>
                            <span class="badge badge-dark badge-pill px-3 py-2" style="font-size: 0.85rem;">
                                {{ $stats['total'] ?? 0 }}
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Bagian 1: Kesekretariatan -->
                <div class="col-md-4 mb-2 mb-md-0">
                    <a href="{{ route('tugas.index', array_filter(['klasifikasi' => 'Kesekretariatan', 'status' => request('status')])) }}"
                       class="card h-100 text-decoration-none {{ ($activeKlasifikasi ?? null) === 'Kesekretariatan' ? 'border-primary shadow-sm' : 'border' }}"
                       style="{{ ($activeKlasifikasi ?? null) === 'Kesekretariatan' ? 'background-color: #eff6ff;' : '' }} transition: all 0.15s ease;">
                        <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-xs font-weight-bold text-primary text-uppercase">
                                    <i class="fas fa-building mr-1"></i> Kesekretariatan
                                </div>
                                <div class="small text-muted">Delegasi Kepala Sub Bagian &amp; Umum</div>
                            </div>
                            <span class="badge badge-primary badge-pill px-3 py-2" style="font-size: 0.85rem;">
                                {{ $stats['kesekretariatan'] ?? 0 }}
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Bagian 2: Kepaniteraan -->
                <div class="col-md-4">
                    <a href="{{ route('tugas.index', array_filter(['klasifikasi' => 'Kepaniteraan', 'status' => request('status')])) }}"
                       class="card h-100 text-decoration-none {{ ($activeKlasifikasi ?? null) === 'Kepaniteraan' ? 'border-success shadow-sm' : 'border' }}"
                       style="{{ ($activeKlasifikasi ?? null) === 'Kepaniteraan' ? 'background-color: #ecfdf5;' : '' }} transition: all 0.15s ease;">
                        <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-xs font-weight-bold text-success text-uppercase">
                                    <i class="fas fa-balance-scale mr-1"></i> Kepaniteraan
                                </div>
                                <div class="small text-muted">Delegasi Panitera Muda &amp; Perkara</div>
                            </div>
                            <span class="badge badge-success badge-pill px-3 py-2" style="font-size: 0.85rem;">
                                {{ $stats['kepaniteraan'] ?? 0 }}
                            </span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card shadow mb-4">
         <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table mr-1"></i> Daftar Tugas
                @if(!empty($activeKlasifikasi))
                    <span class="badge {{ $activeKlasifikasi === 'Kepaniteraan' ? 'badge-success' : 'badge-primary' }} ml-2 px-2 py-1">
                        Klasifikasi: {{ $activeKlasifikasi }}
                    </span>
                @endif
            </h6>
            <div>
                <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#modalExportExcelTugas">
                    <i class="fas fa-file-excel mr-1"></i> Excel
                </button>
                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#modalExportPdfTugas">
                    <i class="fas fa-file-pdf mr-1"></i> PDF
                </button>
            </div>
         </div>
         <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="bg-primary text-white">
                        <tr class="text-center">
                            <th width="4%">No</th>
                            <th>Judul Tugas</th>
                            <th>Klasifikasi</th>
                            @if($isAdmin)
                                <th>Petugas Pelaksana</th>
                            @elseif($isKasubbag ?? false)
                                <th>Pemberi & Pelaksana Tugas</th>
                            @else
                                <th>Didelegasikan Oleh</th>
                            @endif
                            <th>Periode Pengerjaan</th>
                            <th>Status</th>
                            <th>Bukti Tindak Lanjut</th>
                            <th width="16%">
                                <i class="fas fa-cog"></i> Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tugas as $t)
                            <tr>
                                <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                <td class="align-middle">
                                    <div class="font-weight-bold text-gray-900">{{ $t->judul }}</div>
                                    <small class="text-muted">{{ Str::limit($t->deskripsi, 60) }}</small>
                                </td>
                                <td class="text-center align-middle">
                                    @if(($t->klasifikasi ?? 'Kesekretariatan') === 'Kepaniteraan')
                                        <span class="badge badge-success px-2 py-1">
                                            <i class="fas fa-balance-scale mr-1"></i> Kepaniteraan
                                        </span>
                                    @else
                                        <span class="badge badge-primary px-2 py-1">
                                            <i class="fas fa-building mr-1"></i> Kesekretariatan
                                        </span>
                                    @endif
                                </td>

                                @if($isAdmin)
                                    <td class="align-middle">
                                        <span class="font-weight-bold">{{ $t->petugas->name ?? '-' }}</span>
                                        <span class="badge badge-light border ml-1">{{ $t->petugas->jabatan ?? 'Petugas' }}</span>
                                        <br><small class="text-muted">NIP: {{ $t->petugas->nip ?? '-' }}</small>
                                        @if($t->admin && $t->admin_id !== Auth::id())
                                            <br><small class="text-info"><i class="fas fa-share mr-1"></i>Dari: {{ $t->admin->name }}</small>
                                        @endif
                                    </td>
                                @elseif($isKasubbag ?? false)
                                    <td class="align-middle">
                                        <div class="small mb-1">
                                            <span class="text-muted">Dari:</span>
                                            <span class="font-weight-bold text-dark">{{ $t->admin->name ?? 'Admin' }}</span>
                                        </div>
                                        <div class="small">
                                            <span class="text-muted">Pelaksana:</span>
                                            <span class="font-weight-bold text-primary">{{ $t->petugas->name ?? '-' }}</span>
                                            @if($t->petugas_id === Auth::id())
                                                <span class="badge badge-info ml-1">Tugas Masuk</span>
                                            @else
                                                <span class="badge badge-primary ml-1">Didelegasikan ke Petugas</span>
                                            @endif
                                        </div>
                                    </td>
                                @else
                                    <td class="align-middle">
                                        <i class="fas fa-user-shield mr-1 text-dark"></i>
                                        <span class="font-weight-bold">{{ $t->admin->name ?? 'Admin' }}</span>
                                        @if($t->admin && $t->admin->jabatan)
                                            <br><small class="text-muted">{{ $t->admin->jabatan }}</small>
                                        @endif
                                    </td>
                                @endif

                                <td class="text-center align-middle">
                                    <span class="badge badge-light border px-2 py-1">
                                        <i class="far fa-calendar-alt text-muted mr-1"></i>
                                        {{ $t->tanggal_mulai ? $t->tanggal_mulai->format('d/m/Y') : '-' }} s/d {{ $t->tanggal_selesai ? $t->tanggal_selesai->format('d/m/Y') : '-' }}
                                    </span>
                                </td>

                                <td class="text-center align-middle">
                                    @if($t->status === 'Didelegasikan')
                                        <span class="badge badge-info badge-pill px-2 py-1"><i class="fas fa-clock mr-1"></i> Didelegasikan</span>
                                    @elseif($t->status === 'Sedang Dikerjakan')
                                        <span class="badge badge-primary badge-pill px-2 py-1"><i class="fas fa-spinner fa-spin mr-1"></i> Sedang Dikerjakan</span>
                                    @elseif($t->status === 'Menunggu Konfirmasi')
                                        <span class="badge badge-warning badge-pill px-2 py-1 text-dark"><i class="fas fa-paper-plane mr-1"></i> Menunggu Konfirmasi</span>
                                    @elseif($t->status === 'Selesai')
                                        <span class="badge badge-success badge-pill px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Selesai</span>
                                    @elseif($t->status === 'Perlu Revisi')
                                        <span class="badge badge-danger badge-pill px-2 py-1"><i class="fas fa-exclamation-triangle mr-1"></i> Perlu Revisi</span>
                                    @else
                                        <span class="badge badge-secondary badge-pill px-2 py-1">{{ $t->status }}</span>
                                    @endif
                                </td>

                                <td class="text-center align-middle">
                                    @if($t->file_evidence || $t->link_evidence)
                                        @if($t->file_evidence)
                                            <a href="{{ asset('storage/' . $t->file_evidence) }}" target="_blank" class="badge badge-success p-1 mr-1" title="Unduh File Bukti">
                                                <i class="fas fa-file-download mr-1"></i> File
                                            </a>
                                        @endif
                                        @if($t->link_evidence)
                                            <a href="{{ $t->link_evidence }}" target="_blank" class="badge badge-info p-1" title="Buka Tautan Bukti">
                                                <i class="fas fa-external-link-alt mr-1"></i> Link
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-muted small"><em>Belum ada bukti</em></span>
                                    @endif
                                </td>

                                <td class="text-center align-middle">
                                    <div class="d-inline-flex flex-wrap justify-content-center">
                                        <!-- Tombol Detail -->
                                        <a href="{{ route('tugas.show', $t->id) }}" class="btn btn-info btn-sm mr-1 mb-1" title="Lihat Detail & Bukti">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <!-- Tombol Delegasikan ke Petugas (untuk Kepala Sub Bagian yang menerima tugas dari Admin) -->
                                        @if(($isKasubbag ?? false) && $t->petugas_id === Auth::id() && $t->status !== 'Selesai')
                                            <button type="button" class="btn btn-primary btn-sm mr-1 mb-1" data-toggle="modal" data-target="#modalDelegasikanTugas-{{ $t->id }}" title="Delegasikan ke Petugas">
                                                <i class="fas fa-share-square"></i>
                                            </button>
                                        @endif

                                        <!-- Tombol Tindak Lanjut untuk penerima tugas -->
                                        @if(!$isAdmin && $t->petugas_id === Auth::id() && in_array($t->status, ['Didelegasikan', 'Sedang Dikerjakan', 'Perlu Revisi']))
                                            <a href="{{ route('tugas.tindak-lanjut', $t->id) }}" class="btn btn-success btn-sm mr-1 mb-1" title="Tindak Lanjut & Kirim Balik">
                                                <i class="fas fa-reply"></i>
                                            </a>
                                        @endif

                                        <!-- Tombol Buka / Delegasikan Ulang jika tugas sudah Selesai -->
                                        @if(($isAdmin || (($isKasubbag ?? false) && $t->admin_id === Auth::id())) && $t->status === 'Selesai')
                                            <button type="button" class="btn btn-warning btn-sm mr-1 mb-1" data-toggle="modal" data-target="#modalBukaKembaliTugas-{{ $t->id }}" title="Buka / Delegasikan Ulang Tugas">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        @endif

                                        @if($isAdmin || (($isKasubbag ?? false) && $t->admin_id === Auth::id()))
                                            <!-- Tombol Edit -->
                                            <a href="{{ route('tugas.edit', $t->id) }}" class="btn btn-warning btn-sm mr-1 mb-1" title="Edit Tugas">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <!-- Tombol Hapus -->
                                            <form action="{{ route('tugas.destroy', $t->id) }}" method="POST" class="d-inline mb-1" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus Tugas">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    @if(($isKasubbag ?? false) && $t->petugas_id === Auth::id() && $t->status !== 'Selesai')
                                        <!-- Modal Delegasikan ke Petugas -->
                                        <div class="modal fade text-left" id="modalDelegasikanTugas-{{ $t->id }}" tabindex="-1" role="dialog" aria-labelledby="modalDelegasiLabel-{{ $t->id }}" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title" id="modalDelegasiLabel-{{ $t->id }}">
                                                            <i class="fas fa-share-square mr-1"></i> Delegasikan Tugas ke Petugas
                                                        </h5>
                                                        <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">×</span>
                                                        </button>
                                                    </div>
                                                    <form action="{{ route('tugas.delegasikan', $t->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="alert alert-light border small mb-3">
                                                                <strong>Judul Tugas:</strong> {{ $t->judul }}<br>
                                                                <strong>Diterima dari:</strong> {{ $t->admin->name ?? 'Admin' }}
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="font-weight-bold small text-gray-800">Pilih Petugas Pelaksana (Bisa Lebih dari 1 Orang) <span class="text-danger">*</span></label>
                                                                <div class="border rounded p-2 bg-light" style="max-height: 190px; overflow-y: auto;">
                                                                    @foreach($petugasList as $p)
                                                                        @if($p->jabatan === 'Petugas')
                                                                            <div class="custom-control custom-checkbox mb-1">
                                                                                <input type="checkbox" class="custom-control-input" id="delegasi_idx_{{ $t->id }}_p_{{ $p->id }}" name="petugas_id[]" value="{{ $p->id }}">
                                                                                <label class="custom-control-label small text-gray-900" for="delegasi_idx_{{ $t->id }}_p_{{ $p->id }}">
                                                                                    <strong>{{ $p->name }}</strong> (NIP: {{ $p->nip }}) — <span class="badge {{ $p->status === 'Belum Ditugaskan' ? 'badge-secondary' : 'badge-success' }}">{{ $p->status }}</span>
                                                                                </label>
                                                                            </div>
                                                                        @endif
                                                                    @endforeach
                                                                </div>
                                                                <small class="text-muted">Centang 1 atau lebih Petugas yang akan menerima delegasi tugas ini.</small>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="font-weight-bold small text-gray-800">Batas Selesai / Deadline</label>
                                                                <input type="date" class="form-control" name="tanggal_selesai" value="{{ $t->tanggal_selesai ? $t->tanggal_selesai->format('Y-m-d') : '' }}">
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label class="font-weight-bold small text-gray-800">Instruksi / Catatan Tambahan Kepala Sub Bagian (Opsional)</label>
                                                                <textarea class="form-control" name="catatan_delegasi" rows="3" placeholder="Tambahkan arahan atau instruksi khusus untuk Petugas..."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button class="btn btn-secondary btn-sm" type="button" data-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary btn-sm">
                                                                <i class="fas fa-paper-plane mr-1"></i> Delegasikan ke Petugas
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if(($isAdmin || (($isKasubbag ?? false) && $t->admin_id === Auth::id())) && $t->status === 'Selesai')
                                        <!-- Modal Buka Kembali / Delegasikan Ulang Tugas -->
                                        <div class="modal fade text-left" id="modalBukaKembaliTugas-{{ $t->id }}" tabindex="-1" role="dialog" aria-labelledby="modalBukaKembaliLabel-{{ $t->id }}" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-warning text-dark">
                                                        <h5 class="modal-title font-weight-bold" id="modalBukaKembaliLabel-{{ $t->id }}">
                                                            <i class="fas fa-redo mr-1"></i> Buka & Delegasikan Ulang Tugas
                                                        </h5>
                                                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">×</span>
                                                        </button>
                                                    </div>
                                                    <form action="{{ route('tugas.buka-kembali', $t->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="alert alert-light border small mb-3">
                                                                <strong>Judul Tugas:</strong> {{ $t->judul }}<br>
                                                                <strong>Petugas Pelaksana:</strong> {{ $t->petugas->name ?? '-' }} (NIP: {{ $t->petugas->nip ?? '-' }})
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="font-weight-bold small text-gray-800">
                                                                    Catatan Kesalahan / Instruksi Delegasi Ulang <span class="text-danger">*</span>
                                                                </label>
                                                                <textarea class="form-control" name="catatan" rows="3" placeholder="Tuliskan catatan kesalahan pengerjaan atau instruksi perbaikan untuk petugas..." required></textarea>
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label class="font-weight-bold small text-gray-800">Perpanjangan Batas Selesai (Opsional)</label>
                                                                <input type="date" class="form-control" name="tanggal_selesai" value="{{ $t->tanggal_selesai ? $t->tanggal_selesai->format('Y-m-d') : '' }}">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button class="btn btn-secondary btn-sm" type="button" data-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-warning btn-sm font-weight-bold" onclick="return confirm('Buka kembali tugas ini dan delegasikan ulang ke petugas pelaksana?');">
                                                                <i class="fas fa-redo mr-1"></i> Buka & Delegasikan Ulang
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-2 text-gray-300 d-block"></i>
                                    Belum ada data tugas yang tersedia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
         </div>
    </div>

    <!-- Modal Export Excel Tugas -->
    <div class="modal fade" id="modalExportExcelTugas" tabindex="-1" role="dialog" aria-labelledby="modalExcelTugasLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalExcelTugasLabel">
                        <i class="fas fa-file-excel mr-1"></i> Filter & Export Excel Data Tugas
                    </h5>
                    <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ route('tugas.export.excel') }}" method="GET">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-bold small text-gray-800">Periode Waktu Tugas (Opsional):</label>
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Dari Tanggal Mulai:</small>
                                    <input type="date" class="form-control" name="tanggal_awal">
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Sampai Batas Selesai:</small>
                                    <input type="date" class="form-control" name="tanggal_akhir">
                                </div>
                            </div>
                            <small class="form-text text-muted mt-1">Kosongkan periode jika ingin mengekspor seluruh rentang waktu.</small>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold small text-gray-800">Filter Klasifikasi Tugas:</label>
                            <select class="form-control form-control-sm" name="klasifikasi">
                                <option value="">Semua Klasifikasi (Kesekretariatan &amp; Kepaniteraan)</option>
                                <option value="Kesekretariatan" {{ ($activeKlasifikasi ?? null) === 'Kesekretariatan' ? 'selected' : '' }}>Kesekretariatan</option>
                                <option value="Kepaniteraan" {{ ($activeKlasifikasi ?? null) === 'Kepaniteraan' ? 'selected' : '' }}>Kepaniteraan</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-{{ ($canDelegate ?? $isAdmin) ? '6' : '12' }}">
                                <div class="form-group">
                                    <label class="font-weight-bold small text-gray-800">Filter Status Tugas:</label>
                                    <select class="form-control form-control-sm" name="status">
                                        <option value="">Semua Status</option>
                                        <option value="Didelegasikan">Didelegasikan</option>
                                        <option value="Sedang Dikerjakan">Sedang Dikerjakan</option>
                                        <option value="Menunggu Konfirmasi">Menunggu Konfirmasi</option>
                                        <option value="Selesai">Selesai</option>
                                        <option value="Perlu Revisi">Perlu Revisi</option>
                                    </select>
                                </div>
                            </div>
                            @if($canDelegate ?? $isAdmin)
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold small text-gray-800">Filter Petugas:</label>
                                        <select class="form-control form-control-sm" name="petugas_id">
                                            <option value="">Semua Petugas</option>
                                            @foreach($petugasList as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary btn-sm" type="button" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fas fa-download mr-1"></i> Download Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Export PDF Tugas -->
    <div class="modal fade" id="modalExportPdfTugas" tabindex="-1" role="dialog" aria-labelledby="modalPdfTugasLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalPdfTugasLabel">
                        <i class="fas fa-file-pdf mr-1"></i> Filter & Export PDF Data Tugas
                    </h5>
                    <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ route('tugas.export.pdf') }}" method="GET">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-bold small text-gray-800">Periode Waktu Tugas (Opsional):</label>
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Dari Tanggal Mulai:</small>
                                    <input type="date" class="form-control" name="tanggal_awal">
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Sampai Batas Selesai:</small>
                                    <input type="date" class="form-control" name="tanggal_akhir">
                                </div>
                            </div>
                            <small class="form-text text-muted mt-1">Kosongkan periode jika ingin mengekspor seluruh rentang waktu.</small>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold small text-gray-800">Filter Klasifikasi Tugas:</label>
                            <select class="form-control form-control-sm" name="klasifikasi">
                                <option value="">Semua Klasifikasi (Kesekretariatan &amp; Kepaniteraan)</option>
                                <option value="Kesekretariatan" {{ ($activeKlasifikasi ?? null) === 'Kesekretariatan' ? 'selected' : '' }}>Kesekretariatan</option>
                                <option value="Kepaniteraan" {{ ($activeKlasifikasi ?? null) === 'Kepaniteraan' ? 'selected' : '' }}>Kepaniteraan</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-{{ ($canDelegate ?? $isAdmin) ? '6' : '12' }}">
                                <div class="form-group">
                                    <label class="font-weight-bold small text-gray-800">Filter Status Tugas:</label>
                                    <select class="form-control form-control-sm" name="status">
                                        <option value="">Semua Status</option>
                                        <option value="Didelegasikan">Didelegasikan</option>
                                        <option value="Sedang Dikerjakan">Sedang Dikerjakan</option>
                                        <option value="Menunggu Konfirmasi">Menunggu Konfirmasi</option>
                                        <option value="Selesai">Selesai</option>
                                        <option value="Perlu Revisi">Perlu Revisi</option>
                                    </select>
                                </div>
                            </div>
                            @if($canDelegate ?? $isAdmin)
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold small text-gray-800">Filter Petugas:</label>
                                        <select class="form-control form-control-sm" name="petugas_id">
                                            <option value="">Semua Petugas</option>
                                            @foreach($petugasList as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary btn-sm" type="button" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-download mr-1"></i> Download PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
