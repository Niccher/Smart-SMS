# syntax=docker/dockerfile:1
FROM php:8.3-apache

# ── System dependencies ────────────────────────────────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
        libicu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        zip \
        unzip \
        cron \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) intl mysqli pdo_mysql zip gd \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules — swap to prefork (mod_php requires it; event is the default)
RUN a2dismod mpm_event mpm_worker mpm_prefork || true \
    && a2enmod mpm_prefork rewrite headers

# Move DocumentRoot to public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf

# Install cron job: run CodeIgniter scheduler every minute
RUN printf 'PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin\n* * * * * root cd /var/www/html && php spark cron:run >> /var/log/mpesa-cron.log 2>&1\n' \
        > /etc/cron.d/mpesa-analyzer \
    && chmod 644 /etc/cron.d/mpesa-analyzer \
    && touch /var/log/mpesa-cron.log

WORKDIR /var/www/html

# ── Composer dependency layer ─────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --ignore-platform-reqs \
    && rm /usr/bin/composer

# ── Application code ──────────────────────────────────────────────────────────
COPY . /var/www/html

RUN mkdir -p /var/www/html/writable/cache /var/www/html/writable/session /var/www/html/writable/logs /var/www/html/writable/uploads \
    && chown -R www-data:www-data /var/www/html/writable \
    && chmod -R 775 /var/www/html/writable

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
EXPOSE 80
