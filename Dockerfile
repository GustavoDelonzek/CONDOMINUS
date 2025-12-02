FROM php:8.4-fpm

ARG UID=1000
ARG GID=1000

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    curl

RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd sockets zip \
    && pecl install redis \
    && docker-php-ext-enable redis

RUN groupmod -g $GID www-data && \
    usermod -u $UID -g $GID www-data

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN chown -R www-data:www-data /var/www
