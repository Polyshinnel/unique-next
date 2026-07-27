FROM node:22-alpine AS frontend-builder

WORKDIR /app

COPY package*.json ./
RUN if [ -f package-lock.json ]; then npm ci; else npm install; fi

COPY . .
RUN npm run build \
    && mkdir -p /app/resources/js/.next-runtime

FROM php:8.4-fpm-alpine AS php-base

RUN apk add --no-cache \
    git curl curl-dev libcurl \
    libpng-dev libzip-dev zip unzip \
    oniguruma-dev icu-dev icu-libs \
    freetype-dev libjpeg-turbo-dev libwebp-dev \
    nginx supervisor shadow su-exec bash tzdata acl \
    nodejs npm

# PECL's package index is occasionally unavailable (and has returned an empty
# release list in production builds).  Build a pinned phpredis release directly
# from its upstream source instead of depending on that index.
ARG PHPREDIS_VERSION=6.1.0
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && curl -fsSL "https://github.com/phpredis/phpredis/archive/refs/tags/${PHPREDIS_VERSION}.tar.gz" -o /tmp/phpredis.tar.gz \
    && tar -xzf /tmp/phpredis.tar.gz -C /tmp \
    && cd "/tmp/phpredis-${PHPREDIS_VERSION}" \
    && phpize \
    && ./configure \
    && make -j"$(nproc)" \
    && make install \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/phpredis.tar.gz "/tmp/phpredis-${PHPREDIS_VERSION}" \
    && apk del .build-deps

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql bcmath exif gd intl \
        opcache pcntl zip mbstring

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

COPY . .
RUN if [ -f package-lock.json ]; then npm ci; else npm install; fi
RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi

COPY --from=frontend-builder /app/resources/js/.next-runtime ./resources/js/.next-runtime

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
