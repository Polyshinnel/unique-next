# Шаг 09. Product detail API

## Цель

Реализовать `GET /api/catalog/products/{id}`, чтобы детальная страница товара тоже работала от реальной БД и не зависела от моков.

## Зависимости

- Product query builder из шага 03.
- Category resolver из шага 02.
- API Resources из шага 05.

## Файлы

- Создать: `app/Domain/Catalog/Actions/ResolveCatalogProductHrefAction.php`.
- Изменить: `app/Http/Controllers/Api/CatalogProductController.php`.
- Использовать: `CatalogProductDetailResource`.

## Задачи

1. Ищем товар по id через публичную базу:
   - `published_at is not null`;
   - статус `В продаже`;
   - без soft delete.
2. Если товар не найден или не публичен, вернуть `404`.
3. Eager load:
   - `category.parent`;
   - `images`;
   - `productStatus`;
   - `equipmentAvailability`;
   - `equipmentState`;
   - `regions`;
   - `manager`;
   - `tags`;
   - `mainCharacteristics`;
   - `complectation`;
   - `technicalCharacteristics`;
   - `mainInfo`;
   - `additionalInfo`.
4. Построить `canonicalHref`:
   - `/catalog/{category-path}/{product-id}`.
5. Вернуть detail shape:
   - `id`;
   - `title`;
   - `sku`;
   - `description`;
   - `summary`;
   - `canonicalHref`;
   - `category`;
   - `region`;
   - `price`;
   - `availability`;
   - `state`;
   - `manager`;
   - `images`;
   - `tags`;
   - `characteristicBlocks`.
6. Собрать `characteristicBlocks` из доступных таблиц характеристик, не возвращая пустые блоки.

## Важные правила

- Детальная страница не может показывать товар не в статусе `В продаже`.
- Правило цены такое же, как в карточке: `show_price = false` означает `По запросу`.
- `canonicalHref` должен совпадать с frontend route.

## Проверка

- Публичный товар открывается по API.
- Товар без `published_at` или не `В продаже` возвращает 404.
- В ответе есть `canonicalHref`.
- У товара с `show_price = false` возвращается `price.isPublished = false`.

## Готово, когда

- Catch-all frontend route может полностью отказаться от мокового `getCatalogProduct`.
