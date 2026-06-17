FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY vite.config.js postcss.config.js tailwind.config.js ./
RUN mkdir -p public && npm run build

FROM php:8.3-apache AS app

ARG INSTALL_DEV=false

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        default-mysql-client \
        git \
        libcurl4-openssl-dev \
        libicu-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install \
        bcmath \
        curl \
        intl \
        opcache \
        pdo_mysql \
        zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN if [ "$INSTALL_DEV" = "true" ]; then \
        composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts; \
    else \
        composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts; \
    fi

COPY . .
COPY --from=assets /app/public/build ./public/build
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php/opcache.ini /tmp/opcache.production.ini
COPY docker/php/opcache.dev.ini /tmp/opcache.dev.ini
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint

RUN if [ "$INSTALL_DEV" = "true" ]; then \
        cp /tmp/opcache.dev.ini /usr/local/etc/php/conf.d/opcache.ini; \
    else \
        cp /tmp/opcache.production.ini /usr/local/etc/php/conf.d/opcache.ini; \
    fi \
    && chmod +x /usr/local/bin/docker-entrypoint \
    && composer dump-autoload --optimize --no-interaction \
    && mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

ENTRYPOINT ["docker-entrypoint"]
CMD ["apache2-foreground"]
