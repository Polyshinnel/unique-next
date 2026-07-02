# Шаг 14. CatalogPageView and ProductCard

## Цель

Переделать UI каталога с моковых данных на props из API, сохранив SSR-friendly фильтры, сортировку, поиск и пагинацию.

## Зависимости

- Frontend API client/types из шага 11.
- Root page из шага 12.
- Category route из шага 13.

## Файлы

- Изменить: `resources/js/components/catalog/CatalogPageView.tsx`.
- Изменить: `resources/js/components/catalog/ProductCard.tsx`.
- Использовать: `resources/js/lib/catalog-format.ts`.

## Задачи

1. Изменить props `CatalogPageView`:
   - `data: CatalogPageResponse`;
   - `searchParams?: CatalogSearchParams`.
2. Удалить runtime imports:
   - `catalogProducts`;
   - `catalogCategoryTree`;
   - `getCatalogProductsByCategory`;
   - `getCatalogCategoryByPath`.
3. Рендерить breadcrumbs из `data.category`.
4. Рендерить список товаров из `data.products`.
5. Рендерить фильтры из `data.filters`:
   - регионы;
   - категории;
   - доступность;
   - состояние.
6. Сделать фильтры URL-based:
   - фильтр-ссылки через `<Link href="...">`;
   - поиск через `<form method="get">`;
   - сортировка через GET-form или маленький client component, который меняет URL.
7. При изменении фильтра:
   - сохранять остальные активные фильтры;
   - сбрасывать `page`;
   - не добавлять пустые/default значения.
8. Кнопка сброса фильтров ведет на текущий `baseHref` без query params.
9. Пагинация:
   - строит ссылки с активными query params;
   - меняет только `page`;
   - не показывает лишние/битые страницы.
10. `ProductCard` перевести на `CatalogProductCard`:
   - `product.href` использовать напрямую;
   - `product.imageUrl` использовать напрямую;
   - цену форматировать через `formatCatalogPrice`;
   - читать `category.name`, `region.name`, `state.name`, `availability.name`.

## Важные правила

- UI не должен считать фильтры и счетчики в памяти.
- Результат должен быть в HTML ответа.
- Нельзя ломать Header/Footer и общий визуальный каркас страницы.

## Проверка

- `/catalog` показывает 12 карточек из API.
- Ссылки фильтров ведут на URL с query params.
- Поиск работает при отправке формы.
- Сортировка меняет URL и выдачу.
- Пагинация сохраняет активные фильтры.

## Готово, когда

- Каталог визуально работает на API response.
- `ProductCard` больше не знает о моковом типе товара.
