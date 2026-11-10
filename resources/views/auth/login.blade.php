<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Sistem Monitoring Tugas dan Kinerja - Pengadilan Agama Jakarta Utara">
    <meta name="author" content="Pengadilan Agama Jakarta Utara">

    <title>SIMONTER | Login</title>

    <!-- Custom fonts for this template-->
    <link href="{{ asset('sbadmin2/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('sbadmin2/css/sb-admin-2.min.css') }}" rel="stylesheet">

    <style>
        /* Security Patch Captcha "Saya bukan robot" Widget */
        .captcha-widget-box {
            background: #f8fafc;
            border: 1.5px solid #cbd5e1;
            border-radius: 0.85rem;
            padding: 0.8rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.22s ease;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
            user-select: none;
            cursor: pointer;
        }
        .captcha-widget-box:hover {
            border-color: #93c5fd;
            background: #f1f5f9;
        }
        .captcha-widget-box.is-verified {
            border-color: #10b981;
            background: #f0fdf4;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.12);
        }
        .captcha-widget-box.is-invalid-box {
            border-color: #ef4444;
            background: #fef2f2;
        }
        .captcha-check-visual {
            width: 28px;
            height: 28px;
            min-width: 28px;
            border: 2px solid #94a3b8;
            border-radius: 0.4rem;
            background: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.85rem;
            transition: all 0.2s ease;
            position: relative;
        }
        .captcha-widget-box:hover .captcha-check-visual {
            border-color: #2563eb;
        }
        .captcha-widget-box.is-verifying .captcha-check-visual {
            border-color: #2563eb;
            background: #eff6ff;
        }
        .captcha-widget-box.is-verified .captcha-check-visual {
            border-color: #10b981;
            background: #10b981;
            color: #ffffff;
        }
        .captcha-brand-badge {
            text-align: right;
            line-height: 1.15;
        }
        .captcha-brand-badge .shield-icon {
            font-size: 1.35rem;
            color: #2563eb;
            display: block;
            margin-bottom: 2px;
        }
        .captcha-widget-box.is-verified .captcha-brand-badge .shield-icon {
            color: #10b981;
        }
    </style>

</head>

<body class="bg-gradient-primary">

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-6 col-lg-7 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">

                            <div class="col-12">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">
                                            <i class="fas fa-tasks mr-2"></i>SiMonter | Login
                                        </h1>
                                    </div>

                                    @if(session('success'))
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <small><i class="fas fa-check-circle mr-1"></i> {{ session('success') }}</small>
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    @endif

                                    @if(session('error'))
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <small><i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}</small>
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    @endif

                                    <form class="user" method="POST" action="{{ route('loginProses') }}" id="loginForm">
                                        @csrf

                                        <!-- Anti-Bot Honeypot Field (Hidden from real users) -->
                                        <div style="position: absolute; left: -9999px; opacity: 0; pointer-events: none;" aria-hidden="true">
                                            <label for="website_hp">Website</label>
                                            <input type="text" id="website_hp" name="website_hp" tabindex="-1" autocomplete="off">
                                        </div>

                                        <div class="form-group">
                                            <input type="text"
                                                class="form-control form-control-user @error('nip') is-invalid @enderror"
                                                placeholder="Masukkan NIP"
                                                name="nip"
                                                value="{{ old('nip') }}"
                                                autofocus>

                                            @error('nip')
                                                <small class="text-danger ml-3 d-block mt-1">
                                                    {{ $message }}
                                                </small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <input type="password"
                                                class="form-control form-control-user @error('password') is-invalid @enderror"
                                                placeholder="Masukkan Password"
                                                name="password">

                                            @error('password')
                                                <small class="text-danger ml-3 d-block mt-1">
                                                    {{ $message }}
                                                </small>
                                            @enderror
                                        </div>

                                        <!-- Security Patch Captcha Widget ("Saya bukan robot") -->
                                        <div class="form-group mb-3">
                                            <input type="hidden" name="captcha_token" id="captcha_token_input" value="{{ old('captcha_token', $captchaToken ?? '') }}">

                                            <label for="captcha_check"
                                                   id="captchaWidgetBox"
                                                   class="captcha-widget-box mb-0 {{ old('captcha_check') ? 'is-verified' : '' }} @error('captcha_check') is-invalid-box @enderror">
                                                <div class="d-flex align-items-center">
                                                    <input type="checkbox"
                                                           id="captcha_check"
                                                           name="captcha_check"
                                                           value="1"
                                                           class="sr-only"
                                                           {{ old('captcha_check') ? 'checked' : '' }}>
                                                    <span class="captcha-check-visual" id="captchaCheckVisual">
                                                        <i class="fas fa-spinner fa-spin text-primary d-none" id="captchaSpinnerIcon"></i>
                                                        <i class="fas fa-check {{ old('captcha_check') ? '' : 'd-none' }}" id="captchaCheckIcon"></i>
                                                    </span>
                                                    <div>
                                                        <div class="font-weight-bold text-gray-900" style="font-size: 0.9rem;" id="captchaStatusLabel">
                                                            Saya bukan robot
                                                        </div>
                                                        <div class="text-muted" style="font-size: 0.7rem;" id="captchaSubLabel">
                                                            Klik untuk verifikasi keamanan anti-brute force
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="captcha-brand-badge">
                                                    <i class="fas fa-shield-alt shield-icon"></i>
                                                    <span class="font-weight-bold text-gray-700" style="font-size: 0.62rem; letter-spacing: 0.3px;">SIMONTER</span>
                                                    <span class="d-block text-muted" style="font-size: 0.55rem;">Security Guard</span>
                                                </div>
                                            </label>

                                            @error('captcha_check')
                                                <small class="text-danger ml-3 d-block mt-1 font-weight-bold">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>{{ $message }}
                                                </small>
                                            @enderror
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-user btn-block font-weight-bold">
                                            Login
                                        </button>
                                    </form>
                                    <hr>
                                    <div class="text-center">
                                        <small>
                                            Kembali Ke Beranda ?
                                            <a href="{{ route('welcome') }}">Klik Disini</a>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="{{ asset('sbadmin2/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('sbadmin2/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('sbadmin2/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('sbadmin2/js/sb-admin-2.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkbox = document.getElementById('captcha_check');
            const widgetBox = document.getElementById('captchaWidgetBox');
            const spinnerIcon = document.getElementById('captchaSpinnerIcon');
            const checkIcon = document.getElementById('captchaCheckIcon');
            const statusLabel = document.getElementById('captchaStatusLabel');
            const subLabel = document.getElementById('captchaSubLabel');
            let isVerifying = false;

            if (checkbox && widgetBox) {
                checkbox.addEventListener('change', function () {
                    if (isVerifying) {
                        return;
                    }

                    widgetBox.classList.remove('is-invalid-box');

                    if (this.checked) {
                        isVerifying = true;
                        widgetBox.classList.add('is-verifying');
                        widgetBox.classList.remove('is-verified');
                        if (spinnerIcon) spinnerIcon.classList.remove('d-none');
                        if (checkIcon) checkIcon.classList.add('d-none');
                        if (statusLabel) statusLabel.textContent = 'Memverifikasi...';
                        if (subLabel) subLabel.textContent = 'Memeriksa interaksi pengguna';

                        setTimeout(function () {
                            isVerifying = false;
                            widgetBox.classList.remove('is-verifying');
                            widgetBox.classList.add('is-verified');
                            if (spinnerIcon) spinnerIcon.classList.add('d-none');
                            if (checkIcon) checkIcon.classList.remove('d-none');
                            if (statusLabel) statusLabel.textContent = 'Saya bukan robot';
                            if (subLabel) subLabel.textContent = 'Terverifikasi aman • Proteksi Aktif';
                        }, 320);
                    } else {
                        widgetBox.classList.remove('is-verified', 'is-verifying');
                        if (spinnerIcon) spinnerIcon.classList.add('d-none');
                        if (checkIcon) checkIcon.classList.add('d-none');
                        if (statusLabel) statusLabel.textContent = 'Saya bukan robot';
                        if (subLabel) subLabel.textContent = 'Klik untuk verifikasi keamanan anti-brute force';
                    }
                });
            }
        });
    </script>

</body>

</html>
