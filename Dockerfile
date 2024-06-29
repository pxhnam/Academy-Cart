FROM php:8.2-fpm-alpine

WORKDIR /var/www

RUN apk update \
    && apk add --no-cache \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    freetype-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo pdo_mysql \
    && docker-php-ext-install mysqli && docker-php-ext-enable mysqli \
    && docker-php-ext-install exif \
    && docker-php-ext-install zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

COPY ./docker/php/php.ini /usr/local/etc/php/
COPY . .
COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN composer install
# RUN php artisan migrate
RUN php artisan key:generate
