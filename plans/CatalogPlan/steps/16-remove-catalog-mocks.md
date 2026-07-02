# Шаг 16. Remove catalog mocks from runtime

## Цель

Удалить или изолировать старые моковые данные каталога после полной миграции на API.

## Зависимости

- Frontend root catalog page из шага 12.
- Catch-all route из шага 13.
- CatalogPageView/ProductCard из шага 14.
- Detail page из шага 15.

## Файлы

- Удалить или перестать использовать:
  - `resources/js/lib/catalog-products.ts`;
  - `resources/js/lib/catalog-categories.ts`.

## Задачи

1. Выполнить поиск:
   - `rg "catalogProducts|catalogCategoryTree|getCatalogProductsByCategory|getCatalogCategoryByPath" resources/js`.
2. Убедиться, что runtime-код каталога не импортирует моки.
3. Если файлы больше нигде не нужны, удалить их.
4. Если часть данных нужна для storybook/tests/docs, переименовать или перенести так, чтобы runtime route их не импортировал.
5. Обновить импорты, которые ломаются после удаления.

## Важные правила

- Удалять моки только после перевода detail page.
- Не удалять файлы, если они нужны вне runtime, без проверки импортов.

## Проверка

- `rg "catalogProducts|catalogCategoryTree" resources/js` не находит runtime-использований.
- `npm run build` не падает из-за удаленных импортов.

## Готово, когда

- Каталог больше не может случайно вернуться на демонстрационные данные.
