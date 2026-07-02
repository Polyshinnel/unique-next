# Шаг 11. Frontend catalog API client

## Цель

Создать TypeScript API-клиент и типы для SSR-запросов каталога из Next.

## Зависимости

- Backend API из шагов 07-09.
- Существующий SSR API client: `resources/js/lib/api.ts`, `api.server.get(...)`.

## Файлы

- Создать: `resources/js/lib/catalog-api.ts`.
- Создать: `resources/js/lib/catalog-format.ts`.

## Задачи

1. Описать типы:
   - `CatalogPageResponse`;
   - `CatalogProductCard`;
   - `CatalogProductDetail`;
   - `CatalogFilterOption`;
   - `CatalogCategoryNode`;
   - `CatalogPagination`;
   - `CatalogSearchParams`;
   - `CatalogSortValue`.
2. Добавить `getCatalogPage(params)`.
3. Добавить `getCatalogCategoryByPath(path)`.
4. Добавить `getCatalogProduct(id)`.
5. Все запросы выполнять через `api.server.get(...)`.
6. Для каталога использовать `cache: 'no-store'` на первом этапе.
7. Добавить helper сборки query params:
   - не добавлять `null`, `undefined`, пустые строки;
   - не добавлять `sort=default`;
   - не добавлять `page=1`, если это принято в текущем frontend-стиле.
8. Перенести форматирование цены в `catalog-format.ts`:
   - если `!price.isPublished` или `price.amount === null`, вернуть `По запросу`;
   - иначе форматировать рубли с пробелами.

## Важные правила

- Frontend не должен пытаться показать `amount`, если `isPublished = false`.
- Типы должны отражать backend response, а не старые моки.
- Не использовать client-side fetch для списка товаров.

## Проверка

- TypeScript не ругается на импорт новых типов.
- `getCatalogPage` можно вызвать из server component.
- `formatCatalogPrice` корректно возвращает `По запросу`.

## Готово, когда

- Страницы каталога могут импортировать один клиент вместо моковых файлов.
