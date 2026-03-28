FROM php:8.3-cli-alpine

# System dependencies
RUN apk add --no-cache \
    curl-dev \
    git \
    libzip-dev \
    oniguruma-dev \
    sqlite-dev \
    unzip

# PHP extensions
RUN docker-php-ext-install \
    curl \
    mbstring \
    pdo \
    pdo_sqlite \
    zip

# phpredis (build tools removed after install)
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

RUN addgroup -g 1000 www && adduser -u 1000 -G www -s /bin/sh -D www
USER www

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0"]
