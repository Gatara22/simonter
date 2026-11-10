@extends('layouts.app')

@section('content')
<style>
    .recipient-selector-box {
        border: 1px solid #d1d5db;
        border-radius: 0.75rem;
        background: #f8fafc;
        overflow: hidden;
    }
    .recipient-toolbar {
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.75rem 1rem;
    }
    .recipient-grid-scroll {
        max-height: 290px;
        overflow-y: auto;
        padding: 0.85rem;
    }
    .recipient-card-item {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 0.65rem;
        padding: 0.65rem 0.85rem;
        cursor: pointer;
        transition: all 0.18s ease;
        user-select: none;
        height: 100%;
        display: flex;
        align-items: center;
    }
    .recipient-card-item:hover {
        border-color: #93c5fd;
        background: #f0f7ff;
    }
    .recipient-card-item.is-selected {
        border-color: #2563eb;
        background: #eff6ff;
        box-shadow: 0 3px 10px rgba(37, 99, 235, 0.12);
    }
    .recipient-avatar-mini {
        width: 36px;
        height: 36px;
        min-width: 36px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e2e8f0;
    }
    .recipient-card-item.is-selected .recipient-avatar-mini {
        border-color: #2563eb;
    }
</style>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tasks mr-2 text-primary"></i>
            {{ $title }}
        </h1>
        <a href="{{ route('tugas.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Kembali ke Daftar Tugas
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-exclamation-triangle mr-1"></i> Terdapat kesalahan pada input:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-user-tag mr-1"></i>
                @if($isAdmin ?? false)
                    Formulir Delegasi Tugas dari Admin ke Kepala Sub Bagian / Panitera Muda / Petugas (Multi-Penerima)
                @else
                    Formulir Delegasi Tugas dari {{ Auth::user()->jabatan ?? 'Koordinator' }} ke Petugas (Multi-Penerima)
                @endif
            </h6>
            <span class="badge badge-info px-2 py-1 mt-1 mt-sm-0">
                <i class="fas fa-users mr-1"></i> Dapat Memilih 1 atau Lebih Pelaksana
            </span>
        </div>
        <div class="card-body">
            <form action="{{ route('tugas.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if(!empty($sumberTugas))
                    <input type="hidden" name="sumber_tugas_id" value="{{ $sumberTugas->id }}">
                    <div class="alert alert-info small">
                        <i class="fas fa-share-square mr-1"></i> Meneruskan / membuat delegasi tugas berdasarkan tugas: <strong>{{ $sumberTugas->judul }}</strong>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="judul" class="font-weight-bold">Judul Tugas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('judul') is-invalid @enderror" id="judul" name="judul" value="{{ old('judul', $sumberTugas->judul ?? '') }}" placeholder="Contoh: Pemeliharaan Jaringan & Server Pengadilan" required>
                            @error('judul')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="klasifikasi" class="font-weight-bold">Klasifikasi Tugas <span class="text-danger">*</span></label>
                            @if(($isAdmin ?? false))
                                <select class="form-control @error('klasifikasi') is-invalid @enderror" id="klasifikasi" name="klasifikasi">
                                    <option value="Kesekretariatan" {{ old('klasifikasi', $sumberTugas->klasifikasi ?? 'Kesekretariatan') === 'Kesekretariatan' ? 'selected' : '' }}>
                                        Kesekretariatan
                                    </option>
                                    <option value="Kepaniteraan" {{ old('klasifikasi', $sumberTugas->klasifikasi ?? '') === 'Kepaniteraan' ? 'selected' : '' }}>
                                        Kepaniteraan
                                    </option>
                                </select>
                                @error('klasifikasi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @elseif((Auth::user()->jabatan ?? '') === 'Panitera Muda')
                                <input type="hidden" name="klasifikasi" value="Kepaniteraan">
                                <div class="form-control bg-light font-weight-bold text-success d-flex align-items-center">
                                    <i class="fas fa-balance-scale mr-2"></i> Kepaniteraan (Otomatis)
                                </div>
                            @else
                                <input type="hidden" name="klasifikasi" value="Kesekretariatan">
                                <div class="form-control bg-light font-weight-bold text-primary d-flex align-items-center">
                                    <i class="fas fa-building mr-2"></i> Kesekretariatan (Otomatis)
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="tanggal_mulai" class="font-weight-bold">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', isset($sumberTugas) && $sumberTugas->tanggal_mulai ? $sumberTugas->tanggal_mulai->format('Y-m-d') : date('Y-m-d')) }}" required>
                            @error('tanggal_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="tanggal_selesai" class="font-weight-bold">Batas Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror" id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai', isset($sumberTugas) && $sumberTugas->tanggal_selesai ? $sumberTugas->tanggal_selesai->format('Y-m-d') : '') }}" required>
                            @error('tanggal_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                @php
                    $oldPetugasIds = old('petugas_id', []);
                    if (!is_array($oldPetugasIds)) {
                        $oldPetugasIds = $oldPetugasIds ? [$oldPetugasIds] : [];
                    }
                    $oldPetugasIds = array_map('intval', $oldPetugasIds);
                @endphp

                <!-- Multi-Select Penerima Tugas -->
                <div class="form-group">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                        <label class="font-weight-bold mb-0">
                            <i class="fas fa-user-check text-primary mr-1"></i>
                            {{ ($isAdmin ?? false) ? 'Pilih Penerima Tugas (Kepala Sub Bagian / Panitera Muda / Petugas — Bisa Lebih dari 1 Orang)' : 'Pilih Petugas Pelaksana Pekerjaan (Bisa Lebih dari 1 Orang)' }}
                            <span class="text-danger">*</span>
                        </label>
                        <span id="selectedRecipientBadge" class="badge badge-primary px-3 py-1 font-weight-bold">
                            {{ count($oldPetugasIds) }} Penerima Dipilih
                        </span>
                    </div>

                    <div class="recipient-selector-box @error('petugas_id') border-danger @enderror @error('petugas_id.*') border-danger @enderror">
                        <!-- Toolbar Pencarian & Pilih Cepat -->
                        <div class="recipient-toolbar d-flex flex-wrap justify-content-between align-items-center" style="gap: 8px;">
                            <div class="position-relative flex-grow-1" style="max-width: 380px;">
                                <input type="text" id="searchRecipientInput" class="form-control form-control-sm"
                                    placeholder="Cari nama pegawai, NIP, jabatan, atau status...">
                            </div>

                            <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                                <button type="button" class="btn btn-xs btn-outline-primary font-weight-bold py-1 px-2 small" id="btnSelectAllRecipients">
                                    <i class="fas fa-check-double mr-1"></i> Pilih Semua
                                </button>
                                @if($isAdmin ?? false)
                                    <button type="button" class="btn btn-xs btn-outline-info font-weight-bold py-1 px-2 small" id="btnSelectOnlyKasubbag">
                                        <i class="fas fa-user-cog mr-1"></i> Kasubbag &amp; Panmud
                                    </button>
                                    <button type="button" class="btn btn-xs btn-outline-info font-weight-bold py-1 px-2 small" id="btnSelectOnlyPetugas">
                                        <i class="fas fa-user-tie mr-1"></i> Hanya Petugas
                                    </button>
                                @endif
                                <button type="button" class="btn btn-xs btn-outline-success font-weight-bold py-1 px-2 small" id="btnSelectAvailableOnly" title="Pilih yang berstatus Belum Ditugaskan">
                                    <i class="fas fa-user-clock mr-1"></i> Belum Ditugaskan
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-secondary font-weight-bold py-1 px-2 small" id="btnClearRecipients">
                                    <i class="fas fa-eraser mr-1"></i> Reset Pilihan
                                </button>
                            </div>
                        </div>

                        <!-- Daftar Kartu Checkbox Penerima -->
                        <div class="recipient-grid-scroll">
                            <div class="row" id="recipientCardsContainer">
                                @forelse($petugasList as $petugas)
                                    @php
                                        $isChecked = in_array((int) $petugas->id, $oldPetugasIds, true);
                                        $isAvailable = $petugas->status === 'Belum Ditugaskan';
                                    @endphp
                                    <div class="col-xl-4 col-md-6 mb-2 recipient-col-item"
                                         data-search="{{ strtolower($petugas->name . ' ' . $petugas->nip . ' ' . $petugas->jabatan . ' ' . $petugas->status) }}"
                                         data-jabatan="{{ $petugas->jabatan }}"
                                         data-status="{{ $petugas->status }}">
                                        <label class="recipient-card-item mb-0 {{ $isChecked ? 'is-selected' : '' }}" for="petugas_chk_{{ $petugas->id }}">
                                            <div class="custom-control custom-checkbox mr-2">
                                                <input type="checkbox"
                                                       class="custom-control-input recipient-checkbox"
                                                       id="petugas_chk_{{ $petugas->id }}"
                                                       name="petugas_id[]"
                                                       value="{{ $petugas->id }}"
                                                       {{ $isChecked ? 'checked' : '' }}>
                                                <span class="custom-control-label"></span>
                                            </div>
                                            <img src="{{ $petugas->foto_url }}" alt="{{ $petugas->name }}" class="recipient-avatar-mini mr-2">
                                            <div class="flex-grow-1" style="min-width: 0;">
                                                <div class="font-weight-bold text-gray-900 small text-truncate">
                                                    {{ $petugas->name }}
                                                </div>
                                                <div class="text-muted" style="font-size: 0.7rem;">
                                                    NIP: {{ $petugas->nip }}
                                                </div>
                                                <div class="mt-1 d-flex flex-wrap align-items-center" style="gap: 4px;">
                                                    <span class="badge {{ $petugas->jabatan === 'Kepala Sub Bagian' ? 'badge-primary' : ($petugas->jabatan === 'Panitera Muda' ? 'badge-success' : 'badge-info') }}" style="font-size: 0.65rem;">
                                                        {{ $petugas->jabatan }}
                                                    </span>
                                                    <span class="badge {{ $isAvailable ? 'badge-secondary' : 'badge-success' }}" style="font-size: 0.65rem;">
                                                        {{ $petugas->status }}
                                                    </span>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                @empty
                                    <div class="col-12 text-center py-4 text-muted small">
                                        Belum ada data pegawai pelaksana yang terdaftar.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <small class="form-text text-muted mt-1">
                        <i class="fas fa-info-circle text-primary mr-1"></i>
                        Centang satu atau beberapa nama di atas. Setiap penerima yang dipilih akan menerima delegasi tugas, push notifikasi, serta statusnya otomatis diperbarui menjadi <strong>Sedang Bertugas</strong>.
                    </small>
                    @error('petugas_id')
                        <div class="text-danger small font-weight-bold mt-1">{{ $message }}</div>
                    @enderror
                    @error('petugas_id.*')
                        <div class="text-danger small font-weight-bold mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="deskripsi" class="font-weight-bold">Deskripsi Tugas & Petunjuk Pengerjaan <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="5" placeholder="Tuliskan rincian instruksi tugas, target yang diharapkan, dan ketentuan penyelesaian..." required>{{ old('deskripsi', $sumberTugas->deskripsi ?? '') }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="file_tugas" class="font-weight-bold">Lampiran Berkas Instruksi Tugas (Opsional)</label>
                    @if(!empty($sumberTugas) && $sumberTugas->file_tugas)
                        <div class="mb-2 small text-info">
                            <i class="fas fa-paperclip mr-1"></i> Lampiran dari tugas asal ({{ $sumberTugas->file_tugas_name ?? 'Berkas Tugas' }}) akan otomatis disertakan jika Anda tidak mengunggah file baru.
                        </div>
                    @endif
                    <div class="custom-file">
                        <input type="file" class="custom-file-input @error('file_tugas') is-invalid @enderror" id="file_tugas" name="file_tugas">
                        <label class="custom-file-label" for="file_tugas">Pilih file (Maksimal 10 MB)...</label>
                    </div>
                    <small class="form-text text-muted">Format berkas yang didukung: PDF, DOC, DOCX, XLS, XLSX, ZIP, RAR, JPG, PNG (Maks 10 MB).</small>
                    @error('file_tugas')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <hr class="mt-4 mb-4">

                <div class="d-flex justify-content-end">
                    <a href="{{ route('tugas.index') }}" class="btn btn-secondary mr-2">
                        <i class="fas fa-times mr-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary font-weight-bold px-4 shadow-sm">
                        <i class="fas fa-paper-plane mr-1"></i> Delegasikan Tugas
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkboxes = document.querySelectorAll('.recipient-checkbox');
        const badgeEl = document.getElementById('selectedRecipientBadge');
        const searchInput = document.getElementById('searchRecipientInput');
        const colItems = document.querySelectorAll('.recipient-col-item');

        function syncSelectionUI() {
            let count = 0;
            checkboxes.forEach(function (chk) {
                const card = chk.closest('.recipient-card-item');
                if (chk.checked) {
                    count++;
                    if (card) card.classList.add('is-selected');
                } else {
                    if (card) card.classList.remove('is-selected');
                }
            });
            if (badgeEl) {
                badgeEl.textContent = count + ' Penerima Dipilih';
                badgeEl.className = count > 0
                    ? 'badge badge-primary px-3 py-1 font-weight-bold'
                    : 'badge badge-secondary px-3 py-1 font-weight-bold';
            }
        }

        checkboxes.forEach(function (chk) {
            chk.addEventListener('change', syncSelectionUI);
        });

        // Search filter
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const q = this.value.trim().toLowerCase();
                colItems.forEach(function (col) {
                    const text = col.getAttribute('data-search') || '';
                    col.style.display = (!q || text.includes(q)) ? '' : 'none';
                });
            });
        }

        // Quick action buttons
        const btnSelectAll = document.getElementById('btnSelectAllRecipients');
        if (btnSelectAll) {
            btnSelectAll.addEventListener('click', function () {
                colItems.forEach(function (col) {
                    if (col.style.display !== 'none') {
                        const chk = col.querySelector('.recipient-checkbox');
                        if (chk) chk.checked = true;
                    }
                });
                syncSelectionUI();
            });
        }

        const btnClear = document.getElementById('btnClearRecipients');
        if (btnClear) {
            btnClear.addEventListener('click', function () {
                checkboxes.forEach(function (chk) { chk.checked = false; });
                syncSelectionUI();
            });
        }

        const btnOnlyKasubbag = document.getElementById('btnSelectOnlyKasubbag');
        if (btnOnlyKasubbag) {
            btnOnlyKasubbag.addEventListener('click', function () {
                colItems.forEach(function (col) {
                    const chk = col.querySelector('.recipient-checkbox');
                    if (chk) {
                        const jab = col.getAttribute('data-jabatan');
                        chk.checked = (jab === 'Kepala Sub Bagian' || jab === 'Panitera Muda');
                    }
                });
                syncSelectionUI();
            });
        }

        const btnOnlyPetugas = document.getElementById('btnSelectOnlyPetugas');
        if (btnOnlyPetugas) {
            btnOnlyPetugas.addEventListener('click', function () {
                colItems.forEach(function (col) {
                    const chk = col.querySelector('.recipient-checkbox');
                    if (chk) {
                        chk.checked = col.getAttribute('data-jabatan') === 'Petugas';
                    }
                });
                syncSelectionUI();
            });
        }

        const btnAvailableOnly = document.getElementById('btnSelectAvailableOnly');
        if (btnAvailableOnly) {
            btnAvailableOnly.addEventListener('click', function () {
                colItems.forEach(function (col) {
                    const chk = col.querySelector('.recipient-checkbox');
                    if (chk) {
                        chk.checked = col.getAttribute('data-status') === 'Belum Ditugaskan';
                    }
                });
                syncSelectionUI();
            });
        }

        // File input label update
        const fileInput = document.getElementById('file_tugas');
        if (fileInput) {
            fileInput.addEventListener('change', function () {
                const label = this.nextElementSibling;
                if (label && this.files && this.files.length > 0) {
                    label.textContent = this.files[0].name;
                }
            });
        }

        syncSelectionUI();
    });
</script>
@endpush
