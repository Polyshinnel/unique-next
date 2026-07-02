# Шаг 03. Catalog query builder

## Цель

Создать общий builder/support-класс для публичной выборки товаров каталога, чтобы список, счетчики, поиск и детальная страница использовали одинаковые правила публикации.

## Зависимости

- `CatalogPageRequest` из шага 01.
- Category path resolver из шага 02.
- Модель `Product` и связи:
  - `productStatus`;
  - `category`;
  - `mainImage`;
  - `equipmentAvailability`;
  - `equipmentState`;
  - `regions`;
  - `tags`.

## Файлы

- Создать: `app/Domain/Catalog/Support/CatalogQuery.php`.
- При необходимости создать action: `app/Domain/Catalog/Actions/SearchCatalogProductsAction.php`.

## Задачи

1. Создать метод базовой публичной выборки:
   - `whereNotNull('published_at')`;
   - `whereHas('productStatus', name = 'В продаже')`;
   - учитывать soft delete через модель, не отключая global scope.
2. Добавить eager load для списка:
   - `category`;
   - `mainImage`;
   - `productStatus`;
   - `equipmentAvailability`;
   - `equipmentState`;
   - `regions`.
3. Добавить применение category filter:
   - если есть активная категория, получить id категории и потомков;
   - добавить `whereIn('category_id', $categoryIds)`.
4. Добавить фильтр региона через `product_region`:
   - `whereHas('regions', fn ($q) => $q->whereKey($regionId))`.
5. Добавить фильтр доступности:
   - `where('equipment_availability_id', $availabilityId)`.
6. Добавить фильтр состояния:
   - `where('equipment_state_id', $stateId)`.
7. Добавить поиск по:
   - `products.title`;
   - `products.name`;
   - `products.sku`;
   - `tags.name`.
8. Для `LIKE` экранировать `%`, `_`, `\`.
9. Добавить сортировки:
   - `default`: `id desc`;
   - `price_desc`: опубликованная цена сначала, затем `price desc`, затем `id desc`;
   - `price_asc`: опубликованная цена сначала, затем `price asc`, затем `id desc`.
10. Сделать методы применения фильтров так, чтобы можно было исключать один активный фильтр при расчете счетчиков.

## Важные правила

- Все публичные выборки должны требовать статус `В продаже`.
- `product_region` является основным источником фильтра региона.
- Fallback на `products.region_id` не добавлять на этом этапе.
- Для счетчиков использовать clone query или фабричный метод, а не отдельные руками собранные правила.

## Проверка

- По умолчанию товары сортируются по `id desc`.
- `region` фильтрует через связь `regions`.
- Поиск с `%` или `_` не ломает LIKE.
- Товары без статуса `В продаже` не попадают ни в одну публичную выборку.

## Готово, когда

- Контроллеры и actions могут получить одинаково отфильтрованный query из одного места.
- Набор правил не нужно дублировать для списка, счетчиков и detail API.
