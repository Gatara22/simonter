@extends('layouts.app')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-reply-all mr-2"></i>
            {{ $title }}
        </h1>
        <a href="{{ route('tugas.show', $tugas->id) }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Batal & Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-exclamation-triangle mr-1"></i> Terdapat kesalahan pada pengisian bukti/laporan:</strong>
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

    <div class="row">
        <!-- Kolom Ringkasan Tugas -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-tasks mr-1"></i> Ringkasan Tugas
                    </h6>
                </div>
                <div class="card-body">
                    <h5 class="font-weight-bold text-gray-900">{{ $tugas->judul }}</h5>
                    <p class="text-muted small mb-2">
                        <i class="fas fa-user-shield mr-1"></i> Didelegasikan oleh: <strong>{{ $tugas->admin->name ?? 'Admin' }}</strong>
                    </p>
                    <p class="text-muted small mb-3">
                        <i class="fas fa-calendar-alt mr-1"></i> Batas Selesai: <strong>{{ $tugas->tanggal_selesai ? $tugas->tanggal_selesai->format('d/m/Y') : '-' }}</strong>
                    </p>

                    <h6 class="font-weight-bold text-gray-800 border-bottom pb-1 small">Petunjuk Tugas:</h6>
                    <div class="p-2 bg-light rounded text-gray-800 small mb-3" style="white-space: pre-line;">
                        {{ $tugas->deskripsi }}
                    </div>

                    @if($tugas->file_tugas)
                        <div class="alert alert-info py-2 px-3 small">
                            <i class="fas fa-paperclip mr-1"></i> Lampiran dari Admin:
                            <a href="{{ asset('storage/' . $tugas->file_tugas) }}" target="_blank" class="font-weight-bold d-block mt-1">
                                <i class="fas fa-download mr-1"></i> {{ $tugas->file_tugas_name ?? 'Unduh Dokumen Tugas' }}
                            </a>
                        </div>
                    @endif

                    @if($tugas->catatan_admin)
                        <div class="alert alert-warning py-2 px-3 small">
                            <strong class="d-block"><i class="fas fa-exclamation-circle mr-1"></i> Catatan Revisi dari Admin:</strong>
                            <div class="mt-1" style="white-space: pre-line;">{{ $tugas->catatan_admin }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Kolom Form Tindak Lanjut -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-paper-plane mr-1"></i> Formulir Tindak Lanjut & Kirim Balik ke Admin
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('tugas.kirim-balik', $tugas->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Laporan Tindak Lanjut -->
                        <div class="form-group">
                            <label for="catatan_petugas" class="font-weight-bold">
                                Laporan / Keterangan Tindak Lanjut <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('catatan_petugas') is-invalid @enderror" id="catatan_petugas" name="catatan_petugas" rows="5" placeholder="Tuliskan uraian hasil tindak lanjut yang telah dilakukan, kendala (jika ada), atau ringkasan pekerjaan yang telah diselesaikan..." required>{{ old('catatan_petugas', $tugas->catatan_petugas) }}</textarea>
                            @error('catatan_petugas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Card Bukti (Evidence) -->
                        <div class="card bg-light border-0 p-3 mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-folder-open text-primary fa-lg mr-2"></i>
                                <h6 class="m-0 font-weight-bold text-gray-900">Lampiran Bukti (Evidence)</h6>
                            </div>
                            <p class="text-muted small mb-3">
                                Sertakan bukti hasil pengerjaan berupa <strong>Upload Berkas File (Maksimal 10 MB)</strong> ATAU <strong>Link Evidence</strong> (tautan Google Drive, dokumen cloud, dll.). <em>Minimal salah satu harus disertakan.</em>
                            </p>

                            <!-- Pilihan 1: Upload File Evidence -->
                            <div class="form-group mb-3">
                                <label for="file_evidence" class="font-weight-bold small text-gray-800">
                                    <i class="fas fa-file-upload mr-1 text-primary"></i> 1. Upload Berkas Bukti / File Evidence (Maks 10 MB)
                                </label>
                                @if($tugas->file_evidence)
                                    <div class="mb-2">
                                        <span class="text-muted small">File evidence sebelumnya:</span>
                                        <a href="{{ asset('storage/' . $tugas->file_evidence) }}" target="_blank" class="badge badge-success p-1">
                                            <i class="fas fa-file-download mr-1"></i> {{ $tugas->file_evidence_name ?? 'Lihat Berkas' }}
                                        </a>
                                    </div>
                                @endif
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('file_evidence') is-invalid @enderror" id="file_evidence" name="file_evidence">
                                    <label class="custom-file-label" for="file_evidence">Pilih berkas file (Maks 10 MB)...</label>
                                </div>
                                <small class="form-text text-muted">Format yang didukung: PDF, DOC, DOCX, XLS, XLSX, ZIP, RAR, JPG, PNG (Maksimal 10 MB).</small>
                                @error('file_evidence')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="text-center font-weight-bold text-muted my-1">
                                <span class="badge badge-secondary px-3 py-1 font-weight-bold">ATAU / DAN</span>
                            </div>

                            <!-- Pilihan 2: Link Evidence -->
                            <div class="form-group mb-0">
                                <label for="link_evidence" class="font-weight-bold small text-gray-800">
                                    <i class="fas fa-link mr-1 text-info"></i> 2. Tautan / Link Evidence (Google Drive, Cloud Storage, dll.)
                                </label>
                                <input type="url" class="form-control @error('link_evidence') is-invalid @enderror" id="link_evidence" name="link_evidence" value="{{ old('link_evidence', $tugas->link_evidence) }}" placeholder="Contoh: https://drive.google.com/drive/folders/...">
                                <small class="form-text text-muted">Pastikan izin akses tautan Google Drive / Cloud disetel agar dapat dibuka oleh Admin.</small>
                                @error('link_evidence')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="mt-4 mb-4">

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">
                                <i class="fas fa-info-circle mr-1"></i> Setelah dikirim, status tugas akan berubah menjadi <strong>Menunggu Konfirmasi</strong>.
                            </span>
                            <div>
                                <a href="{{ route('tugas.show', $tugas->id) }}" class="btn btn-secondary mr-2">
                                    <i class="fas fa-times mr-1"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-paper-plane mr-1"></i> Kirim Balik ke Admin
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

