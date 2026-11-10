@extends('layouts.app')

@section('content')
<style>
    /* ============================================================
       ARTISTIC & MODERN STYLING - PETUNJUK APLIKASI SIMONTER v1.0
       ============================================================ */
    .guide-hero {
        position: relative;
        background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 52%, #2563eb 100%);
        border-radius: 1.25rem;
        padding: 2.75rem 2.5rem;
        color: #ffffff;
        overflow: hidden;
        box-shadow: 0 20px 45px rgba(30, 58, 138, 0.25);
        margin-bottom: 1.75rem;
    }
    .guide-hero::before {
        content: '';
        position: absolute;
        top: -80px;
        right: -80px;
        width: 320px;
        height: 320px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(96, 165, 250, 0.28) 0%, rgba(37, 99, 235, 0) 70%);
        pointer-events: none;
    }
    .guide-hero::after {
        content: '';
        position: absolute;
        bottom: -90px;
        left: 25%;
        width: 260px;
        height: 260px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.2) 0%, rgba(16, 185, 129, 0) 70%);
        pointer-events: none;
    }
    .guide-hero-badge {
        display: inline-flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.14);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 50rem;
        padding: 0.4rem 1rem;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        color: #e0f2fe;
        margin-bottom: 1rem;
    }
    .guide-stat-pill {
        background: rgba(255, 255, 255, 0.11);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 0.85rem;
        padding: 0.8rem 1rem;
        backdrop-filter: blur(6px);
        transition: transform 0.2s ease, background 0.2s ease;
    }
    .guide-stat-pill:hover {
        transform: translateY(-3px);
        background: rgba(255, 255, 255, 0.18);
    }

    /* Sticky Quick Navigation Bar */
    .guide-nav-wrapper {
        position: sticky;
        top: 10px;
        z-index: 1010;
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(12px);
        border: 1px solid #e2e8f0;
        border-radius: 0.9rem;
        padding: 0.65rem 1rem;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.07);
        margin-bottom: 1.75rem;
    }
    .guide-nav-pills {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        gap: 0.5rem;
        scrollbar-width: none;
    }
    .guide-nav-pills::-webkit-scrollbar {
        display: none;
    }
    .guide-nav-link {
        white-space: nowrap;
        padding: 0.48rem 0.95rem;
        border-radius: 0.6rem;
        font-size: 0.8rem;
        font-weight: 700;
        color: #475569;
        text-decoration: none !important;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        border: 1px solid transparent;
    }
    .guide-nav-link:hover {
        background: #f1f5f9;
        color: #1e40af;
    }
    .guide-nav-link.active {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    /* Section Headers */
    .guide-section {
        scroll-margin-top: 85px;
        margin-bottom: 2.5rem;
    }
    .guide-section-badge {
        display: inline-block;
        padding: 0.28rem 0.75rem;
        border-radius: 50rem;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        margin-bottom: 0.4rem;
    }

    /* Role Cards */
    .role-card {
        border: 1px solid #e2e8f0;
        border-radius: 1.1rem;
        overflow: hidden;
        background: #ffffff;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        height: 100%;
        position: relative;
    }
    .role-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 38px rgba(15, 23, 42, 0.1) !important;
    }
    .role-card-active-user {
        border: 2px solid #2563eb;
        box-shadow: 0 12px 30px rgba(37, 99, 235, 0.16) !important;
    }
    .role-header-admin {
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
        color: #fff;
        padding: 1.5rem;
    }
    .role-header-kasubbag {
        background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);
        color: #fff;
        padding: 1.5rem;
    }
    .role-header-petugas {
        background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%);
        color: #fff;
        padding: 1.5rem;
    }
    .role-avatar-icon {
        width: 56px;
        height: 56px;
        border-radius: 1rem;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(4px);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #ffffff;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
    }
    .duty-list {
        list-style: none;
        padding-left: 0;
        margin-bottom: 0;
    }
    .duty-list li {
        position: relative;
        padding-left: 1.75rem;
        margin-bottom: 0.68rem;
        font-size: 0.85rem;
        color: #334155;
        line-height: 1.45;
    }
    .duty-list li i {
        position: absolute;
        left: 0;
        top: 3px;
        font-size: 0.9rem;
    }

    /* Visual Workflow Diagram */
    .workflow-step-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.35rem 1.2rem;
        position: relative;
        height: 100%;
        transition: all 0.22s ease;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
    }
    .workflow-step-card:hover {
        border-color: #93c5fd;
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(37, 99, 235, 0.1);
    }
    .workflow-number {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.9rem;
        color: #fff;
        margin-bottom: 0.85rem;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    /* Interactive SOP Role Tabs */
    .sop-tab-btn {
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #334155;
        border-radius: 0.75rem !important;
        padding: 0.75rem 1.25rem;
        font-weight: 700;
        font-size: 0.86rem;
        transition: all 0.2s ease;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }
    .sop-tab-btn.active {
        background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%) !important;
        color: #ffffff !important;
        border-color: transparent !important;
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.25);
    }
    .sop-timeline {
        position: relative;
        padding-left: 2.25rem;
    }
    .sop-timeline::before {
        content: '';
        position: absolute;
        left: 14px;
        top: 8px;
        bottom: 8px;
        width: 3px;
        background: linear-gradient(180deg, #2563eb 0%, #10b981 100%);
        border-radius: 3px;
    }
    .sop-timeline-item {
        position: relative;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.85rem;
        padding: 1.1rem 1.25rem;
        margin-bottom: 1rem;
        transition: background 0.2s ease;
    }
    .sop-timeline-item:hover {
        background: #ffffff;
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
    }
    .sop-timeline-dot {
        position: absolute;
        left: -2.25rem;
        top: 1.15rem;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #2563eb;
        color: #ffffff;
        font-weight: 800;
        font-size: 0.78rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid #ffffff;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.35);
    }

    /* Feature Showcase Cards */
    .feature-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.4rem;
        height: 100%;
        transition: all 0.2s ease;
    }
    .feature-box:hover {
        border-color: #60a5fa;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
    }
    .feature-icon-wrap {
        width: 48px;
        height: 48px;
        border-radius: 0.85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 1rem;
    }

    /* Formula & Predikat Box */
    .formula-box {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #f8fafc;
        border-radius: 1rem;
        padding: 1.5rem;
        border: 1px solid #334155;
    }
    .formula-code {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.16);
        border-radius: 0.65rem;
        padding: 0.85rem 1.1rem;
        font-family: 'Consolas', 'Monaco', monospace;
        font-size: 0.9rem;
        color: #38bdf8;
    }

    /* Search Highlight */
    .guide-search-input {
        border-radius: 50rem;
        padding-left: 2.5rem;
        border: 1px solid #cbd5e1;
        font-size: 0.85rem;
    }
    .guide-search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
    }

    @media print {
        .guide-nav-wrapper, #accordionSidebar, .topbar, .sticky-footer, .btn-print-hide {
            display: none !important;
        }
        .guide-hero {
            background: #1e3a8a !important;
            color: #000 !important;
        }
    }
</style>

<!-- ============================================================
     1. HERO BANNER ARTISTIK & IDENTITAS SIMONTER
     ============================================================ -->
<div class="guide-hero">
    <div class="row align-items-center">
        <div class="col-lg-7 mb-4 mb-lg-0">
            <div class="guide-hero-badge">
                <i class="fas fa-book-reader mr-2 text-warning"></i> Dokumentasi Resmi &amp; Buku Pintar Pengguna • SIMONTER v1.0
            </div>
            <h1 class="h2 font-weight-bold mb-2" style="letter-spacing: -0.4px; line-height: 1.25;">
                Pusat Petunjuk Penggunaan &amp; Tata Kelola Aplikasi <span style="color: #93c5fd;">SIMONTER</span>
            </h1>
            <p class="mb-4 text-light" style="opacity: 0.9; font-size: 0.95rem; line-height: 1.6;">
                <strong>Sistem Monitoring Tugas dan Kinerja (SIMONTER)</strong> Pengadilan Agama Jakarta Utara merupakan platform digital terpadu untuk mengelola pendelegasian tugas berjenjang, pelaporan bukti pengerjaan (<em>evidence</em>), verifikasi hasil kerja berlapis, pencatatan <em>audit trail</em> waktu nyata (<code>WIB / UTC+7</code>), serta evaluasi dan pemeringkatan kinerja aparatur secara objektif dan transparan.
            </p>

            <div class="d-flex flex-wrap align-items-center btn-print-hide" style="gap: 10px;">
                <a href="#section-sop" class="btn btn-light text-primary font-weight-bold px-4 py-2 shadow-sm" style="border-radius: 0.65rem;">
                    <i class="fas fa-play-circle mr-1"></i> Mulai Panduan Peran Anda ({{ $user->jabatan }})
                </a>
                <a href="#section-alur" class="btn btn-outline-light font-weight-bold px-3 py-2" style="border-radius: 0.65rem;">
                    <i class="fas fa-project-diagram mr-1"></i> Ilustrasi Alur Kerja
                </a>
                <button type="button" onclick="window.print()" class="btn btn-outline-light font-weight-bold px-3 py-2" style="border-radius: 0.65rem;" title="Cetak Panduan ke PDF / Kertas">
                    <i class="fas fa-print mr-1"></i> Cetak Panduan
                </button>
            </div>
        </div>

        <!-- Ilustrasi SVG Artistik & Statistik Pilar Sistem -->
        <div class="col-lg-5">
            <div class="p-3 rounded-lg" style="background: rgba(15, 23, 42, 0.35); border: 1px solid rgba(255,255,255,0.16); border-radius: 1rem;">
                <!-- Custom SVG Isometric Architecture Illustration -->
                <div class="text-center mb-3">
                    <svg viewBox="0 0 420 155" width="100%" height="145" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Ilustrasi Ekosistem SIMONTER">
                        <defs>
                            <linearGradient id="gradAdmin" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#60a5fa" />
                                <stop offset="100%" stop-color="#2563eb" />
                            </linearGradient>
                            <linearGradient id="gradKasub" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#34d399" />
                                <stop offset="100%" stop-color="#059669" />
                            </linearGradient>
                            <linearGradient id="gradPetugas" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#38bdf8" />
                                <stop offset="100%" stop-color="#0284c7" />
                            </linearGradient>
                        </defs>

                        <!-- Connecting Flow Lines -->
                        <path d="M 95 75 Q 152 30 210 75" fill="none" stroke="#93c5fd" stroke-width="2.5" stroke-dasharray="5,4"/>
                        <path d="M 210 75 Q 268 30 325 75" fill="none" stroke="#6ee7b7" stroke-width="2.5" stroke-dasharray="5,4"/>
                        <path d="M 325 95 Q 210 145 95 95" fill="none" stroke="#fde047" stroke-width="2" stroke-dasharray="4,4"/>

                        <!-- Node 1: Admin -->
                        <g transform="translate(35, 38)">
                            <rect x="0" y="0" width="90" height="72" rx="14" fill="url(#gradAdmin)" stroke="#bfdbfe" stroke-width="1.5"/>
                            <circle cx="45" cy="24" r="12" fill="rgba(255,255,255,0.22)"/>
                            <text x="45" y="28" text-anchor="middle" fill="#ffffff" font-size="11" font-weight="bold">👑</text>
                            <text x="45" y="49" text-anchor="middle" fill="#ffffff" font-size="10" font-weight="bold">ADMIN</text>
                            <text x="45" y="61" text-anchor="middle" fill="#dbeafe" font-size="7.5">Delegasi &amp; Evaluasi</text>
                        </g>

                        <!-- Node 2: Kasubbag & Panitera Muda -->
                        <g transform="translate(165, 38)">
                            <rect x="0" y="0" width="90" height="72" rx="14" fill="url(#gradKasub)" stroke="#a7f3d0" stroke-width="1.5"/>
                            <circle cx="45" cy="24" r="12" fill="rgba(255,255,255,0.22)"/>
                            <text x="45" cy="28" y="28" text-anchor="middle" fill="#ffffff" font-size="11" font-weight="bold">⚡</text>
                            <text x="45" y="48" text-anchor="middle" fill="#ffffff" font-size="8.2" font-weight="bold">KASUBBAG / PANMUD</text>
                            <text x="45" y="61" text-anchor="middle" fill="#d1fae5" font-size="7">Sekretariat &amp; Kepaniteraan</text>
                        </g>

                        <!-- Node 3: Petugas -->
                        <g transform="translate(295, 38)">
                            <rect x="0" y="0" width="90" height="72" rx="14" fill="url(#gradPetugas)" stroke="#bae6fd" stroke-width="1.5"/>
                            <circle cx="45" cy="24" r="12" fill="rgba(255,255,255,0.22)"/>
                            <text x="45" y="28" text-anchor="middle" fill="#ffffff" font-size="11" font-weight="bold">🚀</text>
                            <text x="45" y="49" text-anchor="middle" fill="#ffffff" font-size="10" font-weight="bold">PETUGAS</text>
                            <text x="45" y="61" text-anchor="middle" fill="#e0f2fe" font-size="7.5">Eksekusi &amp; Evidence</text>
                        </g>

                        <!-- Labels on Arcs -->
                        <rect x="118" y="14" width="68" height="16" rx="8" fill="#1e40af" stroke="#60a5fa" stroke-width="1"/>
                        <text x="152" y="25" text-anchor="middle" fill="#ffffff" font-size="7.5" font-weight="bold">Disposisi L1</text>

                        <rect x="234" y="14" width="68" height="16" rx="8" fill="#065f46" stroke="#34d399" stroke-width="1"/>
                        <text x="268" y="25" text-anchor="middle" fill="#ffffff" font-size="7.5" font-weight="bold">Teruskan L2</text>

                        <rect x="152" y="124" width="116" height="18" rx="9" fill="#854d0e" stroke="#fde047" stroke-width="1"/>
                        <text x="210" y="136" text-anchor="middle" fill="#fef08a" font-size="8" font-weight="bold">Laporan Evidence &amp; Verifikasi</text>
                    </svg>
                </div>

                <!-- 4 Mini Pillar Badges -->
                <div class="row no-gutters text-center" style="margin: -4px;">
                    <div class="col-6 p-1">
                        <div class="guide-stat-pill">
                            <div class="font-weight-bold text-warning small"><i class="fas fa-sitemap mr-1"></i> 4 Jabatan &amp; 2 Klasifikasi</div>
                            <div style="font-size: 0.7rem; color: #cbd5e1;">Kesekretariatan &amp; Kepaniteraan</div>
                        </div>
                    </div>
                    <div class="col-6 p-1">
                        <div class="guide-stat-pill">
                            <div class="font-weight-bold text-info small"><i class="fas fa-users mr-1"></i> Multi-Penerima Tugas</div>
                            <div style="font-size: 0.7rem; color: #cbd5e1;">Delegasi &gt; 1 Petugas Sekaligus</div>
                        </div>
                    </div>
                    <div class="col-6 p-1">
                        <div class="guide-stat-pill">
                            <div class="font-weight-bold text-success small"><i class="fas fa-shield-alt mr-1"></i> Security Captcha</div>
                            <div style="font-size: 0.7rem; color: #cbd5e1;">Anti-Bot &amp; Real-Time Push</div>
                        </div>
                    </div>
                    <div class="col-6 p-1">
                        <div class="guide-stat-pill">
                            <div class="font-weight-bold text-light small"><i class="fas fa-trophy text-warning mr-1"></i> Dual Leaderboard</div>
                            <div style="font-size: 0.7rem; color: #cbd5e1;">KPI Pelaksana &amp; Pemberi Tugas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     2. STICKY QUICK NAVIGATION & LIVE FILTER PENCARIAN
     ============================================================ -->
<div class="guide-nav-wrapper">
    <div class="row align-items-center">
        <div class="col-lg-8 mb-2 mb-lg-0">
            <div class="guide-nav-pills" id="guideQuickNav">
                <a href="#section-arsitektur" class="guide-nav-link active">
                    <i class="fas fa-layer-group mr-1 text-primary"></i> 1. Gambaran Umum
                </a>
                <a href="#section-peran" class="guide-nav-link">
                    <i class="fas fa-users-cog mr-1 text-success"></i> 2. Peran &amp; Wewenang User
                </a>
                <a href="#section-alur" class="guide-nav-link">
                    <i class="fas fa-project-diagram mr-1 text-info"></i> 3. Ilustrasi Alur Tugas
                </a>
                <a href="#section-sop" class="guide-nav-link">
                    <i class="fas fa-list-ol mr-1 text-warning"></i> 4. Tata Cara Penggunaan (SOP)
                </a>
                <a href="#section-fitur" class="guide-nav-link">
                    <i class="fas fa-star mr-1 text-danger"></i> 5. Fitur Unggulan &amp; Rumus KPI
                </a>
                <a href="#section-faq" class="guide-nav-link">
                    <i class="fas fa-question-circle mr-1 text-secondary"></i> 6. Bantuan &amp; FAQ
                </a>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="position-relative">
                <i class="fas fa-search guide-search-icon"></i>
                <input type="text" id="guideSearchInput" class="form-control form-control-sm guide-search-input"
                    placeholder="Cari topik panduan (misal: revisi, evidence, export, buka kembali)...">
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     SECTION 1: GAMBARAN UMUM & ARSITEKTUR EKOSISTEM SIMONTER
     ============================================================ -->
<div id="section-arsitektur" class="guide-section searchable-block">
    <div class="mb-3">
        <span class="guide-section-badge bg-primary text-white">Bagian 01 • Ekosistem Aplikasi</span>
        <h4 class="font-weight-bold text-gray-900 mb-1">
            <i class="fas fa-landmark text-primary mr-2"></i>Arsitektur &amp; Filosofi Pengelolaan SIMONTER
        </h4>
        <p class="text-muted small mb-0">
            Bagaimana SIMONTER mentransformasi disposisi manual menjadi sistem kendali kinerja digital yang terukur dan akuntabel.
        </p>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-3">
            <div class="feature-box border-top-primary" style="border-top: 4px solid #2563eb;">
                <div class="feature-icon-wrap bg-primary text-white shadow-sm">
                    <i class="fas fa-sitemap"></i>
                </div>
                <h6 class="font-weight-bold text-gray-900">1. Pendelegasian Multi-Penerima &amp; Klasifikasi 2 Bagian</h6>
                <p class="small text-muted mb-2" style="line-height: 1.55;">
                    Mendukung struktur organisasi Pengadilan Agama Jakarta Utara secara presisi dengan <strong>2 Klasifikasi Data Tugas</strong> yang dapat dilihat oleh seluruh pengguna:
                    <strong>Kesekretariatan</strong> (otomatis untuk delegasi <strong>Kepala Sub Bagian</strong>) dan <strong>Kepaniteraan</strong> (otomatis untuk delegasi <strong>Panitera Muda</strong>). Setiap delegasi tugas juga dapat diberikan kepada <strong>1 atau lebih petugas pelaksana sekaligus</strong>.
                </p>
                <div class="badge badge-light border text-primary font-weight-bold px-2 py-1">
                    <i class="fas fa-check-circle mr-1"></i> Multi-Penerima: Kesekretariatan &amp; Kepaniteraan
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="feature-box" style="border-top: 4px solid #10b981;">
                <div class="feature-icon-wrap bg-success text-white shadow-sm">
                    <i class="fas fa-file-signature"></i>
                </div>
                <h6 class="font-weight-bold text-gray-900">2. Pelaporan Berbasis Bukti (Evidence-Based)</h6>
                <p class="small text-muted mb-2" style="line-height: 1.55;">
                    Setiap penyelesaian tugas wajib dilampiri bukti nyata (<em>evidence</em>) berupa unggahan dokumen/berkas kerja (<code>PDF, DOCX, XLSX, ZIP, RAR, JPG, PNG</code> maks. <strong>10 MB</strong>) dan/atau tautan eksternal (<em>Google Drive, Sistem Peradilan, Website</em>) yang kemudian diverifikasi kelayakannya oleh pemberi tugas.
                </p>
                <div class="badge badge-light border text-success font-weight-bold px-2 py-1">
                    <i class="fas fa-shield-alt mr-1"></i> Validasi Ketat Sebelum Closed
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="feature-box" style="border-top: 4px solid #f59e0b;">
                <div class="feature-icon-wrap bg-warning text-white shadow-sm">
                    <i class="fas fa-history"></i>
                </div>
                <h6 class="font-weight-bold text-gray-900">3. Jejak Audit Kronologis (WIB / UTC+7)</h6>
                <p class="small text-muted mb-2" style="line-height: 1.55;">
                    Tidak ada aktivitas yang hilang. Sejak tugas dibuat, dibuka pertama kali oleh pelaksana, dikirim balik, diminta revisi, dibuka kembali (<em>reopen</em>), hingga ditutup (<em>closed</em>), semuanya terekam otomatis di <strong>Timeline Riwayat Log Tugas</strong> lengkap dengan nama aktor, jabatan, dan jam presisi <code>WIB</code>.
                </p>
                <div class="badge badge-light border text-warning font-weight-bold px-2 py-1">
                    <i class="fas fa-clock mr-1"></i> Transparansi 100% Real-Time
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     SECTION 2: PERAN, TUGAS, DAN FUNGSI SETIAP USER (3 ROLE)
     ============================================================ -->
<div id="section-peran" class="guide-section searchable-block">
    <div class="d-flex flex-wrap justify-content-between align-items-end mb-3">
        <div>
            <span class="guide-section-badge bg-success text-white">Bagian 02 • Struktur Peran &amp; Otoritas</span>
            <h4 class="font-weight-bold text-gray-900 mb-1">
                <i class="fas fa-user-shield text-success mr-2"></i>Peran, Tugas, dan Fungsi Setiap Pengguna
            </h4>
            <p class="text-muted small mb-0">
                Setiap akun memiliki hak akses, menu navigasi, dan tanggung jawab spesifik sesuai jabatannya di dalam sistem.
            </p>
        </div>
        <span class="badge badge-primary px-3 py-2 shadow-sm mt-2 mt-sm-0" style="font-size: 0.78rem; border-radius: 0.5rem;">
            <i class="fas fa-id-badge mr-1"></i> Peran Aktif Anda Saat Ini: <strong>{{ $user->jabatan }}</strong>
        </span>
    </div>

    <div class="row">
        <!-- ROLE 1: ADMIN -->
        <div class="col-lg-4 mb-4">
            <div class="role-card shadow-sm {{ $user->jabatan === 'Admin' ? 'role-card-active-user' : '' }}">
                <div class="role-header-admin">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="role-avatar-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        @if($user->jabatan === 'Admin')
                            <span class="badge badge-warning text-dark font-weight-bold px-2 py-1">
                                <i class="fas fa-star mr-1"></i> Peran Anda
                            </span>
                        @else
                            <span class="badge badge-light text-primary font-weight-bold px-2 py-1">Level 1 • Pimpinan/Admin</span>
                        @endif
                    </div>
                    <h5 class="font-weight-bold mt-3 mb-1">Administrator (Admin)</h5>
                    <p class="small mb-0" style="opacity: 0.88;">
                        Pengelola utama sistem, pendelegasi tingkat pimpinan, evaluator kinerja seluruh aparatur, dan pengendali mutu tugas.
                    </p>
                </div>
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-2" style="letter-spacing: 0.5px;">
                        <i class="fas fa-check-double mr-1"></i> Hak Akses &amp; Tanggung Jawab Utama:
                    </div>
                    <ul class="duty-list">
                        <li>
                            <i class="fas fa-users text-primary"></i>
                            <strong>Manajemen Data User (CRUD):</strong> Menambah, mengubah, mereset kredensial, atau menghapus akun Admin, Kepala Sub Bagian, Panitera Muda, dan Petugas beserta cetak laporan daftar pegawai (Excel/PDF).
                        </li>
                        <li>
                            <i class="fas fa-paper-plane text-primary"></i>
                            <strong>Pendelegasian Multi-Penerima &amp; Lintas Klasifikasi:</strong> Membuat tugas baru pada klasifikasi <strong>Kesekretariatan</strong> maupun <strong>Kepaniteraan</strong> dan menugaskannya kepada 1 atau lebih <strong>Kepala Sub Bagian</strong>, <strong>Panitera Muda</strong>, atau <strong>Petugas</strong> sekaligus.
                        </li>
                        <li>
                            <i class="fas fa-clipboard-check text-success"></i>
                            <strong>Verifikasi Hasil Kerja:</strong> Memeriksa bukti tindak lanjut (<em>file/link evidence</em>) dan memutuskan status <strong>Selesai (Closed)</strong> atau mengembalikan dengan status <strong>Perlu Revisi</strong>.
                        </li>
                        <li>
                            <i class="fas fa-undo-alt text-danger"></i>
                            <strong>Buka Kembali Tugas (Reopen):</strong> Membuka kembali tugas yang sudah berstatus <em>Selesai (Closed)</em> dan mendelegasikannya ulang apabila ditemukan kekeliruan di kemudian hari.
                        </li>
                        <li>
                            <i class="fas fa-chart-pie text-info"></i>
                            <strong>Dual Leaderboard &amp; Laporan:</strong> Memantau grafik tren, <strong>Matriks Kinerja Pelaksana Tugas</strong>, <strong>Matriks Kinerja Pemberi Tugas</strong> (Response Time &amp; Rasio Langsung Closed vs Revisi), serta ekspor Excel/PDF per klasifikasi.
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- ROLE 2: KEPALA SUB BAGIAN & PANITERA MUDA -->
        <div class="col-lg-4 mb-4">
            <div class="role-card shadow-sm {{ in_array($user->jabatan, ['Kepala Sub Bagian', 'Panitera Muda'], true) ? 'role-card-active-user' : '' }}">
                <div class="role-header-kasubbag">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="role-avatar-icon">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        @if(in_array($user->jabatan, ['Kepala Sub Bagian', 'Panitera Muda'], true))
                            <span class="badge badge-warning text-dark font-weight-bold px-2 py-1">
                                <i class="fas fa-star mr-1"></i> Peran Anda ({{ $user->jabatan }})
                            </span>
                        @else
                            <span class="badge badge-light text-teal font-weight-bold px-2 py-1" style="color: #0f766e;">Level 2 • Koordinator</span>
                        @endif
                    </div>
                    <h5 class="font-weight-bold mt-3 mb-1">Kepala Sub Bagian &amp; Panitera Muda</h5>
                    <p class="small mb-0" style="opacity: 0.88;">
                        Pejabat penghubung (<em>middle-tier</em> Kesekretariatan &amp; Kepaniteraan) yang memiliki fungsi ganda: penerima disposisi Admin sekaligus pendelegasi bagi Petugas.
                    </p>
                </div>
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-uppercase mb-2" style="color: #0f766e; letter-spacing: 0.5px;">
                        <i class="fas fa-check-double mr-1"></i> Hak Akses &amp; Tanggung Jawab Utama:
                    </div>
                    <ul class="duty-list">
                        <li>
                            <i class="fas fa-sitemap text-primary"></i>
                            <strong>Otomatisasi Klasifikasi Tugas:</strong> Tugas yang didelegasikan oleh <strong>Kepala Sub Bagian</strong> otomatis masuk klasifikasi <span class="badge badge-primary">Kesekretariatan</span>, sedangkan tugas yang didelegasikan oleh <strong>Panitera Muda</strong> otomatis masuk klasifikasi <span class="badge badge-success">Kepaniteraan</span>.
                        </li>
                        <li>
                            <i class="fas fa-inbox text-success"></i>
                            <strong>Menerima Disposisi &amp; Multi-Delegasi:</strong> Menerima tugas dari Admin dan dapat memilih <strong>Kerjakan Sendiri</strong> atau <strong>Teruskan / Delegasikan ke 1 atau Lebih Petugas Sekaligus</strong>.
                        </li>
                        <li>
                            <i class="fas fa-plus-circle text-info"></i>
                            <strong>Inisiasi Tugas Mandiri (Multi-Penerima):</strong> Membuat dan mendelegasikan tugas baru secara mandiri kepada satu atau beberapa <strong>Petugas</strong> sekaligus melalui menu <em>Delegasikan Tugas Baru</em>.
                        </li>
                        <li>
                            <i class="fas fa-user-check text-warning"></i>
                            <strong>Verifikasi &amp; Reopen Tugas Bawahan:</strong> Memverifikasi bukti kerja Petugas (<em>Selesai</em> atau <em>Perlu Revisi</em>) yang turut menentukan skor <em>Response Time</em> pemberi tugas.
                        </li>
                        <li>
                            <i class="fas fa-file-export text-secondary"></i>
                            <strong>Visibilitas Klasifikasi &amp; Ekspor:</strong> Melihat Data Tugas pada klasifikasi <em>Kesekretariatan</em> maupun <em>Kepaniteraan</em> serta mengunduh laporan Excel/PDF.
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- ROLE 3: PETUGAS -->
        <div class="col-lg-4 mb-4">
            <div class="role-card shadow-sm {{ $user->jabatan === 'Petugas' ? 'role-card-active-user' : '' }}">
                <div class="role-header-petugas">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="role-avatar-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        @if($user->jabatan === 'Petugas')
                            <span class="badge badge-warning text-dark font-weight-bold px-2 py-1">
                                <i class="fas fa-star mr-1"></i> Peran Anda
                            </span>
                        @else
                            <span class="badge badge-light text-info font-weight-bold px-2 py-1">Level 3 • Pelaksana</span>
                        @endif
                    </div>
                    <h5 class="font-weight-bold mt-3 mb-1">Petugas (Pelaksana)</h5>
                    <p class="small mb-0" style="opacity: 0.88;">
                        Ujung tombak eksekusi teknis yang menjalankan instruksi tugas, mengunggah bukti penyelesaian, dan menjaga ketepatan waktu.
                    </p>
                </div>
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-2" style="letter-spacing: 0.5px;">
                        <i class="fas fa-check-double mr-1"></i> Hak Akses &amp; Tanggung Jawab Utama:
                    </div>
                    <ul class="duty-list">
                        <li>
                            <i class="fas fa-bell text-primary"></i>
                            <strong>Penerimaan Push Notifikasi Instan:</strong> Menerima peringatan suara &amp; kartu pop-up saat ada tugas baru, delegasi lanjutan, permintaan revisi, atau pembukaan kembali tugas.
                        </li>
                        <li>
                            <i class="fas fa-play text-info"></i>
                            <strong>Auto-Start Status Pengerjaan:</strong> Begitu Petugas membuka halaman <em>Detail Tugas</em> yang berstatus <code>Didelegasikan</code>, sistem otomatis mengubah status menjadi <code>Sedang Dikerjakan</code> dan mencatat jam mulai di log.
                        </li>
                        <li>
                            <i class="fas fa-cloud-upload-alt text-success"></i>
                            <strong>Pengiriman Tindak Lanjut &amp; Evidence:</strong> Mengisi uraian hasil kerja serta melampirkan berkas bukti (maks. 10 MB) dan/atau tautan URL bukti kerja untuk dikirim balik ke pemberi tugas.
                        </li>
                        <li>
                            <i class="fas fa-sync text-warning"></i>
                            <strong>Penyelesaian Revisi / Reopen:</strong> Menindaklanjuti catatan perbaikan dari Admin atau Kepala Sub Bagian dan mengirimkan ulang bukti terbaru.
                        </li>
                        <li>
                            <i class="fas fa-medal text-danger"></i>
                            <strong>Evaluasi Performa Mandiri:</strong> Memantau tingkat ketepatan waktu pribadi, rata-rata hari pengerjaan, dan predikat kinerja di Dashboard Kinerja Petugas.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Matriks Perbandingan Kewenangan -->
    <div class="card shadow-sm border-0 mt-2" style="border-radius: 1rem; overflow: hidden;">
        <div class="card-header py-3 bg-white border-bottom d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-gray-900">
                <i class="fas fa-table text-primary mr-2"></i>Matriks Perbandingan Hak Akses &amp; Kewenangan Fitur
            </h6>
            <span class="badge badge-light border text-muted">Ringkasan Otorisasi Sistem</span>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0 small">
                <thead class="bg-light text-gray-800">
                    <tr>
                        <th style="min-width: 240px;">Modul / Kapabilitas Sistem</th>
                        <th class="text-center" style="width: 22%;"><i class="fas fa-user-shield text-primary mr-1"></i> Admin</th>
                        <th class="text-center" style="width: 26%;"><i class="fas fa-user-cog text-success mr-1"></i> Kepala Sub Bagian &amp; Panitera Muda</th>
                        <th class="text-center" style="width: 22%;"><i class="fas fa-user-tie text-info mr-1"></i> Petugas</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-weight-bold">Kelola Data User (Tambah, Edit, Hapus, Export Pegawai)</td>
                        <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Penuh (Seluruh User)</td>
                        <td class="text-center text-muted"><i class="fas fa-times-circle mr-1"></i> Tidak Tersedia</td>
                        <td class="text-center text-muted"><i class="fas fa-times-circle mr-1"></i> Tidak Tersedia</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Melihat Klasifikasi Data Tugas (Kesekretariatan &amp; Kepaniteraan)</td>
                        <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Kedua Klasifikasi</td>
                        <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Kedua Klasifikasi</td>
                        <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Kedua Klasifikasi</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Membuat &amp; Mendelegasikan Tugas Baru (Multi-Penerima &gt; 1 Orang)</td>
                        <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Ke Kasubbag, Panmud &amp; Petugas</td>
                        <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Ke Petugas (Sekretariat / Kepaniteraan)</td>
                        <td class="text-center text-muted"><i class="fas fa-times-circle mr-1"></i> Tidak Tersedia</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Klasifikasi Otomatis Saat Mendelegasikan Tugas</td>
                        <td class="text-center text-primary font-weight-bold"><i class="fas fa-sliders-h mr-1"></i> Fleksibel Pilih Klasifikasi</td>
                        <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Kasubbag &rarr; Kesekretariatan<br>Panmud &rarr; Kepaniteraan</td>
                        <td class="text-center text-muted"><i class="fas fa-minus-circle mr-1"></i> Penerima Tugas</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Meneruskan (Forward) Tugas yang Diterima ke Petugas</td>
                        <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Ya</td>
                        <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Ya (Fitur Utama)</td>
                        <td class="text-center text-muted"><i class="fas fa-times-circle mr-1"></i> Tidak Tersedia</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Mengirim Laporan Tindak Lanjut &amp; Upload Evidence</td>
                        <td class="text-center text-info"><i class="fas fa-check-circle mr-1"></i> Ya (Supervisi)</td>
                        <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Ya (Tugas dari Admin)</td>
                        <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Ya (Tugas Saya)</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Verifikasi Tugas (Setujui Selesai / Minta Revisi)</td>
                        <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Seluruh Tugas</td>
                        <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Tugas yang Didelegasikannya</td>
                        <td class="text-center text-muted"><i class="fas fa-times-circle mr-1"></i> Tidak Tersedia</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Buka Kembali (Reopen) Tugas Berstatus Closed</td>
                        <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Seluruh Tugas Closed</td>
                        <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Tugas yang Didelegasikannya</td>
                        <td class="text-center text-muted"><i class="fas fa-times-circle mr-1"></i> Tidak Tersedia</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Export Laporan Tugas ke Excel (`.xls`) &amp; PDF</td>
                        <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Seluruh Tugas + Filter</td>
                        <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Tugas Terkait + Filter</td>
                        <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Tugas Pribadi + Filter</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Leaderboard &amp; Peringkat Kinerja Seluruh Aparatur</td>
                        <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Tabel Lengkap &amp; Grafik</td>
                        <td class="text-center text-info"><i class="fas fa-chart-line mr-1"></i> KPI &amp; Grafik Unit/Pribadi</td>
                        <td class="text-center text-info"><i class="fas fa-chart-line mr-1"></i> KPI &amp; Grafik Pribadi</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Push Notifikasi, Pengaturan Profil, &amp; Petunjuk Aplikasi</td>
                        <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Tersedia</td>
                        <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Tersedia</td>
                        <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Tersedia</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================================
     SECTION 3: ILUSTRASI ALUR KERJA & SIKLUS STATUS TUGAS
     ============================================================ -->
<div id="section-alur" class="guide-section searchable-block">
    <div class="mb-3">
        <span class="guide-section-badge bg-info text-white">Bagian 03 • Ilustrasi Siklus Hidup Tugas</span>
        <h4 class="font-weight-bold text-gray-900 mb-1">
            <i class="fas fa-project-diagram text-info mr-2"></i>Ilustrasi Alur Pendelegasian &amp; Transisi Status Tugas
        </h4>
        <p class="text-muted small mb-0">
            Visualisasi menyeluruh bagaimana sebuah tugas bergerak dari pembuatan instruksi hingga terverifikasi selesai.
        </p>
    </div>

    <!-- 6 Tahap Alur Visual -->
    <div class="row">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="workflow-step-card">
                <div class="workflow-number bg-primary">1</div>
                <span class="badge badge-primary mb-2 px-2 py-1" style="font-size: 0.68rem;">Didelegasikan</span>
                <h6 class="font-weight-bold text-gray-900 small mb-1">Inisiasi &amp; Disposisi</h6>
                <p class="text-muted mb-0" style="font-size: 0.78rem; line-height: 1.45;">
                    Admin atau Kasubbag membuat tugas baru, menentukan batas waktu, &amp; mengunggah berkas lampiran. Status penerima otomatis menjadi <strong>Sedang Bertugas</strong>.
                </p>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="workflow-step-card">
                <div class="workflow-number" style="background: #0d9488;">2</div>
                <span class="badge badge-info mb-2 px-2 py-1" style="font-size: 0.68rem;">Delegasi Lanjutan (Opsional)</span>
                <h6 class="font-weight-bold text-gray-900 small mb-1">Teruskan ke Petugas</h6>
                <p class="text-muted mb-0" style="font-size: 0.78rem; line-height: 1.45;">
                    Jika penerima awal adalah <strong>Kepala Sub Bagian</strong>, tugas dapat dikerjakan sendiri atau diteruskan kepada <strong>Petugas</strong> dengan instruksi tambahan.
                </p>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="workflow-step-card">
                <div class="workflow-number bg-info">3</div>
                <span class="badge badge-info mb-2 px-2 py-1" style="font-size: 0.68rem;">Sedang Dikerjakan</span>
                <h6 class="font-weight-bold text-gray-900 small mb-1">Eksekusi Pelaksana</h6>
                <p class="text-muted mb-0" style="font-size: 0.78rem; line-height: 1.45;">
                    Saat pelaksana membuka halaman <strong>Detail Tugas</strong>, status berubah otomatis ke <em>Sedang Dikerjakan</em>. Pelaksana mengunduh lampiran dan menjalankan instruksi.
                </p>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="workflow-step-card">
                <div class="workflow-number bg-warning">4</div>
                <span class="badge badge-warning text-dark mb-2 px-2 py-1" style="font-size: 0.68rem;">Menunggu Konfirmasi</span>
                <h6 class="font-weight-bold text-gray-900 small mb-1">Kirim Balik &amp; Evidence</h6>
                <p class="text-muted mb-0" style="font-size: 0.78rem; line-height: 1.45;">
                    Pelaksana mengisi laporan tindak lanjut dan mengunggah berkas bukti (maks. 10 MB) atau tautan URL. Notifikasi terkirim ke pemberi tugas.
                </p>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="workflow-step-card">
                <div class="workflow-number bg-danger">5</div>
                <span class="badge badge-danger mb-2 px-2 py-1" style="font-size: 0.68rem;">Perlu Revisi / Reopen</span>
                <h6 class="font-weight-bold text-gray-900 small mb-1">Koreksi &amp; Perbaikan</h6>
                <p class="text-muted mb-0" style="font-size: 0.78rem; line-height: 1.45;">
                    Jika bukti belum sesuai, pendelegasi memilih <strong>Perlu Revisi</strong> (atau <strong>Buka Kembali</strong> jika sudah closed) disertai catatan koreksi untuk diperbaiki.
                </p>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="workflow-step-card">
                <div class="workflow-number bg-success">6</div>
                <span class="badge badge-success mb-2 px-2 py-1" style="font-size: 0.68rem;">Selesai (Closed)</span>
                <h6 class="font-weight-bold text-gray-900 small mb-1">Terverifikasi Tuntas</h6>
                <p class="text-muted mb-0" style="font-size: 0.78rem; line-height: 1.45;">
                    Pendelegasi menyetujui hasil kerja. Tugas ditutup (<em>Closed</em>), poin kinerja dihitung otomatis, &amp; bila tak ada tugas aktif lain, status user kembali <strong>Belum Ditugaskan</strong>.
                </p>
            </div>
        </div>
    </div>

    <!-- Kamus Indikator Badge Status -->
    <div class="card shadow-sm border-0 mt-2" style="border-radius: 1rem;">
        <div class="card-body py-3">
            <div class="font-weight-bold text-gray-800 small mb-2">
                <i class="fas fa-tags text-primary mr-1"></i> Kamus Indikator Klasifikasi Tugas, Status Siklus Tugas, &amp; Status Ketersediaan Pegawai:
            </div>
            <div class="row small">
                <div class="col-md-4 mb-2 mb-md-0">
                    <div class="p-2 bg-light rounded border h-100">
                        <div class="font-weight-bold text-gray-700 mb-2">2 Klasifikasi Data Tugas (Delegasi):</div>
                        <div class="d-flex flex-wrap align-items-center mb-1" style="gap: 6px;">
                            <span class="badge badge-primary px-2 py-1"><i class="fas fa-building mr-1"></i> Kesekretariatan</span>
                            <span class="text-muted" style="font-size: 0.74rem;">Delegasi Kepala Sub Bagian &amp; Umum.</span>
                        </div>
                        <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                            <span class="badge badge-success px-2 py-1"><i class="fas fa-balance-scale mr-1"></i> Kepaniteraan</span>
                            <span class="text-muted" style="font-size: 0.74rem;">Delegasi Panitera Muda &amp; Perkara.</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-2 mb-md-0">
                    <div class="p-2 bg-light rounded border h-100">
                        <div class="font-weight-bold text-gray-700 mb-2">5 Status Siklus Tugas:</div>
                        <div class="d-flex flex-wrap" style="gap: 6px;">
                            <span class="badge badge-primary px-2 py-1">Didelegasikan</span>
                            <span class="badge badge-info px-2 py-1">Sedang Dikerjakan</span>
                            <span class="badge badge-warning text-dark px-2 py-1">Menunggu Konfirmasi</span>
                            <span class="badge badge-danger px-2 py-1">Perlu Revisi</span>
                            <span class="badge badge-success px-2 py-1">Selesai</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-2 bg-light rounded border h-100">
                        <div class="font-weight-bold text-gray-700 mb-2">2 Status Beban Kerja Pegawai (Otomatis):</div>
                        <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                            <span class="badge badge-success px-2 py-1"><i class="fas fa-clipboard-check mr-1"></i> Sedang Bertugas</span>
                            <span class="text-muted" style="font-size: 0.75rem;">Memiliki minimal 1 tugas aktif.</span>
                        </div>
                        <div class="d-flex flex-wrap align-items-center mt-1" style="gap: 8px;">
                            <span class="badge badge-secondary px-2 py-1"><i class="fas fa-clock mr-1"></i> Belum Ditugaskan</span>
                            <span class="text-muted" style="font-size: 0.75rem;">Siap menerima delegasi baru.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     SECTION 4: TATA CARA PENGGUNAAN & PENGELOLAAN (SOP INTERAKTIF)
     ============================================================ -->
<div id="section-sop" class="guide-section searchable-block">
    <div class="mb-3">
        <span class="guide-section-badge bg-warning text-dark">Bagian 04 • Panduan Langkah demi Langkah (SOP)</span>
        <h4 class="font-weight-bold text-gray-900 mb-1">
            <i class="fas fa-laptop-code text-warning mr-2"></i>Tata Cara Penggunaan &amp; Pengelolaan Aplikasi per Peran
        </h4>
        <p class="text-muted small mb-0">
            Pilih tab peran di bawah ini untuk melihat instruksi operasional teknis secara berurutan (<em>step-by-step</em>). Tab otomatis menyesuaikan dengan jabatan Anda saat ini.
        </p>
    </div>

    <!-- Nav Tabs SOP Role -->
    <ul class="nav nav-pills mb-3" id="sopRoleTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link sop-tab-btn {{ $isAdmin ? 'active' : '' }}" id="tab-admin-link" data-toggle="pill" href="#pane-sop-admin" role="tab">
                <i class="fas fa-user-shield mr-1"></i> Panduan Lengkap Admin
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link sop-tab-btn {{ $isKasubbag ? 'active' : '' }}" id="tab-kasubbag-link" data-toggle="pill" href="#pane-sop-kasubbag" role="tab">
                <i class="fas fa-user-cog mr-1"></i> Panduan Kepala Sub Bagian &amp; Panitera Muda
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link sop-tab-btn {{ (!$isAdmin && !$isKasubbag) ? 'active' : '' }}" id="tab-petugas-link" data-toggle="pill" href="#pane-sop-petugas" role="tab">
                <i class="fas fa-user-tie mr-1"></i> Panduan Petugas Pelaksana
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link sop-tab-btn" id="tab-umum-link" data-toggle="pill" href="#pane-sop-umum" role="tab">
                <i class="fas fa-sliders-h mr-1"></i> Pengaturan Akun &amp; Notifikasi (Semua User)
            </a>
        </li>
    </ul>

    <div class="tab-content" id="sopRoleTabContent">
        <!-- ==================== TAB 1: SOP ADMIN ==================== -->
        <div class="tab-pane fade {{ $isAdmin ? 'show active' : '' }}" id="pane-sop-admin" role="tabpanel">
            <div class="card shadow-sm border-0" style="border-radius: 1rem;">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <h5 class="font-weight-bold text-primary mb-0">
                                <i class="fas fa-user-shield mr-1"></i> Standar Operasional Prosedur (SOP) — Administrator
                            </h5>
                            <small class="text-muted">Tata cara mengelola pengguna, mendelegasikan tugas, memverifikasi hasil, membuka kembali tugas, dan mencetak laporan.</small>
                        </div>
                        @if($isAdmin)
                            <div class="mt-2 mt-sm-0">
                                <a href="{{ route('user.index') }}" class="btn btn-sm btn-outline-primary font-weight-bold mr-1">
                                    <i class="fas fa-users mr-1"></i> Buka Data User
                                </a>
                                <a href="{{ route('tugas.create') }}" class="btn btn-sm btn-primary font-weight-bold">
                                    <i class="fas fa-plus mr-1"></i> Buat Delegasi Tugas
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="sop-timeline">
                        <div class="sop-timeline-item">
                            <div class="sop-timeline-dot">1</div>
                            <h6 class="font-weight-bold text-gray-900 mb-1">Mengelola Data Pegawai / Pengguna Sistem (Menu: Data User)</h6>
                            <p class="small text-muted mb-2">
                                Buka menu <strong>Data User</strong> di sidebar kiri. Klik tombol <strong>+ Tambah Data</strong> untuk mendaftarkan aparatur baru dengan mengisi:
                                <em>Nama Lengkap</em>, <em>NIP (18 digit/unik untuk login)</em>, <em>Email</em>, <em>Nomor Kontak/WA</em>, <em>Jabatan (Admin / Kepala Sub Bagian / Panitera Muda / Petugas)</em>, dan <em>Password</em>.
                            </p>
                            <div class="small bg-white p-2 rounded border text-gray-700">
                                <i class="fas fa-lightbulb text-warning mr-1"></i> <strong>Tips:</strong> Anda juga dapat menekan tombol <strong>Export Excel</strong> atau <strong>Export PDF</strong> di kanan atas tabel Data User untuk mengunduh rekapitulasi daftar pegawai beserta status penugasannya.
                            </div>
                        </div>

                        <div class="sop-timeline-item">
                            <div class="sop-timeline-dot">2</div>
                            <h6 class="font-weight-bold text-gray-900 mb-1">Membuat &amp; Mendelegasikan Tugas Baru Multi-Penerima (Menu: Data Tugas -> Delegasikan Tugas)</h6>
                            <p class="small text-muted mb-2">
                                Klik tombol <strong>+ Delegasikan Tugas Baru</strong>. Isi <strong>Judul Tugas</strong>, pilih <strong>Klasifikasi Tugas</strong> (<em>Kesekretariatan</em> atau <em>Kepaniteraan</em>), lalu centang <strong>1 atau lebih penerima tugas sekaligus</strong> (<em>Kepala Sub Bagian, Panitera Muda, atau Petugas</em>) menggunakan fitur pencarian dan tombol pilih cepat. Tentukan <strong>Tanggal Mulai</strong> dan <strong>Batas Waktu (Deadline)</strong>, tuliskan <strong>Deskripsi &amp; Instruksi</strong>, serta unggah <strong>Berkas Lampiran Tugas</strong> (opsional, maks. 10 MB).
                            </p>
                            <div class="small bg-white p-2 rounded border text-gray-700">
                                <i class="fas fa-bell text-primary mr-1"></i> Begitu disimpan, sistem otomatis membuat delegasi untuk seluruh penerima yang dicentang dan mengirim <strong>Push Notifikasi Real-Time</strong>.
                            </div>
                        </div>

                        <div class="sop-timeline-item">
                            <div class="sop-timeline-dot">3</div>
                            <h6 class="font-weight-bold text-gray-900 mb-1">Memeriksa Evidence &amp; Memverifikasi Tugas (Status: Menunggu Konfirmasi)</h6>
                            <p class="small text-muted mb-2">
                                Saat pelaksana mengirimkan laporan tindak lanjut, Anda akan menerima notifikasi hijau/biru. Klik notifikasi tersebut atau buka <strong>Detail Tugas</strong>:
                            </p>
                            <ul class="small text-muted mb-0 pl-3">
                                <li>Periksa <strong>Catatan Laporan Petugas</strong>, unduh <strong>Berkas Evidence</strong>, atau buka <strong>Link Evidence</strong> yang dilampirkan.</li>
                                <li>Pada panel <strong>Verifikasi Hasil Tindak Lanjut</strong>, pilih <strong>Tandai Selesai (Verified)</strong> jika hasil kerja sudah tepat, atau pilih <strong>Kembalikan untuk Revisi</strong> disertai catatan koreksi apabila masih ada kekurangan.</li>
                            </ul>
                        </div>

                        <div class="sop-timeline-item">
                            <div class="sop-timeline-dot">4</div>
                            <h6 class="font-weight-bold text-gray-900 mb-1">Membuka Kembali Tugas yang Sudah Ditutup / Closed (Fitur Reopen)</h6>
                            <p class="small text-muted mb-0">
                                Apabila suatu tugas telah berstatus <span class="badge badge-success">Selesai</span> namun di kemudian hari ditemukan kekeliruan pengerjaan atau butuh penyempurnaan dokumen, buka halaman <strong>Detail Tugas</strong> tersebut. Pada kartu merah/oranye <strong>Buka Kembali &amp; Delegasikan Ulang Tugas</strong>, isi <em>Alasan Pembukaan Kembali / Instruksi Perbaikan</em> dan sesuaikan <em>Batas Selesai Baru</em> (opsional), lalu klik <strong>Buka Kembali &amp; Delegasikan Ulang</strong>. Status tugas akan kembali menjadi <span class="badge badge-primary">Didelegasikan</span> kepada petugas semula.
                            </p>
                        </div>

                        <div class="sop-timeline-item mb-0">
                            <div class="sop-timeline-dot">5</div>
                            <h6 class="font-weight-bold text-gray-900 mb-1">Evaluasi Dashboard Analitik &amp; Cetak Laporan Berkala (Excel / PDF)</h6>
                            <p class="small text-muted mb-0">
                                Gunakan <strong>Dashboard</strong> untuk memantau grafik tren penyelesaian tugas (filter <em>1 Tahun, 6 Bulan, 3 Bulan, 1 Bulan</em>) dan tabel <strong>Matriks &amp; Peringkat Kinerja Seluruh Petugas</strong>. Untuk mencetak laporan resmi, buka menu <strong>Data Tugas</strong>, gunakan filter <em>Petugas</em>, <em>Rentang Tanggal</em>, atau <em>Status</em>, lalu klik <strong>Export Excel</strong> atau <strong>Export PDF</strong>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== TAB 2: SOP KEPALA SUB BAGIAN ==================== -->
        <div class="tab-pane fade {{ $isKasubbag ? 'show active' : '' }}" id="pane-sop-kasubbag" role="tabpanel">
            <div class="card shadow-sm border-0" style="border-radius: 1rem;">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <h5 class="font-weight-bold mb-0" style="color: #0f766e;">
                                <i class="fas fa-user-cog mr-1"></i> Standar Operasional Prosedur (SOP) — Kepala Sub Bagian &amp; Panitera Muda
                            </h5>
                            <small class="text-muted">Tata cara menindaklanjuti disposisi dari Admin, meneruskan tugas ke Petugas, dan memverifikasi hasil kerja unit.</small>
                        </div>
                        @if($isKasubbag)
                            <div class="mt-2 mt-sm-0">
                                <a href="{{ route('tugas.index') }}" class="btn btn-sm btn-outline-info font-weight-bold mr-1">
                                    <i class="fas fa-tasks mr-1"></i> Daftar Tugas Unit
                                </a>
                                <a href="{{ route('tugas.create') }}" class="btn btn-sm btn-primary font-weight-bold">
                                    <i class="fas fa-paper-plane mr-1"></i> Delegasikan Tugas Baru
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="sop-timeline">
                        <div class="sop-timeline-item">
                            <div class="sop-timeline-dot" style="background: #0d9488;">1</div>
                            <h6 class="font-weight-bold text-gray-900 mb-1">Menerima Tugas dari Admin: Kerjakan Sendiri vs Delegasikan ke Petugas</h6>
                            <p class="small text-muted mb-2">
                                Ketika Admin mendelegasikan tugas kepada Anda, buka halaman <strong>Detail Tugas</strong>. Sebagai Kepala Sub Bagian, Anda memiliki <strong>2 jalur tindakan</strong>:
                            </p>
                            <div class="row small">
                                <div class="col-md-6 mb-2 mb-md-0">
                                    <div class="p-2 bg-white rounded border h-100">
                                        <strong class="text-primary"><i class="fas fa-share-alt mr-1"></i> Opsi A — Teruskan / Delegasikan ke Petugas:</strong>
                                        <p class="mb-0 text-muted mt-1">
                                            Gunakan kartu <strong>Delegasikan Tugas Ini ke Petugas</strong> di halaman Detail Tugas (atau tombol <em>Delegasikan</em> di tabel). Pilih nama <strong>Petugas</strong>, tambahkan <em>Instruksi Tambahan Kasubbag</em>, dan sesuaikan tenggat waktu.
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-2 bg-white rounded border h-100">
                                        <strong class="text-success"><i class="fas fa-file-upload mr-1"></i> Opsi B — Tindak Lanjuti Sendiri:</strong>
                                        <p class="mb-0 text-muted mt-1">
                                            Jika tugas bersifat strategis untuk diselesaikan langsung oleh Kasubbag, klik tombol <strong>Tindak Lanjut &amp; Kirim Evidence</strong>, unggah berkas/link bukti, lalu kirim balik ke Admin.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="sop-timeline-item">
                            <div class="sop-timeline-dot" style="background: #0d9488;">2</div>
                            <h6 class="font-weight-bold text-gray-900 mb-1">Membuat Delegasi Tugas Baru &amp; Klasifikasi Otomatis (Kesekretariatan / Kepaniteraan)</h6>
                            <p class="small text-muted mb-0">
                                Selain meneruskan tugas dari Admin, Anda dapat membuat instruksi tugas baru kepada <strong>1 atau beberapa Petugas sekaligus</strong> melalui menu <strong>Delegasikan Tugas Baru</strong>. Sistem secara otomatis menetapkan klasifikasi tugas sesuai jabatan pendelegasi: tugas dari <strong>Kepala Sub Bagian</strong> otomatis masuk klasifikasi <span class="badge badge-primary">Kesekretariatan</span>, sedangkan tugas dari <strong>Panitera Muda</strong> otomatis masuk klasifikasi <span class="badge badge-success">Kepaniteraan</span>.
                            </p>
                        </div>

                        <div class="sop-timeline-item">
                            <div class="sop-timeline-dot" style="background: #0d9488;">3</div>
                            <h6 class="font-weight-bold text-gray-900 mb-1">Memverifikasi Hasil Kerja Petugas &amp; Buka Kembali Tugas</h6>
                            <p class="small text-muted mb-0">
                                Untuk seluruh tugas yang Anda delegasikan kepada Petugas (dimana Anda tercatat sebagai <em>Pendelegasi</em>), Anda berwenang penuh memeriksa bukti <em>evidence</em> dari Petugas, memberikan keputusan <strong>Selesai (Verified)</strong> atau <strong>Perlu Revisi</strong>, serta menggunakan fitur <strong>Buka Kembali (Reopen)</strong> jika tugas yang sudah selesai memerlukan koreksi ulang.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== TAB 3: SOP PETUGAS ==================== -->
        <div class="tab-pane fade {{ (!$isAdmin && !$isKasubbag) ? 'show active' : '' }}" id="pane-sop-petugas" role="tabpanel">
            <div class="card shadow-sm border-0" style="border-radius: 1rem;">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <h5 class="font-weight-bold text-info mb-0">
                                <i class="fas fa-user-tie mr-1"></i> Standar Operasional Prosedur (SOP) — Petugas Pelaksana
                            </h5>
                            <small class="text-muted">Tata cara menerima tugas, mengunduh berkas instruksi, mengunggah bukti evidence, dan menuntaskan revisi.</small>
                        </div>
                        @if(!$isAdmin && !$isKasubbag)
                            <div class="mt-2 mt-sm-0">
                                <a href="{{ route('tugas.index') }}" class="btn btn-sm btn-info font-weight-bold">
                                    <i class="fas fa-tasks mr-1"></i> Buka Data Tugas Saya
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="sop-timeline">
                        <div class="sop-timeline-item">
                            <div class="sop-timeline-dot bg-info">1</div>
                            <h6 class="font-weight-bold text-gray-900 mb-1">Menerima Notifikasi &amp; Membuka Detail Tugas</h6>
                            <p class="small text-muted mb-0">
                                Saat Admin atau Kepala Sub Bagian menugaskan pekerjaan kepada Anda, kartu <strong>Push Notifikasi</strong> akan muncul di kanan atas layar disertai bunyi <em>chime</em>. Klik tombol <strong>Lihat Detail Tugas</strong> pada notifikasi atau buka menu <strong>Data Tugas Saya</strong>. Begitu halaman detail dibuka, status tugas otomatis berubah dari <span class="badge badge-primary">Didelegasikan</span> menjadi <span class="badge badge-info">Sedang Dikerjakan</span>.
                            </p>
                        </div>

                        <div class="sop-timeline-item">
                            <div class="sop-timeline-dot bg-info">2</div>
                            <h6 class="font-weight-bold text-gray-900 mb-1">Mempelajari Instruksi &amp; Mengunduh Lampiran Tugas</h6>
                            <p class="small text-muted mb-0">
                                Perhatikan <strong>Batas Waktu (Deadline)</strong>, baca uraian instruksi (termasuk <em>Instruksi Tambahan Kasubbag</em> bila ada), dan klik tombol <strong>Unduh Berkas Tugas</strong> apabila pemberi tugas menyertakan dokumen acuan.
                            </p>
                        </div>

                        <div class="sop-timeline-item">
                            <div class="sop-timeline-dot bg-info">3</div>
                            <h6 class="font-weight-bold text-gray-900 mb-1">Mengirimkan Laporan Tindak Lanjut &amp; Bukti Kerja (Evidence)</h6>
                            <p class="small text-muted mb-2">
                                Setelah tugas selesai dikerjakan, klik tombol hijau <strong>Tindak Lanjut / Kirim Evidence</strong>:
                            </p>
                            <ul class="small text-muted mb-0 pl-3">
                                <li><strong>Laporan / Keterangan Tindak Lanjut (Wajib):</strong> Tuliskan ringkasan hasil pengerjaan secara jelas.</li>
                                <li><strong>Upload File Evidence (Maks. 10 MB):</strong> Unggah dokumen hasil kerja (<code>PDF, DOC, DOCX, XLS, XLSX, ZIP, RAR, JPG, PNG</code>), <strong>ATAU</strong></li>
                                <li><strong>Link Evidence (URL):</strong> Tempelkan tautan bukti kerja (diawali <code>https://</code> atau <code>http://</code>, misalnya link Google Drive). Anda juga diperbolehkan mengisi keduanya sekaligus.</li>
                                <li>Klik <strong>Kirim Balik ke Pemberi Tugas</strong>. Status tugas akan berubah menjadi <span class="badge badge-warning text-dark">Menunggu Konfirmasi</span>.</li>
                            </ul>
                        </div>

                        <div class="sop-timeline-item mb-0">
                            <div class="sop-timeline-dot bg-info">4</div>
                            <h6 class="font-weight-bold text-gray-900 mb-1">Menangani Permintaan Revisi atau Tugas yang Dibuka Kembali (Reopen)</h6>
                            <p class="small text-muted mb-0">
                                Jika pemberi tugas mengembalikan pekerjaan dengan status <span class="badge badge-danger">Perlu Revisi</span> atau membuka kembali tugas yang telah ditutup, baca dengan saksama <strong>Catatan Admin / Pendelegasi</strong> yang tampil pada kotak peringatan di halaman Detail Tugas, perbaiki hasil kerja Anda, lalu kirimkan kembali <em>evidence</em> terbaru melalui formulir Tindak Lanjut.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== TAB 4: SOP UMUM (SEMUA USER) ==================== -->
        <div class="tab-pane fade" id="pane-sop-umum" role="tabpanel">
            <div class="card shadow-sm border-0" style="border-radius: 1rem;">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <h5 class="font-weight-bold text-gray-900 mb-0">
                                <i class="fas fa-sliders-h text-primary mr-1"></i> Panduan Pengaturan Akun, Keamanan &amp; Pusat Notifikasi
                            </h5>
                            <small class="text-muted">Berlaku untuk seluruh pengguna (Admin, Kepala Sub Bagian, dan Petugas).</small>
                        </div>
                        <a href="{{ route('pengaturan.index') }}" class="btn btn-sm btn-primary font-weight-bold mt-2 mt-sm-0">
                            <i class="fas fa-user-cog mr-1"></i> Buka Pengaturan Akun
                        </a>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded border h-100">
                                <h6 class="font-weight-bold text-primary">
                                    <i class="fas fa-camera mr-1"></i> 1. Mengubah Foto Profil &amp; Nomor WhatsApp/Kontak
                                </h6>
                                <p class="small text-muted mb-2">
                                    Klik nama/foto Anda di pojok kanan atas, lalu pilih <strong>Pengaturan Akun</strong>:
                                </p>
                                <ul class="small text-muted mb-0 pl-3">
                                    <li>Klik ikon kamera pada lingkaran foto atau tombol <strong>Pilih Berkas Foto Baru</strong> (mendukung format <code>JPG, JPEG, PNG, WEBP</code> maks. <strong>2 MB</strong> dengan pratinjau langsung).</li>
                                    <li>Anda juga dapat mencentang <em>Hapus Foto Profil</em> jika ingin kembali ke avatar bawaan.</li>
                                    <li>Perbarui <strong>Nomor Telepon / WhatsApp</strong> untuk memudahkan koordinasi kedinasan.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded border h-100">
                                <h6 class="font-weight-bold text-primary">
                                    <i class="fas fa-lock mr-1"></i> 2. Mengganti Password, Login Fleksibel (NIP / Email) &amp; Security Captcha
                                </h6>
                                <p class="small text-muted mb-2">
                                    SIMONTER mendukung autentikasi ganda yang dilengkapi perlindungan keamanan anti-bot:
                                </p>
                                <ul class="small text-muted mb-0 pl-3">
                                    <li>Saat login, Anda dapat memasukkan <strong>NIP (18 digit)</strong> maupun <strong>Alamat Email</strong>, lalu wajib mencentang verifikasi keamanan <strong>"Saya bukan robot"</strong> (dilengkapi proteksi <em>Anti-Brute Force Rate Limiter</em>).</li>
                                    <li>Untuk mengganti kata sandi di halaman <strong>Pengaturan Akun</strong>, masukkan <em>Password Saat Ini</em>, lalu ketik <em>Password Baru</em> (minimal <strong>8 karakter</strong>) beserta konfirmasinya.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="p-3 bg-light rounded border">
                                <h6 class="font-weight-bold text-primary">
                                    <i class="fas fa-bell mr-1"></i> 3. Mengoptimalkan Pusat Notifikasi &amp; Browser Desktop Alert
                                </h6>
                                <p class="small text-muted mb-0">
                                    Klik ikon lonceng (<i class="fas fa-bell text-primary"></i>) di topbar kanan atas untuk membuka <strong>Pusat Notifikasi</strong>. Jika muncul pita <em>"Aktifkan push notifikasi layar?"</em>, klik tombol <strong>Aktifkan</strong> lalu pilih <strong>Allow / Izinkan</strong> pada browser Anda. Dengan demikian, meskipun Anda sedang membuka tab lain, pemberitahuan tugas dari SIMONTER tetap muncul di pojok layar komputer Anda secara otomatis setiap 8 detik.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     SECTION 5: FITUR UNGGULAN & RUMUS EVALUASI KINERJA (KPI)
     ============================================================ -->
<div id="section-fitur" class="guide-section searchable-block">
    <div class="mb-3">
        <span class="guide-section-badge bg-danger text-white">Bagian 05 • Metodologi Evaluasi &amp; Fitur Unggulan</span>
        <h4 class="font-weight-bold text-gray-900 mb-1">
            <i class="fas fa-calculator text-danger mr-2"></i>Transparansi Perhitungan Skor, Predikat Kinerja, &amp; Log Waktu
        </h4>
        <p class="text-muted small mb-0">
            Penjelasan detail bagaimana SIMONTER menghitung kecepatan kerja, tingkat ketepatan waktu, skor peringkat, dan predikat evaluasi.
        </p>
    </div>

    <div class="row">
        <!-- Kolom Kiri: Rumus Skor & Metrik -->
        <div class="col-lg-6 mb-4">
            <div class="formula-box h-100 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge badge-info px-2 py-1 text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.6px;">
                        Algoritma Leaderboard SIMONTER
                    </span>
                    <i class="fas fa-award fa-lg text-warning"></i>
                </div>
                <h5 class="font-weight-bold text-white mb-2">Formula Perhitungan Skor &amp; Kecepatan Kerja</h5>
                <p class="small text-light mb-3" style="opacity: 0.85; line-height: 1.55;">
                    Pemeringkatan pada <strong>Matriks &amp; Peringkat Kinerja Seluruh Petugas</strong> dihitung secara otomatis berdasarkan kombinasi produktivitas (jumlah tugas tuntas) dan kedisiplinan waktu (<em>On-Time Rate</em>):
                </p>

                <div class="formula-code mb-3">
                    <div class="text-warning font-weight-bold mb-1">// 1. Skor Peringkat Pelaksana Tugas (Petugas/Kasubbag/Panmud)</div>
                    <div>Skor Pelaksana = (Tugas Selesai &times; 10) + (On-Time Rate % &times; 0.5)</div>
                    <hr style="border-color: rgba(255,255,255,0.15);" class="my-2">
                    <div class="text-warning font-weight-bold mb-1">// 2. Skor Peringkat Pemberi Tugas (Admin/Kasubbag/Panmud)</div>
                    <div>Skor Pemberi = (Tugas Diverifikasi &times; 10) + (Skor Kecepatan Respon &times; 0.35) + (First-Time Closed % &times; 0.25)</div>
                    <hr style="border-color: rgba(255,255,255,0.15);" class="my-2">
                    <div class="text-warning font-weight-bold mb-1">// 3. Kriteria Tepat Waktu &amp; Response Time Verifikasi</div>
                    <div>On-Time Pelaksana: Kirim Evidence &le; Tanggal Deadline (23:59:59 WIB)</div>
                    <div>Response Time Pemberi: Selisih Jam dari Kirim Balik &rarr; Keputusan Verifikasi (Closed / Revisi)</div>
                </div>

                <div class="small text-light" style="opacity: 0.85;">
                    <i class="fas fa-info-circle text-info mr-1"></i>
                    Seluruh pencatatan waktu di dalam aplikasi distandarisasi menggunakan zona waktu <strong>Asia/Jakarta (WIB / UTC+7)</strong> untuk menjamin akurasi evaluasi.
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Tabel Predikat Kinerja -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 1rem; overflow: hidden;">
                <div class="card-header py-3 bg-white border-bottom">
                    <h6 class="m-0 font-weight-bold text-gray-900">
                        <i class="fas fa-medal text-warning mr-2"></i>Klasifikasi Predikat Evaluasi Kinerja
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0 small">
                            <thead class="bg-light text-gray-800">
                                <tr>
                                    <th>Indikator Predikat</th>
                                    <th>Syarat Ketepatan Waktu</th>
                                    <th>Syarat Rata-Rata Durasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <span class="badge badge-success px-2 py-1">
                                            <i class="fas fa-bolt mr-1"></i> Sangat Cepat &amp; Tepat / Sangat Memuaskan
                                        </span>
                                    </td>
                                    <td class="font-weight-bold text-success">&ge; 85% (Leaderboard) / &ge; 90% (Pribadi)</td>
                                    <td class="font-weight-bold">&le; 3 Hari / Tugas</td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge badge-primary px-2 py-1">
                                            <i class="fas fa-check-circle mr-1"></i> Tepat Waktu / Baik &amp; Disiplin
                                        </span>
                                    </td>
                                    <td class="font-weight-bold text-primary">&ge; 75% Tepat Waktu</td>
                                    <td>Semua Durasi</td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge badge-warning text-dark px-2 py-1">
                                            <i class="fas fa-thumbs-up mr-1"></i> Cukup Baik
                                        </span>
                                    </td>
                                    <td class="font-weight-bold text-warning">&ge; 50% s.d. &lt; 75%</td>
                                    <td>Semua Durasi</td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge badge-danger px-2 py-1">
                                            <i class="fas fa-exclamation-circle mr-1"></i> Perlu Peningkatan
                                        </span>
                                    </td>
                                    <td class="font-weight-bold text-danger">&lt; 50% Tepat Waktu</td>
                                    <td>Melebihi Tenggat</td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge badge-secondary px-2 py-1">
                                            <i class="fas fa-minus-circle mr-1"></i> Belum Ada Tugas Selesai
                                        </span>
                                    </td>
                                    <td colspan="2" class="text-muted">Belum memiliki tugas yang diverifikasi Selesai pada periode terpilih.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 bg-light border-top small text-muted">
                        <strong><i class="fas fa-chart-area text-primary mr-1"></i> Filter Periode Fleksibel:</strong>
                        Pada Dashboard, Anda dapat menyaring metrik KPI berdasarkan <em>Semua Waktu</em>, <em>Bulan Ini</em>, <em>3 Bulan Terakhir</em>, atau <em>Tahun Ini</em>, serta mengubah rentang grafik tren (<em>1 Tahun, 6 Bulan, 3 Bulan, 1 Bulan</em>) secara interaktif tanpa memuat ulang halaman.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     SECTION 6: PERTANYAAN UMUM (FAQ) & TROUBLESHOOTING
     ============================================================ -->
<div id="section-faq" class="guide-section searchable-block">
    <div class="mb-3">
        <span class="guide-section-badge bg-secondary text-white">Bagian 06 • Tanya Jawab &amp; Solusi Kendala</span>
        <h4 class="font-weight-bold text-gray-900 mb-1">
            <i class="fas fa-question-circle text-primary mr-2"></i>Pertanyaan yang Sering Diajukan (FAQ)
        </h4>
        <p class="text-muted small mb-0">
            Solusi cepat apabila Anda menemui kendala saat mengoperasikan aplikasi SIMONTER.
        </p>
    </div>

    <div class="accordion" id="faqAccordion">
        <!-- FAQ 1 -->
        <div class="card border mb-2 shadow-sm" style="border-radius: 0.75rem; overflow: hidden;">
            <div class="card-header bg-white py-3" id="faqHeading1">
                <h6 class="mb-0">
                    <button class="btn btn-link text-left text-gray-900 font-weight-bold p-0 w-100 d-flex justify-content-between align-items-center text-decoration-none" type="button" data-toggle="collapse" data-target="#faqCollapse1" aria-expanded="true">
                        <span>1. Mengapa saat mengirim tindak lanjut muncul pesan gagal unggah berkas evidence?</span>
                        <i class="fas fa-chevron-down text-primary small"></i>
                    </button>
                </h6>
            </div>
            <div id="faqCollapse1" class="collapse show" aria-labelledby="faqHeading1" data-parent="#faqAccordion">
                <div class="card-body small text-muted bg-light">
                    Pastikan berkas yang diunggah memiliki format yang diizinkan (<code>PDF, DOC, DOCX, XLS, XLSX, ZIP, RAR, JPG, JPEG, PNG</code>) dengan ukuran maksimal <strong>10 MB</strong>. Apabila ukuran berkas melebihi 10 MB (misalnya video dokumentasi atau bundel arsip besar), unggahlah terlebih dahulu ke <em>Google Drive / Cloud Instansi</em> lalu masukkan tautannya pada kolom <strong>Link Evidence</strong>.
                </div>
            </div>
        </div>

        <!-- FAQ 2 -->
        <div class="card border mb-2 shadow-sm" style="border-radius: 0.75rem; overflow: hidden;">
            <div class="card-header bg-white py-3" id="faqHeading2">
                <h6 class="mb-0">
                    <button class="btn btn-link text-left text-gray-900 font-weight-bold p-0 w-100 d-flex justify-content-between align-items-center text-decoration-none collapsed" type="button" data-toggle="collapse" data-target="#faqCollapse2">
                        <span>2. Bagaimana cara mendelegasikan tugas kepada lebih dari 1 orang dan bagaimana aturan klasifikasi Kesekretariatan vs Kepaniteraan?</span>
                        <i class="fas fa-chevron-down text-primary small"></i>
                    </button>
                </h6>
            </div>
            <div id="faqCollapse2" class="collapse" aria-labelledby="faqHeading2" data-parent="#faqAccordion">
                <div class="card-body small text-muted bg-light">
                    Pada halaman <strong>Delegasikan Tugas Baru</strong> maupun modal <strong>Delegasikan ke Petugas</strong>, Admin, Kepala Sub Bagian, dan Panitera Muda dapat mencentang <strong>1 atau beberapa petugas pelaksana sekaligus</strong>. Seluruh user dapat melihat klasifikasi <strong>Kesekretariatan</strong> dan <strong>Kepaniteraan</strong> pada menu Data Tugas: tugas yang didelegasikan oleh <strong>Kepala Sub Bagian</strong> otomatis masuk ke klasifikasi <strong>Kesekretariatan</strong>, sedangkan tugas yang didelegasikan oleh <strong>Panitera Muda</strong> otomatis masuk ke klasifikasi <strong>Kepaniteraan</strong>.
                </div>
            </div>
        </div>

        <!-- FAQ 3 -->
        <div class="card border mb-2 shadow-sm" style="border-radius: 0.75rem; overflow: hidden;">
            <div class="card-header bg-white py-3" id="faqHeading3">
                <h6 class="mb-0">
                    <button class="btn btn-link text-left text-gray-900 font-weight-bold p-0 w-100 d-flex justify-content-between align-items-center text-decoration-none collapsed" type="button" data-toggle="collapse" data-target="#faqCollapse3">
                        <span>3. Apa perbedaan antara "Kembalikan untuk Revisi" dan "Buka Kembali (Reopen)"?</span>
                        <i class="fas fa-chevron-down text-primary small"></i>
                    </button>
                </h6>
            </div>
            <div id="faqCollapse3" class="collapse" aria-labelledby="faqHeading3" data-parent="#faqAccordion">
                <div class="card-body small text-muted bg-light">
                    <strong>Kembalikan untuk Revisi</strong> digunakan saat tugas masih berstatus <span class="badge badge-warning text-dark">Menunggu Konfirmasi</span> (belum disetujui selesai). Sedangkan <strong>Buka Kembali (Reopen)</strong> digunakan apabila tugas sudah terlanjur diverifikasi <span class="badge badge-success">Selesai (Closed)</span>, namun di kemudian hari ditemukan kekeliruan sehingga harus dibuka dan didelegasikan ulang kepada pelaksana yang bersangkutan.
                </div>
            </div>
        </div>

        <!-- FAQ 4 -->
        <div class="card border mb-2 shadow-sm" style="border-radius: 0.75rem; overflow: hidden;">
            <div class="card-header bg-white py-3" id="faqHeading4">
                <h6 class="mb-0">
                    <button class="btn btn-link text-left text-gray-900 font-weight-bold p-0 w-100 d-flex justify-content-between align-items-center text-decoration-none collapsed" type="button" data-toggle="collapse" data-target="#faqCollapse4">
                        <span>4. Bagaimana cara mengubah Nama Lengkap, NIP, atau Jabatan saya jika ada kesalahan penulisan?</span>
                        <i class="fas fa-chevron-down text-primary small"></i>
                    </button>
                </h6>
            </div>
            <div id="faqCollapse4" class="collapse" aria-labelledby="faqHeading4" data-parent="#faqAccordion">
                <div class="card-body small text-muted bg-light">
                    Demi integritas data kepegawaian, perubahan <strong>Nama Lengkap, NIP, Email, dan Jabatan</strong> hanya dapat dilakukan oleh <strong>Administrator</strong> melalui menu <em>Data User</em>. Adapun <strong>Foto Profil, Nomor Telepon/WA, dan Password</strong> dapat Anda ubah sendiri kapan saja melalui menu <em>Pengaturan Akun</em>.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Smooth scroll & active pill switching on quick nav
        const navLinks = document.querySelectorAll('#guideQuickNav .guide-nav-link');
        navLinks.forEach(function (link) {
            link.addEventListener('click', function (e) {
                const targetId = this.getAttribute('href');
                if (targetId && targetId.startsWith('#')) {
                    const targetEl = document.querySelector(targetId);
                    if (targetEl) {
                        e.preventDefault();
                        navLinks.forEach(function (l) { l.classList.remove('active'); });
                        this.classList.add('active');
                        targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });
        });

        // 2. Live Search Filter across guide sections
        const searchInput = document.getElementById('guideSearchInput');
        const searchableBlocks = document.querySelectorAll('.searchable-block');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const query = this.value.trim().toLowerCase();

                // If searching, expand all FAQ accordions and show all SOP tabs so content is visible
                if (query.length > 1) {
                    document.querySelectorAll('#faqAccordion .collapse').forEach(function (col) {
                        col.classList.add('show');
                    });
                }

                searchableBlocks.forEach(function (block) {
                    const text = block.textContent.toLowerCase();
                    if (!query || text.includes(query)) {
                        block.style.display = '';
                    } else {
                        block.style.display = 'none';
                    }
                });
            });
        }
    });
</script>
@endpush

