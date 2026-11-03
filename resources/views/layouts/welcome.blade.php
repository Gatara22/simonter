<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>SIMONTER | BERANDA</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="{{asset('enno/assets/img/favicon.png')}}" rel="icon">
  <link href="{{asset('enno/assets/img/apple-touch-icon.png')}}" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{asset('enno/assets/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
  <link href="{{asset('enno/assets/vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
  <link href="{{asset('enno/assets/vendor/aos/aos.css')}}" rel="stylesheet">
  <link href="{{asset('enno/assets/vendor/glightbox/css/glightbox.min.css')}}" rel="stylesheet">
  <link href="{{asset('enno/assets/vendor/swiper/swiper-bundle.min.css')}}" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="{{asset('enno/assets/css/main.css')}}" rel="stylesheet">

  <style>
    .team .member {
      position: relative;
      margin-bottom: 50px;
    }
    .team .member .pic {
      overflow: hidden;
      border-radius: 12px;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
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
      border-radius: 10px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
      position: absolute;
      bottom: -45px;
      left: 15px;
      right: 15px;
      padding: 16px 18px;
      min-height: 105px;
      transition: all 0.3s ease;
      border-bottom: 3px solid var(--accent-color, #10bc69);
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
      color: var(--accent-color, #10bc69);
    }
  </style>
</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

      <a href="#" class="logo d-flex align-items-center me-auto">
        <!-- Uncomment the line below if you also wish to use an image logo -->
        <!-- <img src="assets/img/logo.png" alt=""> -->
        <h1 class="sitename">SiMonter</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="#hero" class="active">Beranda</a></li>
          <li><a href="#about">Tentang Kami</a></li>
          <li><a href="#testimonials">Testimoni</a></li>
          <li><a href="#team">Tim</a></li>
          <li><a href="#contact">Kontak</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      @auth
        <a class="btn-getstarted" href="{{route('dashboard')}}">Dashboard</a>
      @else
        <a class="btn-getstarted" href="{{route('login')}}">Login</a>
      @endauth

    </div>
  </header>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section">

      <div class="container">
        <div class="row gy-4">
          <div class="col-lg-6 order-2 order-lg-1 d-flex flex-column justify-content-center" data-aos="fade-up">
            <h1>SiMonter</h1>
            <p>Sistem Monitoring Evaluasi Terpadu</p>
            <div class="d-flex">
              @auth
                <a href="{{ route('dashboard') }}" class="btn-get-started">Dashboard</a>
              @else
                <a href="{{ route('login') }}" class="btn-get-started">Login</a>
              @endauth
              <a href="https://www.youtube.com/watch?v=Y7f98aduVJ8" class="glightbox btn-watch-video d-flex align-items-center"><i class="bi bi-play-circle"></i><span>Video Tutorial Penggunaan</span></a>
            </div>
          </div>
          <div class="col-lg-6 order-1 order-lg-2 hero-img" data-aos="zoom-out" data-aos-delay="100">
            <img src="{{asset('enno/assets/img/hero-img.png')}}" class="img-fluid animated" alt="">
          </div>
        </div>
      </div>

    </section><!-- /Hero Section -->

    <!-- About Section -->
    <section id="about" class="about section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <span>Tentang Kami<br></span>
        <h2>Tentang Kami</h2>
        <p>SiMonter (Sistem Monitoring Evaluasi Terpadu) dikembangkan sebagai langkah strategis Pengadilan Agama Jakarta Utara</p> 
        <p>dalam mendukung transformasi digital dan transparansi kinerja peradilan. </p>
      </div><!-- End Section Title -->

      <div class="container">

        <div class="row gy-4">
          <div class="col-lg-6 position-relative align-self-start" data-aos="fade-up" data-aos-delay="100">
            <img src="{{asset('enno/assets/img/about.png')}}" class="img-fluid" alt="">
            <a href="https://www.youtube.com/watch?v=Y7f98aduVJ8" class="glightbox pulsating-play-btn"></a>
          </div>
          <div class="col-lg-6 content" data-aos="fade-up" data-aos-delay="200">
            <h3>SiMonter</h3>
            <h5 class="fst-italic">Sistem Monitoring Evaluasi Terpadu</h5>
            <p>
              Kehadiran aplikasi ini bertujuan untuk mengoptimalkan pengawasan internal, mempercepat proses evaluasi kinerja aparatur secara berkala, serta memastikan seluruh evaluasi pekerjaan bidang kesekretariatan berjalan secara terintegrasi, akuntabel, dan efisien sesuai dengan prinsip tata kelola pemerintahan yang baik. Aplikasi SIMONTER lahir dari analisis mendalam terhadap kendala pengawasan internal pada 3 bidang kesekretariatan.
            </p>
            <ul>
              <li><i class="bi bi-check2-all"></i> <span>Integrasi database 3 bidang kesekretariatan dalam satu panel kontrol monitoring terpadu berbasis indikator kinerja terstandar.</span></li>
              <li><i class="bi bi-check2-all"></i> <span>Alur input data terstruktur dari operator ke pimpinan yang menghasilkan laporan evaluasi kinerja secara cepat, valid, dan akurat.</span></li>
              <li><i class="bi bi-check2-all"></i> <span>Pendokumentasian pekerjaan, penunjukan PIC, penetapan batas waktu, dan penelusuran matriks tindak lanjut terpadu.</span></li>
            </ul>
            <p>
             SIMONTER bukan sekadar inovasi aplikasi, melainkan instrumen strategis untuk memperkuat fungsi koordinasi dan pengendalian Sekretaris Pengadilan Agama Jakarta Utara.
            </p>
          </div>
        </div>

      </div>

    </section><!-- /About Section -->

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials section light-background">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <span>Testimoni</span>
        <h2>Testimoni</h2>
        <p>"Testimoni kami dari stackholder dan pihak terkait"</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="swiper init-swiper" data-speed="600" data-delay="5000" data-breakpoints="{ &quot;320&quot;: { &quot;slidesPerView&quot;: 1, &quot;spaceBetween&quot;: 40 }, &quot;1200&quot;: { &quot;slidesPerView&quot;: 3, &quot;spaceBetween&quot;: 40 } }">
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
                  "spaceBetween": 40
                },
                "1200": {
                  "slidesPerView": 3,
                  "spaceBetween": 20
                }
              }
            }
          </script>
          <div class="swiper-wrapper">

            <div class="swiper-slide">
              <div class="testimonial-item" "="">
            <p>
              <i class=" bi bi-quote quote-icon-left"></i>
                <span>Hadirnya SIMONTER memberikan transparansi penuh terhadap kinerja seluruh divisi. Dashboard terpadunya sangat membantu saya dalam mengambil keputusan strategis secara cepat berbasis data riil tanpa perlu menunggu rekapitulasi manual.</span>
                <i class="bi bi-quote quote-icon-right"></i>
                </p>
                <img src="assets/img/testimonials/testimonials-1.jpg" class="testimonial-img" alt="">
                <h3>Dr. Khoiriyah Roihan, S.Ag., M.H.</h3>
                <h4>Ketua PA Jakarta Utara &amp; Penanggung Jawab Tim</h4>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-item">
                <p>
                  <i class="bi bi-quote quote-icon-left"></i>
                  <span>Monitoring progres kegiatan jadi jauh lebih efisien. Fitur pelaporan berjenjang dan notifikasi di SIMONTER memudahkan kami mengidentifikasi kendala di lapangan lebih awal sehingga tindakan korektif bisa langsung dilakukan.</span>
                  <i class="bi bi-quote quote-icon-right"></i>
                </p>
                <img src="assets/img/testimonials/testimonials-2.jpg" class="testimonial-img" alt="">
                <h3>Yuli, S.Ag., M.H.</h3>
                <h4>Sekretaris &amp; Ketua Tim</h4>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-item">
                <p>
                  <i class="bi bi-quote quote-icon-left"></i>
                  <span>Aplikasi ini sangat menghemat waktu. Penyusunan laporan evaluasi bulanan dan tahunan yang dulunya memakan waktu seminggu, kini bisa diselesaikan dalam hitungan jam karena semua data sudah terintegrasi dengan baik.</span>
                  <i class="bi bi-quote quote-icon-right"></i>
                </p>
                <img src="assets/img/testimonials/testimonials-3.jpg" class="testimonial-img" alt="">
                <h3>Dede Supriadi, S.H., M.H.</h3>
                <h4>Panitera</h4>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-item">
                <p>
                  <i class="bi bi-quote quote-icon-left"></i>
                  <span>Tampilan antarmuka SIMONTER sangat ramah pengguna dan mudah dipahami. Proses input data kegiatan bulanan jadi lebih terstruktur dan tidak membingungkan, bahkan untuk pengguna baru.</span>
                  <i class="bi bi-quote quote-icon-right"></i>
                </p>
                <img src="assets/img/testimonials/testimonials-4.jpg" class="testimonial-img" alt="">
                <h3>Ade Irma Suryani, S.H.</h3>
                <h4>Kasubag PTIP &amp; Tim Efektif</h4>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-item">
                <p>
                  <i class="bi bi-quote quote-icon-left"></i>
                  <span>Secara teknis, SIMONTER memiliki performa yang stabil dan manajemen hak akses yang rapi. Integrasi data antar-unit kerja berjalan mulus tanpa mengabaikan aspek keamanan informasi.</span>
                  <i class="bi bi-quote quote-icon-right"></i>
                </p>
                <img src="assets/img/testimonials/testimonials-5.jpg" class="testimonial-img" alt="">
                <h3>Reza Fahlevi, S.Kom.</h3>
                <h4>Developer &amp; Tim Efektif </h4>
              </div>
            </div><!-- End testimonial item -->

          </div>
          <div class="swiper-pagination"></div>
        </div>

      </div>

    </section><!-- /Testimonials Section -->

    <!-- Team Section -->
    <section id="team" class="team section">

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

        <div class="row gy-4">

          <div class="col-xl-12">

            <div class="info-wrap">
              <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="200">
                <i class="bi bi-geo-alt flex-shrink-0"></i>
                <div>
                  <h3>Alamat</h3>
                  <p>Jl. Raya Plumpang Semper No.5, RT.007 RW.002, Kel. Tugu Selatan, Kec. Koja, Kota Jakarta Utara, DKI Jakarta – 14260</p>
                </div>
              </div><!-- End Info Item -->

              <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="300">
                <i class="bi bi-telephone flex-shrink-0"></i>
                <div>
                  <h3>Hubungi Kami</h3>
                  <p>+62 21 4393 4701</p>
                </div>
              </div><!-- End Info Item -->

              <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="400">
                <i class="bi bi-envelope flex-shrink-0"></i>
                <div>
                  <h3>Email</h3>
                  <p>sekretariat.pajakartautara@gmail.com | kepegawaianpaju@gmail.com</p>
                </div>
              </div><!-- End Info Item -->
              <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3967.0079643704225!2d106.90680107369857!3d-6.129629560096545!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6a1ffe9359ed3d%3A0x34cd66db920dd513!2sNorth%20Jakarta%20Religious%20Court!5e0!3m2!1sen!2sid!4v1789389922075!5m2!1sen!2sid" width="1050" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
            </div>
          </div>
        </div>
      </div>

    </section><!-- /Contact Section -->

  </main>

  <footer id="footer" class="footer">

    <div class="container copyright text-center mt-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">SiMonter ver 1.0</strong> <span>Pengadilan Agama Jakarta Utara 2026</span></p>
      <div class="credits">
        <!-- All the links in the footer should remain intact. -->
        <!-- You can delete the links only if you've purchased the pro version. -->
        <!-- Licensing information: https://bootstrapmade.com/license/ -->
        <!-- Purchase the pro version with working PHP/AJAX contact form: [buy-url] -->
        Designed by <a href="https://pa-jakartautara.go.id/">Reza</a> Distributed by <a href=“https://pa-jakartautara.go.id>iMal
      </div>
    </div>

  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="{{asset('enno/assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
  <script src="{{asset('enno/assets/vendor/php-email-form/validate.js')}}"></script>
  <script src="{{asset('enno/assets/vendor/aos/aos.js')}}"></script>
  <script src="{{asset('enno/assets/vendor/glightbox/js/glightbox.min.js')}}"></script>
  <script src="{{asset('enno/assets/vendor/purecounter/purecounter_vanilla.js')}}"></script>
  <script src="{{asset('enno/assets/vendor/imagesloaded/imagesloaded.pkgd.min.js')}}"></script>
  <script src="{{asset('enno/assets/vendor/isotope-layout/isotope.pkgd.min.js')}}"></script>
  <script src="{{asset('enno/assets/vendor/swiper/swiper-bundle.min.js')}}"></script>
  <script src="{{asset('enno/assets/js/main.js')}}"></script>

</body>

</html>