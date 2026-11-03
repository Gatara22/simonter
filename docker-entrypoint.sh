#!/bin/sh
set -e

cd /var/www/html

# 1. Pastikan struktur direktori storage dan bootstrap/cache tersedia
mkdir -p \
    storage/app/public/tugas \
    storage/app/public/evidence \
    storage/app/public/foto \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 2. Tunggu koneksi database MySQL siap (jika menggunakan mysql)
if [ "${DB_CONNECTION}" = "mysql" ] && [ -n "${DB_HOST}" ]; then
    echo "[SIMONTER] Menunggu database MySQL di ${DB_HOST}:${DB_PORT:-3306}..."
    MAX_TRIES=30
    COUNT=0
    until php -r "try { new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: 3306), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); exit(0); } catch (Exception \$e) { exit(1); }" >/dev/null 2>&1; do
        COUNT=$((COUNT + 1))
        if [ "${COUNT}" -ge "${MAX_TRIES}" ]; then
            echo "[SIMONTER] Peringatan: Database belum merespons setelah ${MAX_TRIES} percobaan, melanjutkan startup..."
            break
        fi
        sleep 2
    done
    echo "[SIMONTER] Koneksi database siap."
fi

# 3. Buat symbolic link storage publik
php artisan storage:link --force --no-interaction >/dev/null 2>&1 || true

# 4. Jalankan migrasi database otomatis pada container utama (jika RUN_MIGRATIONS=true)
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "[SIMONTER] Menjalankan migrasi database production..."
    php artisan migrate --force --no-interaction
fi

# 5. Jalankan seeder awal jika diaktifkan (RUN_SEEDER=true)
if [ "${RUN_SEEDER:-false}" = "true" ]; then
    echo "[SIMONTER] Menjalankan DatabaseSeeder..."
    php artisan db:seed --force --no-interaction
fi

# 6. Optimasi cache konfigurasi, route, view, dan event Laravel untuk Production
if [ "${APP_ENV:-production}" = "production" ]; then
    echo "[SIMONTER] Mengoptimalkan cache konfigurasi, rute, dan tampilan..."
    php artisan config:cache --no-interaction
    php artisan route:cache --no-interaction
    php artisan view:cache --no-interaction
    php artisan event:cache --no-interaction
fi

echo "[SIMONTER] Container siap dijalankan."
exec "$@"

