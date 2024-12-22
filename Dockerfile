FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libxml2-dev \
        libzip-dev \
        && docker-php-ext-configure gd --with-freetype --with-jpeg \
        && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql mysqli zip

COPY . /var/www/html

WORKDIR /var/www/html
