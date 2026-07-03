# Шаг 04. Включить WebP в GD внутри Docker

## Цель

Обновить Docker-образ так, чтобы PHP GD был собран с поддержкой WebP. Без этого сервис конвертации будет падать на кодировании WebP.

## Зависимости

- Нужен существующий `Dockerfile`.
- Проверка фактической поддержки будет выполнена на шаге 13 после пересборки.

## Файлы

- Изменить: `Dockerfile`.

## Задачи

1. Найти блок установки Alpine-пакетов через `apk add`.
2. Добавить пакет `libwebp-dev` рядом с графическими зависимостями:

```dockerfile
RUN apk add --no-cache \
    git curl curl-dev libcurl \
    libpng-dev libzip-dev zip unzip \
    oniguruma-dev icu-dev icu-libs \
    freetype-dev libjpeg-turbo-dev libwebp-dev \
    nginx supervisor shadow su-exec bash tzdata \
    nodejs npm
```

3. Найти конфигурацию GD.
4. Добавить флаг `--with-webp`:

```dockerfile
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql bcmath exif gd intl \
        opcache pcntl zip mbstring
```

5. Не менять остальные расширения и пакеты без необходимости.
6. Не считать локальный PHP источником истины: проверять поддержку надо внутри контейнера `app`.

## Проверка

На этом шаге достаточно проверить diff `Dockerfile`.
Полная проверка после пересборки описана в шаге 13.

## Готово, когда

- В `apk add` есть `libwebp-dev`.
- В `docker-php-ext-configure gd` есть `--with-webp`.
- Остальная Docker-конфигурация не переписана сверх нужного.
