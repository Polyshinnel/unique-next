#!/bin/sh
set -e

sync_www_data_ids() {
    if [ "$(id -u)" != "0" ]; then
        return
    fi

    target_uid="${WWWUSER:-}"
    target_gid="${WWWGROUP:-}"

    if [ -z "$target_uid" ] || [ -z "$target_gid" ]; then
        return
    fi

    current_uid="$(id -u www-data)"
    current_gid="$(id -g www-data)"

    if [ "$current_gid" != "$target_gid" ]; then
        groupmod -o -g "$target_gid" www-data
    fi

    if [ "$current_uid" != "$target_uid" ]; then
        usermod -o -u "$target_uid" -g "$target_gid" www-data
    fi
}

ensure_laravel_permissions() {
    mkdir -p /var/www/html/storage/logs /var/www/html/bootstrap/cache
    touch /var/www/html/storage/logs/laravel.log /var/www/html/storage/logs/horizon.log /var/www/html/storage/logs/next.log

    if [ "$(id -u)" = "0" ]; then
        chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
    fi

    find /var/www/html/storage /var/www/html/bootstrap/cache -type d -exec chmod 2775 {} \;
    find /var/www/html/storage /var/www/html/bootstrap/cache -type f -exec chmod 664 {} \;

    if command -v setfacl >/dev/null 2>&1; then
        setfacl -R -m u:www-data:rwx,g:www-data:rwx /var/www/html/storage /var/www/html/bootstrap/cache
        setfacl -R -d -m u:www-data:rwx,g:www-data:rwx /var/www/html/storage /var/www/html/bootstrap/cache
    fi
}

sync_www_data_ids
ensure_laravel_permissions

if [ "${RUN_COMPOSER_INSTALL:-}" = "true" ]; then
    composer install
fi

if [ "${RUN_NPM_BUILD:-}" = "true" ]; then
    npm install
    npm run build
fi

if [ "${RUN_STORAGE_LINK:-}" = "true" ]; then
    php artisan storage:link
fi

if [ "$(id -u)" = "0" ] && [ "${1:-}" = "php" ] && [ "${2:-}" = "artisan" ]; then
    exec su-exec www-data "$@"
fi

exec "$@"
