# SIMONTER (Sistem Monitoring Tugas dan Kinerja) v1.0

**SIMONTER** adalah aplikasi berbasis web yang dirancang untuk **Pengadilan Agama Jakarta Utara** guna mengelola pendelegasian tugas berjenjang, pelaporan tindak lanjut beserta bukti pengerjaan (*evidence*), verifikasi hasil kerja, pencatatan riwayat/log aktivitas tugas secara detail (`UTC+7 / WIB`), *push notifikasi real-time*, serta evaluasi dan pemeringkatan kinerja petugas secara otomatis.

---

## Fitur Utama

- **Manajemen Peran Berjenjang (3 Role)**:
  - **Admin**: Mengelola data pengguna, mendelegasikan tugas kepada Kepala Sub Bagian atau Petugas, memverifikasi hasil tindak lanjut (*Selesai* / *Perlu Revisi*), membuka kembali (*reopen*) tugas yang sudah *closed* apabila ditemukan koreksi pengerjaan, memantau analitik kinerja di Dashboard, serta mengekspor laporan ke format **Excel (`.xls`)** dan **PDF**.
  - **Kepala Sub Bagian**: Menerima delegasi tugas dari Admin, menindaklanjuti tugas secara langsung atau meneruskan/mendelegasikan tugas kepada Petugas, membuat tugas baru untuk Petugas, serta memverifikasi tugas yang didelegasikannya.
  - **Petugas**: Menerima *push notifikasi* saat tugas baru didelegasikan, mengerjakan tugas, dan mengirimkan laporan tindak lanjut beserta lampiran berkas (*maks. 10 MB*) atau tautan (*link evidence*).
- **Push Notifikasi Real-Time & Pusat Notifikasi**:
  - Notifikasi *pop-up toast* melayang (*Floating Push Card*) dilengkapi efek suara (*Web Audio Chime*), integrasi *Browser Desktop Notification*, dan menu lonceng *Pusat Notifikasi* di *topbar*.
- **Detail Log & Riwayat Aktivitas Tugas**:
  - Mencatat seluruh siklus hidup tugas secara kronologis dengan format waktu `WIB (UTC+7)` mulai dari tugas dibuat/didelegasikan, mulai dikerjakan, pengiriman bukti tindak lanjut, catatan revisi pendelegasi, pembukaan kembali (*reopen*), hingga tugas dinyatakan *Selesai (Closed)*.
- **Dashboard Analitik & Leaderboard Kinerja**:
  - Grafik tren penyelesaian tugas & rata-rata waktu pengerjaan dengan filter periode interaktif (**1 Tahun**, **6 Bulan**, **3 Bulan**, dan **1 Bulan**).
  - Tabel **Matriks & Peringkat Kinerja Seluruh Petugas** dengan paginasi 10 besar per halaman dan indikator status evaluasi performa.

---

## Spesifikasi & Kebutuhan Sistem

- **PHP**: `>= 8.3` (Ekstensi: `BCMath`, `Ctype`, `DOM`, `Exif`, `Fileinfo`, `GD`, `Intl`, `Mbstring`, `OpenSSL`, `PDO_MySQL`, `Tokenizer`, `XML`, `Zip`)
- **Composer**: `>= 2.x`
- **Node.js & NPM**: `>= 20.x` / `22.x`
- **Database**: MySQL `>= 8.0` / MariaDB `>= 10.6`
- **Web Server**: Apache / Nginx / Laragon, atau **Docker & Docker Compose**

---

## Panduan Instalasi Lokal (Manual / Laragon / XAMPP)

### 1. Clone Repository
```bash
git clone https://github.com/Gatara22/simonter.git
cd simonter
```

### 2. Instal Dependensi PHP & Frontend
```bash
composer install
npm install
npm run build
```

### 3. Konfigurasi Environment (`.env`)
Salin file `.env.example` menjadi `.env`:
```bash
# Windows (PowerShell / CMD)
copy .env.example .env

# Linux / macOS
cp .env.example .env
```

Sesuaikan pengaturan database dan zona waktu di dalam file `.env`:
```env
APP_NAME="SIMONTER"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simonter
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Generate Key, Migrasi Database, Seeder, & Storage Link
Pastikan database `simonter` telah dibuat di MySQL, kemudian jalankan:
```bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

### 5. Jalankan Aplikasi
```bash
php artisan serve
```
Buka browser dan akses `http://localhost:8000` (atau `http://simonter.test` jika menggunakan Laragon).

---

## Panduan Instalasi Production (Docker & Docker Compose)

Repository ini telah dilengkapi konfigurasi **Docker Multi-Stage Production** ([`Dockerfile`](Dockerfile), [`docker-compose.yml`](docker-compose.yml), [`docker-entrypoint.sh`](docker-entrypoint.sh), dan [`.env.production.example`](.env.production.example)) yang mencakup layanan:
- **`app`**: PHP 8.3-FPM Alpine dengan OPcache, GD (DomPDF), Redis, & batas upload `15M`
- **`webserver`**: Nginx 1.27 Alpine dengan kompresi Gzip, *cache static assets*, & *security headers*
- **`queue`**: Worker antrean latar belakang Laravel (`php artisan queue:work`)
- **`db`**: MySQL 8.0 (`UTC+7`, `utf8mb4`) dengan *persistent volume* & *healthcheck*
- **`redis`**: Redis 7 Alpine untuk *cache*, *session*, dan *queue*

### Langkah Deployment Docker:

```bash
# 1. Clone repository di server production
git clone https://github.com/Gatara22/simonter.git
cd simonter

# 2. Salin template environment production dan sesuaikan kredensial DB / URL domain
cp .env.production.example .env

# 3. Build dan jalankan seluruh container di background
docker compose up -d --build

# 4. Generate APP_KEY dan optimalkan cache konfigurasi
docker compose exec app php artisan key:generate --force
docker compose exec app php artisan config:cache

# 5. (Opsional) Jalankan seeder data akun awal
docker compose exec app php artisan db:seed --force
```

### Perintah Pemeliharaan Docker:
```bash
# Cek status seluruh container
docker compose ps

# Lihat log aplikasi secara live
docker compose logs -f app

# Restart layanan
docker compose restart

# Hentikan layanan
docker compose down
```

---

## Akun Login Default (Database Seeder)

Setelah menjalankan `php artisan db:seed`, Anda dapat masuk menggunakan **Email** atau **NIP** berikut:

| Jabatan / Role | Email | NIP | Password |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@simonter.go.id` | `199509062020121009` | `password` |
| **Kepala Sub Bagian** | `kasubbag@simonter.go.id` | `199604152021041005` | `password` |
| **Petugas** | `petugas@simonter.go.id` | `199805122022031002` | `password` |

---

## Pengujian Otomatis (Automated Tests)

Untuk menjalankan seluruh *test suite* PHPUnit:
```bash
php artisan test --compact
```

---

**Copyright © Pengadilan Agama Jakarta Utara 2026 | SIMONTER ver 1.0**
