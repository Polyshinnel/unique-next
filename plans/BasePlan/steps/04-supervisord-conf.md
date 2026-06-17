# Шаг 1.4 — `docker/supervisor/supervisord.conf`

**Этап:** 1. Docker-инфраструктура  
**Статус:** [x] Выполнен

## Описание

Конфигурация Supervisor для управления процессами внутри монолитного контейнера `app`. Supervisor запускает и следит за тремя процессами:

1. **php-fpm** — обработка PHP-запросов
2. **nginx** — веб-сервер / reverse proxy
3. **horizon** — Laravel Horizon (обработка очередей Redis)

## Содержимое файла

```ini
[supervisord]
nodaemon=true
user=root

[program:php-fpm]
command=/usr/local/sbin/php-fpm --nodaemonize
autostart=true
autorestart=true
stderr_logfile=/var/log/php-fpm.err.log
stdout_logfile=/var/log/php-fpm.out.log

[program:nginx]
command=/usr/sbin/nginx -g 'daemon off;'
autostart=true
autorestart=true
stderr_logfile=/var/log/nginx.err.log
stdout_logfile=/var/log/nginx.out.log

[program:horizon]
process_name=%(program_name)s
command=php /var/www/html/artisan horizon
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/horizon.log
stopwaitsecs=3600
```

## Ключевые моменты

- **`nodaemon=true`** — Supervisor остаётся на переднем плане (требование Docker — PID 1 не должен демонизироваться)
- **`user=root`** для supervisord — нужен для управления разными процессами под разными пользователями
- **Horizon** запускается от `www-data`, логирует в `storage/logs/horizon.log`
- **`stopwaitsecs=3600`** для Horizon — даёт время завершить текущие задачи при остановке
- **`stopasgroup=true` + `killasgroup=true`** — корректное завершение дочерних процессов Horizon

## Зависимости

- Шаг 1.1 (директория `docker/supervisor/` существует)

## Критерий завершения

Файл `docker/supervisor/supervisord.conf` создан с тремя программами: php-fpm, nginx, horizon.
