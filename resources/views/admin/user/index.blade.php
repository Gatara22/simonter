@extends('layouts.app')

@section('content')
    <h1 class="h3 mb-4 text-gray-800">
        <i class="fas fa-user mr-2"></i>
        {{ $title }}
    </h1> 

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

    @if(!empty($userStats))
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body py-2">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Pengguna</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $userStats['total'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body py-2">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Admin &amp; Pimpinan Unit</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ ($userStats['admin'] ?? 0) + ($userStats['kasubbag'] ?? 0) + ($userStats['panmud'] ?? 0) }}
                                </div>
                                <small class="text-muted">Admin, Kasubbag, Panmud</small>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-dark shadow h-100 py-2">
                    <div class="card-body py-2">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-gray-800 text-uppercase mb-1">Petugas Pelaksana</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $userStats['petugas'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body py-2">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Sedang Bertugas</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $userStats['bertugas'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="card shadow mb-4">
         <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <div class="mb-1">
                <a href="{{ route('user.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus mr-1"></i>
                    Tambah Data
                </a>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#modalExportExcelUser">
                    <i class="fas fa-file-excel mr-1"></i> Excel
                </button>
                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#modalExportPdfUser">
                    <i class="fas fa-file-pdf mr-1"></i> PDF
                </button>
            </div>
         </div>
         <div class="card-body">
            <!-- Quick Klasifikasi Filter Pills -->
            <div class="d-flex flex-wrap align-items-center justify-content-between bg-light rounded border p-2 mb-3" style="gap: 6px;">
                <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                    <span class="small font-weight-bold text-gray-700 mr-1">
                        <i class="fas fa-filter text-primary mr-1"></i> Klasifikasi:
                    </span>
                    <a href="{{ route('user.index') }}"
                       class="btn btn-xs py-1 px-2 small font-weight-bold {{ empty($activeKlasifikasi) ? 'btn-primary' : 'btn-outline-secondary bg-white' }}">
                        Semua ({{ $userStats['total'] ?? 0 }})
                    </a>
                    <a href="{{ route('user.index', ['klasifikasi' => 'Pimpinan']) }}"
                       class="btn btn-xs py-1 px-2 small font-weight-bold {{ ($activeKlasifikasi ?? null) === 'Pimpinan' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark bg-white' }}">
                        <i class="fas fa-user-tie mr-1"></i>Pimpinan ({{ $userStats['pimpinan'] ?? 0 }})
                    </a>
                    <a href="{{ route('user.index', ['klasifikasi' => 'Kesekretariatan']) }}"
                       class="btn btn-xs py-1 px-2 small font-weight-bold {{ ($activeKlasifikasi ?? null) === 'Kesekretariatan' ? 'btn-primary' : 'btn-outline-primary bg-white' }}">
                        <i class="fas fa-building mr-1"></i>Kesekretariatan ({{ $userStats['kesekretariatan'] ?? 0 }})
                    </a>
                    <a href="{{ route('user.index', ['klasifikasi' => 'Kepaniteraan']) }}"
                       class="btn btn-xs py-1 px-2 small font-weight-bold {{ ($activeKlasifikasi ?? null) === 'Kepaniteraan' ? 'btn-success' : 'btn-outline-success bg-white' }}">
                        <i class="fas fa-balance-scale mr-1"></i>Kepaniteraan ({{ $userStats['kepaniteraan'] ?? 0 }})
                    </a>
                    <a href="{{ route('user.index', ['klasifikasi' => 'belum_diatur']) }}"
                       class="btn btn-xs py-1 px-2 small font-weight-bold {{ ($activeKlasifikasi ?? null) === 'belum_diatur' ? 'btn-dark' : 'btn-outline-secondary bg-white' }}">
                        Belum Diatur ({{ $userStats['belum_klasifikasi'] ?? 0 }})
                    </a>
                </div>
                @if(!empty($activeKlasifikasi))
                    <a href="{{ route('user.index') }}" class="small text-danger font-weight-bold">
                        <i class="fas fa-times-circle mr-1"></i> Reset Filter
                    </a>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="bg-primary text-white">
                        <tr class="text-center">
                            <th width="5%">No</th>
                            <th>Nama</th>
                            <th>NIP</th>
                            <th>Kontak</th>
                            <th>Jabatan</th>
                            <th>Klasifikasi</th>
                            <th>Status</th>
                            <th width="15%">
                                <i class="fas fa-cog"></i> Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                <td class="align-middle">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $user->foto_url }}" alt="{{ $user->name }}" class="rounded-circle mr-2 border" style="width: 34px; height: 34px; object-fit: cover;">
                                        <div>
                                            <span class="font-weight-bold text-gray-900">{{ $user->name }}</span>
                                            @if($user->email)
                                                <br><small class="text-muted"><i class="fas fa-envelope fa-xs mr-1"></i>{{ $user->email }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle text-center">{{ $user->nip }}</td>
                                <td class="align-middle text-center">{{ $user->kontak ?? '-' }}</td>
                                <td class="text-center align-middle">
                                    @if($user->jabatan === 'Admin')
                                        <span class="badge badge-dark badge-pill px-2 py-1">Admin</span>
                                    @elseif($user->jabatan === 'Kepala Sub Bagian')
                                        <span class="badge badge-primary badge-pill px-2 py-1">Kepala Sub Bagian</span>
                                    @elseif($user->jabatan === 'Panitera Muda')
                                        <span class="badge badge-success badge-pill px-2 py-1">Panitera Muda</span>
                                    @else
                                        <span class="badge badge-info badge-pill px-2 py-1">Petugas</span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    @if($user->klasifikasi === 'Pimpinan')
                                        <span class="badge badge-warning text-dark badge-pill px-2 py-1"><i class="fas fa-user-tie mr-1"></i>Pimpinan</span>
                                    @elseif($user->klasifikasi === 'Kesekretariatan')
                                        <span class="badge badge-primary badge-pill px-2 py-1"><i class="fas fa-building mr-1"></i>Kesekretariatan</span>
                                    @elseif($user->klasifikasi === 'Kepaniteraan')
                                        <span class="badge badge-success badge-pill px-2 py-1"><i class="fas fa-balance-scale mr-1"></i>Kepaniteraan</span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    @if($user->status === 'Sedang Bertugas')
                                        <span class="badge badge-success badge-pill px-2 py-1">{{ $user->status }}</span>
                                    @else
                                        <span class="badge badge-danger badge-pill px-2 py-1">{{ $user->status }}</span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    <div class="d-inline-flex">
                                        <a href="{{ route('user.edit', $user->id) }}" class="btn btn-warning btn-sm mr-1" title="Edit Data">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        @if($user->id !== Auth::id())
                                            <form action="{{ route('user.destroy', $user->id) }}" method="POST" data-confirm-name="{{ $user->name }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ' + this.dataset.confirmName + '?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus Data">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-secondary btn-sm" title="Akun Anda sendiri tidak dapat dihapus" disabled>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-3 text-muted">Belum ada data user.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
         </div>
    </div>

    <!-- Modal Export Excel User -->
    <div class="modal fade" id="modalExportExcelUser" tabindex="-1" role="dialog" aria-labelledby="modalExcelLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalExcelLabel">
                        <i class="fas fa-file-excel mr-1"></i> Filter & Export Excel Data User
                    </h5>
                    <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ route('user.export.excel') }}" method="GET">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-bold small text-gray-800">Periode Terdaftar (Opsional):</label>
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Dari Tanggal:</small>
                                    <input type="date" class="form-control" name="tanggal_awal">
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Sampai Tanggal:</small>
                                    <input type="date" class="form-control" name="tanggal_akhir">
                                </div>
                            </div>
                            <small class="form-text text-muted mt-1">Kosongkan periode tanggal jika ingin mencetak seluruh data.</small>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label class="font-weight-bold small text-gray-800">Filter Jabatan:</label>
                                    <select class="form-control form-control-sm" name="jabatan">
                                        <option value="">Semua Jabatan</option>
                                        <option value="Admin">Admin</option>
                                        <option value="Kepala Sub Bagian">Kepala Sub Bagian</option>
                                        <option value="Panitera Muda">Panitera Muda</option>
                                        <option value="Petugas">Petugas</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label class="font-weight-bold small text-gray-800">Filter Klasifikasi:</label>
                                    <select class="form-control form-control-sm" name="klasifikasi">
                                        <option value="">Semua Klasifikasi</option>
                                        <option value="Pimpinan">Pimpinan</option>
                                        <option value="Kesekretariatan">Kesekretariatan</option>
                                        <option value="Kepaniteraan">Kepaniteraan</option>
                                        <option value="belum_diatur">Belum Diatur</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label class="font-weight-bold small text-gray-800">Filter Status:</label>
                                    <select class="form-control form-control-sm" name="status">
                                        <option value="">Semua Status</option>
                                        <option value="Belum Ditugaskan">Belum Ditugaskan</option>
                                        <option value="Sedang Bertugas">Sedang Bertugas</option>
                                    </select>
                                </div>
                            </div>
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

    <!-- Modal Export PDF User -->
    <div class="modal fade" id="modalExportPdfUser" tabindex="-1" role="dialog" aria-labelledby="modalPdfLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalPdfLabel">
                        <i class="fas fa-file-pdf mr-1"></i> Filter & Export PDF Data User
                    </h5>
                    <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ route('user.export.pdf') }}" method="GET">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-bold small text-gray-800">Periode Terdaftar (Opsional):</label>
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Dari Tanggal:</small>
                                    <input type="date" class="form-control" name="tanggal_awal">
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Sampai Tanggal:</small>
                                    <input type="date" class="form-control" name="tanggal_akhir">
                                </div>
                            </div>
                            <small class="form-text text-muted mt-1">Kosongkan periode tanggal jika ingin mencetak seluruh data.</small>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label class="font-weight-bold small text-gray-800">Filter Jabatan:</label>
                                    <select class="form-control form-control-sm" name="jabatan">
                                        <option value="">Semua Jabatan</option>
                                        <option value="Admin">Admin</option>
                                        <option value="Kepala Sub Bagian">Kepala Sub Bagian</option>
                                        <option value="Panitera Muda">Panitera Muda</option>
                                        <option value="Petugas">Petugas</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label class="font-weight-bold small text-gray-800">Filter Klasifikasi:</label>
                                    <select class="form-control form-control-sm" name="klasifikasi">
                                        <option value="">Semua Klasifikasi</option>
                                        <option value="Pimpinan">Pimpinan</option>
                                        <option value="Kesekretariatan">Kesekretariatan</option>
                                        <option value="Kepaniteraan">Kepaniteraan</option>
                                        <option value="belum_diatur">Belum Diatur</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label class="font-weight-bold small text-gray-800">Filter Status:</label>
                                    <select class="form-control form-control-sm" name="status">
                                        <option value="">Semua Status</option>
                                        <option value="Belum Ditugaskan">Belum Ditugaskan</option>
                                        <option value="Sedang Bertugas">Sedang Bertugas</option>
                                    </select>
                                </div>
                            </div>
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
