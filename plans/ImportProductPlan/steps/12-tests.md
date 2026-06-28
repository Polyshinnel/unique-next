# Шаг 12 — Тесты

## Цель

Покрыть импорт тестами: корректность маппинга/резолверов, идемпотентность (повторный прогон без
дублей), переименование справочников, диспатч job. Без реальных HTTP-запросов и записи файлов.

## Расположение

- Unit: `tests/Unit/Catalog/Import/...`
- Feature: `tests/Feature/Catalog/Import/...`
- Фикстура XML: `tests/Fixtures/advertisements.xml` (срез из примера ТЗ).

## Unit-тесты

- **Резолверы по `external_id`:**
  - create — записи нет → создаётся;
  - rename — запись есть, имя в фиде другое → обновляется `name`;
  - no-op — запись есть, имя то же → новых записей/изменений нет.
- **`CategoryResolver`:** два прохода проставляют `parent_id` независимо от порядка категорий.
- **Генерация slug:** транслит кириллицы, разрешение коллизий (`-2`, `-3`), неизменность при rename.
- **Маппинг DTO:** `FeedParser` корректно строит `AdvertisementData` (атрибуты `@id`, CDATA, булевы `1/0`).
- **`TagResolver`:** `firstOrCreate` по `name`, без дублей.

## Feature-тесты (прогон импорта на фикстуре)

Использовать `Http::fake()` (фид + изображения) и `Storage::fake('public')`.

```php
Http::fake([
    'panel.uniqset.com/*' => Http::response(file_get_contents($fixtureXml), 200),
    '*' => Http::response('fake-image-bytes', 200, ['Content-Type' => 'image/jpeg']),
]);
Storage::fake('public');
```

Проверить:

- создаются категории с корректным `parent_id`;
- создаётся товар со всеми связями: category, status, manager(owner), regions, tags,
  equipment_state/availability;
- создаются one-to-one текстовые блоки (`product_main_characteristics` и др.);
- создаются статусные блоки (`product_checks`/`product_loadings`/`product_dismantlings`) с `comment`;
- изображения сохранены на `Storage::fake('public')`, строки `product_images` с `file_path`/`is_main`;
- **идемпотентность:** повторный прогон не создаёт дубликатов (дедуп по `external_id`);
- **rename:** изменение имени категории/статуса в фиде → обновление в БД (без новой записи);
- **`--update-existing`:** при включённом режиме обновляются изменяемые поля (price/status/published_at);
- ошибка скачивания одной картинки (`Http::response('', 500)`) не валит импорт товара.

## Тесты очереди / команды

```php
Queue::fake();
$this->artisan('catalog:import-products')->assertSuccessful();
Queue::assertPushed(ImportProductsJob::class);
```

- Проверить, что job уникален (`ShouldBeUnique`) — повторный dispatch не плодит дубль в очереди.

## Definition of Done

- [ ] Unit: резолверы (create/rename/no-op), slug, маппинг DTO.
- [ ] Feature: полный прогон на фикстуре с `Http::fake`/`Storage::fake`.
- [ ] Проверена идемпотентность (повторный прогон без дублей).
- [ ] Проверены rename и режим `--update-existing`.
- [ ] Проверён диспатч `ImportProductsJob` (`Queue::fake`).
- [ ] Все тесты зелёные (`php artisan test`).
