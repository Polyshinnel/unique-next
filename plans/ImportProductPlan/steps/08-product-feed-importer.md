# Шаг 08 — `ProductFeedImporter` (оркестратор)

## Цель

Связать все компоненты (downloader, parser, резолверы, image downloader) в единый процесс
по алгоритму раздела 3 плана. Каждое объявление — в отдельной транзакции. Возвращает счётчики
created/skipped/failed.

## Расположение

`app/Domain/Catalog/Import/Services/ProductFeedImporter.php`,
неймспейс `App\Domain\Catalog\Import\Services`.

## Сигнатура

```php
final class ProductFeedImporter
{
    public function __construct(
        private FeedDownloader $downloader,
        private FeedParser $parser,
        private CategoryResolver $categories,
        private StatusResolvers $statuses,
        private ManagerResolver $managers,
        private RegionResolver $regions,
        private TagResolver $tags,
        private ImageDownloader $images,
    ) {}

    public function import(string $url, bool $updateExisting = false): ImportResult { ... }
}
```

## Алгоритм (раздел 3 плана)

```text
1. $path = downloader->download($url)            // скачать фид во временный файл
2. Справочники верхнего уровня (идемпотентно):
   2.1 categories->upsertMany() ; затем categories->linkParents()   // два прохода
   2.2 advertisement_statuses → statuses->productStatus()
   2.3 check_statuses          → statuses->checkStatus()
   2.4 install_statuses        → statuses->shipmentStatus() И statuses->dismantleStatus()
3. foreach parser->advertisements($path) as $adv:
   DB::transaction(function () use ($adv) {
     3.1 Дедуп: Product::where external_id (fallback sku)
         - найден и !updateExisting → skipped++, return
         - найден и  updateExisting → обновить изменяемые поля (price, status, published_at)
         - не найден → создать
     3.2 Резолв связей: category, product_status, equipment_state/availability,
         manager(owner), regions, check/loading/removal (по name), tags
     3.3 Создать/обновить products + one-to-one текстовые блоки
     3.4 Привязать pivot: product_region, product_manager (role), product_tag (sync)
     3.5 product_checks / product_loadings / product_dismantlings (+ comment)
     3.6 foreach media: images->download($product, $mediaItem)
     created++
   })  // ошибка одного товара → failed++, лог, продолжаем
4. Удалить временный файл; вернуть ImportResult(created, skipped, failed)
```

## One-to-one текстовые блоки (раздел 2.3)

`updateOrCreate(['product_id' => $id], ['content' => $cdata])` для:

| DTO-поле | Таблица |
|---|---|
| `mainCharacteristics` | `product_main_characteristics` |
| `complectation` | `product_complectations` |
| `technicalCharacteristics` | `product_technical_characteristics` |
| `mainInfo` | `product_main_infos` |
| `additionalInfo` | `product_additional_infos` |

## Статусные блоки (раздел 2.4)

- `check` → `check_statuses` (по name) + `product_checks` (`check_status_id`, `comment`).
- `loading` → `shipment_statuses` (по name) + `product_loadings`.
- `removal` → `dismantle_statuses` (по name) + `product_dismantlings`.

## Pivot-связи (раздел 2.5–2.8)

- `product_region` — `regions()->sync([...])`.
- `product_manager` — `managers()->syncWithoutDetaching([$id => ['role' => 'product_owner']])`;
  плюс `products.manager_id = ownerId`.
- `product_tag` — `tags()->sync([...])`.

## Slug категорий (раздел 10 плана)

- Генерировать транслитерацией кириллицы (`Str::slug` недостаточно — нужен транслит-хелпер).
- При коллизии — суффикс `-2`, `-3`.
- Фиксировать при создании; при rename имени slug **не менять**.

## ImportResult

Простой DTO/value-object со счётчиками: `created`, `skipped`, `failed`, опц. `message`.
Используется job'ом и (опц.) для записи в `product_imports` (шаг 03).

## Крайние случаи (раздел 10 плана)

- Транзакция на объявление: сбой одного товара не откатывает остальные.
- `published_at` парсить из `Y-m-d H:i:s`; `price` → `decimal(10,2)`.
- `description` и `og_image` → `null` (в фиде нет аналога).
- Логировать счётчики и ошибки.

## Definition of Done

- [ ] `ProductFeedImporter::import()` реализует порядок раздела 3.
- [ ] Справочники импортируются первыми; категории — в два прохода.
- [ ] Дедуп по `external_id`/`sku`; режим `--update-existing` поддержан.
- [ ] One-to-one блоки, статусные блоки, pivot'ы и медиа создаются.
- [ ] Транзакция на объявление; ошибка товара → failed++ и продолжение.
- [ ] Возвращает `ImportResult` со счётчиками.
