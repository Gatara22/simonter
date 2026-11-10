<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Sistem Monitoring Tugas dan Kinerja">
    <meta name="author" content="Pengadilan Agama Jakarta Utara">

    <title>SIMONTER | {{ $title }}</title>

    <!-- Custom fonts for this template-->
    <link href="{{ asset('sbadmin2/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('sbadmin2/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('sbadmin2/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">

    <style>
        /* Animasi Bell Ringing untuk Push Notifikasi */
        @keyframes bellRing {
            0%, 100% { transform: rotate(0); }
            10%, 30%, 50% { transform: rotate(15deg); }
            20%, 40%, 60% { transform: rotate(-15deg); }
            70% { transform: rotate(7deg); }
            80% { transform: rotate(-7deg); }
            90% { transform: rotate(0); }
        }
        .bell-ringing {
            display: inline-block;
            animation: bellRing 2.2s ease-in-out infinite;
            transform-origin: top center;
            color: #2563eb !important;
        }

        /* Dropdown Pusat Notifikasi Topbar */
        .notif-dropdown-menu {
            width: 420px !important;
            max-width: calc(100vw - 20px);
            border: none !important;
            border-radius: 0.75rem !important;
            overflow: hidden;
            box-shadow: 0 14px 36px rgba(15, 23, 42, 0.2) !important;
        }
        .notif-dropdown-header {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: #ffffff;
            padding: 0.85rem 1.1rem;
        }
        .notif-scroll-area {
            max-height: 390px;
            overflow-y: auto;
        }
        .notif-item-card {
            display: block;
            padding: 0.85rem 1.05rem;
            border-bottom: 1px solid #edf2f9;
            text-decoration: none !important;
            transition: background-color 0.18s ease;
            position: relative;
        }
        .notif-item-card:hover {
            background-color: #f1f5f9 !important;
        }
        .notif-item-unread {
            background-color: #f5f8ff;
            border-left: 4px solid #3b82f6;
        }
        .notif-item-unread.notif-accent-success {
            background-color: #f2fdf8;
            border-left-color: #10b981;
        }
        .notif-item-unread.notif-accent-warning {
            background-color: #fffbeb;
            border-left-color: #f59e0b;
        }
        .notif-item-unread.notif-accent-danger {
            background-color: #fef2f2;
            border-left-color: #ef4444;
        }
        .notif-item-unread.notif-accent-info {
            background-color: #f0fdfa;
            border-left-color: #06b6d4;
        }
        .notif-item-read {
            background-color: #ffffff;
            border-left: 4px solid transparent;
            opacity: 0.88;
        }
        .notif-icon-circle {
            width: 38px;
            height: 38px;
            min-width: 38px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.92rem;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.12);
        }
        .notif-quote-box {
            background: rgba(255, 255, 255, 0.9);
            border-left: 3px solid #cbd5e1;
            padding: 0.35rem 0.6rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            color: #475569;
            margin-top: 0.35rem;
        }

        /* Floating Push Notification Toast Container */
        #pushNotificationContainer {
            position: fixed;
            top: 76px;
            right: 22px;
            z-index: 1080;
            width: 400px;
            max-width: calc(100vw - 28px);
            display: flex;
            flex-direction: column;
            gap: 14px;
            pointer-events: none;
        }
        @keyframes pushSlideIn {
            0% {
                opacity: 0;
                transform: translateX(60px) scale(0.96);
            }
            100% {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
        }
        @keyframes pushProgress {
            from { width: 100%; }
            to { width: 0%; }
        }
        .push-notif-card {
            pointer-events: auto;
            background: #ffffff;
            border-radius: 0.85rem;
            overflow: hidden;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.24), 0 4px 12px rgba(15, 23, 42, 0.08);
            border: 1px solid rgba(226, 232, 240, 0.95);
            animation: pushSlideIn 0.38s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            transition: transform 0.22s ease, opacity 0.22s ease;
        }
        .push-notif-card.push-closing {
            opacity: 0;
            transform: translateX(50px) scale(0.95);
        }
        .push-header-primary { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); }
        .push-header-info    { background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%); }
        .push-header-success { background: linear-gradient(135deg, #059669 0%, #047857 100%); }
        .push-header-warning { background: linear-gradient(135deg, #d97706 0%, #b45309 100%); }
        .push-header-danger  { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); }

        .push-task-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.6rem 0.8rem;
        }
        .push-progress-bar {
            height: 4px;
            background: rgba(37, 99, 235, 0.8);
            animation: pushProgress 11s linear forwards;
        }
        .push-notif-card:hover .push-progress-bar {
            animation-play-state: paused;
        }
    </style>

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('dashboard') }}">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="sidebar-brand-text mx-3">SIMONTER <sup>1.0</sup></div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item {{ $menuDashboard ?? '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            @if(Auth::user() && Auth::user()->jabatan === 'Admin')
            <!-- Heading -->
            <div class="sidebar-heading">
                MENU ADMIN
            </div>

            <!-- Nav Item - Users -->
            <li class="nav-item {{ $menuAdminUser ?? '' }}">
                <a class="nav-link" href="{{ route('user.index') }}">
                    <i class="fas fa-fw fa-user"></i>
                    <span>Data User</span></a>
            </li>

            <!-- Nav Item - Tugas -->
            <li class="nav-item {{ $menuAdminTugas ?? '' }}">
                <a class="nav-link" href="{{ route('tugas.index') }}">
                    <i class="fas fa-fw fa-tasks"></i>
                    <span>Data Tugas (Delegasi)</span></a>
            </li>
            @endif

            @if(Auth::user() && in_array(Auth::user()->jabatan, ['Kepala Sub Bagian', 'Panitera Muda'], true))
            <!-- Heading -->
            <div class="sidebar-heading">
                MENU {{ strtoupper(Auth::user()->jabatan) }}
            </div>

            <!-- Nav Item - Daftar Tugas & Delegasi -->
            <li class="nav-item {{ $menuAdminTugas ?? ($menuKasubbagTugas ?? ($menuPetugasTugas ?? '')) }}">
                <a class="nav-link" href="{{ route('tugas.index') }}">
                    <i class="fas fa-fw fa-tasks"></i>
                    <span>Data Tugas &amp; Delegasi</span></a>
            </li>

            <!-- Nav Item - Delegasikan Tugas Baru -->
            <li class="nav-item {{ $menuDelegasiBaru ?? '' }}">
                <a class="nav-link" href="{{ route('tugas.create') }}">
                    <i class="fas fa-fw fa-paper-plane"></i>
                    <span>Delegasikan Tugas Baru</span></a>
            </li>
            @endif

            @if(Auth::user() && Auth::user()->jabatan === 'Petugas')
            <!-- Heading -->
            <div class="sidebar-heading">
                MENU PETUGAS
            </div>

            <!-- Nav Item - Tugas Saya -->
            <li class="nav-item {{ $menuPetugasTugas ?? '' }}">
                <a class="nav-link" href="{{ route('tugas.index') }}">
                    <i class="fas fa-fw fa-tasks"></i>
                    <span>Data Tugas Saya</span></a>
            </li>
            @endif

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                PANDUAN &amp; BANTUAN
            </div>

            <!-- Nav Item - Petunjuk Aplikasi -->
            <li class="nav-item {{ $menuPetunjuk ?? '' }}">
                <a class="nav-link" href="{{ route('petunjuk.index') }}">
                    <i class="fas fa-fw fa-book-open"></i>
                    <span>Petunjuk Aplikasi</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        @auth
                        @php
                            $unreadCount = $topbarUnreadCount ?? 0;
                            $notificationsList = $topbarNotifications ?? collect();
                        @endphp
                        <!-- Nav Item - Push & In-App Notifications -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                title="Pusat Notifikasi Tugas">
                                <i id="notifBellIcon" class="fas fa-bell fa-fw {{ $unreadCount > 0 ? 'bell-ringing' : 'text-gray-600' }}"></i>
                                <span id="notifBadgeCount"
                                    class="badge badge-danger badge-counter {{ $unreadCount > 0 ? '' : 'd-none' }}">
                                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                </span>
                            </a>

                            <!-- Dropdown - Pusat Notifikasi -->
                            <div class="dropdown-menu dropdown-menu-right notif-dropdown-menu animated--grow-in p-0"
                                aria-labelledby="alertsDropdown" id="notifDropdownMenu">

                                <div class="notif-dropdown-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="font-weight-bold small text-uppercase" style="letter-spacing: 0.6px;">
                                            <i class="fas fa-bell mr-1"></i> Pusat Notifikasi
                                        </div>
                                        <span id="notifHeaderUnreadBadge" class="badge badge-light text-primary font-weight-bold mt-1">
                                            {{ $unreadCount }} Belum Dibaca
                                        </span>
                                    </div>
                                    <form action="{{ route('notifikasi.baca-semua') }}" method="POST" id="formMarkAllNotifRead" class="m-0">
                                        @csrf
                                        <button type="submit" id="btnMarkAllNotifRead"
                                            class="btn btn-sm btn-light text-primary font-weight-bold shadow-sm px-2 py-1 {{ $unreadCount > 0 ? '' : 'd-none' }}"
                                            style="font-size: 0.72rem;">
                                            <i class="fas fa-check-double mr-1"></i> Tandai Dibaca
                                        </button>
                                    </form>
                                </div>

                                <!-- Browser Desktop Push Permission Banner -->
                                <div id="browserPushBanner" class="bg-light border-bottom px-3 py-2 d-none">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small text-gray-700">
                                            <i class="fas fa-desktop text-primary mr-1"></i> Aktifkan push notifikasi layar?
                                        </span>
                                        <button type="button" id="btnEnableBrowserPush" class="btn btn-xs btn-primary py-0 px-2 small font-weight-bold">
                                            Aktifkan
                                        </button>
                                    </div>
                                </div>

                                <div id="notifDropdownList" class="notif-scroll-area">
                                    @forelse($notificationsList as $notif)
                                        <a href="{{ $notif['read_url'] }}"
                                           class="notif-item-card {{ $notif['is_unread'] ? 'notif-item-unread notif-accent-' . $notif['badge_color'] : 'notif-item-read' }}"
                                           data-notif-id="{{ $notif['id'] }}">
                                            <div class="d-flex align-items-start">
                                                <div class="notif-icon-circle bg-{{ $notif['badge_color'] }} mr-3 mt-1">
                                                    <i class="{{ $notif['icon'] }}"></i>
                                                </div>
                                                <div class="flex-grow-1" style="min-width: 0;">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="badge badge-{{ $notif['badge_color'] }} px-2 py-1" style="font-size: 0.65rem;">
                                                            {{ $notif['badge_label'] }}
                                                        </span>
                                                        <small class="text-muted font-weight-bold" style="font-size: 0.68rem;">
                                                            {{ $notif['time_ago'] }}
                                                        </small>
                                                    </div>
                                                    <div class="font-weight-bold text-gray-900 small mb-1">
                                                        {{ $notif['title'] }}
                                                    </div>
                                                    <div class="text-gray-700" style="font-size: 0.78rem; line-height: 1.35;">
                                                        {{ $notif['message'] }}
                                                    </div>
                                                    @if(!empty($notif['catatan']))
                                                        <div class="notif-quote-box text-truncate">
                                                            <i class="fas fa-quote-left mr-1 text-gray-400"></i> {{ $notif['catatan'] }}
                                                        </div>
                                                    @endif
                                                    <div class="d-flex flex-wrap justify-content-between align-items-center mt-2 pt-1 border-top" style="font-size: 0.7rem;">
                                                        <span class="text-primary font-weight-bold">
                                                            <i class="fas fa-user-shield mr-1"></i>{{ $notif['sender_name'] }} ({{ $notif['sender_jabatan'] }})
                                                        </span>
                                                        <span class="text-muted">
                                                            <i class="far fa-clock mr-1"></i>{{ $notif['waktu_wib'] }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="text-center py-4 px-3 text-muted">
                                            <i class="fas fa-bell-slash fa-2x text-gray-300 mb-2 d-block"></i>
                                            <div class="font-weight-bold small text-gray-600">Belum Ada Notifikasi</div>
                                            <small class="text-muted">Notifikasi delegasi tugas baru dan laporan tindak lanjut akan muncul di sini.</small>
                                        </div>
                                    @endforelse
                                </div>

                                <a class="dropdown-item text-center small text-primary font-weight-bold py-2 bg-light border-top"
                                   href="{{ route('tugas.index') }}">
                                    Lihat Seluruh Daftar Tugas <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </li>
                        @endauth

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ Auth::user()->name ?? 'User' }}</span>
                                <img class="img-profile rounded-circle"
                                    src="{{ Auth::user()->foto_url ?? asset('sbadmin2/img/undraw_profile.svg') }}"
                                    alt="{{ Auth::user()->name ?? 'User' }}"
                                    style="object-fit: cover; width: 32px; height: 32px;">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="{{ route('pengaturan.index') }}">
                                    <div class="badge badge-success justify-content-center d-flex">
                                        {{ Auth::user()->jabatan ?? 'User' }}
                                    </div>
                                </a>
                                <a class="dropdown-item" href="{{ route('pengaturan.index') }}">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Pengaturan Akun
                                </a>
                                <a class="dropdown-item" href="{{ route('petunjuk.index') }}">
                                    <i class="fas fa-book-open fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Petunjuk Aplikasi
                                </a>

                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                  @yield('content')

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Pengadilan Agama Jakarta Utara 2026 | ver 1.0</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Floating Push Notification Toast Container -->
    @auth
    <div id="pushNotificationContainer" aria-live="polite" aria-atomic="true"></div>
    @endauth

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Konfirmasi Keluar</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Pilih "Logout" di bawah jika Anda yakin ingin mengakhiri sesi saat ini.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('sbadmin2/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('sbadmin2/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('sbadmin2/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('sbadmin2/js/sb-admin-2.min.js') }}"></script>

    <script src="{{ asset('sbadmin2/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('sbadmin2/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('sbadmin2/js/demo/datatables-demo.js') }}"></script>

    @auth
    <script>
        (function () {
            const currentUserId = {{ Auth::id() }};
            const pollUrl = @json(route('notifikasi.poll'));
            const markAllReadUrl = @json(route('notifikasi.baca-semua'));
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const storageKey = 'simonter_pushed_notifs_u' + currentUserId;

            const initialNotifications = @json($topbarNotifications ?? []);
            const container = document.getElementById('pushNotificationContainer');
            const badgeCountEl = document.getElementById('notifBadgeCount');
            const bellIconEl = document.getElementById('notifBellIcon');
            const headerUnreadEl = document.getElementById('notifHeaderUnreadBadge');
            const markAllBtn = document.getElementById('btnMarkAllNotifRead');
            const dropdownListEl = document.getElementById('notifDropdownList');
            const browserPushBanner = document.getElementById('browserPushBanner');
            const btnEnableBrowserPush = document.getElementById('btnEnableBrowserPush');

            function getPushedIds() {
                try {
                    const raw = sessionStorage.getItem(storageKey);
                    return raw ? JSON.parse(raw) : [];
                } catch (e) {
                    return [];
                }
            }

            function savePushedId(id) {
                try {
                    const ids = getPushedIds();
                    if (!ids.includes(id)) {
                        ids.push(id);
                        if (ids.length > 60) {
                            ids.shift();
                        }
                        sessionStorage.setItem(storageKey, JSON.stringify(ids));
                    }
                } catch (e) {
                    // Ignore storage errors
                }
            }

            function escapeHtml(str) {
                if (!str) {
                    return '';
                }
                const div = document.createElement('div');
                div.textContent = String(str);
                return div.innerHTML;
            }

            function playNotificationChime() {
                try {
                    const AudioCtx = window.AudioContext || window.webkitAudioContext;
                    if (!AudioCtx) {
                        return;
                    }
                    const ctx = new AudioCtx();
                    const now = ctx.currentTime;

                    // Dual-tone pleasant chime
                    [587.33, 880].forEach(function (freq, idx) {
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(freq, now + idx * 0.11);
                        gain.gain.setValueAtTime(0.001, now + idx * 0.11);
                        gain.gain.exponentialRampToValueAtTime(0.08, now + idx * 0.11 + 0.02);
                        gain.gain.exponentialRampToValueAtTime(0.0001, now + idx * 0.11 + 0.35);
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start(now + idx * 0.11);
                        osc.stop(now + idx * 0.11 + 0.36);
                    });
                } catch (e) {
                    // Audio autoplay policy may block until user interacts
                }
            }

            function triggerBrowserNativeNotification(notif) {
                if (!('Notification' in window) || Notification.permission !== 'granted') {
                    return;
                }
                try {
                    const nativeNotif = new Notification(notif.title || 'SIMONTER - Notifikasi Tugas', {
                        body: notif.message || '',
                        icon: @json(asset('sbadmin2/img/undraw_profile.svg')),
                        tag: 'simonter-notif-' + notif.id
                    });
                    nativeNotif.onclick = function () {
                        window.focus();
                        window.location.href = notif.read_url || notif.url;
                    };
                } catch (e) {
                    // Ignore native notification errors
                }
            }

            function showFloatingPushToast(notif) {
                if (!container) {
                    return;
                }

                // Avoid duplicate toast on screen
                if (container.querySelector('[data-toast-id="' + notif.id + '"]')) {
                    return;
                }

                // Limit maximum simultaneous floating cards to 3
                const activeCards = container.querySelectorAll('.push-notif-card');
                if (activeCards.length >= 3) {
                    activeCards[0].remove();
                }

                const color = notif.badge_color || 'primary';
                const card = document.createElement('div');
                card.className = 'push-notif-card';
                card.setAttribute('data-toast-id', notif.id);

                let metaRightHtml = '';
                if (notif.evidence_info) {
                    metaRightHtml = '<span class="badge badge-light border text-success font-weight-bold"><i class="fas fa-paperclip mr-1"></i>' + escapeHtml(notif.evidence_info) + '</span>';
                } else if (notif.tanggal_selesai && notif.tanggal_selesai !== '-') {
                    metaRightHtml = '<span class="badge badge-light border text-danger font-weight-bold"><i class="far fa-calendar-alt mr-1"></i>Batas: ' + escapeHtml(notif.tanggal_selesai) + '</span>';
                }

                let catatanHtml = '';
                if (notif.catatan) {
                    const shortCatatan = notif.catatan.length > 130
                        ? notif.catatan.substring(0, 130) + '...'
                        : notif.catatan;
                    catatanHtml = '<div class="notif-quote-box mt-2"><i class="fas fa-quote-left mr-1 text-gray-400"></i>' + escapeHtml(shortCatatan) + '</div>';
                }

                card.innerHTML =
                    '<div class="push-header-' + escapeHtml(color) + ' text-white px-3 py-2 d-flex justify-content-between align-items-center">' +
                        '<div class="d-flex align-items-center">' +
                            '<span class="badge badge-light text-' + escapeHtml(color) + ' rounded-circle p-1 mr-2 d-inline-flex align-items-center justify-content-center" style="width:24px;height:24px;">' +
                                '<i class="' + escapeHtml(notif.icon || 'fas fa-bell') + '" style="font-size:0.75rem;"></i>' +
                            '</span>' +
                            '<span class="font-weight-bold" style="font-size:0.72rem;letter-spacing:0.5px;">' + escapeHtml(notif.header_tag || 'PUSH NOTIFIKASI • SIMONTER') + '</span>' +
                        '</div>' +
                        '<div class="d-flex align-items-center">' +
                            '<small class="text-white-50 mr-2" style="font-size:0.7rem;">' + escapeHtml(notif.time_ago || 'Baru saja') + '</small>' +
                            '<button type="button" class="btn btn-sm text-white p-0 border-0 btn-close-push" title="Tutup" style="line-height:1;font-size:1.1rem;opacity:0.85;">&times;</button>' +
                        '</div>' +
                    '</div>' +
                    '<div class="p-3">' +
                        '<div class="font-weight-bold text-gray-900 mb-1" style="font-size:0.92rem;">' + escapeHtml(notif.title) + '</div>' +
                        '<div class="push-task-box mb-2">' +
                            '<div class="font-weight-bold text-primary small mb-1"><i class="fas fa-thumbtack mr-1"></i>' + escapeHtml(notif.tugas_judul) + '</div>' +
                            '<div class="d-flex flex-wrap justify-content-between align-items-center" style="gap:4px;">' +
                                '<span class="badge badge-light border text-gray-800"><i class="fas fa-user-check mr-1 text-primary"></i>' + escapeHtml(notif.sender_name) + ' (' + escapeHtml(notif.sender_jabatan) + ')</span>' +
                                metaRightHtml +
                            '</div>' +
                        '</div>' +
                        '<div class="text-gray-700" style="font-size:0.8rem;line-height:1.4;">' + escapeHtml(notif.message) + '</div>' +
                        catatanHtml +
                        '<div class="d-flex justify-content-end align-items-center mt-3 pt-2 border-top" style="gap:8px;">' +
                            '<button type="button" class="btn btn-sm btn-light border text-secondary font-weight-bold px-3 btn-close-push" style="font-size:0.76rem;">Tutup</button>' +
                            '<a href="' + escapeHtml(notif.read_url || notif.url) + '" class="btn btn-sm btn-' + escapeHtml(color) + ' font-weight-bold px-3 shadow-sm" style="font-size:0.76rem;">' +
                                escapeHtml(notif.action_text || 'Lihat Detail Tugas') + ' <i class="fas fa-arrow-right ml-1"></i>' +
                            '</a>' +
                        '</div>' +
                    '</div>' +
                    '<div class="push-progress-bar"></div>';

                const closeButtons = card.querySelectorAll('.btn-close-push');
                closeButtons.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        dismissCard(card);
                    });
                });

                let autoHideTimer = setTimeout(function () {
                    dismissCard(card);
                }, 11000);

                card.addEventListener('mouseenter', function () {
                    clearTimeout(autoHideTimer);
                });

                card.addEventListener('mouseleave', function () {
                    autoHideTimer = setTimeout(function () {
                        dismissCard(card);
                    }, 5000);
                });

                container.appendChild(card);
                playNotificationChime();
                triggerBrowserNativeNotification(notif);
            }

            function dismissCard(card) {
                if (!card || card.classList.contains('push-closing')) {
                    return;
                }
                card.classList.add('push-closing');
                setTimeout(function () {
                    if (card.parentNode) {
                        card.parentNode.removeChild(card);
                    }
                }, 230);
            }

            function renderDropdownItems(notifications) {
                if (!dropdownListEl) {
                    return;
                }
                if (!notifications || notifications.length === 0) {
                    dropdownListEl.innerHTML =
                        '<div class="text-center py-4 px-3 text-muted">' +
                            '<i class="fas fa-bell-slash fa-2x text-gray-300 mb-2 d-block"></i>' +
                            '<div class="font-weight-bold small text-gray-600">Belum Ada Notifikasi</div>' +
                            '<small class="text-muted">Notifikasi delegasi tugas baru dan laporan tindak lanjut akan muncul di sini.</small>' +
                        '</div>';
                    return;
                }

                let html = '';
                notifications.forEach(function (notif) {
                    const color = notif.badge_color || 'primary';
                    const itemClass = notif.is_unread
                        ? 'notif-item-unread notif-accent-' + escapeHtml(color)
                        : 'notif-item-read';

                    const catatanBlock = notif.catatan
                        ? '<div class="notif-quote-box text-truncate"><i class="fas fa-quote-left mr-1 text-gray-400"></i> ' + escapeHtml(notif.catatan) + '</div>'
                        : '';

                    html +=
                        '<a href="' + escapeHtml(notif.read_url || notif.url) + '" class="notif-item-card ' + itemClass + '" data-notif-id="' + escapeHtml(notif.id) + '">' +
                            '<div class="d-flex align-items-start">' +
                                '<div class="notif-icon-circle bg-' + escapeHtml(color) + ' mr-3 mt-1">' +
                                    '<i class="' + escapeHtml(notif.icon || 'fas fa-bell') + '"></i>' +
                                '</div>' +
                                '<div class="flex-grow-1" style="min-width: 0;">' +
                                    '<div class="d-flex justify-content-between align-items-center mb-1">' +
                                        '<span class="badge badge-' + escapeHtml(color) + ' px-2 py-1" style="font-size: 0.65rem;">' + escapeHtml(notif.badge_label) + '</span>' +
                                        '<small class="text-muted font-weight-bold" style="font-size: 0.68rem;">' + escapeHtml(notif.time_ago) + '</small>' +
                                    '</div>' +
                                    '<div class="font-weight-bold text-gray-900 small mb-1">' + escapeHtml(notif.title) + '</div>' +
                                    '<div class="text-gray-700" style="font-size: 0.78rem; line-height: 1.35;">' + escapeHtml(notif.message) + '</div>' +
                                    catatanBlock +
                                    '<div class="d-flex flex-wrap justify-content-between align-items-center mt-2 pt-1 border-top" style="font-size: 0.7rem;">' +
                                        '<span class="text-primary font-weight-bold"><i class="fas fa-user-shield mr-1"></i>' + escapeHtml(notif.sender_name) + ' (' + escapeHtml(notif.sender_jabatan) + ')</span>' +
                                        '<span class="text-muted"><i class="far fa-clock mr-1"></i>' + escapeHtml(notif.waktu_wib) + '</span>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</a>';
                });

                dropdownListEl.innerHTML = html;
            }

            function updateTopbarCounters(unreadCount) {
                if (badgeCountEl) {
                    if (unreadCount > 0) {
                        badgeCountEl.textContent = unreadCount > 9 ? '9+' : String(unreadCount);
                        badgeCountEl.classList.remove('d-none');
                    } else {
                        badgeCountEl.textContent = '0';
                        badgeCountEl.classList.add('d-none');
                    }
                }
                if (bellIconEl) {
                    if (unreadCount > 0) {
                        bellIconEl.classList.add('bell-ringing');
                        bellIconEl.classList.remove('text-gray-600');
                    } else {
                        bellIconEl.classList.remove('bell-ringing');
                        bellIconEl.classList.add('text-gray-600');
                    }
                }
                if (headerUnreadEl) {
                    headerUnreadEl.textContent = unreadCount + ' Belum Dibaca';
                }
                if (markAllBtn) {
                    if (unreadCount > 0) {
                        markAllBtn.classList.remove('d-none');
                    } else {
                        markAllBtn.classList.add('d-none');
                    }
                }
            }

            function processIncomingNotifications(notifications) {
                if (!Array.isArray(notifications)) {
                    return;
                }
                const pushedIds = getPushedIds();
                const newUnreads = notifications.filter(function (n) {
                    return n.is_unread && !pushedIds.includes(n.id);
                });

                // Show up to 2 newest unpushed notifications as floating push cards
                newUnreads.slice(0, 2).reverse().forEach(function (notif) {
                    savePushedId(notif.id);
                    showFloatingPushToast(notif);
                });

                // Mark remaining unpushed IDs as seen in session so they don't flood
                newUnreads.forEach(function (notif) {
                    savePushedId(notif.id);
                });
            }

            // Check browser desktop notification permission banner
            if ('Notification' in window && Notification.permission === 'default' && browserPushBanner) {
                browserPushBanner.classList.remove('d-none');
                if (btnEnableBrowserPush) {
                    btnEnableBrowserPush.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        Notification.requestPermission().then(function () {
                            browserPushBanner.classList.add('d-none');
                        });
                    });
                }
            }

            // Process initial notifications on page load
            processIncomingNotifications(initialNotifications);

            // Handle AJAX "Tandai Semua Dibaca"
            const formMarkAll = document.getElementById('formMarkAllNotifRead');
            if (formMarkAll) {
                formMarkAll.addEventListener('submit', function (e) {
                    e.preventDefault();
                    fetch(markAllReadUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(function (res) { return res.json(); })
                    .then(function () {
                        updateTopbarCounters(0);
                        if (dropdownListEl) {
                            dropdownListEl.querySelectorAll('.notif-item-unread').forEach(function (el) {
                                el.classList.remove('notif-item-unread', 'notif-accent-primary', 'notif-accent-success', 'notif-accent-warning', 'notif-accent-danger', 'notif-accent-info');
                                el.classList.add('notif-item-read');
                            });
                        }
                        if (container) {
                            container.querySelectorAll('.push-notif-card').forEach(function (card) {
                                dismissCard(card);
                            });
                        }
                    })
                    .catch(function () {
                        formMarkAll.submit();
                    });
                });
            }

            // Poll every 8 seconds for real-time push notifications
            setInterval(function () {
                if (document.hidden) {
                    return;
                }
                fetch(pollUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function (res) {
                    if (!res.ok) {
                        throw new Error('Poll error');
                    }
                    return res.json();
                })
                .then(function (data) {
                    if (typeof data.unread_count === 'number') {
                        updateTopbarCounters(data.unread_count);
                    }
                    if (Array.isArray(data.notifications)) {
                        renderDropdownItems(data.notifications);
                        processIncomingNotifications(data.notifications);
                    }
                })
                .catch(function () {
                    // Silent fail on transient network error
                });
            }, 8000);
        })();
    </script>
    @endauth

    @stack('scripts')
</body>

</html>
