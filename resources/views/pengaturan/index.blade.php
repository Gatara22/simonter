@extends('layouts.app')

@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-cogs mr-2 text-primary"></i>
            {{ $title }}
        </h1>
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Kembali ke Dashboard
        </a>
    </div>

    <!-- Alert Success -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Alert Errors -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <strong><i class="fas fa-exclamation-triangle mr-2"></i> Terjadi kesalahan pada isian formulir:</strong>
            <ul class="mb-0 mt-2 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <form action="{{ route('pengaturan.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Hidden File Input for Foto Profil -->
        <input type="file" id="foto" name="foto" class="d-none" accept="image/jpeg,image/png,image/jpg,image/webp">

        <div class="row">
            <!-- Kolom Kiri: Profil Ringkas & Foto -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-gradient-primary text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-user-circle mr-1"></i> Foto & Profil Pengguna
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="position-relative d-inline-block mb-3">
                            <img id="avatarPreview"
                                src="{{ $user->foto_url }}"
                                alt="{{ $user->name }}"
                                class="rounded-circle shadow border border-3 border-light"
                                style="width: 140px; height: 140px; object-fit: cover;">
                            
                            <label for="foto" class="btn btn-sm btn-primary rounded-circle shadow position-absolute"
                                style="bottom: 0; right: 5px; cursor: pointer; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;"
                                title="Ubah Foto Profil">
                                <i class="fas fa-camera"></i>
                            </label>
                        </div>

                        <h5 class="font-weight-bold text-gray-900 mb-1">{{ $user->name }}</h5>
                        <p class="text-muted small mb-2">NIP: {{ $user->nip }}</p>

                        <div class="mb-3">
                            @if($user->jabatan === 'Admin')
                                <span class="badge badge-primary px-3 py-1 font-weight-bold">
                                    <i class="fas fa-user-shield mr-1"></i> Administrator
                                </span>
                            @elseif($user->jabatan === 'Kepala Sub Bagian')
                                <span class="badge badge-primary px-3 py-1 font-weight-bold">
                                    <i class="fas fa-user-cog mr-1"></i> Kepala Sub Bagian
                                </span>
                            @else
                                <span class="badge badge-info px-3 py-1 font-weight-bold">
                                    <i class="fas fa-user-tie mr-1"></i> Petugas
                                </span>
                            @endif

                            @if($user->status === 'Sedang Bertugas')
                                <span class="badge badge-success px-2 py-1 ml-1">
                                    <i class="fas fa-clipboard-check mr-1"></i> Sedang Bertugas
                                </span>
                            @else
                                <span class="badge badge-secondary px-2 py-1 ml-1">
                                    <i class="fas fa-clock mr-1"></i> Belum Ditugaskan
                                </span>
                            @endif
                        </div>

                        <hr>

                        <div class="text-left small text-muted mb-3">
                            <p class="mb-1"><i class="fas fa-info-circle mr-1 text-primary"></i> <strong>Ketentuan Foto:</strong></p>
                            <ul class="pl-3 mb-0">
                                <li>Format yang didukung: JPG, JPEG, PNG, WEBP.</li>
                                <li>Ukuran file maksimal: <strong>2 MB</strong>.</li>
                                <li>Foto akan langsung diperbarui di header dan menu navigasi.</li>
                            </ul>
                        </div>

                        <div class="d-flex flex-column gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm mb-2" onclick="document.getElementById('foto').click();">
                                <i class="fas fa-upload mr-1"></i> Pilih Berkas Foto Baru
                            </button>
                            <span id="selectedFileName" class="text-xs text-muted font-italic mb-2"></span>

                            @if($user->foto)
                                <div class="custom-control custom-checkbox text-left small mt-1">
                                    <input type="checkbox" class="custom-control-input" id="hapus_foto" name="hapus_foto" value="1">
                                    <label class="custom-control-label text-danger font-weight-bold" for="hapus_foto">
                                        <i class="fas fa-trash-alt mr-1"></i> Hapus Foto Profil (Gunakan Avatar Bawaan)
                                    </label>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Form Data Profil & Password -->
            <div class="col-lg-8">
                <!-- Card 1: Informasi Kontak & Data Akun -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-id-card mr-1"></i> Informasi Akun & Kontak
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="name" class="font-weight-bold small text-gray-700">Nama Lengkap</label>
                                <input type="text" class="form-control bg-light" id="name" value="{{ $user->name }}" readonly>
                                <small class="text-muted">Nama terdaftar di sistem (hubungi Super Admin jika perlu penyesuaian).</small>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="nip" class="font-weight-bold small text-gray-700">NIP (Nomor Induk Pegawai)</label>
                                <input type="text" class="form-control bg-light" id="nip" value="{{ $user->nip }}" readonly>
                                <small class="text-muted">NIP digunakan sebagai username untuk masuk ke sistem.</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="email" class="font-weight-bold small text-gray-700">Alamat Email</label>
                                <input type="email" class="form-control bg-light" id="email" value="{{ $user->email ?? '-' }}" readonly>
                                <small class="text-muted">Alamat email resmi terdaftar.</small>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="kontak" class="font-weight-bold small text-gray-800">
                                    Nomor Telepon / WhatsApp <span class="text-primary">*</span>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white"><i class="fas fa-phone-alt text-gray-500"></i></span>
                                    </div>
                                    <input type="text" class="form-control @error('kontak') is-invalid @enderror"
                                        id="kontak" name="kontak" value="{{ old('kontak', $user->kontak) }}"
                                        placeholder="Contoh: 081234567890">
                                    @error('kontak')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="form-text text-muted">Dapat dihubungi untuk koordinasi tugas dan disposisi.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Ubah Password -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-key mr-1"></i> Keamanan & Ubah Password
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-light border small text-muted mb-3">
                            <i class="fas fa-shield-alt text-info mr-1"></i>
                            <strong>Petunjuk:</strong> Kosongkan kolom di bawah ini jika Anda <strong>tidak ingin mengubah password</strong>. Jika ingin memperbarui password, masukkan password saat ini dan password baru minimal 8 karakter.
                        </div>

                        <!-- Password Saat Ini -->
                        <div class="form-group">
                            <label for="current_password" class="font-weight-bold small text-gray-800">
                                Password Saat Ini
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                    id="current_password" name="current_password" placeholder="Masukkan password yang saat ini digunakan">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Password Baru -->
                            <div class="col-md-6 form-group">
                                <label for="password" class="font-weight-bold small text-gray-800">
                                    Password Baru (Minimal 8 Karakter)
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        id="password" name="password" placeholder="Minimal 8 karakter">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Konfirmasi Password Baru -->
                            <div class="col-md-6 form-group">
                                <label for="password_confirmation" class="font-weight-bold small text-gray-800">
                                    Ulangi Password Baru
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control"
                                        id="password_confirmation" name="password_confirmation" placeholder="Ulangi password baru">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Submit Form -->
                <div class="card shadow mb-4">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <span class="small text-muted">
                            <i class="fas fa-check-double mr-1 text-success"></i> Perubahan foto profil dan nomor telepon akan segera aktif.
                        </span>
                        <div>
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary mr-2">
                                <i class="fas fa-times mr-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm font-weight-bold">
                                <i class="fas fa-save mr-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Live image preview
        const fotoInput = document.getElementById('foto');
        const avatarPreview = document.getElementById('avatarPreview');
        const selectedFileName = document.getElementById('selectedFileName');

        if (fotoInput) {
            fotoInput.addEventListener('change', function(e) {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Ukuran file melebihi 2 MB. Harap pilih gambar lain.');
                        this.value = '';
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        avatarPreview.src = evt.target.result;
                        if (selectedFileName) {
                            selectedFileName.innerText = 'File dipilih: ' + file.name;
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // Toggle show/hide password
        document.querySelectorAll('.toggle-password').forEach(function(button) {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const targetInput = document.getElementById(targetId);
                const icon = this.querySelector('i');

                if (targetInput.type === 'password') {
                    targetInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    targetInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    });
</script>
@endpush

