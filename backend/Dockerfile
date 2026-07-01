# syntax=docker/dockerfile:1

# ---------- Base : PHP-FPM + extensions ----------
FROM php:8.3-fpm-alpine AS base

RUN apk add --no-cache \
        git unzip \
        icu-dev libzip-dev \
        freetype-dev libjpeg-turbo-dev libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql intl zip opcache gd \
    && rm -rf /var/cache/apk/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Config PHP applicative (upload 8 Mo, opcache, etc.)
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini

WORKDIR /var/www/html

# ---------- Dépendances (couche cache) ----------
FROM base AS vendor

COPY composer.json composer.lock symfony.lock ./
RUN composer install \
        --no-dev --no-scripts --no-progress \
        --prefer-dist --no-interaction \
    && composer clear-cache

# ---------- Image finale ----------
FROM base AS app

# Vendor déjà installé
COPY --from=vendor /var/www/html/vendor ./vendor

# Code applicatif
COPY . .

# Autoload optimisé + dossiers runtime
RUN composer dump-autoload --no-dev --classmap-authoritative \
    && mkdir -p var/cache var/log public/uploads/articles config/jwt \
    && chown -R www-data:www-data var public/uploads config/jwt

# Entrypoint (normalise les fins de ligne au cas où le fichier vient de Windows)
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint \
    && chmod +x /usr/local/bin/entrypoint

ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]
