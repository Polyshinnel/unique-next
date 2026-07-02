# CatalogPlan — вывод реальных товаров на `/catalog` и страницы категорий

## 1. Цель

Заменить текущий демонстрационный каталог на SSR-вывод реальных товаров из MySQL:

- `/catalog` показывает все опубликованные станки.
- `/catalog/{category-path}` показывает станки выбранной категории и ее дочерних категорий.
- Фильтры, сортировка, поиск и пагинация работают через URL query params, чтобы результат был доступен поисковым системам и открывался без клиентского JavaScript.
- На страницу приходит уже готовая HTML-разметка с карточками товаров, фильтрами, счетчиками и пагинацией.

Фиксированная пагинация: **12 станков на страницу**.

---

## 2. Что уже есть в проекте

### 2.1 Frontend

Текущие файлы каталога:

- `resources/js/app/catalog/page.tsx` — SSR route `/catalog`, сейчас передает `searchParams` в `CatalogPageView`.
- `resources/js/app/catalog/[...slug]/page.tsx` — catch-all route для категорий и карточек товара.
- `resources/js/components/catalog/CatalogPageView.tsx` — текущая страница каталога, фильтры, список товаров, пагинация.
- `resources/js/components/catalog/ProductCard.tsx` — карточка товара.
- `resources/js/lib/catalog-products.ts` — моковые товары.
- `resources/js/lib/catalog-categories.ts` — статическое дерево категорий.
- `resources/js/lib/api.ts` — готовый SSR API-клиент: `api.server.get(...)`.

Сейчас каталог работает на моках:

- категории захардкожены в `catalog-categories.ts`;
- товары захардкожены в `catalog-products.ts`;
- фильтр по региону и счетчики считаются в памяти;
- поиск, доступность, состояние и сортировка в UI фактически не применяют серверную выборку;
- `generateStaticParams()` строится из моков.

### 2.2 Backend

Текущие API routes:

- `GET /api/banners`
- `GET /api/contacts`
- `GET /api/health`
- `GET /api/seo/by-key/{key}`
- `GET /api/seo/by-path`

API для каталога пока нет.

### 2.3 Фактическое состояние локальной БД

Проверено через `docker compose exec app php artisan tinker`:

| Сущность | Кол-во |
|---|---:|
| `products` | 652 |
| опубликованные `products.published_at is not null` | 652 |
| `categories` | 63 |
| `regions` | 10 |
| `equipment_availabilities` | 1 |
| `equipment_states` | 2 |
| `tags` | 1782 |
| `product_region` | 652 |
| товары с `products.region_id` | 652 |

Справочники сейчас содержат:

- доступность: `В наличии`;
- состояние: `Б.У`, `Новое`;
- регионы: `Волгоградская область`, `Воронежская область`, `Калужская область`, `Кировская область`, `Мурманская область`, `Самарская область`, `Тульская область`, `Удмуртия`, `Ульяновская область`, `Челябинская область`.

---

## 3. Таблицы, задействованные в каталоге

### 3.1 Основная выдача товаров

| Таблица | Назначение |
|---|---|
| `products` | основная строка товара: `id`, `external_id`, `name`, `sku`, `title`, `description`, `category_id`, `manager_id`, `equipment_state_id`, `equipment_availability_id`, `product_status_id`, `price`, `show_price`, `price_comment`, `region_id`, `published_at`, soft delete |
| `categories` | категории: `id`, `external_id`, `name`, `slug`, `parent_id`, `title`, `description`, `og_image` |
| `product_images` | изображения товара, основное изображение через `is_main`, сортировка через `sort_order` |
| `regions` | регионы |
| `product_region` | many-to-many связь товара с регионами |
| `product_statuses` | статус публикации/продажи товара; в каталоге участвуют только товары со статусом `В продаже` |
| `equipment_availabilities` | варианты доступности |
| `equipment_states` | варианты состояния |
| `tags` | теги |
| `product_tag` | связь товара с тегами |
| `managers` | менеджер для карточки/детальной страницы |
| `product_manager` | дополнительные менеджеры товара, если понадобится |

### 3.2 Детальная карточка товара

Эти таблицы не обязательны для списка `/catalog`, но уже используются смыслово для страницы товара:

| Таблица | Назначение |
|---|---|
| `product_main_characteristics` | основные характеристики |
| `product_complectations` | комплектация |
| `product_technical_characteristics` | технические характеристики |
| `product_main_infos` | основная информация |
| `product_additional_infos` | дополнительная информация |
| `product_checks` + `check_statuses` | проверка |
| `product_loadings` + `shipment_statuses` | погрузка |
| `product_dismantlings` + `dismantle_statuses` | демонтаж |

### 3.3 Важное решение по регионам

В схеме есть две связи с регионами:

- `products.region_id` — основной регион товара;
- `product_region` — many-to-many регионы товара.

Импорт сейчас заполняет обе связи. Для фильтров и счетчиков лучше использовать **`product_region` + `regions`** как основной источник: это не ломает текущий случай "один товар — один регион" и сохраняет поддержку нескольких регионов в будущем. Для отображения в карточке можно брать первый регион из `regions` или fallback на `products.region`.

Все счетчики по регионам должны считать `distinct products.id`, чтобы many-to-many не давал дублей.

---

## 4. URL и query params

### 4.1 Страницы

| URL | Назначение |
|---|---|
| `/catalog` | общий каталог |
| `/catalog/{category-slug}` | корневая категория |
| `/catalog/{parent-slug}/{child-slug}` | дочерняя категория |
| `/catalog/{category-path}/{product-id}` | детальная страница товара |

`category-path` строится из цепочки `categories.slug` от корня до текущей категории.

### 4.2 Query params

| Param | Пример | Назначение |
|---|---|---|
| `page` | `2` | номер страницы |
| `region` | `6` | `regions.id` |
| `availability` | `1` | `equipment_availabilities.id` |
| `state` | `2` | `equipment_states.id` |
| `sort` | `price_desc` | сортировка |
| `search` | `16К20` | поиск по названию, артикулу, тегу |

Использовать id справочников в query params надежнее, чем имя: названия на русском могут меняться, содержать пробелы и разные варианты регистра.

Поддерживаемые сортировки:

- отсутствует или `default` — `products.id desc`;
- `price_desc` — цена по убыванию;
- `price_asc` — цена по возрастанию.

Для сортировки по цене товары без опубликованной цены (`price is null` или `show_price = false`) ставить в конец:

```sql
order by
  case when show_price = 1 and price is not null then 0 else 1 end asc,
  price desc|asc,
  id desc
```

Отображение цены:

- если `products.show_price = false`, на карточке и детальной странице всегда выводить `По запросу`;
- если `products.show_price = true`, но `products.price is null`, также выводить `По запросу`;
- числовую цену выводить только когда одновременно `show_price = true` и `price is not null`.

---

## 5. Backend API

### 5.1 Добавить routes

В `routes/api.php`:

```php
Route::prefix('catalog')->group(function () {
    Route::get('/page', [CatalogPageController::class, 'show']);
    Route::get('/categories/by-path', [CatalogCategoryController::class, 'byPath']);
    Route::get('/products/{product}', [CatalogProductController::class, 'show']);
});
```

Почему не один catch-all route в API:

- `/page` удобно возвращает все данные для SSR списка одним запросом;
- `/categories/by-path?path=...` удобно использовать в `generateMetadata()` и при разрешении slug;
- `/products/{product}` просто и надежно работает по числовому `id`.

### 5.2 `GET /api/catalog/page`

Query:

| Param | Тип | Примечание |
|---|---|---|
| `category_path` | string/null | `tokarnye-stanki/16k20-i-analogi` |
| `page` | int | минимум 1 |
| `region` | int/null | `regions.id` |
| `availability` | int/null | `equipment_availabilities.id` |
| `state` | int/null | `equipment_states.id` |
| `sort` | string/null | `default`, `price_desc`, `price_asc` |
| `search` | string/null | trim, ограничить длину |

Response:

```json
{
  "category": {
    "id": 12,
    "name": "Токарные станки",
    "title": "Токарные станки",
    "description": "...",
    "slug": "tokarnye-stanki",
    "path": ["tokarnye-stanki"],
    "href": "/catalog/tokarnye-stanki",
    "breadcrumbs": []
  },
  "filters": {
    "regions": [
      { "id": 6, "name": "Самарская область", "count": 24, "href": "/catalog?region=6" }
    ],
    "categories": [
      {
        "id": 1,
        "name": "Токарные станки",
        "slug": "tokarnye-stanki",
        "href": "/catalog/tokarnye-stanki",
        "count": 31,
        "level": 0,
        "children": []
      }
    ],
    "availabilities": [
      { "id": 1, "name": "В наличии", "count": 652, "href": "/catalog?availability=1" }
    ],
    "states": [
      { "id": 1, "name": "Б.У", "count": 641, "href": "/catalog?state=1" }
    ]
  },
  "sorting": {
    "active": "default",
    "options": [
      { "value": "default", "label": "По умолчанию" },
      { "value": "price_desc", "label": "По убыванию цены" },
      { "value": "price_asc", "label": "По возрастанию цены" }
    ]
  },
  "products": [
    {
      "id": 652,
      "title": "Токарно-винторезный 1К62 (РМЦ 1000) 1965 г.в.",
      "sku": "ИЖК-022-05052026-1756",
      "category": { "id": 2, "name": "Токарные станки", "href": "/catalog/..." },
      "region": { "id": 6, "name": "Самарская область" },
      "price": { "amount": "95000.00", "isPublished": true, "comment": null },
      "availability": { "id": 1, "name": "В наличии", "color": null },
      "state": { "id": 1, "name": "Б.У" },
      "imageUrl": "/storage/catalog/...",
      "href": "/catalog/.../652"
    }
  ],
  "pagination": {
    "currentPage": 1,
    "perPage": 12,
    "total": 652,
    "totalPages": 55
  }
}
```

Если `category_path` не найден, вернуть `404`.

`price.isPublished` в ответе вычислять строго из `products.show_price`: если `show_price = false`, API возвращает `isPublished: false`, даже если в `products.price` есть числовое значение.

### 5.3 `GET /api/catalog/categories/by-path`

Query:

- `path=tokarnye-stanki/16k20-i-analogi`

Response:

```json
{
  "id": 12,
  "name": "16К20 и аналоги",
  "title": "16К20 и аналоги",
  "description": "...",
  "slug": "16k20-i-analogi",
  "path": ["tokarnye-stanki", "16k20-i-analogi"],
  "href": "/catalog/tokarnye-stanki/16k20-i-analogi",
  "breadcrumbs": []
}
```

Использование:

- `generateMetadata()` для страниц категорий;
- разрешение `slug` в `resources/js/app/catalog/[...slug]/page.tsx`.

### 5.4 `GET /api/catalog/products/{id}`

Возвращает данные детальной страницы товара и проверяет канонический путь:

```json
{
  "id": 652,
  "title": "...",
  "sku": "...",
  "description": "...",
  "summary": "...",
  "canonicalHref": "/catalog/tokarnye-stanki/652",
  "category": {},
  "region": {},
  "price": {},
  "availability": {},
  "state": {},
  "manager": {},
  "images": [],
  "tags": [],
  "characteristicBlocks": []
}
```

Это не основной пункт текущей задачи, но текущий catch-all route уже обслуживает и категории, и товары. При замене моков нельзя оставить детальную страницу на старом `catalogProducts`.

---

## 6. Backend-логика выборки

### 6.1 Базовый product query

Единая база для всех выборок:

```php
Product::query()
    ->whereNotNull('published_at')
    ->whereHas('productStatus', fn ($q) => $q->where('name', 'В продаже'))
    ->whereNull('deleted_at');
```

Eloquent `SoftDeletes` уже добавляет `where deleted_at is null`, но в описании логики важно явно учитывать soft delete.

Статус `В продаже` — обязательное условие для всех публичных выборок:

- список `/catalog`;
- страницы категорий;
- счетчики фильтров;
- поиск;
- детальная страница товара.

Если товар опубликован по `published_at`, но его `product_statuses.name` отличается от `В продаже`, он не должен попадать в выдачу и должен возвращать `404` на детальной странице.

Eager load для списка:

```php
[
    'category',
    'mainImage',
    'productStatus',
    'equipmentAvailability',
    'equipmentState',
    'regions',
]
```

Для детальной страницы:

```php
[
    'category.parent',
    'images',
    'productStatus',
    'equipmentAvailability',
    'equipmentState',
    'regions',
    'manager',
    'tags',
    'mainCharacteristics',
    'complectation',
    'technicalCharacteristics',
    'mainInfo',
    'additionalInfo',
]
```

### 6.2 Фильтр по категории

1. Найти категорию по `category_path`.
2. Собрать id категории и всех потомков.
3. Добавить:

```php
$query->whereIn('category_id', $categoryIds);
```

Для получения потомков можно:

- на первом этапе загрузить все категории и собрать дерево в PHP, потому что категорий мало;
- позже заменить на recursive CTE, если категорий станет много.

### 6.3 Фильтр по региону

Использовать `product_region`:

```php
$query->whereHas('regions', fn ($q) => $q->whereKey($regionId));
```

Fallback на `products.region_id` не нужен для текущих данных, потому что `product_region` заполнен. Если появится риск старых данных без pivot, добавить отдельную миграцию/команду синхронизации, а не усложнять каждую выборку.

### 6.4 Фильтр по доступности

```php
$query->where('equipment_availability_id', $availabilityId);
```

### 6.5 Фильтр по состоянию

```php
$query->where('equipment_state_id', $stateId);
```

### 6.6 Поиск

Поиск должен работать по:

- `products.title`;
- `products.name`;
- `products.sku`;
- `tags.name`.

Логика:

```php
$query->where(function ($q) use ($search) {
    $q->where('title', 'like', "%{$escaped}%")
      ->orWhere('name', 'like', "%{$escaped}%")
      ->orWhere('sku', 'like', "%{$escaped}%")
      ->orWhereHas('tags', fn ($tagQuery) => $tagQuery->where('name', 'like', "%{$escaped}%"));
});
```

Требования:

- `trim`;
- ограничить длину, например 100 символов;
- экранировать `%`, `_`, `\` для `LIKE`;
- пустую строку не применять.

### 6.7 Сортировка

```php
match ($sort) {
    'price_desc' => $query
        ->orderByRaw('CASE WHEN show_price = 1 AND price IS NOT NULL THEN 0 ELSE 1 END')
        ->orderByDesc('price')
        ->orderByDesc('id'),
    'price_asc' => $query
        ->orderByRaw('CASE WHEN show_price = 1 AND price IS NOT NULL THEN 0 ELSE 1 END')
        ->orderBy('price')
        ->orderByDesc('id'),
    default => $query->orderByDesc('id'),
};
```

### 6.8 Пагинация

```php
$products = $query->paginate(
    perPage: 12,
    page: $page,
);
```

`perPage` не принимать из URL в первой версии, чтобы выполнить требование "по 12 станков на страницу" и не открыть тяжелые запросы.

---

## 7. Счетчики фильтров

Главное правило: счетчики должны считаться по той же опубликованной базе товаров, что и выдача.

Под "той же базой" всегда подразумеваются только товары:

- без soft delete;
- с заполненным `published_at`;
- со статусом `product_statuses.name = "В продаже"`.

### 7.1 Региональные счетчики

"Все доступные регионы и сколько станков по заданному фильтру по региону" трактуем так:

- счетчик региона учитывает текущую категорию, поиск, доступность и состояние;
- активный регион из URL при расчете региональных счетчиков исключается, чтобы пользователь видел, сколько будет товаров при переключении на другой регион.

Пример:

```text
base = published + category + search + availability + state
regionCounts = base grouped by regions.id
products = base + active region
```

SQL-идея:

```sql
select regions.id, regions.name, count(distinct products.id) as products_count
from regions
join product_region on product_region.region_id = regions.id
join products on products.id = product_region.product_id
where ...
group by regions.id, regions.name
order by regions.name
```

### 7.2 Категории и ссылки

Нужно вернуть все категории с:

- `id`;
- `name`/`title`;
- `slug`;
- `path`;
- `href`;
- `level`;
- `children`;
- `count`.

Счетчик категории должен включать товары самой категории и всех дочерних категорий.

Алгоритм:

1. Загрузить все категории.
2. Собрать дерево и `path` для каждой категории.
3. Посчитать количество товаров по прямому `category_id` с учетом активных фильтров, кроме текущего category filter.
4. Прокатить counts вверх по дереву: parent count += children counts.
5. Вернуть дерево в порядке имени или существующего порядка `id`. Лучше на первом этапе `orderBy('name')`, если нет отдельного `sort_order`.

При активной категории подсветка делается на frontend по `currentCategory.id` и `breadcrumbs/path`.

### 7.3 Доступность

Справочник: `equipment_availabilities`.

Счетчик доступности учитывает категорию, поиск, регион, состояние, но исключает активную доступность.

Даже если сейчас в базе только `В наличии`, API должен возвращать список динамически из таблицы.

### 7.4 Состояние

Справочник: `equipment_states`.

Счетчик состояния учитывает категорию, поиск, регион, доступность, но исключает активное состояние.

---

## 8. Backend-структура кода

Предлагаемая структура:

```text
app/
└── Domain/
    └── Catalog/
        ├── Data/
        │   ├── CatalogFiltersData.php
        │   ├── CatalogPageData.php
        │   ├── CatalogProductCardData.php
        │   ├── CatalogProductDetailData.php
        │   └── CatalogCategoryData.php
        ├── Actions/
        │   ├── BuildCatalogPageAction.php
        │   ├── BuildCatalogFiltersAction.php
        │   ├── ResolveCatalogCategoryPathAction.php
        │   ├── ResolveCatalogProductHrefAction.php
        │   └── SearchCatalogProductsAction.php
        └── Support/
            ├── CatalogQuery.php
            └── CategoryTreeBuilder.php

app/
└── Http/
    ├── Controllers/
    │   └── Api/
    │       ├── CatalogPageController.php
    │       ├── CatalogCategoryController.php
    │       └── CatalogProductController.php
    ├── Requests/
    │   └── Catalog/
    │       └── CatalogPageRequest.php
    └── Resources/
        └── Catalog/
            ├── CatalogCategoryResource.php
            ├── CatalogFilterOptionResource.php
            ├── CatalogPageResource.php
            ├── CatalogProductCardResource.php
            └── CatalogProductDetailResource.php
```

Если проект предпочитает меньше классов, можно начать с:

- `CatalogPageController`;
- `CatalogCategoryController`;
- `CatalogProductController`;
- `BuildCatalogPageAction`;
- `CategoryTreeBuilder`;
- `CatalogPageRequest`;
- API Resources.

---

## 9. Frontend-план

### 9.1 Типы и API-клиент

Создать:

```text
resources/js/lib/catalog-api.ts
```

Содержимое:

- TypeScript-типы ответа `CatalogPageResponse`, `CatalogProductCard`, `CatalogFilterOption`, `CatalogCategoryNode`, `CatalogPagination`.
- `getCatalogPage(params, isCategory?)`.
- `getCatalogCategoryByPath(path)`.
- `getCatalogProduct(id)`.
- helper для сборки query params.

Все запросы из страниц делать через `api.server.get(...)`, чтобы данные приходили на сервере.

### 9.2 `/catalog/page.tsx`

Изменить:

- читать все нужные `searchParams`: `page`, `region`, `availability`, `state`, `sort`, `search`;
- вызвать `getCatalogPage({ ...searchParams })`;
- передать готовые данные в `CatalogPageView`;
- оставить `generateMetadata()` через `getPageSeo('catalog')`.

### 9.3 `/catalog/[...slug]/page.tsx`

Новая логика:

1. Получить `slug`.
2. Если последний сегмент числовой:
   - считать это product id;
   - вызвать `getCatalogProduct(id)`;
   - сверить текущий путь с `product.canonicalHref`;
   - если не совпадает, сделать `permanentRedirect(product.canonicalHref)`;
   - отрендерить детальную страницу.
3. Если последний сегмент не числовой:
   - считать весь slug category path;
   - вызвать `getCatalogPage({ category_path: slug.join('/'), ...searchParams })`;
   - если API вернул 404, `notFound()`;
   - отрендерить `CatalogPageView`.

`generateStaticParams()` удалить или не использовать для моков. Для настоящего SSR страница должна уметь рендерить новые товары и категории без пересборки Next.

### 9.4 `CatalogPageView`

Переделать компонент с моков на props:

```ts
type CatalogPageViewProps = {
    data: CatalogPageResponse;
    searchParams?: CatalogSearchParams;
};
```

Убрать зависимости от:

- `catalogProducts`;
- `catalogCategoryTree`;
- `getCatalogProductsByCategory`;
- `getCatalogCategoryByPath`.

Оставить:

- Header/Footer;
- hero;
- breadcrumbs;
- сетку карточек;
- пагинацию.

### 9.5 Фильтры как SSR-friendly формы/ссылки

Все изменения фильтров должны вести на URL:

- регион, категория, доступность, состояние — обычные `<Link href="...">`;
- поиск — `<form method="get">`;
- сортировка — `<select name="sort">` внутри формы с кнопкой "Применить" или маленький client-компонент, который только меняет URL. Результат все равно рендерится сервером.

При построении ссылок:

- сохранять остальные активные фильтры;
- сбрасывать `page` на `1` при изменении любого фильтра, поиска или сортировки;
- не добавлять в URL пустые/default значения;
- кнопка "Сбросить фильтры" ведет на текущий `baseHref` без query params.

### 9.6 `ProductCard`

Переделать тип товара с мокового на API-тип:

- `product.href` использовать напрямую;
- `product.imageUrl` уже приходит готовым;
- `product.price` форматировать через общий helper;
- `product.category.name`, `product.region.name`, `product.state.name`, `product.availability.name`.

### 9.7 Форматирование цены

Перенести helper из `catalog-products.ts` в независимый файл:

```text
resources/js/lib/catalog-format.ts
```

Правило:

- если `!price.isPublished` или `price.amount === null` → `По запросу`;
- иначе форматировать рубли с пробелами.

Backend обязан выставлять `price.isPublished = false`, когда `products.show_price = false`. Поэтому frontend не должен дополнительно пытаться показать `amount`, если `isPublished` ложный.

После перехода на API `catalog-products.ts` и `catalog-categories.ts` можно удалить, если детальная страница тоже полностью переведена на backend.

---

## 10. SEO и SSR

### 10.1 SSR

Требования:

- не использовать client-side fetch для получения списка товаров;
- результат фильтрации, сортировки, поиска и пагинации должен быть в HTML ответа;
- query params должны быть индексируемыми ссылками/формами;
- API-вызовы из Next — через `api.server.get`.

Для каталога лучше использовать `cache: 'no-store'` на первом этапе, потому что товары обновляются импортом:

```ts
api.server.get<CatalogPageResponse>('/catalog/page', {
    params,
    cache: 'no-store',
});
```

Позже можно перейти на `next: { revalidate: 300, tags: [...] }` и инвалидировать tags после импорта.

### 10.2 Metadata

Для `/catalog` оставить текущий `getPageSeo('catalog')`.

Для категории:

- title: `category.title || category.name`;
- description: `category.description || "Станки категории ... в каталоге ЮНИК С."`;
- canonical: `category.href`.

Для страницы товара:

- title: `${product.title} | ЮНИК С`;
- description: `product.description` или короткий fallback из названия/категории/региона;
- canonical: `product.canonicalHref`;
- `openGraph.images`: `product.og_image` или главное изображение.

---

## 11. Индексы и производительность

Текущие уникальные индексы уже есть:

- `products.external_id`;
- `products.sku`;
- `categories.external_id`;
- `categories.slug`;
- pivot unique indexes.

Рекомендуемые дополнительные индексы для каталога:

```php
// products
$table->index(['published_at', 'id']);
$table->index(['category_id', 'published_at']);
$table->index(['product_status_id', 'published_at']);
$table->index(['equipment_availability_id', 'published_at']);
$table->index(['equipment_state_id', 'published_at']);
$table->index(['show_price', 'price']);

// product_region
$table->index(['region_id', 'product_id']);

// product_tag
$table->index(['tag_id', 'product_id']);

// tags
$table->index('name');
```

Для поиска `LIKE "%query%"` обычный индекс почти не поможет. На первом этапе это приемлемо для 652 товаров. Если товаров станет много, добавить:

- MySQL FULLTEXT по `products.name`, `products.title`, `products.sku`;
- отдельный search index/materialized table для тегов;
- нормализацию регистра/морфологии, если потребуется.

---

## 12. Порядок реализации

### Шаг 1. Backend request validation

- Создать `CatalogPageRequest`.
- Валидировать:
  - `page`: integer, min 1;
  - `region`: nullable, exists `regions,id`;
  - `availability`: nullable, exists `equipment_availabilities,id`;
  - `state`: nullable, exists `equipment_states,id`;
  - `sort`: nullable, in `default`, `price_desc`, `price_asc`;
  - `search`: nullable string max 100;
  - `category_path`: nullable string max 500.
- Нормализовать пустые строки в `null`.

### Шаг 2. Category path resolver

- Создать `CategoryTreeBuilder` / `ResolveCatalogCategoryPathAction`.
- Научиться:
  - строить `path` и `href` для каждой категории;
  - находить категорию по полному path;
  - получать descendants ids;
  - строить breadcrumbs.

### Шаг 3. Product query builder

- Создать общий builder/action для применения:
  - published scope;
  - product status `В продаже`;
  - category descendants;
  - region;
  - availability;
  - state;
  - search;
  - sort.
- Обязательно использовать clone query для счетчиков, чтобы не размножить разные правила фильтрации.

### Шаг 4. Catalog page API

- Создать `CatalogPageController`.
- Вернуть:
  - текущую категорию или `null`;
  - фильтры со счетчиками;
  - товары текущей страницы;
  - пагинацию;
  - сортировку.

### Шаг 5. Category API

- Создать `CatalogCategoryController::byPath`.
- Вернуть 404 для несуществующего path.
- Использовать в metadata и slug resolver.

### Шаг 6. Product detail API

- Создать `CatalogProductController::show`.
- Вернуть товар, canonical href, категорию, менеджера, изображения, теги, блоки характеристик.
- Вернуть 404, если товар не опубликован или удален.

### Шаг 7. Frontend catalog API client

- Создать `resources/js/lib/catalog-api.ts`.
- Описать типы response.
- Добавить функции:
  - `getCatalogPage`;
  - `getCatalogCategoryByPath`;
  - `getCatalogProduct`;
  - helpers для query params.

### Шаг 8. Перевести `/catalog`

- Обновить `resources/js/app/catalog/page.tsx`.
- Получать данные через `getCatalogPage`.
- Передавать `data` в `CatalogPageView`.

### Шаг 9. Перевести category pages

- Обновить `resources/js/app/catalog/[...slug]/page.tsx`.
- Категории отдавать через `getCatalogPage({ category_path })`.
- Убрать зависимость от статического дерева категорий.

### Шаг 10. Перевести ProductCard и CatalogPageView

- Убрать моковые импорты.
- Рендерить фильтры по данным API.
- Сделать поиск и сортировку реальными GET-формами/URL.
- Сохранять query params в ссылках фильтров и пагинации.

### Шаг 11. Перевести детальную страницу товара

- Убрать `getCatalogProduct` из моков.
- Рендерить товар из API.
- Проверять canonical href и делать redirect при несовпадении.

### Шаг 12. Удалить моковые данные

После полной миграции:

- удалить или перестать использовать `resources/js/lib/catalog-products.ts`;
- удалить или перестать использовать `resources/js/lib/catalog-categories.ts`;
- проверить, что `rg "catalogProducts|catalogCategoryTree"` не находит runtime-использований.

### Шаг 13. Индексы

- Добавить миграцию с индексами из раздела 11.
- Проверить, что миграция применяется на текущей БД.

---

## 13. Тесты

### 13.1 Feature tests API

Добавить тесты:

- `GET /api/catalog/page` возвращает 200 и 12 товаров на страницу.
- `GET /api/catalog/page` возвращает только товары со статусом `В продаже`.
- товар не в статусе `В продаже` не открывается на детальной странице и возвращает 404.
- товар с `show_price = false` возвращает `price.isPublished = false`, а frontend выводит `По запросу`.
- Default sort возвращает товары по `id desc`.
- `sort=price_desc` сортирует по цене по убыванию, товары без цены в конце.
- `sort=price_asc` сортирует по цене по возрастанию, товары без цены в конце.
- `search` находит по `title`.
- `search` находит по `name`.
- `search` находит по `sku`.
- `search` находит по `tags.name`.
- `region` фильтрует по `product_region`.
- `availability` фильтрует по `equipment_availability_id`.
- `state` фильтрует по `equipment_state_id`.
- `category_path` включает товары дочерних категорий.
- несуществующий `category_path` возвращает 404.
- счетчики регионов считаются с учетом активных фильтров, кроме активного региона.
- счетчики категорий включают descendants.
- пагинация возвращает корректные `total`, `currentPage`, `totalPages`.

### 13.2 Frontend/build checks

- `npm run build`
- Проверить SSR HTML:
  - `curl http://localhost:28080/catalog` содержит названия реальных товаров;
  - `curl http://localhost:28080/catalog?page=2` содержит другие товары;
  - `curl "http://localhost:28080/catalog?search=16%D0%9A20"` содержит результаты поиска;
  - страница категории открывается по реальному slug из БД.

### 13.3 Manual checklist

- `/catalog` открывается и показывает 12 товаров.
- Пагинация сохраняет активные фильтры.
- Фильтр региона показывает счетчики.
- Фильтр категорий показывает дерево и счетчики.
- Фильтр доступности строится из БД.
- Фильтр состояния строится из БД.
- Сортировка меняет URL и выдачу.
- Поиск ищет по названию, артикулу и тегу.
- Категорийные страницы используют тот же компонент выдачи.
- HTML страницы содержит карточки товаров без ожидания client-side fetch.
- Неканонический URL товара редиректит на canonical.

---

## 14. Definition of Done

- [ ] Реальные товары выводятся на `/catalog`.
- [ ] Реальные товары выводятся на `/catalog/{category-path}`.
- [ ] Все данные каталога приходят с Laravel API.
- [ ] В каталоге, категориях, поиске, счетчиках и детальной странице участвуют только товары со статусом `В продаже`.
- [ ] При `products.show_price = false` вместо цены выводится `По запросу`.
- [ ] Моки `catalog-products.ts` и `catalog-categories.ts` не используются в runtime каталога.
- [ ] Фильтр регионов возвращает доступные регионы и счетчики по текущей выборке.
- [ ] Фильтр категорий возвращает дерево, ссылки и счетчики товаров.
- [ ] Фильтр доступности строится из `equipment_availabilities`.
- [ ] Фильтр состояния строится из `equipment_states`.
- [ ] Сортировки `default`, `price_desc`, `price_asc` работают.
- [ ] Поиск работает по `name`, `title`, `sku`, `tags.name`.
- [ ] Пагинация фиксирована по 12 товаров.
- [ ] Рендер списка товаров выполняется SSR.
- [ ] Добавлены backend feature tests.
- [ ] `npm run build` проходит.
- [ ] `curl /catalog` показывает реальные товары в HTML.
