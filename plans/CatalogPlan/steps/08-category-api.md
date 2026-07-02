# Шаг 08. Category API

## Цель

Реализовать `GET /api/catalog/categories/by-path`, который возвращает данные категории по полному path.

## Зависимости

- Category resolver из шага 02.
- API Resources из шага 05.

## Файлы

- Изменить: `app/Http/Controllers/Api/CatalogCategoryController.php`.

## Задачи

1. Прочитать query param `path`.
2. Нормализовать path:
   - trim;
   - убрать слеши по краям;
   - пустое значение считать невалидным для этого endpoint.
3. Найти категорию через resolver.
4. Если категория не найдена, вернуть `404`.
5. Вернуть:
   - `id`;
   - `name`;
   - `title`;
   - `description`;
   - `slug`;
   - `path`;
   - `href`;
   - `breadcrumbs`.

## Использование

- `generateMetadata()` category pages.
- Разрешение slug в `resources/js/app/catalog/[...slug]/page.tsx`, если понадобится отдельная проверка.

## Проверка

- Реальный path категории возвращает 200.
- Несуществующий path возвращает 404.
- В ответе есть canonical `href`.

## Готово, когда

- Frontend может получить SEO-данные категории без мокового дерева.
