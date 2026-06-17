# Шаг 1.3 — `docker/entrypoint.sh`

**Этап:** 1. Docker-инфраструктура  
**Статус:** [x] Выполнен

## Описание

Создать entrypoint-скрипт по образцу `supply.kidsberry.org`. Скрипт выполняется при старте контейнера перед основной командой (supervisord).

## Функции скрипта

1. **`sync_www_data_ids()`** — синхронизация UID/GID пользователя `www-data` внутри контейнера с хостовыми значениями (`WWWUSER`, `WWWGROUP` из env). Решает проблему прав на примонтированные volumes.
2. **`ensure_laravel_permissions()`** — создание необходимых директорий (`storage/logs`, `bootstrap/cache`), создание лог-файлов, установка прав `775`.
3. **Опциональные runtime-задачи** (через переменные окружения):
   - `RUN_COMPOSER_INSTALL=true` → `composer install`
   - `RUN_NPM_BUILD=true` → `npm install && npm run build`
   - `RUN_STORAGE_LINK=true` → `php artisan storage:link`
4. **su-exec** для artisan-команд — если запускается `php artisan ...` от root, переключается на `www-data`.

## Содержимое файла

```bash
#!/bin/sh
set -e

sync_www_data_ids() {
    if [ "$(id -u)" != "0" ]; then return; fi
    target_uid="${WWWUSER:-}"; target_gid="${WWWGROUP:-}"
    if [ -z "$target_uid" ] || [ -z "$target_gid" ]; then return; fi
    current_uid="$(id -u www-data)"; current_gid="$(id -g www-data)"
    [ "$current_gid" != "$target_gid" ] && groupmod -o -g "$target_gid" www-data
    [ "$current_uid" != "$target_uid" ] && usermod -o -u "$target_uid" -g "$target_gid" www-data
}

ensure_laravel_permissions() {
    mkdir -p /var/www/html/storage/logs /var/www/html/bootstrap/cache
    touch /var/www/html/storage/logs/laravel.log /var/www/html/storage/logs/horizon.log /var/www/html/storage/logs/next.log
    if [ "$(id -u)" = "0" ]; then
        chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
    fi
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
}

sync_www_data_ids
ensure_laravel_permissions

[ "${RUN_COMPOSER_INSTALL:-}" = "true" ] && composer install
[ "${RUN_NPM_BUILD:-}" = "true" ] && npm install && npm run build
[ "${RUN_STORAGE_LINK:-}" = "true" ] && php artisan storage:link

if [ "$(id -u)" = "0" ] && [ "${1:-}" = "php" ] && [ "${2:-}" = "artisan" ]; then
    exec su-exec www-data "$@"
fi

exec "$@"
```

## Важно

- Файл должен иметь **Unix line endings** (LF, не CRLF)
- Файл должен быть **исполняемым** (`chmod +x`) — это делается в Dockerfile
- Шебанг `#!/bin/sh` — используем `sh`, не `bash` (Alpine)

## Зависимости

- Шаг 1.1 (директория `docker/` существует)

## Критерий завершения

Файл `docker/entrypoint.sh` создан с корректным содержимым и LF-окончаниями строк.
