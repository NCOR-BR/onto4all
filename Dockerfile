# PHP
FROM php:8.2-apache AS php

RUN apt-get update -y && apt-get install -y \
    unzip \
    libonig-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install -j"$(nproc)" \
    pdo_mysql \
    bcmath \
    mbstring

WORKDIR /var/www

COPY --from=composer:2.7.7 /usr/bin/composer /usr/bin/composer
COPY Docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY Docker/entrypoint.sh /usr/local/bin/app-entrypoint
COPY . .

RUN chmod +x /usr/local/bin/app-entrypoint \
    && mkdir -p /var/www/storage /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && a2enmod rewrite

EXPOSE 80

ENTRYPOINT ["app-entrypoint"]
CMD ["apache2-foreground"]
