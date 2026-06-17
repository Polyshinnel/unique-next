# Шаг 1.7 — `docker/php/www.conf`

**Этап:** 1. Docker-инфраструктура  
**Статус:** [x] Выполнен

## Описание

Конфигурация PHP-FPM pool `[www]`. Определяет пользователя, адрес прослушивания и параметры управления процессами.

## Содержимое файла

```ini
[www]
user = www-data
group = www-data
listen = 127.0.0.1:9000
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
```

## Пояснение параметров

| Параметр | Значение | Зачем |
|---|---|---|
| `user` / `group` | www-data | Процессы FPM работают от этого пользователя |
| `listen` | 127.0.0.1:9000 | Nginx подключается к FPM по TCP внутри контейнера |
| `pm` | dynamic | Динамическое управление количеством worker-процессов |
| `pm.max_children` | 50 | Максимум одновременных worker-процессов |
| `pm.start_servers` | 5 | Начальное количество worker-процессов |

## Зависимости

- Шаг 1.1 (директория `docker/php/` существует)

## Критерий завершения

Файл `docker/php/www.conf` создан.
