FROM php:8.2-fpm

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html