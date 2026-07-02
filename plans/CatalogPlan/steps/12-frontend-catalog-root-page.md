# Шаг 12. Frontend root catalog page

## Цель

Перевести `/catalog` на SSR-данные из Laravel API.

## Зависимости

- Frontend API client из шага 11.
- Backend `/api/catalog/page` из шага 07.

## Файлы

- Изменить: `resources/js/app/catalog/page.tsx`.

## Задачи

1. Прочитать `searchParams`:
   - `page`;
   - `region`;
   - `availability`;
   - `state`;
   - `sort`;
   - `search`.
2. Передать параметры в `getCatalogPage`.
3. Не передавать `category_path` для root каталога.
4. Передать полученный `data` в `CatalogPageView`.
5. Оставить `generateMetadata()` через `getPageSeo('catalog')`.
6. Убедиться, что route остается server component.

## Важные правила

- Список товаров приходит на сервере.
- `/catalog?page=2`, `/catalog?search=...`, `/catalog?region=...` должны рендерить готовый HTML.
- Не использовать `catalogProducts` и `catalogCategoryTree`.

## Проверка

- `/catalog` открывается.
- HTML ответа содержит названия реальных товаров.
- Query params меняют данные без client-side fetch.

## Готово, когда

- Root каталог больше не зависит от моков.
