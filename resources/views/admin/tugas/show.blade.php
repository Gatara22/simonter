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
            @if(($canManageTask ?? $isAdmin) && $tugas->status !== 'Selesai')
                <a href="{{ route('tugas.edit', $tugas->id) }}" class="btn btn-sm btn-warning shadow-sm ml-2 text-dark font-weight-bold">
                    <i class="fas fa-edit fa-sm mr-1"></i> Edit Tugas
                </a>
                <button type="button" class="btn btn-sm btn-info shadow-sm ml-2 font-weight-bold" data-toggle="modal" data-target="#modalKelolaKolaborator">
                    <i class="fas fa-users fa-sm mr-1"></i> Kelola Multi Petugas
                </button>
                @if($tugas->status === 'HOLD')
                    <button type="button" class="btn btn-sm btn-primary shadow-sm ml-2" data-toggle="modal" data-target="#modalHoldTugasShow">
                        <i class="fas fa-play-circle fa-sm text-white-50 mr-1"></i> Cabut HOLD &amp; Lanjutkan
                    </button>
                @else
                    <button type="button" class="btn btn-sm btn-dark shadow-sm ml-2" data-toggle="modal" data-target="#modalHoldTugasShow">
                        <i class="fas fa-pause-circle fa-sm text-white-50 mr-1"></i> Set Status HOLD
                    </button>
                @endif
            @endif
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
            @if(!$isAdmin && ($isAssignedOfficer ?? ($tugas->petugas_id === Auth::id())) && in_array($tugas->status, ['Didelegasikan', 'Sedang Dikerjakan', 'Perlu Revisi']))
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

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-exclamation-triangle mr-1"></i> Terdapat kesalahan pada input:</strong>
            <ul class="mb-0 mt-1 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Visual Lifecycle Progress Stepper -->
    @php
        $stepIndex = match ($tugas->status) {
            'Didelegasikan' => 1,
            'Sedang Dikerjakan', 'HOLD', 'Perlu Revisi' => 2,
            'Menunggu Konfirmasi' => 3,
            'Selesai' => 4,
            default => 1,
        };
    @endphp
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3 px-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                <span class="small font-weight-bold text-gray-800">
                    <i class="fas fa-route text-primary mr-1"></i> Progres Siklus Penyelesaian Tugas
                </span>
                @if($tugas->status === 'HOLD')
                    <span class="badge badge-secondary px-2 py-1" style="background-color: #475569;">
                        <i class="fas fa-pause-circle mr-1"></i> Dijeda Sementara (HOLD)
                    </span>
                @elseif($tugas->status === 'Menunggu Persetujuan Atasan')
                    <span class="badge badge-warning text-dark px-2 py-1">
                        <i class="fas fa-user-check mr-1"></i> Menunggu Persetujuan {{ $tugas->jabatan_atasan_penyetuju ?? 'Atasan' }}
                    </span>
                @elseif($tugas->status === 'Ditolak Atasan')
                    <span class="badge badge-danger px-2 py-1">
                        <i class="fas fa-ban mr-1"></i> Ditolak oleh Atasan Unit Terkait
                    </span>
                @elseif($tugas->status === 'Perlu Revisi')
                    <span class="badge badge-danger px-2 py-1">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Sedang Perlu Revisi
                    </span>
                @endif
            </div>
            <div class="row text-center small">
                @foreach([
                    1 => ['label' => '1. Didelegasikan', 'icon' => 'fa-paper-plane'],
                    2 => ['label' => '2. Sedang Dikerjakan', 'icon' => 'fa-cogs'],
                    3 => ['label' => '3. Menunggu Konfirmasi', 'icon' => 'fa-reply-all'],
                    4 => ['label' => '4. Selesai (Closed)', 'icon' => 'fa-check-double'],
                ] as $num => $step)
                    @php
                        $isDone = $stepIndex > $num || ($stepIndex === 4 && $num === 4);
                        $isCurrent = $stepIndex === $num && $stepIndex < 4;
                    @endphp
                    <div class="col-6 col-md-3 mb-2 mb-md-0">
                        <div class="p-2 rounded border {{ $isDone ? 'bg-success text-white border-success font-weight-bold' : ($isCurrent ? 'bg-primary text-white border-primary font-weight-bold shadow-sm' : 'bg-light text-muted') }}">
                            <i class="fas {{ $isDone ? 'fa-check-circle' : $step['icon'] }} mr-1"></i>
                            {{ $step['label'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

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
                        @elseif($tugas->status === 'HOLD')
                            <span class="badge badge-secondary badge-pill px-3 py-1 font-weight-bold" style="background-color: #475569;"><i class="fas fa-pause-circle mr-1"></i> HOLD</span>
                        @elseif($tugas->status === 'Menunggu Konfirmasi')
                            <span class="badge badge-warning badge-pill px-3 py-1 font-weight-bold text-dark"><i class="fas fa-paper-plane mr-1"></i> Menunggu Konfirmasi</span>
                        @elseif($tugas->status === 'Menunggu Persetujuan Atasan')
                            <span class="badge badge-warning badge-pill px-3 py-1 font-weight-bold text-dark"><i class="fas fa-user-check mr-1"></i> Menunggu Persetujuan Atasan</span>
                        @elseif($tugas->status === 'Ditolak Atasan')
                            <span class="badge badge-danger badge-pill px-3 py-1 font-weight-bold"><i class="fas fa-ban mr-1"></i> Ditolak Atasan</span>
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
                            <td>
                                : <span class="font-weight-bold">{{ $tugas->petugas->name ?? '-' }}</span>
                                (NIP: {{ $tugas->petugas->nip ?? '-' }})
                                <span class="badge badge-primary ml-1">PIC Utama</span>
                                <span class="badge badge-light border ml-1">{{ $tugas->petugas->jabatan ?? 'Petugas' }}</span>

                                @if($tugas->kolaborator && $tugas->kolaborator->count() > 0)
                                    <div class="mt-2 pt-2 border-top">
                                        <div class="small font-weight-bold text-info mb-1">
                                            <i class="fas fa-users mr-1"></i> Tim Kolaborator ({{ $tugas->kolaborator->count() }} Petugas Tambahan):
                                        </div>
                                        <div class="d-flex flex-wrap" style="gap: 6px;">
                                            @foreach($tugas->kolaborator as $kolab)
                                                <span class="badge badge-info px-2 py-1 font-weight-normal">
                                                    <i class="fas fa-user mr-1"></i> {{ $kolab->name }}
                                                    <span class="opacity-75">({{ $kolab->jabatan }})</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                @if(($canManageTask ?? $isAdmin) && $tugas->status !== 'Selesai')
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-xs btn-outline-info py-1 px-2 small font-weight-bold" data-toggle="modal" data-target="#modalKelolaKolaborator">
                                            <i class="fas fa-user-plus mr-1"></i> {{ ($tugas->kolaborator && $tugas->kolaborator->count() > 0) ? 'Ubah Tim Multi Petugas' : 'Tambah Petugas Kolaborator' }}
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Periode Pengerjaan</th>
                            <td>: {{ $tugas->tanggal_mulai ? $tugas->tanggal_mulai->format('d F Y') : '-' }} s/d {{ $tugas->tanggal_selesai ? $tugas->tanggal_selesai->format('d F Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Tanggal Delegasi</th>
                            <td>: {{ $tugas->created_at ? $tugas->created_at->format('d/m/Y H:i') : '-' }}</td>
                        </tr>
                        @if($tugas->status === 'HOLD' || $tugas->getHoldDurationSeconds() > 0)
                            <tr>
                                <th class="text-muted">Total Durasi HOLD</th>
                                <td>:
                                    <span class="badge badge-secondary px-2 py-1" style="background-color: #475569;">
                                        <i class="fas fa-pause-circle mr-1"></i> {{ $tugas->getHoldDurationDays() }} Hari (Dikecualikan dari Keterlambatan)
                                    </span>
                                </td>
                            </tr>
                        @endif
                        @if($tugas->status === 'Selesai')
                            <tr>
                                <th class="text-muted">Durasi Kerja Efektif</th>
                                <td>:
                                    <strong class="text-gray-900">{{ $tugas->getEffectiveWorkingDurationDays() }} Hari</strong>
                                    @if($tugas->isCompletedOnTime())
                                        <span class="badge badge-success ml-1 px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Tepat Waktu</span>
                                    @else
                                        <span class="badge badge-warning text-dark ml-1 px-2 py-1"><i class="fas fa-clock mr-1"></i> Melebihi Deadline</span>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    </table>

                    @if($tugas->status === 'HOLD')
                        <div class="alert alert-secondary border-left-dark shadow-sm small mb-3" style="border-left: 4px solid #475569;">
                            <div class="font-weight-bold text-dark mb-1">
                                <i class="fas fa-pause-circle mr-1"></i> Tugas Sedang Berstatus HOLD (Ditunda Sementara)
                            </div>
                            Selama dalam status <strong>HOLD</strong>, durasi penundaan tidak terhitung apabila terjadi keterlambatan performa penyelesaian tugas. Perhitungan durasi hanya dihitung selama masa pengerjaan aktif sampai dengan tugas dinyatakan <strong>Selesai (Closed)</strong>.
                        </div>
                    @endif

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

                    {{-- Ringkasan Status Permohonan Petugas (jika pernah diajukan) --}}
                    @if($tugas->status_permohonan)
                        <div class="card shadow-sm p-3 mt-3 {{ $tugas->status_permohonan === 'Menunggu Persetujuan' ? 'border-left-warning bg-light' : ($tugas->status_permohonan === 'Disetujui' ? 'border-left-success' : 'border-left-secondary') }}">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                                <h6 class="font-weight-bold text-gray-900 mb-0">
                                    <i class="fas fa-hand-paper text-warning mr-1"></i> Detail Permohonan Ceklist Petugas
                                </h6>
                                @if($tugas->status_permohonan === 'Menunggu Persetujuan')
                                    <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-clock mr-1"></i> Menunggu Persetujuan</span>
                                @elseif($tugas->status_permohonan === 'Disetujui')
                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Disetujui Pemberi Tugas</span>
                                @else
                                    <span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i> Ditolak</span>
                                @endif
                            </div>
                            <div class="mb-2">
                                @if($tugas->permohonan_hold)
                                    <span class="badge badge-secondary px-2 py-1 mr-1" style="background-color: #475569;">
                                        <i class="fas fa-check-square mr-1"></i> Meminta HOLD Tugas
                                    </span>
                                @endif
                                @if($tugas->permohonan_perpanjangan)
                                    <span class="badge badge-info px-2 py-1">
                                        <i class="fas fa-check-square mr-1"></i> Meminta Edit / Perpanjangan Masa Pengerjaan
                                        @if($tugas->usulan_tanggal_selesai)
                                            (Usulan: {{ $tugas->usulan_tanggal_selesai->format('d/m/Y') }})
                                        @endif
                                    </span>
                                @endif
                            </div>
                            <div class="small text-gray-800" style="white-space: pre-line;">
                                <strong>Alasan Petugas:</strong> {{ $tugas->catatan_permohonan }}
                            </div>
                            @if($tugas->tanggal_permohonan)
                                <small class="text-muted d-block mt-1">
                                    <i class="far fa-clock mr-1"></i> Diajukan pada: {{ $tugas->tanggal_permohonan->format('d/m/Y H:i') }} WIB
                                </small>
                            @endif
                        </div>
                    @endif

                    {{-- Panel Ceklist Permohonan dari Sisi User Petugas --}}
                    @if(($isAssignedOfficer ?? ($tugas->petugas_id === Auth::id())) && $tugas->status !== 'Selesai')
                        <div class="card border-left-primary shadow-sm p-3 mt-3" style="background-color: #f8fafc;">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                                <h6 class="font-weight-bold text-primary mb-0">
                                    <i class="fas fa-tasks mr-1"></i> Panel Ceklist Permohonan Petugas kepada Pemberi Tugas
                                </h6>
                                <span class="badge badge-primary px-2 py-1 small">Fitur Pelaksana Tugas</span>
                            </div>
                            <p class="small text-muted mb-3">
                                Centang salah satu atau kedua opsi di bawah ini untuk meminta kepada Pemberi Tugas agar meng-<strong>HOLD</strong> tugas atau mengedit tugas guna <strong>memperpanjang masa pengerjaan</strong>:
                            </p>

                            <form action="{{ route('tugas.permohonan', $tugas->id) }}" method="POST">
                                @csrf
                                <div class="p-2 bg-white rounded border mb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox"
                                               class="custom-control-input"
                                               id="chk_permohonan_hold"
                                               name="jenis_permohonan[]"
                                               value="hold"
                                               {{ (is_array(old('jenis_permohonan')) && in_array('hold', old('jenis_permohonan'))) || $tugas->permohonan_hold ? 'checked' : '' }}>
                                        <label class="custom-control-label font-weight-bold text-gray-900 small" for="chk_permohonan_hold">
                                            <i class="fas fa-pause-circle text-secondary mr-1"></i> Meminta HOLD Tugas (Tunda Sementara Pengerjaan)
                                        </label>
                                    </div>
                                    <small class="text-muted d-block pl-4">
                                        Memohon agar tugas diubah ke status <strong>HOLD</strong> sehingga durasi penundaan tidak dihitung dalam keterlambatan performa.
                                    </small>
                                </div>

                                <div class="p-2 bg-white rounded border mb-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox"
                                               class="custom-control-input"
                                               id="chk_permohonan_perpanjangan"
                                               name="jenis_permohonan[]"
                                               value="perpanjangan"
                                               {{ (is_array(old('jenis_permohonan')) && in_array('perpanjangan', old('jenis_permohonan'))) || $tugas->permohonan_perpanjangan ? 'checked' : '' }}>
                                        <label class="custom-control-label font-weight-bold text-gray-900 small" for="chk_permohonan_perpanjangan">
                                            <i class="fas fa-calendar-plus text-info mr-1"></i> Meminta Edit Tugas untuk Memperpanjang Masa Pengerjaan (Deadline)
                                        </label>
                                    </div>
                                    <small class="text-muted d-block pl-4 mb-2">
                                        Memohon kepada pemberi tugas untuk mengedit batas waktu selesai pengerjaan tugas.
                                    </small>

                                    <div class="pl-4" id="box_usulan_tanggal_selesai">
                                        <label for="usulan_tanggal_selesai" class="small font-weight-bold text-gray-800 mb-1">
                                            Usulan Batas Selesai / Deadline Baru:
                                        </label>
                                        <input type="date"
                                               class="form-control form-control-sm"
                                               id="usulan_tanggal_selesai"
                                               name="usulan_tanggal_selesai"
                                               min="{{ $tugas->tanggal_selesai ? $tugas->tanggal_selesai->format('Y-m-d') : ($tugas->tanggal_mulai ? $tugas->tanggal_mulai->format('Y-m-d') : date('Y-m-d')) }}"
                                               value="{{ old('usulan_tanggal_selesai', $tugas->usulan_tanggal_selesai ? $tugas->usulan_tanggal_selesai->format('Y-m-d') : ($tugas->tanggal_selesai ? $tugas->tanggal_selesai->copy()->addDays(3)->format('Y-m-d') : '')) }}">
                                    </div>
                                </div>

                                <div class="form-group mb-2">
                                    <label for="catatan_permohonan" class="small font-weight-bold text-gray-800">
                                        Alasan / Keterangan Permohonan <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control form-control-sm"
                                              id="catatan_permohonan"
                                              name="catatan_permohonan"
                                              rows="2"
                                              placeholder="Jelaskan kendala teknis atau alasan permohonan Hold / perpanjangan masa pengerjaan tugas..."
                                              required>{{ old('catatan_permohonan', $tugas->catatan_permohonan) }}</textarea>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-sm btn-primary font-weight-bold shadow-sm">
                                        <i class="fas fa-paper-plane mr-1"></i> Kirim Permohonan ke Pemberi Tugas
                                    </button>
                                </div>
                            </form>
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
                            @elseif(!$isAdmin && ($isAssignedOfficer ?? ($tugas->petugas_id === Auth::id())) && in_array($tugas->status, ['Didelegasikan', 'Sedang Dikerjakan', 'Perlu Revisi']))
                                <div class="mt-4">
                                    <a href="{{ route('tugas.tindak-lanjut', $tugas->id) }}" class="btn btn-success">
                                        <i class="fas fa-reply mr-1"></i> Tindak Lanjut Sekarang
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Panel Tindak Lanjut Permohonan Ceklist Petugas (Untuk Admin & Pemberi Tugas) -->
                    @if($tugas->perlu_persetujuan_atasan)
                        <div class="card shadow-sm p-3 mb-3 {{ $tugas->status_persetujuan_atasan === 'Menunggu Persetujuan' ? 'border-left-warning bg-light' : ($tugas->status_persetujuan_atasan === 'Disetujui' ? 'border-left-success' : 'border-left-danger') }}">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                                <h6 class="font-weight-bold text-gray-900 mb-0">
                                    <i class="fas fa-user-check text-primary mr-1"></i> Persetujuan Delegasi Lintas Bidang
                                </h6>
                                @if($tugas->status_persetujuan_atasan === 'Menunggu Persetujuan')
                                    <span class="badge badge-warning text-dark px-2 py-1">Menunggu Persetujuan {{ $tugas->jabatan_atasan_penyetuju }}</span>
                                @elseif($tugas->status_persetujuan_atasan === 'Disetujui')
                                    <span class="badge badge-success px-2 py-1">Disetujui Atasan Unit</span>
                                @else
                                    <span class="badge badge-danger px-2 py-1">Ditolak Atasan Unit</span>
                                @endif
                            </div>
                            <div class="small text-gray-800">
                                Tugas lintas bidang ini memerlukan verifikasi &amp; persetujuan dari <strong>{{ $tugas->jabatan_atasan_penyetuju ?? 'Atasan Unit Terkait' }}</strong>.
                                @if($tugas->atasanPenyetuju)
                                    <div class="mt-1">
                                        <strong>Diverifikasi oleh:</strong> {{ $tugas->atasanPenyetuju->name }} ({{ $tugas->atasanPenyetuju->jabatan }})
                                        @if($tugas->tanggal_persetujuan_atasan)
                                            &bull; {{ $tugas->tanggal_persetujuan_atasan->format('d/m/Y H:i') }} WIB
                                        @endif
                                    </div>
                                @endif
                                @if($tugas->catatan_persetujuan_atasan)
                                    <div class="mt-1"><strong>Catatan Atasan:</strong> {{ $tugas->catatan_persetujuan_atasan }}</div>
                                @endif
                            </div>

                            @if($canApproveCrossUnit ?? false)
                                <hr class="my-3">
                                <form action="{{ route('tugas.persetujuan-atasan', $tugas->id) }}" method="POST">
                                    @csrf
                                    <div class="form-group mb-2">
                                        <label for="catatan_persetujuan_atasan" class="small font-weight-bold text-gray-800 mb-1">
                                            Catatan Verifikasi &amp; Persetujuan Atasan (Opsional):
                                        </label>
                                        <textarea class="form-control form-control-sm"
                                                  id="catatan_persetujuan_atasan"
                                                  name="catatan_persetujuan_atasan"
                                                  rows="2"
                                                  placeholder="Tuliskan arahan, syarat penugasan lintas bidang, atau alasan penolakan..."></textarea>
                                    </div>
                                    <div class="d-flex flex-wrap justify-content-end" style="gap: 6px;">
                                        <button type="submit" name="keputusan" value="tolak" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tolak delegasi lintas bidang ini?');">
                                            <i class="fas fa-ban mr-1"></i> Tolak Delegasi
                                        </button>
                                        <button type="submit" name="keputusan" value="setujui" class="btn btn-sm btn-success font-weight-bold" onclick="return confirm('Setujui dan teruskan delegasi lintas bidang ini kepada petugas pelaksana?');">
                                            <i class="fas fa-check-circle mr-1"></i> Setujui &amp; Teruskan ke Petugas
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    @endif

                    @if(($canManageTask ?? $isAdmin) && $tugas->status_permohonan === 'Menunggu Persetujuan' && $tugas->status !== 'Selesai')
                        <hr class="mt-4 mb-3">
                        <div class="card border-left-warning shadow-sm p-3 mb-3" style="background-color: #fffbeb;">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                                <h6 class="font-weight-bold text-gray-900 mb-0">
                                    <i class="fas fa-bell text-warning mr-1"></i> Tanggapi Permohonan Ceklist dari Petugas Pelaksana
                                </h6>
                                <a href="{{ route('tugas.edit', $tugas->id) }}" class="btn btn-xs btn-outline-primary py-1 px-2 small font-weight-bold">
                                    <i class="fas fa-edit mr-1"></i> Buka Form Edit Tugas
                                </a>
                            </div>
                            <div class="small text-gray-800 mb-2">
                                Petugas <strong>{{ $tugas->petugas->name ?? 'Pelaksana' }}</strong> mengajukan permohonan:
                                @if($tugas->permohonan_hold)
                                    <span class="badge badge-secondary px-2 py-1 ml-1" style="background-color: #475569;">HOLD Tugas</span>
                                @endif
                                @if($tugas->permohonan_perpanjangan)
                                    <span class="badge badge-info px-2 py-1 ml-1">
                                        Perpanjangan Deadline
                                        @if($tugas->usulan_tanggal_selesai)
                                            (s/d {{ $tugas->usulan_tanggal_selesai->format('d/m/Y') }})
                                        @endif
                                    </span>
                                @endif
                            </div>
                            <form action="{{ route('tugas.permohonan.tanggapi', $tugas->id) }}" method="POST">
                                @csrf
                                @if($tugas->permohonan_perpanjangan)
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold text-gray-800 mb-1">Batas Selesai / Deadline Baru (Jika Disetujui):</label>
                                        <input type="date"
                                               class="form-control form-control-sm"
                                               name="tanggal_selesai_baru"
                                               value="{{ $tugas->usulan_tanggal_selesai ? $tugas->usulan_tanggal_selesai->format('Y-m-d') : ($tugas->tanggal_selesai ? $tugas->tanggal_selesai->format('Y-m-d') : '') }}">
                                    </div>
                                @endif
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold text-gray-800 mb-1">Catatan Tanggapan Pemberi Tugas (Opsional):</label>
                                    <textarea class="form-control form-control-sm" name="catatan_admin" rows="2" placeholder="Tuliskan catatan persetujuan atau alasan penolakan permohonan..."></textarea>
                                </div>
                                <div class="d-flex flex-wrap justify-content-end" style="gap: 6px;">
                                    <button type="submit" name="keputusan" value="tolak" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-times-circle mr-1"></i> Tolak Permohonan
                                    </button>
                                    <button type="submit" name="keputusan" value="setujui" class="btn btn-sm btn-warning text-dark font-weight-bold">
                                        <i class="fas fa-check-circle mr-1"></i> Setujui Permohonan Petugas
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <!-- Form Verifikasi Admin / Kepala Sub Bagian / Panitera Muda Pemberi Tugas -->
                    @if(($canManageTask ?? $isAdmin) && $tugas->status !== 'Selesai')
                        <hr class="mt-4 mb-4">
                        <div class="card border-left-success bg-light p-3">
                            <h6 class="font-weight-bold text-gray-900 mb-1">
                                <i class="fas fa-check-double mr-1"></i> Panel Verifikasi &amp; Kendali Status Pemberi Tugas
                            </h6>
                            <p class="small text-muted mb-2">
                                <i class="fas fa-lock text-success mr-1"></i> Tugas hanya dapat dinyatakan <strong>Selesai (Closed)</strong> oleh <strong>Pemberi Tugas (Author)</strong> atau <strong>Admin</strong> — termasuk bila progres diselesaikan lewat ruang chat diskusi tanpa unggah evidence formal.
                            </p>
                            <form action="{{ route('tugas.verifikasi', $tugas->id) }}" method="POST">
                                @csrf
                                <div class="form-group mb-2">
                                    <label for="catatan_admin" class="small font-weight-bold text-muted">Catatan Verifikasi / Catatan Revisi / Alasan HOLD:</label>
                                    <textarea class="form-control form-control-sm" id="catatan_admin" name="catatan_admin" rows="2" placeholder="Tuliskan catatan verifikasi, arahan revisi, atau keterangan penundaan (HOLD)...">{{ old('catatan_admin', $tugas->catatan_admin) }}</textarea>
                                </div>
                                <div class="d-flex flex-wrap justify-content-end" style="gap: 6px;">
                                    @if($tugas->status === 'HOLD')
                                        <button type="submit" name="status" value="Sedang Dikerjakan" class="btn btn-sm btn-primary" onclick="return confirm('Cabut status HOLD dan lanjutkan pengerjaan tugas ini?');">
                                            <i class="fas fa-play-circle mr-1"></i> Cabut HOLD &amp; Lanjutkan
                                        </button>
                                    @else
                                        <button type="submit" name="status" value="HOLD" class="btn btn-sm btn-secondary" style="background-color: #475569;" onclick="return confirm('Ubah status tugas ini menjadi HOLD? Selama berstatus HOLD, durasi tidak dihitung dalam keterlambatan performa.');">
                                            <i class="fas fa-pause-circle mr-1"></i> Set Status HOLD
                                        </button>
                                    @endif
                                    <button type="submit" name="status" value="Perlu Revisi" class="btn btn-sm btn-outline-danger" onclick="return confirm('Minta petugas melakukan revisi pada tugas ini?');">
                                        <i class="fas fa-undo mr-1"></i> Minta Revisi
                                    </button>
                                    <button type="submit" name="status" value="Selesai" class="btn btn-sm btn-success" onclick="return confirm('Apakah Anda yakin menyetujui hasil tindak lanjut ini dan menandai tugas Selesai?');">
                                        <i class="fas fa-check-circle mr-1"></i> Setujui &amp; Tandai Selesai
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

    <!-- Ruang Diskusi & Chat Progres Pekerjaan (Multi-Petugas & Author) -->
    <style>
        .chat-card {
            border-radius: 0.75rem;
            overflow: hidden;
        }
        .chat-header-bar {
            padding: 0.6rem 0.9rem;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
        }
        .chat-header-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #1e40af;
            margin: 0;
        }
        .chat-header-sub {
            font-size: 0.7rem;
            color: #64748b;
        }
        .chat-avatar-strip {
            display: inline-flex;
            align-items: center;
        }
        .chat-avatar-mini {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ffffff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.16);
            margin-left: -6px;
            background: #e2e8f0;
        }
        .chat-avatar-mini:first-child {
            margin-left: 0;
        }
        .chat-filter-btn {
            font-size: 0.68rem;
            padding: 0.15rem 0.5rem;
            border-radius: 999px;
            font-weight: 600;
            transition: all 0.15s ease;
        }
        .chat-micro-notice {
            font-size: 0.71rem;
            line-height: 1.35;
            padding: 0.35rem 0.75rem;
            background-color: #eef6ff;
            border-left: 3px solid #0ea5e9;
            border-radius: 0.45rem;
            color: #334155;
            margin-bottom: 0.6rem;
        }
        .chat-canvas {
            max-height: 360px;
            overflow-y: auto;
            padding: 0.75rem;
            border-radius: 0.65rem;
            border: 1px solid #e2e8f0;
            background-color: #f4f7fb;
            background-image: radial-gradient(#dce4ef 1px, transparent 1px);
            background-size: 16px 16px;
        }
        .chat-canvas::-webkit-scrollbar {
            width: 6px;
        }
        .chat-canvas::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }
        .chat-date-divider {
            display: flex;
            justify-content: center;
            margin: 0.45rem 0 0.65rem;
        }
        .chat-date-pill {
            font-size: 0.65rem;
            font-weight: 700;
            color: #475569;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid #e2e8f0;
            padding: 0.12rem 0.6rem;
            border-radius: 999px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }
        .chat-row {
            display: flex;
            align-items: flex-end;
            gap: 6px;
            margin-bottom: 0.55rem;
            animation: chatFadeUp 0.18s ease-out;
        }
        @keyframes chatFadeUp {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .chat-avatar {
            width: 26px;
            height: 26px;
            min-width: 26px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #cbd5e1;
            background: #ffffff;
        }
        .chat-bubble {
            max-width: 78%;
            min-width: 150px;
            padding: 0.42rem 0.68rem;
            font-size: 0.77rem;
            line-height: 1.38;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.06);
        }
        .chat-bubble-own {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            color: #ffffff;
            border-radius: 0.75rem 0.75rem 0.2rem 0.75rem;
        }
        .chat-bubble-other {
            background: #ffffff;
            color: #1e293b;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem 0.75rem 0.75rem 0.2rem;
        }
        .chat-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            margin-bottom: 0.18rem;
        }
        .chat-sender-name {
            font-size: 0.71rem;
            font-weight: 700;
        }
        .chat-role-badge {
            font-size: 0.61rem;
            padding: 0.08rem 0.38rem;
            border-radius: 999px;
            font-weight: 600;
        }
        .chat-time {
            font-size: 0.64rem;
            opacity: 0.8;
            white-space: nowrap;
        }
        .chat-text {
            font-size: 0.77rem;
            line-height: 1.38;
            white-space: pre-line;
            word-break: break-word;
        }
        .chat-attachment {
            margin-top: 0.35rem;
            padding: 0.32rem 0.5rem;
            border-radius: 0.45rem;
            font-size: 0.7rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #1e293b;
        }
        .chat-attachment-url {
            font-size: 0.64rem;
            color: #0284c7;
            margin-top: 0.15rem;
            word-break: break-all;
        }
        .chat-composer-shell {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 0.65rem;
            padding: 0.5rem 0.65rem;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .chat-composer-shell:focus-within {
            border-color: #3b82f6;
            box-shadow: 0 0 0 0.15rem rgba(59, 130, 246, 0.15);
        }
        .chat-quick-chip {
            font-size: 0.67rem;
            padding: 0.1rem 0.48rem;
            border-radius: 999px;
            border: 1px solid #dbeafe;
            background: #eff6ff;
            color: #1d4ed8;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .chat-quick-chip:hover {
            background: #dbeafe;
            border-color: #93c5fd;
        }
        .chat-composer-textarea {
            font-size: 0.78rem !important;
            line-height: 1.38;
            min-height: 34px;
            max-height: 110px;
            border: 0 !important;
            box-shadow: none !important;
            padding: 0.25rem 0.1rem !important;
            resize: none;
            background: transparent !important;
        }
        .chat-icon-btn {
            width: 30px;
            height: 30px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.45rem;
            font-size: 0.76rem;
        }
        .chat-file-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.7rem;
            background: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            padding: 0.15rem 0.55rem;
        }
    </style>

    @php
        $chatParticipants = collect([$tugas->admin])->merge($tugas->allPetugas())->filter()->unique('id')->values();
        $diskusiCollection = $tugas->diskusi ?? collect();
    @endphp

    <div class="card shadow mb-4 chat-card" id="ruang-diskusi">
        <div class="chat-header-bar d-flex flex-wrap justify-content-between align-items-center" style="gap: 8px;">
            <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                <div>
                    <h6 class="chat-header-title">
                        <i class="fas fa-comments mr-1"></i> Ruang Diskusi &amp; Chat Progres Pekerjaan
                    </h6>
                    <div class="chat-header-sub">Komunikasi fleksibel Pemberi Tugas (Author) &amp; Tim Multi-Petugas</div>
                </div>
                <div class="chat-avatar-strip d-none d-sm-inline-flex" title="Partisipan: {{ $chatParticipants->pluck('name')->implode(', ') }}">
                    @foreach($chatParticipants->take(5) as $part)
                        <img src="{{ $part->foto_url }}" alt="{{ $part->name }}" class="chat-avatar-mini" title="{{ $part->name }} ({{ $part->jabatan }})">
                    @endforeach
                    @if($chatParticipants->count() > 5)
                        <span class="badge badge-light border ml-1" style="font-size: 0.64rem;">+{{ $chatParticipants->count() - 5 }}</span>
                    @endif
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                <div class="btn-group" role="group" aria-label="Filter Chat">
                    <button type="button" class="btn btn-primary chat-filter-btn active" data-chat-filter="all">
                        Semua
                    </button>
                    <button type="button" class="btn btn-outline-secondary chat-filter-btn" data-chat-filter="attachments">
                        <i class="fas fa-paperclip mr-1"></i>Lampiran
                    </button>
                </div>
                <span class="badge badge-primary px-2 py-1" style="font-size: 0.68rem;">
                    <i class="fas fa-users mr-1"></i> {{ $tugas->allPetugas()->count() + 1 }} Partisipan
                </span>
                <span class="badge badge-info px-2 py-1" style="font-size: 0.68rem;">
                    <i class="fas fa-comment-dots mr-1"></i> {{ $diskusiCollection->count() }} Pesan
                </span>
            </div>
        </div>

        <div class="card-body p-2 p-md-3">
            <div class="chat-micro-notice">
                <i class="fas fa-info-circle text-info mr-1"></i>
                Kirim update progres tanpa wajib unggah evidence. Berkas/link otomatis tercatat sebagai <strong>link URL di Log Aktivitas</strong> tanpa mengubah status ke <em>Menunggu Konfirmasi</em>; hanya <strong>Author / Admin</strong> yang dapat menutup tugas (<em>Selesai</em>).
            </div>

            <div id="chatDiskusiContainer" class="chat-canvas mb-2">
                @php
                    $lastDateKey = null;
                @endphp
                @forelse($diskusiCollection as $chat)
                    @php
                        $isOwnMessage = (int) $chat->user_id === (int) Auth::id();
                        $sender = $chat->user;
                        $senderRoleTag = 'Partisipan';
                        $senderRoleClass = 'badge-secondary';
                        if ($sender) {
                            if ((int) $sender->id === (int) $tugas->admin_id) {
                                $senderRoleTag = 'Author';
                                $senderRoleClass = 'badge-dark';
                            } elseif ($sender->jabatan === 'Admin') {
                                $senderRoleTag = 'Admin';
                                $senderRoleClass = 'badge-danger';
                            } elseif ((int) $sender->id === (int) $tugas->petugas_id) {
                                $senderRoleTag = 'PIC Utama';
                                $senderRoleClass = 'badge-primary';
                            } else {
                                $senderRoleTag = 'Kolaborator';
                                $senderRoleClass = 'badge-info';
                            }
                        }

                        $msgDate = $chat->created_at;
                        $dateKey = $msgDate ? $msgDate->format('Y-m-d') : null;
                        $dateLabel = null;
                        if ($dateKey !== null && $dateKey !== $lastDateKey) {
                            $lastDateKey = $dateKey;
                            if ($msgDate->isToday()) {
                                $dateLabel = 'Hari Ini';
                            } elseif ($msgDate->isYesterday()) {
                                $dateLabel = 'Kemarin';
                            } else {
                                $dateLabel = $msgDate->translatedFormat('d M Y');
                            }
                        }
                        $hasAttachment = ($chat->file_path || $chat->link_url) ? '1' : '0';
                    @endphp

                    @if($dateLabel)
                        <div class="chat-date-divider" data-date-group="{{ $dateKey }}">
                            <span class="chat-date-pill">{{ $dateLabel }}</span>
                        </div>
                    @endif

                    <div class="chat-row {{ $isOwnMessage ? 'justify-content-end' : 'justify-content-start' }}" data-has-attachment="{{ $hasAttachment }}">
                        @if(!$isOwnMessage)
                            <img src="{{ $sender->foto_url ?? asset('sbadmin2/img/undraw_profile.svg') }}"
                                 alt="{{ $sender->name ?? 'Pengguna' }}"
                                 class="chat-avatar">
                        @endif

                        <div class="chat-bubble {{ $isOwnMessage ? 'chat-bubble-own' : 'chat-bubble-other' }}">
                            <div class="chat-meta">
                                <div class="d-flex align-items-center flex-wrap" style="gap: 4px;">
                                    <span class="chat-sender-name">{{ $sender->name ?? 'Pengguna' }}</span>
                                    <span class="badge chat-role-badge {{ $isOwnMessage ? 'badge-light text-primary' : $senderRoleClass }}">
                                        {{ $senderRoleTag }}
                                    </span>
                                </div>
                                <span class="chat-time">
                                    {{ $chat->created_at ? $chat->created_at->format('H:i') : '-' }} WIB
                                </span>
                            </div>

                            @if($chat->pesan)
                                <div class="chat-text">{{ $chat->pesan }}</div>
                            @endif

                            @if($chat->file_path)
                                <div class="chat-attachment">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 6px;">
                                        <span class="font-weight-bold text-truncate" style="max-width: 220px;">
                                            <i class="fas fa-paperclip text-info mr-1"></i>{{ $chat->file_name ?? 'Berkas Lampiran' }}
                                        </span>
                                        <a href="{{ $chat->file_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-xs btn-outline-primary py-0 px-2 font-weight-bold" style="font-size: 0.66rem;">
                                            <i class="fas fa-external-link-alt mr-1"></i>Buka
                                        </a>
                                    </div>
                                    <div class="chat-attachment-url text-truncate" style="max-width: 100%;">
                                        <a href="{{ $chat->file_url }}" target="_blank" rel="noopener noreferrer" class="text-info">{{ $chat->file_url }}</a>
                                    </div>
                                </div>
                            @endif

                            @if($chat->link_url)
                                <div class="chat-attachment">
                                    <i class="fas fa-link text-info mr-1"></i>
                                    <a href="{{ $chat->link_url }}" target="_blank" rel="noopener noreferrer" class="font-weight-bold text-info" style="word-break: break-all;">{{ $chat->link_url }}</a>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-comments fa-lg mb-2 text-gray-300 d-block"></i>
                        <div class="font-weight-bold" style="font-size: 0.78rem;">Belum ada pesan diskusi progres.</div>
                        <div style="font-size: 0.71rem;">Mulai koordinasi progres atau bagikan berkas referensi melalui formulir di bawah.</div>
                    </div>
                @endforelse

                <div id="chatFilterEmptyState" class="text-center py-3 text-muted d-none" style="font-size: 0.74rem;">
                    <i class="fas fa-paperclip mr-1"></i> Belum ada pesan yang disertai lampiran berkas atau tautan URL.
                </div>
            </div>

            @if(($canChat ?? false) && $tugas->status !== 'Selesai')
                <form action="{{ route('tugas.diskusi.store', $tugas->id) }}" method="POST" enctype="multipart/form-data" id="formDiskusiProgres" class="chat-composer-shell">
                    @csrf
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-1 pb-1 border-bottom" style="gap: 4px;">
                        <div class="d-flex flex-wrap align-items-center" style="gap: 4px;">
                            <span class="text-muted font-weight-bold mr-1" style="font-size: 0.66rem;">Template Cepat:</span>
                            <button type="button" class="chat-quick-chip" data-chat-template="Update progres: ">Update progres</button>
                            <button type="button" class="chat-quick-chip" data-chat-template="Draft berkas siap dicek: ">Draft siap dicek</button>
                            <button type="button" class="chat-quick-chip" data-chat-template="Mohon arahan terkait kendala: ">Butuh arahan</button>
                        </div>
                        <span class="text-muted d-none d-md-inline" style="font-size: 0.65rem;">
                            <kbd class="bg-light text-dark border px-1">Ctrl</kbd>+<kbd class="bg-light text-dark border px-1">Enter</kbd> kirim
                        </span>
                    </div>

                    <div class="d-flex align-items-end" style="gap: 6px;">
                        <textarea class="form-control form-control-sm chat-composer-textarea @error('pesan') is-invalid @enderror"
                                  id="pesan_diskusi"
                                  name="pesan"
                                  rows="1"
                                  placeholder="Tulis update progres pekerjaan atau catatan koordinasi...">{{ old('pesan') }}</textarea>

                        <input type="file" class="d-none" id="file_lampiran_diskusi" name="file_lampiran">

                        <button type="button" class="btn btn-light border chat-icon-btn" id="btnPickDiskusiFile" title="Unggah File Progres (Maks 10 MB)">
                            <i class="fas fa-paperclip text-info"></i>
                        </button>
                        <button type="button" class="btn btn-light border chat-icon-btn {{ old('link_url') || $errors->has('link_url') ? 'active border-info' : '' }}" id="btnToggleLinkDiskusi" title="Sertakan Tautan URL">
                            <i class="fas fa-link text-primary"></i>
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm font-weight-bold px-3 shadow-sm" style="font-size: 0.74rem; height: 30px; border-radius: 0.45rem; display: inline-flex; align-items: center; justify-content: center; white-space: nowrap;">
                            Kirim
                        </button>
                    </div>

                    <div id="selectedFileChipWrap" class="mt-2 d-none">
                        <span class="chat-file-chip">
                            <i class="fas fa-file-alt"></i>
                            <span id="selectedFileName" class="text-truncate" style="max-width: 260px;"></span>
                            <button type="button" id="btnClearDiskusiFile" class="close ml-1" style="font-size: 0.9rem; line-height: 1;" aria-label="Hapus file">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </span>
                    </div>

                    <div id="linkUrlInputWrap" class="mt-2 {{ old('link_url') || $errors->has('link_url') ? '' : 'd-none' }}">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light" style="font-size: 0.72rem;"><i class="fas fa-link text-info"></i></span>
                            </div>
                            <input type="url"
                                   class="form-control form-control-sm @error('link_url') is-invalid @enderror"
                                   style="font-size: 0.74rem;"
                                   id="link_url_diskusi"
                                   name="link_url"
                                   value="{{ old('link_url') }}"
                                   placeholder="Tempelkan tautan URL referensi (https://...)">
                        </div>
                    </div>

                    @if($errors->has('pesan') || $errors->has('file_lampiran') || $errors->has('link_url'))
                        <div class="mt-1 text-danger" style="font-size: 0.71rem;">
                            @error('pesan') <div>{{ $message }}</div> @enderror
                            @error('file_lampiran') <div>{{ $message }}</div> @enderror
                            @error('link_url') <div>{{ $message }}</div> @enderror
                        </div>
                    @endif
                </form>
            @elseif($tugas->status === 'Selesai')
                <div class="alert alert-light border text-center text-muted mb-0 py-2" style="font-size: 0.73rem;">
                    <i class="fas fa-lock mr-1"></i> Tugas telah dinyatakan <strong>Selesai (Closed)</strong>. Ruang diskusi bersifat arsip (baca-saja).
                </div>
            @endif
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

                            if ($log->action === 'diskusi') {
                                $badgeClass = 'badge-info';
                                $iconClass = 'fa-comments';
                                $borderLeftColor = '#0ea5e9';
                                $headerTextClass = 'text-info';
                            } elseif ($log->action === 'kolaborator') {
                                $badgeClass = 'badge-primary';
                                $iconClass = 'fa-users';
                                $borderLeftColor = '#4e73df';
                                $headerTextClass = 'text-primary';
                            } elseif ($log->action === 'pengajuan_lintas_bidang') {
                                $badgeClass = 'badge-warning text-dark';
                                $iconClass = 'fa-user-check';
                                $borderLeftColor = '#f59e0b';
                                $headerTextClass = 'text-warning';
                            } elseif ($log->action === 'persetujuan_atasan_disetujui') {
                                $badgeClass = 'badge-success';
                                $iconClass = 'fa-check-double';
                                $borderLeftColor = '#10b981';
                                $headerTextClass = 'text-success';
                            } elseif ($log->action === 'persetujuan_atasan_ditolak') {
                                $badgeClass = 'badge-danger';
                                $iconClass = 'fa-ban';
                                $borderLeftColor = '#ef4444';
                                $headerTextClass = 'text-danger';
                            } elseif ($log->action === 'permohonan_petugas') {
                                $badgeClass = 'badge-warning text-dark';
                                $iconClass = 'fa-hand-paper';
                                $borderLeftColor = '#f59e0b';
                                $headerTextClass = 'text-warning';
                            } elseif ($log->action === 'tanggapi_permohonan') {
                                $badgeClass = 'badge-info';
                                $iconClass = 'fa-calendar-check';
                                $borderLeftColor = '#06b6d4';
                                $headerTextClass = 'text-info';
                            } elseif ($log->action === 'tolak_permohonan') {
                                $badgeClass = 'badge-danger';
                                $iconClass = 'fa-times-circle';
                                $borderLeftColor = '#ef4444';
                                $headerTextClass = 'text-danger';
                            } elseif ($log->status === 'Sedang Dikerjakan') {
                                $badgeClass = 'badge-primary';
                                $iconClass = 'fa-spinner fa-spin';
                                $borderLeftColor = '#4e73df';
                                $headerTextClass = 'text-primary';
                            } elseif ($log->status === 'HOLD') {
                                $badgeClass = 'badge-secondary';
                                $iconClass = 'fa-pause-circle';
                                $borderLeftColor = '#475569';
                                $headerTextClass = 'text-secondary';
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
                                        @elseif($log->status === 'HOLD')
                                            Catatan / Alasan Status HOLD dari Pendelegasi:
                                        @elseif($log->status === 'Selesai')
                                            Catatan Verifikasi / Penutupan (Closed):
                                        @elseif($log->action === 'permohonan_petugas')
                                            Alasan Permohonan Ceklist Petugas:
                                        @elseif($log->action === 'tanggapi_permohonan')
                                            Catatan Persetujuan Pemberi Tugas:
                                        @elseif($log->action === 'tolak_permohonan')
                                            Alasan Penolakan Permohonan:
                                        @elseif($log->action === 'diskusi')
                                            Pesan Chat Progres:
                                        @elseif($log->action === 'delegasi')
                                            Catatan / Arahan Pendelegasi:
                                        @else
                                            Catatan / Laporan Tindak Lanjut:
                                        @endif
                                    </strong>
                                    <span class="text-dark ml-1" style="white-space: pre-line;">{{ trim($log->catatan) }}</span>
                                </div>
                            @endif

                            @if($log->file_url)
                                <div class="mt-1 pt-1 border-top small text-gray-800">
                                    <strong class="text-info">
                                        <i class="fas fa-link mr-1"></i> Link URL Lampiran{{ $log->file_name ? ' ('.$log->file_name.')' : '' }}:
                                    </strong>
                                    <a href="{{ $log->file_url }}" target="_blank" rel="noopener noreferrer" class="font-weight-bold text-primary ml-1" style="word-break: break-all;">
                                        {{ $log->file_url }}
                                    </a>
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
                                <label class="font-weight-bold small text-gray-800">Instruksi / Catatan Tambahan {{ Auth::user()->jabatan ?? 'Koordinator' }} (Opsional)</label>
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
    @if(($canManageTask ?? $isAdmin) && $tugas->status !== 'Selesai')
        <!-- Modal Kelola Multi Petugas (Kolaborator) -->
        <div class="modal fade" id="modalKelolaKolaborator" tabindex="-1" role="dialog" aria-labelledby="modalKolaboratorLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title font-weight-bold" id="modalKolaboratorLabel">
                            <i class="fas fa-users mr-1"></i> Kelola Tim Multi Petugas (Kolaborator)
                        </h5>
                        <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <form action="{{ route('tugas.kolaborator.sync', $tugas->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-light border small mb-3">
                                <strong>Judul Tugas:</strong> {{ $tugas->judul }}<br>
                                <strong>PIC Utama:</strong> {{ $tugas->petugas->name ?? '-' }} ({{ $tugas->petugas->jabatan ?? 'Petugas' }})<br>
                                <span class="text-muted">Seluruh petugas yang dicentang di bawah akan tergabung dalam tugas ini, menerima notifikasi, dan dapat saling berkomunikasi di Ruang Diskusi.</span>
                            </div>

                            @php
                                $selectedKolaboratorIds = $tugas->kolaborator
                                    ? $tugas->kolaborator->pluck('id')->map(fn ($id) => (int) $id)->all()
                                    : [];
                            @endphp

                            <div class="form-group mb-0">
                                <label class="font-weight-bold small text-gray-800">Pilih Petugas Kolaborator Tambahan (Opsional):</label>
                                <div class="border rounded p-2 bg-light" style="max-height: 260px; overflow-y: auto;">
                                    <div class="row">
                                        @forelse($availableKolaborator ?? [] as $kandidat)
                                            <div class="col-md-6 mb-2">
                                                <div class="custom-control custom-checkbox p-2 pl-4 bg-white rounded border">
                                                    <input type="checkbox"
                                                           class="custom-control-input"
                                                           id="kolab_chk_{{ $kandidat->id }}"
                                                           name="kolaborator_ids[]"
                                                           value="{{ $kandidat->id }}"
                                                           {{ in_array((int) $kandidat->id, $selectedKolaboratorIds, true) ? 'checked' : '' }}>
                                                    <label class="custom-control-label small text-gray-900 d-block" for="kolab_chk_{{ $kandidat->id }}">
                                                        <strong>{{ $kandidat->name }}</strong>
                                                        <div class="text-muted" style="font-size: 0.72rem;">
                                                            NIP: {{ $kandidat->nip }} &bull; {{ $kandidat->jabatan }}
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-12 text-center py-3 text-muted small">
                                                Tidak ada kandidat petugas tambahan yang tersedia.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary btn-sm" type="button" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-info btn-sm font-weight-bold">
                                <i class="fas fa-save mr-1"></i> Simpan Tim Multi Petugas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Set Status HOLD / Lanjutkan Tugas -->
        <div class="modal fade" id="modalHoldTugasShow" tabindex="-1" role="dialog" aria-labelledby="modalHoldShowLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header {{ $tugas->status === 'HOLD' ? 'bg-primary' : 'bg-dark' }} text-white">
                        <h5 class="modal-title font-weight-bold" id="modalHoldShowLabel">
                            @if($tugas->status === 'HOLD')
                                <i class="fas fa-play-circle mr-1"></i> Cabut Status HOLD &amp; Lanjutkan Tugas
                            @else
                                <i class="fas fa-pause-circle mr-1"></i> Set Status HOLD (Tunda Sementara Tugas)
                            @endif
                        </h5>
                        <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <form action="{{ route('tugas.hold', $tugas->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="{{ $tugas->status === 'HOLD' ? 'lanjutkan' : 'hold' }}">
                        <div class="modal-body">
                            <div class="alert alert-light border small mb-3">
                                <strong>Judul Tugas:</strong> {{ $tugas->judul }}<br>
                                <strong>Petugas Pelaksana:</strong> {{ $tugas->petugas->name ?? '-' }}<br>
                                <strong>Kebijakan Performa:</strong> Selama berstatus <strong>HOLD</strong>, durasi penundaan tidak terhitung dalam keterlambatan penyelesaian tugas.
                            </div>
                            <div class="form-group mb-0">
                                <label class="font-weight-bold small text-gray-800">
                                    {{ $tugas->status === 'HOLD' ? 'Catatan Melanjutkan Pengerjaan (Opsional)' : 'Alasan / Keterangan Status HOLD (Opsional)' }}
                                </label>
                                <textarea class="form-control" name="catatan_hold" rows="3" placeholder="{{ $tugas->status === 'HOLD' ? 'Tuliskan arahan kelanjutan tugas untuk petugas...' : 'Contoh: Menunggu persetujuan berkas eksternal / penundaan jadwal...' }}"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary btn-sm" type="button" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn {{ $tugas->status === 'HOLD' ? 'btn-primary' : 'btn-dark' }} btn-sm font-weight-bold">
                                @if($tugas->status === 'HOLD')
                                    <i class="fas fa-play-circle mr-1"></i> Cabut HOLD &amp; Lanjutkan
                                @else
                                    <i class="fas fa-pause-circle mr-1"></i> Simpan Status HOLD
                                @endif
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const chkPerpanjangan = document.getElementById('chk_permohonan_perpanjangan');
        const boxDeadline = document.getElementById('box_usulan_tanggal_selesai');
        const inputDeadline = document.getElementById('usulan_tanggal_selesai');

        function syncDeadlineField() {
            if (!chkPerpanjangan || !boxDeadline || !inputDeadline) {
                return;
            }
            if (chkPerpanjangan.checked) {
                boxDeadline.style.display = 'block';
                inputDeadline.setAttribute('required', 'required');
            } else {
                boxDeadline.style.display = 'none';
                inputDeadline.removeAttribute('required');
            }
        }

        if (chkPerpanjangan) {
            chkPerpanjangan.addEventListener('change', syncDeadlineField);
            syncDeadlineField();
        }

        const chatContainer = document.getElementById('chatDiskusiContainer');
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        // Dynamic chat composer: auto-resize textarea + Ctrl+Enter submit
        const pesanDiskusi = document.getElementById('pesan_diskusi');
        const formDiskusi = document.getElementById('formDiskusiProgres');
        if (pesanDiskusi) {
            const resizeTextarea = function () {
                pesanDiskusi.style.height = 'auto';
                pesanDiskusi.style.height = Math.min(pesanDiskusi.scrollHeight, 110) + 'px';
            };
            pesanDiskusi.addEventListener('input', resizeTextarea);
            pesanDiskusi.addEventListener('keydown', function (e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter' && formDiskusi) {
                    e.preventDefault();
                    formDiskusi.submit();
                }
            });
            resizeTextarea();
        }

        // Quick-template chips
        document.querySelectorAll('.chat-quick-chip').forEach(function (chip) {
            chip.addEventListener('click', function () {
                if (!pesanDiskusi) return;
                const tpl = this.getAttribute('data-chat-template') || '';
                const existing = pesanDiskusi.value.trim();
                pesanDiskusi.value = existing ? existing + '\n' + tpl : tpl;
                pesanDiskusi.dispatchEvent(new Event('input'));
                pesanDiskusi.focus();
            });
        });

        // Compact file picker with preview chip & clear button
        const fileDiskusiInput = document.getElementById('file_lampiran_diskusi');
        const btnPickFile = document.getElementById('btnPickDiskusiFile');
        const fileChipWrap = document.getElementById('selectedFileChipWrap');
        const selectedFileName = document.getElementById('selectedFileName');
        const btnClearFile = document.getElementById('btnClearDiskusiFile');

        if (btnPickFile && fileDiskusiInput) {
            btnPickFile.addEventListener('click', function () {
                fileDiskusiInput.click();
            });
        }

        if (fileDiskusiInput) {
            fileDiskusiInput.addEventListener('change', function () {
                if (this.files && this.files.length > 0 && fileChipWrap && selectedFileName) {
                    selectedFileName.textContent = this.files[0].name;
                    fileChipWrap.classList.remove('d-none');
                } else if (fileChipWrap) {
                    fileChipWrap.classList.add('d-none');
                }
            });
        }

        if (btnClearFile && fileDiskusiInput && fileChipWrap) {
            btnClearFile.addEventListener('click', function () {
                fileDiskusiInput.value = '';
                fileChipWrap.classList.add('d-none');
            });
        }

        // Toggle URL attachment field
        const btnToggleLink = document.getElementById('btnToggleLinkDiskusi');
        const linkWrap = document.getElementById('linkUrlInputWrap');
        const linkInput = document.getElementById('link_url_diskusi');
        if (btnToggleLink && linkWrap) {
            btnToggleLink.addEventListener('click', function () {
                linkWrap.classList.toggle('d-none');
                this.classList.toggle('active');
                this.classList.toggle('border-info');
                if (!linkWrap.classList.contains('d-none') && linkInput) {
                    linkInput.focus();
                }
            });
        }

        // Dynamic chat filter (Semua vs Lampiran)
        const filterBtns = document.querySelectorAll('.chat-filter-btn');
        const chatRows = document.querySelectorAll('#chatDiskusiContainer .chat-row');
        const dateDividers = document.querySelectorAll('#chatDiskusiContainer .chat-date-divider');
        const filterEmptyState = document.getElementById('chatFilterEmptyState');

        filterBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const mode = this.getAttribute('data-chat-filter');
                filterBtns.forEach(function (b) {
                    b.classList.remove('btn-primary', 'active');
                    b.classList.add('btn-outline-secondary');
                });
                this.classList.remove('btn-outline-secondary');
                this.classList.add('btn-primary', 'active');

                let visibleCount = 0;
                chatRows.forEach(function (row) {
                    const show = mode === 'all' || row.getAttribute('data-has-attachment') === '1';
                    row.style.display = show ? 'flex' : 'none';
                    if (show) visibleCount++;
                });

                dateDividers.forEach(function (div) {
                    div.style.display = mode === 'all' ? 'flex' : 'none';
                });

                if (filterEmptyState) {
                    filterEmptyState.classList.toggle('d-none', visibleCount > 0 || chatRows.length === 0);
                }
            });
        });
    });
</script>
@endpush

