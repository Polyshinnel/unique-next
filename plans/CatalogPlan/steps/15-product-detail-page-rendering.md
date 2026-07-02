# Шаг 15. Product detail page rendering

## Цель

Подключить детальную страницу товара к `GET /api/catalog/products/{id}` и убрать зависимость от моковых товаров.

## Зависимости

- Product detail API из шага 09.
- Catch-all route из шага 13.
- Frontend API client из шага 11.

## Файлы

- Изменить компоненты детальной страницы товара, которые сейчас используют моковый `catalogProducts`.
- Точный список файлов определить через `rg "catalogProducts|getCatalogProduct" resources/js`.

## Задачи

1. Найти все runtime-использования мокового товара.
2. Заменить входной тип detail component на `CatalogProductDetail`.
3. Рендерить:
   - title;
   - sku;
   - description/summary;
   - price;
   - category;
   - region;
   - availability;
   - state;
   - manager;
   - images;
   - tags;
   - characteristicBlocks.
4. Использовать `formatCatalogPrice`.
5. Сохранить canonical redirect в route, а не в UI component.
6. Убедиться, что пустые blocks/manager/images не ломают страницу.

## Важные правила

- Детальная страница не должна обращаться к мокам.
- `show_price = false` всегда отображается как `По запросу`.
- Product URL строится и проверяется через `canonicalHref`.

## Проверка

- Реальный товар открывается.
- Товар без опубликованной цены показывает `По запросу`.
- Страница не падает при отсутствующих изображениях или характеристиках.
- `rg "catalogProducts|getCatalogProduct" resources/js` не показывает runtime-зависимость от моков.

## Готово, когда

- Detail page полностью работает от backend API.
