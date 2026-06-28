# DatabasePlan — Шаги исполнения (индекс)

Разбивка плана [`DatabaseTextPlan.md`](../DatabaseTextPlan.md) на последовательные шаги.
Каждый шаг — отдельный `.md` файл с подробными инструкциями. Выполнять по порядку.

## Стек

- **Laravel** `^13.8`, **PHP** `^8.3`
- **MySQL** `8.4`, кодировка `utf8mb4` / `utf8mb4_unicode_ci`
- **PSR-4**: `App\ => app/` (DDD-неймспейсы `App\Domain\...` подхватываются без правки `composer.json`)

## Порядок выполнения

| # | Файл | Назначение |
|---|---|---|
| 01 | [`01-ddd-structure.md`](01-ddd-structure.md) | Каркас DDD-директорий `app/Domain/*` |
| 02 | [`02-contact-migration-model.md`](02-contact-migration-model.md) | Контекст `Contact`: таблица + модель |
| 03 | [`03-banner-migration-model.md`](03-banner-migration-model.md) | Контекст `Banner`: таблица + модель |
| 04 | [`04-shipment-migrations-models.md`](04-shipment-migrations-models.md) | Контекст `Shipment`: 4 таблицы + модели |
| 05 | [`05-catalog-reference-migrations.md`](05-catalog-reference-migrations.md) | Справочники каталога (10 таблиц) + модели |
| 06 | [`06-products-migration-model.md`](06-products-migration-model.md) | Таблица `products` + модель |
| 07 | [`07-product-content-blocks.md`](07-product-content-blocks.md) | 5 контентных блоков товара (1:1) |
| 08 | [`08-product-status-blocks.md`](08-product-status-blocks.md) | Проверка / погрузка / демонтаж (1:1) |
| 09 | [`09-product-images.md`](09-product-images.md) | Изображения товара (1:N) |
| 10 | [`10-product-pivots.md`](10-product-pivots.md) | Pivot-таблицы `product_tag/region/manager` |
| 11 | [`11-seeders.md`](11-seeders.md) | Сидеры справочников по умолчанию |
| 12 | [`12-xml-import.md`](12-xml-import.md) | Импорт XML-выгрузки (upsert по `external_id`) |
| 13 | [`13-tests.md`](13-tests.md) | Тесты миграций и идемпотентности импорта |

## Общие соглашения (для всех шагов)

- Миграции — единая директория `database/migrations`; timestamp в имени выстраивает порядок (родители → зависимые).
- PK `id` (`id()`), FK `*_id` (`foreignId`), внешний ключ импорта `external_id` (`unsignedBigInteger`, индекс).
- `created_at` / `updated_at` во всех контентных таблицах; `softDeletes` у `products`, `shipments`, `banners`.
- HTML-контент — `text` / `longText`. Цена — `decimal(10, 2)`.
- Имена таблиц: snake_case, мн. число; pivot — ед. число по алфавиту.
- Eloquent-модели располагаются в `App\Domain\<Context>\Models`; задавать `$table`, `$fillable`, `$casts`, связи.

## Definition of Done (общий чеклист, раздел 10 плана)

- [ ] DDD-структура `app/Domain/{Contact,Shipment,Banner,Catalog}`
- [ ] Миграции по порядку (шаги 02–10)
- [ ] Eloquent-модели + связи
- [ ] Сидеры справочников (шаг 11)
- [ ] Импорт XML с upsert по `external_id` (шаг 12)
- [ ] Индексы: `external_id` (unique), `slug`/`sku` (unique), уникальные индексы pivot'ов
- [ ] Тесты: миграции применяются, импорт идемпотентен (шаг 13)
