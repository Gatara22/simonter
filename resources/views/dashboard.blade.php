@extends('layouts.app')

@section('content')
    <!-- Page Heading & Period Filter -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 font-weight-bold">
                <i class="fas {{ $isAdmin ? 'fa-chart-line text-primary' : 'fa-tachometer-alt text-info' }} mr-2"></i>
                {{ $title }}
            </h1>
            <p class="text-muted small mb-0">
                Monitoring evaluasi terpadu & analitik performa penyelesaian tugas Pengadilan Agama Jakarta Utara
            </p>
        </div>

        <div class="d-flex align-items-center mt-3 mt-sm-0">
            <!-- Filter Periode Dropdown -->
            <div class="dropdown mr-2">
                <button class="btn btn-sm btn-white bg-white border shadow-sm dropdown-toggle text-gray-700 font-weight-bold" type="button" id="dropdownPeriode" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-calendar-alt text-primary mr-1"></i> Periode: {{ $periodeLabel }}
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownPeriode">
                    <h6 class="dropdown-header font-weight-bold text-primary">Pilih Rentang Waktu:</h6>
                    <a class="dropdown-item {{ $periode === 'semua' ? 'active font-weight-bold' : '' }}" href="{{ route('dashboard', ['periode' => 'semua']) }}">
                        <i class="fas fa-infinity fa-sm fa-fw mr-2 text-gray-400"></i> Semua Waktu
                    </a>
                    <a class="dropdown-item {{ $periode === 'bulan_ini' ? 'active font-weight-bold' : '' }}" href="{{ route('dashboard', ['periode' => 'bulan_ini']) }}">
                        <i class="fas fa-calendar-day fa-sm fa-fw mr-2 text-gray-400"></i> Bulan Ini ({{ \Carbon\Carbon::now()->translatedFormat('F Y') }})
                    </a>
                    <a class="dropdown-item {{ $periode === '3_bulan' ? 'active font-weight-bold' : '' }}" href="{{ route('dashboard', ['periode' => '3_bulan']) }}">
                        <i class="fas fa-calendar-week fa-sm fa-fw mr-2 text-gray-400"></i> 3 Bulan Terakhir
                    </a>
                    <a class="dropdown-item {{ $periode === 'tahun_ini' ? 'active font-weight-bold' : '' }}" href="{{ route('dashboard', ['periode' => 'tahun_ini']) }}">
                        <i class="fas fa-calendar fa-sm fa-fw mr-2 text-gray-400"></i> Tahun Ini ({{ \Carbon\Carbon::now()->format('Y') }})
                    </a>
                </div>
            </div>

            @if($isAdmin)
                <a href="{{ route('tugas.create') }}" class="btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Delegasikan Tugas
                </a>
            @elseif($isKasubbag ?? false)
                <a href="{{ route('tugas.create') }}" class="btn btn-sm btn-primary shadow-sm mr-2">
                    <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Delegasikan Tugas
                </a>
                <a href="{{ route('tugas.index') }}" class="btn btn-sm btn-info shadow-sm">
                    <i class="fas fa-tasks fa-sm text-white-50 mr-1"></i> Data Tugas
                </a>
            @else
                <a href="{{ route('tugas.index') }}" class="btn btn-sm btn-info shadow-sm">
                    <i class="fas fa-tasks fa-sm text-white-50 mr-1"></i> Tugas Saya
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- 1. Primary Volume Cards -->
    <div class="row">
        <!-- Card 1: Total Tugas -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body py-2">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ $isAdmin ? 'Total Tugas Terdaftar' : 'Total Tugas Ditugaskan' }}
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $totalTugas ?? 0 }}</div>
                            <small class="text-muted font-italic">{{ $periodeLabel }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Tugas Berjalan -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body py-2">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Dalam Proses Pengerjaan
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $tugasBerjalan ?? 0 }}</div>
                            <small class="text-muted">Sedang dikerjakan petugas</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-spinner fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Menunggu Konfirmasi -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body py-2">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Menunggu Verifikasi
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $tugasMenunggu ?? 0 }}</div>
                            <small class="text-muted">Evidence siap diverifikasi</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-reply-all fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Tugas Selesai -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body py-2">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Tugas Selesai & Terverifikasi
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $tugasSelesai ?? 0 }}</div>
                            <small class="text-success font-weight-bold">
                                {{ $totalTugas > 0 ? round(($tugasSelesai / $totalTugas) * 100, 1) : 0 }}% Tuntas
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Performance Infographics Header -->
    <div class="d-flex align-items-center justify-content-between mb-3 mt-2">
        <h5 class="m-0 font-weight-bold text-gray-800">
            <i class="fas fa-medal text-warning mr-1"></i>
            {{ $isAdmin ? 'Infografis Performa Penyelesaian Tugas (Seluruh Petugas)' : 'Infografis Performa Akun Saya' }}
        </h5>
        <span class="badge badge-light border border-secondary-subtle px-2 py-1 text-muted small">
            <i class="fas fa-filter mr-1 text-primary"></i> Analisis Waktu & Ketepatan
        </span>
    </div>

    <!-- 3. Performance KPI Cards (Speed, On-time Rate, Efficiency) -->
    <div class="row">
        <!-- Rata-rata Waktu Penyelesaian -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2 border-bottom-primary">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Rata-rata Waktu Pengerjaan
                            </div>
                            <div class="h3 font-weight-bold text-gray-900 mb-0">
                                {{ $avgDurasiHari }} <span class="h6 font-weight-normal text-muted">Hari</span>
                            </div>
                            <div class="small text-muted mt-1">
                                <i class="fas fa-stopwatch text-primary mr-1"></i> Durasi submit tindak lanjut
                            </div>
                        </div>
                        <div class="icon-circle bg-primary text-white p-3 rounded-circle">
                            <i class="fas fa-clock fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tingkat Ketepatan Waktu (On-Time Rate) -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2 border-bottom-success">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="text-xs font-weight-bold text-success text-uppercase">
                            Tingkat Ketepatan Waktu
                        </div>
                        <span class="badge badge-success">{{ $tugasTepatWaktu }} Tepat Waktu</span>
                    </div>
                    <div class="h3 font-weight-bold text-gray-900 mb-2">
                        {{ $onTimeRate }}%
                    </div>
                    <div class="progress progress-sm mr-2 mb-1" style="height: 7px;">
                        <div class="progress-bar {{ $onTimeRate >= 75 ? 'bg-success' : ($onTimeRate >= 50 ? 'bg-warning' : 'bg-danger') }}"
                             role="progressbar" style="width: {{ $onTimeRate }}%"
                             aria-valuenow="{{ $onTimeRate }}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <small class="text-muted">
                        {{ $tugasTerlambat }} tugas melebihi deadline
                    </small>
                </div>
            </div>
        </div>

        <!-- Rasio Penyelesaian Tugas -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2 border-bottom-info">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="text-xs font-weight-bold text-info text-uppercase">
                            Penyelesaian Tugas
                        </div>
                        <span class="badge badge-info">{{ $tugasSelesai }}/{{ $totalTugas }}</span>
                    </div>
                    <div class="h3 font-weight-bold text-gray-900 mb-2">
                        {{ $totalTugas > 0 ? round(($tugasSelesai / $totalTugas) * 100, 1) : 0 }}%
                    </div>
                    <div class="progress progress-sm mr-2 mb-1" style="height: 7px;">
                        @php
                            $compPct = $totalTugas > 0 ? round(($tugasSelesai / $totalTugas) * 100, 1) : 0;
                        @endphp
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $compPct }}%"
                             aria-valuenow="{{ $compPct }}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <small class="text-muted">
                        {{ $tugasBerjalan + $tugasMenunggu + ($tugasPerluRevisi ?? 0) }} tugas aktif belum tuntas
                    </small>
                </div>
            </div>
        </div>

        <!-- Highlight Performa Terbaik (Admin) / Predikat (Petugas) -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2 border-bottom-warning">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ $isAdmin ? 'Petugas Paling Produktif' : 'Predikat Kinerja Saya' }}
                            </div>
                            @if($isAdmin)
                                <div class="h5 font-weight-bold text-gray-900 mb-0 text-truncate" style="max-width: 170px;" title="{{ $topPetugas['name'] ?? 'Belum ada data' }}">
                                    {{ $topPetugas['name'] ?? 'Belum Ada' }}
                                </div>
                                <div class="small text-muted mt-1">
                                    @if($topPetugas)
                                        <i class="fas fa-check-double text-success mr-1"></i> {{ $topPetugas['selesai'] }} tugas ({{ $topPetugas['avg_durasi'] }} hr)
                                    @else
                                        Belum ada tugas selesai
                                    @endif
                                </div>
                            @else
                                <div class="h5 font-weight-bold text-gray-900 mb-0">
                                    {{ $predikatKinerja }}
                                </div>
                                <div class="small text-muted mt-1">
                                    <i class="fas fa-user-check text-info mr-1"></i> Berdasarkan kepatuhan & durasi
                                </div>
                            @endif
                        </div>
                        <div class="icon-circle bg-warning text-white p-3 rounded-circle">
                            <i class="fas {{ $isAdmin ? 'fa-trophy' : 'fa-award' }} fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Interactive Charts Section -->
    @if($isAdmin)
        <!-- ADMIN CHARTS -->
        <div class="row">
            <!-- Chart 1: Komparasi Kinerja Petugas (Bar Chart) -->
            <div class="col-xl-8 col-lg-7 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-bar mr-1"></i> Komparasi Tugas Selesai & Rata-rata Durasi Tiap Petugas
                        </h6>
                        <span class="badge badge-primary small">Top Petugas</span>
                    </div>
                    <div class="card-body">
                        <div class="chart-bar" style="position: relative; height: 320px;">
                            <canvas id="chartPetugasPerformance"></canvas>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-around text-center small text-muted pt-2">
                            <span><i class="fas fa-square mr-1" style="color: #4e73df;"></i> <strong>Batang Biru:</strong> Jumlah Tugas Selesai (Unit)</span>
                            <span><i class="fas fa-square mr-1" style="color: #1cc88a;"></i> <strong>Batang Hijau:</strong> Rata-rata Durasi Pengerjaan (Hari)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart 2: Distribusi Status & Ketepatan Waktu (Doughnut Chart) -->
            <div class="col-xl-4 col-lg-5 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 bg-white">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-pie mr-1"></i> Distribusi Ketepatan Waktu Tugas
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-pie pt-2 pb-2" style="position: relative; height: 240px;">
                            <canvas id="chartDistribusiStatus"></canvas>
                        </div>
                        <div class="mt-3 small">
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span><i class="fas fa-circle text-success mr-1"></i> Selesai Tepat Waktu</span>
                                <strong class="text-gray-900">{{ $tugasTepatWaktu }}</strong>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span><i class="fas fa-circle text-warning mr-1"></i> Selesai Terlambat</span>
                                <strong class="text-gray-900">{{ $tugasTerlambat }}</strong>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span><i class="fas fa-circle text-info mr-1"></i> Sedang Dikerjakan</span>
                                <strong class="text-gray-900">{{ $tugasBerjalan }}</strong>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span><i class="fas fa-circle text-primary mr-1"></i> Menunggu Konfirmasi</span>
                                <strong class="text-gray-900">{{ $tugasMenunggu }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row Tren Bulanan & Leaderboard -->
        <div class="row">
            <!-- Chart 3: Tren Penyelesaian Tugas & Rata-rata Waktu (1 Tahun, 6 Bulan, 3 Bulan, 1 Bulan) -->
            <div class="col-xl-12 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3 bg-white d-flex flex-column flex-sm-row align-items-sm-center justify-content-between">
                        <div class="mb-2 mb-sm-0">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-chart-line mr-1"></i> Tren Penyelesaian Tugas &amp; Rata-rata Waktu
                            </h6>
                            <span class="small text-muted" id="trendPeriodSubtitle">
                                <i class="fas fa-calendar-alt text-gray-400 mr-1"></i> Periode: 6 Bulan Terakhir
                            </span>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="btn-group btn-group-sm shadow-sm" role="group" id="trendPeriodBtnGroup" aria-label="Pilihan Periode Tren">
                                <button type="button" class="btn btn-outline-primary" data-period="1_tahun">
                                    <i class="fas fa-calendar mr-1 d-none d-md-inline"></i>1 Tahun
                                </button>
                                <button type="button" class="btn btn-outline-primary active" data-period="6_bulan">
                                    <i class="fas fa-calendar-alt mr-1 d-none d-md-inline"></i>6 Bulan
                                </button>
                                <button type="button" class="btn btn-outline-primary" data-period="3_bulan">
                                    <i class="fas fa-calendar-week mr-1 d-none d-md-inline"></i>3 Bulan
                                </button>
                                <button type="button" class="btn btn-outline-primary" data-period="1_bulan">
                                    <i class="fas fa-calendar-day mr-1 d-none d-md-inline"></i>1 Bulan
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-area" style="position: relative; height: 280px;">
                            <canvas id="chartMonthlyTrend"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Leaderboard Kinerja Seluruh Petugas -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-ranking-star mr-1 text-warning"></i> Matriks &amp; Peringkat Kinerja Seluruh Petugas
                </h6>
                <span class="badge badge-primary px-3 py-1 font-weight-bold">
                    {{ count($petugasStats) }} Petugas Terdaftar
                </span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tableLeaderboardPetugas" width="100%" cellspacing="0">
                        <thead class="bg-light text-center">
                            <tr>
                                <th width="5%">Rank</th>
                                <th>Petugas Pelaksana</th>
                                <th width="12%">Total Ditugaskan</th>
                                <th width="12%">Selesai</th>
                                <th width="14%">Tepat Waktu (%)</th>
                                <th width="15%">Rata-rata Waktu</th>
                                <th width="18%">Evaluasi Performa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($petugasStats as $index => $ps)
                                <tr>
                                    <td class="text-center align-middle font-weight-bold">
                                        @if($index === 0 && $ps['selesai'] > 0)
                                            <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-trophy mr-1"></i> #1</span>
                                        @elseif($index === 1 && $ps['selesai'] > 0)
                                            <span class="badge badge-secondary px-2 py-1"><i class="fas fa-medal mr-1"></i> #2</span>
                                        @elseif($index === 2 && $ps['selesai'] > 0)
                                            <span class="badge badge-dark px-2 py-1"><i class="fas fa-award mr-1"></i> #3</span>
                                        @else
                                            <span class="text-muted">#{{ $index + 1 }}</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $ps['foto_url'] }}" alt="{{ $ps['name'] }}" class="rounded-circle mr-2 border" style="width: 34px; height: 34px; object-fit: cover;">
                                            <div>
                                                <div class="font-weight-bold text-gray-900">{{ $ps['name'] }}</div>
                                                <small class="text-muted">NIP: {{ $ps['nip'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle font-weight-bold">
                                        {{ $ps['total_tugas'] }} tugas
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-success px-2 py-1 font-weight-bold">
                                            {{ $ps['selesai'] }} selesai
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex justify-content-between small font-weight-bold mb-1">
                                            <span>{{ $ps['on_time_rate'] }}%</span>
                                            <span class="text-muted">{{ $ps['tepat_waktu'] }}/{{ $ps['selesai'] }}</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar {{ $ps['on_time_rate'] >= 75 ? 'bg-success' : ($ps['on_time_rate'] >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                 role="progressbar" style="width: {{ $ps['on_time_rate'] }}%">
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($ps['selesai'] > 0)
                                            <strong class="text-gray-900">{{ $ps['avg_durasi'] }}</strong> <span class="text-muted small">Hari</span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($ps['selesai'] == 0)
                                            <span class="badge badge-light border text-muted">Belum Ada Selesai</span>
                                        @elseif($ps['on_time_rate'] >= 85 && $ps['avg_durasi'] <= 3)
                                            <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Sangat Cepat & Tepat</span>
                                        @elseif($ps['on_time_rate'] >= 75)
                                            <span class="badge badge-info px-2 py-1"><i class="fas fa-thumbs-up mr-1"></i> Tepat Waktu</span>
                                        @elseif($ps['on_time_rate'] >= 50)
                                            <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-clock mr-1"></i> Cukup Baik</span>
                                        @else
                                            <span class="badge badge-danger px-2 py-1"><i class="fas fa-exclamation-triangle mr-1"></i> Perlu Peningkatan</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-3 text-muted">Belum ada data petugas terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Keterangan Status Evaluasi Performa -->
                <div class="mt-4 p-3 bg-light border rounded small">
                    <div class="d-flex align-items-center font-weight-bold text-gray-800 mb-3">
                        <i class="fas fa-info-circle text-primary mr-2"></i>
                        <span>Keterangan Status Evaluasi Performa</span>
                    </div>
                    @php
                        $evaluasiLegend = [
                            ['class' => 'badge-success', 'icon' => 'fa-check-circle', 'label' => 'Sangat Cepat &amp; Tepat', 'desc' => 'Ketepatan waktu &ge; 85% dan rata-rata waktu pengerjaan &le; 3 hari.'],
                            ['class' => 'badge-info', 'icon' => 'fa-thumbs-up', 'label' => 'Tepat Waktu', 'desc' => 'Ketepatan waktu &ge; 75%.'],
                            ['class' => 'badge-warning text-dark', 'icon' => 'fa-clock', 'label' => 'Cukup Baik', 'desc' => 'Ketepatan waktu 50% &ndash; 74,9%.'],
                            ['class' => 'badge-danger', 'icon' => 'fa-exclamation-triangle', 'label' => 'Perlu Peningkatan', 'desc' => 'Ketepatan waktu &lt; 50%.'],
                            ['class' => 'badge-light border text-muted', 'icon' => 'fa-minus-circle', 'label' => 'Belum Ada Selesai', 'desc' => 'Petugas belum memiliki tugas berstatus Selesai pada periode terpilih.'],
                        ];
                    @endphp
                    <div class="row">
                        @foreach($evaluasiLegend as $legend)
                            <div class="col-lg-6 mb-2">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0" style="width: 170px;">
                                        <span class="badge {{ $legend['class'] }} d-inline-flex align-items-center px-2 py-1" style="font-size: 0.75rem;">
                                            <i class="fas {{ $legend['icon'] }} mr-1"></i>
                                            <span>{!! $legend['label'] !!}</span>
                                        </span>
                                    </div>
                                    <div class="text-muted pl-2" style="line-height: 1.6;">
                                        {!! $legend['desc'] !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <hr class="my-2">
                    <div class="d-flex align-items-start text-muted">
                        <i class="fas fa-sticky-note text-gray-500 mr-2 mt-1 flex-shrink-0"></i>
                        <div style="line-height: 1.6;">
                            <strong>Catatan:</strong> <em>Ketepatan waktu</em> = persentase tugas selesai yang tindak lanjutnya diserahkan sebelum/tepat pada batas waktu.
                            <em>Rata-rata waktu</em> = rata-rata hari dari tanggal mulai hingga tindak lanjut diserahkan. Penilaian mengikuti filter periode yang dipilih.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Leaderboard Kinerja Pemberi Tugas / Pekerjaan (Admin & Kepala Sub Bagian) -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-white d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-check mr-1 text-info"></i> Matriks &amp; Peringkat Kinerja Pemberi Tugas / Pekerjaan
                    </h6>
                    <small class="text-muted">
                        Diukur dari kecepatan respon verifikasi (<em>Respond Time</em>) sejak tugas selesai dikerjakan serta rasio keputusan <strong>Langsung Closed</strong> vs <strong>Perlu Revisi</strong>.
                    </small>
                </div>
                <span class="badge badge-info px-3 py-1 font-weight-bold mt-2 mt-sm-0">
                    {{ count($pemberiStats ?? []) }} Pemberi Tugas Terdaftar
                </span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tableLeaderboardPemberi" width="100%" cellspacing="0">
                        <thead class="bg-light text-center">
                            <tr>
                                <th width="5%">Rank</th>
                                <th>Pemberi Tugas / Pekerjaan</th>
                                <th width="11%">Total Didelegasikan</th>
                                <th width="12%">Telah Direspon</th>
                                <th width="18%">Keputusan Verifikasi (Closed vs Revisi)</th>
                                <th width="14%">Rasio Langsung Closed</th>
                                <th width="15%">Rata-rata Respond Time</th>
                                <th width="15%">Evaluasi Responsivitas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pemberiStats ?? [] as $idx => $pm)
                                <tr>
                                    <td class="text-center align-middle font-weight-bold">
                                        @if($idx === 0 && $pm['direspon'] > 0)
                                            <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-trophy mr-1"></i> #1</span>
                                        @elseif($idx === 1 && $pm['direspon'] > 0)
                                            <span class="badge badge-secondary px-2 py-1"><i class="fas fa-medal mr-1"></i> #2</span>
                                        @elseif($idx === 2 && $pm['direspon'] > 0)
                                            <span class="badge badge-dark px-2 py-1"><i class="fas fa-award mr-1"></i> #3</span>
                                        @else
                                            <span class="text-muted">#{{ $idx + 1 }}</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $pm['foto_url'] }}" alt="{{ $pm['name'] }}" class="rounded-circle mr-2 border" style="width: 34px; height: 34px; object-fit: cover;">
                                            <div>
                                                <div class="font-weight-bold text-gray-900">
                                                    {{ $pm['name'] }}
                                                    <span class="badge {{ $pm['jabatan'] === 'Admin' ? 'badge-primary' : 'badge-info' }} ml-1" style="font-size: 0.65rem;">
                                                        {{ $pm['jabatan'] }}
                                                    </span>
                                                </div>
                                                <small class="text-muted">NIP: {{ $pm['nip'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle font-weight-bold">
                                        {{ $pm['total_delegasi'] }} tugas
                                        @if($pm['menunggu_verifikasi'] > 0)
                                            <div class="mt-1">
                                                <span class="badge badge-warning text-dark" style="font-size: 0.65rem;">
                                                    <i class="fas fa-hourglass-half mr-1"></i>{{ $pm['menunggu_verifikasi'] }} menunggu
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-primary px-2 py-1 font-weight-bold">
                                            {{ $pm['direspon'] }} direspon
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center flex-wrap" style="gap: 4px;">
                                            <span class="badge badge-success px-2 py-1" title="Tugas langsung disetujui Selesai (Closed)">
                                                <i class="fas fa-check-circle mr-1"></i> {{ $pm['langsung_closed'] }} Langsung Closed
                                            </span>
                                            <span class="badge badge-danger px-2 py-1" title="Tugas dikembalikan untuk Perlu Revisi / Buka Kembali">
                                                <i class="fas fa-undo mr-1"></i> {{ $pm['perlu_revisi'] }} Perlu Revisi
                                            </span>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex justify-content-between small font-weight-bold mb-1">
                                            <span class="text-success">{{ $pm['direct_close_rate'] }}% Closed</span>
                                            <span class="text-danger">{{ $pm['revisi_rate'] }}% Revisi</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $pm['direct_close_rate'] }}%" title="Langsung Closed: {{ $pm['direct_close_rate'] }}%"></div>
                                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $pm['revisi_rate'] }}%" title="Perlu Revisi: {{ $pm['revisi_rate'] }}%"></div>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($pm['direspon'] > 0)
                                            <strong class="text-gray-900">{{ $pm['respond_label'] }}</strong>
                                            <div class="text-muted" style="font-size: 0.68rem;">Sejak tindak lanjut</div>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($pm['direspon'] == 0)
                                            <span class="badge badge-light border text-muted">Belum Ada Verifikasi</span>
                                        @elseif($pm['avg_respond_jam'] <= 24)
                                            <span class="badge badge-success px-2 py-1"><i class="fas fa-bolt mr-1"></i> Sangat Responsif</span>
                                        @elseif($pm['avg_respond_jam'] <= 48)
                                            <span class="badge badge-info px-2 py-1"><i class="fas fa-thumbs-up mr-1"></i> Responsif</span>
                                        @elseif($pm['avg_respond_jam'] <= 72)
                                            <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-clock mr-1"></i> Cukup Responsif</span>
                                        @else
                                            <span class="badge badge-danger px-2 py-1"><i class="fas fa-exclamation-triangle mr-1"></i> Perlu Percepatan Verifikasi</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-3 text-muted">Belum ada data pemberi tugas terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Keterangan Status Evaluasi Pemberi Tugas -->
                <div class="mt-4 p-3 bg-light border rounded small">
                    <div class="d-flex align-items-center font-weight-bold text-gray-800 mb-3">
                        <i class="fas fa-info-circle text-info mr-2"></i>
                        <span>Keterangan Matriks &amp; Evaluasi Kinerja Pemberi Tugas</span>
                    </div>
                    @php
                        $pemberiLegend = [
                            ['class' => 'badge-success', 'icon' => 'fa-bolt', 'label' => 'Sangat Responsif', 'desc' => 'Rata-rata waktu respon verifikasi (Respond Time) &le; 24 jam (1 hari) sejak tugas selesai dikerjakan.'],
                            ['class' => 'badge-info', 'icon' => 'fa-thumbs-up', 'label' => 'Responsif', 'desc' => 'Rata-rata waktu respon verifikasi &le; 48 jam (2 hari).'],
                            ['class' => 'badge-warning text-dark', 'icon' => 'fa-clock', 'label' => 'Cukup Responsif', 'desc' => 'Rata-rata waktu respon verifikasi &le; 72 jam (3 hari).'],
                            ['class' => 'badge-danger', 'icon' => 'fa-exclamation-triangle', 'label' => 'Perlu Percepatan Verifikasi', 'desc' => 'Rata-rata waktu respon verifikasi &gt; 72 jam (&gt; 3 hari).'],
                            ['class' => 'badge-light border text-muted', 'icon' => 'fa-minus-circle', 'label' => 'Belum Ada Verifikasi', 'desc' => 'Pemberi tugas belum memiliki tugas yang ditindaklanjuti &amp; diverifikasi pada periode terpilih.'],
                        ];
                    @endphp
                    <div class="row">
                        @foreach($pemberiLegend as $pLegend)
                            <div class="col-lg-6 mb-2">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0" style="width: 195px;">
                                        <span class="badge {{ $pLegend['class'] }} d-inline-flex align-items-center px-2 py-1" style="font-size: 0.75rem;">
                                            <i class="fas {{ $pLegend['icon'] }} mr-1"></i>
                                            <span>{!! $pLegend['label'] !!}</span>
                                        </span>
                                    </div>
                                    <div class="text-muted pl-2" style="line-height: 1.6;">
                                        {!! $pLegend['desc'] !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <hr class="my-2">
                    <div class="d-flex align-items-start text-muted">
                        <i class="fas fa-sticky-note text-gray-500 mr-2 mt-1 flex-shrink-0"></i>
                        <div style="line-height: 1.6;">
                            <strong>Catatan Metodologi:</strong> <em>Respond Time</em> dihitung dari selisih waktu sejak pelaksana mengirim laporan bukti tindak lanjut hingga pemberi tugas memberikan keputusan verifikasi.
                            <em>Langsung Closed</em> = tugas yang langsung disetujui selesai tanpa pengembalian revisi. <em>Perlu Revisi</em> = tugas yang dikembalikan untuk perbaikan atau dibuka kembali (<em>reopen</em>).
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @else
        <!-- PETUGAS CHARTS -->
        <div class="row">
            <!-- Chart 1: Status & Ketepatan Tugas Saya (Doughnut) -->
            <div class="col-xl-5 col-lg-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 bg-white">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-pie mr-1"></i> Distribusi Kinerja Tugas Saya
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-pie pt-2 pb-2" style="position: relative; height: 230px;">
                            <canvas id="chartPetugasStatus"></canvas>
                        </div>
                        <div class="mt-3 small">
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span><i class="fas fa-circle text-success mr-1"></i> Selesai Tepat Waktu</span>
                                <strong class="text-gray-900">{{ $tugasTepatWaktu }} Tugas</strong>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span><i class="fas fa-circle text-warning mr-1"></i> Selesai Terlambat</span>
                                <strong class="text-gray-900">{{ $tugasTerlambat }} Tugas</strong>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span><i class="fas fa-circle text-info mr-1"></i> Sedang Dikerjakan</span>
                                <strong class="text-gray-900">{{ $tugasBerjalan }} Tugas</strong>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span><i class="fas fa-circle text-primary mr-1"></i> Menunggu Konfirmasi Admin</span>
                                <strong class="text-gray-900">{{ $tugasMenunggu }} Tugas</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart 2: Riwayat Durasi Penyelesaian Tugas Terakhir (Bar) -->
            <div class="col-xl-7 col-lg-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-business-time mr-1"></i> Durasi Pengerjaan vs Target Waktu (Tugas Terakhir)
                        </h6>
                        <span class="badge badge-light border">Satuan Hari</span>
                    </div>
                    <div class="card-body">
                        @if(!empty($myTaskLabels))
                            <div class="chart-bar" style="position: relative; height: 260px;">
                                <canvas id="chartPetugasTasks"></canvas>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-around text-center small text-muted pt-1">
                                <span><i class="fas fa-square mr-1 text-primary"></i> <strong>Waktu Aktual:</strong> Hari yang dihabiskan</span>
                                <span><i class="fas fa-square mr-1 text-secondary"></i> <strong>Target Waktu:</strong> Batas deadline</span>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-clipboard-check fa-3x mb-3 text-gray-300"></i>
                                <p class="mb-0 font-weight-bold">Belum ada riwayat tugas yang selesai.</p>
                                <small>Grafik durasi pengerjaan akan otomatis tampil saat Anda menyelesaikan tugas.</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Rekomendasi & Motivasi Kerja Petugas -->
        <div class="card shadow mb-4 border-left-info">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="font-weight-bold text-gray-900 mb-1">
                            <i class="fas fa-lightbulb text-warning mr-1"></i> Rekomendasi Efisiensi & Kualitas Pelaksanaan Tugas:
                        </h6>
                        <p class="small text-muted mb-0">
                            Rata-rata waktu pengerjaan Anda saat ini adalah <strong>{{ $avgDurasiHari }} hari</strong> dengan ketepatan waktu <strong>{{ $onTimeRate }}%</strong>. Pastikan selalu memeriksa tenggat waktu dan segera mengirimkan link atau berkas evidence setelah pekerjaan terselesaikan untuk mempertahankan performa optimal.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-right mt-3 mt-md-0">
                        <a href="{{ route('tugas.index') }}" class="btn btn-sm btn-info shadow-sm">
                            <i class="fas fa-arrow-right mr-1"></i> Buka Daftar Tugas Saya
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- 5. Recent Tasks Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list-alt mr-1"></i> {{ $isAdmin ? '5 Tugas Terbaru Didelegasikan' : '5 Tugas Terbaru Saya' }}
            </h6>
            <a href="{{ route('tugas.index') }}" class="btn btn-sm btn-outline-primary">
                Lihat Semua Tugas <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0" width="100%" cellspacing="0">
                    <thead class="bg-light text-center">
                        <tr>
                            <th width="5%">No</th>
                            <th>Judul Tugas</th>
                            <th>{{ $isAdmin ? 'Petugas' : 'Didelegasikan Oleh' }}</th>
                            <th>Batas Selesai</th>
                            <th>Status</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTugas as $t)
                            <tr>
                                <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                <td class="align-middle">
                                    <span class="font-weight-bold text-gray-900">{{ $t->judul }}</span>
                                </td>
                                <td class="align-middle">
                                    {{ $isAdmin ? ($t->petugas->name ?? '-') : ($t->admin->name ?? 'Admin') }}
                                </td>
                                <td class="text-center align-middle">
                                    {{ $t->tanggal_selesai ? $t->tanggal_selesai->format('d/m/Y') : '-' }}
                                </td>
                                <td class="text-center align-middle">
                                    @if($t->status === 'Didelegasikan')
                                        <span class="badge badge-info badge-pill px-2 py-1">Didelegasikan</span>
                                    @elseif($t->status === 'Sedang Dikerjakan')
                                        <span class="badge badge-primary badge-pill px-2 py-1">Sedang Dikerjakan</span>
                                    @elseif($t->status === 'Menunggu Konfirmasi')
                                        <span class="badge badge-warning badge-pill px-2 py-1 text-dark">Menunggu Konfirmasi</span>
                                    @elseif($t->status === 'Selesai')
                                        <span class="badge badge-success badge-pill px-2 py-1">Selesai</span>
                                    @elseif($t->status === 'Perlu Revisi')
                                        <span class="badge badge-danger badge-pill px-2 py-1">Perlu Revisi</span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    <a href="{{ route('tugas.show', $t->id) }}" class="btn btn-sm btn-info" title="Lihat Rincian">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-3 text-muted">Belum ada tugas terbaru.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Chart.js Library -->
    <script src="{{ asset('sbadmin2/vendor/chart.js/Chart.bundle.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Set Font Family to match SB Admin 2
            Chart.defaults.global.defaultFontFamily = 'Nunito, -apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
            Chart.defaults.global.defaultFontColor = '#858796';

            @if($isAdmin)
                // 1. ADMIN BAR CHART: Kinerja Petugas (Tugas Selesai & Rata-rata Durasi)
                const ctxPetugas = document.getElementById('chartPetugasPerformance');
                if (ctxPetugas) {
                    new Chart(ctxPetugas, {
                        type: 'bar',
                        data: {
                            labels: @json($chartPetugasLabels),
                            datasets: [
                                {
                                    label: 'Tugas Selesai (Unit)',
                                    backgroundColor: '#4e73df',
                                    hoverBackgroundColor: '#2e59d9',
                                    borderColor: '#4e73df',
                                    data: @json($chartPetugasSelesai),
                                    maxBarThickness: 35,
                                },
                                {
                                    label: 'Rata-rata Waktu (Hari)',
                                    backgroundColor: '#1cc88a',
                                    hoverBackgroundColor: '#17a673',
                                    borderColor: '#1cc88a',
                                    data: @json($chartPetugasAvgDurasi),
                                    maxBarThickness: 35,
                                }
                            ]
                        },
                        options: {
                            maintainAspectRatio: false,
                            layout: { padding: { left: 10, right: 15, top: 15, bottom: 0 } },
                            scales: {
                                xAxes: [{
                                    gridLines: { display: false, drawBorder: false },
                                    ticks: { maxTicksLimit: 7 }
                                }],
                                yAxes: [{
                                    ticks: {
                                        min: 0,
                                        maxTicksLimit: 6,
                                        padding: 10,
                                        callback: function(value) { return Number.isInteger(value) ? value : value.toFixed(1); }
                                    },
                                    gridLines: { color: "rgb(234, 236, 244)", zeroLineColor: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] }
                                }]
                            },
                            legend: { display: false },
                            tooltips: {
                                backgroundColor: "rgb(255,255,255)",
                                bodyFontColor: "#858796",
                                titleMarginBottom: 10,
                                titleFontColor: '#6e707e',
                                titleFontSize: 14,
                                borderColor: '#dddfeb',
                                borderWidth: 1,
                                xPadding: 15,
                                yPadding: 15,
                                displayColors: false,
                                intersect: false,
                                mode: 'index',
                                caretPadding: 10
                            }
                        }
                    });
                }

                // 2. ADMIN DOUGHNUT CHART: Distribusi Ketepatan Waktu
                const ctxDistribusi = document.getElementById('chartDistribusiStatus');
                if (ctxDistribusi) {
                    new Chart(ctxDistribusi, {
                        type: 'doughnut',
                        data: {
                            labels: @json($chartDoughnutLabels),
                            datasets: [{
                                data: @json($chartDoughnutData),
                                backgroundColor: ['#1cc88a', '#f6c23e', '#36b9cc', '#4e73df', '#e74a3b'],
                                hoverBackgroundColor: ['#17a673', '#dda20a', '#2c9faf', '#2e59d9', '#be2617'],
                                hoverBorderColor: "rgba(234, 236, 244, 1)",
                            }]
                        },
                        options: {
                            maintainAspectRatio: false,
                            tooltips: {
                                backgroundColor: "rgb(255,255,255)",
                                bodyFontColor: "#858796",
                                borderColor: '#dddfeb',
                                borderWidth: 1,
                                xPadding: 15,
                                yPadding: 15,
                                displayColors: false,
                                caretPadding: 10,
                            },
                            legend: { display: false },
                            cutoutPercentage: 70,
                        }
                    });
                }

                // 3. ADMIN LINE/AREA CHART: Tren Penyelesaian Tugas & Rata-rata Waktu (1 Tahun, 6 Bulan, 3 Bulan, 1 Bulan)
                const ctxTrend = document.getElementById('chartMonthlyTrend');
                const trendDatasets = @json($trendDatasets ?? []);
                let chartMonthlyTrendInstance = null;

                if (ctxTrend) {
                    const initialPeriod = '6_bulan';
                    const initialData = (trendDatasets && trendDatasets[initialPeriod]) ? trendDatasets[initialPeriod] : {
                        labels: @json($trendLabels ?? []),
                        selesai: @json($trendSelesaiData ?? []),
                        durasi: @json($trendAvgDurasiData ?? []),
                        subtitle: '6 Bulan Terakhir'
                    };

                    chartMonthlyTrendInstance = new Chart(ctxTrend, {
                        type: 'line',
                        data: {
                            labels: initialData.labels,
                            datasets: [
                                {
                                    label: 'Tugas Selesai',
                                    lineTension: 0.3,
                                    backgroundColor: "rgba(78, 115, 223, 0.05)",
                                    borderColor: "rgba(78, 115, 223, 1)",
                                    pointRadius: 4,
                                    pointBackgroundColor: "rgba(78, 115, 223, 1)",
                                    pointBorderColor: "rgba(78, 115, 223, 1)",
                                    pointHoverRadius: 5,
                                    pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                                    pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                                    pointHitRadius: 10,
                                    pointBorderWidth: 2,
                                    data: initialData.selesai,
                                },
                                {
                                    label: 'Rata-rata Waktu (Hari)',
                                    lineTension: 0.3,
                                    backgroundColor: "rgba(28, 200, 138, 0.05)",
                                    borderColor: "rgba(28, 200, 138, 1)",
                                    pointRadius: 4,
                                    pointBackgroundColor: "rgba(28, 200, 138, 1)",
                                    pointBorderColor: "rgba(28, 200, 138, 1)",
                                    pointHoverRadius: 5,
                                    pointHoverBackgroundColor: "rgba(28, 200, 138, 1)",
                                    pointHoverBorderColor: "rgba(28, 200, 138, 1)",
                                    pointHitRadius: 10,
                                    pointBorderWidth: 2,
                                    data: initialData.durasi,
                                }
                            ],
                        },
                        options: {
                            maintainAspectRatio: false,
                            layout: { padding: { left: 10, right: 25, top: 15, bottom: 0 } },
                            scales: {
                                xAxes: [{
                                    gridLines: { display: false, drawBorder: false },
                                    ticks: { maxTicksLimit: 12 }
                                }],
                                yAxes: [{
                                    ticks: {
                                        min: 0,
                                        maxTicksLimit: 6,
                                        padding: 10,
                                        callback: function(value) { return Number.isInteger(value) ? value : value.toFixed(1); }
                                    },
                                    gridLines: { color: "rgb(234, 236, 244)", zeroLineColor: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] }
                                }],
                            },
                            legend: { display: true, position: 'top' },
                            tooltips: {
                                backgroundColor: "rgb(255,255,255)",
                                bodyFontColor: "#858796",
                                titleMarginBottom: 10,
                                titleFontColor: '#6e707e',
                                titleFontSize: 14,
                                borderColor: '#dddfeb',
                                borderWidth: 1,
                                xPadding: 15,
                                yPadding: 15,
                                displayColors: false,
                                intersect: false,
                                mode: 'index',
                                caretPadding: 10,
                            }
                        }
                    });

                    // Event listeners untuk tombol pilihan periode tren (1 Tahun, 6 Bulan, 3 Bulan, 1 Bulan)
                    const periodButtons = document.querySelectorAll('#trendPeriodBtnGroup button[data-period]');
                    const periodSubtitle = document.getElementById('trendPeriodSubtitle');

                    periodButtons.forEach(function (button) {
                        button.addEventListener('click', function () {
                            const selectedPeriod = this.getAttribute('data-period');
                            if (!trendDatasets || !trendDatasets[selectedPeriod]) {
                                return;
                            }

                            // Update visual active state pada tombol
                            periodButtons.forEach(function (b) {
                                b.classList.remove('active');
                            });
                            this.classList.add('active');

                            // Update teks sub-judul keterangan periode
                            if (periodSubtitle && trendDatasets[selectedPeriod].subtitle) {
                                periodSubtitle.innerHTML = '<i class="fas fa-calendar-alt text-gray-400 mr-1"></i> Periode: ' + trendDatasets[selectedPeriod].subtitle;
                            }

                            // Update data grafik dan re-render secara instan
                            const targetData = trendDatasets[selectedPeriod];
                            chartMonthlyTrendInstance.data.labels = targetData.labels;
                            chartMonthlyTrendInstance.data.datasets[0].data = targetData.selesai;
                            chartMonthlyTrendInstance.data.datasets[1].data = targetData.durasi;
                            chartMonthlyTrendInstance.update();
                        });
                    });
                }

                // Inisialisasi DataTable untuk Matriks & Peringkat Kinerja Seluruh Petugas
                @if(count($petugasStats) > 0)
                    if ($('#tableLeaderboardPetugas').length) {
                        $('#tableLeaderboardPetugas').DataTable({
                            pageLength: 10,
                            lengthMenu: [10, 25, 50, 100],
                            ordering: false,
                            language: {
                                search: "Cari Petugas:",
                                lengthMenu: "Tampilkan _MENU_ data per halaman",
                                zeroRecords: "Data petugas tidak ditemukan",
                                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ petugas",
                                infoEmpty: "Menampilkan 0 data",
                                infoFiltered: "(difilter dari _MAX_ total petugas)",
                                paginate: {
                                    first: "Pertama",
                                    last: "Terakhir",
                                    next: "Selanjutnya",
                                    previous: "Sebelumnya"
                                }
                            }
                        });
                    }
                @endif

                // Inisialisasi DataTable untuk Matriks & Peringkat Kinerja Pemberi Tugas
                @if(count($pemberiStats ?? []) > 0)
                    if ($('#tableLeaderboardPemberi').length) {
                        $('#tableLeaderboardPemberi').DataTable({
                            pageLength: 10,
                            lengthMenu: [10, 25, 50, 100],
                            ordering: false,
                            language: {
                                search: "Cari Pemberi Tugas:",
                                lengthMenu: "Tampilkan _MENU_ data per halaman",
                                zeroRecords: "Data pemberi tugas tidak ditemukan",
                                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ pemberi tugas",
                                infoEmpty: "Menampilkan 0 data",
                                infoFiltered: "(difilter dari _MAX_ total pemberi tugas)",
                                paginate: {
                                    first: "Pertama",
                                    last: "Terakhir",
                                    next: "Selanjutnya",
                                    previous: "Sebelumnya"
                                }
                            }
                        });
                    }
                @endif
            @else
                // 1. PETUGAS DOUGHNUT CHART: Status Tugas Saya
                const ctxPetugasStatus = document.getElementById('chartPetugasStatus');
                if (ctxPetugasStatus) {
                    new Chart(ctxPetugasStatus, {
                        type: 'doughnut',
                        data: {
                            labels: @json($chartDoughnutLabels),
                            datasets: [{
                                data: @json($chartDoughnutData),
                                backgroundColor: ['#1cc88a', '#f6c23e', '#36b9cc', '#4e73df', '#e74a3b'],
                                hoverBackgroundColor: ['#17a673', '#dda20a', '#2c9faf', '#2e59d9', '#be2617'],
                                hoverBorderColor: "rgba(234, 236, 244, 1)",
                            }]
                        },
                        options: {
                            maintainAspectRatio: false,
                            tooltips: {
                                backgroundColor: "rgb(255,255,255)",
                                bodyFontColor: "#858796",
                                borderColor: '#dddfeb',
                                borderWidth: 1,
                                xPadding: 15,
                                yPadding: 15,
                                displayColors: false,
                                caretPadding: 10,
                            },
                            legend: { display: false },
                            cutoutPercentage: 70,
                        }
                    });
                }

                // 2. PETUGAS BAR CHART: Durasi Pengerjaan Tugas Terakhir vs Target
                const ctxPetugasTasks = document.getElementById('chartPetugasTasks');
                if (ctxPetugasTasks) {
                    new Chart(ctxPetugasTasks, {
                        type: 'bar',
                        data: {
                            labels: @json($myTaskLabels ?? []),
                            datasets: [
                                {
                                    label: 'Waktu Aktual (Hari)',
                                    backgroundColor: '#4e73df',
                                    hoverBackgroundColor: '#2e59d9',
                                    borderColor: '#4e73df',
                                    data: @json($myTaskActualDurasi ?? []),
                                    maxBarThickness: 25,
                                },
                                {
                                    label: 'Target Waktu Deadline (Hari)',
                                    backgroundColor: '#d1d3e2',
                                    hoverBackgroundColor: '#b7b9cc',
                                    borderColor: '#d1d3e2',
                                    data: @json($myTaskTargetDurasi ?? []),
                                    maxBarThickness: 25,
                                }
                            ]
                        },
                        options: {
                            maintainAspectRatio: false,
                            layout: { padding: { left: 10, right: 15, top: 15, bottom: 0 } },
                            scales: {
                                xAxes: [{
                                    gridLines: { display: false, drawBorder: false },
                                    ticks: { maxTicksLimit: 6 }
                                }],
                                yAxes: [{
                                    ticks: {
                                        min: 0,
                                        maxTicksLimit: 5,
                                        padding: 10,
                                        callback: function(value) { return Number.isInteger(value) ? value : value.toFixed(1); }
                                    },
                                    gridLines: { color: "rgb(234, 236, 244)", zeroLineColor: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] }
                                }]
                            },
                            legend: { display: false },
                            tooltips: {
                                backgroundColor: "rgb(255,255,255)",
                                bodyFontColor: "#858796",
                                titleMarginBottom: 10,
                                titleFontColor: '#6e707e',
                                titleFontSize: 14,
                                borderColor: '#dddfeb',
                                borderWidth: 1,
                                xPadding: 15,
                                yPadding: 15,
                                displayColors: false,
                                intersect: false,
                                mode: 'index',
                                caretPadding: 10
                            }
                        }
                    });
                }
            @endif
        });
    </script>
@endpush