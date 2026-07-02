# Шаг 05. API resources and response shapes

## Цель

Описать единый JSON-формат каталога через API Resources или аккуратные mapper-классы, чтобы frontend получил стабильный контракт.

## Зависимости

- Category tree/path resolver из шага 02.
- Product query builder из шага 03.

## Файлы

- Создать в `app/Http/Resources/Catalog/`:
  - `CatalogCategoryResource.php`;
  - `CatalogFilterOptionResource.php`;
  - `CatalogPageResource.php`;
  - `CatalogProductCardResource.php`;
  - `CatalogProductDetailResource.php`.
- Если проект не использует Resources для подобных API, создать эквивалентные mapper-классы в доменном слое.

## Задачи

1. Описать category shape:
   - `id`;
   - `name`;
   - `title`;
   - `description`;
   - `slug`;
   - `path`;
   - `href`;
   - `breadcrumbs`.
2. Описать product card shape:
   - `id`;
   - `title`;
   - `sku`;
   - `category`;
   - `region`;
   - `price`;
   - `availability`;
   - `state`;
   - `imageUrl`;
   - `href`.
3. Описать price shape:
   - `amount`;
   - `isPublished`;
   - `comment`.
4. `price.isPublished` вычислять строго из `products.show_price`.
5. Если `show_price = false`, не разрешать frontend показать цену, даже если `amount` числовой.
6. Для `region` брать первый регион из `regions`, fallback на `products.region` добавлять только если такая связь уже есть и это нужно для карточки.
7. Описать filter option shape:
   - `id`;
   - `name`;
   - `count`;
   - `href`;
   - дополнительные поля для категории: `slug`, `level`, `children`.
8. Описать pagination:
   - `currentPage`;
   - `perPage`;
   - `total`;
   - `totalPages`.
9. Описать sorting:
   - `active`;
   - `options`.

## Важные правила

- `href` товара строить на backend через canonical category path: `/catalog/{category-path}/{product-id}`.
- `imageUrl` должен быть готовой строкой для frontend.
- JSON должен совпадать с контрактом из `CatalogPlan.md`.

## Проверка

- Product card без цены возвращает `price.isPublished = false` или `amount = null`.
- У товара есть готовый `href`.
- Категория содержит готовый `href` и `path`.

## Готово, когда

- Backend может отдавать JSON без frontend-знания внутренних моделей Laravel.
- Все поля, нужные `CatalogPageView` и `ProductCard`, есть в ответе.
