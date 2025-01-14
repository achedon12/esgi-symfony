FROM php:8.3-fpm

ENV NODE_VERSION=20.x

# install dependencies
RUN apt-get update -yq && apt-get install -yq \
    ca-certificates \
    curl \
    gnupg \
    git \
    unzip \
    libzip-dev \
    zlib1g-dev \
    libicu-dev \
    libpng-dev \
    libwebp-dev \
    libfreetype6-dev \
    libjpeg-dev \
    libjpeg62-turbo-dev \
    libxpm-dev \
    jpegoptim \
    optipng \
    pngquant \
    locales \
    libpq-dev \
    libmemcached-dev \
    htop \
    libc-client2007e-dev \
    libc-client-dev \
    libkrb5-dev \
    bash-completion \
    && apt-get clean

# install php dependencies
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    zip \
    intl \
    gd \
    pdo_mysql \
    pdo_pgsql \
    opcache \
    sockets \
    bcmath

# install node and npm
RUN mkdir -p /etc/apt/keyrings \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION nodistro main" | tee /etc/apt/sources.list.d/nodesource.list \
    && apt-get update -yq \
    && apt-get install -yq nodejs \
    && npm install -g npm

# install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . /var/www/html

WORKDIR /var/www/html

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 9000

ENTRYPOINT ["./entrypoint.sh"]

CMD ["php-fpm"]
