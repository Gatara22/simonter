<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>SIMONTER | BERANDA</title>
  <meta name="description" content="Sistem Monitoring Evaluasi Terpadu (SIMONTER) Pengadilan Agama Jakarta Utara">
  <meta name="keywords" content="SIMONTER, Monitoring Kinerja, Pengadilan Agama Jakarta Utara">

  <!-- Favicons -->
  <link href="{{ asset('images/simonter-s-favicon.svg') }}" rel="icon" type="image/svg+xml">
  <link href="{{ asset('images/simonter-s-favicon.svg') }}" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{ asset('enno/assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('enno/assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('enno/assets/vendor/aos/aos.css') }}" rel="stylesheet">
  <link href="{{ asset('enno/assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
  <link href="{{ asset('enno/assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="{{ asset('enno/assets/css/main.css') }}" rel="stylesheet">

  <style>
    :root {
      --simonter-dark: #0b1224;
      --simonter-indigo: #1e3a8a;
      --simonter-cyan: #06b6d4;
      --simonter-emerald: #10b981;
      --simonter-violet: #8b5cf6;
    }

    html {
      scroll-behavior: smooth;
    }

    /* Glassmorphic Header */
    #header.header {
      background: rgba(255, 255, 255, 0.92);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      border-bottom: 1px solid rgba(226, 232, 240, 0.8);
      box-shadow: 0 4px 20px rgba(15, 23, 42, 0.05);
    }

    #header .logo h1.sitename {
      font-weight: 800;
      letter-spacing: 0.02em;
      background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 55%, #06b6d4 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    #header .btn-getstarted {
      background: linear-gradient(135deg, #1d4ed8 0%, #06b6d4 100%);
      border: none;
      box-shadow: 0 6px 18px rgba(29, 78, 216, 0.25);
      font-weight: 600;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    #header .btn-getstarted:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 24px rgba(6, 182, 212, 0.35);
    }

    /* Dynamic Cosmic Aurora Hero */
    #hero.hero.section {
      position: relative;
      overflow: hidden;
      background: linear-gradient(135deg, #0b1224 0%, #172554 52%, #0e7490 100%);
      color: #ffffff;
      padding: 88px 0 112px;
      min-height: 78vh;
    }

    #hero.hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background-image: radial-gradient(rgba(148, 163, 184, 0.16) 1px, transparent 1px);
      background-size: 24px 24px;
      pointer-events: none;
    }

    .hero-aurora-orb {
      position: absolute;
      border-radius: 50%;
      filter: blur(70px);
      opacity: 0.45;
      pointer-events: none;
      animation: auroraPulse 9s ease-in-out infinite alternate;
    }

    .hero-aurora-orb.orb-cyan {
      width: 360px;
      height: 360px;
      top: -90px;
      right: -60px;
      background: #06b6d4;
    }

    .hero-aurora-orb.orb-emerald {
      width: 300px;
      height: 300px;
      bottom: -80px;
      left: -70px;
      background: #10b981;
      animation-delay: -4s;
    }

    @keyframes auroraPulse {
      0% { transform: translate(0, 0) scale(1); opacity: 0.35; }
      100% { transform: translate(18px, -16px) scale(1.12); opacity: 0.55; }
    }

    .hero-badge-pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(255, 255, 255, 0.11);
      border: 1px solid rgba(103, 232, 249, 0.32);
      color: #bae6fd;
      padding: 6px 14px;
      border-radius: 999px;
      font-size: 0.8rem;
      font-weight: 600;
      margin-bottom: 1rem;
      backdrop-filter: blur(8px);
    }

    .hero-live-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #34d399;
      box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.28);
    }

    #hero h1 {
      color: #ffffff !important;
      font-size: clamp(2.4rem, 4.4vw, 3.6rem);
      font-weight: 800;
      letter-spacing: -0.02em;
      margin-bottom: 0.65rem;
    }

    .hero-dynamic-subtitle {
      font-size: clamp(1.05rem, 1.8vw, 1.32rem);
      color: #e2e8f0 !important;
      min-height: 2.1rem;
      font-weight: 500;
      margin-bottom: 1.5rem;
      transition: opacity 0.35s ease, transform 0.35s ease;
    }

    .hero-dynamic-subtitle.is-fading {
      opacity: 0;
      transform: translateY(6px);
    }

    #hero .btn-get-started {
      background: linear-gradient(135deg, #22d3ee 0%, #10b981 100%);
      color: #06283d;
      font-weight: 700;
      border: none;
      box-shadow: 0 10px 24px rgba(16, 185, 129, 0.32);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    #hero .btn-get-started:hover {
      transform: translateY(-2px);
      box-shadow: 0 14px 28px rgba(34, 211, 238, 0.42);
      color: #06283d;
    }

    #hero .btn-watch-video {
      color: #f8fafc !important;
      margin-left: 18px;
      font-weight: 600;
    }

    #hero .btn-watch-video i {
      color: #67e8f9 !important;
    }

    .hero-trust-chips {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-top: 1.6rem;
    }

    .hero-trust-chip {
      font-size: 0.78rem;
      padding: 5px 12px;
      border-radius: 999px;
      background: rgba(15, 23, 42, 0.45);
      border: 1px solid rgba(148, 163, 184, 0.28);
      color: #cbd5e1;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    /* Interactive Hero Preview Card */
    .hero-preview-stage {
      position: relative;
      padding: 1.1rem;
      border-radius: 1.35rem;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.18);
      backdrop-filter: blur(12px);
      box-shadow: 0 24px 50px rgba(2, 6, 23, 0.42);
    }

    .hero-preview-stage img {
      border-radius: 0.9rem;
    }

    .hero-floating-chip {
      position: absolute;
      background: rgba(255, 255, 255, 0.96);
      color: #0f172a;
      border-radius: 0.85rem;
      padding: 0.55rem 0.85rem;
      box-shadow: 0 12px 26px rgba(15, 23, 42, 0.25);
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.76rem;
      font-weight: 700;
      z-index: 3;
      animation: floatChip 4.5s ease-in-out infinite;
    }

    .hero-floating-chip.chip-top {
      top: -14px;
      right: 16px;
    }

    .hero-floating-chip.chip-bottom {
      bottom: -14px;
      left: 16px;
      animation-delay: -2.2s;
    }

    .hero-chip-icon {
      width: 28px;
      height: 28px;
      border-radius: 0.55rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: #ffffff;
      font-size: 0.85rem;
    }

    @keyframes floatChip {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-6px); }
    }

    /* Animated Stats Strip */
    .highlights-strip {
      position: relative;
      margin-top: -54px;
      z-index: 10;
      padding-bottom: 24px;
    }

    .highlight-stat-card {
      background: #ffffff;
      border-radius: 1rem;
      padding: 1.25rem 1.35rem;
      border: 1px solid #e2e8f0;
      box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
      height: 100%;
      display: flex;
      align-items: center;
      gap: 1rem;
      transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
    }

    .highlight-stat-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 16px 34px rgba(29, 78, 216, 0.12);
      border-color: #93c5fd;
    }

    .highlight-stat-icon {
      width: 48px;
      height: 48px;
      min-width: 48px;
      border-radius: 0.85rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: #ffffff;
      font-size: 1.3rem;
    }

    .highlight-stat-num {
      font-size: 1.55rem;
      font-weight: 800;
      color: #0f172a;
      line-height: 1.1;
    }

    .highlight-stat-label {
      font-size: 0.8rem;
      color: #64748b;
      font-weight: 500;
      margin-top: 2px;
    }

    /* Interactive Feature Cards */
    .feature-card-modern {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 1.1rem;
      padding: 1.6rem 1.45rem;
      height: 100%;
      transition: transform 0.24s ease, box-shadow 0.24s ease, border-color 0.24s ease;
      position: relative;
      overflow: hidden;
    }

    .feature-card-modern::after {
      content: '';
      position: absolute;
      left: 0;
      right: 0;
      bottom: 0;
      height: 3px;
      background: linear-gradient(90deg, #1d4ed8, #06b6d4, #10b981);
      opacity: 0;
      transition: opacity 0.24s ease;
    }

    .feature-card-modern:hover {
      transform: translateY(-6px);
      box-shadow: 0 18px 36px rgba(15, 23, 42, 0.09);
      border-color: #cbd5e1;
    }

    .feature-card-modern:hover::after {
      opacity: 1;
    }

    .feature-icon-tile {
      width: 46px;
      height: 46px;
      border-radius: 0.85rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: #ffffff;
      font-size: 1.25rem;
      margin-bottom: 1rem;
      box-shadow: 0 8px 18px rgba(15, 23, 42, 0.14);
    }

    .feature-card-modern h4 {
      font-size: 1.05rem;
      font-weight: 700;
      color: #0f172a;
      margin-bottom: 0.55rem;
    }

    .feature-card-modern p {
      font-size: 0.88rem;
      color: #64748b;
      margin-bottom: 0;
      line-height: 1.55;
    }

    /* Photo-Free Executive Testimonials */
    .testimonials.section {
      background: linear-gradient(180deg, #f8fafc 0%, #eff6ff 100%);
    }

    .testimonials .swiper-slide {
      height: auto;
      display: flex;
    }

    .testimonial-card-modern {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 1.15rem;
      padding: 1.6rem 1.5rem 1.35rem;
      width: 100%;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      box-shadow: 0 10px 26px rgba(15, 23, 42, 0.06);
      position: relative;
      overflow: hidden;
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .testimonial-card-modern::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #1d4ed8 0%, #06b6d4 55%, #10b981 100%);
    }

    .testimonial-card-modern:hover {
      transform: translateY(-5px);
      box-shadow: 0 18px 38px rgba(29, 78, 216, 0.11);
    }

    .testimonial-top-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 0.9rem;
    }

    .testimonial-stars {
      color: #f59e0b;
      font-size: 0.82rem;
      letter-spacing: 1px;
    }

    .testimonial-role-tag {
      font-size: 0.7rem;
      font-weight: 700;
      padding: 0.22rem 0.65rem;
      border-radius: 999px;
      background: #eff6ff;
      color: #1d4ed8;
      border: 1px solid #bfdbfe;
    }

    .testimonial-quote-body {
      font-size: 0.9rem;
      line-height: 1.65;
      color: #334155;
      font-style: italic;
      margin-bottom: 1.35rem;
      position: relative;
      flex-grow: 1;
    }

    .testimonial-quote-mark {
      font-size: 1.5rem;
      color: #38bdf8;
      opacity: 0.65;
      vertical-align: middle;
      margin-right: 4px;
    }

    .testimonial-author-footer {
      border-top: 1px solid #f1f5f9;
      padding-top: 0.95rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .testimonial-author-accent {
      width: 4px;
      height: 36px;
      border-radius: 999px;
      background: linear-gradient(180deg, #06b6d4 0%, #10b981 100%);
      flex-shrink: 0;
    }

    .testimonial-author-footer h3 {
      font-size: 0.96rem;
      font-weight: 700;
      color: #0f172a;
      margin: 0 0 2px;
    }

    .testimonial-author-footer h4 {
      font-size: 0.78rem;
      color: #64748b;
      font-weight: 500;
      margin: 0;
    }

    /* Team Card Polish */
    .team .member {
      position: relative;
      margin-bottom: 50px;
    }
    .team .member .pic {
      overflow: hidden;
      border-radius: 14px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
      aspect-ratio: 1 / 1;
      background-color: #f8f9fa;
    }
    .team .member .pic img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: top center;
      transition: transform 0.4s ease;
    }
    .team .member:hover .pic img {
      transform: scale(1.05);
    }
    .team .member .member-info {
      background: #ffffff;
      border-radius: 12px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
      position: absolute;
      bottom: -45px;
      left: 15px;
      right: 15px;
      padding: 16px 18px;
      min-height: 105px;
      transition: all 0.3s ease;
      border-bottom: 3px solid var(--simonter-emerald);
    }
    .team .member:hover .member-info {
      bottom: -40px;
      box-shadow: 0 14px 28px rgba(0, 0, 0, 0.12);
    }
    .team .member h4 {
      font-weight: 700;
      font-size: 15px;
      line-height: 1.35;
      margin-bottom: 6px;
      min-height: 40px;
      display: flex;
      align-items: center;
      color: #2c384e;
    }
    .team .member span {
      display: block;
      font-size: 12.5px;
      color: #6c757d;
      font-style: italic;
      font-weight: 500;
    }
    .team .member .social {
      position: absolute;
      right: 15px;
      bottom: 12px;
    }
    .team .member .social a {
      color: #8c97a8;
      margin-left: 5px;
      font-size: 15px;
      transition: color 0.2s;
    }
    .team .member .social a:hover {
      color: var(--simonter-cyan);
    }

    /* Footer Polish */
    #footer.footer {
      background: #0b1224;
      color: #cbd5e1;
      padding: 28px 0;
    }
    #footer.footer .credits,
    #footer.footer p {
      color: #cbd5e1;
      font-size: 0.86rem;
    }
    #footer.footer a {
      color: #38bdf8;
    }

    @media (prefers-reduced-motion: reduce) {
      .hero-aurora-orb,
      .hero-floating-chip {
        animation: none !important;
      }
    }
  </style>
</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

      <a href="#hero" class="logo d-flex align-items-center me-auto">
        @include('partials.simonter-logo', ['size' => 38, 'variant' => 'color', 'uid' => 'welcome', 'class' => 'me-2'])
        <h1 class="sitename">SiMonter</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="#hero" class="active">Beranda</a></li>
          <li><a href="#features">Fitur</a></li>
          <li><a href="#about">Tentang Kami</a></li>
          <li><a href="#testimonials">Testimoni</a></li>
          <li><a href="#team">Tim</a></li>
          <li><a href="#contact">Kontak</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      @auth
        <a class="btn-getstarted" href="{{ route('dashboard') }}">Dashboard</a>
      @else
        <a class="btn-getstarted" href="{{ route('login') }}">Login</a>
      @endauth

    </div>
  </header>

  <main class="main">

    <!-- Dynamic Hero Section -->
    <section id="hero" class="hero section">
      <div class="hero-aurora-orb orb-cyan"></div>
      <div class="hero-aurora-orb orb-emerald"></div>

      <div class="container position-relative" style="z-index: 2;">
        <div class="row gy-5 align-items-center">
          <div class="col-lg-6 order-2 order-lg-1 d-flex flex-column justify-content-center" data-aos="fade-up">
            <div>
              <span class="hero-badge-pill">
                <span class="hero-live-dot"></span>
                <span>Pengadilan Agama Jakarta Utara</span>
              </span>
            </div>

            <h1>SiMonter</h1>
            <p id="heroDynamicText" class="hero-dynamic-subtitle">
              Sistem Monitoring Evaluasi Terpadu
            </p>

            <div class="d-flex flex-wrap align-items-center gap-2">
              @auth
                <a href="{{ route('dashboard') }}" class="btn-get-started">
                  <i class="bi bi-speedometer2 me-1"></i> Buka Dashboard
                </a>
              @else
                <a href="{{ route('login') }}" class="btn-get-started">
                  <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Aplikasi
                </a>
              @endauth
              <a href="https://www.youtube.com/watch?v=Y7f98aduVJ8" class="glightbox btn-watch-video d-flex align-items-center">
                <i class="bi bi-play-circle"></i><span>Video Tutorial Penggunaan</span>
              </a>
            </div>

            <div class="hero-trust-chips">
              <span class="hero-trust-chip"><i class="bi bi-building text-info"></i> Kesekretariatan &amp; Kepaniteraan</span>
              <span class="hero-trust-chip"><i class="bi bi-people text-success"></i> Multi-Petugas Kolaborasi</span>
              <span class="hero-trust-chip"><i class="bi bi-chat-dots text-warning"></i> Chat Progres &amp; Log URL</span>
            </div>
          </div>

          <div class="col-lg-6 order-1 order-lg-2 hero-img" data-aos="zoom-out" data-aos-delay="100">
            <div class="hero-preview-stage">
              <div class="hero-floating-chip chip-top d-none d-sm-flex">
                <span class="hero-chip-icon" style="background: linear-gradient(135deg, #0284c7, #38bdf8);">
                  <i class="bi bi-graph-up-arrow"></i>
                </span>
                <div>
                  <div style="font-size: 0.72rem; color: #0f172a;">Matriks Kinerja</div>
                  <div style="font-size: 0.65rem; color: #059669; font-weight: 600;">Otomatis &amp; Real-Time</div>
                </div>
              </div>

              <img src="{{ asset('enno/assets/img/hero-img.png') }}" class="img-fluid animated" alt="Ilustrasi Dashboard Monitoring SIMONTER">

              <div class="hero-floating-chip chip-bottom d-none d-sm-flex">
                <span class="hero-chip-icon" style="background: linear-gradient(135deg, #059669, #34d399);">
                  <i class="bi bi-shield-check"></i>
                </span>
                <div>
                  <div style="font-size: 0.72rem; color: #0f172a;">Audit Trail &amp; HOLD</div>
                  <div style="font-size: 0.65rem; color: #64748b; font-weight: 600;">Transparan &amp; Terukur</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section><!-- /Hero Section -->

    <!-- Animated Key Metrics Strip -->
    <section class="highlights-strip">
      <div class="container">
        <div class="row g-3">
          <div class="col-lg-3 col-sm-6" data-aos="fade-up" data-aos-delay="50">
            <div class="highlight-stat-card">
              <div class="highlight-stat-icon" style="background: linear-gradient(135deg, #1d4ed8, #38bdf8);">
                <i class="bi bi-diagram-3"></i>
              </div>
              <div>
                <div class="highlight-stat-num">
                  <span data-purecounter-start="0" data-purecounter-end="2" data-purecounter-duration="1" class="purecounter">2</span> Bidang
                </div>
                <div class="highlight-stat-label">Kesekretariatan &amp; Kepaniteraan</div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-sm-6" data-aos="fade-up" data-aos-delay="100">
            <div class="highlight-stat-card">
              <div class="highlight-stat-icon" style="background: linear-gradient(135deg, #059669, #34d399);">
                <i class="bi bi-person-badge"></i>
              </div>
              <div>
                <div class="highlight-stat-num">
                  <span data-purecounter-start="0" data-purecounter-end="4" data-purecounter-duration="1" class="purecounter">4</span> Peran
                </div>
                <div class="highlight-stat-label">Admin, Kasubbag, Panmud &amp; Petugas</div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-sm-6" data-aos="fade-up" data-aos-delay="150">
            <div class="highlight-stat-card">
              <div class="highlight-stat-icon" style="background: linear-gradient(135deg, #7c3aed, #a78bfa);">
                <i class="bi bi-journal-check"></i>
              </div>
              <div>
                <div class="highlight-stat-num">
                  <span data-purecounter-start="0" data-purecounter-end="100" data-purecounter-duration="1" class="purecounter">100</span>%
                </div>
                <div class="highlight-stat-label">Log Aktivitas &amp; Link URL</div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-sm-6" data-aos="fade-up" data-aos-delay="200">
            <div class="highlight-stat-card">
              <div class="highlight-stat-icon" style="background: linear-gradient(135deg, #d97706, #fbbf24);">
                <i class="bi bi-bell"></i>
              </div>
              <div>
                <div class="highlight-stat-num">Real-Time</div>
                <div class="highlight-stat-label">Push Notifikasi &amp; Chat Progres</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Interactive Feature Highlights Section -->
    <section id="features" class="section pt-4 pb-5">
      <div class="container section-title" data-aos="fade-up">
        <span>Keunggulan Sistem</span>
        <h2>Fitur Unggulan SiMonter</h2>
        <p>Dirancang khusus untuk mempercepat delegasi tugas, kolaborasi tim pelaksana, dan evaluasi kinerja yang objektif</p>
      </div>

      <div class="container">
        <div class="row g-4">
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="50">
            <div class="feature-card-modern">
              <div class="feature-icon-tile" style="background: linear-gradient(135deg, #1d4ed8, #38bdf8);">
                <i class="bi bi-people-fill"></i>
              </div>
              <h4>Kolaborasi Multi-Petugas</h4>
              <p>Satu tugas dapat dikerjakan secara kolaboratif oleh PIC Utama dan beberapa petugas pendukung dengan kredit kinerja yang adil berdasarkan kontribusi aktif.</p>
            </div>
          </div>

          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="feature-card-modern">
              <div class="feature-icon-tile" style="background: linear-gradient(135deg, #059669, #34d399);">
                <i class="bi bi-chat-square-text-fill"></i>
              </div>
              <h4>Chat Progres &amp; Log URL</h4>
              <p>Author dan tim petugas berdiskusi progres serta mengunggah file langsung menjadi tautan URL di Log Aktivitas tanpa otomatis memicu status tinjauan.</p>
            </div>
          </div>

          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="150">
            <div class="feature-card-modern">
              <div class="feature-icon-tile" style="background: linear-gradient(135deg, #7c3aed, #a78bfa);">
                <i class="bi bi-layers-fill"></i>
              </div>
              <h4>Klasifikasi Kesekretariatan &amp; Kepaniteraan</h4>
              <p>Pemilahan otomatis delegasi dari Kepala Sub Bagian (Kesekretariatan) dan Panitera Muda (Kepaniteraan) yang transparan bagi seluruh pegawai.</p>
            </div>
          </div>

          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
            <div class="feature-card-modern">
              <div class="feature-icon-tile" style="background: linear-gradient(135deg, #475569, #64748b);">
                <i class="bi bi-pause-circle-fill"></i>
              </div>
              <h4>Status HOLD &amp; Ceklist Permohonan</h4>
              <p>Petugas dapat mengajukan permohonan HOLD atau perpanjangan deadline; durasi HOLD tidak dihitung sebagai keterlambatan performa.</p>
            </div>
          </div>

          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="250">
            <div class="feature-card-modern">
              <div class="feature-icon-tile" style="background: linear-gradient(135deg, #d97706, #fbbf24);">
                <i class="bi bi-trophy-fill"></i>
              </div>
              <h4>Matriks &amp; Peringkat Kinerja</h4>
              <p>Pemeringkatan otomatis baik untuk Pelaksana Tugas maupun Pemberi Tugas (Respond Time, Rasio Langsung Closed vs Revisi).</p>
            </div>
          </div>

          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
            <div class="feature-card-modern">
              <div class="feature-icon-tile" style="background: linear-gradient(135deg, #e11d48, #fb7185);">
                <i class="bi bi-file-earmark-bar-graph-fill"></i>
              </div>
              <h4>Export Laporan Excel &amp; PDF</h4>
              <p>Cetak rekapitulasi tugas dan kinerja per periode, klasifikasi, status, ataupun petugas dalam format Excel dan PDF resmi siap lapor.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about section light-background">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <span>Tentang Kami<br></span>
        <h2>Tentang Kami</h2>
        <p>SiMonter (Sistem Monitoring Evaluasi Terpadu) dikembangkan sebagai langkah strategis Pengadilan Agama Jakarta Utara</p>
        <p>dalam mendukung transformasi digital dan transparansi kinerja peradilan.</p>
      </div><!-- End Section Title -->

      <div class="container">

        <div class="row gy-4 align-items-center">
          <div class="col-lg-6 position-relative align-self-start" data-aos="fade-up" data-aos-delay="100">
            <img src="{{ asset('enno/assets/img/about.png') }}" class="img-fluid rounded-4 shadow-sm" alt="Tentang Aplikasi SIMONTER">
            <a href="https://www.youtube.com/watch?v=Y7f98aduVJ8" class="glightbox pulsating-play-btn" aria-label="Putar Video Tentang SiMonter"></a>
          </div>
          <div class="col-lg-6 content" data-aos="fade-up" data-aos-delay="200">
            <h3>SiMonter</h3>
            <h5 class="fst-italic text-primary">Sistem Monitoring Evaluasi Terpadu</h5>
            <p>
              Kehadiran aplikasi ini bertujuan untuk mengoptimalkan pengawasan internal, mempercepat proses evaluasi kinerja aparatur secara berkala, serta memastikan seluruh evaluasi pekerjaan bidang kesekretariatan berjalan secara terintegrasi, akuntabel, dan efisien sesuai dengan prinsip tata kelola pemerintahan yang baik. Aplikasi SIMONTER lahir dari analisis mendalam terhadap kendala pengawasan internal pada 3 bidang kesekretariatan.
            </p>
            <ul>
              <li><i class="bi bi-check2-all"></i> <span>Integrasi data monitoring bidang <strong>Kesekretariatan</strong> dan <strong>Kepaniteraan</strong> dalam satu panel kontrol terpadu berbasis indikator kinerja terstandar.</span></li>
              <li><i class="bi bi-check2-all"></i> <span>Alur delegasi multi-penerima, status penundaan (HOLD), dan persetujuan tindak lanjut yang menghasilkan evaluasi kinerja cepat, valid, dan akurat.</span></li>
              <li><i class="bi bi-check2-all"></i> <span>Pendokumentasian pekerjaan, penunjukan PIC, penetapan batas waktu, dan penelusuran matriks tindak lanjut terpadu.</span></li>
            </ul>
            <p>
              SIMONTER bukan sekadar inovasi aplikasi, melainkan instrumen strategis untuk memperkuat fungsi koordinasi dan pengendalian Sekretaris Pengadilan Agama Jakarta Utara.
            </p>
          </div>
        </div>

      </div>

    </section><!-- /About Section -->

    <!-- Photo-Free Testimonials Section -->
    <section id="testimonials" class="testimonials section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <span>Testimoni</span>
        <h2>Testimoni Stakeholder</h2>
        <p>Apresiasi dan pengalaman nyata pimpinan serta tim efektif dalam memanfaatkan ekosistem SIMONTER</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="swiper init-swiper" data-speed="600" data-delay="5000" data-breakpoints="{ &quot;320&quot;: { &quot;slidesPerView&quot;: 1, &quot;spaceBetween&quot;: 24 }, &quot;768&quot;: { &quot;slidesPerView&quot;: 2, &quot;spaceBetween&quot;: 24 }, &quot;1200&quot;: { &quot;slidesPerView&quot;: 3, &quot;spaceBetween&quot;: 24 } }">
          <script type="application/json" class="swiper-config">
            {
              "loop": true,
              "speed": 600,
              "autoplay": {
                "delay": 5000
              },
              "slidesPerView": "auto",
              "pagination": {
                "el": ".swiper-pagination",
                "type": "bullets",
                "clickable": true
              },
              "breakpoints": {
                "320": {
                  "slidesPerView": 1,
                  "spaceBetween": 24
                },
                "768": {
                  "slidesPerView": 2,
                  "spaceBetween": 24
                },
                "1200": {
                  "slidesPerView": 3,
                  "spaceBetween": 24
                }
              }
            }
          </script>
          <div class="swiper-wrapper">

            <div class="swiper-slide">
              <div class="testimonial-card-modern">
                <div>
                  <div class="testimonial-top-row">
                    <div class="testimonial-stars" aria-label="Rating 5 dari 5">
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                    </div>
                    <span class="testimonial-role-tag">Pimpinan</span>
                  </div>
                  <p class="testimonial-quote-body">
                    <i class="bi bi-quote testimonial-quote-mark"></i>
                    Hadirnya SIMONTER memberikan transparansi penuh terhadap kinerja seluruh divisi. Dashboard terpadunya sangat membantu saya dalam mengambil keputusan strategis secara cepat berbasis data riil tanpa perlu menunggu rekapitulasi manual.
                  </p>
                </div>
                <div class="testimonial-author-footer">
                  <span class="testimonial-author-accent"></span>
                  <div>
                    <h3>Dr. Khoiriyah Roihan, S.Ag., M.H.</h3>
                    <h4>Ketua PA Jakarta Utara &amp; Penanggung Jawab Tim</h4>
                  </div>
                </div>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-card-modern">
                <div>
                  <div class="testimonial-top-row">
                    <div class="testimonial-stars" aria-label="Rating 5 dari 5">
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                    </div>
                    <span class="testimonial-role-tag">Kesekretariatan</span>
                  </div>
                  <p class="testimonial-quote-body">
                    <i class="bi bi-quote testimonial-quote-mark"></i>
                    Monitoring progres kegiatan jadi jauh lebih efisien. Fitur pelaporan berjenjang dan notifikasi di SIMONTER memudahkan kami mengidentifikasi kendala di lapangan lebih awal sehingga tindakan korektif bisa langsung dilakukan.
                  </p>
                </div>
                <div class="testimonial-author-footer">
                  <span class="testimonial-author-accent"></span>
                  <div>
                    <h3>Yuli, S.Ag., M.H.</h3>
                    <h4>Sekretaris &amp; Ketua Tim</h4>
                  </div>
                </div>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-card-modern">
                <div>
                  <div class="testimonial-top-row">
                    <div class="testimonial-stars" aria-label="Rating 5 dari 5">
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                    </div>
                    <span class="testimonial-role-tag">Kepaniteraan</span>
                  </div>
                  <p class="testimonial-quote-body">
                    <i class="bi bi-quote testimonial-quote-mark"></i>
                    Aplikasi ini sangat menghemat waktu. Penyusunan laporan evaluasi bulanan dan tahunan yang dulunya memakan waktu seminggu, kini bisa diselesaikan dalam hitungan jam karena semua data sudah terintegrasi dengan baik.
                  </p>
                </div>
                <div class="testimonial-author-footer">
                  <span class="testimonial-author-accent"></span>
                  <div>
                    <h3>Dede Supriadi, S.H., M.H.</h3>
                    <h4>Panitera</h4>
                  </div>
                </div>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-card-modern">
                <div>
                  <div class="testimonial-top-row">
                    <div class="testimonial-stars" aria-label="Rating 5 dari 5">
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                    </div>
                    <span class="testimonial-role-tag">Tim Efektif</span>
                  </div>
                  <p class="testimonial-quote-body">
                    <i class="bi bi-quote testimonial-quote-mark"></i>
                    Tampilan antarmuka SIMONTER sangat ramah pengguna dan mudah dipahami. Proses input data kegiatan bulanan jadi lebih terstruktur dan tidak membingungkan, bahkan untuk pengguna baru.
                  </p>
                </div>
                <div class="testimonial-author-footer">
                  <span class="testimonial-author-accent"></span>
                  <div>
                    <h3>Ade Irma Suryani, S.H.</h3>
                    <h4>Kasubag PTIP &amp; Tim Efektif</h4>
                  </div>
                </div>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-card-modern">
                <div>
                  <div class="testimonial-top-row">
                    <div class="testimonial-stars" aria-label="Rating 5 dari 5">
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                    </div>
                    <span class="testimonial-role-tag">Pengembang</span>
                  </div>
                  <p class="testimonial-quote-body">
                    <i class="bi bi-quote testimonial-quote-mark"></i>
                    Secara teknis, SIMONTER memiliki performa yang stabil dan manajemen hak akses yang rapi. Integrasi data antar-unit kerja berjalan mulus tanpa mengabaikan aspek keamanan informasi.
                  </p>
                </div>
                <div class="testimonial-author-footer">
                  <span class="testimonial-author-accent"></span>
                  <div>
                    <h3>Reza Fahlevi, S.Kom.</h3>
                    <h4>Developer &amp; Tim Efektif</h4>
                  </div>
                </div>
              </div>
            </div><!-- End testimonial item -->

          </div>
          <div class="swiper-pagination mt-4"></div>
        </div>

      </div>

    </section><!-- /Testimonials Section -->

    <!-- Team Section -->
    <section id="team" class="team section light-background">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <span>Tim SiMonter</span>
        <h2>Tim SiMonter</h2>
        <p>"Tim profesional yang berkomitmen menghadirkan transparansi, efisiensi, dan efektivitas pengawasan kinerja yang efektif di Pengadilan Agama Jakarta Utara"</p>
      </div><!-- End Section Title -->

      <div class="container">

        <div class="row gy-5 justify-content-center">

          <!-- Team Member 1 -->
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="member">
              <div class="pic">
                <img src="{{ asset('enno/assets/img/team/team-1.jpg') }}?v={{ file_exists(public_path('enno/assets/img/team/team-1.jpg')) ? filemtime(public_path('enno/assets/img/team/team-1.jpg')) : '1' }}" class="img-fluid" alt="Dr. KHOIRIYAH ROIHAN, S.Ag., M.H.">
              </div>
              <div class="member-info">
                <h4>Dr. Khoiriyah Roihan, S.Ag., M.H.</h4>
                <span>Penanggung Jawab Tim</span>
                <div class="social">
                  <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                  <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                  <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                  <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                </div>
              </div>
            </div>
          </div><!-- End Team Member -->

          <!-- Team Member 2 -->
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
            <div class="member">
              <div class="pic">
                <img src="{{ asset('enno/assets/img/team/team-2.jpg') }}?v={{ file_exists(public_path('enno/assets/img/team/team-2.jpg')) ? filemtime(public_path('enno/assets/img/team/team-2.jpg')) : '1' }}" class="img-fluid" alt="YULI, S.Ag., M.H.">
              </div>
              <div class="member-info">
                <h4>Yuli, S.Ag., M.H.</h4>
                <span>Ketua Tim</span>
                <div class="social">
                  <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                  <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                  <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                  <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                </div>
              </div>
            </div>
          </div><!-- End Team Member -->

          <!-- Team Member 3 -->
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
            <div class="member">
              <div class="pic">
                <img src="{{ asset('enno/assets/img/team/team-3.jpg') }}?v={{ file_exists(public_path('enno/assets/img/team/team-3.jpg')) ? filemtime(public_path('enno/assets/img/team/team-3.jpg')) : '1' }}" class="img-fluid" alt="Reza Fahlevi, S.Kom.">
              </div>
              <div class="member-info">
                <h4>Reza Fahlevi, S.Kom.</h4>
                <span>Tim Efektif / Pengembang</span>
                <div class="social">
                  <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                  <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                  <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                  <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                </div>
              </div>
            </div>
          </div><!-- End Team Member -->

        </div><!-- End Row -->

      </div><!-- End Container -->

    </section><!-- /Team Section -->

    <!-- Contact Section -->
    <section id="contact" class="contact section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <span>Kontak PAJU</span>
        <h2>Kontak</h2>
        <p>Jika terdapat pertanyaan lebih lanjut seputar Pengadilan Agama Jakarta Utara, hubungi kami disini</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row g-3 mb-4">
          <div class="col-lg-5">
            <div class="info-item d-flex h-100 rounded-4 border p-3 bg-white shadow-sm" data-aos="fade-up" data-aos-delay="150">
              <i class="bi bi-geo-alt flex-shrink-0"></i>
              <div>
                <h3>Alamat</h3>
                <p>Jl. Raya Plumpang Semper No.5, RT.007 RW.002, Kel. Tugu Selatan, Kec. Koja, Kota Jakarta Utara, DKI Jakarta – 14260</p>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6">
            <div class="info-item d-flex h-100 rounded-4 border p-3 bg-white shadow-sm" data-aos="fade-up" data-aos-delay="200">
              <i class="bi bi-telephone flex-shrink-0"></i>
              <div>
                <h3>Hubungi Kami</h3>
                <p>+62 21 4393 4701</p>
              </div>
            </div>
          </div>

          <div class="col-lg-4 col-md-6">
            <div class="info-item d-flex h-100 rounded-4 border p-3 bg-white shadow-sm" data-aos="fade-up" data-aos-delay="250">
              <i class="bi bi-envelope flex-shrink-0"></i>
              <div>
                <h3>Email</h3>
                <p>sekretariat.pajakartautara@gmail.com<br>kepegawaianpaju@gmail.com</p>
              </div>
            </div>
          </div>
        </div>

        <div class="info-wrap rounded-4 overflow-hidden shadow-sm border p-2 bg-white">
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3967.0079643704225!2d106.90680107369857!3d-6.129629560096545!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6a1ffe9359ed3d%3A0x34cd66db920dd513!2sNorth%20Jakarta%20Religious%20Court!5e0!3m2!1sen!2sid!4v1789389922075!5m2!1sen!2sid" width="100%" height="380" style="border:0; border-radius:12px;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
        </div>

      </div>

    </section><!-- /Contact Section -->

  </main>

  <footer id="footer" class="footer">

    <div class="container copyright text-center">
      <div class="d-flex justify-content-center align-items-center gap-2 mb-2">
        @include('partials.simonter-logo', ['size' => 28, 'variant' => 'color', 'uid' => 'footer'])
        <strong class="text-white">SiMonter ver 1.0</strong>
      </div>
      <p class="mb-1">&copy; <span>Copyright</span> <strong class="px-1 sitename text-white">Pengadilan Agama Jakarta Utara</strong> <span>2026</span></p>
      <div class="credits">
        Designed by <a href="https://pa-jakartautara.go.id/" target="_blank" rel="noopener">Reza</a> Distributed by <a href="https://pa-jakartautara.go.id/" target="_blank" rel="noopener">iMal</a>
      </div>
    </div>

  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="{{ asset('enno/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('enno/assets/vendor/php-email-form/validate.js') }}"></script>
  <script src="{{ asset('enno/assets/vendor/aos/aos.js') }}"></script>
  <script src="{{ asset('enno/assets/vendor/glightbox/js/glightbox.min.js') }}"></script>
  <script src="{{ asset('enno/assets/vendor/purecounter/purecounter_vanilla.js') }}"></script>
  <script src="{{ asset('enno/assets/vendor/imagesloaded/imagesloaded.pkgd.min.js') }}"></script>
  <script src="{{ asset('enno/assets/vendor/isotope-layout/isotope.pkgd.min.js') }}"></script>
  <script src="{{ asset('enno/assets/vendor/swiper/swiper-bundle.min.js') }}"></script>
  <script src="{{ asset('enno/assets/js/main.js') }}"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const el = document.getElementById('heroDynamicText');
      if (!el || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
      }

      const taglines = [
        'Sistem Monitoring Evaluasi Terpadu',
        'Kolaborasi Multi-Petugas & Chat Progres Real-Time',
        'Transparansi Kinerja Cepat, Terukur & Akuntabel'
      ];
      let idx = 0;

      setInterval(function () {
        el.classList.add('is-fading');
        setTimeout(function () {
          idx = (idx + 1) % taglines.length;
          el.textContent = taglines[idx];
          el.classList.remove('is-fading');
        }, 320);
      }, 3600);
    });
  </script>

</body>

</html>
