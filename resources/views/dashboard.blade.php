@extends('layouts.app')

@section('content')
    <style>
        @media (min-width: 1200px) {
            .col-xl-fifth {
                flex: 0 0 20%;
                max-width: 20%;
            }
        }
        .deadline-alert-card {
            cursor: pointer;
        }
        .deadline-alert-card.has-urgent-deadline {
            background: linear-gradient(135deg, #ffffff 0%, #fff5f5 100%);
        }
        .legend-collapse-toggle .legend-chevron {
            transition: transform 0.2s ease;
        }
        .legend-collapse-toggle[aria-expanded="true"] .legend-chevron {
            transform: rotate(180deg);
        }
    </style>

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

        <div class="d-flex flex-wrap align-items-center mt-3 mt-sm-0" style="gap: 8px;">
            <!-- Filter Klasifikasi Dropdown -->
            <div class="dropdown">
                <button class="btn btn-sm btn-white bg-white border shadow-sm dropdown-toggle text-gray-700 font-weight-bold" type="button" id="dropdownKlasifikasi" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-sitemap text-success mr-1"></i> Klasifikasi: {{ $klasifikasiLabel ?? 'Semua Klasifikasi' }}
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownKlasifikasi">
                    <h6 class="dropdown-header font-weight-bold text-success">Pilih Klasifikasi Tugas:</h6>
                    <a class="dropdown-item {{ empty($klasifikasi) ? 'active font-weight-bold' : '' }}" href="{{ route('dashboard', ['periode' => $periode]) }}">
                        <i class="fas fa-layer-group fa-sm fa-fw mr-2 text-gray-400"></i> Semua Klasifikasi
                    </a>
                    <a class="dropdown-item {{ ($klasifikasi ?? null) === 'Kesekretariatan' ? 'active font-weight-bold' : '' }}" href="{{ route('dashboard', ['periode' => $periode, 'klasifikasi' => 'Kesekretariatan']) }}">
                        <i class="fas fa-building fa-sm fa-fw mr-2 text-primary"></i> Kesekretariatan
                    </a>
                    <a class="dropdown-item {{ ($klasifikasi ?? null) === 'Kepaniteraan' ? 'active font-weight-bold' : '' }}" href="{{ route('dashboard', ['periode' => $periode, 'klasifikasi' => 'Kepaniteraan']) }}">
                        <i class="fas fa-balance-scale fa-sm fa-fw mr-2 text-success"></i> Kepaniteraan
                    </a>
                </div>
            </div>

            <!-- Filter Periode Dropdown -->
            <div class="dropdown">
                <button class="btn btn-sm btn-white bg-white border shadow-sm dropdown-toggle text-gray-700 font-weight-bold" type="button" id="dropdownPeriode" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-calendar-alt text-primary mr-1"></i> Periode: {{ $periodeLabel }}
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownPeriode">
                    <h6 class="dropdown-header font-weight-bold text-primary">Pilih Rentang Waktu:</h6>
                    <a class="dropdown-item {{ $periode === 'semua' ? 'active font-weight-bold' : '' }}" href="{{ route('dashboard', array_filter(['periode' => 'semua', 'klasifikasi' => $klasifikasi ?? null])) }}">
                        <i class="fas fa-infinity fa-sm fa-fw mr-2 text-gray-400"></i> Semua Waktu
                    </a>
                    <a class="dropdown-item {{ $periode === 'bulan_ini' ? 'active font-weight-bold' : '' }}" href="{{ route('dashboard', array_filter(['periode' => 'bulan_ini', 'klasifikasi' => $klasifikasi ?? null])) }}">
                        <i class="fas fa-calendar-day fa-sm fa-fw mr-2 text-gray-400"></i> Bulan Ini ({{ \Carbon\Carbon::now()->translatedFormat('F Y') }})
                    </a>
                    <a class="dropdown-item {{ $periode === '3_bulan' ? 'active font-weight-bold' : '' }}" href="{{ route('dashboard', array_filter(['periode' => '3_bulan', 'klasifikasi' => $klasifikasi ?? null])) }}">
                        <i class="fas fa-calendar-week fa-sm fa-fw mr-2 text-gray-400"></i> 3 Bulan Terakhir
                    </a>
                    <a class="dropdown-item {{ $periode === 'tahun_ini' ? 'active font-weight-bold' : '' }}" href="{{ route('dashboard', array_filter(['periode' => 'tahun_ini', 'klasifikasi' => $klasifikasi ?? null])) }}">
                        <i class="fas fa-calendar fa-sm fa-fw mr-2 text-gray-400"></i> Tahun Ini ({{ \Carbon\Carbon::now()->format('Y') }})
                    </a>
                </div>
            </div>

            @if($isAdmin)
                <a href="{{ route('tugas.create') }}" class="btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Delegasikan Tugas
                </a>
            @elseif($isKasubbag ?? false)
                <a href="{{ route('tugas.create') }}" class="btn btn-sm btn-primary shadow-sm">
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(($isAdmin || ($isKasubbag ?? false)) && ($pendingPermohonanCount ?? 0) > 0)
        <div class="alert alert-warning border-left-warning shadow-sm d-flex flex-wrap align-items-center justify-content-between" role="alert">
            <div class="small text-gray-900">
                <i class="fas fa-hand-paper text-warning mr-2"></i>
                Terdapat <strong>{{ $pendingPermohonanCount }}</strong> permohonan ceklist petugas (<strong>HOLD / Perpanjangan Masa Pengerjaan</strong>) yang menunggu persetujuan Anda.
            </div>
            <a href="{{ route('tugas.index', array_filter(['permohonan' => 'menunggu', 'klasifikasi' => $klasifikasi ?? null])) }}" class="btn btn-sm btn-warning text-dark font-weight-bold shadow-sm mt-2 mt-sm-0">
                <i class="fas fa-eye mr-1"></i> Tinjau Permohonan
            </a>
        </div>
    @endif

    <!-- 1. Primary Volume Cards -->
    <div class="row">
        <!-- Card 1: Tugas Mendekati Deadline (0-2 Hari) -->
        <div class="col-xl-fifth col-md-6 mb-4">
            <a href="#modalTugasMendekatiDeadline"
               role="button"
               data-toggle="modal"
               data-target="#modalTugasMendekatiDeadline"
               id="cardMendekatiDeadline"
               class="text-decoration-none d-block h-100">
                <div class="card border-left-danger shadow h-100 py-2 card-hover-lift deadline-alert-card {{ ($tugasMendekatiDeadlineCount ?? 0) > 0 ? 'has-urgent-deadline' : '' }}">
                    <div class="card-body py-2">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Mendekati Deadline (0-2 Hari)
                                </div>
                                <div class="h4 mb-0 font-weight-bold {{ ($tugasMendekatiDeadlineCount ?? 0) > 0 ? 'text-danger' : 'text-gray-800' }}">
                                    {{ $tugasMendekatiDeadlineCount ?? 0 }}
                                </div>
                                @if(($tugasMendekatiDeadlineCount ?? 0) > 0)
                                    <small class="text-danger font-weight-bold">
                                        <i class="fas fa-mouse-pointer mr-1"></i>Klik lihat daftar tugas
                                    </small>
                                @else
                                    <small class="text-muted">Tidak ada tugas mendesak</small>
                                @endif
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-hourglass-half fa-2x {{ ($tugasMendekatiDeadlineCount ?? 0) > 0 ? 'text-danger' : 'text-gray-300' }}" style="{{ ($tugasMendekatiDeadlineCount ?? 0) > 0 ? 'opacity: 0.45;' : '' }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Card 2: Total Tugas -->
        <div class="col-xl-fifth col-md-6 mb-4">
            <a href="{{ route('tugas.index', array_filter(['klasifikasi' => $klasifikasi ?? null])) }}" class="text-decoration-none">
                <div class="card border-left-primary shadow h-100 py-2 card-hover-lift">
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
            </a>
        </div>

        <!-- Card 3: Tugas Berjalan -->
        <div class="col-xl-fifth col-md-6 mb-4">
            <a href="{{ route('tugas.index', array_filter(['status' => 'berjalan', 'klasifikasi' => $klasifikasi ?? null])) }}" class="text-decoration-none">
                <div class="card border-left-info shadow h-100 py-2 card-hover-lift">
                    <div class="card-body py-2">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Dalam Proses Pengerjaan
                                </div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $tugasBerjalan ?? 0 }}</div>
                                <small class="text-muted">Sedang dikerjakan petugas</small>
                                @if(($tugasHold ?? 0) > 0)
                                    <div class="mt-1">
                                        <span class="badge badge-secondary px-2 py-0" style="background-color: #475569; font-size: 0.68rem;">
                                            <i class="fas fa-pause-circle mr-1"></i>{{ $tugasHold }} HOLD
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-spinner fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Card 4: Menunggu Konfirmasi -->
        <div class="col-xl-fifth col-md-6 mb-4">
            <a href="{{ route('tugas.index', array_filter(['status' => 'Menunggu Konfirmasi', 'klasifikasi' => $klasifikasi ?? null])) }}" class="text-decoration-none">
                <div class="card border-left-warning shadow h-100 py-2 card-hover-lift">
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
            </a>
        </div>

        <!-- Card 5: Tugas Selesai -->
        <div class="col-xl-fifth col-md-6 mb-4">
            <a href="{{ route('tugas.index', array_filter(['status' => 'Selesai', 'klasifikasi' => $klasifikasi ?? null])) }}" class="text-decoration-none">
                <div class="card border-left-success shadow h-100 py-2 card-hover-lift">
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
            </a>
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
                        {{ $tugasBerjalan + $tugasMenunggu + ($tugasPerluRevisi ?? 0) + ($tugasHold ?? 0) }} tugas aktif belum tuntas
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
            <!-- Chart 1: Komparasi Kinerja Petugas (Dual-Axis Stacked Bar + Line) -->
            <div class="col-xl-8 col-lg-7 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 bg-white d-flex flex-wrap align-items-center justify-content-between" style="gap: 8px;">
                        <div>
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-chart-bar mr-1"></i> Komparasi Tugas Selesai &amp; Rata-rata Durasi Tiap Petugas
                            </h6>
                            <small class="text-muted">Klik batang/titik petugas untuk audit daftar tugas penyusun</small>
                        </div>
                        <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                            <div class="btn-group btn-group-sm shadow-sm" role="group" id="petugasScopeBtnGroup" aria-label="Cakupan Petugas">
                                <button type="button" class="btn btn-outline-primary active" data-scope="top_aktif">Top 7 Aktif</button>
                                <button type="button" class="btn btn-outline-primary" data-scope="semua">Semua</button>
                            </div>
                            <div class="btn-group btn-group-sm shadow-sm" role="group" id="petugasDayModeBtnGroup" aria-label="Basis Hari Petugas">
                                <button type="button" class="btn btn-outline-info active" data-day-mode="kalender">Hari Kalender</button>
                                <button type="button" class="btn btn-outline-info" data-day-mode="kerja">Hari Kerja (Sen-Jum)</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-bar" style="position: relative; height: 320px;">
                            <canvas id="chartPetugasPerformance"></canvas>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex flex-wrap justify-content-between align-items-center small text-muted pt-1" style="gap: 8px;">
                            <div class="d-flex flex-wrap align-items-center" style="gap: 12px;">
                                <span><i class="fas fa-square mr-1" style="color: #1cc88a;"></i> Tepat Waktu</span>
                                <span><i class="fas fa-square mr-1" style="color: #f6c23e;"></i> Terlambat</span>
                                <span><i class="fas fa-minus mr-1" style="color: #4e73df;"></i> Rata-rata Durasi (Sumbu Kanan)</span>
                                <span><i class="fas fa-ellipsis-h mr-1" style="color: #0ea5e9;"></i> Median Durasi</span>
                            </div>
                            <button class="btn btn-xs btn-link p-0 font-weight-bold text-primary" type="button" data-toggle="collapse" data-target="#collapseMetodologiPetugas" aria-expanded="false" aria-controls="collapseMetodologiPetugas">
                                <i class="fas fa-calculator mr-1"></i>Metodologi &amp; Rumus
                            </button>
                        </div>
                        <div class="collapse mt-2" id="collapseMetodologiPetugas">
                            <div class="p-2 bg-light border rounded small text-muted" style="font-size: 0.76rem; line-height: 1.55;">
                                <div><strong>• Sumbu Y Kiri (Stacked Bar):</strong> Total tugas selesai per petugas (<em>Tepat Waktu</em> + <em>Terlambat</em>). Pada tugas kolaborasi multi-petugas, hanya petugas dengan minimal 1 komentar/lampiran yang dihitung.</div>
                                <div><strong>• Sumbu Y Kanan (Garis):</strong> Rata-rata (<em>Mean</em>) &amp; <em>Median</em> durasi pengerjaan efektif (presisi 0,1 hari, mengecualikan durasi <strong>HOLD</strong>). Nilai <code>null</code> (tidak digambar) bila petugas belum menuntaskan tugas.</div>
                                <div><strong>• Skor Peringkat Audit:</strong> <code>(Tepat Waktu × 10) + (Terlambat × 4) + (On-Time % × 0,4) + (First-Pass % × 0,2)</code>.</div>
                            </div>
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
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span><i class="fas fa-circle text-primary mr-1"></i> Menunggu Konfirmasi</span>
                                <strong class="text-gray-900">{{ $tugasMenunggu }}</strong>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span><i class="fas fa-circle mr-1" style="color: #475569;"></i> HOLD (Ditunda)</span>
                                <strong class="text-gray-900">{{ $tugasHold ?? 0 }}</strong>
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
                    <div class="card-header py-3 bg-white d-flex flex-column flex-lg-row align-items-lg-center justify-content-between" style="gap: 8px;">
                        <div class="mb-1 mb-lg-0">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-chart-line mr-1"></i> Tren Penyelesaian Tugas &amp; Rata-rata Waktu
                            </h6>
                            <span class="small text-muted" id="trendPeriodSubtitle">
                                <i class="fas fa-calendar-alt text-gray-400 mr-1"></i> Periode: 6 Bulan Terakhir (Berdasarkan Realisasi Selesai)
                            </span>
                        </div>
                        <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                            <div class="btn-group btn-group-sm shadow-sm" role="group" id="trendDayModeBtnGroup" aria-label="Basis Hari Tren">
                                <button type="button" class="btn btn-outline-info active" data-day-mode="kalender">Hari Kalender</button>
                                <button type="button" class="btn btn-outline-info" data-day-mode="kerja">Hari Kerja (Sen-Jum)</button>
                            </div>
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
                        <div class="chart-area" style="position: relative; height: 295px;">
                            <canvas id="chartMonthlyTrend"></canvas>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex flex-wrap justify-content-between align-items-center small text-muted pt-1" style="gap: 8px;">
                            <div class="d-flex flex-wrap align-items-center" style="gap: 12px;">
                                <span><i class="fas fa-square mr-1" style="color: #1cc88a;"></i> Tepat Waktu (Unit)</span>
                                <span><i class="fas fa-square mr-1" style="color: #f6c23e;"></i> Terlambat (Unit)</span>
                                <span><i class="fas fa-minus mr-1" style="color: #4e73df;"></i> Rata-rata Durasi (Hari)</span>
                                <span><i class="fas fa-ellipsis-h mr-1" style="color: #0ea5e9;"></i> Median Durasi (Hari)</span>
                                <span><i class="fas fa-ellipsis-h mr-1" style="color: #94a3b8;"></i> Rata-rata Target SLA (Hari)</span>
                                <span class="text-info"><i class="fas fa-mouse-pointer mr-1"></i>Klik titik/batang untuk rincian tugas</span>
                            </div>
                            <button class="btn btn-xs btn-link p-0 font-weight-bold text-primary" type="button" data-toggle="collapse" data-target="#collapseMetodologiTren" aria-expanded="false" aria-controls="collapseMetodologiTren">
                                <i class="fas fa-calculator mr-1"></i>Metodologi &amp; Rumus
                            </button>
                        </div>
                        <div class="collapse mt-2" id="collapseMetodologiTren">
                            <div class="p-2 bg-light border rounded small text-muted" style="font-size: 0.76rem; line-height: 1.55;">
                                <div><strong>• Tanggal Pengelompokan Akuntabel:</strong> Tugas dicatat ke bulan/pekan berdasarkan <strong>tanggal realisasi selesai</strong> (timestamp verifikasi <em>Closed/Selesai</em>, atau waktu penyerahan tindak lanjut), bukan berdasarkan tanggal deadline rencana.</div>
                                <div><strong>• Periode Tanpa Data (<code>N = 0</code>):</strong> Jika pada suatu bulan/pekan belum ada tugas selesai, nilai rata-rata/median durasi diperlakukan sebagai <code>null</code> (terputus) agar grafik tidak turun palsu ke angka 0 hari.</div>
                                <div><strong>• Pembanding Outlier &amp; SLA:</strong> Garis <em>Median</em> membantu mendeteksi apakah rata-rata tertarik 1 tugas besar, sedangkan garis <em>Target SLA</em> menunjukkan batas rata-rata waktu rencana yang ditetapkan pemberi tugas.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Leaderboard Kinerja Seluruh Petugas -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-trophy mr-1 text-warning"></i> Matriks &amp; Peringkat Kinerja Seluruh Petugas
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
                                                <div class="font-weight-bold text-gray-900">
                                                    {{ $ps['name'] }}
                                                    @if(!empty($ps['jabatan']))
                                                        <span class="badge {{ $ps['jabatan'] === 'Kepala Sub Bagian' ? 'badge-primary' : ($ps['jabatan'] === 'Panitera Muda' ? 'badge-success' : 'badge-info') }} ml-1" style="font-size: 0.65rem;">
                                                            {{ $ps['jabatan'] }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <small class="text-muted">NIP: {{ $ps['nip'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle font-weight-bold">
                                        {{ $ps['total_tugas'] }} tugas
                                        @if(($ps['kolaborasi_count'] ?? 0) > 0)
                                            <div class="small text-info font-weight-normal" style="font-size: 0.7rem;">
                                                <i class="fas fa-users mr-1"></i>{{ $ps['kolaborasi_count'] }} kolaborasi
                                            </div>
                                        @endif
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
                                            <div>
                                                <strong class="text-gray-900">{{ $ps['avg_durasi'] }}</strong> <span class="text-muted small">Hari</span>
                                            </div>
                                            <div class="text-muted" style="font-size: 0.7rem;">
                                                Kerja: {{ $ps['avg_durasi_kerja'] ?? $ps['avg_durasi'] }} hr | Med: {{ $ps['median_durasi'] ?? $ps['avg_durasi'] }} hr
                                            </div>
                                            @if(($ps['avg_sla_pct_kalender'] ?? null) !== null)
                                                <div class="text-info font-weight-bold" style="font-size: 0.69rem;">
                                                    SLA: {{ $ps['avg_sla_pct_kalender'] }}% target
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($ps['selesai'] == 0)
                                            <span class="badge badge-light border text-muted">Belum Ada Selesai</span>
                                        @elseif($ps['on_time_rate'] >= 85 && $ps['avg_durasi'] <= 3)
                                            <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Sangat Cepat &amp; Tepat</span>
                                        @elseif($ps['on_time_rate'] >= 75)
                                            <span class="badge badge-info px-2 py-1"><i class="fas fa-thumbs-up mr-1"></i> Tepat Waktu</span>
                                        @elseif($ps['on_time_rate'] >= 50)
                                            <span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-clock mr-1"></i> Cukup Baik</span>
                                        @else
                                            <span class="badge badge-danger px-2 py-1"><i class="fas fa-exclamation-triangle mr-1"></i> Perlu Peningkatan</span>
                                        @endif
                                        @if($ps['selesai'] > 0)
                                            <div class="text-muted mt-1" style="font-size: 0.7rem;">
                                                Skor: <strong>{{ number_format($ps['score'], 1) }}</strong> | First-Pass: {{ $ps['first_pass_rate'] ?? 100 }}%
                                            </div>
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

                <!-- Keterangan Status Evaluasi Performa (Collapsible) -->
                <div class="mt-4">
                    <button class="btn btn-sm btn-light border w-100 d-flex align-items-center justify-content-between text-left font-weight-bold text-gray-800 legend-collapse-toggle"
                            type="button"
                            data-toggle="collapse"
                            data-target="#collapseKeteranganPetugas"
                            aria-expanded="false"
                            aria-controls="collapseKeteranganPetugas">
                        <span>
                            <i class="fas fa-info-circle text-primary mr-2"></i>Keterangan Status Evaluasi Performa
                        </span>
                        <span class="small text-primary font-weight-bold">
                            Tampilkan / Sembunyikan <i class="fas fa-chevron-down ml-1 legend-chevron"></i>
                        </span>
                    </button>
                    <div class="collapse mt-2" id="collapseKeteranganPetugas">
                        <div class="p-3 bg-light border rounded small">
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
                                    <strong>Catatan Audit:</strong> <em>Ketepatan waktu</em> = persentase tugas selesai yang tindak lanjutnya diserahkan sebelum/tepat pada batas waktu (durasi berstatus <strong>HOLD</strong> dikecualikan dari keterlambatan).
                                    <em>Rata-rata &amp; Median waktu</em> = durasi pengerjaan aktif dengan presisi sub-hari (min. 0,1 hari, tidak termasuk durasi <strong>HOLD</strong>); tersedia dalam mode <strong>Hari Kalender</strong> dan <strong>Hari Kerja (Senin–Jumat)</strong>.
                                    <em>Efisiensi SLA</em> = rasio durasi aktual terhadap target SLA tugas (&le; 100% berarti lebih cepat/tepat sesuai target).
                                    <em>Skor Peringkat Berimbang</em> = <code>(Tepat Waktu × 10) + (Terlambat × 4) + (On-Time % × 0,4) + (First-Pass % × 0,2)</code>.
                                    Pada tugas jenis <strong>Kolaborasi Multi-Petugas</strong>, setiap petugas yang terdelegasi dan memberikan minimal <strong>1 komentar atau lampiran</strong> pada ruang diskusi dihitung mengerjakan 1 tugas penuh. Penilaian mengikuti filter periode &amp; klasifikasi yang dipilih.
                                </div>
                            </div>
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
                                                    <span class="badge {{ $pm['jabatan'] === 'Admin' ? 'badge-primary' : ($pm['jabatan'] === 'Panitera Muda' ? 'badge-success' : 'badge-info') }} ml-1" style="font-size: 0.65rem;">
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

                <!-- Keterangan Status Evaluasi Pemberi Tugas (Collapsible) -->
                <div class="mt-4">
                    <button class="btn btn-sm btn-light border w-100 d-flex align-items-center justify-content-between text-left font-weight-bold text-gray-800 legend-collapse-toggle"
                            type="button"
                            data-toggle="collapse"
                            data-target="#collapseKeteranganPemberi"
                            aria-expanded="false"
                            aria-controls="collapseKeteranganPemberi">
                        <span>
                            <i class="fas fa-info-circle text-info mr-2"></i>Keterangan Matriks &amp; Evaluasi Kinerja Pemberi Tugas
                        </span>
                        <span class="small text-info font-weight-bold">
                            Tampilkan / Sembunyikan <i class="fas fa-chevron-down ml-1 legend-chevron"></i>
                        </span>
                    </button>
                    <div class="collapse mt-2" id="collapseKeteranganPemberi">
                        <div class="p-3 bg-light border rounded small">
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
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span><i class="fas fa-circle text-primary mr-1"></i> Menunggu Konfirmasi Admin</span>
                                <strong class="text-gray-900">{{ $tugasMenunggu }} Tugas</strong>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span><i class="fas fa-circle mr-1" style="color: #475569;"></i> HOLD (Ditunda)</span>
                                <strong class="text-gray-900">{{ $tugasHold ?? 0 }} Tugas</strong>
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
                                    <div class="mt-1">
                                        @if(($t->klasifikasi ?? 'Kesekretariatan') === 'Kepaniteraan')
                                            <span class="badge badge-success px-2 py-0" style="font-size: 0.68rem;">
                                                <i class="fas fa-balance-scale mr-1"></i>Kepaniteraan
                                            </span>
                                        @else
                                            <span class="badge badge-primary px-2 py-0" style="font-size: 0.68rem;">
                                                <i class="fas fa-building mr-1"></i>Kesekretariatan
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="align-middle">
                                    {{ $isAdmin ? ($t->petugas->name ?? '-') : ($t->admin->name ?? 'Admin') }}
                                </td>
                                <td class="text-center align-middle">
                                    {{ $t->tanggal_selesai ? $t->tanggal_selesai->format('d/m/Y') : '-' }}
                                    @if($t->status !== 'Selesai' && $t->status !== 'HOLD' && $t->tanggal_selesai && $t->tanggal_selesai->isPast() && ! $t->tanggal_selesai->isToday())
                                        <div class="mt-1">
                                            <span class="badge badge-danger" style="font-size: 0.65rem;">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>Melewati Deadline
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    @if($t->status === 'Didelegasikan')
                                        <span class="badge badge-info badge-pill px-2 py-1">Didelegasikan</span>
                                    @elseif($t->status === 'Sedang Dikerjakan')
                                        <span class="badge badge-primary badge-pill px-2 py-1">Sedang Dikerjakan</span>
                                    @elseif($t->status === 'HOLD')
                                        <span class="badge badge-secondary badge-pill px-2 py-1" style="background-color: #475569;"><i class="fas fa-pause-circle mr-1"></i>HOLD</span>
                                    @elseif($t->status === 'Menunggu Konfirmasi')
                                        <span class="badge badge-warning badge-pill px-2 py-1 text-dark">Menunggu Konfirmasi</span>
                                    @elseif($t->status === 'Selesai')
                                        <span class="badge badge-success badge-pill px-2 py-1">Selesai</span>
                                    @elseif($t->status === 'Perlu Revisi')
                                        <span class="badge badge-danger badge-pill px-2 py-1">Perlu Revisi</span>
                                    @else
                                        <span class="badge badge-secondary badge-pill px-2 py-1">{{ $t->status }}</span>
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

    <!-- Modal Daftar Tugas Mendekati Deadline (0-2 Hari) -->
    <div class="modal fade" id="modalTugasMendekatiDeadline" tabindex="-1" role="dialog" aria-labelledby="modalTugasMendekatiDeadlineLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title font-weight-bold" id="modalTugasMendekatiDeadlineLabel">
                        <i class="fas fa-hourglass-half mr-2"></i>Daftar Tugas Mendekati Deadline (0-2 Hari)
                        <span class="badge badge-light text-danger ml-2">{{ $tugasMendekatiDeadlineCount ?? 0 }} Tugas</span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap: 8px;">
                        <p class="small text-muted mb-0">
                            Menampilkan tugas aktif (belum berstatus <strong>Selesai</strong> atau <strong>HOLD</strong>) yang batas waktunya jatuh pada hari ini hingga 2 hari ke depan.
                        </p>
                        @if(!empty($klasifikasi))
                            <span class="badge {{ $klasifikasi === 'Kepaniteraan' ? 'badge-success' : 'badge-primary' }} px-2 py-1">
                                Klasifikasi: {{ $klasifikasi }}
                            </span>
                        @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0" id="tableTugasMendekatiDeadline" width="100%" cellspacing="0">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Judul Tugas</th>
                                    <th>{{ $isAdmin ? 'Petugas Pelaksana' : (($isKasubbag ?? false) ? 'Pemberi & Pelaksana' : 'Didelegasikan Oleh') }}</th>
                                    <th width="18%">Batas Selesai</th>
                                    <th width="16%">Status</th>
                                    <th width="11%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tugasMendekatiDeadline ?? [] as $td)
                                    @php
                                        $daysLeft = $td->getDaysUntilDeadline();
                                    @endphp
                                    <tr>
                                        <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                        <td class="align-middle">
                                            <div class="font-weight-bold text-gray-900">{{ $td->judul }}</div>
                                            <div class="mt-1 d-flex flex-wrap align-items-center" style="gap: 4px;">
                                                @if(($td->klasifikasi ?? 'Kesekretariatan') === 'Kepaniteraan')
                                                    <span class="badge badge-success px-2 py-0" style="font-size: 0.68rem;">
                                                        <i class="fas fa-balance-scale mr-1"></i>Kepaniteraan
                                                    </span>
                                                @else
                                                    <span class="badge badge-primary px-2 py-0" style="font-size: 0.68rem;">
                                                        <i class="fas fa-building mr-1"></i>Kesekretariatan
                                                    </span>
                                                @endif
                                                @if($td->kolaborator && $td->kolaborator->isNotEmpty())
                                                    <span class="badge badge-info px-2 py-0" style="font-size: 0.68rem;" title="Kolaborator: {{ $td->kolaborator->pluck('name')->implode(', ') }}">
                                                        <i class="fas fa-users mr-1"></i>+{{ $td->kolaborator->count() }} Kolaborator
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            @if($isAdmin)
                                                <span class="font-weight-bold text-gray-900">{{ $td->petugas->name ?? '-' }}</span>
                                                @if($td->admin && $td->admin_id !== Auth::id())
                                                    <div class="small text-info"><i class="fas fa-share mr-1"></i>Dari: {{ $td->admin->name }}</div>
                                                @endif
                                            @elseif($isKasubbag ?? false)
                                                <div class="small"><span class="text-muted">Dari:</span> <strong>{{ $td->admin->name ?? 'Admin' }}</strong></div>
                                                <div class="small"><span class="text-muted">Pelaksana:</span> <strong class="text-primary">{{ $td->petugas->name ?? '-' }}</strong></div>
                                            @else
                                                <span class="font-weight-bold text-gray-900">{{ $td->admin->name ?? 'Admin' }}</span>
                                                @if((int) $td->petugas_id !== (int) Auth::id())
                                                    <div class="mt-1">
                                                        <span class="badge badge-info" style="font-size: 0.68rem;">
                                                            <i class="fas fa-users mr-1"></i>Kolaborator (PIC: {{ $td->petugas->name ?? '-' }})
                                                        </span>
                                                    </div>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="font-weight-bold text-gray-900">
                                                {{ $td->tanggal_selesai ? $td->tanggal_selesai->format('d/m/Y') : '-' }}
                                            </div>
                                            @if($daysLeft !== null)
                                                <div class="mt-1">
                                                    @if($daysLeft <= 0)
                                                        <span class="badge badge-danger px-2 py-0" style="font-size: 0.68rem;">
                                                            <i class="fas fa-hourglass-end mr-1"></i>Deadline Hari Ini
                                                        </span>
                                                    @elseif($daysLeft === 1)
                                                        <span class="badge badge-warning text-dark px-2 py-0" style="font-size: 0.68rem;">
                                                            <i class="fas fa-hourglass-half mr-1"></i>Sisa 1 Hari
                                                        </span>
                                                    @else
                                                        <span class="badge badge-info px-2 py-0" style="font-size: 0.68rem;">
                                                            <i class="fas fa-hourglass-half mr-1"></i>Sisa {{ $daysLeft }} Hari
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            @if($td->status === 'Didelegasikan')
                                                <span class="badge badge-info badge-pill px-2 py-1">Didelegasikan</span>
                                            @elseif($td->status === 'Sedang Dikerjakan')
                                                <span class="badge badge-primary badge-pill px-2 py-1">Sedang Dikerjakan</span>
                                            @elseif($td->status === 'Menunggu Konfirmasi')
                                                <span class="badge badge-warning badge-pill px-2 py-1 text-dark">Menunggu Konfirmasi</span>
                                            @elseif($td->status === 'Perlu Revisi')
                                                <span class="badge badge-danger badge-pill px-2 py-1">Perlu Revisi</span>
                                            @else
                                                <span class="badge badge-secondary badge-pill px-2 py-1">{{ $td->status }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            <a href="{{ route('tugas.show', $td->id) }}" class="btn btn-sm btn-info" title="Lihat Rincian">
                                                <i class="fas fa-eye mr-1"></i>Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="fas fa-check-circle fa-2x text-success mb-2 d-block"></i>
                                            Tidak ada tugas yang mendekati deadline (0-2 hari) saat ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <a href="{{ route('tugas.index', array_filter(['deadline' => 'mendekati', 'klasifikasi' => $klasifikasi ?? null])) }}" class="btn btn-sm btn-outline-danger font-weight-bold">
                        <i class="fas fa-external-link-alt mr-1"></i> Buka di Halaman Data Tugas
                    </a>
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @if($isAdmin)
        <!-- Modal Drill-Down Audit Grafik Kinerja & Tren -->
        <div class="modal fade" id="modalAuditGrafik" tabindex="-1" role="dialog" aria-labelledby="modalAuditGrafikTitle" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-primary text-white">
                        <div>
                            <h5 class="modal-title font-weight-bold mb-0" id="modalAuditGrafikTitle">
                                <i class="fas fa-search-plus mr-2"></i>Audit Rincian Tugas
                            </h5>
                            <small id="modalAuditGrafikSubtitle" class="text-white-50"></small>
                        </div>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="modalAuditSummaryPills" class="d-flex flex-wrap mb-3" style="gap: 8px;"></div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm mb-0" id="tableAuditGrafik" width="100%" cellspacing="0">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="4%">No</th>
                                        <th>Judul Tugas</th>
                                        <th>Pelaksana &amp; Pendelegasi</th>
                                        <th width="13%">Mulai / Deadline</th>
                                        <th width="13%">Realisasi Selesai</th>
                                        <th width="8%">HOLD</th>
                                        <th width="13%">Durasi Aktif</th>
                                        <th width="9%">SLA (%)</th>
                                        <th width="11%">Ketepatan</th>
                                        <th width="7%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tableAuditGrafikBody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer bg-light d-flex justify-content-between">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt text-primary mr-1"></i>Data ditarik langsung dari log aktivitas dan catatan waktu tugas SIMONTER.
                        </small>
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
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
                const escapeHtml = function (str) {
                    if (str === null || str === undefined) {
                        return '';
                    }
                    return String(str)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                };

                const fmtDays = function (val) {
                    return (val !== null && val !== undefined) ? val + ' Hari' : 'Tidak ada data';
                };

                const openAuditDrilldownModal = function (title, subtitle, summaryPills, tasks) {
                    const modalEl = document.getElementById('modalAuditGrafik');
                    const titleEl = document.getElementById('modalAuditGrafikTitle');
                    const subtitleEl = document.getElementById('modalAuditGrafikSubtitle');
                    const pillsEl = document.getElementById('modalAuditSummaryPills');
                    const tbodyEl = document.getElementById('tableAuditGrafikBody');
                    if (!modalEl || !tbodyEl) {
                        return;
                    }

                    titleEl.innerHTML = '<i class="fas fa-search-plus mr-2"></i>' + escapeHtml(title);
                    subtitleEl.textContent = subtitle || '';
                    pillsEl.innerHTML = summaryPills.map(function (pill) {
                        return '<span class="badge ' + escapeHtml(pill.badgeClass) + ' px-2 py-1" style="font-size: 0.78rem;">' + escapeHtml(pill.text) + '</span>';
                    }).join('');

                    if (!tasks || tasks.length === 0) {
                        tbodyEl.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-muted">Belum ada tugas selesai pada segmen ini.</td></tr>';
                    } else {
                        tbodyEl.innerHTML = tasks.map(function (task, idx) {
                            const klasBadge = task.klasifikasi === 'Kepaniteraan'
                                ? '<span class="badge badge-success px-2 py-0" style="font-size: 0.65rem;">Kepaniteraan</span>'
                                : '<span class="badge badge-primary px-2 py-0" style="font-size: 0.65rem;">Kesekretariatan</span>';
                            const kolabBadge = task.is_kolaborasi
                                ? ' <span class="badge badge-info px-2 py-0" style="font-size: 0.65rem;">Kolaborasi</span>'
                                : '';
                            const revisiBadge = task.revisi_count > 0
                                ? ' <span class="badge badge-warning text-dark px-2 py-0" style="font-size: 0.65rem;">' + task.revisi_count + 'x Revisi</span>'
                                : '';
                            const onTimeBadge = task.tepat_waktu
                                ? '<span class="badge badge-success px-2 py-1">Tepat Waktu</span>'
                                : '<span class="badge badge-warning text-dark px-2 py-1">Terlambat</span>';
                            const slaBadgeClass = task.sla_pct_kalender <= 100 ? 'text-success' : 'text-danger';

                            return '<tr>'
                                + '<td class="text-center align-middle">' + (idx + 1) + '</td>'
                                + '<td class="align-middle"><div class="font-weight-bold text-gray-900">' + escapeHtml(task.judul) + '</div><div class="mt-1">' + klasBadge + kolabBadge + revisiBadge + '</div></td>'
                                + '<td class="align-middle small"><div><strong>PIC:</strong> ' + escapeHtml(task.petugas_name) + '</div><div class="text-muted">Oleh: ' + escapeHtml(task.admin_name) + '</div></td>'
                                + '<td class="text-center align-middle small"><div>' + escapeHtml(task.tanggal_mulai) + '</div><div class="text-muted">s/d ' + escapeHtml(task.tanggal_deadline) + '</div></td>'
                                + '<td class="text-center align-middle small font-weight-bold">' + escapeHtml(task.tanggal_selesai_aktual) + ' WIB</td>'
                                + '<td class="text-center align-middle small">' + (task.hold_days > 0 ? '<span class="badge badge-secondary">' + task.hold_days + ' hr</span>' : '<span class="text-muted">-</span>') + '</td>'
                                + '<td class="text-center align-middle small"><div><strong>' + task.durasi_kalender + ' hr</strong> (Kal)</div><div class="text-muted">' + task.durasi_kerja + ' hr (Sen-Jum)</div></td>'
                                + '<td class="text-center align-middle small"><strong class="' + slaBadgeClass + '">' + task.sla_pct_kalender + '%</strong><div class="text-muted" style="font-size:0.68rem;">Target: ' + task.target_kalender + ' hr</div></td>'
                                + '<td class="text-center align-middle">' + onTimeBadge + '</td>'
                                + '<td class="text-center align-middle"><a href="' + escapeHtml(task.url) + '" class="btn btn-xs btn-info py-1 px-2" title="Buka Tugas"><i class="fas fa-eye"></i></a></td>'
                                + '</tr>';
                        }).join('');
                    }

                    if (window.jQuery && jQuery.fn.modal) {
                        jQuery(modalEl).modal('show');
                    }
                };

                // 1. ADMIN COMBO CHART: Komparasi Kinerja Petugas (Dual Y-Axis Stacked Bar + Mean/Median Lines)
                const ctxPetugas = document.getElementById('chartPetugasPerformance');
                const petugasChartDatasets = @json($petugasChartDatasets ?? []);
                let currentPetugasScope = 'top_aktif';
                let currentPetugasDayMode = 'kalender';
                let chartPetugasInstance = null;

                const getPetugasActivePayload = function () {
                    const fallbackPayload = {
                        labels: @json($chartPetugasLabels),
                        selesai: @json($chartPetugasSelesai),
                        tepat_waktu: @json($chartPetugasSelesai),
                        terlambat: [],
                        on_time_rate: [],
                        first_pass_rate: [],
                        revisi_total: [],
                        mandiri_selesai: [],
                        kolaborasi_selesai: [],
                        score: [],
                        durasi_kalender: @json($chartPetugasAvgDurasi),
                        durasi_kerja: @json($chartPetugasAvgDurasi),
                        median_kalender: [],
                        median_kerja: [],
                        target_kalender: [],
                        target_kerja: [],
                        sla_kalender: [],
                        sla_kerja: [],
                        tasks: []
                    };
                    return (petugasChartDatasets && petugasChartDatasets[currentPetugasScope])
                        ? petugasChartDatasets[currentPetugasScope]
                        : fallbackPayload;
                };

                if (ctxPetugas) {
                    const initPetugasData = getPetugasActivePayload();

                    chartPetugasInstance = new Chart(ctxPetugas, {
                        type: 'bar',
                        data: {
                            labels: initPetugasData.labels,
                            datasets: [
                                {
                                    type: 'line',
                                    label: 'Rata-rata Durasi (Hari)',
                                    yAxisID: 'y-days',
                                    order: 1,
                                    fill: false,
                                    lineTension: 0.25,
                                    borderColor: '#4e73df',
                                    backgroundColor: '#4e73df',
                                    pointBackgroundColor: '#4e73df',
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                    borderWidth: 2.5,
                                    spanGaps: false,
                                    data: initPetugasData.durasi_kalender,
                                },
                                {
                                    type: 'line',
                                    label: 'Median Durasi (Hari)',
                                    yAxisID: 'y-days',
                                    order: 1,
                                    fill: false,
                                    lineTension: 0.25,
                                    borderColor: '#0ea5e9',
                                    backgroundColor: '#0ea5e9',
                                    pointBackgroundColor: '#0ea5e9',
                                    pointRadius: 3,
                                    pointHoverRadius: 5,
                                    borderWidth: 2,
                                    borderDash: [5, 5],
                                    spanGaps: false,
                                    data: initPetugasData.median_kalender,
                                },
                                {
                                    type: 'bar',
                                    label: 'Selesai Tepat Waktu (Unit)',
                                    yAxisID: 'y-count',
                                    order: 2,
                                    backgroundColor: '#1cc88a',
                                    hoverBackgroundColor: '#17a673',
                                    borderColor: '#1cc88a',
                                    data: initPetugasData.tepat_waktu,
                                    maxBarThickness: 36,
                                },
                                {
                                    type: 'bar',
                                    label: 'Selesai Terlambat (Unit)',
                                    yAxisID: 'y-count',
                                    order: 2,
                                    backgroundColor: '#f6c23e',
                                    hoverBackgroundColor: '#dda20a',
                                    borderColor: '#f6c23e',
                                    data: initPetugasData.terlambat,
                                    maxBarThickness: 36,
                                }
                            ]
                        },
                        options: {
                            maintainAspectRatio: false,
                            layout: { padding: { left: 10, right: 10, top: 15, bottom: 0 } },
                            scales: {
                                xAxes: [{
                                    stacked: true,
                                    gridLines: { display: false, drawBorder: false },
                                    ticks: { maxTicksLimit: 20 }
                                }],
                                yAxes: [
                                    {
                                        id: 'y-count',
                                        position: 'left',
                                        stacked: true,
                                        ticks: {
                                            beginAtZero: true,
                                            precision: 0,
                                            maxTicksLimit: 6,
                                            padding: 8
                                        },
                                        scaleLabel: {
                                            display: true,
                                            labelString: 'Jumlah Tugas (Unit)'
                                        },
                                        gridLines: { color: "rgb(234, 236, 244)", zeroLineColor: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] }
                                    },
                                    {
                                        id: 'y-days',
                                        position: 'right',
                                        stacked: false,
                                        ticks: {
                                            beginAtZero: true,
                                            maxTicksLimit: 6,
                                            padding: 8,
                                            callback: function(value) { return Number.isInteger(value) ? value : value.toFixed(1); }
                                        },
                                        scaleLabel: {
                                            display: true,
                                            labelString: 'Durasi (Hari)'
                                        },
                                        gridLines: { drawOnChartArea: false, drawBorder: false }
                                    }
                                ]
                            },
                            legend: { display: false },
                            tooltips: {
                                backgroundColor: "rgb(255,255,255)",
                                bodyFontColor: "#5a5c69",
                                titleMarginBottom: 8,
                                titleFontColor: '#2e3a4d',
                                titleFontSize: 13,
                                borderColor: '#dddfeb',
                                borderWidth: 1,
                                xPadding: 14,
                                yPadding: 12,
                                displayColors: true,
                                intersect: false,
                                mode: 'index',
                                caretPadding: 10,
                                callbacks: {
                                    label: function (tooltipItem, data) {
                                        const dsLabel = data.datasets[tooltipItem.datasetIndex].label || '';
                                        const val = tooltipItem.yLabel;
                                        if (dsLabel.indexOf('Durasi') !== -1) {
                                            return dsLabel + ': ' + ((val !== null && val !== undefined && !isNaN(val)) ? val + ' Hari' : 'Tidak ada data');
                                        }
                                        return dsLabel + ': ' + (val || 0) + ' Tugas';
                                    },
                                    afterBody: function (tooltipItems) {
                                        if (!tooltipItems || tooltipItems.length === 0) {
                                            return [];
                                        }
                                        const idx = tooltipItems[0].index;
                                        const p = getPetugasActivePayload();
                                        const total = p.selesai[idx] || 0;
                                        const onTimeRate = p.on_time_rate[idx] ?? 0;
                                        const firstPass = p.first_pass_rate[idx] ?? 0;
                                        const mandiri = p.mandiri_selesai[idx] ?? 0;
                                        const kolab = p.kolaborasi_selesai[idx] ?? 0;
                                        const score = p.score[idx] ?? 0;
                                        const isKerja = currentPetugasDayMode === 'kerja';
                                        const targetVal = isKerja ? p.target_kerja[idx] : p.target_kalender[idx];
                                        const slaVal = isKerja ? p.sla_kerja[idx] : p.sla_kalender[idx];

                                        const lines = [
                                            'Total Selesai: ' + total + ' Tugas (On-Time: ' + onTimeRate + '%)',
                                            'Komposisi: ' + mandiri + ' Mandiri, ' + kolab + ' Kolaborasi',
                                            'First-Pass (Tanpa Revisi): ' + firstPass + '% | Skor: ' + score
                                        ];
                                        if (targetVal !== null && targetVal !== undefined) {
                                            lines.push('Target SLA: ' + targetVal + ' Hari (Efisiensi: ' + slaVal + '%)');
                                        }
                                        lines.push('Klik untuk audit rincian tugas');
                                        return lines;
                                    }
                                }
                            },
                            onClick: function (evt) {
                                const points = chartPetugasInstance.getElementsAtEventForMode(evt, 'index', { intersect: false }, true);
                                if (!points || points.length === 0) {
                                    return;
                                }
                                const idx = points[0]._index;
                                const p = getPetugasActivePayload();
                                const officerName = p.labels[idx] || 'Petugas';
                                const isKerja = currentPetugasDayMode === 'kerja';
                                const avgDur = isKerja ? p.durasi_kerja[idx] : p.durasi_kalender[idx];
                                const medDur = isKerja ? p.median_kerja[idx] : p.median_kalender[idx];
                                const slaVal = isKerja ? p.sla_kerja[idx] : p.sla_kalender[idx];

                                openAuditDrilldownModal(
                                    'Audit Kinerja Petugas: ' + officerName,
                                    'Mode Perhitungan Aktif: ' + (isKerja ? 'Hari Kerja (Senin-Jumat)' : 'Hari Kalender'),
                                    [
                                        { badgeClass: 'badge-primary', text: 'Selesai: ' + (p.selesai[idx] || 0) + ' Tugas' },
                                        { badgeClass: 'badge-success', text: 'Tepat Waktu: ' + (p.tepat_waktu[idx] || 0) + ' (' + (p.on_time_rate[idx] || 0) + '%)' },
                                        { badgeClass: 'badge-warning text-dark', text: 'Terlambat: ' + (p.terlambat[idx] || 0) },
                                        { badgeClass: 'badge-info', text: 'Rata-rata: ' + fmtDays(avgDur) + ' | Median: ' + fmtDays(medDur) },
                                        { badgeClass: 'badge-secondary', text: 'Efisiensi SLA: ' + (slaVal !== null && slaVal !== undefined ? slaVal + '%' : '-') },
                                        { badgeClass: 'badge-dark', text: 'Skor Audit: ' + (p.score[idx] ?? 0) }
                                    ],
                                    p.tasks[idx] || []
                                );
                            }
                        }
                    });

                    const updatePetugasChart = function () {
                        const p = getPetugasActivePayload();
                        const isKerja = currentPetugasDayMode === 'kerja';
                        chartPetugasInstance.data.labels = p.labels;
                        chartPetugasInstance.data.datasets[0].label = isKerja ? 'Rata-rata Durasi (Hari Kerja)' : 'Rata-rata Durasi (Hari Kalender)';
                        chartPetugasInstance.data.datasets[0].data = isKerja ? p.durasi_kerja : p.durasi_kalender;
                        chartPetugasInstance.data.datasets[1].label = isKerja ? 'Median Durasi (Hari Kerja)' : 'Median Durasi (Hari Kalender)';
                        chartPetugasInstance.data.datasets[1].data = isKerja ? p.median_kerja : p.median_kalender;
                        chartPetugasInstance.data.datasets[2].data = p.tepat_waktu;
                        chartPetugasInstance.data.datasets[3].data = p.terlambat;
                        chartPetugasInstance.update();
                    };

                    const scopeButtons = document.querySelectorAll('#petugasScopeBtnGroup button[data-scope]');
                    scopeButtons.forEach(function (button) {
                        button.addEventListener('click', function () {
                            currentPetugasScope = this.getAttribute('data-scope') || 'top_aktif';
                            scopeButtons.forEach(function (b) { b.classList.remove('active'); });
                            this.classList.add('active');
                            updatePetugasChart();
                        });
                    });

                    const petugasDayButtons = document.querySelectorAll('#petugasDayModeBtnGroup button[data-day-mode]');
                    petugasDayButtons.forEach(function (button) {
                        button.addEventListener('click', function () {
                            currentPetugasDayMode = this.getAttribute('data-day-mode') || 'kalender';
                            petugasDayButtons.forEach(function (b) { b.classList.remove('active'); });
                            this.classList.add('active');
                            updatePetugasChart();
                        });
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
                                backgroundColor: ['#1cc88a', '#f6c23e', '#36b9cc', '#4e73df', '#e74a3b', '#475569'],
                                hoverBackgroundColor: ['#17a673', '#dda20a', '#2c9faf', '#2e59d9', '#be2617', '#334155'],
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

                // 3. ADMIN COMBO CHART: Tren Penyelesaian Tugas & Rata-rata Waktu (Dual Y-Axis)
                const ctxTrend = document.getElementById('chartMonthlyTrend');
                const trendDatasets = @json($trendDatasets ?? []);
                let currentTrendPeriod = '6_bulan';
                let currentTrendDayMode = 'kalender';
                let chartMonthlyTrendInstance = null;

                const getTrendActivePayload = function () {
                    return (trendDatasets && trendDatasets[currentTrendPeriod]) ? trendDatasets[currentTrendPeriod] : {
                        labels: @json($trendLabels ?? []),
                        selesai: @json($trendSelesaiData ?? []),
                        tepat_waktu: @json($trendSelesaiData ?? []),
                        terlambat: [],
                        on_time_rate: [],
                        durasi: @json($trendAvgDurasiData ?? []),
                        durasi_kerja: @json($trendAvgDurasiData ?? []),
                        median_durasi: [],
                        median_durasi_kerja: [],
                        target_durasi: [],
                        target_durasi_kerja: [],
                        sla_efisiensi: [],
                        sla_efisiensi_kerja: [],
                        tasks: [],
                        subtitle: '6 Bulan Terakhir (Berdasarkan Realisasi Selesai)'
                    };
                };

                if (ctxTrend) {
                    const initialData = getTrendActivePayload();

                    chartMonthlyTrendInstance = new Chart(ctxTrend, {
                        type: 'bar',
                        data: {
                            labels: initialData.labels,
                            datasets: [
                                {
                                    type: 'line',
                                    label: 'Rata-rata Durasi (Hari)',
                                    yAxisID: 'y-days',
                                    order: 1,
                                    lineTension: 0.25,
                                    fill: false,
                                    backgroundColor: '#4e73df',
                                    borderColor: '#4e73df',
                                    pointRadius: 4,
                                    pointBackgroundColor: '#4e73df',
                                    pointBorderColor: '#4e73df',
                                    pointHoverRadius: 6,
                                    pointHitRadius: 10,
                                    borderWidth: 2.5,
                                    spanGaps: false,
                                    data: initialData.durasi,
                                },
                                {
                                    type: 'line',
                                    label: 'Median Durasi (Hari)',
                                    yAxisID: 'y-days',
                                    order: 1,
                                    lineTension: 0.25,
                                    fill: false,
                                    backgroundColor: '#0ea5e9',
                                    borderColor: '#0ea5e9',
                                    pointRadius: 3,
                                    pointBackgroundColor: '#0ea5e9',
                                    pointHoverRadius: 5,
                                    borderWidth: 2,
                                    borderDash: [5, 5],
                                    spanGaps: false,
                                    data: initialData.median_durasi,
                                },
                                {
                                    type: 'line',
                                    label: 'Rata-rata Target SLA (Hari)',
                                    yAxisID: 'y-days',
                                    order: 1,
                                    lineTension: 0.25,
                                    fill: false,
                                    backgroundColor: '#94a3b8',
                                    borderColor: '#94a3b8',
                                    pointRadius: 3,
                                    pointBackgroundColor: '#94a3b8',
                                    pointHoverRadius: 5,
                                    borderWidth: 2,
                                    borderDash: [2, 3],
                                    spanGaps: false,
                                    data: initialData.target_durasi,
                                },
                                {
                                    type: 'bar',
                                    label: 'Selesai Tepat Waktu (Unit)',
                                    yAxisID: 'y-count',
                                    order: 2,
                                    backgroundColor: '#1cc88a',
                                    hoverBackgroundColor: '#17a673',
                                    borderColor: '#1cc88a',
                                    data: initialData.tepat_waktu,
                                    maxBarThickness: 32,
                                },
                                {
                                    type: 'bar',
                                    label: 'Selesai Terlambat (Unit)',
                                    yAxisID: 'y-count',
                                    order: 2,
                                    backgroundColor: '#f6c23e',
                                    hoverBackgroundColor: '#dda20a',
                                    borderColor: '#f6c23e',
                                    data: initialData.terlambat,
                                    maxBarThickness: 32,
                                }
                            ],
                        },
                        options: {
                            maintainAspectRatio: false,
                            layout: { padding: { left: 10, right: 10, top: 15, bottom: 0 } },
                            scales: {
                                xAxes: [{
                                    stacked: true,
                                    gridLines: { display: false, drawBorder: false },
                                    ticks: { maxTicksLimit: 12 }
                                }],
                                yAxes: [
                                    {
                                        id: 'y-count',
                                        position: 'left',
                                        stacked: true,
                                        ticks: {
                                            beginAtZero: true,
                                            precision: 0,
                                            maxTicksLimit: 6,
                                            padding: 8
                                        },
                                        scaleLabel: {
                                            display: true,
                                            labelString: 'Jumlah Tugas (Unit)'
                                        },
                                        gridLines: { color: "rgb(234, 236, 244)", zeroLineColor: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] }
                                    },
                                    {
                                        id: 'y-days',
                                        position: 'right',
                                        stacked: false,
                                        ticks: {
                                            beginAtZero: true,
                                            maxTicksLimit: 6,
                                            padding: 8,
                                            callback: function(value) { return Number.isInteger(value) ? value : value.toFixed(1); }
                                        },
                                        scaleLabel: {
                                            display: true,
                                            labelString: 'Durasi (Hari)'
                                        },
                                        gridLines: { drawOnChartArea: false, drawBorder: false }
                                    }
                                ],
                            },
                            legend: { display: false },
                            tooltips: {
                                backgroundColor: "rgb(255,255,255)",
                                bodyFontColor: "#5a5c69",
                                titleMarginBottom: 8,
                                titleFontColor: '#2e3a4d',
                                titleFontSize: 13,
                                borderColor: '#dddfeb',
                                borderWidth: 1,
                                xPadding: 14,
                                yPadding: 12,
                                displayColors: true,
                                intersect: false,
                                mode: 'index',
                                caretPadding: 10,
                                callbacks: {
                                    label: function (tooltipItem, data) {
                                        const dsLabel = data.datasets[tooltipItem.datasetIndex].label || '';
                                        const val = tooltipItem.yLabel;
                                        if (dsLabel.indexOf('Durasi') !== -1 || dsLabel.indexOf('Target') !== -1) {
                                            return dsLabel + ': ' + ((val !== null && val !== undefined && !isNaN(val)) ? val + ' Hari' : 'Tidak ada data');
                                        }
                                        return dsLabel + ': ' + (val || 0) + ' Tugas';
                                    },
                                    afterBody: function (tooltipItems) {
                                        if (!tooltipItems || tooltipItems.length === 0) {
                                            return [];
                                        }
                                        const idx = tooltipItems[0].index;
                                        const t = getTrendActivePayload();
                                        const total = t.selesai[idx] || 0;
                                        const onTime = t.on_time_rate[idx];
                                        const isKerja = currentTrendDayMode === 'kerja';
                                        const slaVal = isKerja ? t.sla_efisiensi_kerja[idx] : t.sla_efisiensi[idx];
                                        if (total === 0) {
                                            return ['Belum ada tugas selesai pada periode ini'];
                                        }
                                        const lines = [
                                            'Total Selesai (N): ' + total + ' Tugas (On-Time: ' + (onTime ?? 0) + '%)',
                                            'Efisiensi SLA Aktual/Target: ' + (slaVal !== null && slaVal !== undefined ? slaVal + '%' : '-'),
                                            'Klik untuk audit rincian tugas periode ini'
                                        ];
                                        return lines;
                                    }
                                }
                            },
                            onClick: function (evt) {
                                const points = chartMonthlyTrendInstance.getElementsAtEventForMode(evt, 'index', { intersect: false }, true);
                                if (!points || points.length === 0) {
                                    return;
                                }
                                const idx = points[0]._index;
                                const t = getTrendActivePayload();
                                const bucketLabel = t.labels[idx] || 'Periode';
                                const isKerja = currentTrendDayMode === 'kerja';
                                const avgDur = isKerja ? t.durasi_kerja[idx] : t.durasi[idx];
                                const medDur = isKerja ? t.median_durasi_kerja[idx] : t.median_durasi[idx];
                                const slaVal = isKerja ? t.sla_efisiensi_kerja[idx] : t.sla_efisiensi[idx];

                                openAuditDrilldownModal(
                                    'Audit Penyelesaian Periode: ' + bucketLabel,
                                    'Dikelompokkan berdasarkan tanggal realisasi selesai (' + (isKerja ? 'Hari Kerja Senin-Jumat' : 'Hari Kalender') + ')',
                                    [
                                        { badgeClass: 'badge-primary', text: 'Selesai (N): ' + (t.selesai[idx] || 0) + ' Tugas' },
                                        { badgeClass: 'badge-success', text: 'Tepat Waktu: ' + (t.tepat_waktu[idx] || 0) + ((t.on_time_rate[idx] !== null && t.on_time_rate[idx] !== undefined) ? ' (' + t.on_time_rate[idx] + '%)' : '') },
                                        { badgeClass: 'badge-warning text-dark', text: 'Terlambat: ' + (t.terlambat[idx] || 0) },
                                        { badgeClass: 'badge-info', text: 'Rata-rata: ' + fmtDays(avgDur) + ' | Median: ' + fmtDays(medDur) },
                                        { badgeClass: 'badge-secondary', text: 'Efisiensi SLA: ' + (slaVal !== null && slaVal !== undefined ? slaVal + '%' : '-') }
                                    ],
                                    t.tasks[idx] || []
                                );
                            }
                        }
                    });

                    const periodSubtitle = document.getElementById('trendPeriodSubtitle');
                    const updateTrendChart = function () {
                        const targetData = getTrendActivePayload();
                        const isKerja = currentTrendDayMode === 'kerja';

                        if (periodSubtitle && targetData.subtitle) {
                            periodSubtitle.innerHTML = '<i class="fas fa-calendar-alt text-gray-400 mr-1"></i> Periode: ' + escapeHtml(targetData.subtitle);
                        }

                        chartMonthlyTrendInstance.data.labels = targetData.labels;
                        chartMonthlyTrendInstance.data.datasets[0].label = isKerja ? 'Rata-rata Durasi (Hari Kerja)' : 'Rata-rata Durasi (Hari)';
                        chartMonthlyTrendInstance.data.datasets[0].data = isKerja ? targetData.durasi_kerja : targetData.durasi;
                        chartMonthlyTrendInstance.data.datasets[1].label = isKerja ? 'Median Durasi (Hari Kerja)' : 'Median Durasi (Hari)';
                        chartMonthlyTrendInstance.data.datasets[1].data = isKerja ? targetData.median_durasi_kerja : targetData.median_durasi;
                        chartMonthlyTrendInstance.data.datasets[2].label = isKerja ? 'Rata-rata Target SLA (Hari Kerja)' : 'Rata-rata Target SLA (Hari)';
                        chartMonthlyTrendInstance.data.datasets[2].data = isKerja ? targetData.target_durasi_kerja : targetData.target_durasi;
                        chartMonthlyTrendInstance.data.datasets[3].data = targetData.tepat_waktu;
                        chartMonthlyTrendInstance.data.datasets[4].data = targetData.terlambat;
                        chartMonthlyTrendInstance.update();
                    };

                    // Event listeners untuk tombol pilihan periode tren (1 Tahun, 6 Bulan, 3 Bulan, 1 Bulan)
                    const periodButtons = document.querySelectorAll('#trendPeriodBtnGroup button[data-period]');
                    periodButtons.forEach(function (button) {
                        button.addEventListener('click', function () {
                            const selectedPeriod = this.getAttribute('data-period');
                            if (!trendDatasets || !trendDatasets[selectedPeriod]) {
                                return;
                            }
                            currentTrendPeriod = selectedPeriod;
                            periodButtons.forEach(function (b) { b.classList.remove('active'); });
                            this.classList.add('active');
                            updateTrendChart();
                        });
                    });

                    const trendDayButtons = document.querySelectorAll('#trendDayModeBtnGroup button[data-day-mode]');
                    trendDayButtons.forEach(function (button) {
                        button.addEventListener('click', function () {
                            currentTrendDayMode = this.getAttribute('data-day-mode') || 'kalender';
                            trendDayButtons.forEach(function (b) { b.classList.remove('active'); });
                            this.classList.add('active');
                            updateTrendChart();
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
                                backgroundColor: ['#1cc88a', '#f6c23e', '#36b9cc', '#4e73df', '#e74a3b', '#475569'],
                                hoverBackgroundColor: ['#17a673', '#dda20a', '#2c9faf', '#2e59d9', '#be2617', '#334155'],
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