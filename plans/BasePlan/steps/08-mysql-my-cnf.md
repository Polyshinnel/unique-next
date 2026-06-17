# Шаг 1.8 — `docker/mysql/my.cnf`

**Этап:** 1. Docker-инфраструктура  
**Статус:** [ ] Не выполнен

## Описание

Кастомная конфигурация MySQL 8.4. Устанавливает кодировку `utf8mb4` и строгий SQL-режим.

## Содержимое файла

```ini
[client]
default-character-set = utf8mb4

[mysqld]
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
innodb_file_per_table = 1
max_connections = 100
sql_mode = STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION
```

## Пояснение параметров

| Параметр | Значение | Зачем |
|---|---|---|
| `character-set-server` | utf8mb4 | Полная поддержка Unicode (включая эмодзи) |
| `collation-server` | utf8mb4_unicode_ci | Стандартная сортировка для Laravel |
| `innodb_file_per_table` | 1 | Каждая таблица в отдельном файле (удобство управления) |
| `sql_mode` | STRICT_TRANS_TABLES,... | Строгий режим, совместимый с Laravel |

## Зависимости

- Шаг 1.1 (директория `docker/mysql/` существует)

## Критерий завершения

Файл `docker/mysql/my.cnf` создан.
