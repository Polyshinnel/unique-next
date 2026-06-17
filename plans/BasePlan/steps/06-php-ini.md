# Шаг 1.6 — `docker/php/php.ini`

**Этап:** 1. Docker-инфраструктура  
**Статус:** [x] Выполнен

## Описание

Кастомная конфигурация PHP, которая подключается как `custom.ini` в `conf.d/`. Настраивает лимиты памяти, загрузки файлов и OPcache.

## Содержимое файла

```ini
[PHP]
memory_limit = 256M
upload_max_filesize = 64M
post_max_size = 64M
max_execution_time = 300
max_input_time = 300

[opcache]
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

## Пояснение параметров

| Параметр | Значение | Зачем |
|---|---|---|
| `memory_limit` | 256M | Достаточно для Laravel + Horizon |
| `upload_max_filesize` | 64M | Согласовано с nginx `client_max_body_size` |
| `post_max_size` | 64M | Должен быть ≥ `upload_max_filesize` |
| `max_execution_time` | 300 | 5 минут на тяжёлые запросы |
| `opcache.enable` | 1 | Кеширование байткода PHP |
| `opcache.memory_consumption` | 128M | Размер кеша OPcache |
| `opcache.revalidate_freq` | 2 | Проверка изменений файлов каждые 2 сек (dev) |

## Зависимости

- Шаг 1.1 (директория `docker/php/` существует)

## Критерий завершения

Файл `docker/php/php.ini` создан с указанными настройками.
