@extends('layouts.app')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit mr-2"></i>
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
                <i class="fas fa-edit mr-1"></i> Edit Data Delegasi Tugas
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('tugas.update', $tugas->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="judul" class="font-weight-bold">Judul Tugas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('judul') is-invalid @enderror" id="judul" name="judul" value="{{ old('judul', $tugas->judul) }}" required>
                            @error('judul')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="klasifikasi" class="font-weight-bold">Klasifikasi Tugas <span class="text-danger">*</span></label>
                            @if(($isAdmin ?? false))
                                <select class="form-control @error('klasifikasi') is-invalid @enderror" id="klasifikasi" name="klasifikasi">
                                    <option value="Kesekretariatan" {{ old('klasifikasi', $tugas->klasifikasi ?? 'Kesekretariatan') === 'Kesekretariatan' ? 'selected' : '' }}>
                                        Kesekretariatan
                                    </option>
                                    <option value="Kepaniteraan" {{ old('klasifikasi', $tugas->klasifikasi ?? '') === 'Kepaniteraan' ? 'selected' : '' }}>
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
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="petugas_id" class="font-weight-bold">Petugas Pelaksana <span class="text-danger">*</span></label>
                            <select class="form-control @error('petugas_id') is-invalid @enderror" id="petugas_id" name="petugas_id" required>
                                <option value="">-- Pilih Petugas yang Ditugaskan --</option>
                                @foreach($petugasList as $petugas)
                                    <option value="{{ $petugas->id }}" {{ old('petugas_id', $tugas->petugas_id) == $petugas->id ? 'selected' : '' }}>
                                        [{{ $petugas->jabatan }}] {{ $petugas->name }} (NIP: {{ $petugas->nip }})
                                    </option>
                                @endforeach
                            </select>
                            @error('petugas_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tanggal_mulai" class="font-weight-bold">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', $tugas->tanggal_mulai ? $tugas->tanggal_mulai->format('Y-m-d') : '') }}" required>
                            @error('tanggal_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tanggal_selesai" class="font-weight-bold">Batas Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror" id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai', $tugas->tanggal_selesai ? $tugas->tanggal_selesai->format('Y-m-d') : '') }}" required>
                            @error('tanggal_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="deskripsi" class="font-weight-bold">Deskripsi Tugas & Petunjuk Pengerjaan <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="5" required>{{ old('deskripsi', $tugas->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="file_tugas" class="font-weight-bold">Ganti Berkas Instruksi Tugas (Opsional)</label>
                    @if($tugas->file_tugas)
                        <div class="mb-2">
                            <span class="text-muted small">File saat ini:</span>
                            <a href="{{ asset('storage/' . $tugas->file_tugas) }}" target="_blank" class="badge badge-info p-1">
                                <i class="fas fa-file-download mr-1"></i> {{ $tugas->file_tugas_name ?? 'Unduh File' }}
                            </a>
                        </div>
                    @endif
                    <div class="custom-file">
                        <input type="file" class="custom-file-input @error('file_tugas') is-invalid @enderror" id="file_tugas" name="file_tugas">
                        <label class="custom-file-label" for="file_tugas">Pilih file baru jika ingin mengganti (Maks 10 MB)...</label>
                    </div>
                    <small class="form-text text-muted">Format: PDF, DOC, DOCX, XLS, XLSX, ZIP, RAR, JPG, PNG (Maks 10 MB). Kosongkan jika tidak ingin mengubah berkas.</small>
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
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

