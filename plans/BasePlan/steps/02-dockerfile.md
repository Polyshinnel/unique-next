# Шаг 1.2 — Dockerfile (монолитный, в корне проекта)

**Этап:** 1. Docker-инфраструктура  
**Статус:** [x] Выполнен

## Описание

Написать многоэтапный (multi-stage) Dockerfile для монолитного контейнера `app`.

- **Базовый образ:** `php:8.4-fpm-alpine`
- **Этап 1 (frontend-builder):** сборка Vite-ассетов для Laravel через `node:22-alpine`
- **Этап 2 (php-base):** PHP 8.4 FPM + системные пакеты + PHP-расширения + Nginx + Supervisor + Node.js 22

## Содержимое файла

```dockerfile
# Этап 1: Сборка фронтенда (Vite assets для Laravel)
FROM node:22-alpine AS frontend-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Этап 2: PHP приложение
FROM php:8.4-fpm-alpine AS php-base

RUN apk add --no-cache \
    git curl curl-dev libcurl \
    libpng-dev libzip-dev zip unzip \
    oniguruma-dev icu-dev icu-libs \
    freetype-dev libjpeg-turbo-dev \
    nginx supervisor shadow su-exec \
    nodejs npm

# PHP расширения
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql bcmath exif gd intl \
        opcache pcntl zip mbstring

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

COPY . .
RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi

# Копируем собранные ассеты
COPY --from=frontend-builder /app/public/build ./public/build

# Права
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Конфиги
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

## Ключевые моменты

- **multi-stage build:** первый этап собирает Vite-ассеты, второй — финальный образ с PHP
- **phpredis** устанавливается через `pecl install redis` (не predis)
- **Composer** копируется из официального образа `composer:2`
- **Порядок COPY:** сначала `composer.json` + `composer.lock` (кеширование слоёв), потом весь код
- **EXPOSE 80** — внутренний порт nginx, проброс на хост через docker-compose

## Зависимости

- Шаг 1.1 (структура файлов должна существовать)
- Шаги 1.3–1.8 (файлы конфигов, копируемые в образ)

## Критерий завершения

Файл `Dockerfile` в корне проекта содержит корректный multi-stage build.
