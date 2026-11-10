@extends('layouts.app')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-clipboard-check mr-2"></i>
            {{ $title }}
        </h1>
        <div>
            <a href="{{ route('tugas.index') }}" class="btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Kembali ke Daftar
            </a>
            @if(($isKasubbag ?? false) && $tugas->petugas_id === Auth::id() && $tugas->status !== 'Selesai')
                <button type="button" class="btn btn-sm btn-primary shadow-sm ml-2" data-toggle="modal" data-target="#modalDelegasikanTugasShow">
                    <i class="fas fa-share-square fa-sm text-white-50 mr-1"></i> Delegasikan ke Petugas
                </button>
            @endif
            @if(($isAdmin || (($isKasubbag ?? false) && $tugas->admin_id === Auth::id())) && $tugas->status === 'Selesai')
                <button type="button" class="btn btn-sm btn-warning shadow-sm ml-2" data-toggle="modal" data-target="#modalBukaKembaliTugasShow">
                    <i class="fas fa-redo fa-sm text-white-50 mr-1"></i> Buka / Delegasikan Ulang
                </button>
            @endif
            @if(!$isAdmin && $tugas->petugas_id === Auth::id() && in_array($tugas->status, ['Didelegasikan', 'Sedang Dikerjakan', 'Perlu Revisi']))
                <a href="{{ route('tugas.tindak-lanjut', $tugas->id) }}" class="btn btn-sm btn-success shadow-sm ml-2">
                    <i class="fas fa-reply fa-sm text-white-50 mr-1"></i> Tindak Lanjut / Kirim Balik
                </a>
            @endif
        </div>
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

    <div class="row">
        <!-- Kolom Kiri: Rincian Tugas -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle mr-1"></i> Informasi & Instruksi Tugas
                    </h6>
                    <div>
                        @if($tugas->status === 'Didelegasikan')
                            <span class="badge badge-info badge-pill px-3 py-1 font-weight-bold"><i class="fas fa-clock mr-1"></i> Didelegasikan</span>
                        @elseif($tugas->status === 'Sedang Dikerjakan')
                            <span class="badge badge-primary badge-pill px-3 py-1 font-weight-bold"><i class="fas fa-spinner fa-spin mr-1"></i> Sedang Dikerjakan</span>
                        @elseif($tugas->status === 'Menunggu Konfirmasi')
                            <span class="badge badge-warning badge-pill px-3 py-1 font-weight-bold text-dark"><i class="fas fa-paper-plane mr-1"></i> Menunggu Konfirmasi</span>
                        @elseif($tugas->status === 'Selesai')
                            <span class="badge badge-success badge-pill px-3 py-1 font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Selesai</span>
                        @elseif($tugas->status === 'Perlu Revisi')
                            <span class="badge badge-danger badge-pill px-3 py-1 font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i> Perlu Revisi</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-4">
                        <tr>
                            <th width="35%" class="text-muted">Judul Tugas</th>
                            <td class="font-weight-bold text-gray-900">: {{ $tugas->judul }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Klasifikasi Tugas</th>
                            <td>:
                                @if(($tugas->klasifikasi ?? 'Kesekretariatan') === 'Kepaniteraan')
                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-balance-scale mr-1"></i> Kepaniteraan</span>
                                @else
                                    <span class="badge badge-primary px-2 py-1"><i class="fas fa-building mr-1"></i> Kesekretariatan</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Pemberi Tugas</th>
                            <td>: {{ $tugas->admin->name ?? 'Admin' }} <span class="badge badge-light border ml-1">{{ $tugas->admin->jabatan ?? 'Admin' }}</span></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Petugas Pelaksana</th>
                            <td>: <span class="font-weight-bold">{{ $tugas->petugas->name ?? '-' }}</span> (NIP: {{ $tugas->petugas->nip ?? '-' }}) <span class="badge badge-light border ml-1">{{ $tugas->petugas->jabatan ?? 'Petugas' }}</span></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Periode Pengerjaan</th>
                            <td>: {{ $tugas->tanggal_mulai ? $tugas->tanggal_mulai->format('d F Y') : '-' }} s/d {{ $tugas->tanggal_selesai ? $tugas->tanggal_selesai->format('d F Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Tanggal Delegasi</th>
                            <td>: {{ $tugas->created_at ? $tugas->created_at->format('d/m/Y H:i') : '-' }}</td>
                        </tr>
                    </table>

                    <h6 class="font-weight-bold text-gray-800 border-bottom pb-2">Deskripsi & Petunjuk Tugas:</h6>
                    <div class="p-3 bg-light rounded text-gray-900 mb-3" style="white-space: pre-line;">
                        {{ $tugas->deskripsi }}
                    </div>

                    @if($tugas->file_tugas)
                        <div class="card border-left-info shadow-sm p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="font-weight-bold text-info"><i class="fas fa-paperclip mr-1"></i> Berkas Lampiran Instruksi</div>
                                    <small class="text-muted">{{ $tugas->file_tugas_name ?? 'Dokumen Instruksi' }}</small>
                                </div>
                                <a href="{{ asset('storage/' . $tugas->file_tugas) }}" target="_blank" class="btn btn-sm btn-info">
                                    <i class="fas fa-download mr-1"></i> Unduh Berkas
                                </a>
                            </div>
                        </div>
                    @endif

                    @if($tugas->catatan_admin)
                        <div class="card border-left-danger shadow-sm p-3 mt-3">
                            <h6 class="font-weight-bold text-danger mb-1"><i class="fas fa-comment-dots mr-1"></i> Catatan Verifikasi / Revisi:</h6>
                            <div class="text-gray-900 small" style="white-space: pre-line;">{{ $tugas->catatan_admin }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Hasil Tindak Lanjut Petugas & Verifikasi -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-reply-all mr-1"></i> Hasil Tindak Lanjut & Bukti (Petugas)
                    </h6>
                </div>
                <div class="card-body">
                    @if($tugas->tanggal_tindak_lanjut || $tugas->catatan_petugas || $tugas->file_evidence || $tugas->link_evidence)
                        <div class="mb-3">
                            <span class="text-muted small">Waktu Dikirim Balik:</span>
                            <span class="badge badge-light border ml-1">
                                <i class="fas fa-clock mr-1"></i> {{ $tugas->tanggal_tindak_lanjut ? $tugas->tanggal_tindak_lanjut->format('d F Y, H:i') : '-' }} WIB
                            </span>
                        </div>

                        <h6 class="font-weight-bold text-gray-800">Laporan / Keterangan Tindak Lanjut:</h6>
                        <div class="p-3 bg-light rounded text-gray-900 mb-4" style="white-space: pre-line;">
                            {{ $tugas->catatan_petugas ?? 'Tidak ada catatan tertulis.' }}
                        </div>

                        <h6 class="font-weight-bold text-gray-800 mb-2">Bukti Evidence yang Dilampirkan:</h6>
                        
                        <div class="list-group mb-4">
                            <!-- Bukti File -->
                            @if($tugas->file_evidence)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file-alt fa-2x text-primary mr-3"></i>
                                        <div>
                                            <div class="font-weight-bold text-gray-900">Berkas File Evidence (Maks 10 MB)</div>
                                            <small class="text-muted">{{ $tugas->file_evidence_name ?? 'Bukti_Tugas.file' }}</small>
                                        </div>
                                    </div>
                                    <a href="{{ asset('storage/' . $tugas->file_evidence) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-download mr-1"></i> Unduh / Buka
                                    </a>
                                </div>
                            @endif

                            <!-- Bukti Link URL -->
                            @if($tugas->link_evidence)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-link fa-2x text-info mr-3"></i>
                                        <div style="max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <div class="font-weight-bold text-gray-900">Tautan Link Evidence</div>
                                            <small class="text-info">{{ $tugas->link_evidence }}</small>
                                        </div>
                                    </div>
                                    <a href="{{ $tugas->link_evidence }}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-external-link-alt mr-1"></i> Buka Link
                                    </a>
                                </div>
                            @endif
                        </div>

                        @if(!$tugas->file_evidence && !$tugas->link_evidence)
                            <div class="alert alert-warning small">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Belum ada file atau link evidence yang dilampirkan.
                            </div>
                        @endif

                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-hourglass-half fa-3x mb-3 text-gray-300 d-block"></i>
                            <p class="font-weight-bold mb-1">Tugas Belum Ditindaklanjuti</p>
                            <small>Petugas pelaksana belum mengirimkan laporan hasil tindak lanjut dan bukti evidence.</small>
                            
                            @if(($isKasubbag ?? false) && $tugas->petugas_id === Auth::id() && $tugas->status !== 'Selesai')
                                <div class="mt-4">
                                    <button type="button" class="btn btn-primary mr-2" data-toggle="modal" data-target="#modalDelegasikanTugasShow">
                                        <i class="fas fa-share-square mr-1"></i> Delegasikan ke Petugas
                                    </button>
                                    <a href="{{ route('tugas.tindak-lanjut', $tugas->id) }}" class="btn btn-success">
                                        <i class="fas fa-reply mr-1"></i> Kerjakan & Tindak Lanjut Sendiri
                                    </a>
                                </div>
                            @elseif(!$isAdmin && $tugas->petugas_id === Auth::id() && in_array($tugas->status, ['Didelegasikan', 'Sedang Dikerjakan', 'Perlu Revisi']))
                                <div class="mt-4">
                                    <a href="{{ route('tugas.tindak-lanjut', $tugas->id) }}" class="btn btn-success">
                                        <i class="fas fa-reply mr-1"></i> Tindak Lanjut Sekarang
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Form Verifikasi Admin / Kepala Sub Bagian Pemberi Tugas -->
                    @if(($canManageTask ?? $isAdmin) && $tugas->status !== 'Selesai')
                        <hr class="mt-4 mb-4">
                        <div class="card border-left-success bg-light p-3">
                            <h6 class="font-weight-bold text-gray-900 mb-2">
                                <i class="fas fa-check-double mr-1"></i> Panel Verifikasi Pemberi Tugas
                            </h6>
                            <form action="{{ route('tugas.verifikasi', $tugas->id) }}" method="POST">
                                @csrf
                                <div class="form-group mb-2">
                                    <label for="catatan_admin" class="small font-weight-bold text-muted">Catatan Verifikasi / Catatan Revisi:</label>
                                    <textarea class="form-control form-control-sm" id="catatan_admin" name="catatan_admin" rows="2" placeholder="Tuliskan catatan apresiasi atau arahan perbaikan jika meminta revisi...">{{ old('catatan_admin', $tugas->catatan_admin) }}</textarea>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" name="status" value="Perlu Revisi" class="btn btn-sm btn-outline-danger mr-2" onclick="return confirm('Minta petugas melakukan revisi pada tugas ini?');">
                                        <i class="fas fa-undo mr-1"></i> Minta Revisi
                                    </button>
                                    <button type="submit" name="status" value="Selesai" class="btn btn-sm btn-success" onclick="return confirm('Apakah Anda yakin menyetujui hasil tindak lanjut ini dan menandai tugas Selesai?');">
                                        <i class="fas fa-check-circle mr-1"></i> Setujui & Tandai Selesai
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    @if($tugas->status === 'Selesai')
                        <div class="alert alert-success mt-4 text-center mb-2">
                            <i class="fas fa-check-circle fa-2x d-block mb-2"></i>
                            <strong>Tugas ini telah diverifikasi dan dinyatakan Selesai (Closed).</strong>
                        </div>

                        @if($isAdmin || (($isKasubbag ?? false) && $tugas->admin_id === Auth::id()))
                            <div class="card border-left-warning bg-light p-3 mt-3">
                                <div class="d-flex align-items-center justify-content-between flex-wrap">
                                    <div class="mr-2">
                                        <h6 class="font-weight-bold text-gray-900 mb-1">
                                            <i class="fas fa-redo text-warning mr-1"></i> Buka / Delegasikan Ulang Tugas
                                        </h6>
                                        <small class="text-muted">Jika ditemukan kesalahan pengerjaan, buka kembali tugas ini untuk didelegasikan ulang ke petugas pelaksana.</small>
                                    </div>
                                    <button type="button" class="btn btn-warning btn-sm font-weight-bold mt-2 mt-sm-0" data-toggle="modal" data-target="#modalBukaKembaliTugasShow">
                                        <i class="fas fa-redo mr-1"></i> Buka Ulang Tugas
                                    </button>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Card Detail Log & Riwayat Aktivitas Tugas -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white border-bottom">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-history mr-1"></i> Detail Log & Riwayat Aktivitas Tugas
            </h6>
            <span class="badge badge-light border text-muted">
                <i class="fas fa-stream mr-1"></i> {{ $tugas->logs ? $tugas->logs->count() : 0 }} Aktivitas
            </span>
        </div>
        <div class="card-body p-3">
            @if($tugas->logs && $tugas->logs->count() > 0)
                <div class="timeline-activity">
                    @foreach($tugas->logs as $log)
                        @php
                            $badgeClass = 'badge-info';
                            $iconClass = 'fa-paper-plane';
                            $borderLeftColor = '#36b9cc';
                            $headerTextClass = 'text-info';

                            if ($log->status === 'Sedang Dikerjakan') {
                                $badgeClass = 'badge-primary';
                                $iconClass = 'fa-spinner fa-spin';
                                $borderLeftColor = '#4e73df';
                                $headerTextClass = 'text-primary';
                            } elseif ($log->status === 'Menunggu Konfirmasi') {
                                $badgeClass = 'badge-warning text-dark';
                                $iconClass = 'fa-reply';
                                $borderLeftColor = '#f6c23e';
                                $headerTextClass = 'text-warning';
                            } elseif ($log->status === 'Perlu Revisi') {
                                $badgeClass = 'badge-danger';
                                $iconClass = 'fa-exclamation-triangle';
                                $borderLeftColor = '#e74a3b';
                                $headerTextClass = 'text-danger';
                            } elseif ($log->status === 'Selesai') {
                                $badgeClass = 'badge-success';
                                $iconClass = 'fa-check-circle';
                                $borderLeftColor = '#1cc88a';
                                $headerTextClass = 'text-success';
                            }
                        @endphp
                        <div class="border rounded bg-white shadow-sm p-2 px-3 mb-2" style="border-left: 4px solid {{ $borderLeftColor }} !important;">
                            <div class="d-flex flex-wrap justify-content-between align-items-center">
                                <div class="d-flex align-items-center flex-wrap mr-2">
                                    <span class="badge {{ $badgeClass }} badge-pill px-2 py-1 mr-2 small">
                                        <i class="fas {{ $iconClass }} mr-1"></i> {{ $log->status }}
                                    </span>
                                    <span class="font-weight-bold text-gray-900 small mr-1">
                                        {{ $log->user ? $log->user->name : 'Sistem' }}
                                    </span>
                                    @if($log->user && $log->user->jabatan)
                                        <span class="badge badge-light border text-muted small mr-2">
                                            {{ $log->user->jabatan }}
                                        </span>
                                    @endif
                                    <span class="text-gray-800 small">{{ $log->keterangan }}</span>
                                </div>
                                <div class="text-muted small mt-1 mt-md-0 text-nowrap">
                                    <i class="far fa-calendar-alt mr-1 text-primary"></i>{{ $log->created_at->translatedFormat('d F Y') }},
                                    <i class="far fa-clock ml-1 mr-1 text-info"></i>{{ $log->created_at->format('H:i:s') }} WIB
                                </div>
                            </div>

                            @if($log->catatan)
                                <div class="mt-1 pt-1 border-top text-gray-800 small">
                                    <strong class="{{ $headerTextClass }}">
                                        <i class="fas fa-comment-dots mr-1"></i>
                                        @if($log->status === 'Perlu Revisi')
                                            Catatan / Arahan Revisi dari Pendelegasi:
                                        @elseif($log->status === 'Selesai')
                                            Catatan Verifikasi / Penutupan (Closed):
                                        @elseif($log->action === 'delegasi')
                                            Catatan / Arahan Pendelegasi:
                                        @else
                                            Catatan / Laporan Tindak Lanjut:
                                        @endif
                                    </strong>
                                    <span class="text-dark ml-1" style="white-space: pre-line;">{{ trim($log->catatan) }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-3 text-muted">
                    <i class="fas fa-clipboard-list fa-2x mb-2 text-gray-300 d-block"></i>
                    Belum ada riwayat aktivitas log untuk tugas ini.
                </div>
            @endif
        </div>
    </div>

    @if(($isKasubbag ?? false) && $tugas->petugas_id === Auth::id() && $tugas->status !== 'Selesai')
        <!-- Modal Delegasikan ke Petugas -->
        <div class="modal fade" id="modalDelegasikanTugasShow" tabindex="-1" role="dialog" aria-labelledby="modalDelegasiShowLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalDelegasiShowLabel">
                            <i class="fas fa-share-square mr-1"></i> Delegasikan Tugas ke Petugas
                        </h5>
                        <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <form action="{{ route('tugas.delegasikan', $tugas->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-light border small mb-3">
                                <strong>Judul Tugas:</strong> {{ $tugas->judul }}<br>
                                <strong>Diterima dari:</strong> {{ $tugas->admin->name ?? 'Admin' }}
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold small text-gray-800">Pilih Petugas Pelaksana (Bisa Lebih dari 1 Orang) <span class="text-danger">*</span></label>
                                <div class="border rounded p-2 bg-light" style="max-height: 190px; overflow-y: auto;">
                                    @foreach($petugasList ?? [] as $p)
                                        @if($p->jabatan === 'Petugas')
                                            <div class="custom-control custom-checkbox mb-1">
                                                <input type="checkbox" class="custom-control-input" id="delegasi_show_p_{{ $p->id }}" name="petugas_id[]" value="{{ $p->id }}">
                                                <label class="custom-control-label small text-gray-900" for="delegasi_show_p_{{ $p->id }}">
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
                                <input type="date" class="form-control" name="tanggal_selesai" value="{{ $tugas->tanggal_selesai ? $tugas->tanggal_selesai->format('Y-m-d') : '' }}">
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

    @if(($isAdmin || (($isKasubbag ?? false) && $tugas->admin_id === Auth::id())) && $tugas->status === 'Selesai')
        <!-- Modal Buka Kembali / Delegasikan Ulang Tugas -->
        <div class="modal fade" id="modalBukaKembaliTugasShow" tabindex="-1" role="dialog" aria-labelledby="modalBukaKembaliShowLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title font-weight-bold" id="modalBukaKembaliShowLabel">
                            <i class="fas fa-redo mr-1"></i> Buka Kembali & Delegasikan Ulang Tugas
                        </h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <form action="{{ route('tugas.buka-kembali', $tugas->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-light border small mb-3">
                                <strong>Judul Tugas:</strong> {{ $tugas->judul }}<br>
                                <strong>Petugas Pelaksana:</strong> {{ $tugas->petugas->name ?? '-' }} (NIP: {{ $tugas->petugas->nip ?? '-' }})
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold small text-gray-800">
                                    Catatan Kesalahan / Instruksi Delegasi Ulang <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" name="catatan" rows="4" placeholder="Jelaskan kesalahan pengerjaan yang ditemukan dan instruksi perbaikan yang harus dilakukan oleh petugas..." required></textarea>
                                <small class="form-text text-muted">Catatan ini akan otomatis masuk ke dalam log riwayat dan dikirimkan ke petugas pelaksana.</small>
                            </div>

                            <div class="form-group mb-0">
                                <label class="font-weight-bold small text-gray-800">Perpanjangan Batas Selesai / Deadline (Opsional)</label>
                                <input type="date" class="form-control" name="tanggal_selesai" value="{{ $tugas->tanggal_selesai ? $tugas->tanggal_selesai->format('Y-m-d') : '' }}">
                                <small class="form-text text-muted">Sesuaikan tanggal deadline baru jika diperlukan waktu tambahan untuk pengerjaan ulang.</small>
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
@endsection

