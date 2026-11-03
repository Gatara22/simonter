================================================================================
SIMONTER (Sistem Monitoring Tugas dan Kinerja) - ver 1.0
Pengadilan Agama Jakarta Utara
Repository: https://github.com/Gatara22/simonter.git
================================================================================

DESKRIPSI SINGKAT
-----------------
SIMONTER adalah aplikasi berbasis web untuk mengelola pendelegasian tugas,
pelaporan tindak lanjut beserta bukti pengerjaan (evidence), verifikasi berjenjang,
pencatatan riwayat/log aktivitas tugas secara detail (UTC+7 / WIB), push notifikasi
real-time, serta evaluasi dan pemeringkatan kinerja petugas secara otomatis.

Hak Akses & Peran Pengguna (Role):
1. Admin             : Mengelola data user, mendelegasikan tugas ke Kepala Sub Bagian
                       atau Petugas, memverifikasi hasil tugas (Selesai / Perlu Revisi),
                       membuka kembali (reopen) tugas yang sudah closed, memantau
                       analitik dashboard & leaderboard kinerja, serta ekspor laporan
                       (PDF & Excel).
2. Kepala Sub Bagian : Menerima delegasi tugas dari Admin, menindaklanjuti tugas,
                       meneruskan/mendelegasikan tugas kepada Petugas, membuat tugas
                       baru untuk Petugas, serta memverifikasi tugas yang didelegasikannya.
3. Petugas           : Menerima push notifikasi tugas baru, mengerjakan tugas, dan
                       mengirimkan laporan tindak lanjut beserta berkas lampiran
                       (maks. 10 MB) atau tautan (link) evidence.


================================================================================
A. SPESIFIKASI & KEBUTUHAN SISTEM (SYSTEM REQUIREMENTS)
================================================================================
- PHP               : >= 8.3 (Ekstensi: BCMath, Ctype, DOM, Exif, Fileinfo, GD,
                      Intl, JSON, Mbstring, OpenSSL, PDO_MySQL, Tokenizer, XML, Zip)
- Composer          : >= 2.x
- Node.js & NPM     : >= 20.x / 22.x
- Database          : MySQL >= 8.0 / MariaDB >= 10.6 (atau SQLite untuk pengujian)
- Web Server        : Apache / Nginx / Laragon (atau Docker & Docker Compose)


================================================================================
B. PANDUAN INSTALASI LOKAL (MANUAL / LARAGON / XAMPP)
================================================================================

1. Clone Repository
   ----------------
   git clone https://github.com/Gatara22/simonter.git
   cd simonter

2. Instal Dependensi PHP (Composer)
   --------------------------------
   composer install

3. Instal Dependensi Frontend & Build Aset (NPM)
   ---------------------------------------------
   npm install
   npm run build

4. Konfigurasi File Environment (.env)
   -----------------------------------
   Salin file .env.example menjadi .env:
   - Windows (CMD/PowerShell) : copy .env.example .env
   - Linux / macOS            : cp .env.example .env

   Buka file .env dan sesuaikan konfigurasi database MySQL serta zona waktu:
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

5. Generate Application Key
   ------------------------
   php artisan key:generate

6. Buat Database & Jalankan Migrasi beserta Seeder Data Awal
   ---------------------------------------------------------
   Pastikan database bernama `simonter` sudah dibuat di MySQL, lalu jalankan:
   php artisan migrate --seed

7. Buat Symbolic Link Storage Publik (Untuk Upload File & Foto)
   ------------------------------------------------------------
   php artisan storage:link

8. Jalankan Server Aplikasi
   ------------------------
   php artisan serve
   Akses aplikasi melalui browser di: http://localhost:8000
   (Atau melalui virtual host Laragon: http://simonter.test)


================================================================================
C. PANDUAN INSTALASI PRODUCTION MENGGUNAKAN DOCKER
================================================================================
Aplikasi ini telah dilengkapi konfigurasi Docker Multi-Stage Production
(PHP 8.3-FPM Alpine, Nginx 1.27, MySQL 8.0, Redis 7, dan Queue Worker).

1. Clone Repository di Server Production
   -------------------------------------
   git clone https://github.com/Gatara22/simonter.git
   cd simonter

2. Siapkan File Environment Production (.env)
   ------------------------------------------
   cp .env.production.example .env

   Edit file .env dan sesuaikan parameter berikut:
   - APP_URL          : URL domain production (misal: https://simonter.pa-jakartautara.go.id)
   - APP_PORT         : Port HTTP host (default: 80)
   - DB_DATABASE      : Nama database (default: simonter)
   - DB_USERNAME      : Username database (default: simonter)
   - DB_PASSWORD      : Password database yang kuat
   - DB_ROOT_PASSWORD : Password root MySQL yang kuat

3. Build dan Jalankan Container Docker
   -----------------------------------
   docker compose up -d --build

   Catatan:
   Saat container `app` pertama kali berjalan, script `docker-entrypoint.sh`
   secara otomatis akan:
   - Menyiapkan hak akses folder `storage` dan `bootstrap/cache`
   - Menunggu kesiapan koneksi database MySQL
   - Membuat symbolic link `storage:link`
   - Menjalankan migrasi database (`php artisan migrate --force`)
   - Mengoptimalkan cache (`config:cache`, `route:cache`, `view:cache`, `event:cache`)

4. Generate APP_KEY & Jalankan Seeder Awal di Docker
   -------------------------------------------------
   docker compose exec app php artisan key:generate --force
   docker compose exec app php artisan config:cache
   docker compose exec app php artisan db:seed --force

5. Perintah Pemeliharaan Docker (Maintenance)
   ------------------------------------------
   - Melihat status container : docker compose ps
   - Melihat log aplikasi     : docker compose logs -f app
   - Restart layanan          : docker compose restart
   - Menghentikan layanan     : docker compose down


================================================================================
D. AKUN LOGIN DEFAULT (HASIL DATABASE SEEDER)
================================================================================
Setelah menjalankan `php artisan db:seed`, Anda dapat login menggunakan akun
default berikut (dapat menggunakan Email atau NIP):

1. Administrator
   - Email    : admin@simonter.go.id
   - NIP      : 199509062020121009
   - Password : password

2. Kepala Sub Bagian
   - Email    : kasubbag@simonter.go.id
   - NIP      : 199604152021041005
   - Password : password

3. Petugas
   - Email    : petugas@simonter.go.id
   - NIP      : 199805122022031002
   - Password : password


================================================================================
E. PENGUJIAN OTOMATIS (AUTOMATED TESTING)
================================================================================
Untuk menjalankan seluruh unit & feature test (PHPUnit):
   php artisan test --compact
================================================================================

