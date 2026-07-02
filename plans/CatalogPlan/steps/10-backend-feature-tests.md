# Шаг 10. Backend feature tests

## Цель

Зафиксировать поведение API каталога тестами до подключения frontend или сразу после backend-реализации.

## Зависимости

- Backend API из шагов 07-09.
- Фабрики или seed-данные для товаров, категорий, справочников.

## Файлы

- Создать или расширить feature tests каталога, например:
  - `tests/Feature/Api/CatalogPageTest.php`;
  - `tests/Feature/Api/CatalogProductTest.php`;
  - `tests/Feature/Api/CatalogCategoryTest.php`.

## Задачи

1. Проверить `GET /api/catalog/page`:
   - возвращает 200;
   - возвращает 12 товаров на страницу;
   - pagination содержит `total`, `currentPage`, `totalPages`.
2. Проверить публичность:
   - товары не в статусе `В продаже` не попадают в список;
   - товары не в статусе `В продаже` дают 404 на detail API.
3. Проверить цену:
   - `show_price = false` возвращает `price.isPublished = false`;
   - frontend-совместимый response не раскрывает цену как опубликованную.
4. Проверить сортировки:
   - `default` по `id desc`;
   - `price_desc`;
   - `price_asc`;
   - товары без опубликованной цены в конце.
5. Проверить поиск:
   - по `title`;
   - по `name`;
   - по `sku`;
   - по `tags.name`.
6. Проверить фильтры:
   - `region` через `product_region`;
   - `availability`;
   - `state`;
   - `category_path` включает descendants.
7. Проверить ошибки:
   - несуществующий `category_path` возвращает 404;
   - несуществующий category API path возвращает 404.
8. Проверить счетчики:
   - региональные учитывают активные фильтры, кроме активного региона;
   - category count включает descendants.

## Важные правила

- Тестовые данные должны явно различать `published_at`, статус, цену, регион и категорию.
- Не полагаться на локальную импортированную БД в тестах, если suite использует отдельную test database.

## Проверка

- Запустить релевантные PHP tests.
- Если весь suite тяжелый, сначала запустить только новые tests.

## Готово, когда

- Ключевые backend-контракты защищены feature tests.
- Ошибки в фильтрах/сортировках ловятся до frontend.
