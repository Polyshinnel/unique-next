# ImportProductPlan — Шаги исполнения (индекс)

Разбивка плана [`ImportProductPlan.md`](../ImportProductPlan.md) на последовательные шаги.
Каждый шаг — отдельный `.md` файл с подробными инструкциями. Выполнять строго по порядку.

## Стек

- **Laravel** `^13.8`, **PHP** `^8.3`, **MySQL** `8.4`
- **PSR-4**: `App\ => app/` (DDD-неймспейсы `App\Domain\...` подхватываются без правки `composer.json`)
- Очередь: `database` (по умолчанию) либо Redis + **Laravel Horizon** (уже в зависимостях)
- Парсинг: `XMLReader` (потоковый), файловый диск `public`

## Источник данных

- **URL фида:** `https://panel.uniqset.com/storage/exports/advertisements.xml`
- **Единица импорта:** `<advertisement>` → строка в `products`
- **Идемпотентность:** дедуп по `external_id` (fallback `sku`); upsert справочников по `external_id`

## Порядок выполнения

| # | Файл | Назначение |
|---|---|---|
| 01 | [`01-config.md`](01-config.md) | `config/catalog_import.php` + `.env.example` |
| 02 | [`02-drop-city-name.md`](02-drop-city-name.md) | Удалить `city_name` из `regions` (миграция/модель/резолвер) |
| 03 | [`03-product-imports-migration.md`](03-product-imports-migration.md) | (Опц.) лог-таблица `product_imports` |
| 04 | [`04-dto.md`](04-dto.md) | DTO в `Domain/Catalog/Import/Data` |
| 05 | [`05-feed-downloader-parser.md`](05-feed-downloader-parser.md) | `FeedDownloader` + `FeedParser` (XMLReader) |
| 06 | [`06-reference-resolvers.md`](06-reference-resolvers.md) | Резолверы справочников (idempotent upsert) |
| 07 | [`07-image-downloader.md`](07-image-downloader.md) | `ImageDownloader` (диск `public`, dedup) |
| 08 | [`08-product-feed-importer.md`](08-product-feed-importer.md) | `ProductFeedImporter` — оркестратор |
| 09 | [`09-import-job.md`](09-import-job.md) | `ImportProductsJob` (`ShouldQueue` + `ShouldBeUnique`) |
| 10 | [`10-import-command.md`](10-import-command.md) | `ImportProductsCommand` (`catalog:import-products`) |
| 11 | [`11-schedule.md`](11-schedule.md) | Планировщик в `routes/console.php` |
| 12 | [`12-tests.md`](12-tests.md) | Тесты Unit + Feature (`Http::fake` / `Storage::fake`) |

## Общие соглашения (для всех шагов)

- Код импортёра — в `app/Domain/Catalog/Import/{Commands,Jobs,Services,Resolvers,Data}`.
- Все upsert'ы справочников — по `external_id`; при расхождении имени — переименовать (`rename`).
- `check`/`loading`/`removal` внутри объявления не содержат `@id` → match по `name`.
- Каждое объявление обрабатывается в отдельной транзакции (`DB::transaction`).
- Ошибки скачивания изображения не должны ронять импорт товара — логировать и продолжать.
- Счётчики `created` / `skipped` / `failed` — в лог и (опц.) в `product_imports`.

## Definition of Done (общий чеклист, раздел 13 плана)

- [ ] `config/catalog_import.php` + `.env.example` (шаг 01)
- [ ] `city_name` удалён из `regions` (шаг 02)
- [ ] (Опц.) миграция `product_imports` (шаг 03)
- [ ] DTO advertisement/category/media и др. (шаг 04)
- [ ] `FeedDownloader` + `FeedParser` (шаг 05)
- [ ] Резолверы справочников idempotent (шаг 06)
- [ ] `ImageDownloader` (шаг 07)
- [ ] `ProductFeedImporter` (шаг 08)
- [ ] `ImportProductsJob` (шаг 09)
- [ ] `ImportProductsCommand` (шаг 10)
- [ ] Планировщик (шаг 11)
- [ ] Тесты Unit + Feature (шаг 12)
- [ ] `php artisan storage:link` выполнен
