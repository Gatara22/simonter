@extends('layouts.app')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tasks mr-2"></i>
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
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-user-tag mr-1"></i>
                @if($isAdmin ?? false)
                    Formulir Delegasi Tugas dari Admin ke Kepala Sub Bagian / Petugas
                @else
                    Formulir Delegasi Tugas dari Kepala Sub Bagian ke Petugas
                @endif
            </h6>
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
                
                <div class="form-group">
                    <label for="judul" class="font-weight-bold">Judul Tugas <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('judul') is-invalid @enderror" id="judul" name="judul" value="{{ old('judul', $sumberTugas->judul ?? '') }}" placeholder="Contoh: Pemeliharaan Jaringan & Server Pengadilan" required>
                    @error('judul')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="petugas_id" class="font-weight-bold">
                                {{ ($isAdmin ?? false) ? 'Pilih Penerima Tugas (Kepala Sub Bagian / Petugas)' : 'Pilih Petugas Pelaksana' }}
                                <span class="text-danger">*</span>
                            </label>
                            <select class="form-control @error('petugas_id') is-invalid @enderror" id="petugas_id" name="petugas_id" required>
                                <option value="">-- Pilih Penerima Tugas --</option>
                                @foreach($petugasList as $petugas)
                                    <option value="{{ $petugas->id }}" {{ old('petugas_id') == $petugas->id ? 'selected' : '' }}>
                                        [{{ $petugas->jabatan }}] {{ $petugas->name }} (NIP: {{ $petugas->nip }}) - [Status: {{ $petugas->status }}]
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Penerima tugas yang dipilih statusnya akan otomatis diperbarui menjadi <strong>Sedang Bertugas</strong>.</small>
                            @error('petugas_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tanggal_mulai" class="font-weight-bold">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', isset($sumberTugas) && $sumberTugas->tanggal_mulai ? $sumberTugas->tanggal_mulai->format('Y-m-d') : date('Y-m-d')) }}" required>
                            @error('tanggal_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tanggal_selesai" class="font-weight-bold">Batas Selesai (Deadline) <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror" id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai', isset($sumberTugas) && $sumberTugas->tanggal_selesai ? $sumberTugas->tanggal_selesai->format('Y-m-d') : '') }}" required>
                            @error('tanggal_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
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
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane mr-1"></i> Delegasikan Tugas
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

