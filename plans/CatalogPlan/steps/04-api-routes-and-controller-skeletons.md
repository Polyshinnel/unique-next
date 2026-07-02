# Шаг 04. API routes and controller skeletons

## Цель

Добавить публичные API routes каталога и минимальные controller-классы, чтобы дальнейшие шаги подключались к стабильным endpoint.

## Зависимости

- Laravel routes.
- Namespace контроллеров проекта.

## Файлы

- Изменить: `routes/api.php`.
- Создать:
  - `app/Http/Controllers/Api/CatalogPageController.php`;
  - `app/Http/Controllers/Api/CatalogCategoryController.php`;
  - `app/Http/Controllers/Api/CatalogProductController.php`.

## Задачи

1. Импортировать новые контроллеры в `routes/api.php`.
2. Добавить routes:
   - `GET /api/catalog/page`;
   - `GET /api/catalog/categories/by-path`;
   - `GET /api/catalog/products/{product}`.
3. Создать controller skeletons:
   - `CatalogPageController::show(CatalogPageRequest $request)`;
   - `CatalogCategoryController::byPath(Request $request)`;
   - `CatalogProductController::show(Product $product)` или `show(int $product)`, если нужен ручной query для 404 по публичным правилам.
4. Для временной реализации вернуть понятные заглушки или сразу подключить actions последующих шагов.

## Важные правила

- Не делать catch-all route в API.
- `/page` возвращает все данные для SSR списка.
- `/categories/by-path` нужен для metadata и slug resolution.
- `/products/{product}` работает по числовому id.

## Проверка

- `php artisan route:list` показывает три новых endpoint.
- URL не конфликтуют с существующими API routes.

## Готово, когда

- API-контракты доступны по правильным URL.
- Контроллеры готовы принимать доменную логику следующих шагов.
