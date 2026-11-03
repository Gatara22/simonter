# ==============================================================================
# STAGE 1: Composer Dependencies (Production)
# ==============================================================================
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# ==============================================================================
# STAGE 2: Frontend Assets Build (Vite + Tailwind CSS v4)
# ==============================================================================
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json ./
RUN npm install --ignore-scripts

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm run build

# ==============================================================================
# STAGE 3: Application Runtime (PHP 8.3 FPM Alpine - Production)
# ==============================================================================
FROM php:8.3-fpm-alpine AS app

LABEL maintainer="Pengadilan Agama Jakarta Utara - SIMONTER"

ENV TZ=Asia/Jakarta \
    APP_ENV=production \
    APP_DEBUG=false \
    COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

# Install system dependencies & PHP extensions (termasuk GD & DOM untuk DomPDF, Zip, Intl, MySQL, Redis)
RUN apk add --no-cache \
        tzdata \
        curl \
        libpng \
        libjpeg-turbo \
        freetype \
        libwebp \
        libzip \
        icu-libs \
        oniguruma \
        libxml2 \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libwebp-dev \
        libzip-dev \
        icu-dev \
        oniguruma-dev \
        libxml2-dev \
    && cp /usr/share/zoneinfo/Asia/Jakarta /etc/localtime \
    && echo "Asia/Jakarta" > /etc/timezone \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        xml \
        dom \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps \
    && rm -rf /tmp/pear /var/cache/apk/*

# Konfigurasi PHP & OPcache khusus Production (mendukung upload berkas tugas/evidence s.d. 15MB)
RUN { \
        echo "expose_php = Off"; \
        echo "display_errors = Off"; \
        echo "display_startup_errors = Off"; \
        echo "log_errors = On"; \
        echo "error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT"; \
        echo "memory_limit = 256M"; \
        echo "max_execution_time = 60"; \
        echo "upload_max_filesize = 15M"; \
        echo "post_max_size = 16M"; \
        echo "date.timezone = Asia/Jakarta"; \
        echo "opcache.enable = 1"; \
        echo "opcache.enable_cli = 1"; \
        echo "opcache.memory_consumption = 192"; \
        echo "opcache.interned_strings_buffer = 16"; \
        echo "opcache.max_accelerated_files = 20000"; \
        echo "opcache.validate_timestamps = 0"; \
        echo "opcache.save_comments = 1"; \
    } > /usr/local/etc/php/conf.d/99-simonter-production.ini

# Konfigurasi PHP-FPM pool tuning untuk Production
RUN { \
        echo "[www]"; \
        echo "pm = dynamic"; \
        echo "pm.max_children = 25"; \
        echo "pm.start_servers = 5"; \
        echo "pm.min_spare_servers = 3"; \
        echo "pm.max_spare_servers = 10"; \
        echo "pm.max_requests = 500"; \
        echo "clear_env = no"; \
        echo "catch_workers_output = yes"; \
        echo "decorate_workers_output = no"; \
    } > /usr/local/etc/php-fpm.d/zz-simonter-fpm.conf

# Salin source code aplikasi
COPY --chown=www-data:www-data . /var/www/html

# Salin vendor dari stage composer dan hasil build Vite dari stage frontend
COPY --from=vendor --chown=www-data:www-data /app/vendor /var/www/html/vendor
COPY --from=frontend --chown=www-data:www-data /app/public/build /var/www/html/public/build

# Generate optimized classmap autoloader tanpa dev dependencies
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative --no-interaction \
    && rm -f /usr/local/bin/composer

# Siapkan entrypoint script (pastikan format LF apabila dibuild dari host Windows)
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php-fpm"]

# ==============================================================================
# STAGE 4: Webserver (Nginx 1.27 Alpine - Production)
# ==============================================================================
FROM nginx:1.27-alpine AS webserver

ENV TZ=Asia/Jakarta

RUN apk add --no-cache tzdata \
    && cp /usr/share/zoneinfo/Asia/Jakarta /etc/localtime \
    && echo "Asia/Jakarta" > /etc/timezone

# Salin aset publik statis (termasuk sbadmin2 dan hasil build Vite)
COPY --from=app /var/www/html/public /var/www/html/public

# Buat konfigurasi Nginx Production
RUN cat <<'EOF' > /etc/nginx/conf.d/default.conf
server {
    listen 80;
    server_name _;
    root /var/www/html/public;
    index index.php index.html;

    charset utf-8;
    client_max_body_size 15M;
    server_tokens off;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_min_length 1024;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/json
        application/javascript
        application/xml
        application/rss+xml
        image/svg+xml;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    # Cache Static Assets (SBAdmin2, Vite Build, Images, Fonts)
    location ~* \.(?:css|js|jpg|jpeg|gif|png|ico|cur|gz|svg|svgz|mp4|ogg|ogv|webm|htc|woff|woff2|ttf|eot)$ {
        expires 30d;
        access_log off;
        add_header Cache-Control "public, no-transform";
        try_files $uri /index.php?$query_string;
    }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 60s;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

EXPOSE 80

