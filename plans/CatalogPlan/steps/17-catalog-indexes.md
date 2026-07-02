# Шаг 17. Catalog indexes

## Цель

Добавить индексы, которые поддерживают фильтры, сортировку и joins каталога.

## Зависимости

- Понимание текущих индексов в миграциях.
- Backend query из шага 03.

## Файлы

- Создать новую Laravel migration для индексов каталога.

## Задачи

1. Проверить существующие индексы в миграциях или через schema inspection.
2. Добавить недостающие индексы:
   - `products`: `published_at, id`;
   - `products`: `category_id, published_at`;
   - `products`: `product_status_id, published_at`;
   - `products`: `equipment_availability_id, published_at`;
   - `products`: `equipment_state_id, published_at`;
   - `products`: `show_price, price`;
   - `product_region`: `region_id, product_id`;
   - `product_tag`: `tag_id, product_id`;
   - `tags`: `name`.
3. Не создавать дубликаты уже существующих индексов.
4. В `down()` корректно удалять только добавленные индексы.
5. Применить миграцию на локальной БД.

## Важные правила

- Обычный индекс по `tags.name` почти не ускорит `LIKE "%query%"`, но полезен для будущих вариантов поиска.
- FULLTEXT не добавлять в первой версии, если задача только в текущем плане.
- Миграция должна быть безопасной для повторного деплоя.

## Проверка

- `php artisan migrate` проходит.
- Нет ошибки duplicate index.
- `php artisan migrate:rollback --step=1` корректно откатывает индексы, если это безопасно выполнять в текущей среде.

## Готово, когда

- Основные фильтры каталога имеют поддержку индексами.
