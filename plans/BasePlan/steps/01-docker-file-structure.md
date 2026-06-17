# Шаг 1.1 — Создать структуру файлов Docker

**Этап:** 1. Docker-инфраструктура  
**Статус:** [x] Выполнен

## Описание

Создать дерево каталогов и пустые файлы для Docker-конфигурации проекта.

## Целевая структура

```
Dockerfile                        # Монолитный: PHP 8.4 FPM + Nginx + Node.js 22 + Supervisor
.dockerignore
docker/
├── entrypoint.sh                 # Права, storage dirs, опциональные runtime-задачи
├── nginx/
│   └── default.conf              # Nginx конфиг (fastcgi → 127.0.0.1:9000, proxy → 127.0.0.1:3000)
├── php/
│   ├── php.ini                   # memory_limit, upload, opcache
│   └── www.conf                  # PHP-FPM pool config
├── supervisor/
│   └── supervisord.conf          # php-fpm + nginx + next + horizon
└── mysql/
    └── my.cnf                    # utf8mb4, strict mode
```

## Действия

1. Создать директорию `docker/` в корне проекта
2. Создать поддиректории: `docker/nginx/`, `docker/php/`, `docker/supervisor/`, `docker/mysql/`
3. Создать пустые файлы-заглушки (содержимое заполняется на следующих шагах):
   - `Dockerfile`
   - `.dockerignore`
   - `docker/entrypoint.sh`
   - `docker/nginx/default.conf`
   - `docker/php/php.ini`
   - `docker/php/www.conf`
   - `docker/supervisor/supervisord.conf`
   - `docker/mysql/my.cnf`

## Критерий завершения

Все директории и файлы из списка существуют в файловой системе.

## Зависимости

Нет — это первый шаг.
