# Шаг 07. Catalog page API

## Цель

Реализовать `GET /api/catalog/page`, который одним запросом возвращает все данные для SSR-страницы каталога.

## Зависимости

- `CatalogPageRequest` из шага 01.
- Category resolver из шага 02.
- Product query builder из шага 03.
- API Resources из шага 05.
- Filter counters из шага 06.

## Файлы

- Создать: `app/Domain/Catalog/Actions/BuildCatalogPageAction.php`.
- Изменить: `app/Http/Controllers/Api/CatalogPageController.php`.

## Задачи

1. Прочитать нормализованные query params из `CatalogPageRequest`.
2. Если передан `category_path`, разрешить категорию.
3. Если `category_path` не найден, вернуть `404`.
4. Построить product query:
   - публичная база;
   - category descendants;
   - region;
   - availability;
   - state;
   - search;
   - sort.
5. Выполнить paginate:
   - `perPage = 12`;
   - `page` из request.
6. Построить фильтры через `BuildCatalogFiltersAction`.
7. Вернуть response:
   - `category`;
   - `filters`;
   - `sorting`;
   - `products`;
   - `pagination`.

## Важные правила

- `perPage` фиксирован: 12.
- Все товары в ответе должны быть SSR-ready: готовые `href`, `imageUrl`, `price`.
- `category` равен `null` для `/catalog`.
- Ошибка несуществующей категории должна быть именно `404`, чтобы Next мог вызвать `notFound()`.

## Проверка

- `/api/catalog/page` возвращает 200.
- `/api/catalog/page?page=2` возвращает другую страницу.
- `/api/catalog/page?category_path=unknown` возвращает 404.
- `/api/catalog/page?sort=price_desc` сортирует цену по убыванию, товары без цены в конце.
- `/api/catalog/page?search=16К20` применяет поиск.

## Готово, когда

- Endpoint полностью покрывает SSR списка каталога и страницы категории.
- Frontend больше не нуждается в моковых товарах для выдачи.
