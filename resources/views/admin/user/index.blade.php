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
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="bg-primary text-white">
                        <tr class="text-center">
                            <th width="5%">No</th>
                            <th>Nama</th>
                            <th>NIP</th>
                            <th>Kontak</th>
                            <th>Jabatan</th>
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
                                    <span class="font-weight-bold">{{ $user->name }}</span>
                                    @if($user->email)
                                        <br><small class="text-muted"><i class="fas fa-envelope fa-xs mr-1"></i>{{ $user->email }}</small>
                                    @endif
                                </td>
                                <td class="align-middle text-center">{{ $user->nip }}</td>
                                <td class="align-middle text-center">{{ $user->kontak ?? '-' }}</td>
                                <td class="text-center align-middle">
                                    @if($user->jabatan === 'Admin')
                                        <span class="badge badge-dark badge-pill px-2 py-1">Admin</span>
                                    @elseif($user->jabatan === 'Kepala Sub Bagian')
                                        <span class="badge badge-primary badge-pill px-2 py-1">Kepala Sub Bagian</span>
                                    @else
                                        <span class="badge badge-info badge-pill px-2 py-1">Petugas</span>
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
                                            <form action="{{ route('user.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user {{ $user->name }}?');">
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
                                <td colspan="7" class="text-center py-3 text-muted">Belum ada data user.</td>
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
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="font-weight-bold small text-gray-800">Filter Jabatan:</label>
                                    <select class="form-control form-control-sm" name="jabatan">
                                        <option value="">Semua Jabatan</option>
                                        <option value="Admin">Admin</option>
                                        <option value="Kepala Sub Bagian">Kepala Sub Bagian</option>
                                        <option value="Petugas">Petugas</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
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
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="font-weight-bold small text-gray-800">Filter Jabatan:</label>
                                    <select class="form-control form-control-sm" name="jabatan">
                                        <option value="">Semua Jabatan</option>
                                        <option value="Admin">Admin</option>
                                        <option value="Kepala Sub Bagian">Kepala Sub Bagian</option>
                                        <option value="Petugas">Petugas</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
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
