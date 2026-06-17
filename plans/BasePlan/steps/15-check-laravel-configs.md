# Шаг 2.3 — Проверить конфигурацию Laravel

**Этап:** 2. Настройка Laravel под MySQL + Redis  
**Статус:** [x] Выполнен

## Описание

Проверить, что стандартные конфигурационные файлы Laravel корректно настроены для работы с MySQL и Redis. Все настройки уже есть в Laravel из коробки, но нужно убедиться, что ничего не сломано.

## Файлы для проверки

### `config/database.php`

- Убедиться, что connection `mysql` присутствует и содержит:
  - `'host' => env('DB_HOST', '127.0.0.1')`
  - `'port' => env('DB_PORT', '3306')`
  - `'database' => env('DB_DATABASE', 'laravel')`
  - `'username' => env('DB_USERNAME', 'root')`
  - `'password' => env('DB_PASSWORD', '')`
  - `'charset' => 'utf8mb4'`
  - `'collation' => 'utf8mb4_unicode_ci'`

### `config/cache.php`

- Убедиться, что Redis store настроен:
  - `'redis' => ['driver' => 'redis', 'connection' => 'cache', ...]`

### `config/queue.php`

- Убедиться, что Redis connection настроен:
  - `'redis' => ['driver' => 'redis', 'connection' => 'default', 'queue' => 'default', ...]`

### `config/session.php`

- Убедиться, что поддержка Redis driver есть:
  - `'driver' => env('SESSION_DRIVER', 'database')`

## Действия

1. Открыть каждый файл и проверить наличие Redis/MySQL секций
2. Если что-то отсутствует — добавить (маловероятно для свежего Laravel 13)
3. Не менять значения по умолчанию — всё должно читаться из `.env`

## Зависимости

- Шаг 2.1 (`.env.example` обновлён)

## Проверка выполнения

- В [config/database.php](/home/andrey/projects/uniqset2.com/config/database.php:47) присутствует connection `mysql` с `host`, `port`, `database`, `username`, `password`, `charset` и `collation`, читаемыми из `env(...)`
- В [config/database.php](/home/andrey/projects/uniqset2.com/config/database.php:146) присутствуют Redis connections `default` и `cache`
- В [config/cache.php](/home/andrey/projects/uniqset2.com/config/cache.php:81) настроен store `redis` с Redis driver и connection `cache`
- В [config/queue.php](/home/andrey/projects/uniqset2.com/config/queue.php:67) настроен queue connection `redis` с connection `default` и queue `default`
- В [config/session.php](/home/andrey/projects/uniqset2.com/config/session.php:21) драйвер сессий читается из `SESSION_DRIVER`, что позволяет использовать `redis`

## Критерий завершения

Все четыре конфиг-файла содержат корректные секции для MySQL и Redis. Изменения не требуются (если Laravel стандартный).
