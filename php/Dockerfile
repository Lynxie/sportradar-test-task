FROM php:8.2-fpm

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN groupadd -g 1000 compgroup && \
    useradd -m -u 1000 -g compgroup compuser

RUN usermod --shell /bin/bash compuser
RUN usermod -a -G www-data compuser
RUN chown -R compuser:compgroup .

USER compuser

WORKDIR /var/www/html