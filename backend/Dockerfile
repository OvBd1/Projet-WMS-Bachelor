FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
        git unzip libicu-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql intl zip opcache \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN printf "opcache.enable=1\nopcache.memory_consumption=128\nopcache.interned_strings_buffer=8\nopcache.max_accelerated_files=4000\nopcache.revalidate_freq=0\n" \
    > /usr/local/etc/php/conf.d/opcache-prod.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

COPY . .

RUN composer install --optimize-autoloader --no-interaction --no-scripts \
    && mkdir -p var/cache var/log public/uploads/articles \
    && chown -R www-data:www-data var public/uploads

EXPOSE 80
